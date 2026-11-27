<?php

namespace App\Support;

use App\Models\Store;
use Illuminate\Support\Collection;
use App\Models\Media;

/**
 * Everything a storefront needs to know about its store's look.
 * Every value is re-validated here before it reaches a page,
 * even though the admin form already validates it.
 */
class Theme
{
    private ?Collection $categories = null;

    private function __construct(
        public readonly string $slug,
        private readonly array $settings,
        private readonly Store $store,
    ) {
    }

    public static function for(Store $store): self
    {
        // Only a slug from our own catalog is ever used to pick views
        $slug = array_key_exists((string) $store->theme, config('themes.catalog'))
            ? $store->theme
            : config('themes.default');

        return new self($slug, (array) ($store->theme_settings ?? []), $store);
    }

    public function layout(): string
    {
        $themed = "themes.{$this->slug}.layout";

        return view()->exists($themed) ? $themed : 'layouts.storefront';
    }

    /** A themed version of a page if one exists, otherwise the shared one. */
    public function view(string $name): string
    {
        $themed = "themes.{$this->slug}.{$name}";

        return view()->exists($themed) ? $themed : "storefront.{$name}";
    }

    public function brandColor(): string
    {
        $color = $this->settings['brand_color'] ?? null;

        return is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/', $color)
            ? strtolower($color)
            : '#0f766e';
    }

    /** White or dark text, whichever is easier to read on the brand colour. */
    // public function brandInk(): string
    // {
    //     [$r, $g, $b] = sscanf($this->brandColor(), '#%02x%02x%02x');

    //     $luminance = 0.2126 * $this->channel($r) + 0.7152 * $this->channel($g) + 0.0722 * $this->channel($b);

    //     // Mid-tone colors like green, emerald, teal, and blue look much cleaner and crisper with white text.
    //     // Dark text (#111827) is only used on truly bright backgrounds (e.g., pale yellows, creams, white).
    //     return $luminance < 0.45 ? '#ffffff' : '#111827';
    // }

    public function brandInk(): string
    {
        return $this->actionInk();
    }

    /** White or dark text, whichever is easier to read on this colour. */
    public function inkFor(string $hex): string
    {
        $luminance = $this->luminance($hex);

        $contrastWithWhite = 1.05 / ($luminance + 0.05);
        $contrastWithDark = ($luminance + 0.05) / (0.0093 + 0.05); // dark = #111827

        return $contrastWithWhite >= $contrastWithDark ? '#ffffff' : '#111827';
    }

    /** The colour itself when it reads well as text on a light background, otherwise near-black. */
    public function readableOnLight(string $hex): string
    {
        return 1.05 / ($this->luminance($hex) + 0.05) >= 4.5 ? $hex : '#111827';
    }

    /** How easy it is to read one colour on another. 4.5 or more is comfortable. */
    private function contrast(string $a, string $b): float
    {
        $first = $this->luminance($a);
        $second = $this->luminance($b);

        return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
    }

    /** The colour blended with white, for example 0.1 keeps 10% of the colour. */
    private function mixWithWhite(string $hex, float $share): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        $mix = fn (int $channel) => (int) round($channel * $share + 255 * (1 - $share));

