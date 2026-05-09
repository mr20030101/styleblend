@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="max-w-3xl space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Settings</h2>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            <strong>Please fix the following errors:</strong>
            <ul class="list-disc ml-5 mt-1 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Store Information --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-store text-gray-700"></i> Store Information
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                    <input type="text" name="store_name" value="{{ $settings['store_name'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>

                {{-- Logo Upload --}}
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Store Logo</label>
                    <div class="flex items-center gap-6">
                        {{-- Current logo preview --}}
                        <div id="logo-preview-wrap" class="{{ empty($settings['store_logo']) ? 'hidden' : '' }}">
                            <img id="logo-preview"
                                src="{{ !empty($settings['store_logo']) ? asset('storage/'.$settings['store_logo']) : '' }}"
                                alt="Store Logo"
                                class="h-16 w-auto rounded-lg border border-gray-200 object-contain bg-gray-50 p-1">
                        </div>
                        {{-- Placeholder when no logo --}}
                        <div id="logo-placeholder" class="{{ !empty($settings['store_logo']) ? 'hidden' : '' }} w-16 h-16 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-300">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                        <div class="flex-1 space-y-2">
                            <label class="cursor-pointer inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition">
                                <i class="fas fa-upload"></i> Upload Logo
                                <input type="file" name="store_logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                            </label>
                            <p class="text-xs text-gray-400">PNG, JPG, SVG or WebP. Max 2MB. Recommended: square or landscape.</p>
                            @if(!empty($settings['store_logo']))
                            <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300">
                                Remove current logo
                            </label>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="store_address" value="{{ $settings['store_address'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="store_email" value="{{ $settings['store_email'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Footer Message</label>
                    <input type="text" name="store_footer" value="{{ $settings['store_footer'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand"
                        placeholder="e.g. Thank you for shopping with us!">
                </div>
            </div>
        </div>

        {{-- Tax Settings --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-percent text-gray-600"></i> Tax Settings
            </h3>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-700 text-sm">Enable Tax</p>
                    <p class="text-xs text-gray-400">Apply tax to all transactions</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="tax_enabled" value="1" class="sr-only peer"
                        {{ ($settings['tax_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                </label>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                    <div class="relative">
                        <input type="number" name="tax_rate" value="{{ $settings['tax_rate'] ?? '12' }}"
                            min="0" max="100" step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-brand">
                        <span class="absolute right-3 top-2.5 text-gray-400 text-sm">%</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax Label</label>
                    <input type="text" name="tax_label" value="{{ $settings['tax_label'] ?? 'VAT' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand"
                        placeholder="e.g. VAT, GST">
                </div>
                <div class="flex flex-col justify-end">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg h-[42px]">
                        <label class="text-sm font-medium text-gray-700 cursor-pointer" for="tax_inclusive">Tax Inclusive</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="tax_inclusive" name="tax_inclusive" value="1" class="sr-only peer"
                                {{ ($settings['tax_inclusive'] ?? '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Price already includes tax</p>
                </div>
            </div>
        </div>

        {{-- Raffle Settings --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-ticket-alt text-gray-600"></i> Raffle Settings
            </h3>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-700 text-sm">Enable Raffle</p>
                    <p class="text-xs text-gray-400">Automatically generate raffle entries on checkout</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="raffle_enabled" value="1" class="sr-only peer"
                        {{ ($settings['raffle_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Amount per Raffle Entry (₱)</label>
                <input type="number" name="raffle_per_entry" value="{{ $settings['raffle_per_entry'] ?? '300' }}"
                    min="1" step="1"
                    class="w-48 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                <p class="text-xs text-gray-400 mt-1">e.g. ₱300 spend = 1 raffle entry, ₱600 = 2 entries</p>
            </div>
        </div>

        {{-- Discount Settings --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-tag text-gray-600"></i> Discount Settings
            </h3>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-700 text-sm">Enable Discounts</p>
                    <p class="text-xs text-gray-400">Allow cashiers to apply discounts at checkout</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="discount_enabled" value="1" class="sr-only peer"
                        {{ ($settings['discount_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Discount Amount (₱)</label>
                <input type="number" name="max_discount" value="{{ $settings['max_discount'] ?? '100' }}"
                    min="0" step="0.01"
                    class="w-48 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                <p class="text-xs text-gray-400 mt-1">Set to 0 for no limit</p>
            </div>
        </div>

        {{-- POS Settings --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-cash-register text-gray-600"></i> POS Settings
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] ?? '₱' }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand"
                        placeholder="₱">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ $settings['low_stock_threshold'] ?? '5' }}"
                        min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                    <p class="text-xs text-gray-400 mt-1">Alert when stock falls at or below this number</p>
                </div>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-700 text-sm">Auto-Print Receipt</p>
                    <p class="text-xs text-gray-400">Automatically open print dialog after checkout</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="receipt_auto_print" value="1" class="sr-only peer"
                        {{ ($settings['receipt_auto_print'] ?? '0') == '1' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg font-medium transition">
                <i class="fas fa-save mr-2"></i> Save Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#logo-preview').attr('src', e.target.result);
            $('#logo-preview-wrap').removeClass('hidden');
            $('#logo-placeholder').addClass('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
