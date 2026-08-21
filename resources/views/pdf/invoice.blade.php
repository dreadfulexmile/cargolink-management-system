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
        .totals td { border: none; padding: 4px 8px; }
        .totals .label { text-align: right; color: #6b7280; }
        .right { text-align: right; }
        .header-grid { width: 100%; margin-top: 20px; }
        .header-grid td { border: none; padding: 2px 8px; vertical-align: top; }
        .section-title { margin-top: 16px; font-weight: bold; }
        .terms { margin-top: 30px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>Cargo Link Custom Brokers (Pvt) Ltd</h1>
    <div class="muted">Customs House Agent &amp; Freight Forwarder, Port of Colombo, Sri Lanka</div>

    <table class="header-grid">
        <tr>
            <td><strong>Invoice No:</strong> {{ $invoice->invoice_no }}</td>
            <td><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong> {{ $invoice->customer->name }}</td>
            <td><strong>Job No:</strong> {{ $invoice->job->job_no }}</td>
        </tr>
        <tr>
            <td><strong>CusDec No:</strong> {{ $invoice->job->cusdec_no ?: '-' }}</td>
            <td><strong>HBL No:</strong> {{ $invoice->job->hbl_no ?: '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr><th>Description</th><th class="right">Amount (LKR)</th></tr>
        </thead>
        <tbody>
            @foreach ($invoice->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="right">{{ number_format($line->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($invoice->payments->isNotEmpty())
        <div class="section-title">Payments Received</div>
        <table>
            <thead>
                <tr><th>Date</th><th>Method</th><th>Reference</th><th class="right">Amount (LKR)</th></tr>
            </thead>
            <tbody>
                @foreach ($invoice->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_on->format('Y-m-d') }}</td>
                        <td>{{ ucfirst($payment->method) }}</td>
                        <td>{{ $payment->reference ?: '-' }}</td>
                        <td class="right">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="totals">
        <tr><td colspan="1"></td><td class="label">Subtotal</td><td class="right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td colspan="1"></td><td class="label">Less: Advance</td><td class="right">{{ number_format($invoice->advance_total, 2) }}</td></tr>
        <tr><td colspan="1"></td><td class="label">Less: Payments Received</td><td class="right">{{ number_format($invoice->paymentsTotal(), 2) }}</td></tr>
        <tr><td colspan="1"></td><td class="label"><strong>Balance Due</strong></td><td class="right"><strong>{{ number_format($invoice->balance_due, 2) }}</strong></td></tr>
    </table>

    <div class="terms">
        Standard Terms: Payment due within the customer's agreed credit period from invoice date.
        Disbursements above are recovered at cost on the customer's behalf; service charges represent
        Cargo Link's fees for handling this shipment.
    </div>
</body>
</html>
