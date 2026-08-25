@props(['name'])

<svg {{ $attributes->merge(['class' => 'w-5 h-5', 'fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'stroke-width' => '1.5']) }}>
    @switch($name)
        @case('dashboard')
            <rect x="3" y="3" width="8" height="8" rx="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <rect x="13" y="3" width="8" height="5" rx="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <rect x="13" y="10" width="8" height="11" rx="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <rect x="3" y="13" width="8" height="8" rx="1.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('customers')
            <circle cx="12" cy="8" r="3.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M5 20c0.5-4 3-6 7-6s6.5 2 7 6" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('jobs')
            <polyline points="3,7 12,3 21,7 21,17 12,21 3,17 3,7" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="3" y1="7" x2="12" y2="11" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="21" y1="7" x2="12" y2="11" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="12" y1="11" x2="12" y2="21" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('invoices')
            <rect x="5" y="3" width="14" height="18" rx="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="8" x2="16" y2="8" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="12" x2="16" y2="12" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="16" x2="13" y2="16" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('expenses')
            <rect x="3" y="7" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M3 10h18" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="17" cy="14" r="1" fill="currentColor" stroke="none" />
            @break

        @case('vehicles')
            <path d="M4 16V11l2-4h10l3 4v5" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="4" y1="16" x2="20" y2="16" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="7.5" cy="16.5" r="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="16.5" cy="16.5" r="1.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('lorries')
            <rect x="2" y="7" width="12" height="9" rx="1" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M14 10h4l3 3v3h-7z" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="6.5" cy="18" r="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="17" cy="18" r="1.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('reports')
            <rect x="4" y="12" width="3" height="8" stroke-linecap="round" stroke-linejoin="round" />
            <rect x="10.5" y="7" width="3" height="13" stroke-linecap="round" stroke-linejoin="round" />
            <rect x="17" y="3" width="3" height="17" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('director')
            <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M6.5 18c0.5-2.5 2.7-4 5.5-4s5 1.5 5.5 4" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('creditors')
            <line x1="12" y1="3" x2="12" y2="20" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="4" y1="7" x2="20" y2="7" stroke-linecap="round" stroke-linejoin="round" />
            <polyline points="4,7 4,11 8,11 8,7" stroke-linecap="round" stroke-linejoin="round" />
            <polyline points="16,7 16,11 20,11 20,7" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="20" x2="16" y2="20" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('revenue')
            <rect x="2" y="6" width="20" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="5.5" y1="9.5" x2="5.5" y2="9.5" stroke-linecap="round" />
            <line x1="18.5" y1="14.5" x2="18.5" y2="14.5" stroke-linecap="round" />
            @break

        @case('cost')
            <rect x="4" y="3" width="16" height="18" rx="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="8" x2="16" y2="8" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="12" x2="16" y2="12" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="16" x2="13" y2="16" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('profit')
            <polyline points="3,17 9,11 13,15 21,7" stroke-linecap="round" stroke-linejoin="round" />
            <polyline points="15,7 21,7 21,13" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="12" y1="7" x2="12" y2="12" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="12" y1="12" x2="15.5" y2="14" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('users')
            <circle cx="9" cy="8" r="3.25" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M3.5 20c0.5-4 2.7-6 5.5-6s5 2 5.5 6" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M15 8.5c1.4 0.2 2.5 1.3 2.5 3s-1.1 2.8-2.5 3" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M15.5 14.2c2.3 0.4 4 2.2 4.5 5.8" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('settings')
            <circle cx="12" cy="12" r="6.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="12" r="2.5" stroke-linecap="round" stroke-linejoin="round" />
            @for ($i = 0; $i < 8; $i++)
                <rect x="10.8" y="2" width="2.4" height="2.6" rx="0.6" transform="rotate({{ $i * 45 }} 12 12)" stroke-linecap="round" stroke-linejoin="round" />
            @endfor
            @break

        @case('hash')
            <line x1="9" y1="4" x2="7" y2="20" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="17" y1="4" x2="15" y2="20" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="4.5" y1="9.5" x2="20" y2="9.5" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="4" y1="14.5" x2="19.5" y2="14.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('key')
            <circle cx="8" cy="8" r="4" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="12" x2="8" y2="20" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="15.5" x2="11" y2="15.5" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="8" y1="18.5" x2="10.5" y2="18.5" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('warning')
            <polygon points="12,3 21,19 3,19" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="12" y1="9" x2="12" y2="13.5" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="12" cy="16.3" r="0.9" fill="currentColor" stroke="none" />
            @break

        @case('menu')
            <line x1="4" y1="6" x2="20" y2="6" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="4" y1="12" x2="20" y2="12" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="4" y1="18" x2="20" y2="18" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('close')
            <line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('chevron-down')
            <polyline points="6,9 12,15 18,9" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('logout')
            <path d="M15 17l5-5-5-5" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="20" y1="12" x2="9" y2="12" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('edit')
            <path d="M12 20h9" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" stroke-linecap="round" stroke-linejoin="round" />
            @break

        @case('delete')
            <path d="M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M6 7l1 13a2 2 0 002 2h6a2 2 0 002-2l1-13" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="10" y1="11" x2="10" y2="17" stroke-linecap="round" stroke-linejoin="round" />
            <line x1="14" y1="11" x2="14" y2="17" stroke-linecap="round" stroke-linejoin="round" />
            @break
    @endswitch
</svg>
