@extends('layouts.app')
@section('title', 'POS')
@push('styles')
<style>
.product-card { transition: transform 0.15s, box-shadow 0.15s; position: relative; z-index: 1; }
.product-card:hover { transform: translateY(-2px); box-shadow: 0 0 0 2px #111, 0 8px 20px rgba(0,0,0,0.1); z-index: 10; }
#cart-items::-webkit-scrollbar { width: 4px; }
#cart-items::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
.pos-modal:not(.hidden) { display: flex; }
</style>
@endpush
@section('content')
<div class="flex gap-4 h-[calc(100vh-6rem)]">

    <!-- LEFT: Product Grid -->
    <div class="flex-1 flex flex-col min-w-0">
        <div class="bg-white rounded-xl shadow p-3 md:p-4 mb-3 md:mb-4">
            <div class="flex flex-wrap gap-2 md:gap-3">
                <div class="flex-1 min-w-0 relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="text" id="search-input" placeholder="Search products... (F2)"
                        class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm">
                </div>
                <select id="category-filter" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand max-w-[140px] md:max-w-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button id="fullscreen-btn" onclick="toggleFullscreen()" title="Fullscreen (F11)"
                    class="hidden md:flex flex-shrink-0 border border-gray-300 hover:bg-gray-100 text-gray-600 px-3 rounded-lg transition items-center">
                    <i id="fullscreen-icon" class="fas fa-expand"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pb-24 md:pb-1">
            <!-- Custom Item card (always visible) -->
            <div class="px-1 pt-1 pb-0">
                <button onclick="openCustomItemModal()"
                    class="w-full flex items-center gap-3 bg-white border-2 border-dashed border-gray-300 hover:border-gray-500 hover:bg-gray-50 rounded-xl px-4 py-3 text-sm text-gray-500 hover:text-gray-800 transition">
                    <i class="fas fa-plus-circle text-base"></i>
                    <span class="font-medium">Add Custom Item</span>
                    <span class="ml-auto text-xs text-gray-400">Sell without selecting a product</span>
                </button>
            </div>
            <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-3 p-1 pt-2">
                <div class="col-span-full text-center py-12 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
                    <p>Loading products...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Cart — drawer on mobile, sidebar on desktop -->
    <div id="cart-panel"
        class="fixed inset-x-0 bottom-0 z-30 max-h-[85vh] rounded-t-2xl translate-y-full
               md:relative md:inset-auto md:bottom-auto md:z-auto md:max-h-none md:h-full md:w-96 md:rounded-xl md:translate-y-0
               flex-shrink-0 bg-white shadow-2xl md:shadow flex flex-col overflow-visible transition-transform duration-300">
        {{-- Mobile drag handle --}}
        <div class="md:hidden flex justify-center pt-2 pb-1 cursor-pointer" onclick="closeMobileCart()">
            <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
        </div>
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-shopping-cart text-gray-900"></i> Cart
                <span id="cart-count" class="bg-gray-900 text-white text-xs rounded-full px-2 py-0.5 ml-auto">0</span>
                <button onclick="closeMobileCart()" class="md:hidden text-gray-400 hover:text-gray-700 text-xl ml-1">&times;</button>
            </h2>
        </div>

        <!-- Customer Lookup -->
        <div class="px-4 pt-3 pb-2 border-b border-gray-100">
            <div class="relative">
                <div id="customer-wrap" class="relative flex gap-2">
                    <div class="flex-1 relative">
                        <i class="fas fa-user absolute left-3 top-2.5 text-gray-400 text-xs z-10"></i>
                        <input type="text" id="customer-search" placeholder="Search customer..."
                            class="w-full pl-8 pr-4 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-brand bg-gray-50"
                            autocomplete="off">
                    </div>
                    <button onclick="openQuickAdd('')"
                        class="flex-shrink-0 bg-brand hover:bg-brand-dark text-white px-3 py-2 rounded-lg text-xs font-medium transition"
                        title="Add new customer">
                        <i class="fas fa-user-plus"></i>
                    </button>
                </div>
                <div id="customer-dropdown" class="absolute left-0 right-10 z-30 bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto"></div>
            </div>
            <div id="customer-selected" class="hidden mt-2 flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                <i class="fas fa-user-check text-gray-700 text-xs"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-800 truncate" id="sel-name"></p>
                    <p class="text-xs text-gray-500" id="sel-phone"></p>
                </div>
                <button onclick="clearCustomer()" class="text-gray-400 hover:text-gray-900 text-xs ml-1"><i class="fas fa-times"></i></button>
            </div>
            <input type="hidden" id="selected-customer-id">
        </div>

        <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-3">
            <div id="empty-cart" class="text-center py-12 text-gray-400">
                <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                <p class="text-sm">Cart is empty</p>
            </div>
        </div>

        <div class="border-t border-gray-100 p-4 space-y-3">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span id="subtotal">{{ $currencySymbol }}0.00</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 items-center">
                <span>Discount</span>
                @if($discountEnabled)
                <div class="flex items-center gap-1">
                    <span class="text-xs font-medium text-gray-500">{{ $currencySymbol }}</span>
                    <input type="number" id="discount" value="0" min="0" step="0.01"
                        {{ $maxDiscount > 0 ? 'max="'.$maxDiscount.'"' : '' }}
                        class="w-24 border border-gray-300 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-brand">
                </div>
                @else
                <span class="text-gray-400 text-xs">Disabled</span>
                <input type="hidden" id="discount" value="0">
                @endif
            </div>
            <div class="flex justify-between text-sm text-gray-600 items-center">
                <span>{{ $taxLabel }} @if($taxEnabled)({{ $taxRate }}%)@endif</span>
                @if($taxEnabled && !$taxInclusive)
                <span id="tax-display" class="text-sm text-gray-600">{{ $currencySymbol }} 0.00</span>
                <input type="hidden" id="tax" value="0">
                @else
                <span class="text-gray-400 text-xs">{{ $taxInclusive ? 'Included in price' : 'Disabled' }}</span>
                <input type="hidden" id="tax" value="0">
                @endif
            </div>
            <div class="flex justify-between font-bold text-lg border-t pt-3">
                <span>Total</span>
                <span id="total" class="text-gray-900">{{ $currencySymbol }}0.00</span>
            </div>

            <button id="checkout-btn" onclick="openCheckout()"
                class="w-full bg-brand hover:bg-brand-dark text-white font-semibold py-3 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <i class="fas fa-credit-card mr-2"></i> Checkout (F12)
            </button>
            <!-- Raffle preview -->
            <div id="raffle-preview" class="hidden bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-center text-xs text-gray-800 font-medium">
                🎟 <span id="raffle-entries-count"></span> will be generated
            </div>
            <!-- Wheel of Fortune button -->
            <div id="wheel-spin-wrap" class="hidden">
                <button id="wheel-spin-btn" onclick="openWheelModal()"
                    class="w-full text-white font-bold py-2.5 rounded-lg text-sm transition"
                    style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                    🎡 Spin to Win!
                </button>
            </div>
            <button onclick="clearCart()" class="w-full border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm transition">
                <i class="fas fa-trash mr-1"></i> Clear Cart
            </button>
        </div>
    </div>
</div>

<!-- Variant Selection Modal -->
<div id="variant-modal" class="fixed inset-0 bg-black/50 z-40 hidden pos-modal items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg text-gray-800" id="modal-product-name">Select Variant</h3>
            <button onclick="closeVariantModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div id="variant-options" class="space-y-2 max-h-80 overflow-y-auto"></div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkout-modal" class="fixed inset-0 bg-black/50 z-40 hidden pos-modal items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg text-gray-800">Checkout</h3>
            <button onclick="closeCheckout()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div class="space-y-4">
            <!-- Customer row -->
            <div id="co-customer-row" class="hidden bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 flex items-center gap-2 text-sm">
                <i class="fas fa-user text-gray-500 text-xs"></i>
                <span id="co-customer" class="text-gray-800 font-medium"></span>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span id="co-subtotal">{{ $currencySymbol }}0.00</span></div>
                <div class="flex justify-between"><span>Discount</span><span id="co-discount">{{ $currencySymbol }}0.00</span></div>
                <div class="flex justify-between"><span>Tax</span><span id="co-tax">{{ $currencySymbol }}0.00</span></div>
                <div class="flex justify-between font-bold text-base border-t pt-2"><span>Total</span><span id="co-total" class="text-gray-900">{{ $currencySymbol }}0.00</span></div>
            </div>
            <!-- Raffle preview -->
            <div id="co-raffle-row" class="hidden bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-800 font-medium text-center">
                <span id="co-raffle"></span>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <div class="grid grid-cols-3 gap-2" id="payment-method-grid">
                    @foreach([
                        ['cash',         'fa-money-bill-wave', 'Cash'],
                        ['card',         'fa-credit-card',     'Card'],
                        ['gcash',        'fa-mobile-alt',      'GCash'],
                        ['maya',         'fa-mobile-alt',      'Maya'],
                        ['bank_transfer','fa-university',      'Bank'],
                        ['other',        'fa-ellipsis-h',      'Other'],
                    ] as [$val, $icon, $label])
                    <button type="button" data-method="{{ $val }}"
                        onclick="selectPaymentMethod('{{ $val }}')"
                        class="payment-method-btn flex flex-col items-center gap-1 border-2 rounded-lg py-2.5 text-xs font-medium transition
                               {{ $val === 'cash' ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 text-gray-600 hover:border-gray-400' }}">
                        <i class="fas {{ $icon }} text-base"></i>
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Cash: amount paid + quick amounts + change -->
            <div id="cash-fields">
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount Paid ({{ $currencySymbol }})</label>
                <input type="number" id="amount-paid" min="0" step="0.01" placeholder="0.00"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-lg font-bold focus:outline-none focus:ring-2 focus:ring-brand"
                    oninput="calcChange()">
                <div class="flex gap-2 mt-2">
                    @foreach([50, 100, 200, 500, 1000] as $amt)
                    <button onclick="setAmount({{ $amt }})" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs py-1.5 rounded transition">{{ $amt }}</button>
                    @endforeach
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 flex justify-between items-center mt-3">
                    <span class="font-medium text-gray-700">Change</span>
                    <span id="change-display" class="text-2xl font-bold text-gray-700">{{ $currencySymbol }}0.00</span>
                </div>
            </div>

            <!-- Non-cash: reference number + exact payment notice -->
            <div id="non-cash-fields" class="hidden space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Tracking Number <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" id="reference-number" placeholder="e.g. GCash ref, approval code..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center text-sm text-gray-500">
                    <i class="fas fa-check-circle text-gray-400 mr-1"></i> Exact amount — no change
                </div>
            </div>

            <button id="process-btn" onclick="processCheckout()"
                class="w-full bg-gray-800 hover:bg-gray-900 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-check mr-2"></i> Process Payment
            </button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="fixed inset-0 bg-black/50 z-50 hidden pos-modal items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 text-center">
        <div class="w-16 h-16 bg-gray-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-white text-2xl"></i>
        </div>
        <h3 class="font-bold text-xl text-gray-800 mb-1">Payment Successful!</h3>
        <p class="text-gray-400 text-sm mb-4" id="receipt-txn"></p>
        <div class="bg-gray-50 rounded-xl p-4 mb-3 text-left text-sm space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-500">Total</span>
                <span id="receipt-total" class="font-bold text-gray-800"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Paid</span>
                <span id="receipt-paid" class="text-gray-700"></span>
            </div>
            <div class="flex justify-between border-t pt-2">
                <span class="font-semibold text-gray-700">Change</span>
                <span id="receipt-change" class="font-bold text-gray-700 text-base"></span>
            </div>
        </div>
        <div id="receipt-raffle"></div>
        <div id="receipt-wheel"></div>
        <div class="flex gap-3 mt-4">
            <button id="print-receipt-btn" onclick="openReceipt()"
                class="flex-1 bg-brand hover:bg-brand-dark text-white py-2.5 rounded-xl text-sm font-semibold transition">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a id="view-txn-btn" href="#" target="_blank"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-semibold transition text-center">
                <i class="fas fa-eye mr-1"></i> View
            </a>
            <button onclick="newTransaction()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-semibold transition">
                New Sale
            </button>
        </div>
    </div>
</div>

<!-- Wheel of Fortune Modal -->
<div id="wheel-pos-modal" class="fixed inset-0 bg-black/70 z-50 hidden">
    <div class="flex items-center justify-center h-full p-4">
    <div class="bg-gray-900 rounded-2xl shadow-2xl p-6 w-full max-w-sm text-center" style="border:1px solid rgba(255,255,255,.1)">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-white font-black text-lg">🎡 Spin to Win!</h3>
            <button id="wheel-pos-close-btn" onclick="closeWheelModal()" class="text-white/40 hover:text-white text-xl">&times;</button>
        </div>
        <div class="flex flex-col items-center gap-1 mb-4">
            <div class="text-white text-2xl leading-none" style="filter:drop-shadow(0 2px 4px rgba(0,0,0,.8));margin-bottom:-4px;">▼</div>
            <canvas id="wheel-pos-canvas" width="280" height="280" class="rounded-full block" style="max-width:100%;height:auto;"></canvas>
        </div>
        <button id="wheel-pos-spin-btn" onclick="spinWheelPos()"
            class="w-full text-white font-black text-lg rounded-full py-3 mb-3 transition"
            style="background:linear-gradient(135deg,#1DB87A,#0F8A58);letter-spacing:2px;">
            SPIN
        </button>
        <p id="wheel-pos-status" class="text-white/40 text-xs min-h-4"></p>

        <!-- Win result (shown after spin) -->
        <div id="wheel-pos-result" class="hidden mt-3 rounded-xl p-4" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1)">
            <p class="text-white/50 text-xs uppercase tracking-widest mb-1">You Won!</p>
            <p class="text-3xl font-black mb-1" id="wheel-pos-win-name"></p>
            <p class="text-white/40 text-xs mb-3" id="wheel-pos-win-desc"></p>
            <div id="wheel-pos-discount-info" class="hidden bg-green-900/50 border border-green-500/30 rounded-lg px-3 py-2 mb-3">
                <p class="text-green-300 text-sm font-semibold" id="wheel-pos-discount-text"></p>
            </div>
            <button onclick="applyWheelDiscount()" id="wheel-pos-apply-btn"
                class="w-full text-white font-bold py-2.5 rounded-xl transition"
                style="background:linear-gradient(135deg,#1DB87A,#0F8A58);">
                Apply Discount to Cart
            </button>
        </div>
    </div>
    </div>
</div>

<!-- Mobile cart backdrop -->
<div id="cart-backdrop" onclick="closeMobileCart()"
    class="md:hidden fixed inset-0 bg-black/50 z-20 hidden"></div>

<!-- Mobile cart FAB -->
<button id="cart-fab" onclick="openMobileCart()"
    class="md:hidden fixed bottom-6 right-6 z-20 bg-brand hover:bg-brand-dark text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center transition">
    <i class="fas fa-shopping-cart text-lg"></i>
    <span id="cart-fab-count"
        class="absolute -top-1 -right-1 bg-gray-900 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
</button>

<!-- Custom Item Modal -->
<div id="custom-item-modal" class="fixed inset-0 bg-black/50 z-50 hidden pos-modal items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg text-gray-800"><i class="fas fa-plus-circle text-gray-600 mr-2"></i>Custom Item</h3>
            <button onclick="closeCustomItemModal()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Item Name <span class="text-gray-600">*</span></label>
                <input type="text" id="ci-name" placeholder="e.g. Alteration fee, Gift wrap..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Price <span class="text-gray-600">*</span></label>
                    <input type="number" id="ci-price" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" id="ci-qty" min="1" value="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div id="ci-error" class="hidden text-xs text-gray-700 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2"></div>
            <div class="flex gap-3 pt-1">
                <button onclick="addCustomItem()"
                    class="flex-1 bg-brand hover:bg-brand-dark text-white py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                </button>
                <button onclick="closeCustomItemModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div id="quick-add-modal" class="fixed inset-0 bg-black/50 z-50 hidden pos-modal items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg text-gray-800"><i class="fas fa-user-plus text-gray-700 mr-2"></i>New Customer</h3>
            <button onclick="$('#quick-add-modal').addClass('hidden')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Name <span class="text-gray-600">*</span></label>
                <input type="text" id="qa-name" placeholder="Full name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" id="qa-phone" placeholder="+63 9XX XXX XXXX"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="qa-email" placeholder="optional"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div id="qa-error" class="hidden text-xs text-gray-700 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2"></div>
            <div class="flex gap-3 pt-1">
                <button onclick="saveQuickCustomer()"
                    class="flex-1 bg-brand hover:bg-brand-dark text-white py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-save mr-1"></i> Save & Select
                </button>
                <button onclick="$('#quick-add-modal').addClass('hidden')"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
const TAX_ENABLED        = {{ $taxEnabled ? 'true' : 'false' }};
const TAX_RATE           = {{ $taxRate }};
const TAX_INCLUSIVE      = {{ $taxInclusive ? 'true' : 'false' }};
const DISCOUNT_ENABLED   = {{ $discountEnabled ? 'true' : 'false' }};
const MAX_DISCOUNT       = {{ $maxDiscount }};
const RAFFLE_ENABLED     = {{ \App\Models\Setting::get('raffle_enabled','1') == '1' ? 'true' : 'false' }} && {{ $activeRaffle ? 'true' : 'false' }};
const RAFFLE_PER_ENTRY   = {{ (float)\App\Models\Setting::get('raffle_per_entry', 300) }};
const CURRENCY           = '{{ $currencySymbol }}';
const WHEEL_ENABLED      = {{ $wheelEnabled ? 'true' : 'false' }};
const WHEEL_MIN_PURCHASE = {{ $wheelMinPurchase }};
const WHEEL_SPIN_MODE    = '{{ $wheelSpinMode }}';

let wheelSpun = false;

let cart = [];
let searchTimeout, customerSearchTimeout;
let selectedCustomer = null;
let selectedPaymentMethod = 'cash';

const CART_STORAGE_KEY = 'styleblend_pos_cart';

function saveCart() {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify({
        cart,
        customer: selectedCustomer,
        discount: parseFloat($('#discount').val()) || 0,
    }));
}

function loadCart() {
    try {
        const data = JSON.parse(localStorage.getItem(CART_STORAGE_KEY));
        if (!data) return;
        cart = data.cart || [];
        if (data.discount) $('#discount').val(data.discount);
        if (data.customer) {
            selectedCustomer = data.customer;
            $('#sel-name').text(selectedCustomer.name);
            $('#sel-phone').text(selectedCustomer.phone || '');
            $('#customer-selected').removeClass('hidden');
            $('#selected-customer-id').val(selectedCustomer.id);
        }
        if (cart.length) renderCart();
    } catch (e) {
        localStorage.removeItem(CART_STORAGE_KEY);
    }
}

// ── Product search ──────────────────────────────────────────
$('#search-input').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchProducts, 300);
});
$('#category-filter').on('change', searchProducts);

