<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - AksesRuang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-x-hidden">
    <div class="relative min-h-screen lg:h-screen lg:overflow-x-hidden flex items-start lg:items-start justify-center py-6 lg:py-0 overflow-y-auto lg:overflow-y-hidden">
        <div class="absolute inset-0 animate-bg-zoom" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('{{ Vite::asset('resources/images/register-bg.jpg') }}'); background-size: cover; background-position: center;"></div>
        
        <div class="absolute inset-0 pointer-events-none">
            <div class="float-circle float-circle-1"></div>
            <div class="float-circle float-circle-2"></div>
            <div class="float-circle float-circle-3"></div>
            <div class="float-circle float-circle-4"></div>
        </div>
        
        <div class="relative flex w-full max-w-6xl mx-auto px-4 sm:px-6 gap-6 lg:gap-8 flex-col lg:flex-row items-center lg:items-start justify-center lg:justify-center h-full lg:pt-8 xl:pt-12">
            
            <div class="hidden lg:flex flex-1 flex-col text-white pt-6 lg:pt-8 pb-4 animate-fade-in-left" style="animation-fill-mode: both;">
                <div class="mb-6 animate-fade-in-up stagger-1" style="animation-fill-mode: both;">
                    <div class="flex items-center gap-3 mb-3 logo-glow rounded-xl w-fit pr-3">
                        <div class="w-10 h-10 xl:w-11 xl:h-11 rounded-xl bg-teal-500/90 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" /><polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                        </div>
                        <span class="text-2xl xl:text-3xl font-extrabold tracking-tight">AksesRuang</span>
                    </div>
                </div>
                
                <h1 class="text-3xl lg:text-4xl xl:text-5xl font-black mb-3 leading-none animate-fade-in-up stagger-2" style="animation-fill-mode: both;">
                    SELAMAT DATANG KEMBALI<br>
                    DENGAN<br>
                    <span class="text-teal-300">KOMUNITAS KAMI</span>
                </h1>
                
                <p class="text-sm xl:text-base mb-6 opacity-90 leading-relaxed animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
                    Masukkan email dan kata sandi Anda untuk mengakses AksesRuang
                </p>

                <div class="space-y-3 text-sm animate-fade-in-up stagger-4" style="animation-fill-mode: both;">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur rounded-full px-4 py-2 w-fit feature-icon transition">
                        <span class="w-6 h-6 rounded-full bg-teal-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <span>Dapatkan akses ke ratusan ruang unik</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur rounded-full px-4 py-2 w-fit feature-icon transition">
                        <span class="w-6 h-6 rounded-full bg-teal-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <span>Konfirmasi instan</span>
                    </div>
                </div>
            </div>
            
            <div class="lg:hidden flex flex-col items-center text-white mb-2 text-center">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-teal-500/90 flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold">AksesRuang</span>
                </div>
                <h1 class="text-2xl font-black leading-tight">BERGABUNG DENGAN <span class="text-teal-300">KOMUNITAS KAMI</span></h1>
                <p class="text-xs opacity-80 mt-1">Platform Booking Ruang Rapat & Studi</p>
            </div>
            
            <div class="w-full max-w-sm sm:max-w-md lg:max-w-sm lg:self-center mobile-full-card animate-fade-in-right lg:pt-2" style="animation-fill-mode: both;">
                <div class="glass-shimmer backdrop-blur-md bg-white/10 border border-white/20 rounded-3xl p-5 sm:p-6 shadow-2xl relative z-10">
                    
                    <div class="animate-fade-in-up stagger-1" style="animation-fill-mode: both;">
                        <h2 class="text-xl font-bold text-white">Masuk Sekarang</h2>
                        <p class="text-xs text-white/60 mt-1 mb-4">Masukkan email institusi dan kata sandi</p>
                    </div>
                    
                    @if ($errors->any())
                        <div class="bg-red-500/20 border border-red-500/40 rounded-xl p-3 mb-4 backdrop-blur">
                            <p class="text-red-100 text-xs font-semibold mb-1">Terjadi kesalahan:</p>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li class="text-red-100 text-xs">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('login') }}" x-data="loginForm()" @submit="validateForm" class="relative z-10">
                        @csrf
                        
                        <div class="mb-3 animate-fade-in-up stagger-2" style="animation-fill-mode: both;">
                            <label for="email" class="block text-xs font-semibold text-white/90 mb-1.5">Email Institusi</label>
                            <div class="relative group">
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-white/50 group-focus-within:text-teal-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email Institusi"
                                    x-model="email" @input="validateEmail()" required
                                    class="input-glow w-full pl-10 pr-3 py-2.5 bg-white/15 border border-white/25 rounded-xl text-white placeholder-white/50 focus:outline-none transition-all duration-300 text-sm">
                            </div>
                            <template x-if="errors.email"><p class="text-red-300 text-xs mt-1" x-text="errors.email"></p></template>
                        </div>
                        
                        <div class="mb-3 animate-fade-in-up stagger-3" style="animation-fill-mode: both;">
                            <label for="password" class="block text-xs font-semibold text-white/90 mb-1.5">Kata Sandi</label>
                            <div class="relative group">
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-white/50 group-focus-within:text-teal-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" placeholder="Kata Sandi"
                                    x-model="password" @input="validatePassword()" required
                                    class="input-glow w-full pl-10 pr-10 py-2.5 bg-white/15 border border-white/25 rounded-xl text-white placeholder-white/50 focus:outline-none transition-all duration-300 text-sm">
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-white/50 hover:text-white transition">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L5.636 5.636m4.242 4.242M14.12 14.12m-4.242-4.242L19.5 5.636M14.12 14.12L19.5 19.5M9.878 9.878L3 3m6.878 6.878L21 21"/></svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </button>
                            </div>
                            <template x-if="errors.password"><p class="text-red-300 text-xs mt-1" x-text="errors.password"></p></template>
                        </div>
                        
                        <div class="flex items-center mt-1 mb-3 animate-fade-in-up stagger-4" style="animation-fill-mode: both;">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-white/40 bg-white/15 text-teal-400 focus:ring-teal-300 focus:ring-offset-0" name="remember">
                                <span class="ms-2 text-xs text-white/80">Ingat saya</span>
                            </label>
                        </div>
                        
                        <button type="submit" :disabled="!isFormValid" class="btn-lift w-full py-2.5 bg-teal-500 hover:bg-teal-400 disabled:bg-white/20 disabled:text-white/40 text-white font-bold rounded-xl transition-all duration-300 mb-3 animate-fade-in-up stagger-6" style="animation-fill-mode: both;">
                            Masuk Sekarang
                        </button>
                        
                        <div class="space-y-1.5 text-xs text-white/75 mb-3 animate-fade-in-up stagger-7" style="animation-fill-mode: both;">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-full bg-teal-400/30 flex items-center justify-center flex-shrink-0"><svg class="w-2.5 h-2.5 text-teal-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                                <span>Dapatkan akses ke ratusan ruang unik</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-full bg-teal-400/30 flex items-center justify-center flex-shrink-0"><svg class="w-2.5 h-2.5 text-teal-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></span>
                                <span>Konfirmasi instan</span>
                            </div>
                        </div>
                        
                        <div class="p-3 bg-teal-500/15 border border-teal-400/30 rounded-xl backdrop-blur animate-fade-in-up stagger-7" style="animation-fill-mode: both;">
                            <p class="text-xs text-white/90 leading-relaxed">
                                <span class="font-semibold text-teal-200">Catatan:</span> Setelah masuk, Anda akan diarahkan ke dashboard sesuai peran Anda.
                            </p>
                        </div>
                        
                        <div class="mt-3 text-center animate-fade-in-up stagger-8" style="animation-fill-mode: both;">
                            <p class="text-white/70 text-xs">
                                Belum punya akun?
                                <a href="{{ route('register') }}" class="text-teal-300 hover:text-teal-200 font-bold transition underline underline-offset-4 decoration-teal-300/40 hover:decoration-teal-300">Daftar di sini</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function loginForm() {
            return {
                email: '',
                password: '',
                showPassword: false,
                errors: {},
                get isFormValid() {
                    return this.email && this.password;
                },
                validateEmail() {
                    this.errors.email = '';
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!re.test(this.email)) this.errors.email = 'Format email tidak valid';
                },
                validatePassword() {
                    this.errors.password = '';
                    if (this.password.length < 8) this.errors.password = 'Kata sandi minimal 8 karakter';
                },
                validateForm(e) {
                    this.validateEmail();
                    this.validatePassword();
                    if (!this.isFormValid) e.preventDefault();
                }
            }
        }
    </script>
</body>
</html>