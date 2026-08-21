@props(['amount', 'cents' => true, 'symbol' => true])
<span {{ $attributes->merge(['class' => 'whitespace-nowrap']) }}>{{ \App\Support\Money::format($amount, $cents, $symbol) }}</span>
