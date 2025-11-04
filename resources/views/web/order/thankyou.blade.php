@extends('layouts.app')

@section('title', 'Thank You')

@section('content')

    @if(session('title') && session('message'))
        <div class="thank-you">
            <p>{{ session('title') }}</p>
            <p>{!! nl2br(e(session('message'))) !!}</p>
            <a href="{{ route('home') }}" class="btn">Go back to Home</a>
        </div>
    @else
        <div class="thank-you">
            <h1>Thank you for your order!</h1>
            <p>Your order was placed successfully. You will receive a confirmation email shortly.</p>
            <a href="{{ route('home') }}" class="btn">Go back to Home</a>
        </div>
    @endif


    @if(session('clear_local_cart'))
        <script>
            localStorage.removeItem('cart');
        </script>
    @endif
   


@endsection