// ── Barcode scan (Enter key = exact scan → auto-add) ────────
$('#search-input').on('keydown', function(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const code = $(this).val().trim();
    if (!code) return;
    clearTimeout(searchTimeout);

    $.get('{{ route("pos.scan") }}', { barcode: code }, function(res) {
        if (!res.found) {
            showToast('No product found for: ' + code, 'error');
            return;
        }
        if (res.type === 'variant') {
            addToCart(res.product, res.variant);
            $('#search-input').val('').focus();
            searchProducts();
        } else {
            // Multiple variants — open selection modal
            selectProduct(res.product);
            $('#search-input').val('');
        }
    }).fail(function() {
        showToast('Scan error. Try again.', 'error');
    });
});

function searchProducts() {
    $.get('{{ route("pos.search") }}', {
        search: $('#search-input').val(),
        category_id: $('#category-filter').val()
    }, renderProducts);
}

function renderProducts(products) {
    if (!products.length) {
        $('#product-grid').html('<div class="col-span-full text-center py-12 text-gray-400"><i class="fas fa-box-open text-4xl mb-3"></i><p>No products found</p></div>');
        return;
    }
    let html = '';
    products.forEach(p => {
        const totalStock = p.variants.reduce((s, v) => s + v.stock_quantity, 0);
        const minPrice   = Math.min(...p.variants.map(v => parseFloat(v.price)));
        html += `
        <div class="product-card bg-white rounded-xl shadow cursor-pointer overflow-hidden"
             onclick="selectProduct(${JSON.stringify(p).replace(/"/g,'&quot;')})">
            <div class="aspect-square bg-gray-100 overflow-hidden">
                <img src="${p.image_url}" alt="${p.name}" class="w-full h-full object-cover" onerror="this.src='/images/no-image.svg'">
            </div>
            <div class="p-3">
                <p class="font-semibold text-gray-800 text-sm truncate">${p.name}</p>
                <p class="text-xs text-gray-400">${p.category}</p>
                <div class="flex justify-between items-center mt-1">
                    <span class="text-gray-900 font-bold text-sm">${CURRENCY}${parseFloat(minPrice).toFixed(2)}</span>
                    <span class="text-xs ${totalStock <= 5 ? 'text-gray-600' : 'text-gray-400'}">${totalStock} in stock</span>
                </div>
            </div>
        </div>`;
    });
    $('#product-grid').html(html);
}

