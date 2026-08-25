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
        .total-row td { border-top: 2px solid #1f2937; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Cargo Link Custom Brokers (Pvt) Ltd</h1>
    <div class="muted">Annual Report &mdash; {{ $periodLabel }}</div>

    <table>
        <tr>
            <th>Month</th>
            <th class="right">Revenue</th>
            <th class="right">Cost of Services</th>
            <th class="right">Gross Profit</th>
            <th class="right">Operating Expenses</th>
            <th class="right">Operating Profit</th>
            <th class="right">Final Company Profit</th>
        </tr>
        @foreach ($months as $month)
            <tr>
                <td>{{ $month['label'] }}</td>
                <td class="right">{{ number_format($month['summary']['revenue'], 2) }}</td>
                <td class="right">{{ number_format($month['summary']['cost_of_services'], 2) }}</td>
                <td class="right">{{ number_format($month['summary']['gross_profit'], 2) }}</td>
                <td class="right">{{ number_format($month['summary']['operating_expenses'], 2) }}</td>
                <td class="right">{{ number_format($month['summary']['operating_profit'], 2) }}</td>
                <td class="right">{{ number_format($month['summary']['final_company_profit'], 2) }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td>Total</td>
            <td class="right">{{ number_format($summary['revenue'], 2) }}</td>
            <td class="right">{{ number_format($summary['cost_of_services'], 2) }}</td>
            <td class="right">{{ number_format($summary['gross_profit'], 2) }}</td>
            <td class="right">{{ number_format($summary['operating_expenses'], 2) }}</td>
            <td class="right">{{ number_format($summary['operating_profit'], 2) }}</td>
            <td class="right">{{ number_format($summary['final_company_profit'], 2) }}</td>
        </tr>
    </table>

    <table>
        <tr><th>Line</th><th class="right">LKR</th></tr>
        <tr><td>Lease Payments</td><td class="right">{{ number_format($summary['lease_payments'], 2) }}</td></tr>
        <tr><td>Creditor Repayments</td><td class="right">{{ number_format($summary['creditor_repayments'], 2) }}</td></tr>
        <tr><td>Profit After Leases</td><td class="right">{{ number_format($summary['profit_after_leases'], 2) }}</td></tr>
        <tr><td>Total Debt (as of today)</td><td class="right">{{ number_format($totalDebt, 2) }}</td></tr>
    </table>

    <table>
        <tr><th>Jobs ({{ $periodLabel }})</th><th class="right">Count</th></tr>
        <tr><td>Sea</td><td class="right">{{ $jobCounts['sea'] }}</td></tr>
        <tr><td>Air</td><td class="right">{{ $jobCounts['air'] }}</td></tr>
        <tr><td>Import</td><td class="right">{{ $jobCounts['import'] }}</td></tr>
        <tr><td>Export</td><td class="right">{{ $jobCounts['export'] }}</td></tr>
        <tr><td>Active Customers</td><td class="right">{{ $jobCounts['active_customers'] }}</td></tr>
    </table>
</body>
</html>
