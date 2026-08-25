<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 15px; margin: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        .header-grid { margin-top: 20px; }
        .header-grid td { border: none; padding: 3px 8px; vertical-align: top; }
        .header-grid .label { color: #6b7280; width: 140px; }
        .section-title { margin-top: 20px; font-weight: bold; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .totals { margin-top: 12px; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .label { text-align: right; color: #6b7280; }
        .right { text-align: right; }
        .terms { margin-top: 30px; font-size: 10px; color: #6b7280; }
        .signature { margin-top: 50px; width: 100%; }
        .signature td { border: none; padding: 0 8px; }
        .signature .line { border-top: 1px solid #1f2937; padding-top: 4px; margin-top: 40px; display: block; }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h1>Cargo Link Custom Brokers (Pvt) Ltd</h1>
            <div class="muted">Customs House Agent &amp; Freight Forwarder, Port of Colombo, Sri Lanka</div>
        </div>
        <div style="text-align: right;">
            <h2>Lorry Hire Receipt</h2>
            <div class="muted">No: LR-{{ str_pad($hire->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="muted">Date: {{ $hire->hire_date->format('Y-m-d') }}</div>
        </div>
    </div>

    <table class="header-grid">
        <tr>
            <td class="label">Hired By</td>
            <td><strong>{{ $hire->hirer_name ?: '-' }}</strong></td>
            <td class="label">Lorry</td>
            <td><strong>{{ $hire->lorry->reg_no }}{{ $hire->lorry->name ? ' — '.$hire->lorry->name : '' }}</strong></td>
        </tr>
        <tr>
            <td class="label">From</td>
            <td>{{ $hire->from_location ?: '-' }}</td>
            <td class="label">To</td>
            <td>{{ $hire->to_location ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Start</td>
            <td>{{ $hire->started_at?->format('Y-m-d H:i') ?: '-' }}</td>
            <td class="label">End</td>
            <td>{{ $hire->ended_at?->format('Y-m-d H:i') ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Distance</td>
            <td colspan="3">{{ $hire->distance_km !== null ? number_format((float) $hire->distance_km, 2).' km' : '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Charges</div>
    <table class="totals">
        <tr><td></td><td class="label">Hire Amount</td><td class="right" style="width: 140px;">{{ number_format($hire->amount, 2) }}</td></tr>
        @if (bccomp((string) $hire->held_fee, '0', 2) > 0)
            <tr>
                <td></td>
                <td class="label">Held-up Fee ({{ rtrim(rtrim((string) $hire->held_hours, '0'), '.') }}h &times; {{ number_format((float) $hire->held_hourly_rate, 2) }})</td>
                <td class="right">{{ number_format($hire->held_fee, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td></td>
            <td class="label"><strong>Total (LKR)</strong></td>
            <td class="right"><strong>{{ number_format(bcadd((string) $hire->amount, (string) $hire->held_fee, 2), 2) }}</strong></td>
        </tr>
    </table>

    @if ($hire->notes)
        <div class="section-title">Notes</div>
        <div>{{ $hire->notes }}</div>
    @endif

    <table class="signature">
        <tr>
            <td style="width: 50%;"><span class="line">Received By</span></td>
            <td style="width: 50%;"><span class="line">For Cargo Link Custom Brokers (Pvt) Ltd</span></td>
        </tr>
    </table>

    <div class="terms">
        This receipt confirms the hire charges above for the trip described. Please retain for your records.
    </div>
</body>
</html>
