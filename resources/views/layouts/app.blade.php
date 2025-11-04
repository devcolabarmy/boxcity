<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Tag Manager -->
    <script>
    // (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    //             new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    //         j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    //         'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    //     })(window,document,'script','dataLayer','GTM-T77PQBGD');
    </script>
    <!-- End Google Tag Manager -->
    
    
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MB433BJ4');</script>
<!-- End Google Tag Manager -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" integrity="sha512-gxWow8Mo6q6pLa1XH/CcH8JyiSDEtiwJV78E+D+QP0EVasFs8wKXq16G8CLD4CJ2SnonHr4Lm/yY2fSI2+cbmw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>@yield('title') </title>
    <link href="{{ asset('public/css/style.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ asset('public/assets/Favicon-BoxCity.png/')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.css"
          integrity="sha512-6lLUdeQ5uheMFbWm3CP271l14RsX1xtx+J5x2yeIDkkiBpeVTNhTqijME7GgRKKi6hCqovwCoBTlRBEC20M8Mg=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    
    
    <script>var $wc_load=function(a){return  JSON.parse(JSON.stringify(a))},$wc_leads=$wc_leads||{doc:{url:$wc_load(document.URL),ref:$wc_load(document.referrer),search:$wc_load(location.search),hash:$wc_load(location.hash)}};</script>
<script src="//s.ksrndkehqnwntyxlhgto.com/140812.js"></script>
    
</head>


<!-- Google tag (gtag.js) --> <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16836195263"></script> <script>   window.dataLayer = window.dataLayer || [];   function gtag(){dataLayer.push(arguments);}   gtag('js', new Date());    gtag('config', 'AW-16836195263'); </script>

