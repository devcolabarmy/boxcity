
@php
    $agent = new \Jenssegers\Agent\Agent();
    $isMobileOrTablet = $agent->isMobile() || $agent->isTablet();
@endphp

@if($isMobileOrTablet)
    <div class="product-card-list">
        @if($products->isEmpty())
            <div class="product-card product-card--empty">
                <p>No Products Found</p>
            </div>
        @else
            @foreach ($products as $product)
                @php
                    $length = trim((string) ($product->length ?? ''));
                    $width = trim((string) ($product->width ?? ''));
                    $height = trim((string) ($product->height ?? ''));

                    $normalizedLength = str_replace([chr(215), 'X'], 'x', $length);

                    if ($normalizedLength !== '' && \Illuminate\Support\Str::contains($normalizedLength, 'x')) {
                        $parts = preg_split('/\s*x\s*/i', $normalizedLength);

                        if (count($parts) === 3) {
                            $parsedLength = trim($parts[0]);
                            $parsedWidth = trim($parts[1]);
                            $parsedHeight = trim($parts[2]);

                            if ($parsedLength !== '') {
                                $length = $parsedLength;
                            }

                            if ($width === '' && $parsedWidth !== '') {
                                $width = $parsedWidth;
                            }

                            if ($height === '' && $parsedHeight !== '') {
                                $height = $parsedHeight;
                            }
                        }
                    }

                    $dimensionValues = [
                        $length !== '' ? $length : null,
                        $width !== '' ? $width : null,
                        $height !== '' ? $height : null,
                    ];

                    $hasDimensionData = array_filter($dimensionValues, function ($value) {
                        return $value !== null;
                    });

                    $displayValues = array_map(function ($value) {
                        return $value ?? '-';
                    }, $dimensionValues);

                    $dimensionDisplay = !empty($hasDimensionData)
                        ? implode(' x ', $displayValues)
                        : 'N/A';
                @endphp
                <div class="product-card-outer">

                <div class="product-card">
                    <div class="product-card-thumb">
                        @if(!empty($product->thumbnailUrl))
                            <img src="{{ $product->thumbnailUrl }}" alt="{{ $product->name }}">
                        @else
                            <span class="product-card-thumb__placeholder">No Image</span>
                        @endif
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-row">
                            <span class="product-card-label">Product ID :</span>
                            <span class="product-card-value"><a class="product-single" href="{{ route('product.detail', ['id' => $product->productId]) }}">{{ $product->productId }}</a></span>
                        </div>
                        <div class="product-card-row">
                            <span class="product-card-label">Type :</span>
                            <span class="product-card-value">{{ optional($product->category)->categoryName ?? 'N/A' }}</span>
                        </div>
                        <div class="product-card-row">
                            <span class="product-card-label">L x W x H :</span>
                            <span class="product-card-value">{{ $dimensionDisplay }}</span>
                        </div>
                        <div class="product-card-row">
                            <span class="product-card-label">Reg. Price :</span>
                            <span class="product-card-value">{{ config('app.currency_symbol') }}{{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="product-card-row bulk-discount-row">
                            
                            <span class="product-card-label">Discount </br> Price: </span>
                            
                            <div class="bulk-pricing">
                                <div class="bulk-pricing-column">
                                    <span class="bulk-pricing-qty">12+ / 50+ / 100+</span>
                                    <span class="product-card-value">{!! calculatePrice($product->price, 12) !!} / {!! calculatePrice($product->price, 50) !!} / {!! calculatePrice($product->price, 100) !!} </span> 
                                </div>
                       
                             
                      </div>
                        </div>
                 
                       
                    </div>
                </div>
                 <div class="product-card-row add-to-cart-row">
                            
                            <span class="product-card-value">
                                <div class="quantity-container">
                                    <div class="qty-container">
                                        <button class="quantity-btn minus">-</button>
                                        <input type="text" class="quantity-input" value="1"/>
                                        <button class="quantity-btn plus">+</button>
                                    </div>
                                    <button class="add-btn" data-product-id="{{ $product->productId }}" data-product-name="{{ $product->name }}" data-product-image="{{ $product->thumbnailUrl ?? ''}}"
                                            data-product-retail-price="{{ number_format($product->price, 2) }}" data-product-bulk-price-12="{{ calculatePrice($product->price, 12, false) }}"
                                            data-product-bulk-price-50="{{ calculatePrice($product->price, 50, false) }}" data-product-bulk-price-100="{{ calculatePrice($product->price, 100, false) }}">
                                        ADD
                                    </button>
                                </div>
                            </span>
                        </div>
            </div>
            @endforeach
        @endif
    </div>
@else
    <div class="table-responsive">
        <table id="product-table" class="responsive-table">
            <thead>
            <tr>
                <th rowspan="2">Product ID</th>
                <th rowspan="2">Type</th>
                <th rowspan="2">L x W x H</th>
                <th rowspan="2">Retail Price</th>
                <th colspan="3" class="bulk-price-header main">Discounted Bulk Price</th>
                <th rowspan="2">Add To Cart</th>
            </tr>
            <tr>
                <th class="bulk-price-header">12+</th>
                <th class="bulk-price-header">50+</th>
                <th class="bulk-price-header">100+</th>
            </tr>
            </thead>
            <tbody id="product-list">
                @if($products->isEmpty())
                    <tr class="scroll-{{ $scroll ?? '' }}">
                        <td colspan="100%" class="text-center py-3" data-label="Products">No Products Found</td>
                    </tr>
                @else
                    @foreach ($products as $product)
                        @php
                            $length = trim((string) ($product->length ?? ''));
                            $width = trim((string) ($product->width ?? ''));
                            $height = trim((string) ($product->height ?? ''));

                            $normalizedLength = str_replace([chr(215), 'X'], 'x', $length);

                            if ($normalizedLength !== '' && \Illuminate\Support\Str::contains($normalizedLength, 'x')) {
                                $parts = preg_split('/\s*x\s*/i', $normalizedLength);

                                if (count($parts) === 3) {
                                    $parsedLength = trim($parts[0]);
                                    $parsedWidth = trim($parts[1]);
                                    $parsedHeight = trim($parts[2]);

                                    if ($parsedLength !== '') {
                                        $length = $parsedLength;
                                    }

                                    if ($width === '' && $parsedWidth !== '') {
                                        $width = $parsedWidth;
                                    }

                                    if ($height === '' && $parsedHeight !== '') {
                                        $height = $parsedHeight;
                                    }
                                }
                            }

                            $dimensionValues = [
                                $length !== '' ? $length : null,
                                $width !== '' ? $width : null,
                                $height !== '' ? $height : null,
                            ];

                            $hasDimensionData = array_filter($dimensionValues, function ($value) {
                                return $value !== null;
                            });

                            $displayValues = array_map(function ($value) {
                                return $value ?? '-';
                            }, $dimensionValues);

                            $dimensionDisplay = !empty($hasDimensionData)
                                ? implode(' x ', $displayValues)
                                : 'N/A';
                        @endphp

                        <tr class="scroll-{{$scroll  ?? ''}}">
                            <td data-label="Product ID :"><a class="product-single" href="{{ route('product.detail', ['id' => $product->productId]) }}">{{ $product->productId }}</a></td>
                            <td data-label="Category :">{{ optional($product->category)->categoryName ?? 'N/A' }}</td>
                            <td data-label="L x W x H :">{{ $dimensionDisplay }}</td>
                            <td data-label="Retail Price :">{{ config('app.currency_symbol')}}{{ number_format($product->price, 2) }}</td>
                            <td class="bulk-price" data-label="12+ :">{!! calculatePrice($product->price, 12) !!}</td>
                            <td class="bulk-price" data-label="50+ :">{!! calculatePrice($product->price, 50) !!}</td>
                            <td class="bulk-price" data-label="100+ :">{!! calculatePrice($product->price, 100) !!}</td>
                            <td data-label="Add To Cart :">
                                <div class="quantity-container">
                                    <div class="qty-container">
                                        <button class="quantity-btn minus">-</button>
                                        <input type="text" class="quantity-input" value="1"/>
                                        <button class="quantity-btn plus">+</button>
                                    </div>
                                    <button class="add-btn" data-product-id="{{ $product->productId }}" data-product-name="{{ $product->name }}" data-product-image="{{ $product->thumbnailUrl ?? ''}}"
                                            data-product-retail-price="{{number_format($product->price, 2)}}" data-product-bulk-price-12="{{ calculatePrice($product->price, 12, false) }}"
                                            data-product-bulk-price-50="{{ calculatePrice($product->price, 50, false) }}" data-product-bulk-price-100="{{ calculatePrice($product->price, 100, false) }}">
                                        ADD
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
@endif

<div class="pagination-links text-center mt-4">
    {!! $products->links() !!}
</div>