// ── Customer search ─────────────────────────────────────────
$('#customer-search').on('input', function() {
    clearTimeout(customerSearchTimeout);
    const q = $(this).val().trim();
    if (q.length < 2) { $('#customer-dropdown').addClass('hidden'); return; }
    customerSearchTimeout = setTimeout(() => {
        $.get('{{ route("customers.search") }}', { q }, function(customers) {
            let html = '';
            if (!customers.length) {
                html = `<div class="p-3 text-xs text-gray-400">No customer found.
                    <button onclick="openQuickAdd('${q.replace(/'/g,"\\'")}')" class="text-gray-900 hover:underline ml-1">+ Add new</button>
                </div>`;
            } else {
                html = customers.map(c => `
                    <div onclick="selectCustomer(${c.id},'${c.name.replace(/'/g,"\\'")}','${c.phone||''}')"
                        class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm border-b border-gray-50 last:border-0">
                        <p class="font-medium text-gray-800">${c.name}</p>
                        <p class="text-xs text-gray-400">${c.phone||c.email||''}</p>
                    </div>`).join('');
                html += `<div onclick="openQuickAdd('')" class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-xs text-gray-900 border-t border-gray-100">
                    <i class="fas fa-plus mr-1"></i> Add new customer
                </div>`;
            }
            $('#customer-dropdown').html(html).removeClass('hidden');
        });
    }, 300);
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('#customer-wrap, #customer-dropdown').length)
        $('#customer-dropdown').addClass('hidden');
});

