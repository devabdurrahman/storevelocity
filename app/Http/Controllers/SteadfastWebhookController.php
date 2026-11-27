<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Support\ApplyCourierStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SteadfastWebhookController extends Controller
{
    public function __invoke(Request $request, string $webhookKey, ApplyCourierStatus $apply)
    {
        $store = Store::where('webhook_key', $webhookKey)->first();

        if (! $store || blank($store->steadfast_webhook_token)) {
            abort(404);
        }

        // Constant-time comparison of the bearer token
        if (! hash_equals($store->steadfast_webhook_token, (string) $request->bearerToken())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'consignment_id' => ['required'],
            'status' => ['nullable', 'string', 'max:50'],
            'tracking_message' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Scoped to THIS store: another store's parcel can never be touched
        $order = $store->orders()
            ->where('consignment_id', (string) $request->input('consignment_id'))
            ->first();

        if ($order) {
            $apply->handle($order, $request->input('status'), $request->input('tracking_message'));
        }

        // Always 200 for a valid, authenticated call so Steadfast doesn't keep retrying
        return response()->json(['status' => 'success', 'message' => 'Webhook received successfully.']);
    }
}