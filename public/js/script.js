jQuery(document).ready(function($) {
    $('.slider').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        infinite: true,
        arrows: true,
        responsive: [
          {
            breakpoint: 1100,
            settings: {
              slidesToShow: 2,
              arrows:false,
            }
          },
          {
            breakpoint: 821,
            settings: {
              slidesToShow: 1
            }
          }
        ]
      });
});

let cart = JSON.parse(localStorage.getItem("cart")) || [];

document.addEventListener('DOMContentLoaded', function () {
    const locationItems = document.querySelectorAll('.list-group-item');
    const locationInfos = document.querySelectorAll('.location-info');

    locationItems.forEach((item, index) => {
        item.addEventListener('click', function () {
            // Remove active class and reset border styles
            locationItems.forEach(i => {
                i.classList.remove('active');
                i.style.borderBottom = ''; // reset
            });
            locationInfos.forEach(info => info.classList.remove('active'));

            // Add active class to clicked item and corresponding details
            const location = this.dataset.location;
            this.classList.add('active');
            document.getElementById(location).classList.add('active');

            // Remove border-bottom from the previous item (if exists)
            if (index > 0) {
                locationItems[index - 1].style.borderBottom = 'none';
            }
        });
    });
});


// function updateProductCount() {
//     let rowCount = 0;
//
//     $('#product-table tbody tr').each(function () {
//         const cellText = $(this).find('td').text().trim();
//         if (cellText !== 'No Products Found') {
//             rowCount++;
//         }
//     });
// }

function applyDataLabels() {
    // Data labels are defined server-side within the Blade template.
}

// let nextPageUrl = 'load-more-products';
// let loading = false;
// let hasMore = true;
//
// function loadMoreProducts() {
//     if (loading || !hasMore) return;
//     loading = true;
//     $('#loading').show();
//     $.ajax({
//         url: nextPageUrl,
//         method: 'GET',
//         dataType: 'json',
//         success: function(response) {
//             if (response.product_html) {
//                 // Append the rendered HTML for the products
//                 $('#product-list').append(response.product_html);
//                 nextPageUrl = response.next_page_url;
//                 hasMore = response.has_more;
//                 updateProductCount();
//             }
//             if (!hasMore) {
//                 $(window).off('scroll');
//             }
//
//             applyDataLabels();
//         },
//         error: function(xhr, status, error) {
//             console.error('Error loading more products:', error);
//         },
//         complete: function() {
//             loading = false;
//             $('#loading').hide();
//         }
//     });
// }
//
//
// $(window).on('scroll', function() {
//     var tableBody = $('#product-list');
//     var bottomOfTableBody = tableBody.offset().top + tableBody.height();
//     var bottomOfWindow = $(window).scrollTop() + $(window).height();
//
//     if (bottomOfWindow >= bottomOfTableBody - 100 && tableBody.find('tr.scroll-false').length === 0) {
//         loadMoreProducts();
//
//     }
// });
//
// $(document).ready(function() {
//     loadMoreProducts();
//  ;
// });



function updateCartTotal() {
    let total = 0;

    $(".total-unit").each(function () {
        let priceText = $(this).text().replace("$", "").trim()
        console.log(priceText);
        let price = parseFloat(priceText) || 0;
        total += price;
    });

    $(".cart-total").text(`Subtotal : $${total.toFixed(2)}`);
}


function updateTotalPrice() {
    
    let total = 0;

    cart.forEach(item => {
        total += item.price * item.quantity;
    });

    $(".cart-total").text(`Subtotal : $${total.toFixed(2)}`);

    let totalSaved = 0;

    cart.forEach(item => {
        const itemSaved = (item.retailPrice * item.quantity) - (item.price * item.quantity);
        totalSaved += itemSaved;
    });

    if(totalSaved > 0){
$('.save-amount').text(`You Save : $${totalSaved.toFixed(2)}`);
    }else{
         $(".save-amount").text("");
    }
    

}