function selectCustomer(id, name, phone) {
    selectedCustomer = { id, name, phone };
    $('#customer-search').val('');
    $('#customer-dropdown').addClass('hidden');
    $('#sel-name').text(name);
    $('#sel-phone').text(phone || '');
    $('#customer-selected').removeClass('hidden');
    $('#selected-customer-id').val(id);
    updateRafflePreview();
    saveCart();
}

function clearCustomer() {
    selectedCustomer = null;
    $('#selected-customer-id').val('');
    $('#customer-selected').addClass('hidden');
    $('#customer-search').val('');
    $('#raffle-preview').addClass('hidden');
    saveCart();
}

// ── Quick Add Customer ──────────────────────────────────────
function openQuickAdd(prefill) {
    $('#qa-name').val(prefill || '');
    $('#qa-phone').val('');
    $('#qa-email').val('');
    $('#qa-error').addClass('hidden').text('');
    $('#customer-dropdown').addClass('hidden');
    $('#quick-add-modal').removeClass('hidden');
    setTimeout(() => $('#qa-name').focus(), 100);
}

function saveQuickCustomer() {
    const name  = $('#qa-name').val().trim();
    const phone = $('#qa-phone').val().trim();
    const email = $('#qa-email').val().trim();
    if (!name) {
        $('#qa-error').text('Name is required.').removeClass('hidden'); return;
    }
    $('#qa-error').addClass('hidden');
    $.ajax({
        url: '{{ route("customers.quick") }}', method: 'POST',
        data: { name, phone, email },
        success: function(res) {
            selectCustomer(res.customer.id, res.customer.name, res.customer.phone || '');
            $('#quick-add-modal').addClass('hidden');
            showToast('Customer "' + res.customer.name + '" added and selected!');
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.errors
                ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                : (xhr.responseJSON?.message || 'Error saving customer');
            $('#qa-error').text(msg).removeClass('hidden');
        }
    });
}

