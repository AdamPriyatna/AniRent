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
.page-title { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.page-sub { font-size: 0.875rem; color: rgba(255,255,255,0.7); }

.alert-success {
    background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #86efac; margin-bottom: 1rem;
    backdrop-filter: blur(8px);
}
.alert-error {
    background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #fca5a5; margin-bottom: 1rem;
    backdrop-filter: blur(8px);
}

/* Stats */
.stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 600px) { .stats-row { grid-template-columns: 1fr; } }

.stat-card {
    display: flex; align-items: center; gap: 14px;
    padding: 1rem 1.25rem; border-radius: 12px;
    border: 1px solid transparent; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
}
.stat-blue   { background: rgba(59, 130, 246, 0.15); border-color: rgba(59, 130, 246, 0.3); }
.stat-teal   { background: rgba(34, 197, 94, 0.15); border-color: rgba(34, 197, 94, 0.3); }
.stat-red    { background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); }

.stat-icon { font-size: 22px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.3)); }
.stat-val { font-size: 1.5rem; font-weight: 700; color: #fff; line-height: 1; text-shadow: 0 0 10px rgba(255,255,255,0.2); }
.stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 4px; }

/* Filter */
.filter-bar { display: flex; gap: 10px; margin-bottom: 1.25rem; flex-wrap: wrap; }
.input-search {
    flex: 1; min-width: 200px; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;
    font-size: 0.875rem; outline: none; background: rgba(0,0,0,0.4); color: white;
    backdrop-filter: blur(8px);
}
.input-search::placeholder { color: rgba(255,255,255,0.4); }
.input-search:focus { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }

