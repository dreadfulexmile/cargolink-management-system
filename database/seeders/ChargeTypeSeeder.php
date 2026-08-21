<?php

namespace Database\Seeders;

use App\Models\ChargeType;
use Illuminate\Database\Seeder;

class ChargeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $disbursements = [
            'D/O & A.W.B', 'Custom Duty', 'S.L.P.A', 'Custom O.T', 'R.C.T/Gryline',
            'Yard O.T', 'Weight Bridge', 'Quarantine', 'Pyto', 'CO', 'SLS',
            'Custom Penalty', 'Custom Computer Fee',
        ];

        $services = [
            'Documentation', 'Handling', 'Agency Fee', 'Transport',
            'Custom Examination Expenses', 'Valuation Department',
            'R.C.T/Gryline Handling', 'Labour & Fork Lift', 'Transport Demurrages',
            'Officer/Doctor Transport', 'Additional Expenses for Custom',
        ];

        $sortOrder = 0;

        foreach ($disbursements as $name) {
            ChargeType::firstOrCreate(
                ['name' => $name, 'kind' => 'disbursement'],
                ['sort_order' => $sortOrder]
            );
            $sortOrder++;
        }

        foreach ($services as $name) {
            ChargeType::firstOrCreate(
                ['name' => $name, 'kind' => 'service'],
                ['sort_order' => $sortOrder]
            );
            $sortOrder++;
        }
    }
}
