<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\EcwidApiClient;
use App\Services\OrderBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use Stevebauman\Location\Facades\Location;

/**
 * Controller responsible for managing the checkout process.
 */
class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected EcwidApiClient $ecwidClient;
    protected OrderBuilder $orderBuilder;

    /**
     *
     * Inject dependencies via constructor.
     *
     * @param CartService $cartService
     * @param EcwidApiClient $ecwidClient
     * @param OrderBuilder $orderBuilder
     */
    public function __construct(
        CartService $cartService,
        EcwidApiClient $ecwidClient,
        OrderBuilder $orderBuilder
    ) {
        $this->cartService = $cartService;
        $this->ecwidClient = $ecwidClient;
        $this->orderBuilder = $orderBuilder;
    }

    /**
     * Display the checkout view.
     *
     * @return View
     */
    public function index(): View
    {
        $ip = request()->ip();
//        $position = Location::get($ip);
        $position = Location::get('116.90.110.129');

        $userLat = null;
        $userLng = null;

        if ($position) {
            $userLat = $position->latitude;
            $userLng = $position->longitude;
        }

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
            // Update label for nearest store
            $stores = $stores->map(function ($store) use ($nearestKey) {
                if ($store['key'] === $nearestKey) {
                    $store['label'] .= ' (Nearest – with all the items)';
                }
                return $store;
            });

            // Move nearest store to top
            $stores = $stores->sortByDesc(fn($store) => $store['key'] === $nearestKey)->values();
        }

        return view('web.order.checkout', [
            'stores' => $stores,
            'nearestKey' => $nearestKey,
        ]);
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Process the checkout and place the order via Ecwid API.
     *
     * @param CheckoutRequest $request
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function processCheckout(CheckoutRequest $request): JsonResponse
    {
        $cart = $this->cartService->getCart();

        if ($this->cartService->isEmpty()) {
            return response()->json(['error' => 'Your cart is empty.'], 400);
        }

        $orderData = $this->orderBuilder->build($request, $cart);

        $response = $this->ecwidClient->postOrder($orderData);

        if (isset($response['error'])) {
            // Log the error for debugging
            Log::error('Order placement failed', [
                'error' => $response['error'],
                'orderData' => $orderData,
                'response' => $response['details'] ?? 'No further details available.',
            ]);

            return response()->json([
                'error' => $response['error'],
                'response' => $response['details'] ?? 'No further details available.',
            ], 400);
        }

        $this->cartService->clear();

        return response()->json([
            'success' => 'Order has been placed successfully!',
            'orderId' => $response['id'],
        ]);
    }

    /**
     * Display the thank you page after successful checkout.
     *
     * @return View
     */
    public function thankyou(): View
    {
        return view('web.order.thankyou');
    }

}
