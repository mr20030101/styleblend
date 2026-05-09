<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ \App\Models\Setting::get('store_name', 'Track Buddy') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-sand min-h-screen flex items-center justify-center">
<div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
    <div class="text-center mb-8">
        @php $logo = \App\Models\Setting::get('store_logo'); $name = \App\Models\Setting::get('store_name', 'Track Buddy'); @endphp
        @if($logo)
            <img src="{{ asset('storage/' . $logo) }}" alt="{{ $name }}"
                class="h-16 w-auto mx-auto mb-3 object-contain">
        @else
            {{-- TB logo mark --}}
            <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#1DB87A 0%,#0F8A58 100%);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-family:'Nunito',sans-serif;font-weight:900;font-size:28px;color:white;letter-spacing:-1px;">TB</div>
        @endif
        {{-- Wordmark --}}
        <div style="font-family:'Nunito',sans-serif;font-weight:800;font-size:22px;letter-spacing:-0.3px;line-height:1;">
            <span style="color:#1A2B3C;">Track</span><span style="color:#1DB87A;">Buddy</span>
        </div>
        <p class="text-gray-500 text-sm mt-2">Sign in to your account</p>
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
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="admin@pos.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent"
                placeholder="••••••••">
        </div>
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-gray-900">
            <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
        </div>
        <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white font-semibold py-2.5 rounded-lg transition">
            Sign In
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-6">
        Powered by <span style="font-family:'Nunito',sans-serif;font-weight:700;color:#1A2B3C;">Track</span><span style="font-family:'Nunito',sans-serif;font-weight:700;color:#1DB87A;">Buddy</span>
    </p>

</div>
</body>
</html>
