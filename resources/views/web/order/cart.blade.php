@extends('layouts.app')

@section('title', 'Cart')

@section('content')

    <div class="container cart-container">
        {{-- Page Title --}}
        <h2>Your Cart</h2>

        {{-- Cart Table --}}

        <div class="table-responsive">
        <table id="product-table" class="responsive-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="cart-page-list">
            {{-- JavaScript will populate this area dynamically --}}
            <tr><td colspan="6">Loading cart...</td></tr>
            </tbody>
        </table>
        </div>
        <div class="clear-cart-container">
          <span id="clear-cart" class="clear-cart-btn">Clear Cart</span>
        </div>
        {{-- Cart Total and Clear Button --}}
        <div class="cart-total-container">
            <h3 class="cart-total">Subtotal :</h3>

        </div>
        <h3 class="save-amount-heading"><span class="save-amount"></span></h3>
        {{-- Proceed to Checkout Button --}}
        <div class="proceed-container"> 
            <a href="{{route('home')}}" id="continue-shopping" class="btn btn-danger">Continue Shopping</a>
            <button id="proceed-to-checkout" class="btn btn-danger">Proceed To Checkout</button>
        </div>
         
    </div>


    <script>
        $(document).ready(function () {
            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            updateCartPage(); // Update cart page on load
            syncCartWithSession(); // Sync cart with Laravel session on page load

            // Function to sync cart data with Laravel session
            function syncCartWithSession() {
                $.ajax({
                    url: "{{ route('cart.store') }}",
                    method: "POST",
                    data: { cart: cart, _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        
                    }
                });
            }




            $(document).on("click", ".quantity-btn", function () {
                let $row = $(this).closest("tr");
                let productName = $(this).data("product");
                let $input = $row.find(".cart-quantity");
                let value = parseInt($input.val());

                if ($(this).hasClass("plus")) {
                    $input.val(value + 1);
                } else if ($(this).hasClass("minus")) {
                    $input.val(Math.max(1, value - 1));
                }

                let newQuantity = parseInt($input.val());
                updateCartQuantity(productName, newQuantity, $row);

            });



            $(document).on("change", ".cart-quantity", function () {
                let $row = $(this).closest("tr"); // Get the closest row
                let productName = $(this).data("product");
                let newQuantity = parseInt($(this).val());

                if (newQuantity < 1 || isNaN(newQuantity)) {
                    alert("Quantity must be at least 1");
                    $(this).val(1);
                    newQuantity = 1;
                }

                updateCartQuantity(productName, newQuantity, $row); // Pass row for updating
                updateCartPage();
            });






            updateTotalPrice();




            // Clear Cart
            $("#clear-cart").click(function () {
                $.ajax({
                    url: "{{ route('cart.clear') }}",
                    method: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function () {
                        localStorage.removeItem("cart");
                        location.reload();
                    }
                });
                updateTotalPrice();
            });


            $("#proceed-to-checkout").click(function () {
                window.location.href = "{{ route('checkout') }}";
            });
        });

    </script>
@endsection
