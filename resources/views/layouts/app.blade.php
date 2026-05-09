<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Track Buddy POS')</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* Sidebar collapse */
        #sidebar { transition: width 0.25s cubic-bezier(0.4,0,0.2,1); }
        #sidebar.is-collapsed { width: 4.5rem; }

        #sidebar .nav-label {
            overflow: hidden; white-space: nowrap;
            transition: opacity 0.2s ease, max-width 0.25s ease;
            max-width: 200px;
        }
        #sidebar.is-collapsed .nav-label { opacity: 0; max-width: 0; pointer-events: none; }

        #sidebar .nav-section {
            transition: opacity 0.2s ease, max-height 0.25s ease;
            max-height: 40px; overflow: hidden;
        }
        #sidebar.is-collapsed .nav-section { opacity: 0; max-height: 0; margin: 0; }

        #sidebar.is-collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        #sidebar.is-collapsed .logo-text-area { display: none; }
        #sidebar.is-collapsed #toggle-icon { transform: rotate(180deg); }
        #toggle-icon { transition: transform 0.25s ease; }

        /* Mobile sidebar */
        @media (max-width: 767px) {
            #sidebar {
                position: fixed !important;
                top: 0; bottom: 0; left: 0;
                z-index: 50;
                width: 16rem !important;
                transform: translateX(-100%);
                transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
                display: flex !important;
            }
            #sidebar.mobile-open { transform: translateX(0); }
            #sidebar.is-collapsed .nav-label { opacity: 1 !important; max-width: 200px !important; }
            #sidebar.is-collapsed .nav-section { opacity: 1 !important; max-height: 40px !important; margin: initial !important; }
            #sidebar.is-collapsed .sidebar-link { justify-content: flex-start !important; padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
            #sidebar.is-collapsed .logo-text-area { display: block !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-sand font-sans">

<!-- Mobile top bar -->
<div class="md:hidden fixed top-0 left-0 right-0 z-40 bg-navy text-white flex items-center gap-3 px-4 h-14 shadow-lg">
    <button onclick="openMobileSidebar()" class="text-white/70 hover:text-white w-8 h-8 flex items-center justify-center">
        <i class="fas fa-bars text-lg"></i>
    </button>
    <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:17px;letter-spacing:-0.3px;">
        <span style="color:white;">Track</span><span style="color:#1DB87A;">Buddy</span>
    </div>
</div>

<!-- Mobile sidebar backdrop -->
<div id="sidebar-backdrop" onclick="closeMobileSidebar()"
    class="md:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>

