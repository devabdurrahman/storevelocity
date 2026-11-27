<?php

namespace App\Http\Middleware;

use App\Support\CurrentStore;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetStoreUrlDefaults
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($store = app(CurrentStore::class)->get()) {
            URL::defaults(['storeSlug' => $store->slug]);
        }

        return $next($request);
    }
}