<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;

class Register extends BaseRegister
{
    protected static string $layout = 'filament.auth-layout';

    public function getHeading(): string|Htmlable
    {
        return 'Open your store';
    }

    public function getSubHeading(): string|Htmlable
    {
        return 'Set up your online store in a few minutes.';
    }
}