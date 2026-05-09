<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Clothing Store POS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-sand font-sans">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-navy text-white flex flex-col shrink-0">
        <div class="p-4 border-b border-white/10">
            @php
                $storeLogo = \App\Models\Setting::get('store_logo');
                $storeName = \App\Models\Setting::get('store_name', 'StyleBlend');
            @endphp

            @if($storeLogo)
                <img src="{{ asset('storage/' . $storeLogo) }}" alt="{{ $storeName }}"
                    class="h-10 w-auto object-contain mb-2" style="filter: brightness(0) invert(1);">
            @else
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:linear-gradient(135deg,#1DB87A,#0F8A58);">
                        <span class="font-display font-black text-white text-sm leading-none">{{ strtoupper(substr($storeName, 0, 2)) }}</span>
                    </div>
                    <h1 class="font-display font-extrabold text-lg text-white leading-tight">{{ $storeName }}</h1>
                </div>
            @endif
            <p class="text-xs text-white/50 mt-1">{{ auth()->user()->name }}</p>
            <span class="text-xs bg-brand/20 text-brand px-2 py-0.5 rounded-full font-medium uppercase tracking-wide">{{ auth()->user()->getRoleNames()->first() }}</span>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto scrollbar-hide">
            @role('admin')
            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('dashboard') ? 'bg-white/10' : '' }}">
                <i class="fas fa-tachometer-alt w-5"></i> Dashboard
            </a>
            @endrole
            <a href="{{ route('pos.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('pos.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-cash-register w-5"></i> POS
            </a>
            @role('admin')
            <a href="{{ route('products.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('products.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-tshirt w-5"></i> Products
            </a>
            <a href="{{ route('categories.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('categories.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-tags w-5"></i> Categories
            </a>
            <a href="{{ route('brands.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('brands.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-copyright w-5"></i> Brands
            </a>
            <a href="{{ route('inventory.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('inventory.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-boxes w-5"></i> Inventory
            </a>
            <div class="pt-2">
                <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-1">Reports</p>
                <a href="{{ route('reports.sales') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('reports.sales') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-chart-bar w-5"></i> Sales
                </a>
                <a href="{{ route('reports.inventory') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('reports.inventory') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-warehouse w-5"></i> Inventory
                </a>
            </div>
            <a href="{{ route('transactions.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('transactions.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-receipt w-5"></i> Transactions
            </a>
            <div class="pt-2">
                <p class="text-xs text-gray-500 uppercase tracking-wider px-3 mb-1">Purchasing</p>
                <a href="{{ route('suppliers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('suppliers.*') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-truck w-5"></i> Suppliers
                </a>
                <a href="{{ route('purchase-orders.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('purchase-orders.*') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-file-invoice w-5"></i> Purchase Orders
                </a>
                <a href="{{ route('expenses.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('expenses.*') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-money-bill-wave w-5"></i> Expenses
                </a>
            </div>
            <a href="{{ route('users.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('users.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-users w-5"></i> Users
            </a>
            <a href="{{ route('customers.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('customers.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-user-friends w-5"></i> Customers
            </a>
            <a href="{{ route('raffle.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('raffle.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-ticket-alt w-5"></i> Raffle
            </a>
            <a href="{{ route('settings.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('settings.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-cog w-5"></i> Settings
            </a>
            @endrole
        </nav>
        <div class="p-4 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition text-left">
                    <i class="fas fa-sign-out-alt w-5"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded relative" id="flash-msg">
                    {{ session('success') }}
                    <button onclick="document.getElementById('flash-msg').remove()" class="absolute top-2 right-3 text-gray-700">&times;</button>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
});

function showToast(msg, type = 'success') {
    const iconMap = { success: 'success', error: 'error', warning: 'warning' };
    Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon: iconMap[type] || 'success',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

function swalConfirm({ title, text, confirmText = 'Yes', icon = 'warning' }, onConfirm) {
    Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonColor: '#1DB87A',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
    }).then(result => { if (result.isConfirmed) onConfirm(); });
}
</script>
@stack('scripts')
</body>
</html>
