<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Financial Expenses' => ['Bank Charges', 'Loan Interest', 'Overdraft Interest'],
            'Legal Charges' => ['Legal Charges'],
            'Marketing Expenses' => ['Business Promotion', 'Donation', 'Sales Commission', 'Travelling Expenses'],
            'Overhead Expenses' => [
                'Fuel', 'Lorry Expenses', 'Office Equipment Maintenance', 'Office Rent',
                'Office Service Charge', 'Subscription Fee', 'Telephone (SLT)', 'Water', 'Welfare',
            ],
            'Salary Expenses' => ['Director Salary', 'EPF', 'ETF', 'Staff Salary', 'Staff Welfare'],
            'Secretarial Expenses' => ['Secretarial Expenses'],
            'Vehicle Cost' => ['Vehicle Maintenance'],
        ];

        foreach ($categories as $group => $names) {
            foreach ($names as $name) {
                ExpenseCategory::create([
                    'group' => $group,
                    'name' => $name,
                ]);
            }
        }
    }
}
