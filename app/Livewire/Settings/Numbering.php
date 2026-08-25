<?php

namespace App\Livewire\Settings;

use App\Models\Invoice;
use App\Models\Job;
use App\Models\Setting;
use App\Services\InvoiceNumberGenerator;
use App\Services\JobNumberGenerator;
use Livewire\Component;

// Rendered as a tab inside Settings\Index — see resources/views/livewire/settings/index.blade.php.
class Numbering extends Component
{
    public string $nextJobNumber = '';

    public string $nextInvoiceNumber = '';

    public bool $jobNumberSaved = false;

    public bool $invoiceNumberSaved = false;

    public function mount(): void
    {
        $this->nextJobNumber = (string) $this->currentNextJobSequence();
        $this->nextInvoiceNumber = (string) $this->currentNextInvoiceSequence();
    }

    public function updatedNextJobNumber(): void
    {
        $this->jobNumberSaved = false;
    }

    public function updatedNextInvoiceNumber(): void
    {
        $this->invoiceNumberSaved = false;
    }

    private function currentNextJobSequence(): int
    {
        $stored = Setting::get(JobNumberGenerator::SETTING_KEY);

        return $stored !== null ? ((int) $stored) + 1 : $this->highestJobSequence() + 1;
    }

    private function currentNextInvoiceSequence(): int
    {
        $stored = Setting::get(InvoiceNumberGenerator::SETTING_KEY);

        return $stored !== null ? ((int) $stored) + 1 : $this->highestInvoiceSequence() + 1;
    }

    private function highestJobSequence(): int
    {
        return Job::withTrashed()->pluck('job_no')
            ->map(fn (string $jobNo) => (int) substr($jobNo, -4))
            ->max() ?? 0;
    }

    private function highestInvoiceSequence(): int
    {
        return Invoice::withTrashed()->pluck('invoice_no')
            ->map(fn (string $invoiceNo) => (int) substr($invoiceNo, -4))
            ->max() ?? 0;
    }

    public function saveJobNumber(): void
    {
        $data = $this->validate(['nextJobNumber' => 'required|integer|min:1']);

        $floor = $this->highestJobSequence();

        if ((int) $data['nextJobNumber'] <= $floor) {
            $this->addError('nextJobNumber', "Must be greater than the highest job number already in use ({$floor}).");

            return;
        }

        Setting::set(JobNumberGenerator::SETTING_KEY, (string) ((int) $data['nextJobNumber'] - 1));
        $this->jobNumberSaved = true;
    }

    public function saveInvoiceNumber(): void
    {
        $data = $this->validate(['nextInvoiceNumber' => 'required|integer|min:1']);

        $floor = $this->highestInvoiceSequence();

        if ((int) $data['nextInvoiceNumber'] <= $floor) {
            $this->addError('nextInvoiceNumber', "Must be greater than the highest invoice number already in use ({$floor}).");

            return;
        }

        Setting::set(InvoiceNumberGenerator::SETTING_KEY, (string) ((int) $data['nextInvoiceNumber'] - 1));
        $this->invoiceNumberSaved = true;
    }

    public function render()
    {
        return view('livewire.settings.numbering');
    }
}
