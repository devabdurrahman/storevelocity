<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function __invoke(Request $request)
    {
        // ?lang=en or ?lang=bn switches language; the choice is remembered
        $locale = $request->query('lang');

        if (! in_array($locale, ['bn', 'en'], true)) {
            $locale = session('locale', 'bn');
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        // Bangla pages show Bangla digits
        $digits = fn (int|string $number): string => $locale === 'bn'
            ? strtr((string) $number, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $number;

        return view('landing', [
            'registerUrl' => url('/admin/register'),
            'loginUrl' => url('/admin/login'),
            'planPrice' => (int) config('plans.price'),
            'planDays' => (int) config('plans.trial_days'),
            'priceText' => $digits(config('plans.price')),
            'daysText' => $digits(config('plans.trial_days')),
        ]);
    }
}