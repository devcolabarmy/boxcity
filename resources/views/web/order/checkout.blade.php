
@extends('layouts.app')

@section('title', 'Checkout')

@section('content')

<script src="https://js.stripe.com/v3/"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.googleMapsApiKey = "{{ $googleMapsKey }}";
    window.checkoutStores = @json($storesJson);
</script>
<script>
document.addEventListener('DOMContentLoaded', async function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  async function syncCartNoReload() {
    try {
      const raw = localStorage.getItem('cart');
      const items = raw ? JSON.parse(raw) : [];
      await fetch("{{ route('cart.sync') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
        body: JSON.stringify({ items })
      });
    } catch (e) {
      console.warn('Cart sync failed:', e);
    }
  }

  async function refreshPickupNotes() {
    try {
      const raw = localStorage.getItem('cart');
      const items = raw ? JSON.parse(raw) : [];
      const res = await fetch("{{ route('checkout.stock_notes') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        credentials: 'same-origin',
        body: JSON.stringify({ items })
      });
      const data = await res.json();
      if (!data || !data.ok || !data.notes) return;

      // Update the <select id="pickup-location"> labels inline
      const select = document.getElementById('pickup-location');
      if (!select) return;

      for (const opt of select.options) {
        const storeKey = opt.getAttribute('data-key');
        if (!storeKey) continue;
        const note = data.notes[storeKey];
        if (!note) continue;

        // original label text is everything before " — "
        const baseLabel = (opt.textContent || '').split(' — ')[0];
        opt.textContent = `${baseLabel} — ${note}`;
      }
    } catch (e) {
      console.warn('refreshPickupNotes failed:', e);
    }
  }

   window.syncCartNoReload = syncCartNoReload;
  window.refreshPickupNotes = refreshPickupNotes;

  await syncCartNoReload();
  await refreshPickupNotes();


});



</script>

