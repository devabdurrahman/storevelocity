<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Tracking;
use Illuminate\Http\Request;
use App\Support\ProductSort;
use App\Support\Throttle;

class StorefrontController extends Controller
{
    public function home(Request $request)
    {
        // Old search links that pointed at "/" keep working
        if ($request->filled('q')) {
            return redirect()->route('storefront.products', ['q' => $request->query('q')]);
        }

        $store = $request->attributes->get('store');
        $theme = $request->attributes->get('theme');
        $seo = $request->attributes->get('seo');

        $featuredCategories = $theme->featuredCategories();
        $shelfLimit = $theme->shelfLimit(\App\Support\Theme::HOME_ROWS);
        // $featuredProducts = $store->visibleProducts()->with('variants')->where('is_featured', true)->latest()->limit(8)->get();
        $featuredProducts = $store->visibleProducts()->with('variants')->where('is_featured', true)->latest()->limit($shelfLimit)->get();

        // Fall back to showing new arrivals if there is nothing else to show
        $showNew = $theme->showNewArrivals()
            || ($featuredProducts->isEmpty() && $featuredCategories->isEmpty() && ! $theme->heroEnabled());

        $newArrivals = $showNew
            // ? $store->visibleProducts()->with('variants')->whereNotIn('id', $featuredProducts->pluck('id'))->latest()->limit(8)->get()
            ? $store->visibleProducts()->with('variants')->whereNotIn('id', $featuredProducts->pluck('id'))->latest()->limit($shelfLimit)->get()
            : collect();

        return view($theme->view('home'), [
            'featuredProducts' => $featuredProducts,
            'featuredCategories' => $featuredCategories,
            'newArrivals' => $newArrivals,
            'seo' => $seo->home(),
        ]);
    }

    // public function index(Request $request)
    // {
    //     $store = $request->attributes->get('store');
    //     $theme = $request->attributes->get('theme');
    //     $seo = $request->attributes->get('seo');

    //     $search = mb_substr(trim((string) $request->query('q', '')), 0, 60);
    //     $page = max(1, (int) $request->query('page', 1));

    //     $products = $store->visibleProducts()
    //         ->with('variants')
    //         ->when($search !== '', function ($query) use ($search) {
    //             $like = '%' . addcslashes($search, '%_\\') . '%';

    //             $query->where(fn ($q) => $q
    //                 ->where('name', 'like', $like)
    //                 ->orWhere('sku', 'like', $like)
    //                 ->orWhere('short_description', 'like', $like));
    //         })
    //         ->latest()
    //         ->paginate(12)
    //         ->withQueryString();

    //     return view($theme->view('index'), [
    //         'products' => $products,
    //         'category' => null,
    //         'search' => $search,
    //         'seo' => $seo->products($page, $search),
    //     ]);
    // }

