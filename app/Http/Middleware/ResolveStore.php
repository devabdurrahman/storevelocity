<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveStore
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $slug = $request->route('storeSlug');

        // $store = Store::where('slug', $slug)
        //     ->where('is_active', true)
        //     ->first();

        // abort_if(! $store, 404);

        $store = app(\App\Support\CurrentStore::class)->getOrFail();

        $request->attributes->set('store', $store);
        View::share('store', $store);
        $theme = \App\Support\Theme::for($store);
        $request->attributes->set('theme', $theme);
        View::share('theme', $theme);
        View::share('themeLayout', $theme->layout());

        $seo = new \App\Support\Seo($store, $theme);
        $request->attributes->set('seo', $seo);
        View::share('siteSeo', $seo);

        URL::defaults(['storeSlug' => $store->slug]);

        // Don't pass the subdomain into controller methods
        $request->route()->forgetParameter('storeSlug');

        return $next($request);
    }
}
