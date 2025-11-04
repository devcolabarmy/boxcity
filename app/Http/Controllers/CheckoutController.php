<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\EcwidApiClient;
use App\Services\OrderBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use GuzzleHttp\Exception\GuzzleException;
use Stevebauman\Location\Facades\Location;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected EcwidApiClient $ecwidClient;
    protected OrderBuilder $orderBuilder;

    public function __construct(
        CartService $cartService,
        EcwidApiClient $ecwidClient,
        OrderBuilder $orderBuilder
    ) {
        $this->cartService  = $cartService;
        $this->ecwidClient  = $ecwidClient;
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * Checkout page.
     * - Preserves nearest-store logic
     * - Adds per-pickup-location stock notes using Ecwid locationInventory
     */

public function index(): View
{
    $ip = request()->ip();
    // $position = Location::get($ip);
    $position = \Stevebauman\Location\Facades\Location::get('116.90.110.129');

    $userLat = $position?->latitude;
    $userLng = $position?->longitude;

    $stores = collect(config('stores'));
    $nearestStore = null;
    $nearestKey = null;
    $minDistance = INF;

    if ($userLat && $userLng) {
        foreach ($stores as $store) {
            $distance = $this->haversine($userLat, $userLng, $store['lat'], $store['lon']);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestStore = $store;
                $nearestKey = $store['key'];
            }
        }
    }

    if ($nearestStore) {
        $stores = $stores->map(function ($store) use ($nearestKey) {
            if (($store['key'] ?? null) === $nearestKey) {
                $store['label'] .= '';
            }
            return $store;
        })->sortByDesc(fn ($s) => ($s['key'] ?? null) === $nearestKey);
    }

    // 1) Fetch Ecwid shipping options (unchanged)
    $shippingOptions = [];
    try {
        $shippingOptions = $this->ecwidClient->fetchShippingOptions();
    } catch (\Throwable $e) {
        // ignore for UI
    }

    $deliveryOptions = collect($shippingOptions)->filter(fn ($o) =>
        isset($o['fulfilmentType']) && strtolower($o['fulfilmentType']) === 'delivery'
    );

    // 2) Attach delivery zone/id for UI (THIS was the block that got corrupted)
    $stores = $stores->map(function ($store) use ($deliveryOptions) {
        $match = $deliveryOptions->first(function ($option) use ($store) {
            $haystack = strtolower(($option['title'] ?? '') . ' ' . ($option['destinationZone']['name'] ?? ''));
            $label = strtolower($store['label'] ?? '');
            $value = strtolower($store['value'] ?? '');
            return \Illuminate\Support\Str::contains($haystack, $label) ||
                   \Illuminate\Support\Str::contains($haystack, $value);
        });

        $store['delivery_destination_zone'] = $match['destinationZone']['name'] ?? ($match['title'] ?? null);
        $store['shipping_option_id']        = $match['id'] ?? null; // delivery option id
        return $store;
    })->values();

    // 3) Build pickup notes using per-store inventory_location_id
    $cartItems = $this->getCartItems();

    // per-request product cache
    static $productCache = [];
    $fetch = function (string $pid) use (&$productCache) {
        if (!array_key_exists($pid, $productCache)) {
            $productCache[$pid] = $this->fetchEcwidProduct($pid);
        }
        return $productCache[$pid];
    };

    // Relaxed logic: only "unknown" if NONE of the items have locationInventory
    $stores = $stores->map(function ($store) use ($cartItems, $fetch) {
        $locId = (int) ($store['inventory_location_id'] ?? 0);
        if ($locId <= 0) {
            $store['stock_note'] = 'Availability unknown';
            return $store;
        }

          $shortUnits   = 0;  // total units short across all items
    $knownChecked = 0;  // items where we actually had locationInventory

    foreach ($cartItems as $item) {
        $pid = (string) ($item['id'] ?? '');
        $qty = (int) ($item['qty'] ?? 0);
        if ($pid === '' || $qty <= 0) continue;

        $prod = $fetch($pid);
        $inv  = $prod['locationInventory'] ?? null;

        if (!is_array($inv)) {
            // no inventory info — skip, don't penalize
            continue;
        }

        $knownChecked++;
      $availableRaw = (int) ($inv[(string)$locId] ?? 0);
$available    = max(0, $availableRaw);               // clamp negatives to 0
$shortUnits  += max(0, $qty - $available);
    }

    if (empty($cartItems)) {
        $store['stock_note'] = 'Add items to check stock';
    } elseif ($knownChecked === 0) {
        $store['stock_note'] = 'Availability unknown';
    } else {
        $store['stock_note'] = $shortUnits > 0
            ? $shortUnits . ' ' . \Illuminate\Support\Str::plural('item', $shortUnits) . ' out of stock'
            : 'All items are in stock';
    }

    return $store;
})->values();

    $storesJson = $stores->map(function ($store) {
        return [
            'key'    => $store['key'],
            'label'  => $store['label'],
            'value'  => $store['value'],
            'lat'    => $store['lat'],
            'lon'    => $store['lon'],
            'address'=> $store['address'] ?? '',
            'city'   => $store['city'] ?? '',
            'delivery_destination_zone' => $store['delivery_destination_zone'] ?? null,
        ];
    })->toArray();

    // 4) Simple on-page debug (optional)
  

    return view('web.order.checkout', [
        'stores'        => $stores,
        'nearestKey'    => $nearestKey,
        'storesJson'    => $storesJson,
        'googleMapsKey' => config('services.google.maps_key'),
       
    ]);
}
public function stockNotes(Request $request): JsonResponse
{
    // Accept items directly from the browser (don’t depend on session for this call)
    $items = (array) $request->input('items', []);
    // Normalize to [['id'=>..., 'qty'=>..., 'name'=>...], ...]
    $cartItems = [];
    foreach ($items as $it) {
        $pid = (string) ($it['productId'] ?? $it['id'] ?? '');
        $qty = (int)    ($it['quantity']  ?? $it['qty'] ?? 0);
        if ($pid === '' || $qty <= 0) continue;
        $cartItems[] = [
            'id'   => $pid,
            'qty'  => $qty,
            'name' => (string) ($it['product'] ?? $it['name'] ?? ''),
        ];
    }

    // Build notes for each store using the same logic (count **units** short)
    $stores = collect(config('stores'));

    // product cache within request
    static $cache = [];
    $fetch = function (string $pid) use (&$cache) {
        if (!array_key_exists($pid, $cache)) {
            $cache[$pid] = $this->fetchEcwidProduct($pid);
        }
        return $cache[$pid];
    };

    $notes = []; // [store_key => "note"]
    foreach ($stores as $store) {
        $key   = (string) ($store['key'] ?? '');
        $locId = (int)    ($store['inventory_location_id'] ?? 0);

        if ($locId <= 0) {
            $notes[$key] = 'Availability unknown';
            continue;
        }

        $shortUnits   = 0;
        $knownChecked = 0;

        foreach ($cartItems as $item) {
            $pid = $item['id'];
            $qty = (int) $item['qty'];
            if (!$pid || $qty <= 0) continue;

            $prod = $fetch($pid);
            $inv  = $prod['locationInventory'] ?? null;

            if (!is_array($inv)) {
                continue; // unknown inventory; skip
            }

            $knownChecked++;
            $availableRaw = (int) ($inv[(string)$locId] ?? 0);
$available    = max(0, $availableRaw);               // clamp negatives to 0
$shortUnits  += max(0, $qty - $available);
        }

        if (empty($cartItems)) {
            $notes[$key] = 'Add items to check stock';
        } elseif ($knownChecked === 0) {
            $notes[$key] = 'Availability unknown';
        } else {
            $notes[$key] = $shortUnits > 0
                ? $shortUnits . ' ' . \Illuminate\Support\Str::plural('item', $shortUnits) . ' out of stock'
                : 'All items are in stock';
        }
    }

    return response()->json([
        'ok'    => true,
        'notes' => $notes, // keyed by store['key']
    ]);
}

