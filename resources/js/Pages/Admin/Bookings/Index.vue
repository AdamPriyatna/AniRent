<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bookings: Object,
    filters:  Object,
    stats:    Object,
})

// ─── Search & Filter ──────────────────────────────────────────────────────────
const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

function doSearch() {
    router.get(route('admin.bookings.index'), {
        search: search.value,
        status: status.value,
    }, { preserveState: true, replace: true })
}

function resetFilter() {
    search.value = ''
    status.value = ''
    doSearch()
}

// ─── Return Modal ─────────────────────────────────────────────────────────────
const showReturnModal  = ref(false)
const bookingToReturn  = ref(null)
const isProcessing     = ref(false)

function openReturn(booking) {
    bookingToReturn.value = booking
    showReturnModal.value = true
}

function cancelReturn() {
    showReturnModal.value = false
    bookingToReturn.value = null
}

function doReturn() {
    if (!bookingToReturn.value) return
    isProcessing.value = true
    router.patch(route('admin.bookings.return', bookingToReturn.value.id), {}, {
        onSuccess: () => { showReturnModal.value = false; bookingToReturn.value = null },
        onFinish:  () => { isProcessing.value = false },
    })
}

function doPickup(booking) {
    if (!confirm(`Konfirmasi pengambilan item untuk booking ${booking.kode_booking}?`)) return
    router.patch(route('admin.bookings.pickup', booking.id))
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatRupiah(val) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

const statusMap = {
    booked: { label: 'Booked',  cls: 'pill-blue'  },
    active: { label: 'Aktif',   cls: 'pill-teal'  },
    late:   { label: 'Terlambat', cls: 'pill-red' },
}

function statusInfo(s) {
    return statusMap[s] ?? { label: s, cls: 'pill-gray' }
}

function isLate(booking) {
    return booking.status === 'late' || (
        booking.status === 'active' &&
        new Date(booking.tanggal_selesai_rencana) < new Date()
    )
}

const returnBooking = computed(() => bookingToReturn.value)
</script>

<template>
    <Head title="Manajemen Booking" />

    <div class="page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Booking Aktif</h1>
                <p class="page-sub">Monitor dan proses pengembalian peminjaman</p>
            </div>
            <Link :href="route('admin.bookings.history')" class="btn-outline">
                📋 Riwayat Pengembalian
            </Link>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">{{ $page.props.flash.success }}</div>
        <div v-if="$page.props.flash?.error"   class="alert-error">{{ $page.props.flash.error }}</div>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card stat-blue">
                <div class="stat-icon">📅</div>
                <div>
                    <div class="stat-val">{{ stats.booked }}</div>
                    <div class="stat-label">Menunggu Aktivasi</div>
                </div>
            </div>
            <div class="stat-card stat-teal">
                <div class="stat-icon">✅</div>
                <div>
                    <div class="stat-val">{{ stats.active }}</div>
                    <div class="stat-label">Sedang Dipinjam</div>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="stat-icon">⚠️</div>
                <div>
                    <div class="stat-val">{{ stats.late }}</div>
                    <div class="stat-label">Terlambat</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input
                v-model="search"
                type="text"
                placeholder="Cari kode booking atau nama anggota..."
                class="input-search"
                @keyup.enter="doSearch"
            />
            <select v-model="status" class="input-select" @change="doSearch">
                <option value="">Semua Status</option>
                <option value="booked">Booked</option>
                <option value="active">Aktif</option>
                <option value="late">Terlambat</option>
            </select>
            <button class="btn-primary" @click="doSearch">Cari</button>
            <button v-if="search || status" class="btn-outline" @click="resetFilter">Reset</button>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Anggota</th>
                        <th>Item</th>
                        <th>Mulai</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th>Denda Est.</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="bookings.data.length === 0">
                        <td colspan="8" class="empty-row">Tidak ada booking aktif</td>
                    </tr>
                    <tr
                        v-for="b in bookings.data"
                        :key="b.id"
                        :class="{ 'row-late': b.status === 'late' }"
                    >
                        <!-- Kode -->
                        <td>
                            <span class="code-badge">{{ b.kode_booking }}</span>
                        </td>

                        <!-- Anggota -->
                        <td>
                            <div class="member-name">{{ b.user?.name ?? '—' }}</div>
                        </td>

                        <!-- Item (Unit atau Bundle) -->
                        <td>
                            <div v-if="b.unit" class="item-cell">
                                <span class="item-type unit-type">Unit</span>
                                <span class="item-name">{{ b.unit.nama_unit }}</span>
                            </div>
                            <div v-else-if="b.bundle" class="item-cell">
                                <span class="item-type bundle-type">Bundle</span>
                                <span class="item-name">{{ b.bundle.nama_bundle }}</span>
                            </div>
                            <span v-else class="text-muted">—</span>
                        </td>

                        <!-- Mulai -->
                        <td class="date-cell">{{ formatDate(b.tanggal_mulai) }}</td>

                        <!-- Tenggat -->
                        <td class="date-cell">
                            <span :class="{ 'date-late': b.status === 'late' }">
                                {{ formatDate(b.tanggal_selesai_rencana) }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span :class="['pill', statusInfo(b.status).cls]">
                                {{ statusInfo(b.status).label }}
                            </span>
                        </td>

                        <!-- Denda Estimasi -->
                        <td>
                            <template v-if="b.status === 'late' && b.hari_terlambat > 0">
                                <div class="denda-val">{{ formatRupiah(b.denda_estimasi) }}</div>
                                <div class="denda-hari">{{ b.hari_terlambat }} hari terlambat</div>
                            </template>
                            <span v-else class="text-muted">—</span>
                        </td>

                        <!-- Aksi -->
                        <td>
                            <button
                                v-if="b.status === 'booked'"
                                class="btn-pickup"
                                @click="doPickup(b)"
                            >
                                Serahkan Item
                            </button>
                            <button
                                v-else
                                class="btn-return"
                                @click="openReturn(b)"
                            >
                                Proses Kembali
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="bookings.last_page > 1">
            <a
                v-for="link in bookings.links"
                :key="link.label"
                :href="link.url ?? '#'"
                class="page-btn"
                :class="{ active: link.active, disabled: !link.url }"
                v-html="link.label"
                @click.prevent="link.url && router.get(link.url)"
            />
        </div>
    </div>

    <!-- ═══════════════ RETURN CONFIRMATION MODAL ═══════════════ -->
    <Teleport to="body">
        <div v-if="showReturnModal" class="modal-overlay" @click.self="cancelReturn">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Proses Pengembalian</h2>
                    <button class="modal-close" @click="cancelReturn">✕</button>
                </div>

                <div class="modal-body" v-if="returnBooking">

                    <!-- Booking Info -->
                    <div class="return-info-card">
                        <div class="return-row">
                            <span class="return-label">Kode Booking</span>
                            <span class="return-val code-badge">{{ returnBooking.kode_booking }}</span>
                        </div>
                        <div class="return-row">
                            <span class="return-label">Anggota</span>
                            <span class="return-val">{{ returnBooking.user?.name ?? '—' }}</span>
                        </div>
                        <div class="return-row">
                            <span class="return-label">Item</span>
                            <span class="return-val">
                                {{ returnBooking.unit?.nama_unit ?? returnBooking.bundle?.nama_bundle ?? '—' }}
                            </span>
                        </div>
                        <div class="return-row">
                            <span class="return-label">Tenggat</span>
                            <span class="return-val" :class="{ 'text-red': returnBooking.status === 'late' }">
                                {{ formatDate(returnBooking.tanggal_selesai_rencana) }}
                            </span>
                        </div>
                        <div class="return-row">
                            <span class="return-label">Tanggal Kembali</span>
                            <span class="return-val">{{ formatDate(new Date()) }}</span>
                        </div>
                    </div>

                    <!-- Late warning -->
                    <div v-if="returnBooking.status === 'late'" class="late-warning">
                        <div class="late-icon">⚠️</div>
                        <div>
                            <div class="late-title">Terlambat {{ returnBooking.hari_terlambat }} hari!</div>
                            <div class="late-sub">
                                Estimasi denda: <strong>{{ formatRupiah(returnBooking.denda_estimasi) }}</strong>
                                <br>Denda akan otomatis dicatat dan status pembayaran: <em>Belum Lunas</em>
                            </div>
                        </div>
                    </div>

                    <!-- On time info -->
                    <div v-else class="ontime-info">
                        ✅ Pengembalian tepat waktu — tidak ada denda
                    </div>

                    <div class="modal-footer">
                        <button class="btn-outline" @click="cancelReturn">Batal</button>
                        <button
                            class="btn-confirm"
                            :disabled="isProcessing"
                            @click="doReturn"
                        >
                            {{ isProcessing ? 'Memproses...' : 'Konfirmasi Pengembalian' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.page { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }

.page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
}
.page-title { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-sub { font-size: 0.875rem; color: #888; }

.alert-success {
    background: #E1F5EE; border: 0.5px solid #5DCAA5;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #085041; margin-bottom: 1rem;
}
.alert-error {
    background: #FAECE7; border: 0.5px solid #f59e80;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #993C1D; margin-bottom: 1rem;
}

/* Stats */
.stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 600px) { .stats-row { grid-template-columns: 1fr; } }

.stat-card {
    display: flex; align-items: center; gap: 14px;
    padding: 1rem 1.25rem; border-radius: 12px;
    border: 0.5px solid transparent;
}
.stat-blue   { background: #EFF6FF; border-color: #BFDBFE; }
.stat-teal   { background: #E1F5EE; border-color: #A7F3D0; }
.stat-red    { background: #FEF2F2; border-color: #FECACA; }

.stat-icon { font-size: 22px; }
.stat-val { font-size: 1.5rem; font-weight: 700; color: #1a1a2e; line-height: 1; }
.stat-label { font-size: 0.75rem; color: #888; margin-top: 4px; }

/* Filter */
.filter-bar { display: flex; gap: 10px; margin-bottom: 1.25rem; flex-wrap: wrap; }
.input-search {
    flex: 1; min-width: 200px; padding: 8px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none;
}
.input-search:focus { border-color: #534AB7; }
.input-select {
    padding: 8px 12px; border: 0.5px solid #d0ccc4;
    border-radius: 8px; font-size: 0.875rem; outline: none;
    background: #fff; cursor: pointer;
}
.input-select:focus { border-color: #534AB7; }

/* Table */
.table-wrap { border: 0.5px solid #e0ddd5; border-radius: 12px; overflow: hidden; }
.table { width: 100%; border-collapse: collapse; }
.table thead tr { background: #F8F7F4; }
.table th {
    padding: 11px 14px; text-align: left;
    font-size: 0.75rem; font-weight: 600; color: #888;
    border-bottom: 0.5px solid #e0ddd5; white-space: nowrap;
}
.table td {
    padding: 12px 14px; font-size: 0.8125rem;
    border-bottom: 0.5px solid #f0ece6; vertical-align: middle;
}
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover { background: #FAFAF8; }
.row-late { background: #FFF8F7 !important; }
.row-late:hover { background: #FEF2F0 !important; }
.empty-row { text-align: center; color: #aaa; padding: 32px !important; }
.text-muted { color: #aaa; }

/* Cells */
.code-badge {
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 600;
    padding: 3px 8px; border-radius: 6px;
    font-family: monospace; white-space: nowrap;
}
.member-name { font-weight: 500; color: #1a1a2e; }

.item-cell { display: flex; flex-direction: column; gap: 3px; }
.item-type {
    font-size: 0.625rem; font-weight: 700; padding: 1px 6px;
    border-radius: 4px; display: inline-block; width: fit-content;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.unit-type   { background: #EEEDFE; color: #534AB7; }
.bundle-type { background: #FEF3C7; color: #92400E; }
.item-name { font-size: 0.8125rem; color: #1a1a2e; font-weight: 500; }

.date-cell { white-space: nowrap; color: #555; }
.date-late { color: #e53935; font-weight: 600; }
.text-red  { color: #e53935; font-weight: 600; }

/* Pills */
.pill { font-size: 0.6875rem; padding: 3px 10px; border-radius: 10px; font-weight: 500; }
.pill-blue  { background: #EFF6FF; color: #1D4ED8; }
.pill-teal  { background: #E1F5EE; color: #0F6E56; }
.pill-red   { background: #FEF2F2; color: #B91C1C; }
.pill-gray  { background: #F1EFE8; color: #5F5E5A; }

/* Denda */
.denda-val  { font-weight: 600; color: #e53935; font-size: 0.8125rem; }
.denda-hari { font-size: 0.6875rem; color: #aaa; }

/* Buttons */
.btn-return {
    padding: 6px 14px; border-radius: 7px;
    background: #534AB7; color: #fff;
    font-size: 0.75rem; font-weight: 500;
    border: none; cursor: pointer; white-space: nowrap;
    transition: background 0.15s;
}
.btn-return:hover { background: #3C3489; }

.btn-pickup {
    padding: 6px 14px; border-radius: 7px;
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 600;
    border: 1.5px solid #534AB7; cursor: pointer; white-space: nowrap;
    transition: all 0.2s;
}
.btn-pickup:hover { background: #534AB7; color: white; }

.btn-primary {
    padding: 8px 16px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    border: none; cursor: pointer; white-space: nowrap;
}
.btn-primary:hover { background: #3C3489; }

.btn-outline {
    padding: 8px 16px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.8125rem; font-weight: 500;
    text-decoration: none; cursor: pointer; white-space: nowrap;
}
.btn-outline:hover { background: #EEEDFE; }

.pagination { display: flex; gap: 4px; margin-top: 1.25rem; flex-wrap: wrap; }
.page-btn {
    padding: 6px 11px; border-radius: 6px;
    border: 0.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #534AB7; text-decoration: none; cursor: pointer;
}
.page-btn.active { background: #534AB7; color: #fff; border-color: #534AB7; }
.page-btn.disabled { color: #ccc; pointer-events: none; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem; animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }
.modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 480px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    animation: slideUp 0.2s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 0.5px solid #f0ece6;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1rem; }
.modal-close:hover { color: #555; }
.modal-body { padding: 1.25rem 1.5rem 1.5rem; }

.return-info-card {
    background: #F9F8FF; border: 0.5px solid #dddaf8;
    border-radius: 10px; padding: 1rem; margin-bottom: 1rem;
}
.return-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; margin-bottom: 8px; font-size: 0.8125rem;
}
.return-row:last-child { margin-bottom: 0; }
.return-label { color: #888; flex-shrink: 0; }
.return-val { color: #1a1a2e; font-weight: 500; text-align: right; }

.late-warning {
    display: flex; gap: 12px; align-items: flex-start;
    background: #FEF2F2; border: 0.5px solid #FECACA;
    border-radius: 10px; padding: 12px 14px;
    margin-bottom: 1rem;
}
.late-icon { font-size: 20px; flex-shrink: 0; }
.late-title { font-size: 0.875rem; font-weight: 600; color: #B91C1C; margin-bottom: 4px; }
.late-sub { font-size: 0.8125rem; color: #7F1D1D; line-height: 1.5; }

.ontime-info {
    background: #E1F5EE; border: 0.5px solid #A7F3D0;
    border-radius: 10px; padding: 12px 14px;
    font-size: 0.8125rem; color: #065F46;
    margin-bottom: 1rem;
}

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 1rem; border-top: 0.5px solid #f0ece6;
}
.btn-confirm {
    padding: 9px 20px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.875rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-confirm:hover:not(:disabled) { background: #3C3489; }
.btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
