<!-- Side pane -->
<?php
use App\Models\Category;
?>

<div class="col-lg-3 col-sm-12">
    <div class="accordion" id="accordionExample">
        <!-- Length filter -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Length
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
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

    <!-- Side banner -->
    <div class="offer">
        <h3>Why Choose<br/>Box City? </h3>
        <ul>
            <li>Lowest Price Guarantee</li>
            <li>Same-Day Local Pickup</li>
            <li>Next Day Delivery</li>
            <li>Trusted By Business Owners</li>
        </ul>

        <h4>Call Us For All Your Packing & Shipping Needs</h4>
        <h3><a href="tel:3107062246">(310) 706-2246</a></h3>
    </div>

</div>


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
</script>