// ── Raffle preview ──────────────────────────────────────────
function updateRafflePreview() {
    if (!RAFFLE_ENABLED || !selectedCustomer) { $('#raffle-preview').addClass('hidden'); return; }
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat($('#discount').val()) || 0;
    const tax      = parseFloat($('#tax').val()) || 0;
    const total    = subtotal - discount + tax;
    const entries  = Math.floor(total / RAFFLE_PER_ENTRY);
    if (entries > 0) {
        $('#raffle-entries-count').text(entries + ' raffle ' + (entries === 1 ? 'entry' : 'entries'));
        $('#raffle-preview').removeClass('hidden');
    } else {
        $('#raffle-preview').addClass('hidden');
    }
}

// ── Cart ────────────────────────────────────────────────────
function selectProduct(product) {
    if (product.variants.length === 1) { addToCart(product, product.variants[0]); return; }
    $('#modal-product-name').text(product.name);
    let html = '';
    product.variants.forEach(v => {
        const disabled = v.stock_quantity === 0;
        html += `
        <button onclick="addToCart(${JSON.stringify(product).replace(/"/g,'&quot;')},${JSON.stringify(v).replace(/"/g,'&quot;')}); closeVariantModal();"
            class="w-full flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50 hover:border-gray-900 transition text-sm ${disabled?'opacity-50 cursor-not-allowed':''}"
            ${disabled?'disabled':''}>
            <span class="font-medium">${v.size} / ${v.color}</span>
            <div class="text-right">
                <span class="font-bold text-gray-900">${CURRENCY}${parseFloat(v.price).toFixed(2)}</span>
                <span class="text-xs text-gray-400 ml-2">${v.stock_quantity} left</span>
            </div>
        </button>`;
    });
    $('#variant-options').html(html);
    $('#variant-modal').removeClass('hidden');
}

function closeVariantModal() { $('#variant-modal').addClass('hidden'); }

function addToCart(product, variant) {
    const existing = cart.find(i => i.variant_id === variant.id);
    if (existing) {
        if (existing.quantity >= variant.stock_quantity) { showToast('Not enough stock!','error'); return; }
        existing.quantity++;
        existing.subtotal = existing.quantity * existing.unit_price;
    } else {
        cart.push({ variant_id: variant.id, product_name: product.name, variant_info: variant.variant_info,
            unit_price: parseFloat(variant.price), quantity: 1, subtotal: parseFloat(variant.price), max_stock: variant.stock_quantity });
    }
    renderCart();
    showToast(`${product.name} (${variant.variant_info}) added`);
}

function openCustomItemModal() {
    $('#ci-name').val('');
    $('#ci-price').val('');
    $('#ci-qty').val(1);
    $('#ci-error').addClass('hidden').text('');
    $('#custom-item-modal').removeClass('hidden');
    setTimeout(() => $('#ci-name').focus(), 100);
}

function closeCustomItemModal() {
    $('#custom-item-modal').addClass('hidden');
}

function addCustomItem() {
    const name  = $('#ci-name').val().trim();
    const price = parseFloat($('#ci-price').val());
    const qty   = parseInt($('#ci-qty').val()) || 1;

    if (!name) { $('#ci-error').text('Item name is required.').removeClass('hidden'); return; }
    if (isNaN(price) || price < 0) { $('#ci-error').text('Enter a valid price.').removeClass('hidden'); return; }

    const customKey = `custom:${name}:${price}`;
    const existing  = cart.find(i => i.is_custom && i._key === customKey);
    if (existing) {
        existing.quantity += qty;
        existing.subtotal  = existing.quantity * existing.unit_price;
    } else {
        cart.push({
            variant_id: null,
            is_custom: true,
            _key: customKey,
            product_name: name,
            variant_info: 'Custom',
            unit_price: price,
            quantity: qty,
            subtotal: price * qty,
            max_stock: Infinity,
        });
    }
    renderCart();
    showToast(`"${name}" added to cart`);
    closeCustomItemModal();
}

function renderCart() {
    if (!cart.length) {
        $('#cart-items').html('<div class="text-center py-12 text-gray-400"><i class="fas fa-shopping-cart text-4xl mb-3"></i><p class="text-sm">Cart is empty</p></div>');
        $('#cart-count').text(0);
        const fab = document.getElementById('cart-fab-count');
        if (fab) { fab.textContent = 0; fab.classList.add('hidden'); }
        $('#checkout-btn').prop('disabled', true);
        updateTotals();
        return;
    }
    let html = '';
    cart.forEach((item, idx) => {
        html += `
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 text-sm truncate">${item.product_name}</p>
                    <p class="text-xs text-gray-400">${item.variant_info}</p>
                </div>
                <button onclick="removeFromCart(${idx})" class="text-gray-500 hover:text-gray-700 ml-2 text-sm"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <button onclick="updateQty(${idx},-1)" class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-bold transition">-</button>
                    <span class="w-8 text-center font-semibold text-sm">${item.quantity}</span>
                    <button onclick="updateQty(${idx},1)" class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full text-sm font-bold transition">+</button>
                </div>
                <span class="font-bold text-gray-900 text-sm">${CURRENCY}${item.subtotal.toFixed(2)}</span>
            </div>
        </div>`;
    });
    $('#cart-items').html(html);
    const totalQty = cart.reduce((s, i) => s + i.quantity, 0);
    $('#cart-count').text(totalQty);
    const fab = document.getElementById('cart-fab-count');
    if (fab) { fab.textContent = totalQty; fab.classList.toggle('hidden', totalQty === 0); }
    $('#checkout-btn').prop('disabled', false);
    updateTotals();
    saveCart();
}