function getGroupKey(productName) {
    if (productName.toLowerCase().includes("corrugated boxes")) {
        return "Corrugated Boxes"; // normalized group name
    }
    return productName;
}

function applyGroupPricing() {
    const grouped = {};

    // First, group products by groupKey
    cart.forEach(item => {
        const groupKey = getGroupKey(item.product);
        if (!grouped[groupKey]) grouped[groupKey] = [];
        grouped[groupKey].push(item);
    });

    for (const groupKey in grouped) {
        const groupItems = grouped[groupKey];
        const groupTotalQty = grouped[groupKey].reduce((sum, i) => sum + i.quantity, 0);

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

    return grouped;
}


function updateCartQuantity(productName, newQuantity, $row) {
    let item = cart.find(item => item.product === productName);

    if (item) {
        item.quantity = newQuantity;

        let retailPrice = parseFloat(item.retailPrice) || 0;
        let priceBulkOne = parseFloat(item.priceBulkOne) || retailPrice;
        let priceBulkTwo = parseFloat(item.priceBulkTwo) || priceBulkOne;
        let priceBulkThree = parseFloat(item.priceBulkThree) || priceBulkTwo;



        applyGroupPricing();

        // Save to localStorage
        localStorage.setItem("cart", JSON.stringify(cart));

        $(".cart-quantity").each(function () {
            let product = $(this).data("product");
            let qty = parseInt($(this).val());
            let row = $(this).closest("tr");

            let productItem = cart.find(p => p.product === product);
            if (productItem) {
                let unitTotal = productItem.price * qty;
                row.find(".total-unit").text(`$${unitTotal.toFixed(2)}`);
                row.find(".cart-price").text(`$${productItem.price.toFixed(2)}`);
            }
            applyGroupPricing();
        });


        updateCartTotal();
        applyGroupPricing();
    }
}


function updateCartPage() {

    let finalPrice = applyGroupPricing();
    console.log(finalPrice);
    let $cartPageList = $("#cart-page-list");
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let $checkoutBtn = $("#proceed-to-checkout");

    if ($cartPageList.length) {
        $cartPageList.empty();

        if (cart.length === 0) {
            $cartPageList.append("<tr><td colspan='6'>Cart is empty</td></tr>");
            $checkoutBtn.prop("disabled", true);
        } else {
            localStorage.setItem("cart", JSON.stringify(cart));
            console.log("Updated cart with pricing:", cart);
            finalPrice["Corrugated Boxes"].forEach(item => {

                $cartPageList.append(`
                    <tr>
                        <td data-label="Id :"><a href="${productDetailUrl.replace('000', item.productId)}">${item.productId}</a></td>
                        <td data-label="Name :"><a href="${productDetailUrl.replace('000', item.productId)}">${item.product}</a></td>
                        <td data-label="Quantity :">
                            <div class="quantity-container">
                            <div class="qty-container">
                                <button class="quantity-btn minus" data-product="${item.product}">−</button>
                                <input type="text" class="cart-quantity text-center" data-product="${item.product}" value="${item.quantity}" min="1" style="width: 40px;">
                                <button class="quantity-btn plus" data-product="${item.product}">+</button>
                            </div>
                            </div>
                        </td>
                        <td class="cart-price" style="display: none !important;">$${item.price}</td>
                        <td class="cart-price total-unit" style="display: none !important;">$${item.retailPrice}</td>
                        <td class="cart-price total-unit" data-label="Total :">$${(item.price * item.quantity).toFixed(2)}</td>
                        <td class="cart-price total-unit discount-amout" style="display: none !important;">$${(item.retailPrice * item.quantity - item.price * item.quantity).toFixed(2)}</td>
                        <td data-label="">
                            <button class="remove-item btn btn-danger btn-sm" data-product="${item.product}">Remove</button>
                        </td>
                    </tr>
                `);
            });

            if ($checkoutBtn.length) {
                $checkoutBtn.prop("disabled", false); // Enable if cart is not empty
            }

        }
    }


}


$(document).ready(function () {
    updateCartPage();
    applyGroupPricing();
});

function updateCartUI() {
    let $cartList = $("#cart-list");
    let $cartCounter = $(".cart-counter");
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    $cartList.empty();
    let totalItems = 0;

    if (cart.length === 0) {
        $cartList.append("<li>Cart is empty</li>");
        $cartCounter.hide(); // Hide counter if empty
    } else {
        cart.forEach(item => {
            $cartList.append(`
                <li>${item.product} - ${item.quantity}
                    <button class="remove-item" data-product="${item.product}">❌</button>
                </li>
            `);
            totalItems += item.quantity;
        });

        $cartCounter.text(totalItems).show(); // Update and show counter
    }
}




$(document).ready(function () {
    $(document).on("click", ".quantity-btn", function () {
        let $wrapper = $(this).closest(".quantity-container");
        let $input = $wrapper.find(".quantity-input");
        let value = Number($input.val()) || 1;
        if ($(this).hasClass("plus")) {
            $input.val(value + 1);
        } else if ($(this).hasClass("minus")) {
            $input.val(Math.max(1, value - 1));
        }
        updateCartPage();
        updateTotalPrice();
    });


    $(document).on("input", ".quantity-input", function () {
        let val = $(this).val().replace(/[^0-9]/g, "");
        $(this).val(val || 1);
    });
});


function syncCartWithSession() {
    $.ajax({
        url: "{{ route('cart.store') }}",
        method: "POST",
        data: { cart: cart, _token: "{{ csrf_token() }}" },
        success: function (response) {
            console.log("Cart synced with session!");
        }
    });
}


$(document).on("click", ".remove-item", function () {
    let productName = $(this).data("product");
    cart = cart.filter(item => item.product !== productName);
    localStorage.setItem("cart", JSON.stringify(cart));

    syncCartWithSession();
    updateTotalPrice();
    updateCartPage();
});

$(document).ready(function () {

    let cart = JSON.parse(sessionStorage.getItem("cart")) || [];
    updateCartUI();
    updateCartPage();

    // Toggle Cart Panel
    $(".cart-icon").on("click", function () {
        $(".cart-panel").fadeToggle();
    });

    // Add to Cart Functionality
    $(document).on("click", ".add-btn", function () {
        let $container = $(this).closest(".quantity-container");
        let productName = $(this).data("product-name");
        let price = $(this).data("product-retail-price");
        let priceBulkOne = $(this).data("product-bulk-price-12");
        let priceBulkTwo = $(this).data("product-bulk-price-50");
        let priceBulkThree = $(this).data("product-bulk-price-100");
        let productThumb = $(this).data("product-image");
        let productId = $(this).data("product-id");
        let quantity = parseInt($container.find(".quantity-input").val()) || 1;

        let finalPrice;
        if (quantity >= 100) {
            finalPrice = priceBulkThree;
        } else if (quantity >= 50) {
            finalPrice = priceBulkTwo;
        } else if (quantity >= 12) {
            finalPrice = priceBulkOne;
        } else {
            finalPrice = price;
        }

        finalPrice = finalPrice ? parseFloat(finalPrice) : 0;
        let cart = JSON.parse(localStorage.getItem("cart")) || [];

        if (quantity > 0) {
            let existingItem = cart.find(item => item.product === productName);

            if (existingItem) {
                existingItem.quantity += quantity;
                existingItem.price = finalPrice; // Optional: Only update if price should change
            } else {
                cart.push({
                    product: productName,
                    quantity: quantity,
                    price: finalPrice,
                    priceBulkOne: priceBulkOne,
                    priceBulkTwo: priceBulkTwo,
                    priceBulkThree: priceBulkThree,
                    retailPrice: price,
                    productThumb: productThumb,
                    productId: productId
                });
            }

            localStorage.setItem("cart", JSON.stringify(cart));
            applyGroupPricing();
            updateCartUI();
            updateCartPage();
            updateCartDrawer();
            openCartDrawer();

            setTimeout(() => {
                $container.find(".quantity-input").val(1);
            }, 500);

        } else {
            alert("Quantity must be at least 1");
        }
    });


    function runCartUpdate() {
        updateCartUI();
    }

    window.addEventListener('load', runCartUpdate);
    window.addEventListener('pageshow', runCartUpdate);




    // Clear Cart
    $("#clear-cart").on("click", function () {
        sessionStorage.removeItem("cart");
        cart = [];
        updateCartUI();
        updateCartPage();
    });

    // Reset cart counter when tab is closed
    $(window).on("unload", function () {
        sessionStorage.removeItem("cart");
    });


    function applyDataLabelsCart() {

        let headers;
        if(cart.length === 0){
            headers = [
            "", "Name :", "Quantity :", "Total :", "Action :",
        ];
        } else{
            headers = [
            "Id:", "Name :", "Quantity :", "Total :", "Action :",
        ];
        }

      


        $("#cart-page-list tr").each(function () {
            $(this).find("td").each(function (index) {
                $(this).attr("data-label", headers[index]);
            });
        });
    }

    setTimeout(function () {
        applyDataLabels();
        applyDataLabelsCart();
    }, 3000); // 200ms delay, adjust as needed




});


$(document).ready(function () {
    $('#hamburger-btn').on('click', function () {
        $('#mobile-menu').addClass('active');
        $('body').addClass('menu-open');
    });

    $('#close-menu').on('click', function () {
        $('#mobile-menu').removeClass('active');
        $('body').removeClass('menu-open');
    });

    // Optional: Close menu when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#mobile-menu, #hamburger-btn').length) {
            $('#mobile-menu').removeClass('active');
            $('body').removeClass('menu-open');
        }
    });

    $(document).ready(function () {
        $('li').has('.primary-sub-menu').addClass('menu-item-has-children');
    });

    $('li').has('.primary-sub-menu').hover(

        function () {
            // Mouse enter: show submenu
            $(this).find('.primary-sub-menu').stop(true, true).slideDown(5);
        },
        function () {
            // Mouse leave: hide submenu
            $(this).find('.primary-sub-menu').stop(true, true).slideUp(5);
        }
    );

});




