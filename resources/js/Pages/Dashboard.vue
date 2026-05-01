<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage } from '@inertiajs/vue3'



const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalUnit: 0,
            unitDipinjam: 0,
            unitTerlambat: 0,
            totalAnggota: 0,
            totalBundle: 0,
            totalBookingHariIni: 0,
            bookingMenunggu: 0,
            bookingAktif: 0,
        })
    },
    bookingTerbaru: {
        type: Array,
        default: () => []
    },
    unitPopuler: {
        type: Array,
        default: () => []
    },
    user: Object,
})


const page = usePage()

const user = computed(() => page.props.auth.user)

const isAdmin = computed(() => page.props.auth.user?.role === 'admin')

const statusClass = (status) => {
    const map = {
        active:    'pill-active',
        booked:    'pill-booked',
        late:      'pill-late',
        returned:  'pill-done',
    }
    return map[status] ?? 'pill-done'
}

const statusLabel = (status) => {
    const map = {
        active: 'Aktif', booked: 'Booked',
        late: 'Terlambat', returned: 'Dikembalikan',
    }
    return map[status] ?? status
}

const unitStatusClass = (status) => {
    return status === 'tersedia' ? 'dot-green' : status === 'dipinjam' ? 'dot-red' : 'dot-gray'
}

const initials = (name) => name?.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase() ?? '?'
</script>

