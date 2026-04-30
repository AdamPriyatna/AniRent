<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage } from '@inertiajs/vue3'

defineOptions({
    layout: AuthenticatedLayout
})

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
                <div class="stat-card stat-purple">
                    <p class="stat-label">Total Unit</p>
                    <p class="stat-value">{{ stats.totalUnit }}</p>
                    <span class="stat-badge badge-purple">{{ stats.totalBundle }} bundle</span>
                </div>
                <div class="stat-card stat-teal">
                    <p class="stat-label">Sedang Dipinjam</p>
                    <p class="stat-value">{{ stats.unitDipinjam }}</p>
                    <span class="stat-badge badge-teal">aktif</span>
                </div>
                <div class="stat-card stat-coral">
                    <p class="stat-label">Terlambat</p>
                    <p class="stat-value">{{ stats.unitTerlambat }}</p>
                    <span class="stat-badge badge-coral">perlu aksi</span>
                </div>
                <div class="stat-card stat-amber">
                    <p class="stat-label">Total Anggota</p>
                    <p class="stat-value">{{ stats.totalAnggota }}</p>
                    <span class="stat-badge badge-amber">terdaftar</span>
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
.greeting { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.greeting-sub { font-size: 0.875rem; color: #666; }
.header-actions { display: flex; gap: 8px; align-items: center; }

/* Buttons */
.btn-primary {
    padding: 8px 16px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    text-decoration: none; border: none; cursor: pointer;
}
.btn-primary:hover { background: #3C3489; }
.btn-outline {
    padding: 8px 16px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.8125rem; font-weight: 500;
    text-decoration: none; cursor: pointer;
}
.btn-outline:hover { background: #EEEDFE; }

/* Alert */
.alert-banner {
    background: #FAECE7; border: 0.5px solid #F0997B;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #712B13;
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 1.25rem; flex-wrap: wrap;
}
.alert-info { background: #EEEDFE; border-color: #AFA9EC; color: #3C3489; }
.alert-icon {
    width: 18px; height: 18px; border-radius: 50%;
    background: #D85A30; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.alert-icon-info { background: #534AB7; }
.alert-link { margin-left: auto; font-weight: 500; color: inherit; text-decoration: underline; }

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 1.25rem;
}
.stats-grid-3 { grid-template-columns: repeat(3, 1fr); }
@media (max-width: 600px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-grid-3 { grid-template-columns: repeat(2, 1fr); }
}

.stat-card {
    border-radius: 10px; padding: 14px 16px;
}
.stat-label { font-size: 0.75rem; color: inherit; opacity: 0.75; margin-bottom: 4px; }
.stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1; margin-bottom: 6px; }
.stat-badge {
    display: inline-block; font-size: 0.6875rem;
    padding: 3px 8px; border-radius: 10px; font-weight: 500;
}

.stat-purple { background: #EEEDFE; color: #3C3489; }
.stat-teal   { background: #E1F5EE; color: #085041; }
.stat-coral  { background: #FAECE7; color: #712B13; }
.stat-amber  { background: #FAEEDA; color: #633806; }
.stat-pink   { background: #FBEAF0; color: #72243E; }

.badge-purple { background: #CECBF6; color: #3C3489; }
.badge-teal   { background: #9FE1CB; color: #085041; }
.badge-coral  { background: #F5C4B3; color: #712B13; }
.badge-amber  { background: #FAC775; color: #633806; }
.badge-pink   { background: #F4C0D1; color: #72243E; }

/* Two col layout */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 640px) { .two-col { grid-template-columns: 1fr; } }

/* Card */
.card {
    background: #fff;
    border: 0.5px solid #e0ddd5;
    border-radius: 12px; padding: 16px;
}
.card-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 12px;
}
.card-title { font-size: 0.875rem; font-weight: 600; color: #1a1a2e; }
.card-link { font-size: 0.75rem; color: #534AB7; text-decoration: none; }
.card-link:hover { text-decoration: underline; }
.empty-state { text-align: center; padding: 24px 0; color: #aaa; font-size: 0.8125rem; }

/* List row (booking terbaru) */
.list-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 0; border-bottom: 0.5px solid #f0ece6;
}
.list-row:last-child { border-bottom: none; }
.list-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: #EEEDFE; color: #534AB7;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600; flex-shrink: 0;
}
.list-info { flex: 1; min-width: 0; }
.list-name { font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-sub  { font-size: 0.6875rem; color: #888; margin-top: 1px; }

/* Unit row */
.unit-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 0; border-bottom: 0.5px solid #f0ece6;
}
.unit-row:last-child { border-bottom: none; }
.unit-code-badge {
    width: 30px; height: 30px; border-radius: 6px;
    background: #F1EFE8; color: #5F5E5A;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0;
}

/* Availability dot */
.avail-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.dot-green { background: #1D9E75; }
.dot-red   { background: #D85A30; }
.dot-gray  { background: #B4B2A9; }

.legend {
    display: flex; gap: 14px; margin-top: 10px; padding-top: 8px;
    border-top: 0.5px solid #f0ece6;
}
.legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.6875rem; color: #888; }

/* Status pills */
.pill { font-size: 0.6875rem; padding: 3px 9px; border-radius: 10px; font-weight: 500; white-space: nowrap; }
.pill-active { background: #E1F5EE; color: #0F6E56; }
.pill-booked { background: #EEEDFE; color: #534AB7; }
.pill-late   { background: #FAECE7; color: #993C1D; }
.pill-done   { background: #F1EFE8; color: #5F5E5A; }

/* Booking card (anggota) */
.booking-card {
    border: 0.5px solid #e0ddd5; border-radius: 10px;
    padding: 12px 14px; margin-bottom: 10px;
}
.booking-card:last-child { margin-bottom: 0; }
.booking-card-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 8px;
}
.booking-dates {
    display: flex; gap: 16px;
    font-size: 0.75rem; color: #888;
}
.booking-dates strong { color: #1a1a2e; }
.late-notice {
    margin-top: 8px; padding: 6px 10px;
    background: #FAECE7; border-radius: 6px;
    font-size: 0.75rem; color: #993C1D;
}
</style>