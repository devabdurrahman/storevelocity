@props(['amount'])
{{ '৳' . number_format((float) $amount, fmod((float) $amount, 1) ? 2 : 0) }}