<template>
    <Head title="Dashboard" />

        <div class="dashboard">
            <!-- Header -->
            <div class="dash-header">
                <div>
                    <h1 class="greeting">
                        {{ isAdmin ? 'Selamat datang, Admin' : `Halo, ${user?.name?.split(' ')[0]}!` }}
                    </h1>
                    <p class="greeting-sub">
                        {{ isAdmin ? 'Ringkasan data AniRent hari ini' : 'Kelola pinjaman unitmu di sini' }}
                    </p>
                </div>
                <div v-if="isAdmin" class="header-actions">
                    <Link :href="route('admin.bookings.history')" class="btn-outline">
                        Cetak Riwayat
                    </Link>
                    <Link :href="route('admin.units.create')" class="btn-primary">
                        + Tambah Unit
                    </Link>
                </div>
            </div>

            <!-- Alert terlambat (admin) -->
            <div v-if="isAdmin && stats.unitTerlambat > 0" class="alert-banner">
                <span class="alert-icon">!</span>
                Ada <strong>{{ stats.unitTerlambat }} unit</strong> yang terlambat dikembalikan.
                <Link :href="route('admin.bookings.index', { status: 'late' })" class="alert-link">
                    Lihat detail →
                </Link>
            </div>

            <!-- Alert slot (anggota) -->
            <div v-if="!isAdmin" class="alert-banner alert-info">
                <span class="alert-icon alert-icon-info">i</span>
                Kamu masih punya <strong>{{ 2 - (stats.pinjamanAktif ?? 0) }} slot</strong> pinjaman tersedia.
                <Link :href="route('units.index')" class="alert-link">
                    Cari unit →
                </Link>
            </div>

            <!-- Stats grid (Admin) -->
            <div v-if="isAdmin" class="stats-grid">
                <div class="stat-card stat-blue">
                    <p class="stat-label">Menunggu Pengambilan</p>
                    <p class="stat-value">{{ stats.bookingMenunggu }}</p>
                    <span class="stat-badge badge-blue">perlu diserahkan</span>
                </div>
                <div class="stat-card stat-teal">
                    <p class="stat-label">Sedang Dipinjam</p>
                    <p class="stat-value">{{ stats.bookingAktif }}</p>
                    <span class="stat-badge badge-teal">aktif di user</span>
                </div>
                <div class="stat-card stat-coral">
                    <p class="stat-label">Terlambat</p>
                    <p class="stat-value">{{ stats.unitTerlambat }}</p>
                    <span class="stat-badge badge-coral">perlu aksi</span>
                </div>
                <div class="stat-card stat-purple">
                    <p class="stat-label">Total Unit</p>
                    <p class="stat-value">{{ stats.totalUnit }}</p>
                    <span class="stat-badge badge-purple">{{ stats.totalBundle }} bundle</span>
                </div>
            </div>

            <!-- Stats grid (Anggota) -->
            <div v-if="!isAdmin" class="stats-grid stats-grid-3">
                <div class="stat-card stat-pink">
                    <p class="stat-label">Pinjaman Aktif</p>
                    <p class="stat-value">{{ stats.pinjamanAktif ?? 0 }}</p>
                    <span class="stat-badge badge-pink">dari maks 2</span>
                </div>
                <div class="stat-card stat-teal">
                    <p class="stat-label">Slot Tersisa</p>
                    <p class="stat-value">{{ 2 - (stats.pinjamanAktif ?? 0) }}</p>
                    <span class="stat-badge badge-teal">bisa pinjam lagi</span>
                </div>
                <div class="stat-card stat-purple">
                    <p class="stat-label">Total Riwayat</p>
                    <p class="stat-value">{{ stats.totalRiwayat ?? 0 }}</p>
                    <span class="stat-badge badge-purple">peminjaman</span>
                </div>
            </div>

            <!-- Two column section (Admin) -->
            <div v-if="isAdmin" class="two-col">

                <!-- Booking Terbaru -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Booking terbaru</h2>
                        <Link :href="route('admin.bookings.index')" class="card-link">Lihat semua →</Link>
                    </div>
                    <div v-if="bookingTerbaru.length === 0" class="empty-state">
                        Belum ada data booking
                    </div>
                    <div v-for="booking in bookingTerbaru" :key="booking.id" class="list-row">
                        <div class="list-avatar">
                            {{ initials(booking.user?.name) }}
                        </div>
                        <div class="list-info">
                            <p class="list-name">{{ booking.user?.name }}</p>
                            <p class="list-sub">
                                {{ booking.unit?.nama_unit ?? booking.bundle?.nama_bundle }}
                                &middot; {{ booking.unit?.kode_unit ?? 'Bundle' }}
                            </p>
                        </div>
                        <span :class="['pill', statusClass(booking.status)]">
                            {{ statusLabel(booking.status) }}
                        </span>
                    </div>
                </div>

                <!-- Status Unit Populer -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Status unit populer</h2>
                        <Link :href="route('admin.units.index')" class="card-link">Kelola →</Link>
                    </div>
                    <div v-if="unitPopuler.length === 0" class="empty-state">
                        Belum ada data unit
                    </div>
                    <div v-for="unit in unitPopuler" :key="unit.id" class="unit-row">
                        <div class="unit-code-badge">{{ unit.kode_unit?.substring(0, 3) }}</div>
                        <div class="list-info">
                            <p class="list-name">{{ unit.nama_unit }}</p>
                            <p class="list-sub">{{ unit.kode_unit }}</p>
                        </div>
                        <span :class="['avail-dot', unitStatusClass(unit.status)]"></span>
                    </div>
                    <div class="legend">
                        <span class="legend-item"><span class="avail-dot dot-green"></span> Tersedia</span>
                        <span class="legend-item"><span class="avail-dot dot-red"></span> Dipinjam</span>
                        <span class="legend-item"><span class="avail-dot dot-gray"></span> Rusak</span>
                    </div>
                </div>
            </div>

            <!-- Pinjaman anggota -->
            <div v-if="!isAdmin" class="card">
                <div class="card-head">
                    <h2 class="card-title">Pinjaman saya saat ini</h2>
                    <Link :href="route('bookings.mine')" class="card-link">Lihat semua →</Link>
                </div>
                <div v-if="bookingTerbaru.length === 0" class="empty-state">
                    <p>Kamu belum meminjam unit apapun.</p>
                    <Link :href="route('units.index')" class="btn-primary" style="margin-top: 12px; display: inline-block;">
                        Cari unit sekarang
                    </Link>
                </div>
                <div v-for="booking in bookingTerbaru" :key="booking.id" class="booking-card">
                    <div class="booking-card-head">
                        <div>
                            <p class="list-name">
                                {{ booking.unit?.nama_unit ?? booking.bundle?.nama_bundle }}
                            </p>
                            <p class="list-sub">
                                {{ booking.unit?.kode_unit ?? 'Bundle' }} &middot; #{{ booking.kode_booking }}
                            </p>
                        </div>
                        <span :class="['pill', statusClass(booking.status)]">
                            {{ statusLabel(booking.status) }}
                        </span>
                    </div>
                    <div class="booking-dates">
                        <span>Mulai: <strong>{{ booking.tanggal_mulai }}</strong></span>
                        <span>Batas: <strong>{{ booking.tanggal_selesai_rencana }}</strong></span>
                    </div>
                    <p v-if="booking.status === 'late'" class="late-notice">
                        Unit ini terlambat dikembalikan. Segera hubungi admin.
                    </p>
                </div>
            </div>

        </div>
</template>

<style scoped>
.dashboard {
    max-width: 960px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
}

/* Header */
.dash-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    gap: 1rem;
    flex-wrap: wrap;
}
.greeting { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.greeting-sub { font-size: 0.875rem; color: rgba(255,255,255,0.7); }
.header-actions { display: flex; gap: 8px; align-items: center; }

/* Buttons */
.btn-primary {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.8125rem; font-weight: 600;
    text-decoration: none; border: none; cursor: pointer; box-shadow: 0 0 15px rgba(168,85,247,0.4); transition: 0.3s;
}
.btn-primary:hover { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }
.btn-outline {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.8125rem; font-weight: 600;
    text-decoration: none; cursor: pointer; transition: 0.2s; backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }

/* Alert */
.alert-banner {
    background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #fca5a5;
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 1.25rem; flex-wrap: wrap; backdrop-filter: blur(8px);
}
.alert-info { background: rgba(168, 85, 247, 0.2); border-color: rgba(168, 85, 247, 0.4); color: #d8b4fe; }
.alert-icon {
    width: 18px; height: 18px; border-radius: 50%;
    background: rgba(239, 68, 68, 0.8); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0; box-shadow: 0 0 5px rgba(239, 68, 68, 0.5);
}
.alert-icon-info { background: rgba(168, 85, 247, 0.8); box-shadow: 0 0 5px rgba(168, 85, 247, 0.5); }
.alert-link { margin-left: auto; font-weight: 600; color: #fff; text-decoration: underline; }

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 1.5rem;
}
.stats-grid-3 { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 600px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-grid-3 { grid-template-columns: repeat(2, 1fr); }
}

.stat-card {
    border-radius: 14px; padding: 16px;
    background: rgba(15, 10, 30, 0.75); border: 1px solid rgba(255,255,255,0.15);
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4); transition: 0.3s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.5); border-color: rgba(216,180,254,0.4); }

.stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 8px; color: #fff; }
.stat-badge {
    display: inline-block; font-size: 0.6875rem;
    padding: 4px 10px; border-radius: 10px; font-weight: 600; border: 1px solid transparent;
}

.stat-purple { background: linear-gradient(145deg, rgba(168, 85, 247, 0.15), rgba(15, 10, 30, 0.8)); }
.stat-teal   { background: linear-gradient(145deg, rgba(34, 197, 94, 0.15), rgba(15, 10, 30, 0.8)); }
.stat-coral  { background: linear-gradient(145deg, rgba(239, 68, 68, 0.15), rgba(15, 10, 30, 0.8)); }
.stat-amber  { background: linear-gradient(145deg, rgba(245, 158, 11, 0.15), rgba(15, 10, 30, 0.8)); }
.stat-pink   { background: linear-gradient(145deg, rgba(236, 72, 153, 0.15), rgba(15, 10, 30, 0.8)); }
.stat-blue   { background: linear-gradient(145deg, rgba(59, 130, 246, 0.15), rgba(15, 10, 30, 0.8)); }

.badge-purple { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border-color: rgba(168, 85, 247, 0.3); }
.badge-teal   { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.badge-coral  { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
.badge-amber  { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border-color: rgba(245, 158, 11, 0.3); }
.badge-pink   { background: rgba(236, 72, 153, 0.2); color: #f9a8d4; border-color: rgba(236, 72, 153, 0.3); }
.badge-blue   { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3); }

/* Two col layout */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 640px) { .two-col { grid-template-columns: 1fr; } }

/* Card */
.card {
    background: rgba(15, 10, 30, 0.75);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 14px; padding: 1.25rem;
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}
.card-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 12px;
}
.card-title { font-size: 1rem; font-weight: 600; color: #fff; }
.card-link { font-size: 0.75rem; color: #d8b4fe; text-decoration: none; font-weight: 600; }
.card-link:hover { text-decoration: underline; color: #fff; }
.empty-state { text-align: center; padding: 24px 0; color: rgba(255,255,255,0.5); font-size: 0.8125rem; }

/* List row (booking terbaru) */
.list-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);
}
.list-row:last-child { border-bottom: none; }
.list-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168,85,247,0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600; flex-shrink: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}
.list-info { flex: 1; min-width: 0; }
.list-name { font-size: 0.875rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-sub  { font-size: 0.6875rem; color: rgba(255,255,255,0.6); margin-top: 2px; }

/* Unit row */
.unit-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);
}
.unit-row:last-child { border-bottom: none; }
.unit-code-badge {
    width: 36px; height: 36px; border-radius: 8px;
    background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0; text-transform: uppercase;
}

/* Availability dot */
.avail-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 5px rgba(0,0,0,0.5); }
.dot-green { background: #34d399; box-shadow: 0 0 8px rgba(52, 211, 153, 0.6); }
.dot-red   { background: #f87171; box-shadow: 0 0 8px rgba(248, 113, 113, 0.6); }
.dot-gray  { background: #9ca3af; }

.legend {
    display: flex; gap: 14px; margin-top: 14px; padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.1);
}
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: rgba(255,255,255,0.6); font-weight: 500; }

/* Status pills */
.pill { font-size: 0.6875rem; padding: 4px 10px; border-radius: 10px; font-weight: 600; white-space: nowrap; border: 1px solid transparent; }
.pill-active { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.pill-booked { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border-color: rgba(168, 85, 247, 0.3); }
.pill-late   { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
.pill-done   { background: rgba(255, 255, 255, 0.1); color: #fff; border-color: rgba(255, 255, 255, 0.2); }

/* Booking card (anggota) */
.booking-card {
    background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px;
    padding: 12px 14px; margin-bottom: 10px; transition: 0.2s;
}
.booking-card:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2); }
.booking-card:last-child { margin-bottom: 0; }
.booking-card-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 8px;
}
.booking-dates {
    display: flex; gap: 16px;
    font-size: 0.75rem; color: rgba(255,255,255,0.6);
}
.booking-dates strong { color: #fff; font-weight: 600; }
.late-notice {
    margin-top: 8px; padding: 8px 12px;
    background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px;
    font-size: 0.75rem; color: #fca5a5; font-weight: 500;
}
</style>