        return sprintf('#%02x%02x%02x', $mix($r), $mix($g), $mix($b));
    }

    private function luminance(string $hex): float
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return 0.2126 * $this->channel($r) + 0.7152 * $this->channel($g) + 0.0722 * $this->channel($b);
    }

    /** auto, light or dark: what the owner chose for text on buttons. */
    public function brandTextMode(): string
    {
        $mode = $this->settings['brand_text'] ?? 'auto';

        return in_array($mode, ['auto', 'light', 'dark'], true) ? $mode : 'auto';
    }

    private ?array $brandAction = null;

    /** The brand colour as buttons use it, and the text colour that goes on top. */
    private function brandAction(): array
    {
        return $this->brandAction ??= $this->action($this->brandColor(), $this->brandTextMode());
    }

    public function actionColor(): string
    {
        return $this->brandAction()[0];
    }

    public function actionInk(): string
    {
        return $this->brandAction()[1];
    }

    /**
     * A colour for something that carries text, plus the text colour for it.
     * Mid-tone colours (lime, orange, sky blue) are deepened until white text reads well.
     *
     * @return array{0: string, 1: string}
     */
    private function action(string $hex, string $mode = 'auto'): array
    {
        if ($mode === 'light') {
            return [$hex, '#ffffff'];
        }

        if ($mode === 'dark') {
            return [$hex, '#111827'];
        }

        $whiteContrast = 1.05 / ($this->luminance($hex) + 0.05);

        if ($whiteContrast >= 4.5) {
            return [$hex, '#ffffff'];       // already dark enough
        }

        if ($whiteContrast < 2.0) {
            return [$hex, '#111827'];       // a light colour: keep it, use dark text
        }

        $deeper = $hex;

        for ($step = 1; $step <= 30; $step++) {
            $deeper = $this->darken($hex, $step * 0.03);

            if (1.05 / ($this->luminance($deeper) + 0.05) >= 4.5) {
                break;
            }
        }

        return [$deeper, '#ffffff'];
    }

    /** Mixes a colour with black. 0.1 makes it 10% darker. */
    private function darken(string $hex, float $amount): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        $keep = 1 - $amount;

        return sprintf('#%02x%02x%02x', (int) round($r * $keep), (int) round($g * $keep), (int) round($b * $keep));
    }

    private function channel(int $value): float
    {
        $c = $value / 255;

        return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    }

    /** Only files from THIS store's own folder are ever accepted. */
    private function ownImage(mixed $path): ?string
    {
        $prefix = 'stores/' . $this->store->id . '/';

        if (! is_string($path) || ! str_starts_with($path, $prefix) || str_contains($path, '..')) {
            return null;
        }

        return asset('storage/' . $path);
    }

    public function logoUrl(): ?string
    {
        return $this->ownImage($this->settings['logo'] ?? null);
    }

    public function heroEnabled(): bool
    {
        return (bool) ($this->settings['hero_enabled'] ?? true);
    }

    public function heroImageUrl(): ?string
    {
        return $this->ownImage($this->settings['hero_image'] ?? null);
    }

    public function heroTitle(): string
    {
        $text = trim((string) ($this->settings['hero_title'] ?? ''));

        return $text !== '' ? mb_substr($text, 0, 70) : 'Welcome to ' . $this->store->name;
    }

    public function heroSubtitle(): ?string
    {
        $text = trim((string) ($this->settings['hero_subtitle'] ?? ''));

        return $text !== '' ? mb_substr($text, 0, 160) : null;
    }

    public function heroButtonText(): string
    {
        $text = trim((string) ($this->settings['hero_button_text'] ?? ''));

        return $text !== '' ? mb_substr($text, 0, 24) : 'Shop all';
    }

    /** "All products" or one of THIS store's own categories. Never a free-text link. */
    public function heroButtonUrl(): string
    {
        $target = $this->settings['hero_button_target'] ?? 'all';

        if ($target !== 'all' && ctype_digit((string) $target)) {
            $category = $this->store->categories()->find((int) $target);

            if ($category) {
                return route('storefront.category', ['slug' => $category->slug]);
            }
        }

        return route('storefront.products');
    }

    /** picture, overlay, split, or text (no picture uploaded). */
    public function heroStyle(): string
    {
        if (($this->settings['hero_style'] ?? null) === 'slider') {
            // A slider with no valid pictures falls back to the plain text banner
            return $this->heroSlides() ? 'slider' : 'text';
        }

        if (! $this->heroImageUrl()) {
            return 'text';
        }

        $style = $this->settings['hero_style'] ?? 'picture';

        return in_array($style, ['picture', 'overlay', 'split'], true) ? $style : 'picture';
    }

    public function heroMobileImageUrl(): ?string
    {
        return $this->ownImage($this->settings['hero_image_mobile'] ?? null);
    }

    /** What a screen reader says about the banner picture. */
    public function heroAlt(): string
    {
        $alt = trim((string) ($this->settings['hero_alt'] ?? ''));

        if ($alt !== '') {
            return mb_substr($alt, 0, 125);
        }

        $title = trim((string) ($this->settings['hero_title'] ?? ''));

        return $title !== '' ? mb_substr($title, 0, 125) : $this->store->name . ' offers';
    }

    /** Width and height of the banner, so the page can reserve its space while it loads. */
    public function heroImageSize(): ?array
    {
        $path = $this->settings['hero_image'] ?? null;

        if (! is_string($path) || ! $this->heroImageUrl()) {
            return null;
        }

        $media = Media::where('store_id', $this->store->id)->where('path', $path)->first(['width', 'height']);

        return $media && $media->width && $media->height ? [(int) $media->width, (int) $media->height] : null;
    }

    public function showNewArrivals(): bool
    {
        return (bool) ($this->settings['show_new_arrivals'] ?? true);
    }

    public function featuredCategories(): Collection
    {
        return $this->store->categories()
            ->where('is_featured', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    public function announcement(): ?string
    {
        $text = trim((string) ($this->settings['announcement'] ?? ''));

        return $text !== '' ? mb_substr($text, 0, 120) : null;
    }

    public function footerAboutText(): ?string
    {
        $text = trim((string) ($this->settings['footer_about_text'] ?? ''));

        return $text !== '' ? mb_substr($text, 0, 220) : null;
    }

    public function whatsappUrl(): ?string
    {
        $number = $this->settings['whatsapp'] ?? null;

        if (! is_string($number) || ! preg_match('/^(?:\+?88)?(01[3-9]\d{8})$/', $number, $match)) {
            return null;
        }

        return 'https://wa.me/88' . $match[1];
    }

    public function facebookUrl(): ?string
    {
        $url = $this->settings['facebook_url'] ?? null;

        return is_string($url) && preg_match('#^https://(www\.|m\.)?facebook\.com/[A-Za-z0-9._\-/]+$#', $url)
            ? $url
            : null;
    }

    /** Categories that actually have products, for navigation. */
    public function categories(): Collection
    {
        return $this->categories ??= $this->store->categories()
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function logoSize(): string
    {
        $size = $this->settings['logo_size'] ?? 'medium';

        return in_array($size, ['small', 'medium', 'large'], true) ? $size : 'medium';
    }

    /** How many rows each shelf shows at each screen size. */
    public const HOME_ROWS = ['base' => 2, 'sm' => 2, 'md' => 2, 'lg' => 2];
    public const RELATED_ROWS = ['base' => 2, 'sm' => 2, 'md' => 1, 'lg' => 1];

    /** Columns of the product grid at each screen size. Must match the grid classes in product-grid.blade.php. */
    public function gridColumns(): array
    {
        return match ($this->slug) {
            'fresh' => ['base' => 2, 'sm' => 3, 'md' => 4, 'lg' => 5],
            default => ['base' => 2, 'sm' => 2, 'md' => 3, 'lg' => 4],
        };
    }

    /** How many products to load so that every screen size can fill its rows. */
    public function shelfLimit(array $rows): int
    {
        $most = 0;

        foreach ($this->gridColumns() as $point => $columns) {
            $most = max($most, $columns * ($rows[$point] ?? 2));
        }

        return $most;
    }

    private function cleanText(mixed $value, int $max): string
    {
        return mb_substr(trim((string) $value), 0, $max);
    }

    private function validColor(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : null;
    }

    /** Where a button may point: All products, or a category or product of THIS store only. */
    private function targetUrl(mixed $target): string
    {
        $target = (string) $target;

        if (preg_match('/^c:(\d+)$/', $target, $match)) {
            $category = $this->store->categories()->find((int) $match[1]);

            if ($category) {
                return route('storefront.category', ['slug' => $category->slug]);
            }
        }

        if (preg_match('/^p:(\d+)$/', $target, $match)) {
            $product = $this->store->visibleProducts()->find((int) $match[1]);

            if ($product) {
                return $product->storefrontUrl();
            }
        }

        return route('storefront.products');
    }

    /** Up to three promotions, each one checked before it reaches a page. */
    public function promos(): array
    {
        $items = $this->settings['promos'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        $promos = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! ($item['enabled'] ?? true)) {
                continue;
            }

            $title = $this->cleanText($item['title'] ?? '', 70);

            if ($title === '') {
                continue;
            }

            $rawColor = $this->validColor($item['color'] ?? null) ?? $this->brandColor();
            // $mode = in_array($item['text_color'] ?? 'auto', ['auto', 'light', 'dark'], true) ? $item['text_color'] : 'auto';

            $mode = $item['text_color'] ?? 'auto';

            if (! in_array($mode, ['auto', 'light', 'dark'], true)) {
                $mode = 'auto';
            }

            // The colour as a button or strip uses it, and its text colour
            [$color, $ink] = $this->action($rawColor, $mode);

            // The background the picture fades into: the owner's choice, else a soft tint of the colour
            $bg = $this->validColor($item['background'] ?? null) ?? $this->mixWithWhite($rawColor, 0.1);
            $bgIsDark = $this->inkFor($bg) === '#ffffff';

            $promos[] = [
                'title' => $title,
                'text' => $this->cleanText($item['text'] ?? '', 140),
                'button' => $this->cleanText($item['button_text'] ?? '', 24),
                'url' => $this->targetUrl($item['target'] ?? 'all'),
                'image' => $this->ownImage($item['image'] ?? null),

                // For the plain colour strip
                'color' => $color,
                'ink' => $ink,

                // For the banner with a picture
                'bg' => $bg,
                'bg_dark' => $bgIsDark,
                'headline' => $bgIsDark ? '#ffffff' : ($this->contrast($color, $bg) >= 4.5 ? $color : '#111827'),
                'subtext' => $bgIsDark ? '#e5e7eb' : '#374151',
                'button_bg' => $bgIsDark ? '#ffffff' : $color,
                'button_ink' => $bgIsDark ? '#111827' : $ink,
            ];

            if (count($promos) === 3) {
                break;
            }
        }

        return $promos;
    }

    public function faqTitle(): string
    {
        $title = $this->cleanText($this->settings['faq_title'] ?? '', 60);

        return $title !== '' ? $title : 'Questions and answers';
    }

    /** An optional short line under the FAQ title. */
    public function faqText(): ?string
    {
        $text = $this->cleanText($this->settings['faq_text'] ?? '', 140);

        return $text !== '' ? $text : null;
    }

    /** Questions that have both a question and an answer. */
    public function faqItems(): array
    {
        $items = $this->settings['faq'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(fn ($item) => [
                'q' => $this->cleanText($item['question'] ?? '', 120),
                'a' => $this->cleanText($item['answer'] ?? '', 600),
            ])
            ->filter(fn ($item) => $item['q'] !== '' && $item['a'] !== '')
            ->take(10)
            ->values()
            ->all();
    }

    /** Up to six slides, each one checked before it reaches a page. */
    public function heroSlides(): array
    {
        $items = $this->settings['hero_slides'] ?? [];

        if (! is_array($items)) {
            return [];
        }

        $slides = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $image = $this->ownImage($item['image'] ?? null);

            // Only files from THIS store's own folder are accepted
            if (! $image) {
                continue;
            }

            $mobile = $this->ownImage($item['mobile_image'] ?? null);
            $alt = $this->cleanText($item['alt'] ?? '', 125);
            $target = (string) ($item['target'] ?? 'all');

            $slides[] = [
                'image' => $image,
                'path' => $item['image'],
                'mobile' => $mobile,
                'mobile_path' => $mobile ? $item['mobile_image'] : null,
                'alt' => $alt !== '' ? $alt : $this->store->name . ' offer ' . (count($slides) + 1),
                'url' => $target === 'none' ? null : $this->targetUrl($target),
            ];

            if (count($slides) === 6) {
                break;
            }
        }

        return $slides;
    }

    private function imageSize(?string $path): ?array
    {
        if (! is_string($path)) {
            return null;
        }

        $media = Media::where('store_id', $this->store->id)->where('path', $path)->first(['width', 'height']);

        return $media && $media->width && $media->height ? [(int) $media->width, (int) $media->height] : null;
    }

    /**
     * The shape of the slider box, from the first slide, kept within sensible limits.
     *
     * @return array{desktop: string, mobile: string}
     */
    public function heroSliderRatios(): array
    {
        $first = $this->heroSlides()[0] ?? null;

        $size = $first ? $this->imageSize($first['path']) : null;
        $desktop = $size ? max(1.8, min(4.0, $size[0] / $size[1])) : 8 / 3;

        $mobileSize = $first && $first['mobile_path'] ? $this->imageSize($first['mobile_path']) : null;
        $mobile = $mobileSize ? max(0.6, min(2.0, $mobileSize[0] / $mobileSize[1])) : $desktop;

        return [
            'desktop' => number_format($desktop, 3, '.', '') . ' / 1',
            'mobile' => number_format($mobile, 3, '.', '') . ' / 1',
        ];
    }

    public function heroSliderAutoplay(): bool
    {
        return (bool) ($this->settings['hero_slider_autoplay'] ?? true);
    }

    public function heroSliderSeconds(): int
    {
        $seconds = (int) ($this->settings['hero_slider_seconds'] ?? 5);

        return in_array($seconds, [4, 5, 6, 8, 10], true) ? $seconds : 5;
    }

}