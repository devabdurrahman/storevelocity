{{-- Inline styles on purpose: the admin panel doesn't load our Tailwind CSS --}}
<div style="display:flex; align-items:center; gap:0.5rem; font-weight:800; font-size:1.25rem; line-height:1;">
    <svg width="28" height="28" viewBox="0 0 64 64" aria-hidden="true">
        <rect width="64" height="64" rx="14" fill="#0f766e"/>
        <path d="M12 26 17 15h30l5 11z" fill="#fff"/>
        <rect x="16" y="28" width="32" height="20" rx="2" fill="#fff"/>
        <rect x="28" y="36" width="8" height="12" fill="#0f766e"/>
    </svg>
    <span>{{ config('app.name') }}@isset($suffix) <span style="font-weight:500; opacity:0.6;">{{ $suffix }}</span>@endisset</span>
</div>