<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Cargo Link Custom Brokers (Pvt) Ltd</h1>
    <div class="muted">Management Report &mdash; {{ $month }}</div>

    <table>
        <tr><th>Line</th><th class="right">LKR</th></tr>
        <tr><td>Revenue</td><td class="right">{{ number_format($summary['revenue'], 2) }}</td></tr>
        <tr><td>Cost of Services (Disbursements)</td><td class="right">{{ number_format($summary['cost_of_services'], 2) }}</td></tr>
        <tr><td>Gross Profit</td><td class="right">{{ number_format($summary['gross_profit'], 2) }}</td></tr>
        <tr><td>Operating Expenses</td><td class="right">{{ number_format($summary['operating_expenses'], 2) }}</td></tr>
        <tr><td>Operating Profit</td><td class="right">{{ number_format($summary['operating_profit'], 2) }}</td></tr>
        <tr><td>Lease Payments</td><td class="right">{{ number_format($summary['lease_payments'], 2) }}</td></tr>
        <tr><td>Creditor Repayments</td><td class="right">{{ number_format($summary['creditor_repayments'], 2) }}</td></tr>
        <tr><td>Profit After Leases</td><td class="right">{{ number_format($summary['profit_after_leases'], 2) }}</td></tr>
        <tr><td><strong>Final Company Profit</strong></td><td class="right"><strong>{{ number_format($summary['final_company_profit'], 2) }}</strong></td></tr>
        <tr><td>Total Debt</td><td class="right">{{ number_format($totalDebt, 2) }}</td></tr>
    </table>

    <table>
        <tr><th>Jobs</th><th class="right">Count</th></tr>
        <tr><td>Sea</td><td class="right">{{ $jobCounts['sea'] }}</td></tr>
        <tr><td>Air</td><td class="right">{{ $jobCounts['air'] }}</td></tr>
        <tr><td>Import</td><td class="right">{{ $jobCounts['import'] }}</td></tr>
        <tr><td>Export</td><td class="right">{{ $jobCounts['export'] }}</td></tr>
        <tr><td>Active Customers</td><td class="right">{{ $jobCounts['active_customers'] }}</td></tr>
    </table>
</body>
</html>
