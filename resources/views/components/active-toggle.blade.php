@props([
    'active' => false,
    'method' => 'toggleActive',
    'params' => [],
    'label' => '',
    'entity' => 'record',
])

<button type="button" x-on:click="$dispatch('confirm-open', @js([
        'title' => ($active ? 'Deactivate' : 'Activate').' '.$entity,
        'message' => ($active ? 'Deactivate' : 'Activate').' '.$label.'?',
        'method' => $method,
        'params' => $params,
        'danger' => $active,
        'confirmLabel' => $active ? 'Deactivate' : 'Activate',
    ]))"
    role="switch" aria-checked="{{ $active ? 'true' : 'false' }}"
    title="{{ $active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}"
    {{ $attributes->merge(['class' => ($active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-slate-600').' relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors']) }}>
    <span class="{{ $active ? 'translate-x-5' : 'translate-x-1' }} inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"></span>
</button>
