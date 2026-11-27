<?php

namespace App\Support;

class BrandColor
{
    /**
     * Primary palette calibrated for StoreVelocity.
     * Shade 600 aligns precisely with the landing page and logo teal (#0f766e).
     * Shade 500 is calibrated so Filament selects:
     * - Background: shade 600 (#0f766e)
     * - Hover background: shade 500
     * - Text: white (#ffffff)
     */
    public const TEAL = [
        50 => 'oklch(0.977 0.014 186.391)',
        100 => 'oklch(0.950 0.033 186.391)',
        200 => 'oklch(0.905 0.063 186.391)',
        300 => 'oklch(0.840 0.106 186.391)',
        400 => 'oklch(0.753 0.150 186.391)',
        500 => 'oklch(0.550 0.120 186.391)',
        600 => 'oklch(0.5149 0.1494 186.391)', // Exact #0f766e
        700 => 'oklch(0.4461 0.1233 186.391)',
        800 => 'oklch(0.3945 0.0996 186.391)',
        900 => 'oklch(0.2778 0.0713 186.391)',
        950 => 'oklch(0.1800 0.0500 186.391)',
    ];
}
