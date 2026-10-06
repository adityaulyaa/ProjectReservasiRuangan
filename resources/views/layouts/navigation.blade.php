<nav x-data="{ open: false }" class="relative z-50 bg-slate-950/75 border-b border-white/10 backdrop-blur-md">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-lg font-bold text-amber-100 hover:text-white transition">
                        <div class="w-8 h-8 rounded-lg bg-teal-500/90 flex items-center justify-center text-white shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" /><polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                        </div>
                        <span>AksesRuang</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:flex">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'border-teal-400 text-teal-300' : 'border-transparent text-slate-300 hover:text-white hover:border-white/30' }}">
                        {{ __('Dashboard') }}
                    </a>
                    @php
                        $userRole = is_object(Auth::user()->role) ? Auth::user()->role->value : Auth::user()->role;
                    @endphp
                    @if($userRole === 'user')
                        <a href="{{ route('reservations.index') }}" 
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-semibold transition {{ request()->routeIs('reservations.*') ? 'border-teal-400 text-teal-300' : 'border-transparent text-slate-300 hover:text-white hover:border-white/30' }}">
                            {{ __('Reservasi Saya') }}
                        </a>
                        <a href="{{ route('facilities.index') }}" 
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-semibold transition {{ request()->routeIs('facilities.*') ? 'border-teal-400 text-teal-300' : 'border-transparent text-slate-300 hover:text-white hover:border-white/30' }}">
                            {{ __('Daftar Fasilitas') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false" @close.stop="dropdownOpen = false">
                    <div>
                        <button @click="dropdownOpen = ! dropdownOpen" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 text-sm font-medium transition cursor-pointer">
                            <span>{{ Auth::user()->name }}</span>
                            <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 border border-teal-500/30">
                                {{ $userRole }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 z-50 mt-2 w-48 rounded-2xl bg-slate-900/95 border border-white/15 shadow-2xl backdrop-blur-xl py-1 text-slate-200"
                         style="display: none;">
                        <div class="px-4 py-2 border-b border-white/10">
                            <p class="text-xs text-slate-400">Masuk sebagai</p>
                            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="block w-full px-4 py-2 text-start text-xs font-medium text-slate-300 hover:text-white hover:bg-white/10 transition">
                            {{ __('Pengaturan Profil') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-start px-4 py-2 text-xs font-medium text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition cursor-pointer">
                                {{ __('Keluar (Log Out)') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-950/95 border-b border-white/10 backdrop-blur-xl">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" 
               class="block py-2 text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'text-teal-400 font-bold' : 'text-slate-300 hover:text-white' }}">
                {{ __('Dashboard') }}
            </a>
            @if($userRole === 'user')
                <a href="{{ route('reservations.index') }}" 
                   class="block py-2 text-sm font-semibold transition {{ request()->routeIs('reservations.*') ? 'text-teal-400 font-bold' : 'text-slate-300 hover:text-white' }}">
                    {{ __('Reservasi Saya') }}
                </a>
                <a href="{{ route('facilities.index') }}" 
                   class="block py-2 text-sm font-semibold transition {{ request()->routeIs('facilities.*') ? 'text-teal-400 font-bold' : 'text-slate-300 hover:text-white' }}">
                    {{ __('Daftar Fasilitas') }}
                </a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-white/10 px-4">
            <div class="mb-3">
                <div class="font-bold text-sm text-white">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="block py-2 text-xs font-medium text-slate-300 hover:text-white">
                    {{ __('Pengaturan Profil') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-start py-2 text-xs font-semibold text-rose-400 hover:text-rose-300">
                        {{ __('Keluar (Log Out)') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