public function stockDetail(Request $request): JsonResponse
{
    // Inputs: items[] (same shape you already send) + store_key (required)
    $items    = (array) $request->input('items', []);
    $storeKey = (string) $request->input('store_key', '');

    if ($storeKey === '') {
        return response()->json(['ok' => false, 'error' => 'Missing store_key']);
    }

    // Normalize items
    $cartItems = [];
    foreach ($items as $it) {
        $pid = (string) ($it['productId'] ?? $it['id'] ?? '');
        $qty = (int)    ($it['quantity']  ?? $it['qty'] ?? 0);
        if ($pid === '' || $qty <= 0) continue;
        $cartItems[] = [
            'id'      => $pid,
            'qty'     => $qty,
            'name'    => (string) ($it['product'] ?? $it['name'] ?? ''),
            'thumb'   => (string) ($it['productThumb'] ?? ''),
            'sku'     => (string) ($it['sku'] ?? ''),
            'price'   => isset($it['price']) ? (float)$it['price'] : null,
        ];
    }

    // Resolve store + location id
    $store = collect(config('stores'))->first(fn($s) => ($s['key'] ?? '') === $storeKey);
    if (!$store) {
        return response()->json(['ok' => false, 'error' => 'Invalid store_key']);
    }
    $locId = (int) ($store['inventory_location_id'] ?? 0);
    if ($locId <= 0) {
        return response()->json(['ok' => true, 'anyShortage' => true, 'items' => [], 'reason' => 'unknown_inventory']);
    }

    static $cache = [];
    $fetch = function (string $pid) use (&$cache) {
        if (!array_key_exists($pid, $cache)) {
            $cache[$pid] = $this->fetchEcwidProduct($pid);
        }
        return $cache[$pid];
    };

    $detail = [];
    $anyShortage = false;

    foreach ($cartItems as $ci) {
        $prod = $fetch($ci['id']);
        $available = null;
        if (is_array($prod) && isset($prod['locationInventory']) && is_array($prod['locationInventory'])) {
          $availableRaw = (int) ($prod['locationInventory'][(string)$locId] ?? 0);
$available    = max(0, $availableRaw);     
        }

        // If we can't read inventory, treat as unknown (block checkout & force UI path)
        if (!is_int($available)) {
            $detail[] = [
                'productId'  => $ci['id'],
                'name'       => $ci['name'],
                'requested'  => (int)$ci['qty'],
                'available'  => null,
                'shortage'   => (int)$ci['qty'], // unknown -> require user action
                'thumb'      => $ci['thumb'],
                'price'      => $ci['price'],
            ];
            $anyShortage = true;
            continue;
        }

        $shortage = max(0, (int)$ci['qty'] - $available);
        if ($shortage > 0) $anyShortage = true;

        $detail[] = [
            'productId'  => $ci['id'],
            'name'       => $ci['name'],
            'requested'  => (int)$ci['qty'],
            'available'  => $available,
            'shortage'   => $shortage,
            'thumb'      => $ci['thumb'],
            'price'      => $ci['price'],
        ];
    }

    return response()->json([
        'ok'          => true,
        'anyShortage' => $anyShortage,
        'items'       => $detail,
    ]);
}


    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Accepts browser cart (from localStorage) and stores in session.
     * Shape expected per your console example: { productId, quantity, product?, price? }
     */
    public function syncCart(Request $request): JsonResponse
    {
        $items = (array) $request->input('items', []);
        $normalized = [];
        foreach ($items as $it) {
            $pid = (string) ($it['productId'] ?? $it['id'] ?? '');
            $qty = (int)    ($it['quantity']  ?? $it['qty'] ?? 0);
            if ($pid === '' || $qty <= 0) continue;
            $normalized[] = [
                'productId' => $pid,
                'quantity'  => $qty,
                'product'   => (string)($it['product'] ?? $it['name'] ?? ''),
                'price'     => isset($it['price']) ? (float) $it['price'] : null,
            ];
        }
        session(['cart.items' => $normalized]);
        return response()->json(['ok' => true, 'count' => count($normalized)]);
    }

    /**
     * Process checkout via Ecwid (original flow).
     * @throws GuzzleException
     */
    public function processCheckout(CheckoutRequest $request): JsonResponse
    {
        $cart = $this->cartService->getCart();
        if ($this->cartService->isEmpty()) {
            return response()->json(['error' => 'Your cart is empty.'], 400);
        }
        $orderData = $this->orderBuilder->build($request, $cart);
        $response  = $this->ecwidClient->postOrder($orderData);

        if (isset($response['error'])) {
            Log::error('Order placement failed', [
                'error'     => $response['error'],
                'orderData' => $orderData,
                'response'  => $response['details'] ?? 'No further details available.',
            ]);
            return response()->json([
                'error'    => $response['error'],
                'response' => $response['details'] ?? 'No further details available.',
            ], 400);
        }

        $this->cartService->clear();

        return response()->json([
            'success' => 'Order has been placed successfully!',
            'orderId' => $response['id'],
        ]);
    }

    public function thankyou(): View
    {
        return view('web.order.thankyou');
    }

    /* ======================= Helpers ======================= */

    /**
     * Normalize cart to [['id'=>string, 'name'=>string, 'qty'=>int, 'price'=>float|null], ...]
     * Uses session('cart.items') pushed from localStorage; falls back to CartService if present.
     */