.input-select {
    padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px; font-size: 0.875rem; outline: none;
    background: rgba(0,0,0,0.4); color: white; cursor: pointer;
    backdrop-filter: blur(8px);
}
.input-select:focus { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-select option { background: #1a103c; color: white; }

/* Table */
.table-wrap { 
    border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; overflow: hidden; 
    background: rgba(15, 10, 30, 0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.6);
}
.table { width: 100%; border-collapse: collapse; color: white; }
.table thead tr { background: rgba(255,255,255,0.08); }
.table th {
    padding: 14px; text-align: left;
    font-size: 0.75rem; font-weight: 600; color: #f8fafc;
    border-bottom: 1px solid rgba(255,255,255,0.1); white-space: nowrap;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.table td {
    padding: 14px; font-size: 0.8125rem;
    border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle;
}
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover { background: rgba(255,255,255,0.05); }
.row-late { background: rgba(239, 68, 68, 0.08) !important; }
.row-late:hover { background: rgba(239, 68, 68, 0.15) !important; }
.empty-row { text-align: center; color: rgba(255,255,255,0.5); padding: 32px !important; }
.text-muted { color: rgba(255,255,255,0.5); }

/* Cells */
.code-badge {
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.75rem; font-weight: 600;
    padding: 4px 8px; border-radius: 6px;
    font-family: monospace; white-space: nowrap;
    border: 1px solid rgba(168, 85, 247, 0.3);
}
.member-name { font-weight: 600; color: #fff; }

.item-cell { display: flex; flex-direction: column; gap: 3px; }
.item-type {
    font-size: 0.625rem; font-weight: 700; padding: 1px 6px;
    border-radius: 4px; display: inline-block; width: fit-content;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.unit-type   { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.3); }
.bundle-type { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3); }
.item-name { font-size: 0.8125rem; color: #fff; font-weight: 500; }

.date-cell { white-space: nowrap; color: rgba(255,255,255,0.7); }
.date-late { color: #fca5a5; font-weight: 600; }
.text-red  { color: #fca5a5; font-weight: 600; }

/* Pills */
.pill { font-size: 0.6875rem; padding: 4px 10px; border-radius: 10px; font-weight: 600; border: 1px solid transparent; }
.pill-blue  { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3); }
.pill-teal  { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.pill-red   { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
.pill-gray  { background: rgba(255, 255, 255, 0.1); color: rgba(255,255,255,0.7); }

/* Denda */
.denda-val  { font-weight: 600; color: #fca5a5; font-size: 0.8125rem; }
.denda-hari { font-size: 0.6875rem; color: rgba(255,255,255,0.5); }

/* Buttons */
.btn-return {
    padding: 8px 16px; border-radius: 7px;
    background: linear-gradient(135deg, #a855f7, #ec4899); color: #fff;
    font-size: 0.75rem; font-weight: 600;
    border: none; cursor: pointer; white-space: nowrap;
    transition: 0.3s; box-shadow: 0 0 10px rgba(168,85,247,0.3);
}
.btn-return:hover { box-shadow: 0 0 20px rgba(168,85,247,0.6); transform: translateY(-1px); }

.btn-pickup {
    padding: 8px 16px; border-radius: 7px;
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.75rem; font-weight: 600;
    border: 1px solid rgba(168, 85, 247, 0.3); cursor: pointer; white-space: nowrap;
    transition: 0.2s;
}
.btn-pickup:hover { background: rgba(168, 85, 247, 0.4); color: #fff; }

.btn-primary {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.8125rem; font-weight: 600;
    border: none; cursor: pointer; white-space: nowrap; box-shadow: 0 0 15px rgba(168,85,247,0.4); transition: 0.3s;
}
.btn-primary:hover { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }

.btn-outline {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.8125rem; font-weight: 600;
    text-decoration: none; cursor: pointer; white-space: nowrap; transition: 0.2s; backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }

.pagination { display: flex; gap: 6px; margin-top: 1.5rem; flex-wrap: wrap; }
.page-btn {
    padding: 8px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3);
    font-size: 0.8125rem; color: rgba(255,255,255,0.7); text-decoration: none; cursor: pointer;
    transition: 0.2s; backdrop-filter: blur(8px);
}
.page-btn:hover:not(.disabled) { background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.3); }
.page-btn.active { background: linear-gradient(135deg, #a855f7, #ec4899); color: #fff; border: none; box-shadow: 0 0 10px rgba(168,85,247,0.4); }
.page-btn.disabled { color: rgba(255,255,255,0.3); pointer-events: none; border-color: transparent; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem; animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }
.modal {
    background: rgba(20, 12, 45, 0.95); border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 480px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6); animation: slideUp 0.2s ease; color: white;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);
}
.modal-title { font-size: 1.2rem; font-weight: 600; color: #fff; }
.modal-close { background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.5); font-size: 1.2rem; transition: 0.2s; }
.modal-close:hover { color: #fff; }
.modal-body { padding: 1.25rem 1.5rem 1.5rem; }

.return-info-card {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px; padding: 1rem; margin-bottom: 1rem;
}
.return-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; margin-bottom: 8px; font-size: 0.8125rem;
}
.return-row:last-child { margin-bottom: 0; }
.return-label { color: rgba(255,255,255,0.6); flex-shrink: 0; }
.return-val { color: #fff; font-weight: 600; text-align: right; }

.late-warning {
    display: flex; gap: 12px; align-items: flex-start;
    background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 10px; padding: 12px 14px; margin-bottom: 1rem;
}
.late-icon { font-size: 20px; flex-shrink: 0; }
.late-title { font-size: 0.875rem; font-weight: 600; color: #fca5a5; margin-bottom: 4px; }
.late-sub { font-size: 0.8125rem; color: rgba(255,255,255,0.8); line-height: 1.5; }

.ontime-info {
    background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 10px; padding: 12px 14px; font-size: 0.8125rem; color: #86efac; margin-bottom: 1rem;
}

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);
}
.btn-confirm {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7, #ec4899); color: #fff;
    font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: 0.3s;
}
.btn-confirm:hover:not(:disabled) { box-shadow: 0 0 20px rgba(168,85,247,0.6); transform: translateY(-1px); }
.btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