function updateCartDrawer() {
    let $cartDrawerList = $("#cart-drawer-list");
    $cartDrawerList.empty();
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    if (cart.length === 0) {
        $cartDrawerList.append("<tr><td colspan='3'>Cart is empty</td></tr>");
    } else {
        cart.forEach(item => {
            $cartDrawerList.append(`
                <tr>
                    <td style="width: 60px;">
                        <img
  src="${item.productThumb}"
  alt="${item.product}"
  style="width: 50px; height: 50px; object-fit: cover;"
  onerror="this.onerror=null;this.src='https://boxcityweb.colabwebdemo.com/boxcity/public/assets/header-logo.png';">

                    </td>
                    <td>
                        <strong>${item.product}</strong><br>
                        <small>Qty: ${item.quantity}</small><br>
                        <small>Price: $${item.price.toFixed(2)}</small>
                    </td>

                </tr>
            `);
        });
    }
}


// Open drawer
function openCartDrawer() {
    $("#cartDrawer").addClass("active");
    $("#cartBackdrop").addClass("active");
    updateCartDrawer();

    // Auto close after 10 seconds (10,000 milliseconds)
    setTimeout(() => {
        closeCartDrawer();
    }, 2500);
}

// Close drawer
function closeCartDrawer() {
    $("#cartDrawer").removeClass("active");
    $("#cartBackdrop").removeClass("active");
}

$(document).on("click", "#closeCartDrawer, #cartBackdrop", function() {
    closeCartDrawer();
});

