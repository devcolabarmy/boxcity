<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayPalService;
use App\Http\Requests\CheckoutRequest;

class PayPalController extends Controller
{

    public function createOrder(Request $request, PayPalService $paypal)
    {
        $amount = $request->input('amount', 0);
        $email = $request->input('email');

        if ($amount <= 0) {
            return response()->json(['error' => 'Invalid amount.'], 422);
        }

        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email.'], 422);
        }

        $order = $paypal->createOrder($amount, $email);

        $approveUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        return response()->json([
            'paypal_url' => $approveUrl,
            'orderID' => $order['id']
        ]);
    }




    // 🔹 Capture the order after approval
    public function captureOrder(Request $request, PayPalService $paypal)
    {
        $orderID = $request->input('orderID') ?? $request->input('token'); // fallback to token


        if (!$orderID) {
            return response()->json([
                'error' => 'Missing order ID',
                'message' => 'No orderID provided in request.'
            ], 400);
        }

        try {
            $response = $paypal->captureOrder($orderID);

            // Optional: temporary for debugging
            return response()->json([
                'message' => 'Order captured successfully!',
                
            ]);

             $checkoutRequest = new CheckoutRequest($request->all());
             $checkoutController = app()->make(\App\Http\Controllers\CheckoutController::class);
             return $checkoutController->processCheckout($checkoutRequest);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'PayPal capture failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }




}