<header>
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <!-- Logo -->
            <div class="col-md-3 col-6 ">
                <a href="{{route('home')}}">
                    <img src="{{ asset('public/assets/header-logo.png')}}" alt="logo">
                </a>
            </div>


            <div class="col-md-9 col-6 d-flex justify-content-end align-items-center">
                <nav class="d-none d-lg-block mb-hide">
                    <ul class="sub-menu d-flex justify-content-end">
                        <li><a href="javascript:void(0)">Products</a>
                            <ul class="primary-sub-menu">
                                <li><a href="{{ config('app.site_url') }}gift-supplies">Gift Supplies</a></li>
                                <li><a href="{{ config('app.site_url') }}bags-and-pouches">Bags And Pouches</a></li>
                                <li><a href="{{ config('app.site_url') }}corrugated-boxes">Corrugated Boxes</a></li>
                                <li><a href="{{ config('app.site_url') }}mailing-items">Mailing Items</a></li>
                                <li><a href="{{ config('app.site_url') }}">Packing Supplies</a></li>
                                <li><a href="{{ config('app.site_url') }}moving-boxes-and-supplies">Moving Boxes And
                                        Supplies</a></li>
                            </ul>

                        </li>

                        <li><a href="javascript:void(0)">Services</a>
                            <ul class="primary-sub-menu">
                                <li><a href="{{ config('app.site_url') }}b2b-incentives">Business Owner Incentives</a></li>
                                <li><a href="{{ config('app.site_url') }}custom-boxes">Custom Boxes</a></li>
                                <li><a href="{{ config('app.site_url') }}custom-tape">Custom Tape</a></li>
                                <li><a href="{{ config('app.site_url') }}domestic-shipping">Domestic Shipping</a></li>
                                <li><a href="{{ config('app.site_url') }}international-shipping">International Shipping</a>
                                </li>
                                <li><a href="{{ config('app.site_url') }}international-freight-forwarding">International
                                        Freight Forwarding</a></li>
                                <li><a href="{{ config('app.site_url') }}ltl-and-tl-shipping">LTL and TL Shipping</a></li>
                                <li><a href="{{ config('app.site_url') }}pack-and-ship">Pack and Ship</a></li>
                                <li><a href="{{ config('app.site_url') }}specialized-packing-for-irregular-shapes">Specialized
                                        Packing For Irregular Shapes</a></li>
                                <li><a href="{{ config('app.site_url') }}ups-access-point">UPS Access Point</a></li>
                            </ul>
                        </li>

                        <li><a href="{{ config('app.site_url') }}custom-tape">Locations</a></li>
                        {{-- <li><a href="{{ config('app.site_url') }}b2b-incentives/">Bulk Orders</a></li> --}}
                        <li><a href="{{ config('app.site_url') }}custom-boxes/">Contact</a></li>
                        <li class="menu-btn"><a href="tel:3107062246">(310) 706-2246</a></li>
                    </ul>
                </nav>

                <!-- Hamburger Button for Mobile -->
                <button class="hamburger d-lg-none mb-show" id="hamburger-btn">
                    ☰
                </button>
            </div>

            <!-- Mobile Menu -->
            <div class="col-12 d-lg-none mb-show">
                <div class="mobile-menu" id="mobile-menu">
                    <button class="close-btn" id="close-menu">✕</button>
                    <ul class="mobile-sub-menu">
                        <li><a href="javascript:void(0)">Services</a>
                            <ul class="primary-sub-menu">
                                <li><a href="{{ config('app.site_url') }}b2b-incentives/">Business Owner Incentives</a></li>
                                <li><a href="{{ config('app.site_url') }}custom-boxes/">Custom Boxes</a></li>
                                <li><a href="{{ config('app.site_url') }}custom-tape/">Custom Tape</a></li>
                                <li><a href="{{ config('app.site_url') }}domestic-shipping/">Domestic Shipping</a></li>
                                <li><a href="{{ config('app.site_url') }}international-shipping/">International Shipping</a></li>
                                <li><a href="{{ config('app.site_url') }}international-freight-forwarding/">International Freight Forwarding</a></li>
                                <li><a href="{{ config('app.site_url') }}ltl-and-tl-shipping/">LTL and TL Shipping</a></li>
                                <li><a href="{{ config('app.site_url') }}pack-and-ship/">Pack and Ship</a></li>
                                <li><a href="{{ config('app.site_url') }}specialized-packing-for-irregular-shapes/">Specialized Packing For Irregular Shapes</a></li>
                                <li><a href="{{ config('app.site_url') }}ups-access-point/">UPS Access Point</a></li>
                            </ul>

                        </li>
                        <li><a href="javascript:void(0)">Shop Products</a>
                            <ul class="primary-sub-menu">
                                <li><a href="{{ config('app.site_url') }}gift-supplies/">Gift Supplies</a></li>
                                <li><a href="{{ config('app.site_url') }}bags-and-pouches/">Bags And Pouches</a></li>
                                <li><a href="{{ config('app.site_url') }}corrugated-boxes/">Corrugated Boxes</a></li>
                                <li><a href="{{ config('app.site_url') }}mailing-items/">Mailing Items</a></li>
                                <li><a href="{{ config('app.site_url') }}">Packing Supplies</a></li>
                                <li><a href="{{ config('app.site_url') }}moving-boxes-and-supplies/">Moving Boxes And Supplies</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ config('app.site_url') }}custom-tape/">Locations</a></li>
                        <li><a href="{{ config('app.site_url') }}b2b-incentives/">Wholesale / Bulk Orders</a></li>
                        <li><a href="{{ config('app.site_url') }}custom-boxes/">Contact</a></li>
                        <li class="menu-btn"><a href="tel:3107062246">(310) 706-2246</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>


<div id="cartDrawer" class="cart-drawer">
    <div class="cart-header">
        <h5>Your Cart</h5>
        <button id="closeCartDrawer" class="close-btn">&times;</button>
    </div>
    <div class="cart-body">
        <table class="table">
            <tbody id="cart-drawer-list">
            <!-- Items will be injected here -->
            </tbody>
        </table>
        <a href="{{route('cart')}}" class="popup-cart-btn">View Cart</a>
    </div>
</div>

<body>
<script>
  fbq('track', 'Purchase', {
    currency: 'USD',
  });
