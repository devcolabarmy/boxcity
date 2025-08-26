@extends('layouts.app')

@section('title', 'Checkout')

@section('content')

{{--    <script src="https://www.paypal.com/sdk/js?client-id=AR6e2wTLuLlHurDdYKlnbsWymNsaithT5ASiSrt0sC2cDcPCj6htPJQAdpGeyzcaVMtNR15Nw9YVO9Mv&currency=USD"></script>--}}
<script src="https://js.stripe.com/v3/"></script>


<meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container checkout-container">
        {{-- Page Heading --}}
        <h2>Checkout</h2>

        <div class="row">
            {{-- Billing Details Section --}}
            <div class="col-md-7">
                <h3>Billing Details</h3>

                <form id="checkout-form">
                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="full-name">Full Name</label>
                        <input type="text" id="full-name" name="full-name" class="form-control" required>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                        <span id="email-error" style="color: red; font-size: 13px; font-family: 'gilroy-semibolduploaded_file';"></span>
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                        <span id="phone-error" style="color: red; font-size: 13px; font-family: 'gilroy-semibolduploaded_file';"></span>
                    </div>

                    <!-- Shipping Method -->
                    <div class="form-group">
                        <label for="shipping-method">Select Shipping Method</label>
                        <div class="custom-select-wrapper">
                        <select id="shipping-method" name="shipping-method" class="form-control">
                            <option disabled selected hidden>Select Shipping Method</option>
                            <option value="Pickup method" name="Pick up" >Pick Up</option>
                            <option value="Delivery details" name="Delivery">Delivery</option>
                        </select>
                    </div>
                    </div>

                    <!-- PICKUP SECTION -->
                    <div id="pickup-section" class="d-none">

                        <!-- Location -->
                        <div class="form-group">
                            <label for="pickup-location">Select Store Location</label>
                            <div class="custom-select-wrapper">
                            <select id="pickup-location" name="pickup-location" class="form-control">
                                <option disabled selected hidden>Select a Location</option>
                                <option value="#1 Box City Van Nuys" name="Pickup-VanNuys">Van Nuys</option>
                                <option value="#2 Box City North Hollywood" name="Pickup-NorthHollywood">North Hollywood</option>
                                <option value="#3 Box City Westwood" name="Pickup-WestLosAngeles">West Los Angeles</option>
                                <option value="#4 Box City Valencia" name="Pickup-Valencia">Valencia</option>
                                <option value="#5 Box City Pasadena" name="Pickup-Pasadena">Pasadena</option>
                                <option value="#6 Box City Marina" name="Pickup-MarinaDelRey">Marina Del Rey</option>
                                <option value="#7 Box City Canoga Park" name="Pickup-CanogaPark">Canoga Park</option>
                                <option value="#8 Box City - Glendale" name="Pickup-Glendale">Glendale</option>
                                <option value="#9 Box City Azusa" name="Pickup-Azusa">Azusa</option>
                            </select>
                        </div>
                        </div>

                        <!-- Pickup Date -->
                        <div class="form-group">
                            <label for="pickup-date">Pickup Date</label>
                            <input type="date" id="pickup-date" name="pickup-date" class="form-control">
                        </div>

                        <!-- Country, State, City, Postal Code -->
                        <div class="row form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup-country">Country</label>
                                    <div class="custom-select-wrapper">
                                    <select id="pickup-country" name="pickup-country" class="form-control">
                                        <option disabled selected>Select Country</option>
                                        <option value="US">United States</option>
                                    </select>
                                </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup-state">State</label>
                                    <div class="custom-select-wrapper">
                                    <select id="pickup-state" name="pickup-state" class="form-control">
                                        <option disabled selected>Select State</option>
                                    </select>
                                </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup-city">City</label>
                                    <div class="custom-select-wrapper">
                                    <select id="pickup-city" name="pickup-city" class="form-control">
                                        <option disabled selected>Select City</option>
                                    </select>
                                </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup-postal">Postal Code</label>
                                    <input type="text" id="pickup-postal" name="pickup-postal" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label for="pickup-address">Address</label>
                            <textarea id="pickup-address" name="pickup-address" class="form-control"></textarea>
                        </div>
                    </div>

                    <!-- DELIVERY SECTION -->
                    <div id="delivery-section" class="d-none">
                        <div id="shipping-address">

                            <!-- Country, State, City, Zip -->
                            <div class="row form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping-country">Country</label>
                                        <div class="custom-select-wrapper">
                                        <select id="shipping-country" name="shipping-country" class="form-control">
                                            <option disabled selected>Select Country</option>
                                            <option value="US">United States</option>
                                        </select>
                                    </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping-state">State</label>
                                        <div class="custom-select-wrapper">
                                        <select id="shipping-state" name="shipping-state" class="form-control">
                                            <option disabled selected>Select State</option>
                                        </select>
                                    </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping-city">City</label>
                                        <div class="custom-select-wrapper">
                                        <select id="shipping-city" name="shipping-city" class="form-control">
                                            <option disabled selected>Select City</option>
                                        </select>
                                    </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping-zip">Zip Code</label>
                                        <input type="text" id="shipping-zip" name="shipping-zip" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="delivery-location">Select Store Location</label>
                                <div class="custom-select-wrapper">
                                <select id="delivery-location" name="pickup-location" class="form-control">
                                    <option disabled selected hidden>Select a Location</option>
                                    <option value="Local delivery (#1 Box City Van Nuys)" name="Delivery-VanNuys">Van Nuys</option>
                                    <option value="Local delivery (#2 Box City North Hollywood)" name="Delivery-NorthHollywood">North Hollywood</option>
                                    <option value="Local delivery (#3 Box City Westwood)" name="Delivery-WestLosAngeles">West Los Angeles</option>
                                    <option value="Local delivery (#4 Box City Valencia)" name="Delivery-Valencia">Valencia</option>
                                    <option value="Local delivery (#5 Box City Pasadena)" name="Delivery-Pasadena">Pasadena</option>
                                    <option value="Local delivery (#6 Box City Marina)" name="Delivery-MarinaDelRey">Marina Del Rey</option>
                                    <option value="Local delivery (#7 Box City Canoga Park)" name="Delivery-CanogaPark">Canoga Park</option>
                                    <option value="Local delivery (#8 Box City - Glendale)" name="Delivery-Glendale">Glendale</option>
                                    <option value="Local delivery – Azusa" name="Delivery-Azusa">Azusa</option>
                                </select>
                            </div>
                            </div>


                            <!-- Address -->
                            <div class="form-group">
                                <label for="address-text">Address</label>
                                <textarea id="address-text" name="shipping-address-text" class="form-control"></textarea>
                            </div>
                        </div>


                        <div class="form-group">
                        <label class="form-check-label" for="delivery-address"> Delivery Address</label>
                        </div>

                        <div class="form-check mb-2 mt-2">

                            <input class="form-check-input" type="checkbox" id="same-as-billing">
                            <label class="form-check-label" for="same-as-billing">
                                Same as Billing Address
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="shipping-address-text">Shipping Address</label>
                            <textarea id="shipping-address-text" name="shipping-address-text" class="form-control"></textarea>
                        </div>

                    </div>



                    <!-- Order Button and Total -->
                    <div class="btn-container">
                        <h4 class="checkout-total">Total: $0.00</h4>
                        <button id="place-order" type="submit" class="btn btn-success btn-lg">Place Order</button>
                    </div>