function updateQty(idx, delta) {
    cart[idx].quantity += delta;
    if (cart[idx].quantity <= 0) { cart.splice(idx, 1); }
    else if (!cart[idx].is_custom && cart[idx].quantity > cart[idx].max_stock) {
        cart[idx].quantity = cart[idx].max_stock; showToast('Max stock reached!','warning');
    } else { cart[idx].subtotal = cart[idx].quantity * cart[idx].unit_price; }
    renderCart();
}

function removeFromCart(idx) { cart.splice(idx, 1); renderCart(); }
function clearCart() {
    cart = [];
    wheelSpun = false;
    wheelPosWinner = null;
    $('#wheel-pos-spin-btn').removeClass('hidden').prop('disabled', false);
    localStorage.removeItem(CART_STORAGE_KEY);
    renderCart();
}

function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat($('#discount').val()) || 0;
    let tax = 0;
    if (TAX_ENABLED && !TAX_INCLUSIVE) {
        tax = (subtotal - discount) * (TAX_RATE / 100);
        $('#tax').val(tax.toFixed(2));
        if ($('#tax-display').length) $('#tax-display').text(CURRENCY +tax.toFixed(2));
    }
    const total = subtotal - discount + tax;
    $('#subtotal').text(CURRENCY +subtotal.toFixed(2));
    $('#total').text(CURRENCY +total.toFixed(2));
    updateRafflePreview();
    updateWheelButton();
}

$('#discount').on('input', function() {
    if (MAX_DISCOUNT > 0 && parseFloat(this.value) > MAX_DISCOUNT) {
        this.value = MAX_DISCOUNT; showToast('Maximum discount is ' + CURRENCY + MAX_DISCOUNT, 'warning');
    }
    updateTotals();
    saveCart();
});

// ── Checkout ────────────────────────────────────────────────
function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    $('.payment-method-btn').each(function() {
        const active = $(this).data('method') === method;
        $(this)
            .toggleClass('border-gray-900 bg-gray-900 text-white', active)
            .toggleClass('border-gray-200 text-gray-600 hover:border-gray-400', !active);
    });
    const isCash = method === 'cash';
    $('#cash-fields').toggleClass('hidden', !isCash);
    $('#non-cash-fields').toggleClass('hidden', isCash);
    if (isCash) {
        $('#amount-paid').val('');
        $('#change-display').text(CURRENCY + '0.00');
        setTimeout(() => $('#amount-paid').focus(), 50);
    } else {
        setTimeout(() => $('#reference-number').focus(), 50);
    }
}

function openCheckout() {
    if (!cart.length) return;
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat($('#discount').val()) || 0;
    const tax      = parseFloat($('#tax').val()) || 0;
    const total    = subtotal - discount + tax;
    $('#co-subtotal').text(CURRENCY + subtotal.toFixed(2));
    $('#co-discount').text(CURRENCY + discount.toFixed(2));
    $('#co-tax').text(CURRENCY + tax.toFixed(2));
    $('#co-total').text(CURRENCY + total.toFixed(2));

    if (selectedCustomer) {
        $('#co-customer').text(selectedCustomer.name + (selectedCustomer.phone ? ' · ' + selectedCustomer.phone : ''));
        $('#co-customer-row').removeClass('hidden');
    } else {
        $('#co-customer-row').addClass('hidden');
    }

    const entries = Math.floor(total / RAFFLE_PER_ENTRY);
    if (RAFFLE_ENABLED && selectedCustomer && entries > 0) {
        $('#co-raffle').text('🎟 ' + entries + ' raffle ' + (entries === 1 ? 'entry' : 'entries') + ' will be generated');
        $('#co-raffle-row').removeClass('hidden');
    } else {
        $('#co-raffle-row').addClass('hidden');
    }

    // Reset to cash + clear reference on each open
    $('#reference-number').val('');
    selectPaymentMethod('cash');
    $('#checkout-modal').removeClass('hidden');
}

function closeCheckout() { $('#checkout-modal').addClass('hidden'); }
function setAmount(val) { $('#amount-paid').val(val); calcChange(); }

function calcChange() {
    const total  = parseFloat($('#co-total').text().replace(CURRENCY, '')) || 0;
    const paid   = parseFloat($('#amount-paid').val()) || 0;
    const change = paid - total;
    $('#change-display').text(CURRENCY + Math.max(0, change).toFixed(2));
}

