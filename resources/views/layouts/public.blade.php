<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AksesRuang')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-x-hidden @if(View::hasSection('lightTheme')) bg-[#F8FAF9] text-gray-800 @endif">
    <div class="relative min-h-screen lg:overflow-x-hidden flex flex-col">
        @unless(View::hasSection('lightTheme'))
            <div class="fixed inset-0 z-0 animate-bg-zoom" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('{{ Vite::asset('resources/images/register-bg.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed;"></div>

            <div class="absolute inset-0 pointer-events-none">
                <div class="float-circle float-circle-1"></div>
                <div class="float-circle float-circle-2"></div>
                <div class="float-circle float-circle-3"></div>
                <div class="float-circle float-circle-4"></div>
            </div>

            <!-- Dark Navigation Bar -->
            <nav class="relative z-50 border-b border-amber-200/30 bg-black/40 backdrop-blur-md animate-fade-in-up" style="animation-fill-mode: both; animation-delay: 0.1s;">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <!-- Logo -->
                        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-amber-100 logo-glow">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/90 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" /><polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                            </div>
                            AksesRuang
                        </a>

                        <!-- Menu -->
                        <div class="hidden md:flex items-center gap-8">
                            <a href="{{ route('home') }}" class="text-amber-100 hover:text-teal-300 transition {{ request()->routeIs('home') ? 'border-b-2 border-teal-300' : '' }}">Beranda</a>
                            <a href="{{ route('facilities.index') }}" class="text-amber-100 hover:text-teal-300 transition {{ request()->routeIs('facilities.index') ? 'border-b-2 border-teal-300' : '' }}">Fasilitas</a>
                        </div>

                        <!-- Auth Buttons -->
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="px-4 py-2 text-amber-100 hover:text-teal-300 transition">Dashboard</a>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-teal-500 text-white font-bold hover:bg-teal-400 transition btn-lift">Logout</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-amber-200/60 text-amber-100 hover:text-amber-50 transition btn-lift">Masuk</a>
                                <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-teal-500 text-white font-bold hover:bg-teal-400 transition btn-lift">Daftar</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        @else
            <!-- Light Navigation Bar (Matching Mockup) -->
            <nav class="relative z-50 bg-[#F8FAF9] border-b border-gray-100">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <!-- Logo -->
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-base sm:text-lg font-bold text-gray-900 tracking-tight">
                            <div class="w-8 h-8 rounded-lg bg-[#1E4D40] flex items-center justify-center text-white font-bold text-sm shadow-xs">
                                A
                            </div>
                            <span>AksesRuang</span>
                        </a>

                        <!-- Menu -->
                        <div class="hidden md:flex items-center gap-6">
                            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">Beranda</a>
                            <a href="{{ route('facilities.index') }}" class="text-sm font-medium px-4 py-1.5 rounded-full border border-gray-200 text-gray-900 bg-white shadow-xs transition hover:border-gray-300">Fasilitas</a>
                        </div>

                        <!-- Auth Buttons -->
                        <div class="flex items-center gap-4">
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 transition">Dashboard</a>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-5 py-2 rounded-full bg-[#1E4D40] text-white text-sm font-medium hover:bg-[#163c32] transition shadow-xs">Logout</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 transition">Masuk</a>
                                <a href="{{ route('register') }}" class="px-5 py-2 rounded-full bg-[#1E4D40] text-white text-sm font-medium hover:bg-[#163c32] transition shadow-xs">Daftar</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        @endunless

        <!-- Main Content -->
        <main class="relative z-10 flex-1">
            @yield('content')
        </main>
    </div>
</body>
</html>
