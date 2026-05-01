<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    featuredUnits: {
        type: Array,
        default: () => [],
    }
});
</script>

<script>
export default {
    layout: null,
};
</script>

<template>
    <Head title="Welcome" />
    
    <!-- Main wrapper with fixed background -->
    <div class="relative min-h-screen font-sans text-gray-100 overflow-x-hidden selection:bg-purple-500 selection:text-white">
        <!-- Background Image & Overlay -->
        <div class="fixed inset-0 z-0">
            <img src="/images/anime_bg.png" alt="Anime Background" class="w-full h-full object-cover opacity-80" />
            <div class="absolute inset-0 bg-gradient-to-b from-[#1a103c]/80 via-[#2d1b54]/70 to-[#1a103c]/95 backdrop-blur-[2px]"></div>
        </div>

        <!-- Content Wrapper -->
        <div class="relative z-10 flex flex-col min-h-screen">
            
            <!-- Topbar -->
            <header class="w-full py-6 px-6 sm:px-12 flex justify-between items-center glass-header sticky top-0 z-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center shadow-[0_0_15px_rgba(168,85,247,0.6)]">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-purple-300 to-pink-200" style="font-family: 'Inter', sans-serif;">AniRent</span>
                </div>
                
                <nav v-if="canLogin" class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="btn-glass px-6 py-2 rounded-full font-semibold transition-all hover:shadow-[0_0_20px_rgba(168,85,247,0.5)]"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="btn-primary px-8 py-2.5 rounded-full font-bold text-white transition-all shadow-[0_0_15px_rgba(192,132,252,0.5)] hover:shadow-[0_0_25px_rgba(192,132,252,0.8)] hover:scale-105"
                        >
                            Masuk
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Main Content -->
            <main class="flex-grow">
                <!-- Hero Section -->
                <section class="container mx-auto px-6 sm:px-12 py-12 lg:py-24 flex flex-col-reverse lg:flex-row items-center gap-12">
                    <div class="flex-1 text-center lg:text-left space-y-8">
                        <div class="inline-block px-4 py-1.5 rounded-full border border-purple-400/30 bg-purple-500/10 backdrop-blur-md text-purple-200 text-sm font-medium tracking-wide mb-2 animate-fade-in-up">
                            ✨ Platform Rental No. 1
                        </div>
                        <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight text-transparent bg-clip-text bg-gradient-to-br from-white via-purple-100 to-purple-300 drop-shadow-lg animate-fade-in-up" style="animation-delay: 0.1s;">
                            Sewa Properti <br/>Anime Lebih <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-purple-400">Mudah & Cepat</span>
                        </h1>
                        <p class="text-lg text-purple-100/80 max-w-xl mx-auto lg:mx-0 animate-fade-in-up" style="animation-delay: 0.2s; line-height: 1.8;">
                            Temukan ribuan koleksi kostum, senjata, dan aksesoris cosplay premium. Wujudkan karakter impianmu tanpa harus merogoh kocek dalam-dalam.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 animate-fade-in-up" style="animation-delay: 0.3s;">
                            <Link :href="route('login')" class="w-full sm:w-auto text-center px-8 py-4 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 text-white font-bold text-lg transition-all shadow-[0_0_20px_rgba(216,180,254,0.4)] hover:shadow-[0_0_30px_rgba(216,180,254,0.7)] hover:scale-105">
                                Mulai Pinjam Sekarang
                            </Link>
                            <Link href="#katalog" class="w-full sm:w-auto text-center px-8 py-4 rounded-full border border-purple-300/30 bg-white/5 backdrop-blur-md text-purple-100 font-semibold text-lg hover:bg-white/10 transition-all">
                                Lihat Katalog
                            </Link>
                        </div>
                    </div>
                    
                    <div class="flex-1 relative flex justify-center items-center animate-float">
                        <!-- Decorative glowing orb behind image -->
                        <div class="absolute w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[120px] -z-10"></div>
                        <img 
                            src="/images/hero-samurai.jpg" 
                            alt="Anime Character" 
                            class="max-w-full h-auto max-h-[700px] object-contain opacity-95 mix-blend-luminosity hover:mix-blend-normal transition-all duration-700" 
                            style="-webkit-mask-image: radial-gradient(ellipse 90% 90% at 50% 50%, black 50%, transparent 100%); mask-image: radial-gradient(ellipse 90% 90% at 50% 50%, black 50%, transparent 100%);"
                        />
                    </div>
                </section>

                <!-- Featured Section -->
                <section id="katalog" class="container mx-auto px-6 sm:px-12 py-20">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-purple-200 inline-block mb-4">Koleksi Unggulan</h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto rounded-full glow-line"></div>
                    </div>

                    <div v-if="featuredUnits.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <Link v-for="(unit, index) in featuredUnits" :key="unit.id" :href="route('units.show', unit.id)" class="glass-card rounded-3xl p-5 group cursor-pointer block" :class="{ 'hidden lg:block': index === 2 }">
                            <div class="relative overflow-hidden rounded-2xl mb-4 aspect-[4/3] bg-gradient-to-br from-purple-900/50 to-indigo-900/50 flex items-center justify-center">
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-all duration-300 z-10"></div>
                                
                                <img v-if="unit.foto" :src="`/storage/${unit.foto}`" :alt="unit.nama_unit" class="w-full h-full object-cover z-0 absolute group-hover:scale-110 transition-transform duration-500" />
                                <svg v-else class="w-20 h-20 text-purple-300/50 group-hover:scale-110 group-hover:text-purple-300 transition-all duration-500 relative z-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                
                                <span class="absolute top-3 right-3 z-20 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.5)]" :class="unit.status === 'tersedia' ? 'bg-green-500/80 shadow-[0_0_10px_rgba(34,197,94,0.5)]' : 'bg-amber-500/80 shadow-[0_0_10px_rgba(245,158,11,0.5)]'">
                                    {{ unit.status === 'tersedia' ? 'Tersedia' : 'Dipinjam' }}
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-300 transition-colors line-clamp-1">{{ unit.nama_unit }}</h3>
                            <div class="flex justify-between items-center">
                                <span class="text-pink-300 font-semibold">Rp {{ Number(unit.harga_sewa).toLocaleString('id-ID') }} / hari</span>
                                <button class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center group-hover:bg-purple-500 transition-colors shadow-[0_0_10px_rgba(168,85,247,0)] group-hover:shadow-[0_0_15px_rgba(168,85,247,0.6)]">
                                    <svg class="w-5 h-5 text-purple-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="text-center py-10 text-purple-200/50">
                        Belum ada unit yang tersedia.
                    </div>
                    
                    <div class="text-center mt-12">
                         <Link :href="route('login')" class="inline-flex items-center gap-2 text-purple-300 hover:text-white font-semibold transition-colors group">
                            Lihat Semua Koleksi 
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </Link>
                    </div>
                </section>

                <!-- How it works -->
                <section class="container mx-auto px-6 sm:px-12 py-20 relative">
                    <div class="absolute inset-0 bg-purple-900/10 backdrop-blur-sm skew-y-[-2deg] -z-10"></div>
                    <div class="text-center mb-16">
                        <h2 class="text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-purple-200 inline-block mb-4">Cara Kerja AniRent</h2>
                        <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto rounded-full glow-line"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                        <!-- Connector line for desktop -->
                        <div class="hidden md:block absolute top-12 left-1/8 right-1/8 h-0.5 bg-gradient-to-r from-transparent via-purple-500/50 to-transparent z-0"></div>

                        <!-- Step 1 -->
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="w-24 h-24 rounded-full glass-card flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(168,85,247,0.3)]">
                                <svg class="w-10 h-10 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">1. Pilih Unit</h3>
                            <p class="text-purple-200/70 text-sm">Cari kostum atau properti yang sesuai dengan karaktermu.</p>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="w-24 h-24 rounded-full glass-card flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(236,72,153,0.3)]">
                                <svg class="w-10 h-10 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">2. Booking</h3>
                            <p class="text-purple-200/70 text-sm">Pilih tanggal sewa dan selesaikan pembayaran dengan aman.</p>
                        </div>

                        <!-- Step 3 -->
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="w-24 h-24 rounded-full glass-card flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(59,130,246,0.3)]">
                                <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">3. Ambil Unit</h3>
                            <p class="text-purple-200/70 text-sm">Ambil barang di lokasi kami atau pilih opsi pengiriman.</p>
                        </div>

                        <!-- Step 4 -->
                        <div class="relative z-10 flex flex-col items-center text-center">
                            <div class="w-24 h-24 rounded-full glass-card flex items-center justify-center mb-6 shadow-[0_0_20px_rgba(34,197,94,0.3)]">
                                <svg class="w-10 h-10 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">4. Kembalikan</h3>
                            <p class="text-purple-200/70 text-sm">Kembalikan unit tepat waktu dan dalam kondisi baik.</p>
                        </div>
                    </div>
                </section>

                <!-- Bottom CTA -->
                <section class="py-24 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-800/40 to-pink-900/40 z-0"></div>
                    <!-- Decorative circles -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-pink-500/20 rounded-full blur-[80px] -z-10"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-purple-600/20 rounded-full blur-[100px] -z-10"></div>
                    
                    <div class="container mx-auto px-6 relative z-10 text-center">
                        <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6 drop-shadow-lg">Siap Memulai Petualanganmu?</h2>
                        <p class="text-xl text-purple-100 mb-10 max-w-2xl mx-auto">Bergabunglah dengan ratusan cosplayer lainnya dan temukan kemudahan menyewa properti berkualitas di AniRent.</p>
                        <Link :href="route('login')" class="inline-block px-10 py-4 rounded-full bg-white text-purple-900 font-bold text-lg transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.6)] hover:scale-105">
                            Gabung Sekarang
                        </Link>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class="py-8 text-center border-t border-white/10 glass-header">
                <p class="text-purple-200/60 text-sm">
                    &copy; 2026 AniRent. All rights reserved. <br/>
                    <span class="text-xs mt-2 inline-block opacity-50">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</span>
                </p>
            </footer>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}
.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
}

.glow-line {
    box-shadow: 0 0 10px rgba(168, 85, 247, 0.8), 0 0 20px rgba(236, 72, 153, 0.6);
}

/* Glassmorphism utilities */
.glass-header {
    background: rgba(26, 16, 60, 0.3);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.glass-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.glass-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 15px 40px 0 rgba(168, 85, 247, 0.2);
    transform: translateY(-5px);
}

.btn-glass {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-glass:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
}

.btn-primary {
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
    background-size: 200% auto;
    transition: 0.5s;
}

.btn-primary:hover {
    background-position: right center;
}
</style>

