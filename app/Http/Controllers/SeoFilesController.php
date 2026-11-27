<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeoFilesController extends Controller
{
    public function robots(Request $request)
    {
        $seo = $request->attributes->get('seo');

        $lines = ['User-agent: *'];

        if ($seo->indexable()) {
            array_push(
                $lines,
                'Disallow: /cart',
                'Disallow: /checkout',
                'Disallow: /order/',
                'Disallow: /pay/',
                'Allow: /',
                '',
                'Sitemap: ' . route('storefront.sitemap'),
            );
        } else {
            // The owner hasn't switched search engines on yet
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(Request $request)
    {
        $store = $request->attributes->get('store');
        $theme = $request->attributes->get('theme');
        $seo = $request->attributes->get('seo');

        abort_unless($seo->indexable(), 404);

        $xml = Cache::remember("sitemap:{$store->id}", 600, function () use ($store, $theme) {
            $urls = [
                [route('storefront.index'), $store->updated_at],
                [route('storefront.products'), null],
            ];

            foreach ($theme->categories() as $category) {
                $urls[] = [route('storefront.category', ['slug' => $category->slug]), $category->updated_at];
            }

            $store->visibleProducts()->orderBy('id')->limit(5000)->get(['id', 'name', 'updated_at'])
                ->each(function ($product) use (&$urls) {
                    $urls[] = [$product->storefrontUrl(), $product->updated_at];
                });

            $body = '';

            foreach ($urls as [$loc, $modified]) {
                $body .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>'
                    . ($modified ? '<lastmod>' . $modified->toAtomString() . '</lastmod>' : '')
                    . "</url>\n";
            }

            return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
                . $body
                . '</urlset>' . "\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}