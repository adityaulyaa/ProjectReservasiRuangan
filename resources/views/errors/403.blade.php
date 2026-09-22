<x-guest-layout>
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <h1 class="text-6xl font-black text-red-500 mb-4">403</h1>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak</h2>
            <p class="text-gray-600 mb-6">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <a href="{{ url()->previous() ?: route('home') }}" class="inline-block px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition">Kembali</a>
        </div>
    </div>
</x-guest-layout>