private function getCartItems(): array
{
    // 1) Prefer items synced from localStorage -> session
    $sessionItems = (array) session('cart.items', []);
    $rawItems = $sessionItems;

    // 2) If session is empty, fall back to CartService (if you still use it)
    if (empty($rawItems)) {
        $cart = method_exists($this->cartService, 'getCart')
            ? (array) $this->cartService->getCart()
            : [];
        if (!empty($cart['items']) && is_array($cart['items'])) {
            $rawItems = $cart['items'];
        }
    }

    $out = [];
    foreach ($rawItems as $it) {
        $pid = (string) ($it['productId'] ?? $it['id'] ?? $it['product_id'] ?? '');
        $qty = (int)    ($it['quantity']  ?? $it['qty'] ?? 0);
        if ($pid === '' || $qty <= 0) continue;

        $name  = (string) ($it['product'] ?? $it['name'] ?? '');
        $price = isset($it['price']) ? (float) $it['price'] : null;

        $out[] = [
            'id'    => $pid,
            'name'  => $name,
            'qty'   => $qty,
            'price' => $price,
        ];
    }

    return array_values($out);
}


    /**
     * Fetch a single product from Ecwid.
     * Per your note: /products/{id} returns locationInventory.
     */
private function fetchEcwidProduct(string $productId): ?array
{
    $base  = rtrim((string) config('ecwid.api_base_url'), '/'); // e.g. https://app.ecwid.com/api/v3/109333282
    $token = (string) config('ecwid.access_token');

    try {
        $res = \Illuminate\Support\Facades\Http::timeout(15)
            ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get("{$base}/products/" . urlencode($productId));

        if (!$res->ok()) {
            return null;
        }

        $data = $res->json();

        // Defensive: some responses might wrap results
        if (is_array($data) && isset($data['items'][0])) {
            $data = $data['items'][0];
        }

        return is_array($data) ? $data : null;
    } catch (\Throwable $e) {
        return null;
    }
}

}
