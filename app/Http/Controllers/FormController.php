<?php

namespace App\Http\Controllers;

use App\Mail\AppMailer;
use App\Models\NewsLetter;
use App\Models\Product;

use App\Models\WpCf7Submit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class FormController extends Controller
{

    public function contactForm(Request $request)
    {
        // Validate request fields
        $validated = $request->validate([
            'your-name' => 'required|string|max:255',
            'your-company' => 'nullable|string|max:255',
            'your-email' => 'required|email|max:255',
            'your-phone' => 'required|string|max:20',
            'select-service' => 'required|string|max:255',
            'your-message' => 'required|string',
        ]);

        $formId = 602;
        $submitTime = Carbon::now();

        // Prepare data with cfdb7_status
        $formFields = array_merge(
            ['cfdb7_status' => 'unread'],
            $validated
        );

        WpCf7submit::insert([
            'form_post_id' => $formId,
            'form_value' => serialize($formFields),
            'form_date' => $submitTime,
        ]);

        // Send mail
        Mail::to($formFields['your-email'])->send(new AppMailer(
            "Welcome to the BoxCity!",
            "emails.contact",
            [
                'userName' => $formFields['your-name'],
                'company' => $formFields['your-company'] ?? '',
                'email' => $formFields['your-email'] ?? '',
                'phone' => $formFields['your-phone'],
                'service' => $formFields['select-service'],
                'msg' => $formFields['your-message']
            ]
        ));

        return redirect(route('checkout.thankyou'))->with([
            'title' => 'Thank you for contacting us. One of our team members will reach out to you shortly.',
            'message' => "Don’t want to wait? Call us now (800) 992-6924 \n Hours: Monday to Friday 8:30AM – 5:00PM"
        ]);
    }

}



