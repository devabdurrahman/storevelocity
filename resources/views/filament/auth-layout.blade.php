<x-filament-panels::layout.base :livewire="$livewire">
    <style>
        .auth-shell { min-height: 100vh; display: grid; grid-template-columns: minmax(0, 1fr); background: #fff; color: #111827; }
        .auth-form-side { display: flex; flex-direction: column; justify-content: center; padding: 2rem 1.5rem; }
        .auth-form-inner { width: 100%; max-width: 26rem; margin: 0 auto; }

        /* Filament centres its logo, heading and subheading. Here everything sits on the left. */
        .auth-form-inner header { align-items: flex-start; text-align: left; }
        .auth-form-inner header h1,
        .auth-form-inner header p { text-align: left; }
        .auth-form-inner header h1 { font-weight: 800; }
        .auth-form-inner header p { line-height: 1.6; }

        .auth-link { color: #0f766e; font-weight: 600; text-decoration: none; }
        .auth-link:hover { text-decoration: underline; }

        /* Below the form */
        .auth-after { margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #e5e7eb; text-align: center; font-size: .9rem; color: #4b5563; }
        .auth-after p { margin: 0; }
        .auth-after p + p { margin-top: .75rem; font-size: .825rem; }
        .auth-after .auth-muted { color: #6b7280; text-decoration: underline; }

        .auth-promo { display: none; }

        @media (min-width: 1024px) {
            .auth-shell { grid-template-columns: minmax(0, 5fr) minmax(0, 6fr); gap: 1rem; padding: 1rem; }
            .auth-form-side { padding: 2rem 3rem; }
            .auth-promo {
                display: flex; flex-direction: column; justify-content: center; align-items: center;
                background: #0a3d3a; color: #fff; border-radius: 1.5rem; padding: 3rem 2.5rem;
            }
        }

        .auth-promo-inner { width: 100%; max-width: 25rem; }

        /* The two cards */
        .av-stage { position: relative; width: 100%; height: 21rem; margin-bottom: 2.75rem; }
        .av-card { background: #fff; color: #111827; border-radius: 1rem; box-shadow: 0 20px 40px -12px rgba(0, 0, 0, .4); }
        .av-a { position: absolute; left: 0; top: 0; width: 19.5rem; padding: 1rem 1.15rem 1.1rem; }
        .av-b { position: absolute; right: 0; bottom: 0; width: 15rem; padding: 1rem 1.15rem; }
        .av-chip {
            position: absolute; left: 1.5rem; bottom: .25rem; display: flex; align-items: center; gap: .6rem;
            background: #fff; color: #111827; border-radius: .9rem; padding: .65rem .9rem; box-shadow: 0 14px 30px -10px rgba(0, 0, 0, .4);
        }
        .av-chip i { width: .55rem; height: .55rem; border-radius: 999px; background: #f6b73c; display: block; }
        .av-chip small { display: block; font-size: .65rem; color: #6b7280; }
        .av-chip b { font-size: .95rem; }

        .av-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .6rem; }
        .av-head b { font-size: .85rem; }
        .av-live { background: #f6b73c; color: #2b1d00; border-radius: 999px; padding: .15rem .6rem; font-size: .65rem; font-weight: 700; }
        .av-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .55rem 0; border-top: 1px solid #f1f5f9; }
        .av-row b { display: block; font-size: .8rem; }
        .av-row small { font-size: .7rem; color: #6b7280; }
        .av-pill { border-radius: 999px; padding: .2rem .6rem; font-size: .65rem; font-weight: 700; white-space: nowrap; }
        .av-new { background: #f6b73c; color: #2b1d00; }
        .av-ok { background: #ccfbf1; color: #134e4a; }
        .av-ship { background: #e0f2fe; color: #075985; }

        .av-code { margin: .55rem 0 .8rem; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .95rem; font-weight: 700; }
        .av-steps { display: flex; justify-content: space-between; }
        .av-step { flex: 1; text-align: center; font-size: .62rem; color: #6b7280; }
        .av-dot { display: flex; align-items: center; justify-content: center; width: 1.35rem; height: 1.35rem; margin: 0 auto .3rem; border-radius: 999px; border: 1.5px solid #d1d5db; font-size: .65rem; font-weight: 700; }
        .av-done .av-dot { background: #0f766e; border-color: #0f766e; color: #fff; }
        .av-done { color: #111827; }

        .auth-promo h2 { margin: 0 0 1.1rem; font-size: 1.65rem; font-weight: 800; line-height: 1.3; }
        .auth-points { list-style: none; margin: 0; padding: 0; display: grid; gap: .8rem; }
        .auth-points li { display: flex; gap: .75rem; line-height: 1.5; font-size: .95rem; color: #cfe8e5; }
        .auth-points svg { width: 1.2rem; height: 1.2rem; flex-shrink: 0; margin-top: 2px; color: #f6b73c; }
        .auth-trial {
            display: inline-block; margin-top: 1.5rem; border: 1px solid #f6b73c; color: #f6b73c;
            border-radius: 999px; padding: .35rem .9rem; font-size: .8rem; font-weight: 600;
        }
    </style>

    @php
        $check = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 10.5 4 4 8-9"/></svg>';
    @endphp

    <div class="auth-shell">
        <main class="auth-form-side">
            <div class="auth-form-inner">
                {{ $slot }}

                <div class="auth-after">
                    @if ($livewire instanceof \App\Filament\Pages\Auth\Login)
                        <p>New to {{ config('app.name') }}? <a class="auth-link" href="{{ filament()->getRegistrationUrl() }}">Start your free trial</a></p>
                    @elseif ($livewire instanceof \App\Filament\Pages\Auth\Register)
                        <p>Already have a store? <a class="auth-link" href="{{ filament()->getLoginUrl() }}">Log in</a></p>
                    @endif

                    <p><a class="auth-muted" href="{{ route('landing') }}">Back to {{ config('app.name') }}</a></p>
                </div>
            </div>
        </main>

        <aside class="auth-promo">
            <div class="auth-promo-inner">
                <div class="av-stage" aria-hidden="true">
                    <div class="av-card av-a">
                        <div class="av-head"><b>Orders today</b><span class="av-live">Live</span></div>

                        <div class="av-row">
                            <div><b>#12 Rahima</b><small>৳1,260</small></div>
                            <span class="av-pill av-new">New order</span>
                        </div>
                        <div class="av-row">
                            <div><b>#11 Karim</b><small>৳2,450</small></div>
                            <span class="av-pill av-ok">Confirmed</span>
                        </div>
                        <div class="av-row">
                            <div><b>#10 Nasrin</b><small>৳890</small></div>
                            <span class="av-pill av-ship">With courier</span>
                        </div>
                    </div>

                    <div class="av-chip">
                        <i></i>
                        <div><small>Sales this month</small><b>৳48,200</b></div>
                    </div>

                    <div class="av-card av-b">
                        <div class="av-head"><b>Parcel booked</b><span class="av-pill av-ship">Steadfast</span></div>
                        <p class="av-code">SF48K2M9QX</p>

                        <div class="av-steps">
                            <div class="av-step av-done"><span class="av-dot">✓</span>Booked</div>
                            <div class="av-step av-done"><span class="av-dot">✓</span>On the way</div>
                            <div class="av-step"><span class="av-dot"></span>Delivered</div>
                        </div>
                    </div>
                </div>

                <h2>Customers order. You pack. Steadfast delivers.</h2>

                <ul class="auth-points">
                    <li>{!! $check !!}<span>Share one link, and customers can order at any hour</span></li>
                    <li>{!! $check !!}<span>Confirm an order, book the parcel and follow it from the same page</span></li>
                    <li>{!! $check !!}<span>Blocked numbers and order history help you spot fake orders</span></li>
                </ul>

                <span class="auth-trial">Free for {{ config('plans.trial_days') }} days, then ৳{{ config('plans.price') }} a month</span>
            </div>
        </aside>
    </div>
</x-filament-panels::layout.base>