<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $invoice->load(['job', 'customer', 'lines', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        $filename = str_replace('/', '-', $invoice->invoice_no);

        return $pdf->stream("{$filename}.pdf");
    }
}
