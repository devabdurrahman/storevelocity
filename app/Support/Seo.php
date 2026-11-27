<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Str;

/**
 * Decides what search engines and social networks see for one store.
 * Every value from the owner is re-validated here before it reaches a page.
 */
class Seo
{
    public function __construct(private Store $store, private Theme $theme)
    {
    }

    private function settings(): array
    {
        return (array) ($this->store->seo ?? []);
    }

    public function indexable(): bool
    {
        return (bool) ($this->settings()['indexable'] ?? false);
    }

    /** The store-wide switch always wins over a page's own wish. */
    public function robots(?string $pageRobots = null): string
    {
        if (! $this->indexable()) {
            return 'noindex, nofollow';
        }

        return $pageRobots ?: 'index, follow';
    }

    public function homeTitle(): string
    {
        $title = trim((string) ($this->settings()['title'] ?? ''));

        return $title !== '' ? mb_substr($title, 0, 70) : $this->store->name;
    }

    public function homeDescription(): string
    {
        $text = trim((string) ($this->settings()['description'] ?? ''));

        if ($text !== '') {
            return mb_substr($text, 0, 170);
        }

        return $this->theme->heroSubtitle() ?: 'Shop online at ' . $this->store->name . '.';
    }

    /** The share image: the owner's choice (from their own folder only), else the logo. */
    public function defaultImageUrl(): ?string
    {
        $path = $this->settings()['image'] ?? null;
        $prefix = 'stores/' . $this->store->id . '/';

        if (! is_string($path) || ! str_starts_with($path, $prefix) || str_contains($path, '..')) {
            return $this->theme->logoUrl();
        }

        return asset('storage/' . $path);
    }

    public function googleVerification(): ?string
    {
        return $this->token('google_verification');
    }

    public function bingVerification(): ?string
    {
        return $this->token('bing_verification');
    }

    private function token(string $key): ?string
    {
        $value = $this->settings()[$key] ?? null;

        return is_string($value) && preg_match('/^[A-Za-z0-9_\-]{10,100}$/', $value) ? $value : null;
    }

    private function withSite(string $title): string
    {
        $title = trim($title);

        return Str::contains(mb_strtolower($title), mb_strtolower($this->store->name))
            ? $title
            : $title . ' | ' . $this->store->name;
    }

    public function home(): array
    {
        return [
            'title' => $this->homeTitle(),
            'description' => $this->homeDescription(),
            'canonical' => route('storefront.index'),
            'image' => $this->defaultImageUrl(),
            'type' => 'website',
            'jsonld' => [$this->organization(), $this->website()],
        ];
    }

    public function products(int $page = 1, string $search = '', bool $sorted = false): array
    {
        $title = $search !== '' ? 'Search results' : 'All products';

        if ($page > 1) {
            $title .= ' - Page ' . $page;
        }

        return [
            'title' => $this->withSite($title),
            'description' => 'Browse all products at ' . $this->store->name . '.',
            // Search results are never listed, and everything points at the clean address
            'canonical' => $page > 1 ? route('storefront.products', ['page' => $page]) : route('storefront.products'),
            'robots' => ($search !== '' || $sorted) ? 'noindex, follow' : null,
            'image' => $this->defaultImageUrl(),
            'type' => 'website',
            'jsonld' => [],
        ];
    }

    public function category(Category $category, int $page = 1, bool $sorted = false): array
    {
        $title = trim((string) $category->seo_title) ?: $category->name;

        if ($page > 1) {
            $title .= ' - Page ' . $page;
        }

        $description = trim((string) $category->seo_description)
            ?: 'Shop ' . $category->name . ' at ' . $this->store->name . '.';

        $url = route('storefront.category', ['slug' => $category->slug] + ($page > 1 ? ['page' => $page] : []));

        return [
            'title' => $this->withSite($title),
            'description' => mb_substr($description, 0, 170),
            'canonical' => $url,
            'robots' => $sorted ? 'noindex, follow' : null,
            'image' => $category->coverUrl() ?: $this->defaultImageUrl(),
            'type' => 'website',
            'jsonld' => [
                $this->breadcrumbs([
                    ['Home', route('storefront.index')],
                    [$category->name, route('storefront.category', ['slug' => $category->slug])],
                ]),
            ],
        ];
    }

    public function product(Product $product): array
    {
        $product->loadMissing('category', 'variants');

        $url = $product->storefrontUrl();
        $images = $product->imageUrls();
        $plain = $this->plainText($product);

        $title = trim((string) $product->seo_title) ?: $product->name;
        $description = trim((string) $product->seo_description) ?: $plain;

        $trail = [['Home', route('storefront.index')]];

        if ($product->category) {
            $trail[] = [$product->category->name, route('storefront.category', ['slug' => $product->category->slug])];
        }

        $trail[] = [$product->name, $url];

        return [
            'title' => $this->withSite($title),
            'description' => mb_substr($description, 0, 170),
            'canonical' => $url,
            'image' => $images[0] ?? $this->defaultImageUrl(),
            'type' => 'product',
            'jsonld' => [
                $this->productSchema($product, $url, $images, $plain),
                $this->breadcrumbs($trail),
            ],
        ];
    }

    private function plainText(Product $product): string
    {
        $source = $product->short_description ?: ($product->descriptionHtml() ?: $product->name);
        $text = html_entity_decode(strip_tags((string) $source), ENT_QUOTES | ENT_HTML5);

        return Str::limit(trim((string) preg_replace('/\s+/u', ' ', $text)), 160, '');
    }

    private function productSchema(Product $product, string $url, array $images, string $plain): array
    {
        $variants = $product->has_variants ? $product->activeVariants() : collect();

        $prices = $variants->isNotEmpty()
            ? $variants->map(fn ($variant) => (float) ($variant->price ?? $product->price))
            : collect([(float) $product->price]);

        $availability = $product->isAvailable() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
        $format = fn (float $amount) => number_format($amount, 2, '.', '');

        if ($prices->count() > 1 && $prices->min() !== $prices->max()) {
            $offers = [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'BDT',
                'lowPrice' => $format($prices->min()),
                'highPrice' => $format($prices->max()),
                'offerCount' => $prices->count(),
                'availability' => $availability,
                'url' => $url,
            ];
        } else {
            $offers = [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => $format($prices->min()),
                'availability' => $availability,
                'url' => $url,
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $plain,
            'url' => $url,
            'offers' => $offers,
        ];

        if ($images) {
            $schema['image'] = $images;
        }

        if ($product->sku) {
            $schema['sku'] = $product->sku;
        }

        if ($product->category) {
            $schema['category'] = $product->category->name;
        }

        return $schema;
    }

    private function organization(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->store->name,
            'url' => route('storefront.index'),
        ];

        if ($logo = $this->theme->logoUrl()) {
            $data['logo'] = $logo;
        }

        if ($facebook = $this->theme->facebookUrl()) {
            $data['sameAs'] = [$facebook];
        }

        return $data;
    }

    private function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->store->name,
            'url' => route('storefront.index'),
        ];
    }

    private function breadcrumbs(array $trail): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()->map(fn ($crumb, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb[0],
                'item' => $crumb[1],
            ])->all(),
        ];
    }
}