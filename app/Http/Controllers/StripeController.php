<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Illuminate\Support\Facades\Http;

class StripeController extends Controller
{
    /**
     * Create a Stripe Checkout Session
     */
    public function createOrder(Request $request)
    {
        $amount = $request->input('amount', 0);
        $email  = $request->input('email');
        $ecwidOrderId = $request->input('order_id'); // 🔹 Expect Ecwid orderId from frontend

        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount.'], 422);
        }

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email.'], 422);
        }

        if (!$ecwidOrderId) {
            return response()->json(['error' => 'Missing Ecwid order ID.'], 422);
        }

        try {
            Stripe::setApiKey(config('stripe.secret'));

            $session = CheckoutSession::create([
                'payment_method_types' => ['card'],
                'customer_email' => $email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $amount * 100, // cents
                        'product_data' => [
                            'name' => 'Order Payment',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('stripe.cancel'),
                'metadata' => [ // 🔹 Store Ecwid order ID in Stripe
                    'order_id' => $ecwidOrderId
                ]
            ]);

            return response()->json([
                'stripe_url' => $session->url,
                'session_id' => $session->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Stripe session creation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }






    // Success page after Stripe Checkout
    public function success(Request $request)
    {
        \Stripe\Stripe::setApiKey(config('stripe.secret'));
        $session = \Stripe\Checkout\Session::retrieve($request->get('session_id'));

        $order_id = $session->metadata->order_id;
        $storeId = config('stripe.store_id');  // make sure you added this in config/ecwid.php
        $token   = config('stripe.token');     // add token to config

        $url = "https://app.ecwid.com/api/v3/" . config('stripe.store_id') . "/orders/" . $order_id;
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->put($url, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'paymentStatus' => 'PAID'
                ],
            ]);

            return redirect()->route('checkout.thankyou');

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ecwid update failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }




    /**
     * Cancel page
     */
    public function cancel()
    {
        return view('web.order.cancel');
    }
}