function processCheckout() {
    const total  = parseFloat($('#co-total').text().replace(CURRENCY, '')) || 0;
    const isCash = selectedPaymentMethod === 'cash';
    const paid   = isCash ? (parseFloat($('#amount-paid').val()) || 0) : total;

    if (isCash && paid < total) { showToast('Amount paid is less than total!', 'error'); return; }

    $('#process-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

    $.ajax({
        url: '{{ route("pos.checkout") }}',
        method: 'POST',
        contentType: 'application/json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: JSON.stringify({
            items: cart.map(i => i.is_custom
                ? { variant_id: null, quantity: i.quantity, custom_name: i.product_name, custom_price: i.unit_price }
                : { variant_id: i.variant_id, quantity: i.quantity }
            ),
            discount:        parseFloat($('#discount').val()) || 0,
            tax:             parseFloat($('#tax').val()) || 0,
            amount_paid:     paid,
            payment_method:   selectedPaymentMethod,
            reference_number: selectedPaymentMethod !== 'cash' ? ($('#reference-number').val().trim() || null) : null,
            customer_id:      selectedCustomer?.id || null,
            customer_name:   selectedCustomer?.name || null,
            customer_phone:  selectedCustomer?.phone || null,
        }),
        success: function(res) {
            localStorage.removeItem(CART_STORAGE_KEY);
            closeCheckout();
            const methodLabels = { cash: 'Cash', card: 'Card', gcash: 'GCash', maya: 'Maya', bank_transfer: 'Bank Transfer', other: 'Other' };
            $('#receipt-txn').text('Transaction: ' + res.transaction_number + ' · ' + (methodLabels[selectedPaymentMethod] || selectedPaymentMethod));
            $('#receipt-total').text(CURRENCY + total.toFixed(2));
            $('#receipt-paid').text(CURRENCY + paid.toFixed(2));
            $('#receipt-change').text(CURRENCY + parseFloat(res.change).toFixed(2));
            $('#print-receipt-btn').data('receipt-url', '/pos/receipt/' + res.transaction_id);
            $('#view-txn-btn').attr('href', '/transactions/' + res.transaction_id);

            if (res.raffle_entries > 0) {
                $('#receipt-raffle').html(`
                    <div class="mt-2 bg-gray-900 rounded-xl p-4 text-center text-white">
                        <p class="text-xs font-medium opacity-80 mb-1">🎟 Raffle Tickets Earned!</p>
                        <p class="text-3xl font-bold">${res.raffle_entries} ${res.raffle_entries===1?'Entry':'Entries'}</p>
                    </div>`);
            } else {
                $('#receipt-raffle').html('');
            }

            if (res.wheel_spin_token && WHEEL_SPIN_MODE === 'next_purchase') {
                const playUrl = '/wheel/play?token=' + res.wheel_spin_token;
                $('#receipt-wheel').html(`
                    <div class="mt-2 bg-indigo-950 rounded-xl p-4 text-center">
                        <p class="text-xs font-medium text-indigo-300 mb-2">🎡 You earned a spin!</p>
                        <a href="${playUrl}" target="_blank"
                            class="inline-block bg-brand hover:bg-brand-dark text-white font-bold px-6 py-2 rounded-full text-sm transition">
                            Spin the Wheel
                        </a>
                    </div>`);
            } else {
                $('#receipt-wheel').html('');
            }

            $('#receipt-modal').removeClass('hidden');
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Checkout failed.', 'error');
        },
        complete: function() {
            $('#process-btn').prop('disabled', false).html('<i class="fas fa-check mr-2"></i> Process Payment');
        }
    });
}

function openReceipt() {
    const url = $('#print-receipt-btn').data('receipt-url');
    if (!url) return;

    const iframe = document.createElement('iframe');
    iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;border:none;';
    document.body.appendChild(iframe);

    iframe.onload = function () {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (e) {
            window.open(url, '_blank');
        }
        setTimeout(function () {
            if (document.body.contains(iframe)) document.body.removeChild(iframe);
        }, 5000);
    };

    iframe.src = url;
}

function newTransaction() {
    cart = [];
    selectedCustomer = null;
    selectedPaymentMethod = 'cash';
    wheelSpun = false;
    wheelPosWinner = null;
    $('#discount').val(0); $('#tax').val(0);
    $('#selected-customer-id').val('');
    $('#customer-selected').addClass('hidden');
    $('#customer-search').val('');
    $('#raffle-preview').addClass('hidden');
    $('#wheel-spin-wrap').addClass('hidden');
    $('#wheel-pos-spin-btn').removeClass('hidden').prop('disabled', false);
    renderCart();
    localStorage.removeItem(CART_STORAGE_KEY);
    $('#receipt-modal').addClass('hidden');
    $('#search-input').val('').focus();
    searchProducts();
}

$(document).on('keydown', function(e) {
    if (e.key === 'F2')  { e.preventDefault(); $('#search-input').focus(); }
    if (e.key === 'F11') { e.preventDefault(); toggleFullscreen(); }
    if (e.key === 'F12') { e.preventDefault(); if (cart.length) openCheckout(); }
    if (e.key === 'Escape') { closeVariantModal(); closeCheckout(); closeCustomItemModal(); $('#customer-dropdown, #quick-add-modal').addClass('hidden'); }
});

// ── Mobile cart drawer ──────────────────────────────────────
function openMobileCart() {
    document.getElementById('cart-panel').classList.remove('translate-y-full');
    document.getElementById('cart-backdrop').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMobileCart() {
    document.getElementById('cart-panel').classList.add('translate-y-full');
    document.getElementById('cart-backdrop').classList.add('hidden');
    document.body.style.overflow = '';
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
    } else {
        document.exitFullscreen().catch(() => {});
    }
}

document.addEventListener('fullscreenchange', function() {
    const icon = document.getElementById('fullscreen-icon');
    const sidebar = document.querySelector('aside');
    if (document.fullscreenElement) {
        icon.classList.replace('fa-expand', 'fa-compress');
        sidebar.classList.add('hidden');
    } else {
        icon.classList.replace('fa-compress', 'fa-expand');
        sidebar.classList.remove('hidden');
    }
});

// ── Wheel of Fortune (inline cart spin) ────────────────────
let wheelPosCurrentRotation = 0;
let wheelPosSpinning = false;
let wheelPosPrizes = [];
let wheelPosWinner = null;
const WHEEL_TAU = 2 * Math.PI;

function updateWheelButton() {
    if (!WHEEL_ENABLED || WHEEL_SPIN_MODE !== 'immediate') { $('#wheel-spin-wrap').addClass('hidden'); return; }
    if (wheelSpun) { $('#wheel-spin-wrap').addClass('hidden'); return; }
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat($('#discount').val()) || 0;
    const tax      = parseFloat($('#tax').val()) || 0;
    const total    = subtotal - discount + tax;
    const threshold = WHEEL_MIN_PURCHASE > 0 ? WHEEL_MIN_PURCHASE : 0.01;
    if (total >= threshold) {
        $('#wheel-spin-wrap').removeClass('hidden');
    } else {
        $('#wheel-spin-wrap').addClass('hidden');
    }
}

