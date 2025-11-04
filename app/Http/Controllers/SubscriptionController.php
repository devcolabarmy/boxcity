<?php

namespace App\Http\Controllers;

use App\Mail\AppMailer;
use App\Models\NewsLetter;
use App\Models\Subscriber;
use App\Models\SubscriptionConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $email = $request->input('email');

        // Basic validation
        $request->validate([
            'email' => 'required|email'
        ]);

        // Create token
        $token = Str::random(40);

        // Save or update existing token
        SubscriptionConfirmation::updateOrCreate(
            ['email' => $email],
            ['token' => $token]
        );

        $newsletter = NewsLetter::updateOrCreate([
            'email' => $request->email
        ]);

        Subscriber::create([
            "list_id" => 0,
            "contact_id" => $newsletter->id,
            "status" => "unconfirmed",
            "optin_type" => 0
        ]);

        // Generate confirmation URL
        $confirmationUrl = route('subscription.confirm', ['token' => $token]);

        // Send confirmation email
        Mail::to($request->email)->send(new AppMailer(
            'Welcome to the BoxCity!',
            'emails.newsletter_subscribe',
            [
                'cta' => $confirmationUrl
            ]
        ));

        return response()->json(['message' => 'A confirmation link has been sent to your email.']);
    }

    public function confirm($token)
    {
        $confirmation = SubscriptionConfirmation::where('token', $token)->first();
        $newsletter = NewsLetter::where(['email' => $confirmation->email])->first();
        if (!$confirmation || !$newsletter) {
            abort(404, 'Invalid or expired token.');
        }

        Subscriber::updateOrCreate(
            ['contact_id' => $newsletter->id],
            [
                'status' => 'Subscribed',
                'subscribed_at' => now()
            ]
        );

        // Send confirmation email
        Mail::to( $confirmation->email)->send(new AppMailer(
            'Welcome to the BoxCity!',
            'emails.newsletter_confirmed'
        ));

        // Delete the token to prevent reuse
        $confirmation->delete();

        return view('web.subscription.confirmed');
    }

}