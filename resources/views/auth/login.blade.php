<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ \App\Models\Setting::get('store_name', 'StyleBlend') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
    <div class="text-center mb-8">
        @php $logo = \App\Models\Setting::get('store_logo'); $name = \App\Models\Setting::get('store_name', 'StyleBlend'); @endphp
        @if($logo)
            <img src="{{ asset('storage/' . $logo) }}" alt="{{ $name }}"
                class="h-16 w-auto mx-auto mb-3 object-contain">
        @else
            <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <span class="text-white font-bold text-xl">{{ strtoupper(substr($name, 0, 2)) }}</span>
            </div>
        @endif
        <h1 class="text-2xl font-bold text-gray-800">{{ $name }}</h1>
        <p class="text-gray-500 text-sm mt-1">Sign in to your account</p>
    </div>

    @if($errors->any())
        <div class="bg-gray-50 border border-gray-300 text-gray-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                placeholder="admin@pos.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                placeholder="••••••••">
        </div>
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-gray-900">
            <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
        </div>
        <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white font-semibold py-2.5 rounded-lg transition">
            Sign In
        </button>
    </form>

    <div class="mt-6 p-4 bg-gray-50 rounded-lg text-xs text-gray-500">
        <p class="font-medium mb-1">Demo Accounts:</p>
        <p>Admin: admin@pos.com / password</p>
        <p>Cashier: cashier@pos.com / password</p>
    </div>
</div>
</body>
</html>