function drawWheelPos(prizes, rot) {
    const canvas = document.getElementById('wheel-pos-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const cx = canvas.width / 2, cy = canvas.height / 2, r = cx - 6;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if (!prizes.length) {
        ctx.fillStyle = '#1e293b'; ctx.beginPath(); ctx.arc(cx, cy, r, 0, WHEEL_TAU); ctx.fill();
        return;
    }
    const slice = WHEEL_TAU / prizes.length;
    let angle = rot;
    prizes.forEach(p => {
        const end = angle + slice;
        ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, angle, end); ctx.closePath();
        ctx.fillStyle = p.color; ctx.fill();
        ctx.strokeStyle = '#0f172a'; ctx.lineWidth = 2; ctx.stroke();
        const mid = angle + slice / 2;
        const lr = r * 0.65;
        ctx.save();
        ctx.translate(cx + lr * Math.cos(mid), cy + lr * Math.sin(mid));
        ctx.rotate(mid + Math.PI / 2);
        ctx.fillStyle = '#fff'; ctx.font = 'bold 11px sans-serif';
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(p.name.length > 10 ? p.name.slice(0, 9) + '…' : p.name, 0, 0);
        ctx.restore();
        angle = end;
    });
    ctx.beginPath(); ctx.arc(cx, cy, 14, 0, WHEEL_TAU);
    ctx.fillStyle = '#fff'; ctx.fill();
    ctx.strokeStyle = '#cbd5e1'; ctx.lineWidth = 2; ctx.stroke();
}

function openWheelModal() {
    if (wheelSpun) return;
    $('#wheel-pos-result').addClass('hidden');
    $('#wheel-pos-status').text('');
    $('#wheel-pos-spin-btn').prop('disabled', false).text('SPIN');
    $('#wheel-pos-modal').removeClass('hidden');

    $.get('/wheel/prizes/active', function(prizes) {
        wheelPosPrizes = prizes;
        wheelPosCurrentRotation = 0;
        drawWheelPos(prizes, 0);
    }).fail(function() {
        // fallback: redraw with empty
        drawWheelPos([], 0);
        $('#wheel-pos-status').text('Failed to load prizes.');
    });
}

function closeWheelModal() {
    if (wheelPosSpinning) return;
    $('#wheel-pos-modal').addClass('hidden');
}

function spinWheelPos() {
    if (wheelPosSpinning || !wheelPosPrizes.length) return;
    wheelPosSpinning = true;
    $('#wheel-pos-spin-btn').prop('disabled', true);
    $('#wheel-pos-status').text('');

    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat($('#discount').val()) || 0;
    const tax      = parseFloat($('#tax').val()) || 0;
    const cartTotal = subtotal - discount + tax;

    $.ajax({
        url: '{{ route("wheel.spin.pos") }}',
        method: 'POST',
        data: { cart_total: cartTotal },
        success(res) {
            wheelPosWinner = res.prize;
            const prizes = wheelPosPrizes;
            const slice  = WHEEL_TAU / prizes.length;
            const idx    = prizes.findIndex(p => p.id === res.prize.id);
            const segMid = (idx + 0.5) * slice;
            const targetAngle = ((3 * Math.PI / 2 - segMid - wheelPosCurrentRotation) % WHEEL_TAU + WHEEL_TAU) % WHEEL_TAU;
            const extraSpins  = (5 + Math.floor(Math.random() * 4)) * WHEEL_TAU;
            const delta = extraSpins + targetAngle;
            animateWheelPos(wheelPosCurrentRotation, wheelPosCurrentRotation + delta, 5000, res.prize);
        },
        error(xhr) {
            $('#wheel-pos-status').text(xhr.responseJSON?.error || 'Error. Try again.');
            wheelPosSpinning = false;
            $('#wheel-pos-spin-btn').prop('disabled', false);
        }
    });
}

function animateWheelPos(from, to, duration, prize) {
    const t0 = performance.now();
    (function frame(now) {
        const progress = Math.min((now - t0) / duration, 1);
        const eased    = 1 - Math.pow(1 - progress, 4);
        wheelPosCurrentRotation = from + (to - from) * eased;
        drawWheelPos(wheelPosPrizes, wheelPosCurrentRotation);
        if (progress < 1) {
            requestAnimationFrame(frame);
        } else {
            wheelPosCurrentRotation = to;
            drawWheelPos(wheelPosPrizes, to);
            wheelPosSpinning = false;
            showWheelPosResult(prize);
        }
    })(t0);
}

function showWheelPosResult(prize) {
    $('#wheel-pos-win-name').text(prize.name).css('color', prize.color);
    $('#wheel-pos-win-desc').text(prize.description || '');
    $('#wheel-pos-close-btn').prop('disabled', false);

    if (prize.discount_type && prize.discount_type !== 'none' && prize.discount_value > 0) {
        const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
        let discountText = '';
        if (prize.discount_type === 'fixed') {
            discountText = `₱${parseFloat(prize.discount_value).toFixed(2)} will be deducted from your cart!`;
        } else {
            const amount = (subtotal * prize.discount_value / 100).toFixed(2);
            discountText = `${prize.discount_value}% off — ₱${amount} discount applied!`;
        }
        $('#wheel-pos-discount-text').text(discountText);
        $('#wheel-pos-discount-info').removeClass('hidden');
        $('#wheel-pos-apply-btn').removeClass('hidden');
    } else {
        $('#wheel-pos-discount-info').addClass('hidden');
        $('#wheel-pos-apply-btn').addClass('hidden');
    }
    $('#wheel-pos-result').removeClass('hidden');
    $('#wheel-pos-spin-btn').addClass('hidden');
}

function applyWheelDiscount() {
    if (!wheelPosWinner) return;
    const prize    = wheelPosWinner;
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    let amount = 0;

    if (prize.discount_type === 'fixed') {
        amount = parseFloat(prize.discount_value);
    } else if (prize.discount_type === 'percentage') {
        amount = subtotal * (parseFloat(prize.discount_value) / 100);
    }

    if (amount > 0) {
        const existing = parseFloat($('#discount').val()) || 0;
        const newDiscount = (existing + amount).toFixed(2);
        $('#discount').val(newDiscount);
        updateTotals();
        saveCart();
    }

    wheelSpun = true;
    $('#wheel-spin-wrap').addClass('hidden');
    $('#wheel-pos-apply-btn').prop('disabled', true).text('✓ Applied!');
    setTimeout(() => closeWheelModal(), 1200);
    showToast('🎡 Discount applied to cart!');
}

loadCart();
searchProducts();
</script>
@endpush