<div class="container checkout-container">
    <div class="page-title-container">
        <a href="{{ route('cart') }}" class="back">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M15 6L9 12L15 18" stroke="#33363F" stroke-width="2" />
            </svg>
        </a>
        <h2 class="page-title">Delivery Details</h2>
    </div>

    <div class="row main-checkout-row">
        <div class="col-md-7">
            <form id="checkout-form">
                <div class="info-container">
                    <h3>Select Shipping Method</h3>
                    <div class="shipping-method-container">
                        <div class="shipping-option delivery" data-method="Delivery details">
                            <div></div>
                        </div>
                        <div class="shipping-option pickup" data-method="Pickup method">
                            <div></div>
                        </div>
                    </div>
                    <input type="hidden" id="shipping-method" name="shipping-method">
                </div>

                <div class="info-container">
                    <h3>Contact Info</h3>
                    <div class="row form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" name="first-name" class="form-control" required placeholder="First Name">
                                <span id="fname-error" style="color: red; font-size: 13px; font-family: 'gilroy-semibolduploaded_file';"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="last-name" class="form-control" required placeholder="Last Name">
                                <span id="lname-error" style="color: red; font-size: 13px; font-family: 'gilroy-semibolduploaded_file';"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required placeholder="Email Address">
                                <span id="email-error" style="color: red; font-size: 13px; font-family: 'gilroy-semibolduploaded_file';"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required placeholder="Phone Number">
                                <span id="phone-error" style="color: red; font-size: 13px; font-family: 'gilroy-semibolduploaded_file';"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="pickup-section" class="info-container d-none">
                    <h3>Pickup Details</h3>

                    <div class="form-group">
                        <label for="pickup-location">Select Store Location</label>
                        <div class="custom-select-wrapper">
                            <select id="pickup-location" name="pickup-location" class="form-control">
                                <option disabled {{ !$nearestKey ? 'selected' : '' }} hidden>Select a Location</option>
                                @foreach ($stores as $store)
                                 <option
                                    value="{{ $store['value'] }}"
                                    data-id="{{ $store['pickup_option_id'] ?? '' }}"
                                    data-key="{{ $store['key'] }}"
                                    data-address="{{ $store['address'] }}"
                                    data-city="{{ $store['city'] }}"
                                    data-country="{{ $store['country'] }}"
                                    data-email="{{ $store['email'] }}"
                                    data-hours="{{ $store['hours'] }}"
                                    {{ $nearestKey === $store['key'] ? 'selected' : '' }}>
                                    {{ $store['label'] }} — {{ $store['stock_note'] ?? '' }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="shipping_option_id" id="shipping_option_id">
                        </div>
                        <div id="pickup-details" style="margin-top:15px;"></div>
                    </div>
                    <div class="form-group">
                        <label for="pickup-date">Pickup Date</label>
                        <input type="date" id="pickup-date" name="pickup-date" class="form-control">
                    </div>
                </div>

                <div id="delivery-section" class="info-container">
                    <h3>Delivery Details</h3>
            <div id="delivery-distance-error" class="alert alert-danger" style="display:none;"></div>
                    <div id="delivery-address-fields">
                        <div class="form-group">
                            <label for="address-text" id="address-text-label">Street Address</label>
                            <input type="text" id="address-text" name="shipping-address-text" class="form-control" placeholder="Street Address">
                            <span id="delivery-address-error" style="color:red; font-size:13px; font-family:'gilroy_semibolduploaded_file';"></span>
                        </div>
                        <div class="row form-row">
                           <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping-city">City</label>
                                    <input type="text" id="shipping-city" name="shipping-city" class="form-control" placeholder="City">
                                    <span id="delivery-city-error" style="color:red; font-size:13px; font-family:'gilroy_semibolduploaded_file';"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" title="We only deliver in California">
                                    <label for="shipping-state">State <span class='label-note'>(CA Only)</span></label>
                                    <div class="custom-select-wrapper">
                                        <select id="shipping-state" name="shipping-state" class="form-control" disabled>
                                            <option value="CA" selected>California</option>
                                        </select>
                                    </div>
                                    <span id="delivery-state-error" style="color:red; font-size:13px; font-family:'gilroy-semibolduploaded_file';"></span>
                                </div>
                            </div>
                        
                                 <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping-country">Country</label>
                                    <div class="custom-select-wrapper">
                                        <select id="shipping-country" name="shipping-country" class="form-control" disabled>
                                            <option>Select Country</option>
                                            <option value="US" disabled selected>United States</option>
                                        </select>
                                    </div>
                                    <span id="delivery-country-error" style="color:red; font-size:13px; font-family:'gilroy_semibolduploaded_file';"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping-zip">Zip Code</label>
                                    <input type="text" id="shipping-zip" name="shipping-zip" class="form-control" placeholder="Zip Code">
                                    <span id="delivery-zip-error" style="color:red; font-size:13px; font-family:'gilroy_semibolduploaded_file';"></span>
                                </div>
                            </div>
                        </div>
         
                    </div>
                </div>

                <div class="btn-container">
                    <h4 class="checkout-total">Total: $0.00</h4>
                    <button id="place-order" type="button" class="btn btn-success btn-lg">Place Order</button>
                </div>

                <button id="stripe-checkout-button" type="button" class="stripe-button">Checkout</button>
            </form>
        </div>


        <div class="col-md-4 fade-box">
            <div class="info-container">
                <h3>Order Summary</h3>
                <div id="checkout-cart-container"></div>
                <h4 class="checkout-sub-total">Subtotal: <span id="checkout-subtotal-value">$0.00</span></h4>
                <h4 class="shipping-cost">Shipping Cost: <span id="checkout-shipping-value">$0.00</span></h4>
                <h4 class="checkout-total">Total: <span id="checkout-total-value">$0.00</span></h4>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shippingOptions = document.querySelectorAll('.shipping-option');
    const hiddenInput = document.getElementById('shipping-method');
    const pickupSection = document.getElementById('pickup-section');
    const deliverySection = document.getElementById('delivery-section');
    const deliveryAddressFields = document.getElementById('delivery-address-fields');
    const pageTitle = document.querySelector('.page-title');

    const checkoutStores = window.checkoutStores || [];
    const googleMapsApiKey = window.googleMapsApiKey || '';
    const deliveryDistanceError = document.getElementById('delivery-distance-error');

      // === Stock modal plumbing ===
const modal = {
  mask: document.getElementById('stockModalMask'),
  wrap: document.getElementById('stockModalWrap'),
  modal:document.querySelector('.modal'),
  msg: document.getElementById('stockModalTitle'),
  list: document.getElementById('stockConflictList'),
  btnAlt: document.getElementById('stockModalAltBtn'),
  btnGo: document.getElementById('stockModalContinue'),
  appbody:document.body,
  open(){ this.mask.style.display='block'; this.wrap.style.display='flex'; this.modal.style.display='flex'; this.appbody.style.overflowY='hidden';},
  close(){ this.mask.style.display='none'; this.wrap.style.display='none';  this.modal.style.display='none';this.appbody.style.overflowY='auto';}
};

function productBaseName(rawName){
  if (!rawName) return '';
  return rawName.toLowerCase().includes('corrugated boxes') ? 'Corrugated Boxes' : rawName;
}
function productSizeFrom(rawName){
  if (!rawName) return '';
  const base = productBaseName(rawName);
  return base === 'Corrugated Boxes' ? rawName.replace(/corrugated boxes/i, '').trim() : '';
}

// Compute conflicts for a store key via server (uses existing Ecwid server creds)
async function fetchStockDetail(storeKey){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const cart = getCart();
  const res = await fetch("{{ route('checkout.stock_detail') }}",{
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
    credentials:'same-origin',
    body: JSON.stringify({ items: cart, store_key: storeKey })
  });
  return res.json();
}

// Render conflict list; returns true if all resolved
function renderConflictUI(detail){
  modal.list.innerHTML = '';
  let allResolved = true;

  const cart = getCart(); // latest localStorage state
  const byId = Object.fromEntries(cart.map(i => [String(i.productId), i]));

  (detail.items || []).forEach(row => {
    // Only show items that are short (or unknown)
    const shortageFromServer = Number(row.shortage || 0);
    const availableFromServer = (row.available === null || typeof row.available === 'undefined')
      ? null
      : Number(row.available);

    // If neither shortage nor unknown, skip
    const mustShow = (availableFromServer === null) || (shortageFromServer > 0);
    if (!mustShow) return;

    const item = byId[String(row.productId)];
    const currentQty = item ? (parseInt(item.quantity, 10) || 0) : 0;

    // Qty available to display (static), clamp negatives to 0
    const available = Math.max(0, (availableFromServer ?? 0));

    // If cart qty exceeds available, not resolved yet
    if (currentQty > available) allResolved = false;

    const rawName = item?.product || row.name || '';
    const name = productBaseName(rawName);
    const size = productSizeFrom(rawName);
    const thumb = (item?.productThumb && item.productThumb.trim() !== '')
      ? item.productThumb
      : (row.thumb && row.thumb.trim() !== '' ? row.thumb
      : `${window.location.origin}/boxcity/public/assets/Placeholder.png`);

    const incDisabled = currentQty >= available; // can’t exceed available
    const decDisabled = currentQty <= 1;         // don’t go < 1 via minus (trash removes)

    const el = document.createElement('div');
    el.className = 'conflict-item';
    el.setAttribute('data-id', String(row.productId));
    el.setAttribute('data-available', String(available)); // keep for click handlers
    el.innerHTML = `
      <img src="${thumb}" alt="">
      <div class="conflict-item-details" >
        <div class="conflict-item-info">
        <div class="name">${name}</div>
        ${size ? `<div class="size">Size: ${size}</div>` : ''}
        </div>
        <div class="conflict-item-qty-controls-container">
        <div class="qty-controls">
            <div class="conflict-item-qty-btns">
          <button class="qty-btn dec" ${decDisabled ? 'disabled' : ''}>-</button>
          <span class="qty-val">${currentQty}</span>
          <button class="qty-btn inc" ${incDisabled ? 'disabled' : ''}>+</button>
          </div>
          <svg class="trash" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" role="button" tabindex="0">
            <title>Remove item</title>
            <path d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke="#444" stroke-width="1.5"/>
          </svg>
        </div>
        <div class="unavailable">Qty available: <span class="u-val">${available}</span></div>
        </div>
      </div>
    `;
    modal.list.appendChild(el);
  });

  modal.btnGo.disabled = !allResolved;
  return allResolved;
}


// Attach handlers (increment/decrement/trash) – keep order summary + notes in sync
// Instant, optimistic updates in the modal (no async/await here)
modal.list.addEventListener('click', (e) => {
  const card = e.target.closest('.conflict-item');
  if (!card) return;

  const id = String(card.getAttribute('data-id'));
  const available = Math.max(0, parseInt(card.getAttribute('data-available'), 10) || 0);
  let cart = getCart();
  const idx = cart.findIndex(x => String(x.productId) === id);
  if (idx === -1) return;

  const isInc   = e.target.classList.contains('inc');
  const isDec   = e.target.classList.contains('dec');
  const isTrash = e.target.closest('.trash');

  const qtySpan   = card.querySelector('.qty-val');
  const btnInc    = card.querySelector('.qty-btn.inc');
  const btnDec    = card.querySelector('.qty-btn.dec');
  let   currentQty = parseInt(qtySpan.textContent, 10) || 0;

  // --- instant local changes (mirror Order Summary behavior) ---
  if (isTrash) {
    cart.splice(idx, 1);
    card.remove(); // remove row immediately
  } else if (isInc) {
    const next = Math.min(available, currentQty + 1);   // clamp to available
    cart[idx].quantity = next;
    qtySpan.textContent = String(next);
  } else if (isDec) {
    const next = Math.max(1, currentQty - 1);
    cart[idx].quantity = next;
    qtySpan.textContent = String(next);
  }

  // Update button disabled states instantly
  const newQty = isTrash ? 0 : parseInt(qtySpan.textContent, 10) || 0;
  if (!isTrash) {
    btnInc.disabled = newQty >= available;
    btnDec.disabled = newQty <= 1;
  }

  // Persist + update Order Summary instantly
  applyGroupPricingForCheckout(cart);
  saveCart(cart);
  renderCheckoutCart();

  // Enable/disable Continue based on whether *any* item still exceeds available
  const unresolved = Array.from(modal.list.querySelectorAll('.conflict-item')).some(ci => {
    const av = Math.max(0, parseInt(ci.getAttribute('data-available'), 10) || 0);
    const q  = parseInt(ci.querySelector('.qty-val')?.textContent || '0', 10) || 0;
    return q > av;
  });
  modal.btnGo.disabled = unresolved;

  // Fire-and-forget: sync server + refresh pickup notes (don’t block UI)
  Promise.resolve().then(() => {
    syncCartNoReload();
    refreshPickupNotes();
  });

  // Optional: refresh conflict data in the background so the UI stays honest
  // (does not block instant feedback)
  Promise.resolve().then(async () => {
    if (!modal._storeKey) return;
    const detail = await fetchStockDetail(modal._storeKey);
    renderConflictUI(detail);
  });
});


// Modal flow helpers
function showStockModal({method, storeKey}){
  const pickup = (method === 'Pickup method');
  modal.msg.textContent = pickup
    ? 'Remove unavailable items or choose a different location.'
    : 'Remove unavailable items or pick up from a different location.';
  modal.btnAlt.textContent = pickup ? 'Change Location' : 'Pickup Instead';
  modal.btnGo.textContent = 'Continue';
  modal._storeKey = storeKey;
  modal.open();
}

modal.btnAlt.addEventListener('click', () => {
  const method = hiddenInput ? hiddenInput.value : 'Delivery details';
  if (method !== 'Pickup method') {
    // Switch to pickup instead
    setShippingMethod('Pickup method');
    document.getElementById('pickup-location')?.focus();
  }
  modal.close();
});

// When "Continue" is enabled (allResolved), we proceed with the checkout again.
modal.btnGo.addEventListener('click', () => {
  if (modal.btnGo.disabled) return;
  modal.close();
  // Trigger Stripe button again (same validation path)
  document.getElementById('stripe-checkout-button')?.click();
});

// Gatekeeper: check selected store’s stock; open modal if conflict
async function checkStockBeforeCheckout(method, selectedStoreKey){
  try {
    if (!selectedStoreKey) return true; // nothing to check, let it pass
    const detail = await fetchStockDetail(selectedStoreKey);
    if (!detail || !detail.ok) return true; // fail-open to avoid blocking purchases on noise

    // If any shortages, show modal and render it
    if (detail.anyShortage) {
      showStockModal({ method, storeKey: selectedStoreKey });
      renderConflictUI(detail);
      return false; // stop the normal flow; modal takes over
    }
    return true;
  } catch (err) {
    console.warn('checkStockBeforeCheckout error', err);
    return true; // fail-open
  }
}

    function showDeliveryError(message) {
        if (deliveryDistanceError) {
            deliveryDistanceError.textContent = message;
            deliveryDistanceError.style.display = 'block';
        } else {
            alert(message);
        }
    }

    function clearDeliveryError() {
        if (deliveryDistanceError) {
            deliveryDistanceError.textContent = '';
            deliveryDistanceError.style.display = 'none';
        }
    }
    function haversineMiles(lat1, lon1, lat2, lon2) {
        const toRad = (value) => (value * Math.PI) / 180;
        const earthRadiusMiles = 3958.8;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return earthRadiusMiles * c;
    }

        async function geocodeAddress(fullAddress) {
        if (!googleMapsApiKey) {
            throw new Error('Delivery validation is temporarily unavailable.');
        }

        const response = await fetch(
            `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(fullAddress)}&key=${googleMapsApiKey}`
        );
        if (!response.ok) {
            throw new Error('Unable to validate delivery address. Try again.');
        }

        const data = await response.json();
        if (data.status !== 'OK' || !data.results.length) {
            throw new Error('We could not locate that address. Please confirm it is correct.');
        }

        const result = data.results[0];
        const locationType =
            result.geometry && result.geometry.location_type
                ? result.geometry.location_type
                : '';
        const allowedTypes = ['ROOFTOP', 'RANGE_INTERPOLATED', 'GEOMETRIC_CENTER'];

        if (!allowedTypes.includes(locationType)) {
            throw new Error(
                'We could not validate that address precisely. Please confirm the street and house number.'
            );
        }

        const location = result.geometry.location;
        return { lat: location.lat, lng: location.lng, locationType };
    }

    async function validateDeliveryCoverage(addressParts) {
        clearDeliveryError();

        const { street, city, state, zip } = addressParts;
        if (!street || !city || !state || !zip) {
            showDeliveryError('Please provide a complete delivery address.');
            return { ok: false };
        }

        let coordinates;
        try {
            coordinates = await geocodeAddress(`${street}, ${city}, ${state} ${zip}`);
        } catch (error) {
            showDeliveryError(error.message || 'Unable to verify your delivery address.');
            return { ok: false };
        }

        let closestStore = null;
        let minimumDistance = Infinity;

        checkoutStores.forEach((store) => {
            if (typeof store.lat === 'number' && typeof store.lon === 'number') {
                const distance = haversineMiles(
                    coordinates.lat,
                    coordinates.lng,
                    store.lat,
                    store.lon
                );
                if (distance < minimumDistance) {
                    minimumDistance = distance;
                    closestStore = store;
                }
            }
        });

        if (!closestStore) {
            showDeliveryError('Unable to determine the nearest store for delivery.');
            return { ok: false };
        }

        if (minimumDistance > 30) {
            const miles = minimumDistance.toFixed(1);
            const name = closestStore.label || 'our nearest location';
            showDeliveryError(
                `Delivery is available within 30 miles of our stores. The closest store (${name}) is ${miles} miles away.`
            );
            return { ok: false };
        }

        if (!closestStore.delivery_destination_zone) {
            showDeliveryError(
                'Delivery zone is not configured for the nearest store. Please contact us to complete your order.'
            );
            return { ok: false };
        }

        return {
            ok: true,
            store: closestStore,
            distance: minimumDistance,
            destinationZoneName: closestStore.delivery_destination_zone
        };
    }


    const $cartContainer = $('#checkout-cart-container');
    const $subTotalRow = $('.checkout-sub-total');
    const $shippingRow = $('.shipping-cost');
    const $subTotalValue = $('#checkout-subtotal-value');
    const $shippingValue = $('#checkout-shipping-value');
    const $totalValue = $('#checkout-total-value');

    const deliveryFieldErrors = [
        'delivery-country-error',
        'delivery-state-error',
        'delivery-city-error',
        'delivery-zip-error',
        'delivery-address-error'
    ];

    function parseCurrency(value) {
        if (!value) {
            return 0;
        }
        const cleaned = value.toString().replace(/[^0-9.]/g, '');
        return cleaned ? parseFloat(cleaned) : 0;
    }

    function getCart() {
        const stored = localStorage.getItem('cart');
        console.log(stored)
        if (!stored) {
            return [];
        }
        try {
            return JSON.parse(stored);
        } catch (error) {
            console.error('Unable to parse cart from localStorage', error);
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function getGroupKey(name) {
        if (!name) {
            return '';
        }
        return name.toLowerCase().includes('corrugated boxes') ? 'Corrugated Boxes' : name;
    }

    function applyGroupPricingForCheckout(cart) {
        const grouped = {};
        cart.forEach(item => {
            const key = getGroupKey(item.product || '');
            if (!grouped[key]) {
                grouped[key] = [];
            }
            grouped[key].push(item);
        });

        Object.values(grouped).forEach(items => {
            const totalQty = items.reduce((sum, entry) => sum + (parseInt(entry.quantity, 10) || 0), 0);

            items.forEach(entry => {
                const retail = parseFloat(entry.retailPrice) || parseFloat(entry.price) || 0;
                const bulkOne = parseFloat(entry.priceBulkOne) || retail;
                const bulkTwo = parseFloat(entry.priceBulkTwo) || bulkOne;
                const bulkThree = parseFloat(entry.priceBulkThree) || bulkTwo;

                if (totalQty >= 100) {
                    entry.price = bulkThree;
                } else if (totalQty >= 50) {
                    entry.price = bulkTwo;
                } else if (totalQty >= 12) {
                    entry.price = bulkOne;
                } else {
                    entry.price = retail;
                }
            });
        });
    }

    function updateCheckout(subtotalOverride) {
        const subtotal = typeof subtotalOverride === 'number' ? subtotalOverride : parseCurrency($subTotalValue.text());
        $subTotalRow.show();
        $shippingRow.show();

        let shipping = 0;
        if (subtotal === 0) {
            $shippingValue.text('$0.00');
        } else if (subtotal >= 300) {
            $shippingValue.text('Free');
        } else {
            shipping = 30;
            $shippingValue.text(`$${shipping.toFixed(2)}`);
        }

        const total = subtotal + shipping;
        $totalValue.text(`$${total.toFixed(2)}`);
    }
    function applyPickupMethod(subtotalOverride) {
        const subtotal = typeof subtotalOverride === 'number' ? subtotalOverride : parseCurrency($subTotalValue.text());
        $subTotalRow.hide();
        $shippingRow.hide();
        $shippingValue.text('$0.00');
        $totalValue.text(`$${subtotal.toFixed(2)}`);
    }
    function updatePageTitle(method) {
        if (!pageTitle) {
            return;
        }
        pageTitle.textContent = method === 'Pickup method' ? 'Pickup Details' : 'Shipping Details';
    }

    function clearDeliveryErrors() {
        deliveryFieldErrors.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.innerText = '';
            }
        });
    }

    function renderCheckoutCart() {
        let cart = getCart();
        applyGroupPricingForCheckout(cart);
        saveCart(cart);

        $cartContainer.empty();

        if (!cart.length) {
            $cartContainer.append('<p>Your cart is empty.</p>');
            $subTotalValue.text('$0.00');
            if (hiddenInput && hiddenInput.value === 'Pickup method') {
                applyPickupMethod(0);
            } else {
                updateCheckout(0);
            }
            return;
        }
        let subtotal = 0;
        cart.forEach(item => {
            const quantity = parseInt(item.quantity, 10) || 0;
            const price = parseFloat(item.price) || 0;
            const itemTotal = price * quantity;
            subtotal += itemTotal;

            const rawName = item.product || '';
            const productName = rawName.toLowerCase().includes('corrugated boxes') ? 'Corrugated Boxes' : rawName;
            const productSize = productName === 'Corrugated Boxes' ? rawName.replace(productName, '').trim() : '';
            const placeholder = `${window.location.origin}/boxcity/public/assets/Placeholder.png`;
            const thumb = item.productThumb && item.productThumb.trim() !== '' ? item.productThumb : placeholder;

            $cartContainer.append(`
                <div class="cart-item-card" data-product-id="${item.productId}">
                    <div class="product-image-container">
                        <img src="${thumb}" alt="${rawName}">
                    </div>
                    <div class="product-details">
                        <div class="product-name">${productName}</div>
                        ${productSize ? `<div class="product-size"><span>Size:</span> ${productSize}</div>` : ''}
                        <div class="quantity-controls">
                            <button class="quantity-btn decrement minus" data-id="${item.productId}">-</button>
                            <span class="quantity-value">${quantity}</span>
                            <button class="quantity-btn increment plus" data-id="${item.productId}">+</button>
                        </div>
                    </div>
                </div>
            `);
        });

        $subTotalValue.text(`$${subtotal.toFixed(2)}`);

        if (hiddenInput && hiddenInput.value === 'Pickup method') {
            applyPickupMethod(subtotal);
        } else {
            updateCheckout(subtotal);
        }
        
    }

    function setShippingMethod(method) {
        shippingOptions.forEach(option => {
            option.classList.toggle('active', option.dataset.method === method);
        });

        if (hiddenInput) {
            hiddenInput.value = method;
        }
        const currentSubtotal = parseCurrency($subTotalValue.text());

        if (method === 'Pickup method') {
            if (pickupSection) {
                pickupSection.classList.remove('d-none');
            }
            if (deliverySection) {
                deliverySection.classList.add('d-none');
            }
            if (deliveryAddressFields) {
                deliveryAddressFields.classList.add('d-none');
            }
            clearDeliveryErrors();
            applyPickupMethod(currentSubtotal);
        } else {
            if (pickupSection) {
                pickupSection.classList.add('d-none');
            }
            if (deliverySection) {
                deliverySection.classList.remove('d-none');
            }
            if (deliveryAddressFields) {
                deliveryAddressFields.classList.remove('d-none');
            }
            updateCheckout(currentSubtotal);
        }

        updatePageTitle(method);
    }

    shippingOptions.forEach(option => {
        option.addEventListener('click', () => {
            setShippingMethod(option.dataset.method);
        });
    });

    const defaultOption = document.querySelector('.shipping-option.active') ||
        document.querySelector('.shipping-option[data-method="Delivery details"]');
    setShippingMethod(defaultOption ? defaultOption.dataset.method : 'Delivery details');

    $(document).on('click', '.quantity-btn', async function (event) {
        event.preventDefault();

        const $button = $(this);
        const productId = $button.closest('.cart-item-card').data('product-id');

        let cart = getCart();
        const item = cart.find(entry => entry.productId == productId);

        if (!item) {
            return;
        }
        const currentQty = parseInt(item.quantity, 10) || 0;

        if ($button.hasClass('increment')) {
            item.quantity = currentQty + 1;
        } else if ($button.hasClass('decrement')) {
            item.quantity = Math.max(1, currentQty - 1);
        }

        applyGroupPricingForCheckout(cart);
        saveCart(cart);
        renderCheckoutCart();
    await syncCartNoReload();
    await refreshPickupNotes();
    });

    function normalizePhone(value) {
        if (!value) {
            return '';
        }
        const digits = value.replace(/[^0-9]/g, '');
        if (!digits) {
            return '';
        }
        if (digits.length === 11 && digits.startsWith('1')) {
            return '+' + digits;
        }
        if (digits.length === 10) {
            return '+1' + digits;
        }
        if (value.startsWith('+')) {
            return value;
        }
        return '+' + digits;
    }

    function formatPickupDate(raw) {
        if (!raw) {
            return '';
        }
        const date = new Date(raw + 'T00:00:00');
        if (Number.isNaN(date.getTime())) {
            return '';
        }
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function buildOrderPayload(options) {
        const cart = getCart();
        applyGroupPricingForCheckout(cart);
        saveCart(cart);

        const subtotal = cart.reduce((sum, item) => {
            const price = parseFloat(item.price) || 0;
            const qty = parseInt(item.quantity, 10) || 0;
            return sum + price * qty;
        }, 0);

        const method = options.method;
        const shipping = method === 'Delivery details' && subtotal > 0 && subtotal < 300 ? 30 : 0;
        const total = subtotal + shipping;
        const normalizedPhone = normalizePhone(options.phone);
        const fullName = `${options.firstName} ${options.lastName}`.trim();

        const mapItem = (item) => ({
            productId: item.productId,
            name: item.product || '',
            sku: item.sku || '',
            quantity: parseInt(item.quantity, 10) || 0,
            price: parseFloat(item.price) || 0,
            priceWithoutTax: parseFloat(item.price) || 0,
            productPrice: parseFloat(item.price) || 0,
            weight: parseFloat(item.weight) || 0
        });

        const order = {
            subtotal,
            subtotalWithoutTax: subtotal,
            total,
            totalWithoutTax: total,
            email: options.email,
            paymentMethod: 'Credit or Debit card.',
            paymentSubtype: 'manual',
            paymentStatus: 'PAID',
            tax: 0,
            customerTaxExempt: false,
            customerTaxId: '',
            customerTaxIdValid: false,
            reversedTaxApplied: false,
            fulfillmentStatus: 'AWAITING_PROCESSING',
            items: cart.map(mapItem),
            billingPerson: {
                name: fullName || '',
                companyName: '',
                firstName: options.firstName || '',
                lastName: options.lastName || '',
                street: method === 'Delivery details' ? options.deliveryAddress : '',
                city: method === 'Delivery details' ? options.deliveryCity : '',
                countryCode: 'US',
                postalCode: method === 'Delivery details' ? options.deliveryZip : '',
                stateOrProvinceCode: method === 'Delivery details' ? options.deliveryState : '',
                phone: normalizedPhone
            },
            shippingPerson: {
                name: fullName || '',
                companyName: '',
                firstName: options.firstName || '',
                lastName: options.lastName || '',
                street: method === 'Delivery details' ? options.deliveryAddress : '',
                city: method === 'Delivery details' ? options.deliveryCity : '',
                countryCode: 'US',
                postalCode: method === 'Delivery details' ? options.deliveryZip : '',
                stateOrProvinceCode: method === 'Delivery details' ? options.deliveryState : '',
                phone: normalizedPhone
            },
            shippingOption: {
                shippingMethodName: options.shippingMethodName,
                shippingRate: shipping,
                fulfillmentType: method === 'Pickup method' ? 'PICKUP' : 'DELIVERY'
            },
            hidden: false,
            privateAdminNotes: '',
            acceptMarketing: false,
            disableAllCustomerNotifications: false,
            externalFulfillment: false,
            externalOrderId: '',
            pricesIncludeTax: false
        };

        if (options.pickupDate) {
            order.orderExtraFields = [{
                id: 'zti72gx',
                value: options.pickupDate,
                title: 'Choose Pick Up day',
                orderDetailsDisplaySection: 'shipping_info',
                orderBy: "1",
                showInNotifications: false,
                showInInvoice: false,
                saveToCustomerProfile: false
            }];
        }

        return { order, totals: { subtotal, shipping, total } };
    }

    // stripe flow
    const stripeButton = document.getElementById('stripe-checkout-button');
    if (stripeButton) {
        stripeButton.setAttribute('type', 'button');
        const buttonDefaultLabel = stripeButton.innerText;

        stripeButton.addEventListener('click', async (event) => {
            event.preventDefault();

            if (stripeButton.disabled) {
                return;
            }

            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const firstNameInput = document.getElementById('first-name');
            const lastNameInput = document.getElementById('last-name');
            const deliveryAddressInput = document.getElementById('address-text');
            const deliveryCityInput = document.getElementById('shipping-city');
            const deliveryStateSelect = document.getElementById('shipping-state');
            const deliveryZipInput = document.getElementById('shipping-zip');
            const deliveryLocationSelect = document.getElementById('delivery-location');
            const pickupLocationSelect = document.getElementById('pickup-location');
            const pickupDateInput = document.getElementById('pickup-date');

            const email = emailInput ? emailInput.value.trim() : '';
            const phone = phoneInput ? phoneInput.value.trim() : '';
            const firstName = firstNameInput ? firstNameInput.value.trim() : '';
            const lastName = lastNameInput ? lastNameInput.value.trim() : '';
            const method = hiddenInput ? hiddenInput.value : 'Delivery details';

            const phoneError = document.getElementById('phone-error');
            const emailError = document.getElementById('email-error');
            const firstNameError = document.getElementById('fname-error');
            const lastNameError = document.getElementById('lname-error');

            const phonePattern = /^(\+1\s?)?(\([0-9]{3}\)|[0-9]{3})[-\s]?[0-9]{3}[-\s]?[0-9]{4}$/;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            let isValid = true;
            let firstInvalidField = null;

            if (!phonePattern.test(phone)) {
                if (phoneError) {
                    phoneError.innerText = 'Please enter a valid phone number.';
                }
                isValid = false;
                firstInvalidField = firstInvalidField || phoneInput;
            } else if (phoneError) {
        
                phoneError.innerText = '';
            }

            if (!emailPattern.test(email)) {
                if (emailError) {
                    emailError.innerText = 'Please enter a valid email address.';
                }
                isValid = false;
                firstInvalidField = firstInvalidField || emailInput;
            } else if (emailError) {
                emailError.innerText = '';
            }

            if (!firstName) {
                if (firstNameError) {
                    firstNameError.innerText = 'First name is required.';
                }
                isValid = false;
                firstInvalidField = firstInvalidField || firstNameInput;
            } else if (firstNameError) {
                firstNameError.innerText = '';
            }

            if (!lastName) {
                if (lastNameError) {
                    lastNameError.innerText = 'Last name is required.';
                }
                isValid = false;
                firstInvalidField = firstInvalidField || lastNameInput;
            } else if (lastNameError) {
                lastNameError.innerText = '';
            }

            const deliveryAddress = deliveryAddressInput ? deliveryAddressInput.value.trim() : '';
            const deliveryCity = deliveryCityInput ? deliveryCityInput.value.trim() : '';
            const deliveryStateRaw = deliveryStateSelect ? deliveryStateSelect.value : '';
            const deliveryState = deliveryStateRaw && deliveryStateRaw !== 'Select State' ? deliveryStateRaw : '';
            const deliveryZip = deliveryZipInput ? deliveryZipInput.value.trim() : '';

            if (method === 'Delivery details') {
                if (!deliveryAddress && document.getElementById('delivery-address-error')) {
                    document.getElementById('delivery-address-error').innerText = 'Street address is required.';
                    isValid = false;
                    firstInvalidField = firstInvalidField || deliveryAddressInput;
                } else if (document.getElementById('delivery-address-error')) {
                    document.getElementById('delivery-address-error').innerText = '';
                }

                if (!deliveryCity && document.getElementById('delivery-city-error')) {
                    document.getElementById('delivery-city-error').innerText = 'City is required.';
                    isValid = false;
                    firstInvalidField = firstInvalidField || deliveryCityInput;
                } else if (document.getElementById('delivery-city-error')) {
                    document.getElementById('delivery-city-error').innerText = '';
                }

                if (!deliveryState && document.getElementById('delivery-state-error')) {
                    document.getElementById('delivery-state-error').innerText = 'Please select a state.';
                    isValid = false;
                    firstInvalidField = firstInvalidField || deliveryStateSelect;
                } else if (document.getElementById('delivery-state-error')) {
                    document.getElementById('delivery-state-error').innerText = '';
                }

                const zipPattern = /^[0-9]{4,10}$/;
                if (!zipPattern.test(deliveryZip)) {
                    if (document.getElementById('delivery-zip-error')) {
                        document.getElementById('delivery-zip-error').innerText = 'Please enter a valid Zip Code.';
                    }
                    isValid = false;
                    firstInvalidField = firstInvalidField || deliveryZipInput;
                } else if (document.getElementById('delivery-zip-error')) {
                    document.getElementById('delivery-zip-error').innerText = '';
                }
            } else {
                clearDeliveryErrors();
            }

            if (!isValid) {
                if (firstInvalidField && typeof firstInvalidField.focus === 'function') {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidField.focus();
                }
                return;
            }

            const cart = getCart();
            if (!cart.length) {
                alert('Your cart is empty. Add items before placing an order.');
                return;
            }

            const pickupOption = pickupLocationSelect && pickupLocationSelect.selectedIndex >= 0
                ? pickupLocationSelect.options[pickupLocationSelect.selectedIndex]
                : null;
            const pickupMethodName = pickupOption ? pickupOption.value : 'In-Store Pickup';

            let deliveryMethodName = deliveryLocationSelect ? deliveryLocationSelect.value : 'Local delivery';
            let selectedDeliveryStore = null;

            if (method === 'Delivery details') {
                stripeButton.disabled = true;
                stripeButton.innerText = 'Validating address...';

                const coverageResult = await validateDeliveryCoverage({
                    street: deliveryAddress,
                    city: deliveryCity,
                    state: deliveryState,
                    zip: deliveryZip
                });

                if (!coverageResult.ok) {
                    stripeButton.disabled = false;
                    stripeButton.innerText = buttonDefaultLabel;
                    return;
                }

                stripeButton.disabled = false;
                stripeButton.innerText = buttonDefaultLabel;

                selectedDeliveryStore = coverageResult.store;
                deliveryMethodName = coverageResult.destinationZoneName || selectedDeliveryStore.label || deliveryMethodName;

                if (deliveryLocationSelect && selectedDeliveryStore && selectedDeliveryStore.value) {
                    deliveryLocationSelect.value = selectedDeliveryStore.value;
                }
            } else {
                clearDeliveryError();
            }

            const formattedPickupDate = formatPickupDate(pickupDateInput ? pickupDateInput.value : '');

            const { order: orderPayload, totals } = buildOrderPayload({
                method,
                firstName,
                lastName,
                email,
                phone,
                deliveryAddress,
                deliveryCity,
                deliveryState,
                deliveryZip,
                shippingMethodName: method === 'Pickup method' ? pickupMethodName : deliveryMethodName,
                pickupDate: method === 'Pickup method' ? formattedPickupDate : ''
            });

            const amount = totals.total;
            // === BLOCK checkout when chosen store can’t fulfill ===
let selectedStoreKey = '';
if (method === 'Pickup method') {
  // pickup: use <option data-key="">
  const opt = pickupLocationSelect && pickupLocationSelect.options[ pickupLocationSelect.selectedIndex ];
  selectedStoreKey = opt ? (opt.getAttribute('data-key') || '') : '';
} else {
  // delivery: use the nearest store you already resolved
  // selectedDeliveryStore is set above when validating address
  selectedStoreKey = (selectedDeliveryStore && selectedDeliveryStore.key) ? selectedDeliveryStore.key : '';
}

// If shortages exist for the chosen location, show the modal and stop here
const okToContinue = await checkStockBeforeCheckout(method, selectedStoreKey);
if (!okToContinue) {
  stripeButton.disabled = false;
  stripeButton.innerText = buttonDefaultLabel;
  return;
}

            if (amount <= 0) {
                alert('Invalid order total.');
                return;
            }


            try {
                stripeButton.disabled = true;
                stripeButton.innerText = 'Processing...';

                const response = await fetch("{{ route('stripe.create') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        amount: amount,
                        email: email,
                        order: orderPayload
                    })
                });

                let data;
                try {
                    data = await response.json();
                } catch (parseError) {
                    console.error('Unable to parse Stripe session response', parseError);
                    throw new Error('Unable to start payment session. Please try again.');
                }

                if (!response.ok || (data && data.error)) {
                    const message = data && data.error ? data.error : 'Unable to start payment session.';
                    throw new Error(message);
                }

            if (!data || !data.stripe_url) { 
                throw new Error('Missing Stripe checkout URL from server.');
            }
            stripeButton.innerText = 'Redirecting…';
            window.location.assign(data.stripe_url);
            return;
            } catch (error) {
                console.error('Stripe Checkout failed', error);
                alert(error.message || 'Unable to start Stripe Checkout. Please try again.');
                stripeButton.disabled = false;
                stripeButton.innerText = buttonDefaultLabel;
                return;
            }
        });
    }

    $(document).on('click', '.iti__selected-dial-code', function (event) {
        event.preventDefault();
    });

    const phoneInput = document.querySelector('#phone');
    if (phoneInput && window.intlTelInput) {
        window.intlTelInput(phoneInput, {
            initialCountry: 'us',
            onlyCountries: ['us'],
            separateDialCode: true,
            autoPlaceholder: 'off',
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
        });
    }

    function updatePickupDetails() {
        const select = document.getElementById('pickup-location');
        if (!select) {
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) {
            return;
        }

        const address = selectedOption.getAttribute('data-address') || '';
        const city = selectedOption.getAttribute('data-city') || '';
        const zip = selectedOption.getAttribute('data-zip') || '';
        const country = selectedOption.getAttribute('data-country') || '';
        const email = selectedOption.getAttribute('data-email') || '';
        const hours = selectedOption.getAttribute('data-hours') || '';

        const details = document.getElementById('pickup-details');
        if (!details) {
            return;
        }

        details.innerHTML = `
            <div class="location-address">
                <p><strong>Pickup location</strong></p>
                <p>${address}</p>
                <p>${city}${zip ? ', ' + zip : ''}</p>
                <p>${country}</p>
                <p>${email}</p>
                <p><strong>Business hours</strong></p>
                <p>${hours}</p>
            </div>
        `;
    }

    const pickupLocation = document.getElementById('pickup-location');
    if (pickupLocation) {
        pickupLocation.addEventListener('change', updatePickupDetails);
    }
    updatePickupDetails();

    renderCheckoutCart();

    window.updateCheckout = updateCheckout;
    window.applyPickupMethod = applyPickupMethod;
    window.renderCheckoutCart = renderCheckoutCart;
});
</script>


<script>
(function () {
  var select = document.getElementById('pickup-location');
  var hidden = document.getElementById('shipping_option_id');
  function sync() {
    var opt = select && select.options[select.selectedIndex];
    hidden.value = opt ? (opt.getAttribute('data-id') || '') : '';
  }
  sync();
  if (select) select.addEventListener('change', sync);
})();
</script>

<div id="stockModalMask" class="modal-mask"></div>
<div id="stockModalWrap" class="modal-wrap" style="display:none;">
  <div id="stockModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="stockModalTitle">
    
    <header id="stockModalTitle"></header>
    <div class="body">
      
      <div id="stockConflictList"></div>
    </div>
    <footer>
      <button id="stockModalAltBtn" type="button" class="btn-secondary"></button>
      <button id="stockModalContinue" type="button" class="btn-primary" disabled>Continue</button>
    </footer>
  </div>
</div>

@endsection