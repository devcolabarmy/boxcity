@extends('layouts.app')

@section('title', 'Product')

@section('content')

    <!-- Container for the entire product single page layout --> <!-- Container for the entire product single page layout -->
    <div class="container">
        <div class="row">
            <div id="single-product">

                <!-- Product Image Section (Left Side) -->
                <div class="col-md-6">
                    <div class="img-magnifier-container" style="width: 65%;">
                        <!-- Zoomable product image -->
                        <img id="product-image" src="{{ $product->thumbnailUrl }}" alt="Product Image" data-action="zoom"
                             style="width: 100%;" />
                    </div>
                </div>

                <!-- Product Info Section (Right Side) -->
                <div class="col-md-6">
                    <!-- Product Name -->
                    <h1 id="product-title">{{ $product->name }}</h1>

                    <!-- Product SKU (if available) -->
                    <div id="product-sku">SKU: {{ $product->sku ?? '' }}</div>

                    <!-- Product Description (if available) -->
                    <div id="product-description">{{ $product->description ?? '' }}</div>
                </div>

            </div>
        </div>

        <!-- Bulk Pricing Table Section -->
        <div class="row table-row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table id="product-table" class="responsive-table">
                        <thead>
                        <!-- First Header Row -->
                        <tr>
                            <th rowspan="2">Product ID</th>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Retail Price</th>
                            <!-- Bulk pricing header -->
                            <th colspan="3" class="bulk-price-header main">Discounted Bulk Price</th>
                            <th rowspan="2">Add To Cart</th>
                        </tr>

                        <!-- Second Header Row for Bulk Pricing Tiers -->
                        <tr>
                            <th class="bulk-price-header">12+</th>
                            <th class="bulk-price-header">50+</th>
                            <th class="bulk-price-header">100+</th>
                        </tr>
                        </thead>

                        <!-- Product Row -->
                        <tbody id="product-list">
                        <tr>
                            <!-- Product ID -->
                            <td data-label="Product ID :"><a class="product-single" href="#" id="product-id">{{ $product->productId }}</a></td>

                            <!-- Product Name -->
                            <td data-label="Name :"><a class="product-single" href="#" id="product-name">{{ $product->name }}</a></td>

                            <!-- Retail Price -->
                            <td class="retail-price" id="retail-price" data-label="Retail Price :">{{ config('app.currency_symbol') }}{{ $product->price }}</td>

                            <!-- Bulk Price: 12+ -->
                            <td class="bulk-price-12" id="bulk-12" data-label="12+ :">{!! calculatePrice($product->price, 12) !!}</td>

                            <!-- Bulk Price: 50+ -->
                            <td class="bulk-price-50" id="bulk-50" data-label="50+ :">{!! calculatePrice($product->price, 50) !!}</td>

                            <!-- Bulk Price: 100+ -->
                            <td class="bulk-price-100" id="bulk-100" data-label="100+ :">{!! calculatePrice($product->price, 100) !!}</td>

                            <!-- Quantity Selector + Add Button -->
                            <td data-label="Add To Cart :">
                                <div class="quantity-container">
                                    <!-- Quantity Increment/Decrement Controls -->
                                    <div class="qty-container">
                                        <button class="quantity-btn minus">-</button>
                                        <input type="text" class="quantity-input" value="1">
                                        <button class="quantity-btn plus">+</button>
                                    </div>

                                    <!-- Add to Cart Button with Data Attributes -->
                                    <button class="add-btn"
                                            data-product-id="{{ $product->productId }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-image="{{ $product->thumbnailUrl }}"
                                            data-product-retail-price="{{ $product->price }}"
                                            data-product-bulk-price-12="{{ calculatePrice($product->price, 12, false) }}"
                                            data-product-bulk-price-50="{{ calculatePrice($product->price, 50, false) }}"
                                            data-product-bulk-price-100="{{ calculatePrice($product->price, 100, false) }}">
                                        ADD
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.table-responsive -->
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container -->

@endsection

@section('script')

    <!-- Load ElevateZoom Library from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/elevatezoom/2.2.3/jquery.elevatezoom.min.js"
            integrity="sha512-UH428GPLVbCa8xDVooDWXytY8WASfzVv3kxCvTAFkxD2vPjouf1I3+RJ2QcSckESsb7sI+gv3yhsgw9ZhM7sDw=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $(document).ready(function () {
            // Apply elevateZoom to the product image
            $('#product-image').elevateZoom({
                zoomType: "lens",
                lensShape: "square",
                lensSize: 200
            });
        });
    </script>

@endsection