<div class="flex h-screen overflow-hidden pt-14 md:pt-0">
    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-navy text-white flex flex-col shrink-0">
        <div class="p-4 border-b border-white/10 overflow-hidden">
            @php
                $storeLogo = \App\Models\Setting::get('store_logo');
                $storeName = \App\Models\Setting::get('store_name', 'Track Buddy');
            @endphp
            <div class="flex items-center gap-3">
                @if($storeLogo)
                    <img src="{{ asset('storage/' . $storeLogo) }}" alt="{{ $storeName }}"
                        class="h-9 w-9 object-contain rounded-xl shrink-0">
                @else
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:linear-gradient(135deg,#1DB87A,#0F8A58);">
                        <span class="font-display font-black text-white text-sm leading-none">{{ strtoupper(substr($storeName, 0, 2)) }}</span>
                    </div>
                @endif
                <div class="logo-text-area min-w-0">
                    <h1 class="font-display font-extrabold text-base text-white leading-tight truncate">{{ $storeName }}</h1>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-xs text-white/50 truncate">{{ auth()->user()->name }}</span>
                        <span class="text-xs bg-brand/20 text-brand px-1.5 py-0.5 rounded-full font-medium uppercase tracking-wide shrink-0">{{ auth()->user()->getRoleNames()->first() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto scrollbar-hide">
            @role('admin')
            <a href="{{ route('dashboard') }}" title="Dashboard"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('dashboard') ? 'bg-white/10' : '' }}">
                <i class="fas fa-tachometer-alt w-5 shrink-0 text-center"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            @endrole
            <a href="{{ route('pos.index') }}" title="POS"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('pos.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-cash-register w-5 shrink-0 text-center"></i>
                <span class="nav-label">POS</span>
            </a>
            @role('admin')
            <a href="{{ route('products.index') }}" title="Products"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('products.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-tshirt w-5 shrink-0 text-center"></i>
                <span class="nav-label">Products</span>
            </a>
            <a href="{{ route('categories.index') }}" title="Categories"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('categories.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-tags w-5 shrink-0 text-center"></i>
                <span class="nav-label">Categories</span>
            </a>
            <a href="{{ route('brands.index') }}" title="Brands"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('brands.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-copyright w-5 shrink-0 text-center"></i>
                <span class="nav-label">Brands</span>
            </a>
            <a href="{{ route('inventory.index') }}" title="Inventory"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('inventory.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-boxes w-5 shrink-0 text-center"></i>
                <span class="nav-label">Inventory</span>
            </a>
            <a href="{{ route('transactions.index') }}" title="Transactions"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('transactions.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-receipt w-5 shrink-0 text-center"></i>
                <span class="nav-label">Transactions</span>
            </a>

            <div class="pt-2">
                <p class="nav-section text-xs text-white/30 uppercase tracking-wider px-3 mb-1">Reports</p>
                <a href="{{ route('reports.sales') }}" title="Sales Report"
                    class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('reports.sales') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-chart-bar w-5 shrink-0 text-center"></i>
                    <span class="nav-label">Sales</span>
                </a>
                <a href="{{ route('reports.inventory') }}" title="Inventory Report"
                    class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('reports.inventory') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-warehouse w-5 shrink-0 text-center"></i>
                    <span class="nav-label">Inventory</span>
                </a>
            </div>

            <div class="pt-2">
                <p class="nav-section text-xs text-white/30 uppercase tracking-wider px-3 mb-1">Purchasing</p>
                <a href="{{ route('suppliers.index') }}" title="Suppliers"
                    class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('suppliers.*') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-truck w-5 shrink-0 text-center"></i>
                    <span class="nav-label">Suppliers</span>
                </a>
                <a href="{{ route('purchase-orders.index') }}" title="Purchase Orders"
                    class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('purchase-orders.*') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-file-invoice w-5 shrink-0 text-center"></i>
                    <span class="nav-label">Purchase Orders</span>
                </a>
                <a href="{{ route('expenses.index') }}" title="Expenses"
                    class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('expenses.*') ? 'bg-white/10' : '' }}">
                    <i class="fas fa-money-bill-wave w-5 shrink-0 text-center"></i>
                    <span class="nav-label">Expenses</span>
                </a>
            </div>

            <a href="{{ route('users.index') }}" title="Users"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('users.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-users w-5 shrink-0 text-center"></i>
                <span class="nav-label">Users</span>
            </a>
            <a href="{{ route('customers.index') }}" title="Customers"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('customers.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-user-friends w-5 shrink-0 text-center"></i>
                <span class="nav-label">Customers</span>
            </a>
            <a href="{{ route('raffle.index') }}" title="Raffle"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('raffle.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-ticket-alt w-5 shrink-0 text-center"></i>
                <span class="nav-label">Raffle</span>
            </a>
            <a href="{{ route('settings.index') }}" title="Settings"
                class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/10 transition {{ request()->routeIs('settings.*') ? 'bg-white/10' : '' }}">
                <i class="fas fa-cog w-5 shrink-0 text-center"></i>
                <span class="nav-label">Settings</span>
            </a>
            @endrole
        </nav>

        <div class="border-t border-white/10">
            {{-- Toggle button --}}
            <button onclick="toggleSidebar()"
                class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-white/10 transition text-white/60 hover:text-white border-b border-white/10 sidebar-link">
                <i id="toggle-icon" class="fas fa-chevron-left w-5 shrink-0 text-center text-sm"></i>
                <span class="nav-label text-sm">Collapse</span>
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout"
                    class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 hover:bg-white/10 transition text-left">
                    <i class="fas fa-sign-out-alt w-5 shrink-0 text-center"></i>
                    <span class="nav-label">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <div class="p-4 md:p-6">
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

// Mobile sidebar
function openMobileSidebar() {
    document.getElementById('sidebar').classList.add('mobile-open');
    document.getElementById('sidebar-backdrop').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMobileSidebar() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebar-backdrop').classList.add('hidden');
    document.body.style.overflow = '';
}

// Sidebar collapse (desktop only)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('is-collapsed');
    localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('is-collapsed'));
}

// Restore state on load
(function() {
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.add('is-collapsed');
    }
})();

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
