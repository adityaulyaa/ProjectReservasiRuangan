<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AksesRuang') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950 text-white min-h-screen">
        <div class="relative min-h-screen flex flex-col">
            <!-- Background Image overlay -->
            <div class="fixed inset-0 z-0 pointer-events-none" style="background-image: linear-gradient(rgba(10, 15, 29, 0.85), rgba(15, 23, 42, 0.92)), url('{{ Vite::asset('resources/images/register-bg.jpg') }}'); background-size: cover; background-position: center;"></div>

            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="relative z-10 border-b border-white/10 bg-black/20 backdrop-blur-md">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="relative z-10 flex-1">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