{{--                    <div id="paypal-button-container"></div>--}}

                    <button id="stripe-checkout-button" class="stripe-button">Pay with Stripe</button>
                </form>
            </div>

            {{-- Order Summary Section --}}
            <div class="col-md-5">
                <h3>Order Summary</h3>

                <table class="table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                    </thead>
                    <tbody id="checkout-cart-list">
                    <tr><td colspan="3">Loading cart...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function () {

            function applyGroupPricingForCheckout(cart) {
                const grouped = {};

                // Group items by group key
                cart.forEach(item => {
                    const groupKey = getGroupKey(item.product); // make sure this function is defined
                    if (!grouped[groupKey]) grouped[groupKey] = [];
                    grouped[groupKey].push(item);
                });

                // Apply group pricing based on total group quantity
                for (const groupKey in grouped) {
                    const groupItems = grouped[groupKey];
                    const groupTotalQty = groupItems.reduce((sum, i) => sum + i.quantity, 0);

                    groupItems.forEach(i => {
                        const retail = parseFloat(i.retailPrice) || 0;
                        const bulk1 = parseFloat(i.priceBulkOne) || retail;
                        const bulk2 = parseFloat(i.priceBulkTwo) || bulk1;
                        const bulk3 = parseFloat(i.priceBulkThree) || bulk2;

                        if (groupTotalQty >= 100) {
                            i.price = bulk3;
                        } else if (groupTotalQty >= 50) {
                            i.price = bulk2;
                        } else if (groupTotalQty >= 12) {
                            i.price = bulk1;
                        } else {
                            i.price = retail;
                        }
                    });
                }

                // Update localStorage with new prices
                localStorage.setItem('cart', JSON.stringify(cart));
            }



            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            applyGroupPricingForCheckout(cart);
            let $cartList = $("#checkout-cart-list");
            let total = 0;

            $cartList.empty();
            if (cart.length === 0) {
                $cartList.append("<tr><td colspan='3'>Your cart is empty</td></tr>");
            } else {
                cart.forEach((item, index) => {
                    // Get the price from the existing table if available
                    let priceCell = document.querySelectorAll('tr[data-label="Total Price :"] td')[index];
                    let itemTotal = 0;

                    if (priceCell) {
                        let priceText = priceCell.textContent.trim().replace('$', '');
                        itemTotal = parseFloat(priceText);
                    } else {
                        // fallback to manual calculation
                        itemTotal = item.price * item.quantity;
                    }

                    var productDetailUrl = "{{ route('product.detail', ['id' => '000']) }}".replace('000', item.productId);
                    total += itemTotal;

                    $cartList.append(`
            <tr>
                <td><a href="${productDetailUrl}" target="_blank">${item.product}</a></td>
                <td>${item.quantity}</td>
                <td>$${itemTotal.toFixed(2)}</td>
            </tr>
        `);
                });
            }
            $(".checkout-total").text(`Total: $${total.toFixed(2)}`);


            function submitOrderToEcwid() {
                return new Promise((resolve, reject) => {
                event.preventDefault();
                let fullName = $("#full-name").val();
                let email = $("#email").val();
                let phone = $("#phone").val();
                let shipmethod = $('#shipping-method').val();
                let isPickup = (shipmethod === 'Pickup method');
                let pickupDate = $('#pickup-date').val();
                let formattedPickupDate = '';
                if (isPickup) {
                    let pickupDate = $('#pickup-date').val();
                    if (pickupDate) {
                        let date = new Date(pickupDate);
                        formattedPickupDate = date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                }


                let deliveryAddress = '';
                if (!isPickup) {
                    if ($('#same-as-billing').is(':checked')) {
                        deliveryAddress = $('#address-text').val();
                    } else {
                        deliveryAddress = $('#shipping-address-text').val();
                    }
                }



                let address, country, state, city, postcode, Location;
                //Pickup values
                if (isPickup) {
                    address = $("#pickup-address").val();
                    country = $("#pickup-country").val();
                    state = $("#pickup-state").val();
                    city = $("#pickup-city").val();
                    postcode = $("#pickup-postal").val();
                    Location = $('#pickup-location').val();

                    console.log("Pickup Address:", address);
                    console.log("Pickup Country:", country);
                    console.log("Pickup State:", state);
                    console.log("Pickup City:", city);
                    console.log("Pickup Postal Code:", postcode);
                    console.log("Pickup Location:", Location);
                    console.log("Pickup Date:", formattedPickupDate);


                } else {
                    // Delivery values
                    address = deliveryAddress;
                    country = $("#shipping-country").val();
                    state = $("#shipping-state").val();
                    city = $("#shipping-city").val();
                    postcode = $("#shipping-zip").val();
                    Location = $('#delivery-location').val();

                    console.log("Delivery Address:", address);
                    console.log("Delivery Country:", country);
                    console.log("Delivery State:", state);
                    console.log("Delivery City:", city);
                    console.log("Delivery Postal Code:", postcode);
                    console.log("Delivery Location:", Location);
                }



                if (!fullName || !email || !phone || !address || !country || !state || !city || !postcode) {
                    alert("Please fill all fields before placing the order.");
                    return;
                }

                // Retrieve cart items from localStorage
                let cartItems = JSON.parse(localStorage.getItem("cart")) || [];

                if (cartItems.length === 0) {
                    alert("Your cart is empty. Add items before placing an order.");
                    return;
                }

                let totalAmount = cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);

                let orderData = {
                    subtotal: totalAmount,
                    subtotalWithoutTax: totalAmount,
                    total: totalAmount,
                    totalWithoutTax: totalAmount,
                    giftCardRedemption: 0,
                    totalBeforeGiftCardRedemption: totalAmount,
                    giftCardDoubleSpending: false,
                    email: email,
                    paymentMethod: "Credit or Debit card.",
                    paymentSubtype: "manual",
                    tax: 0,
                    customerTaxExempt: false,
                    customerTaxId: "",
                    customerTaxIdValid: false,
                    b2b_b2c: "b2c",
                    reversedTaxApplied: false,
                    customerRequestedInvoice: false,
                    customerFiscalCode: "",
                    electronicInvoicePecEmail: "",
                    electronicInvoiceSdiCode: "",
                    paymentStatus: "AWAITING_PAYMENT",
                    fulfillmentStatus: "AWAITING_PROCESSING",
                    createDate: new Date().toISOString(),
                    updateDate: new Date().toISOString(),
                    createTimestamp: Math.floor(Date.now() / 1000),
                    updateTimestamp: Math.floor(Date.now() / 1000),
                    items: cartItems.map(item => ({

                            productId: item.productId,
                            name: item.product,
                            categoryId: item.categoryId || null,
                            price: item.price,
                            priceWithoutTax: item.price,
                            productPrice: item.price,
                            sku: item.sku || "",
                            quantity: item.quantity,
                            shortDescription: item.shortDescription || "",
                            shortDescriptionTranslated: {
                                en: item.shortDescription || "",
                                es: ""
                            },

                        tax: 0,
                        shipping: 0,
                        quantityInStock: item.quantityInStock || 0,
                        name: item.product,
                        nameTranslated: {
                            en: item.name,
                            es: ""
                        },
                        isShippingRequired: true,
                        weight: item.weight || 1,
                        trackQuantity: true,
                        fixedShippingRateOnly: false,
                        imageUrl: item.imageUrl || "",
                        smallThumbnailUrl: item.smallThumbnailUrl || "",
                        hdThumbnailUrl: item.hdThumbnailUrl || "",
                        fixedShippingRate: 0,
                        digital: false,
                        productAvailable: true,
                        couponApplied: false,
                        taxes: [],
                        dimensions: {
                            length: item.length || 0,
                            width: item.width || 0,
                            height: item.height || 0
                        },
                        discounts: [],
                        discountsAllowed: true,
                        taxable: true,
                        giftCard: false,
                        recurringTaxIds: [],
                        isCustomerSetPrice: false,
                        externalReferenceId: item.externalReferenceId || "",
                        attributes: item.attributes || []
                    })),
                    billingPerson: {
                        name: fullName,
                        firstName: fullName.split(" ")[0] || "",
                        lastName: fullName.split(" ").slice(1).join(" ") || "",
                        street: address,
                        phone: phone,
                        countryCode: "US",
                        postalCode: postcode,
                        city: city,
                        stateOrProvinceCode: "CA",
                        stateOrProvinceName: state
                    },
                    shippingPerson: {
                        name: fullName,
                        firstName: fullName.split(" ")[0] || "",
                        lastName: fullName.split(" ").slice(1).join(" ") || "",
                        phone: phone
                    },
                    shippingOption: {
                        shippingMethodName: Location,
                        shippingRate: 0,
                        shippingRateWithoutTax: 0,
                        isPickup: false,
                        fulfillmentType: isPickup ? "PICKUP" : "DELIVERY",
                        isShippingLimit: false
                    },
                    handlingFee: {
                        name: "Handling Fee",
                        value: 0,
                        valueWithoutTax: 0,
                        description: "",
                        taxes: []
                    },

                    predictedPackage: [],
                    shippingLabelAvailableForShipment: false,
                    shipments: [],
                    refunds: [],
                    hidden: false,
                    privateAdminNotes: "",
                    acceptMarketing: false,
                    disableAllCustomerNotifications: false,
                    externalFulfillment: false,
                    pricesIncludeTax: false,
                    orderExtraFields: [{
                        id: "",
                        value:formattedPickupDate,
                        title: "Choose Delivery or Pick Up day",
                        orderDetailsDisplaySection: "billing_info",
                        orderBy: "",
                        showInNotifications: false,
                        showInInvoice: false,
                        saveToCustomerProfile: false
                    }],

                    discountInfo: [],
                    creditCardStatus: {
                        avsMessage: "not checked",
                        cvvMessage: "not checked"
                    },
                    invoices: [],
                    lang: "en"
                };


                $.ajax({
                    url: "https://app.ecwid.com/api/v3/109333282/orders",
                    type: "POST",
                    data: JSON.stringify(orderData),
                    contentType: "application/json",
                    headers: {
                        "Authorization": "Bearer public_TKetLbQHRiCT4zFeDFBzzncr3rWjzA9E"
                    },
                    beforeSend: function () {
                        console.log("Sending order request to Ecwid API...");
                    },
                    success: function (response) {
                        console.log("Order placed successfully!", response);
                        localStorage.setItem("ecwidOrderId", response.id);
                        {{--window.location.href = "{{route('checkout.thankyou')}}";--}}
                        localStorage.removeItem("cart");

                        resolve(response.id);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        console.error("Full Error Response:", xhr.responseText);
                        alert("Error: " + (xhr.responseJSON?.error || "Something went wrong."));
                        reject(error);
                    }
                });

                });
            }



            let citiesByState = {
                "Alabama": ["Birmingham", "Montgomery", "Mobile", "Huntsville"],
                "Alaska": ["Anchorage", "Juneau", "Fairbanks"],
                "Arizona": ["Phoenix", "Tucson", "Mesa"],
                "Arkansas": ["Little Rock", "Fort Smith", "Fayetteville"],
                "California": ["Los Angeles", "San Francisco", "San Diego", "Sacramento"],
                "Colorado": ["Denver", "Colorado Springs", "Aurora"],
                "Connecticut": ["Bridgeport", "New Haven", "Stamford"],
                "Delaware": ["Wilmington", "Dover"],
                "Florida": ["Miami", "Orlando", "Tampa", "Jacksonville"],
                "Georgia": ["Atlanta", "Savannah", "Augusta"],
                "Hawaii": ["Honolulu", "Hilo"],
                "Idaho": ["Boise", "Idaho Falls"],
                "Illinois": ["Chicago", "Springfield", "Naperville", "Rockford"],
                "Indiana": ["Indianapolis", "Fort Wayne", "Evansville"],
                "Iowa": ["Des Moines", "Cedar Rapids"],
                "Kansas": ["Wichita", "Topeka"],
                "Kentucky": ["Louisville", "Lexington"],
                "Louisiana": ["New Orleans", "Baton Rouge", "Shreveport"],
                "Maine": ["Portland", "Augusta"],
                "Maryland": ["Baltimore", "Annapolis"],
                "Massachusetts": ["Boston", "Worcester"],
                "Michigan": ["Detroit", "Grand Rapids", "Lansing"],
                "Minnesota": ["Minneapolis", "Saint Paul"],
                "Mississippi": ["Jackson", "Biloxi"],
                "Missouri": ["Kansas City", "St. Louis", "Springfield"],
                "Montana": ["Billings", "Missoula"],
                "Nebraska": ["Omaha", "Lincoln"],
                "Nevada": ["Las Vegas", "Reno"],
                "New Hampshire": ["Manchester", "Concord"],
                "New Jersey": ["Newark", "Jersey City", "Trenton"],
                "New Mexico": ["Albuquerque", "Santa Fe"],
                "New York": ["New York City", "Buffalo", "Rochester", "Albany"],
                "North Carolina": ["Charlotte", "Raleigh", "Durham"],
                "North Dakota": ["Fargo", "Bismarck"],
                "Ohio": ["Columbus", "Cleveland", "Cincinnati"],
                "Oklahoma": ["Oklahoma City", "Tulsa"],
                "Oregon": ["Portland", "Salem"],
                "Pennsylvania": ["Philadelphia", "Pittsburgh", "Harrisburg"],
                "Rhode Island": ["Providence"],
                "South Carolina": ["Charleston", "Columbia"],
                "South Dakota": ["Sioux Falls", "Rapid City"],
                "Tennessee": ["Nashville", "Memphis", "Knoxville"],
                "Texas": ["Houston", "Dallas", "Austin", "San Antonio"],
                "Utah": ["Salt Lake City", "Provo"],
                "Vermont": ["Burlington", "Montpelier"],
                "Virginia": ["Virginia Beach", "Richmond", "Norfolk"],
                "Washington": ["Seattle", "Spokane", "Tacoma"],
                "West Virginia": ["Charleston", "Huntington"],
                "Wisconsin": ["Milwaukee", "Madison"],
                "Wyoming": ["Cheyenne", "Casper"]
            };


            $(document).ready(function () {
                const pickupStateDropdown = $("#pickup-state");
                const pickupCityDropdown = $("#pickup-city");

                const deliveryStateDropdown = $("#shipping-state");
                const deliveryCityDropdown = $("#shipping-city");

                // Populate both state dropdowns
                Object.keys(citiesByState).forEach(function (state) {
                    pickupStateDropdown.append(`<option value="${state}">${state}</option>`);
                    deliveryStateDropdown.append(`<option value="${state}">${state}</option>`);
                });

                // On pickup state change
                pickupStateDropdown.change(function () {
                    const selectedState = $(this).val();
                    pickupCityDropdown.empty().append('<option value="" disabled selected>Select City</option>');

                    if (citiesByState[selectedState]) {
                        citiesByState[selectedState].forEach(function (city) {
                            pickupCityDropdown.append(`<option value="${city}">${city}</option>`);
                        });
                    }
                });

                // On delivery state change
                deliveryStateDropdown.change(function () {
                    const selectedState = $(this).val();
                    deliveryCityDropdown.empty().append('<option value="" disabled selected>Select City</option>');

                    if (citiesByState[selectedState]) {
                        citiesByState[selectedState].forEach(function (city) {
                            deliveryCityDropdown.append(`<option value="${city}">${city}</option>`);
                        });
                    }
                });
            });




            {{--paypal.Buttons({--}}
            {{--    createOrder: function(data, actions) {--}}
            {{--        const totalElement = document.querySelector('.checkout-total');--}}
            {{--        const email = $("#email").val().trim();--}}
            {{--        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;--}}
            {{--        console.log(email);--}}

            {{--        if (!emailPattern.test(email)) {--}}
            {{--            $("#email-error").text("Please enter a valid email address.");--}}
            {{--            return Promise.reject("Invalid email");--}}
            {{--        } else {--}}
            {{--            $("#email-error").text("");--}}
            {{--        }--}}
            {{--        if (!totalElement) {--}}
            {{--            throw new Error("Checkout total element not found.");--}}
            {{--        }--}}

            {{--        // Clean and parse the price--}}
            {{--        const rawAmount = totalElement.innerText.replace(/[^0-9.]/g, '');--}}
            {{--        const amount = parseFloat(rawAmount);--}}

            {{--        if (isNaN(amount) || amount <= 0) {--}}
            {{--            throw new Error("Invalid total amount.");--}}
            {{--        }--}}
            {{--        submitOrderToEcwid();--}}
            {{--        return fetch('{{route("paypal.create")}}', {--}}
            {{--            method: 'POST',--}}
            {{--            headers: {--}}
            {{--                'Content-Type': 'application/json',--}}
            {{--                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')--}}
            {{--            },--}}
            {{--            body: JSON.stringify({--}}
            {{--                amount: amount,--}}
            {{--                email: email--}}
            {{--            })--}}
            {{--        })--}}
            {{--            .then(res => {--}}
            {{--                if (!res.ok) {--}}
            {{--                    throw new Error("Network response was not ok");--}}
            {{--                }--}}
            {{--                return res.json();--}}
            {{--            })--}}
            {{--            .then(data => {--}}
            {{--                if (!data.orderID) {--}}
            {{--                    throw new Error("Order ID not found in response");--}}
            {{--                }--}}
            {{--                return data.orderID;--}}
            {{--            });--}}


            {{--    },--}}

            {{--    onApprove: function(data, actions) {--}}
            {{--        return fetch(`{{ route("paypal.capture") }}`)--}}
            {{--            .then(res => res.json())--}}
            {{--            .then(result => {--}}
            {{--                console.log('Payment captured:', result);--}}

            {{--                // Get the order ID from localStorage--}}
            {{--                const ecwidOrderId = localStorage.getItem("ecwidOrderId");--}}

            {{--                if (ecwidOrderId) {--}}
            {{--                    fetch(`https://app.ecwid.com/api/v3/109333282/orders/${ecwidOrderId}`, {--}}
            {{--                        method: 'PUT',--}}
            {{--                        headers: {--}}
            {{--                            'Authorization': 'Bearer secret_Asd3RgYgyNkGaKhN3hke67RHyAkigTXG',--}}
            {{--                            'Content-Type': 'application/json'--}}
            {{--                        },--}}
            {{--                        body: JSON.stringify({--}}
            {{--                            paymentStatus: "PAID"--}}
            {{--                        })--}}
            {{--                    })--}}
            {{--                        .then(updateRes => updateRes.json())--}}
            {{--                        .then(updateResult => {--}}
            {{--                            console.log("Ecwid order marked as PAID", updateResult);--}}

            {{--                            // Clear order ID--}}
            {{--                            localStorage.removeItem("ecwidOrderId");--}}

            {{--                            // Redirect to thank you page--}}
            {{--                            window.location.href = "{{route('checkout.thankyou')}}";--}}
            {{--                        })--}}
            {{--                        .catch(err => {--}}
            {{--                            console.error("Failed to update Ecwid order status:", err);--}}
            {{--                            alert("Order placed but status update failed. Please contact support.");--}}
            {{--                        });--}}
            {{--                } else {--}}
            {{--                    console.warn("Missing Ecwid order ID");--}}
            {{--                    alert("Payment succeeded but order ID is missing.");--}}
            {{--                }--}}
            {{--            });--}}
            {{--    },--}}

            {{--    onCancel: function (data) {--}}
            {{--        console.log("Payment cancelled by user", data);--}}
            {{--        window.location.reload(); // 🔁--}}
            {{--    }--}}

            {{--}).render('#paypal-button-container');--}}

            document.getElementById("stripe-checkout-button").addEventListener("click", async function (e) {
                e.preventDefault();

                const totalElement = document.querySelector('.checkout-total');
                const email = document.getElementById("email").value.trim();
                const phone = document.getElementById("phone").value.trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const phonePattern = /^(\+1\s?)?(\([0-9]{3}\)|[0-9]{3})[-\s]?[0-9]{3}[-\s]?[0-9]{4}$/;
                let isValid = true;

                if (!phonePattern.test(phone)) {
                    document.getElementById("phone-error").innerText = "Please enter a valid phone number.";
                    isValid = false;
                } else {
                    document.getElementById("phone-error").innerText = "";
                }

                if (!emailPattern.test(email)) {
                    document.getElementById("email-error").innerText = "Please enter a valid email address.";
                    isValid = false;
                } else {
                    document.getElementById("email-error").innerText = "";
                }

                if (!isValid) return;

                if (!totalElement) {
                    alert("Checkout total element not found.");
                    return;
                }

                // Parse amount in dollars
                const rawAmount = totalElement.innerText.replace(/[^0-9.]/g, '');
                const amount = parseFloat(rawAmount);

                if (isNaN(amount) || amount <= 0) {
                    alert("Invalid total amount.");
                    return;
                }

                try {
                    // 🔹 Save Ecwid order first and wait for response
                    const ecwidOrderId = await submitOrderToEcwid();
                    console.log("Ecwid Order ID (numeric):", ecwidOrderId);

                    if (!ecwidOrderId) {
                        alert("Failed to create Ecwid order.");
                        return;
                    }

                    // 🔹 Call backend to create Stripe Checkout Session
                    let response = await fetch("{{ route('stripe.create') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            amount: amount,        // in dollars
                            email: email,
                            order_id: ecwidOrderId // 🔹 send numeric Ecwid order ID
                        })
                    });

                    let data = await response.json();

                    if (data.error) {
                        console.error("Stripe session error:", data);
                        alert("Stripe error: " + data.error);
                        return;
                    }

                    // 🔹 Redirect to Stripe Checkout
                    const stripe = Stripe("{{ config('stripe.key') }}");
                    const result = await stripe.redirectToCheckout({ sessionId: data.session_id });

                    if (result.error) {
                        alert(result.error.message);
                    }
                } catch (err) {
                    console.error("Stripe Checkout failed:", err);
                    alert("Payment initialization failed. Please try again.");
                }
            });




            $('#shipping-method').on('change', function () {
                const method = $(this).val();

                // Hide both sections first
                $('#pickup-section, #delivery-section').addClass('d-none');

                if (method === 'Pickup method') {
                    $('#pickup-section').removeClass('d-none');
                } else if (method === 'Delivery details') {
                    $('#delivery-section').removeClass('d-none');
                    $('#same-as-billing').prop('checked', true).trigger('change');
                }
            });


            $('#shipping-address-text').closest('.form-group').hide();
            $('#same-as-billing').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#shipping-address-text').closest('.form-group').hide();
                } else {
                    $('#shipping-address-text').closest('.form-group').show();
                }
            });
        });

    </script>

@endsection
