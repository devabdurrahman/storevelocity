<?php

namespace App\Services;

use App\Exceptions\CourierException;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SteadfastClient
{
    public const AUTH_FAILED = 'Steadfast did not accept the API keys. Check them in Store settings.';

    public function __construct(private Store $store)
    {
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.steadfast.base_url'), '/'))
            ->withHeaders([
                'Api-Key' => $this->store->steadfast_api_key,
                'Secret-Key' => $this->store->steadfast_secret_key,
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    private function send(string $method, string $path, array $body = []): array
    {
        $response = $method === 'GET'
            ? $this->http()->get($path)
            : $this->http()->post($path, $body);

        $json = $response->json();

        if (! is_array($json)) {
            throw new CourierException('Unexpected response from Steadfast.');
        }

        // if (! $response->successful() || (int) ($json['status'] ?? 200) >= 400) {
        //     throw new CourierException($this->failure($json, $response->status()));
        // }

        if (! $response->successful() || (int) ($json['status'] ?? 200) >= 400) {
            \Illuminate\Support\Facades\Log::warning('Steadfast request failed', [
                'path' => $path,
                'http_status' => $response->status(),
                'body' => \Illuminate\Support\Str::limit($response->body(), 500),
            ]);

            throw new CourierException($this->failure($json, $response->status()));
        }

        return $json;
    }

    
    // private function send(string $method, string $path, array $body = []): array
    // {
    //     $response = $method === 'GET'
    //         ? $this->http()->get($path)
    //         : $this->http()->post($path, $body);

    //     $json = $response->json();

    //     // Log responses that are not valid JSON arrays
    //     if (! is_array($json)) {
    //         \Illuminate\Support\Facades\Log::error('Steadfast returned an unexpected response', [
    //             'path' => $path,
    //             'http_status' => $response->status(),
    //             'content_type' => $response->header('Content-Type'),
    //             'response_body' => \Illuminate\Support\Str::limit($response->body(), 1000),
    //         ]);

    //         throw new CourierException(
    //             'Steadfast returned an invalid response (HTTP ' . $response->status() . '). Check Laravel logs.'
    //         );
    //     }

    //     // Handle HTTP and API errors
    //     if (! $response->successful() || (int) ($json['status'] ?? 200) >= 400) {
    //         \Illuminate\Support\Facades\Log::warning('Steadfast request failed', [
    //             'path' => $path,
    //             'http_status' => $response->status(),
    //             'response' => $json,
    //         ]);

    //         throw new CourierException($this->failure($json, $response->status()));
    //     }

    //     return $json;
    // }


    private function failure(array $json, int $httpStatus): string
    {
        if (in_array($httpStatus, [401, 403], true) || (int) ($json['status'] ?? 0) === 401) {
            return self::AUTH_FAILED;
        }

        if (! empty($json['errors']) && is_array($json['errors'])) {
            $first = collect($json['errors'])->flatten()->first();

            if ($first) {
                return (string) $first;
            }
        }

        return (string) ($json['message'] ?? 'Steadfast could not process the request.');
    }

    public function balance(): float
    {
        if (config('services.steadfast.fake')) {
            return 1234.56;
        }

        $json = $this->send('GET', 'get_balance');

        if (! isset($json['current_balance'])) {
            throw new CourierException('Steadfast did not return a balance.');
        }

        return (float) $json['current_balance'];
    }

    /** For the dashboard: cached for 10 minutes, and never throws. */
    public function cachedBalance(): ?float
    {
        $key = "steadfast:balance:{$this->store->id}";

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        try {
            $balance = $this->balance();
        } catch (\Throwable) {
            return null;
        }

        Cache::put($key, $balance, now()->addMinutes(10));

        return $balance;
    }

    /** @return array{consignment_id: string, tracking_code: string, status: string} */
    public function createOrder(Order $order): array
    {
        if (config('services.steadfast.fake')) {
            return [
                'consignment_id' => (string) random_int(10000000, 99999999),
                'tracking_code' => strtoupper(Str::random(10)),
                'status' => 'in_review',
            ];
        }

        $order->loadMissing('items');

        $items = $order->items
            ->map(fn ($item) => $item->quantity . ' x ' . $item->product_name . ($item->variant_name ? ' (' . $item->variant_name . ')' : ''))
            ->implode(', ');

        $json = $this->send('POST', 'create_order', [
            'invoice' => $this->store->slug . '-' . $order->number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => Str::limit($order->customer_address, 250, ''),
            // The customer pays the full total in cash on delivery
            'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total : 0,
            'note' => Str::limit((string) $order->customer_note, 200, ''),
            'item_description' => Str::limit($items, 200, ''),
        ]);

        $consignment = $json['consignment'] ?? null;

        if (empty($consignment['consignment_id'])) {
            throw new CourierException((string) ($json['message'] ?? 'Steadfast did not accept the order.'));
        }

        return [
            'consignment_id' => (string) $consignment['consignment_id'],
            'tracking_code' => (string) ($consignment['tracking_code'] ?? ''),
            'status' => (string) ($consignment['status'] ?? 'in_review'),
        ];
    }

    public function statusByConsignment(string $consignmentId): string
    {
        if (config('services.steadfast.fake')) {
            return 'in_review';
        }

        $json = $this->send('GET', 'status_by_cid/' . rawurlencode($consignmentId));

        return (string) ($json['delivery_status'] ?? '');
    }

    public function createReturnRequest(string $consignmentId, ?string $reason = null): void
    {
        if (config('services.steadfast.fake')) {
            return;
        }

        $this->send('POST', 'create_return_request', array_filter([
            'consignment_id' => $consignmentId,
            'reason' => $reason,
        ], fn ($value) => filled($value)));
    }

    /**
     * How a customer's earlier parcels went, across all merchants.
     *
     * @return array{total: int, success: int, cancel: int}
     */
    public function fraudCheck(string $phone): array
    {
        if (config('services.steadfast.fake')) {
            // Made-up but steady numbers, so testing shows different customers
            $seed = abs(crc32($phone));
            $total = $seed % 25;
            $success = (int) floor($total * (50 + $seed % 51) / 100);

            return ['total' => $total, 'success' => $success, 'cancel' => $total - $success];
        }

        $path = config('services.steadfast.fraud_path');

        if (blank($path)) {
            throw new CourierException('The Steadfast fraud check address is not set up yet.');
        }

        $json = $this->send('GET', ltrim(str_replace('{phone}', rawurlencode($phone), $path), '/'));

        return $this->readFraudReply($json);
    }

    /**
     * Reads the reply into three numbers. If the reply isn't recognised we say so,
     * and log only its field NAMES, so we can adapt instead of showing wrong numbers.
     */
    private function readFraudReply(array $json): array
    {
        $data = is_array($json['data'] ?? null) ? $json['data'] : $json;

        $pick = function (array $keys) use ($data): ?int {
            foreach ($keys as $key) {
                if (isset($data[$key]) && is_numeric($data[$key])) {
                    return (int) $data[$key];
                }
            }

            return null;
        };

        $success = $pick(['total_delivered', 'success', 'delivered', 'total_success', 'success_count']);
        $cancel = $pick(['total_cancelled', 'total_canceled', 'cancel', 'cancelled', 'total_cancel', 'cancel_count']);
        $total = $pick(['total_parcels', 'total', 'total_orders', 'total_deliveries']);

        if ($success === null && $cancel === null && $total === null) {
            Log::warning('Steadfast fraud check: reply not recognised', ['fields' => array_keys($data)]);

            throw new CourierException('Steadfast replied, but the numbers could not be read. Please tell the developer.');
        }

        $success ??= 0;
        $cancel ??= 0;

        return [
            'total' => $total ?? ($success + $cancel),
            'success' => $success,
            'cancel' => $cancel,
        ];
    }
}