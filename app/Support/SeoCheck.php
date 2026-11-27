<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/** The "is my keyword in the right places" list shown in the admin. Nothing here reaches a public page. */
class SeoCheck
{
    /**
     * @param  array<string, string|bool|null>  $places  where it should appear: text to look in, or true/false
     */
    public static function summary(?string $keyword, array $places): HtmlString|string
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return 'Optional. The main phrase a customer would type into Google to find this, for example "cotton panjabi". It only powers the checklist below. Nobody sees it, and Google does not read it.';
        }

        $lines = [];

        foreach ($places as $label => $where) {
            $found = is_bool($where)
                ? $where
                : ($where !== null && mb_stripos(strip_tags((string) $where), $keyword) !== false);

            $mark = $found
                ? '<span style="color:#15803d;font-weight:700">✓</span>'
                : '<span style="color:#b45309;font-weight:700">✗</span>';

            $lines[] = $mark . ' ' . e($label);
        }

        $lines[] = '<span style="color:#6b7280">Write naturally for people. Using the same phrase many times does not help.</span>';

        return new HtmlString(implode('<br>', $lines));
    }
}