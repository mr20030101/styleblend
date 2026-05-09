@extends('layouts.app')

@section('title', 'About — TrackBuddy POS')

@section('content')
<div class="max-w-3xl mx-auto py-10 px-4 space-y-6">

    {{-- Hero --}}
    <div class="bg-navy rounded-2xl px-8 pt-10 pb-8 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5 shadow-lg"
             style="background:linear-gradient(135deg,#1DB87A,#0F8A58);">
            <span style="font-family:'Nunito',sans-serif;font-weight:900;font-size:22px;color:#fff;letter-spacing:-1px;">TB</span>
        </div>
        <h1 style="font-family:'Nunito',sans-serif;font-weight:900;font-size:2.2rem;letter-spacing:-0.5px;line-height:1.1;">
            <span class="text-white">Track</span><span style="color:#1DB87A;">Buddy</span>
        </h1>
        <p class="text-white/40 text-xs mt-2 tracking-widest uppercase">Point of Sale System</p>
        <span class="mt-4 inline-block bg-white/10 text-white/60 text-xs px-4 py-1.5 rounded-full border border-white/10">
            Version 1.0.0
        </span>
    </div>

    {{-- About --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">About</h2>
        <p class="text-gray-700 leading-relaxed">
            <strong class="text-navy" style="font-family:'Nunito',sans-serif;font-weight:800;">TrackBuddy</strong>
            is a modern, lightweight Point of Sale system built for clothing retail businesses.
            It covers the full retail workflow — product &amp; variant management, real-time inventory tracking,
            sales transactions, purchase orders, expenses, customer loyalty with raffle, and detailed reports —
            all from a clean, fast interface designed to keep checkout quick and stock accurate.
        </p>
    </div>

    {{-- Team --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">The Team</h2>
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                     style="background:linear-gradient(135deg,#1DB87A,#0F8A58);">
                    <span class="text-white font-bold text-lg" style="font-family:'Nunito',sans-serif;">SL</span>
                </div>
                <div>
                    <p class="font-bold text-navy text-base" style="font-family:'Nunito',sans-serif;">Shean Louise Margallo</p>
                    <p class="text-gray-500 text-sm">Lead Developer &amp; Designer</p>
                    <p class="text-gray-400 text-xs mt-0.5">sheanlouisemargallo@gmail.com</p>
                </div>
            </div>
            <div class="border-t border-gray-100"></div>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                     style="background:linear-gradient(135deg,#3B82F6,#1D4ED8);">
                    <span class="text-white font-bold text-lg" style="font-family:'Nunito',sans-serif;">JA</span>
                </div>
                <div>
                    <p class="font-bold text-navy text-base" style="font-family:'Nunito',sans-serif;">Jay-Anne R. Tan</p>
                    <p class="text-gray-500 text-sm">Product Owner &amp; Business Analyst</p>
                    <p class="text-gray-400 text-xs mt-0.5">Defined system requirements &amp; feature direction</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Legal --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">Legal</h2>

        <div class="flex gap-3">
            <div class="mt-0.5 w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <i class="fas fa-tag text-xs" style="color:#1DB87A;"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-700">Name &amp; Branding</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    <strong>TrackBuddy</strong> is the working name of this application, conceived, designed,
                    and built by Shean Louise Margallo. The name is not currently registered as a trademark.
                </p>
            </div>
        </div>

        <div class="border-t border-gray-100"></div>

        <div class="flex gap-3">
            <div class="mt-0.5 w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <i class="fas fa-copyright text-xs" style="color:#1DB87A;"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-700">Copyright</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    &copy; {{ date('Y') }} TrackBuddy. This is a proprietary internal system.
                    Unauthorized copying, redistribution, or modification of this software
                    is not permitted without written permission from the developer.
                </p>
            </div>
        </div>

        <div class="border-t border-gray-100"></div>

        <div class="flex gap-3">
            <div class="mt-0.5 w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                <i class="fas fa-shield-alt text-xs" style="color:#1DB87A;"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-700">License</p>
                <p class="text-sm text-gray-500 mt-0.5">
                    This software is intended for internal business use only and is not open-source.
                    All third-party libraries and frameworks used in this system remain under
                    their respective licenses.
                </p>
            </div>
        </div>
    </div>

    {{-- Tech Stack --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Built With</h2>
        <div class="flex flex-wrap gap-2">
            @foreach([
                'Laravel 12', 'PHP 8.3', 'Alpine.js', 'Tailwind CSS',
                'MySQL', 'Vite', 'SweetAlert2', 'jQuery'
            ] as $tech)
            <span class="text-xs font-medium bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full">{{ $tech }}</span>
            @endforeach
        </div>
    </div>

    {{-- Footer note --}}
    <p class="text-center text-xs text-gray-400 pb-2">
        TrackBuddy POS &mdash; Designed &amp; built by Shean Louise Margallo &copy; {{ date('Y') }}
    </p>

</div>
@endsection