    public function index(Request $request)
    {
        $store = $request->attributes->get('store');
        $theme = $request->attributes->get('theme');
        $seo = $request->attributes->get('seo');

        $rawSearch = $request->query('q');
        $search = is_string($rawSearch) ? mb_substr(trim($rawSearch), 0, 60) : '';

        if ($search !== '' && ! \App\Support\Throttle::allow('search', 30)) {
            abort(429);
        }

        $rawSort = $request->query('sort');
        $sort = ProductSort::from(is_string($rawSort) ? $rawSort : null);

        $page = max(1, (int) $request->query('page', 1));

        $query = $store->visibleProducts()
            ->with('variants')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('short_description', 'like', $like)
                    ->orWhere('search_words', 'like', $like));
            });

        ProductSort::apply($query, $sort);

        $products = $query->paginate(12)->withQueryString();

        return view($theme->view('index'), [
            'products' => $products,
            'category' => null,
            'search' => $search,
            'sort' => $sort,
            'seo' => $seo->products($page, $search, $sort !== ProductSort::DEFAULT),
        ]);
    }

    // public function category(Request $request, string $slug)
    // {
    //     $store = $request->attributes->get('store');
    //     $theme = $request->attributes->get('theme');
    //     $seo = $request->attributes->get('seo');

    //     $category = $store->categories()->where('slug', $slug)->firstOrFail();
    //     $page = max(1, (int) $request->query('page', 1));

    //     // Scoped by store AND category, so nothing from another store can appear
    //     $products = $store->visibleProducts()
    //         ->with('variants')
    //         ->where('category_id', $category->id)
    //         ->latest()
    //         ->paginate(12);

    //     return view($theme->view('index'), [
    //         'products' => $products,
    //         'category' => $category,
    //         'search' => '',
    //         'seo' => $seo->category($category, $page),
    //     ]);
    // }

    public function category(Request $request, string $slug)
    {
        $store = $request->attributes->get('store');
        $theme = $request->attributes->get('theme');
        $seo = $request->attributes->get('seo');

        $category = $store->categories()->where('slug', $slug)->firstOrFail();

        $rawSort = $request->query('sort');
        $sort = ProductSort::from(is_string($rawSort) ? $rawSort : null);

        $page = max(1, (int) $request->query('page', 1));

        // Scoped by store AND category, so nothing from another store can appear
        $query = $store->visibleProducts()
            ->with('variants')
            ->where('category_id', $category->id);

        ProductSort::apply($query, $sort);

        $products = $query->paginate(12)->withQueryString();

        return view($theme->view('index'), [
            'products' => $products,
            'category' => $category,
            'search' => '',
            'sort' => $sort,
            'seo' => $seo->category($category, $page, $sort !== ProductSort::DEFAULT),
        ]);
    }

    public function show(Request $request, string $product)
    {
        $store = $request->attributes->get('store');
        $theme = $request->attributes->get('theme');
        $seo = $request->attributes->get('seo');

        // The address is "12-cotton-panjabi": the number finds the product
        if (! preg_match('/^(\d+)(?:-(.*))?$/', $product, $match)) {
            abort(404);
        }

        $item = $store->visibleProducts()->with(['variants', 'category'])->findOrFail((int) $match[1]);

        // One address per product: old or mistyped words are sent permanently to the right one
        if (($match[2] ?? '') !== $item->slugPart()) {
            return redirect()->to($item->storefrontUrl(), 301);
        }

        // $limit = $theme->slug === 'fresh' ? 5 : 4;
        $limit = $theme->shelfLimit(\App\Support\Theme::RELATED_ROWS);

        // Same category first, then the newest products to fill the row
        $pool = $store->visibleProducts()->with('variants')
            ->whereKeyNot($item->id)
            ->when($item->category_id, fn ($query) => $query->where('category_id', $item->category_id))
            ->latest()
            ->limit(12)
            ->get();

        if ($pool->count() < $limit) {
            $pool = $pool->concat(
                $store->visibleProducts()->with('variants')
                    ->whereKeyNot($item->id)
                    ->whereNotIn('id', $pool->pluck('id'))
                    ->latest()
                    ->limit($limit - $pool->count())
                    ->get()
            );
        }

        // Products that can be bought come first
        $related = $pool->sortByDesc(fn ($candidate) => $candidate->isAvailable())->values()->take($limit);

        return view($theme->view('show'), [
            'product' => $item,
            'related' => $related,
            'relatedUrl' => $item->category
                ? route('storefront.category', ['slug' => $item->category->slug])
                : route('storefront.products'),
            'seo' => $seo->product($item),
        ]);
    }

    public function thanks(Request $request, string $publicId)
    {
        $store = $request->attributes->get('store');

        // Scoped to this store AND matched by an unguessable ID
        $order = $store->orders()->with('items')->where('public_id', $publicId)->firstOrFail();

        $track = null;

        if (! in_array($order->status, ['awaiting_payment', 'cancelled'], true)) {
            // Atomic claim: only the first page view of this order reports the sale
            $claimed = Order::whereKey($order->id)
                ->whereNull('purchase_tracked_at')
                ->update(['purchase_tracked_at' => now()]);

            if ($claimed) {
                $track = Tracking::purchase($order);
            }
        }

        return view('storefront.thanks', compact('order', 'track'));
    }
}