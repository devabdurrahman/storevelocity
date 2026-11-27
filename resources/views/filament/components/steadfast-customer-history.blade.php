@php
    $check = $record?->courierCheck();
@endphp

@if (! $check)
    <div style="display:flex; align-items:center; justify-content:space-between; border:1px solid #e2e8f0; background-color:#f8fafc; border-radius:0.75rem; padding:1rem; margin-top:0.25rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="display:flex; height:2.5rem; width:2.5rem; align-items:center; justify-content:center; border-radius:0.5rem; background-color:#f1f5f9; color:#64748b;">
                <svg style="height:1.5rem; width:1.5rem;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
            <div>
                <p style="margin:0; font-size:0.875rem; font-weight:600; color:#0f172a;">Not checked yet with Steadfast</p>
                <p style="margin:0.25rem 0 0 0; font-size:0.75rem; color:#64748b;">Click the <strong>"Check customer"</strong> button at the top of this page to fetch this customer's track record.</p>
            </div>
        </div>
    </div>
@else
    @php
        $ratio = $check->ratio();
        $level = $check->level();
        
        $badgeBg = match ($level) {
            'good' => '#ecfdf5',
            'medium' => '#fffbeb',
            'poor' => '#fff1f2',
            default => '#f1f5f9',
        };
        $badgeColor = match ($level) {
            'good' => '#047857',
            'medium' => '#b45309',
            'poor' => '#be123c',
            default => '#475569',
        };
        $badgeBorder = match ($level) {
            'good' => '#a7f3d0',
            'medium' => '#fde68a',
            'poor' => '#fecdd3',
            default => '#cbd5e1',
        };

        $riskLabel = match ($level) {
            'good' => 'Safe (Low Return Risk)',
            'medium' => 'Moderate Risk',
            'poor' => 'High Return / Cancellation Risk',
            default => 'Little History',
        };

        $ratioDisplay = $ratio !== null ? $ratio . '%' : 'N/A';
        $checkedAgo = $check->checked_at ? $check->checked_at->diffForHumans() : 'Recently';
    @endphp

    <div style="display:flex; flex-direction:column; gap:0.875rem; width:100%;">
        {{-- Metric Stat Cards Grid --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:0.75rem; width:100%;">
            <div style="border:1px solid #e2e8f0; background-color:#ffffff; border-radius:0.75rem; padding:0.875rem; box-shadow:0 1px 2px 0 rgba(0, 0, 0, 0.04);">
                <p style="margin:0; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">Delivery Rate</p>
                <p style="margin:0.35rem 0 0 0; font-size:1.5rem; font-weight:800; color:#0f172a; line-height:1.2;">{{ $ratioDisplay }}</p>
                <p style="margin:0.25rem 0 0 0; font-size:0.75rem; color:#64748b;">Overall success rate</p>
            </div>

            <div style="border:1px solid #e2e8f0; background-color:#ffffff; border-radius:0.75rem; padding:0.875rem; box-shadow:0 1px 2px 0 rgba(0, 0, 0, 0.04);">
                <p style="margin:0; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">Total Parcels</p>
                <p style="margin:0.35rem 0 0 0; font-size:1.5rem; font-weight:800; color:#0f172a; line-height:1.2;">{{ (int) $check->total }}</p>
                <p style="margin:0.25rem 0 0 0; font-size:0.75rem; color:#64748b;">Across all merchants</p>
            </div>

            <div style="border:1px solid #e2e8f0; background-color:#ffffff; border-radius:0.75rem; padding:0.875rem; box-shadow:0 1px 2px 0 rgba(0, 0, 0, 0.04);">
                <p style="margin:0; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">Delivered</p>
                <p style="margin:0.35rem 0 0 0; font-size:1.5rem; font-weight:800; color:#059669; line-height:1.2;">{{ (int) $check->success }}</p>
                <p style="margin:0.25rem 0 0 0; font-size:0.75rem; color:#64748b;">Completed deliveries</p>
            </div>

            <div style="border:1px solid #e2e8f0; background-color:#ffffff; border-radius:0.75rem; padding:0.875rem; box-shadow:0 1px 2px 0 rgba(0, 0, 0, 0.04);">
                <p style="margin:0; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">Cancelled / Returned</p>
                <p style="margin:0.35rem 0 0 0; font-size:1.5rem; font-weight:800; color:#e11d48; line-height:1.2;">{{ (int) $check->cancel }}</p>
                <p style="margin:0.25rem 0 0 0; font-size:0.75rem; color:#64748b;">Failed or returned</p>
            </div>
        </div>

        {{-- Assessment Footer Pill --}}
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.5rem; border:1px solid #e2e8f0; background-color:#f8fafc; border-radius:0.75rem; padding:0.75rem 1rem; font-size:0.8125rem;">
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <span style="font-weight:600; color:#334155;">Risk Assessment:</span>
                <span style="display:inline-flex; align-items:center; padding:0.2rem 0.65rem; border-radius:9999px; font-size:0.75rem; font-weight:700; border:1px solid {{ $badgeBorder }}; background-color:{{ $badgeBg }}; color:{{ $badgeColor }};">
                    {{ $riskLabel }}
                </span>
            </div>
            <div style="color:#64748b; font-size:0.75rem;">
                <span>Checked {{ $checkedAgo }}</span>
                <span style="margin:0 0.35rem;">•</span>
                <span>Click <strong>"Recheck customer"</strong> above to refresh</span>
            </div>
        </div>
    </div>
@endif
