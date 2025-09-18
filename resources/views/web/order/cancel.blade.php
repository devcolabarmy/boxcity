@extends('layouts.app')

@section('title', 'Payment Cancel')

@section('content')


        <div class="thank-you">
            <h1>Payment Canceled</h1>
            <p>Your payment was cancelled. No charges were made</p>
            <a href="{{ route('home') }}" class="btn">Go back to Home</a>
        </div>

@endsection
