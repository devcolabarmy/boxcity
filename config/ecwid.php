<?php

return [
    /**
     * Ecwid API configuration
     */
    'api_base_url' => env('ECWID_API_BASE_URL', 'https://app.ecwid.com/api/v3/109333282'),
    'access_token' => env('ECWID_ACCESS_TOKEN', 'secret_E4xUxJKaFW1erpDFq4fCRu1K6j7TpsS1'),
    'category_id' => env('ECWID_CATEGORY_ID', 174055330), // Category ID for filtering (default as per Corrugated Category)
    'limit' => env('ECWID_API_LIMIT', 100), // Default limit of products per request
     // 1) Delivery ShippingOption.id -> InventoryLocationId
    'delivery_option_to_location' => [
        "34271-1729279207618" => 1,   // Delivery (#1 Box City Van Nuys)
        "734406-1729282490371" => 2,  // Delivery (#4 Box City Valencia)
        "980661-1729282906374" => 3,  // Delivery (#5 Box City Pasadena)
        "107731-1729279342564" => 4,  // Delivery (#2 Box City North Hollywood)
        "1240499-1729283422177" => 5, // Delivery (#7 Box City Canoga Park)
        "1093330-1729283125676" => 6, // Delivery (#6 Box City Marina)
        "331295-1729279683219" => 7,  // Delivery (#3 Box City Westwood)
        "1508986-1729284157425" => 8, // Delivery (#8 Box City - Glendale)
        "be673a67-f1d7-45a2-bc59-1cfb67d25d43-1741118739805" => 10, // Delivery – Azusa
    ],

    // 2) Pickup ShippingOption.id -> InventoryLocationId
    'pickup_option_to_location' => [
        "128688-1729211877836" => 1,  // #1 Box City Van Nuys
        "151519-1729211921280" => 2,  // #4 Box City Valencia
        "160684-1729211937093" => 3,  // #5 Box City Pasadena
        "1928517660-1729185872005" => 4, // #2 Box City North Hollywood
        "17212-1729279179826" => 5,   // #7 Box City Canoga Park
        "7919-1729279153259" => 6,    // #6 Box City Marina
        "142409-1729211907050" => 7,  // #3 Box City Westwood
        "26507-1729279191704" => 8,   // #8 Box City - Glendale
        "3596000c-6689-42ff-8713-c358ad426a81-1741119686139" => 10, // #9 Box City Azusa
    ],

    
    'inventory_locations' => [
        1  => ['title' => '#1 Box City Van Nuys',     'lat' => null, 'lng' => null],
        2  => ['title' => '#4 Box City Valencia',     'lat' => null, 'lng' => null],
        3  => ['title' => '#5 Box City Pasadena',     'lat' => null, 'lng' => null],
        4  => ['title' => '#2 Box City North Hollywood','lat' => null, 'lng' => null],
        5  => ['title' => '#7 Box City Canoga Park',  'lat' => null, 'lng' => null],
        6  => ['title' => '#6 Box City Marina',       'lat' => null, 'lng' => null],
        7  => ['title' => '#3 Box City Westwood',     'lat' => null, 'lng' => null],
        8  => ['title' => '#8 Box City - Glendale',   'lat' => null, 'lng' => null],
        10 => ['title' => '#9 Box City Azusa',        'lat' => null, 'lng' => null],
    ],
];
