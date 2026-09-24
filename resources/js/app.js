

import Alpine from 'alpinejs';
import '../images/register-bg.jpg';

window.Alpine = Alpine;

window.showLoginToast = function() {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 z-50 flex items-center gap-3 rounded-lg bg-black/70 backdrop-blur-md border border-amber-200/30 px-4 py-3 text-amber-200 font-medium shadow-lg animate-fade-in-up max-w-sm';
    toast.innerHTML = `
        <svg class="w-5 h-5 text-amber-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4l6 6m6-6-6 6m2-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="flex-1">Anda harus login terlebih dahulu</span>
        <button onclick="window.location.href='/login'" class="ml-3 px-3 py-1 rounded bg-teal-500 text-white text-sm font-semibold hover:bg-teal-400 transition btn-lift">
            Masuk Sekarang
        </button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('opacity-0'); toast.style.transition = 'opacity 0.5s'; }, 5000);
    setTimeout(() => toast.remove(), 5500);
};
Alpine.start();
