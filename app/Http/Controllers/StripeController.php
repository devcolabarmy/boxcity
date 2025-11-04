<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\EcwidApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;
use Illuminate\Support\Facades\DB;

class StripeController extends Controller
{
    /**
     * The Ecwid API client instance.
     */
    protected EcwidApiClient $ecwidClient;

    /**
     * Cart service to manage the session cart.
     */
    protected CartService $cartService;

    public function __construct(EcwidApiClient $ecwidClient, CartService $cartService)
    {
        $this->ecwidClient = $ecwidClient;
        $this->cartService = $cartService;
    }

    /**
     * Create a Stripe Checkout Session and stash the Ecwid order payload
     * until payment is confirmed.
     */
    public function createOrder(Request $request)
    {
        $amount = (float) $request->input('amount', 0);
        $email = $request->input('email');
        $orderData = $request->input('order');

        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount.'], 422);
        }

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email.'], 422);
        }

        if (!is_array($orderData) || empty($orderData)) {
            return response()->json(['error' => 'Missing order data.'], 422);
        }

        $orderTotal = isset($orderData['total']) ? (float) $orderData['total'] : null;

        if ($orderTotal === null || $orderTotal <= 0) {
            return response()->json(['error' => 'Invalid order total.'], 422);
        }

        // Normalise the amount against the order payload to avoid tampering.
        $amount = $orderTotal;

        $pendingOrders = session('pending_ecwid_orders', []);
        // $pendingId = (string) Str::uuid();
        // $pendingOrders[$pendingId] = [
        //     'order' => $orderData,
        //     'email' => $email,
        //     'created_at' => now()->toIso8601String(),
        // ];
        // session(['pending_ecwid_orders' => $pendingOrders]);
        // session()->save();

           $pendingId = (string) Str::uuid();
    DB::table('pending_orders')->insert([
        'id'         => $pendingId,
        'email'      => $email,
        'cart'       => json_encode($orderData, JSON_UNESCAPED_UNICODE),
        'status'     => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

        Log::info('Stripe checkout pending order stored', [
            'pending_id' => $pendingId,
            'amount' => $amount,
            'email' => $email,
        ]);
    

        try {
            Stripe::setApiKey(config('stripe.secret'));

            $session = CheckoutSession::create([
                'payment_method_types' => ['card'],
                'customer_email' => $email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int) round($amount * 100),
                        'product_data' => [
                            'name' => 'Order Payment',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}&pending_id=' . $pendingId,
                'cancel_url'  => route('stripe.cancel', [], true),
                'metadata' => [
                    'pending_order_id' => $pendingId,
                ],
            ]);

            return response()->json([
                'stripe_url' => $session->url,
                'session_id' => $session->id,
                'pending_id' => $pendingId,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout session creation failed', [
                'error' => $e->getMessage(),
            ]);

          DB::table('pending_orders')->where('id', $pendingId)->delete();

            Log::error('Stripe checkout session creation failed', [
                'pending_id' => $pendingId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Stripe session creation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle the return from Stripe. Only create the Ecwid order once the
     * payment status is confirmed as paid by Stripe.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $pendingId = $request->query('pending_id');

        Log::info('Stripe success route invoked', [
            'session_id' => $sessionId,
            'pending_id' => $pendingId,
        ]);

        if (!$sessionId || !$pendingId) {
            Log::warning('Stripe success callback missing identifiers', [
                'session_id' => $sessionId,
                'pending_id' => $pendingId,
            ]);

            return redirect()->route('stripe.cancel')->with('error', 'Unable to verify payment details.');
        }

        try {
            Stripe::setApiKey(config('stripe.secret'));
            $session = CheckoutSession::retrieve($sessionId);
        } catch (\Exception $e) {
            Log::error('Stripe session retrieval failed', [
                'session_id' => $sessionId,
                'pending_id' => $pendingId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('stripe.cancel')->with('error', 'Unable to verify payment status.');
        }

        Log::info('Stripe session returned', [
            'session_id' => $sessionId,
            'pending_id' => $pendingId,
            'payment_status' => $session->payment_status,
            'mode' => $session->mode ?? null,
        ]);

        if ($session->payment_status !== 'paid') {
            Log::warning('Stripe reported unpaid session on success redirect', [
                'session_id' => $sessionId,
                'pending_id' => $pendingId,
                'payment_status' => $session->payment_status,
            ]);

            return redirect()->route('stripe.cancel')->with('error', 'Payment was not completed.');
        }

        $row = DB::table('pending_orders')->where('id', $pendingId)->first();
if (!$row) {
    Log::error('Pending Ecwid order not found for Stripe success', [
        'pending_id' => $pendingId,
    ]);
    return redirect()->route('stripe.cancel')->with('error', 'Order details were not found. Please contact support.');
}

$orderPayload = json_decode($row->cart ?? '[]', true) ?: [];
$orderPayload['paymentStatus'] = 'PAID';
$orderPayload['paymentMethod'] = $orderPayload['paymentMethod'] ?? 'Stripe Checkout';
$emailForLog = $row->email ?? null;


        
      Log::info('Posting paid order to Ecwid', [
    'pending_id' => $pendingId,
    'email' => $emailForLog,
    'token_prefix' => substr(config('ecwid.access_token'), 0, 8),
]);

        
        $ecwidResponse = null;
        $ecwidOrderId = null;
        $ecwidError = null;

        try {
            $ecwidResponse = $this->ecwidClient->postOrder($orderPayload);
        } catch (\Exception $e) {
            $ecwidError = [
                'error' => 'Exception during Ecwid order post',
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ];

            Log::error('Ecwid order creation failed after Stripe success', [
                'pending_id' => $pendingId,
                'error' => $e->getMessage(),
            ]);
        }

        if (!$ecwidError && isset($ecwidResponse['error'])) {
            $ecwidError = $ecwidResponse;

            Log::error('Ecwid API returned error after Stripe success', [
                'pending_id' => $pendingId,
                'response' => $ecwidResponse,
            ]);
        }

        if ($ecwidError) {
            session()->flash('order_warning', 'Payment processed, but we could not sync the order to Ecwid. Our team has been notified.');
            session()->flash('ecwid_error_response', $ecwidError);
            session()->flash('ecwid_error_payload', $orderPayload);
            session()->flash('stripe_debug_ids', [
                'pending_id' => $pendingId,
                'session_id' => $sessionId,
            ]);
        } else {
            $ecwidOrderId = $ecwidResponse['id'] ?? null;

            Log::info('Ecwid order created after Stripe success', [
                'pending_id' => $pendingId,
                'ecwid_order_id' => $ecwidOrderId,
            ]);

            session()->forget('ecwid_error_response');
            session()->forget('ecwid_error_payload');
            session()->forget('stripe_debug_ids');
            session()->forget('order_warning');
        }

     DB::table('pending_orders')->where('id', $pendingId)->delete();

        $this->cartService->clear();

        session()->flash('clear_local_cart', true);
        if ($ecwidOrderId) {
            session()->flash('ecwid_order_id', $ecwidOrderId);
        }

        return redirect()->route('checkout.thankyou');
    }

    /**
     * Cancel page.
     */
    public function cancel()
    {
        if (session()->has('error')) {
            Log::notice('Stripe cancel page shown with error', [
                'error' => session('error'),
            ]);
        } else {
            Log::notice('Stripe cancel page shown without specific error');
        }

        $debug = [
            'query' => request()->query(),
            'session_keys' => array_keys(session()->all()),
            'pending_ecwid_orders' => session('pending_ecwid_orders'),
            'error' => session('error'),
            'ecwid_error_response' => session('ecwid_error_response'),
            'ecwid_error_payload' => session('ecwid_error_payload'),
            'stripe_debug_ids' => session('stripe_debug_ids'),
            'ecwid_token_prefix' => substr(config('ecwid.access_token'), 0, 8),
            'ecwid_base_url' => config('ecwid.api_base_url'),
        ];

        return view('web.order.cancel', ['debug' => $debug]);
    }
}