</script>    
    
<!-- Google Tag Manager (noscript) -->
<!--<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T77PQBGD"-->
<!--                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>-->
<!-- End Google Tag Manager (noscript) -->


<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MB433BJ4"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<main>
    @yield('content')
</main>
</body>
@if(in_array(Route::currentRouteName(), ['home', 'product.detail']))
    @include('partials.floating_cart')
@endif
<footer>
    <div class="container">
        <div class="row first-row">
            <div class="col-md-6">
                <div class="footer-detail">
                    <a href="{{route('home')}}"><img src="{{ asset('public/assets/footer-logo.png')}}"
                                                                       alt="Logo" width="auto" height="auto"> </a>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/OfficialBoxCity/"><img
                                src="{{ asset('public/assets/fb-icon.png')}}" alt="Logo" width="auto" height="auto"></a>
                        <a href="https://www.instagram.com/officialboxcity/?hl=en"><img
                                src="{{ asset('public/assets/insta-icon.png')}}" alt="Logo" width="auto" height="auto"></a>
                        <a href="https://www.linkedin.com/company/boxcitystores/"><img
                                src="{{ asset('public/assets/linkedin-icon.png')}}" alt="Logo" width="auto"
                                height="auto"></a>
                    </div>
                    <p>
                        Box City is L.A.’s premier packing and shipping service for businesses and consumers. Contact us
                        today to pack and ship any item.
                    </p>
                    <div class="phone-numer">
                        <a href="tel:310-706-2246"><img src="{{ asset('public/assets/phone-icon-1.png')}}" alt="Logo"
                                                      width="auto" height="auto"> <span>(310) 706-2246</span></a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-4">
                        <h1>
                            Locations
                        </h1>
                        <ul>
                            <li><a href="{{ config('app.site_url') }}location-van-nuys/">Van Nuys</a></li>
                            <li><a href="{{ config('app.site_url') }}location-north-hollywood/">North Hollywood</a></li>
                            <li><a href="{{ config('app.site_url') }}location-west-los-angeles/">West Los Angeles</a>
                            </li>
                            <li><a href="{{ config('app.site_url') }}location-valencia/">Valencia</a></li>
                            <li><a href="{{ config('app.site_url') }}location-pasadena/">Pasadena</a></li>
                            <li><a href="{{ config('app.site_url') }}location-marina-del-rey/">Marina Del Rey</a></li>
                            <li><a href="{{ config('app.site_url') }}location-canoga-park/">Canoga Park</a></li>
                            <li><a href="{{ config('app.site_url') }}location-glendale/">Glendale</a></li>

                            <li><a href="{{ config('app.site_url') }}location-azusa/">Azusa</a></li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <h1>
                            Products &amp; Services
                        </h1>
                        <ul>
                            <li><a href="{{ config('app.site_url') }}corrugated-boxes">Corrugated Boxes and Pads</a>
                            </li>
                            <li><a href="{{ config('app.site_url') }}moving-boxes-and-supplies">Moving Boxes and
                                    Supplies</a></li>
                            <li><a href="{{ config('app.site_url') }}gift-supplies">Gift Supplies</a></li>
                            <li><a href="{{ config('app.site_url') }}mailing-items">Mailing Items</a></li>
                            <li><a href="{{ config('app.site_url') }}packing-supplies">Packing Supplies</a></li>
                            <li><a href="{{ config('app.site_url') }}bags-and-pouches">Bags &amp; Pouches</a></li>
                        </ul>
                    </div>

                </div>
                <h2>
                    Sign up for tips &amp; tricks
                </h2>
                <div class="emaillist" id="es_form_f3-n1">
                    <form action="{{route('submit.newsletter')}}" method="post"
                          class="es_subscription_form es_shortcode_form  es_ajax_subscription_form"
                          id="es_subscription_form_67cad1bc73b49" data-source="ig-es" data-form-id="3">
                        @csrf

                        <style>form.es_subscription_form[data-form-id="3"] * {
                                box-sizing: border-box;
                            }

                            body {
                                margin: 0;
                            }

                            #esfpx_email_0fe74ada6116e {
                                color: #ffffff;
                            }

                            form[data-form-id="3"] .es-form-field-container .gjs-row {
                                display: flex;
                                justify-content: flex-start;
                                align-items: stretch;
                                flex-wrap: nowrap;
                            }

                            form[data-form-id="3"] .es-form-field-container .gjs-cell {
                                flex-grow: 1;
                                flex-basis: 100%;
                            }

                            form[data-form-id="3"] .es-form-field-container .gjs-cell[data-highlightable="1"]:empty {
                                border-top-width: 1px;
                                border-right-width: 1px;
                                border-bottom-width: 1px;
                                border-left-width: 1px;
                                border-top-style: dashed;
                                border-right-style: dashed;
                                border-bottom-style: dashed;
                                border-left-style: dashed;
                                border-top-color: rgb(204, 204, 204);
                                border-right-color: rgb(204, 204, 204);
                                border-bottom-color: rgb(204, 204, 204);
                                border-left-color: rgb(204, 204, 204);
                                border-image-source: initial;
                                border-image-slice: initial;
                                border-image-width: initial;
                                border-image-outset: initial;
                                border-image-repeat: initial;
                                height: 30px;
                            }

                            form[data-form-id="3"] .es-form-field-container .gjs-row .gjs-cell input[type="checkbox"], form[data-form-id="3"] .es-form-field-container .gjs-row .gjs-cell input[type="radio"] {
                                margin-top: 0px;
                                margin-right: 5px;
                                margin-bottom: 0px;
                                margin-left: 0px;
                                width: auto;
                            }

                            form[data-form-id="3"] .es-form-field-container .gjs-row {
                                margin-bottom: 0.6em;
                            }

                            form[data-form-id="3"] .es-form-field-container label.es-field-label {
                                display: block;
                            }

                            @media only screen and (max-width: 576px) {
                                .gjs-row {
                                    display: flex;
                                    flex-direction: column;
                                }

                                .gjs-cell img {
                                    width: 100%;
                                    height: auto;
                                }
                            }

                            @media (max-width: 320px) {
                                form[data-form-id="3"] .es-form-field-container {
                                    padding-top: 1rem;
                                    padding-right: 1rem;
                                    padding-bottom: 1rem;
                                    padding-left: 1rem;
                                }
                            }</style>
                        <div class="es-form-field-container">
                            <div class="gjs-row"></div>
                            <div class="gjs-row">
                                <div class="gjs-cell">
                                    <input type="email" required="" class="es-email" name="email"
                                           autocomplete="email" placeholder="Your email" id="esfpx_email_0fe74ada6116e">
                                    <input type="submit" name="submit" value="">
                                </div>
                            </div>
                            <div class="gjs-row"></div>
                        </div>
                        <span class="es_spinner_image" id="spinner-image"><img
                                src="{{ config('app.site_url') }}wp-content/plugins/email-subscribers/lite/public/images/spinner.gif"
                                alt="Loading"></span>
                    </form>
                    <span class="es_subscription_message " id="es_subscription_message_67cad1bc73b49" role="alert"
                          aria-live="assertive"></span>
                </div>
            </div>
        </div>
    </div>
</footer>
</body>
<script>
    var productDetailUrl = "{{ route('product.detail', ['id' => '000']) }}";
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js"
        integrity="sha512-HGOnQO9+SP1V92SrtZfjqxxtLmVzqZpjFFekvzZVWoiASSQgSr4cw9Kqd2+l8Llp4Gm0G8GIFJ4ddwZilcdb8A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/elevatezoom/2.2.3/jquery.elevatezoom.min.js"
        integrity="sha512-UH428GPLVbCa8xDVooDWXytY8WASfzVv3kxCvTAFkxD2vPjouf1I3+RJ2QcSckESsb7sI+gv3yhsgw9ZhM7sDw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ asset('public/js/script.js') }}"></script>
@yield('script')
</html>
