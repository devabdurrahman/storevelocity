<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static string $layout = 'filament.auth-layout';

    public function getHeading(): string|Htmlable
    {
        return 'Welcome back';
    }

    public function getSubHeading(): string|Htmlable
    {
        return 'Log in to your store.';
    }
}