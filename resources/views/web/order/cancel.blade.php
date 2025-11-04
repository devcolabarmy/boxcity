@extends('layouts.app')

@section('title', 'Payment Cancel')

@section('content')


        <div class="thank-you">
            <h1>Payment Canceled</h1>
            @if(session('error'))
                <p>{{ session('error') }}</p>
            @else
                <p>Payment was cancelled. No charges were made</p>
            @endif
            <a href="{{ route('home') }}" class="btn">Go back to Home</a>
        </div>


@endsection
