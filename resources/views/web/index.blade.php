@extends('layouts.app')

@section('title', 'Home Page')

@section('content')

    @php
        use Jenssegers\Agent\Agent;
        $agent = new Agent();
    @endphp

    @if ($agent->isMobile() || $agent->isTablet())
        <section class="products-container">
            <div class="container">
                <div class="row">
                    <div class="mobile-layout col-12">
                        <div class="banner-img-mobile">
                            <img src="{{asset('public/assets/Mobile-Version.webp')}}"/>
                        </div>

                     
                        <!-- Bulk order section -->
                        <div class="bulk-orders">
                            <h3>Need Over 100 boxes?</h3>
                                <button class="bulk-partner-btn">
                                    <a href="mailto:partnerships@boxcity.com">E-mail Our Partnerships Team</a>
                                </button>
                        </div>
                        <div style="position: relative">
                        <div class="mobile-accordion-container">

                        <div class="accordion mobile-accordion" id="accordionExample">
                            <!-- Length filter -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        Length
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne">
                                    <div class="accordion-body">
                                        <ul>
                                            @foreach ($sizes as $size)
                                                @if ($size['count'] > 0)
                                                    <li
                                                        data-min="{{ $size['min'] }}"
                                                        data-max="{{ $size['max'] }}"
                                                        style="cursor: pointer;"
                                                    >
                                    <span class="length">
                                    {{ $size['label'] }} ({{ $size['count'] }})</span>

                                                        <span class="remove-length" style="display: none;">&times;</span>

                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Strength filter -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Type
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
                                    <div class="accordion-body">
                                        @include('partials.category-accordion', ['categories' => $categories])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                        <div class="product-table">
                        </div>
                    </div>
 @if ($agent->isMobile() || $agent->isTablet())
                           <div class="offer">
                            <h3>Why Choose<br/>Box City? </h3>
                            <ul>
                                <li>Lowest Price Guarantee</li>
                                <li>Same-Day Local Pickup</li>
                                <li>Next Day Delivery</li>
                                <li>Trusted By Business Owners</li>
                            </ul>

                            <h4>Call Us For All Your Packing & Shipping Needs</h4>
                            <h3><a href="tel:8009926924">(800) 992-6924</a></h3>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>


        <style>
            .bulk-orders {
                margin-bottom: 30px;
            }

            .products-container {
                margin-top: 16px;
            }

            .cart-icon {
                z-index: 999999;
            }

            .banner-img-mobile{
                display: block;
            }

        </style>


    @else
        <section class="products-container">
            <div class="container">
                <div class="row">
                    <!-- Side Pane -->
                    @include('partials.side_pane')

                    <div class="col-lg-9 col-sm-12">
                        <div class="banner-img">
                            <img src="{{asset('public/assets/Full-Banner.webp')}}"/>
                        </div>
                        <!-- Bulk order section -->
                        <div class="bulk-orders">
                            <h3>
                                For orders exceeding 100 boxes, reach out to our partnerships team at
                                <a href="mailto:partnerships@boxcity.com">partnerships@boxcity.com</a>
                            </h3>
                        </div>

                        <!-- Product table -->

                        <div class="product-table">
                        </div>
                    </div>

                </div>
            </div>
        </section>
    @endif
    <!-- Cart Panel -->
    <div class="cart-panel">
        <h3>Shopping Cart</h3>
        <ul id="cart-list"></ul>
        <button id="clear-cart">Clear Cart</button>
    </div>

    <!-- Quote Request Form -->
    @include('partials.quote_from')

    <!-- Testimonials Section -->
    @include('partials.testimonial')

    <!-- Store Location Tabs -->
    @include('partials.store_location')
    <script>

        function updateProductCount() {
            let rowCount = 0;

            $('#product-table tbody tr').each(function () {
                const cellText = $(this).find('td').text().trim();
                if (cellText !== 'No Products Found') {
                    rowCount++;
                }
            });

            $('.product-count').html(`Showing ${rowCount} products`);
        }


        $(document).on('click', '.filters, .length, .remove-filter, .remove-length', function (e) {
            const $clicked = $(this);
            setTimeout(function () {
                if ($clicked.hasClass('filters')) {
                    // Remove active class from all filters
                    $('.filters').removeClass('active');
                    $('.filters').each(function () {
                        const $this = $(this);
                        const btn = $this.closest('.accordion-button');
                        btn.removeClass('active-highlight');

                        if (!$this.is($clicked)) {
                            btn.css('background-color', '');
                            btn.find('.remove-filter').hide();
                        }
                    });

                    // Set active class on clicked filter
                    $clicked.addClass('active');
                    const $btn = $clicked.closest('.accordion-button');
                    $btn.addClass('active-highlight');

                    $btn.find('.remove-filter').show();

                }


                if ($clicked.hasClass('remove-filter')) {
                    const $btn = $clicked.closest('.accordion-button');
                    $btn.find('.filters').removeClass('active');
                    $btn.removeClass('active-highlight');

                    $clicked.hide();
                    updateProductCount();
                }


                if ($clicked.hasClass('length')) {
                    $('.length').removeClass('active');
                    $('.length').each(function () {
                        $(this).closest('li').css('background-color', '');
                        $(this).siblings('.remove-length').hide();
                    });

                    $clicked.addClass('active');
                    $clicked.closest('.accordion-item').find('.accordion-button').addClass('active-highlight');
                    $clicked.closest('li').css('background-color', '#FFE175');
                    $clicked.siblings('.remove-length').show();
                    updateProductCount();
                }


                if ($clicked.hasClass('remove-length')) {
                    const $li = $clicked.closest('li');
                    $li.find('.length').removeClass('active');
                    $li.removeClass('active-highlight');
                    $li.css('background-color', '');
                    $clicked.hide();
                    updateProductCount();
                }

                loadProducts();
            }, 100);
        });

        $(document).ready(function () {
            loadProducts();

            $(document).on('click', '.pagination-links a', function (e) {
                e.preventDefault();

                let url = new URL($(this).attr('href'));
                let page = url.searchParams.get('page');
                page = (page && !isNaN(page)) ? parseInt(page) : 1;
                loadProducts(page);

                $('html, body').animate({
                    scrollTop: $('#product-table').offset().top
                }, 1500);

            });
        });


        function loadProducts(page = 1){
            let $len = $('.length.active').closest('li');
            let $filter = $('.filters.active');
            let url = "{{route('category.level')}}?page="+page;

            if ($len.length) {
                let min = $len.data('min');
                let max = $len.data('max');
                url = url+"&min="+min+"&max="+max;
            }

            if ($filter.length) {
                let category_id = $filter.data('category-id');
                url = url+"&categoryId="+category_id;
            }

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    if (response.product_html) {
                        $('.product-table').html(response.product_html);
                        applyDataLabels();

                    }
                },
                error: function () {
                    alert('Pagination failed. Please try again.');
                }
            });
        }

    </script>
@endsection
