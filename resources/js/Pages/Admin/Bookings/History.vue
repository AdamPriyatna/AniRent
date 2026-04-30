<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bookings: Object,
    filters:  Object,
})

// ─── Filter ───────────────────────────────────────────────────────────────────
const search = ref(props.filters?.search ?? '')
const bulan  = ref(props.filters?.bulan  ?? '')
const tahun  = ref(props.filters?.tahun  ?? '')

const bulanList = [
    { val: '1', label: 'Januari' }, { val: '2', label: 'Februari' },
    { val: '3', label: 'Maret'   }, { val: '4', label: 'April'   },
    { val: '5', label: 'Mei'     }, { val: '6', label: 'Juni'    },
    { val: '7', label: 'Juli'    }, { val: '8', label: 'Agustus' },
    { val: '9', label: 'September' }, { val: '10', label: 'Oktober' },
    { val: '11', label: 'November' }, { val: '12', label: 'Desember' },
]

const currentYear = new Date().getFullYear()
const tahunList   = Array.from({ length: 5 }, (_, i) => currentYear - i)

function doSearch() {
    router.get(route('admin.bookings.history'), {
        search: search.value,
        bulan:  bulan.value,
        tahun:  tahun.value,
    }, { preserveState: true, replace: true })
}

function resetFilter() {
    search.value = ''
    bulan.value  = ''
    tahun.value  = ''
    doSearch()
}

// ─── PDF Report URL ───────────────────────────────────────────────────────────
const reportUrl = computed(() => {
    const params = new URLSearchParams()
    if (bulan.value) params.set('bulan', bulan.value)
    if (tahun.value) params.set('tahun', tahun.value)
    const qs = params.toString()
    return route('admin.bookings.report') + (qs ? '?' + qs : '')
})

// ─── Detail Modal ─────────────────────────────────────────────────────────────
const showDetail  = ref(false)
const detailItem  = ref(null)

function openDetail(b) {
    detailItem.value = b
    showDetail.value = true
}

function closeDetail() {
    showDetail.value = false
    detailItem.value = null
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function formatDate(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatRupiah(val) {
    if (!val) return '—'
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

function wasLate(b) {
    return b.fine !== null
}

function durasi(b) {
    if (!b.tanggal_mulai || !b.tanggal_selesai_aktual) return '—'
    const start = new Date(b.tanggal_mulai)
    const end   = new Date(b.tanggal_selesai_aktual)
    const days  = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
    return `${days} hari`
}
</script>

<template>
    <Head title="Riwayat Booking" />

    <div class="page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Riwayat Pengembalian</h1>
                <p class="page-sub">Semua booking yang sudah dikembalikan</p>
            </div>
            <div class="header-actions">
                <a :href="reportUrl" target="_blank" class="btn-pdf">🖨️ Cetak PDF</a>
                <Link :href="route('admin.bookings.index')" class="btn-outline">
                    ← Booking Aktif
                </Link>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">{{ $page.props.flash.success }}</div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input
                v-model="search"
                type="text"
                placeholder="Cari kode booking atau nama anggota..."
                class="input-search"
                @keyup.enter="doSearch"
            />
            <select v-model="bulan" class="input-select" @change="doSearch">
                <option value="">Semua Bulan</option>
                <option v-for="b in bulanList" :key="b.val" :value="b.val">{{ b.label }}</option>
            </select>
            <select v-model="tahun" class="input-select" @change="doSearch">
                <option value="">Semua Tahun</option>
                <option v-for="y in tahunList" :key="y" :value="y">{{ y }}</option>
            </select>
            <button class="btn-primary" @click="doSearch">Cari</button>
            <button v-if="search || bulan || tahun" class="btn-outline" @click="resetFilter">Reset</button>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Anggota</th>
                        <th>Item</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Durasi</th>
                        <th>Denda</th>
                        <th>Diproses</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="bookings.data.length === 0">
                        <td colspan="9" class="empty-row">Belum ada riwayat pengembalian</td>
                    </tr>
                    <tr v-for="b in bookings.data" :key="b.id">

                        <!-- Kode -->
                        <td><span class="code-badge">{{ b.kode_booking }}</span></td>

                        <!-- Anggota -->
                        <td>
                            <div class="member-name">{{ b.user?.name ?? '—' }}</div>
                            <div class="member-email">{{ b.user?.email ?? '' }}</div>
                        </td>

                        <!-- Item -->
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

                        <!-- Tanggal Pinjam -->
                        <td class="date-cell">{{ formatDate(b.tanggal_mulai) }}</td>

                        <!-- Tanggal Kembali -->
                        <td class="date-cell">{{ formatDate(b.tanggal_selesai_aktual) }}</td>

                        <!-- Durasi -->
                        <td class="text-muted">{{ durasi(b) }}</td>

                        <!-- Denda -->
                        <td>
                            <template v-if="wasLate(b) && b.fine">
                                <div class="denda-val">{{ formatRupiah(b.fine.jumlah_denda) }}</div>
                                <span :class="['pill-fine', b.fine.status === 'paid' ? 'fine-paid' : 'fine-unpaid']">
                                    {{ b.fine.status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </template>
                            <span v-else class="no-denda">—</span>
                        </td>

                        <!-- Diproses oleh -->
                        <td class="text-muted">{{ b.processed_by?.name ?? b.processedBy?.name ?? '—' }}</td>

                        <!-- Detail button -->
                        <td>
                            <button class="btn-detail" @click="openDetail(b)">Lihat</button>
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

    <!-- ═══════════════ DETAIL MODAL ═══════════════ -->
    <Teleport to="body">
        <div v-if="showDetail && detailItem" class="modal-overlay" @click.self="closeDetail">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Detail Booking</h2>
                        <span class="code-badge">{{ detailItem.kode_booking }}</span>
                    </div>
                    <button class="modal-close" @click="closeDetail">✕</button>
                </div>

                <div class="modal-body">
                    <!-- Info Sections -->
                    <div class="detail-section">
                        <div class="detail-section-title">Informasi Anggota</div>
                        <div class="detail-row">
                            <span class="detail-label">Nama</span>
                            <span class="detail-val">{{ detailItem.user?.name ?? '—' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email</span>
                            <span class="detail-val">{{ detailItem.user?.email ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Informasi Item</div>
                        <div class="detail-row">
                            <span class="detail-label">Tipe</span>
                            <span class="detail-val">
                                <span v-if="detailItem.unit"   class="item-type unit-type">Unit</span>
                                <span v-if="detailItem.bundle" class="item-type bundle-type">Bundle</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Nama</span>
                            <span class="detail-val">
                                {{ detailItem.unit?.nama_unit ?? detailItem.bundle?.nama_bundle ?? '—' }}
                            </span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-section-title">Jadwal Peminjaman</div>
                        <div class="detail-row">
                            <span class="detail-label">Mulai Pinjam</span>
                            <span class="detail-val">{{ formatDate(detailItem.tanggal_mulai) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tenggat Rencana</span>
                            <span class="detail-val">{{ formatDate(detailItem.tanggal_selesai_rencana) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Tanggal Kembali</span>
                            <span class="detail-val" :class="{ 'text-red': wasLate(detailItem) }">
                                {{ formatDate(detailItem.tanggal_selesai_aktual) }}
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Durasi Aktual</span>
                            <span class="detail-val">{{ durasi(detailItem) }}</span>
                        </div>
                    </div>

                    <!-- Denda -->
                    <div class="detail-section" v-if="detailItem.fine">
                        <div class="detail-section-title text-red">Denda Keterlambatan</div>
                        <div class="detail-row">
                            <span class="detail-label">Hari Terlambat</span>
                            <span class="detail-val text-red">{{ detailItem.fine.hari_terlambat }} hari</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Jumlah Denda</span>
                            <span class="detail-val text-red fw-600">{{ formatRupiah(detailItem.fine.jumlah_denda) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Status Denda</span>
                            <span class="detail-val">
                                <span :class="['pill-fine', detailItem.fine.status === 'paid' ? 'fine-paid' : 'fine-unpaid']">
                                    {{ detailItem.fine.status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="detail-row">
                            <span class="detail-label">Diproses Oleh</span>
                            <span class="detail-val">{{ detailItem.processedBy?.name ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn-outline" @click="closeDetail">Tutup</button>
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
.empty-row { text-align: center; color: #aaa; padding: 32px !important; }
.text-muted { color: #aaa; }

.code-badge {
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 600;
    padding: 3px 8px; border-radius: 6px;
    font-family: monospace; white-space: nowrap;
}

.member-name { font-weight: 500; color: #1a1a2e; }
.member-email { font-size: 0.75rem; color: #aaa; }

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

.denda-val { font-weight: 600; color: #e53935; font-size: 0.8125rem; }
.no-denda  { color: #aaa; }

.pill-fine {
    font-size: 0.625rem; padding: 2px 8px; border-radius: 8px; font-weight: 600;
    display: inline-block; margin-top: 2px;
}
.fine-paid   { background: #E1F5EE; color: #0F6E56; }
.fine-unpaid { background: #FEF2F2; color: #B91C1C; }

.btn-detail {
    padding: 5px 12px; border-radius: 6px;
    background: #F1EFE8; color: #5F5E5A;
    font-size: 0.75rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-detail:hover { background: #E0DDD5; }

.btn-primary {
    padding: 8px 16px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    border: none; cursor: pointer; white-space: nowrap;
}
.btn-primary:hover { background: #3C3489; }

.header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.btn-pdf {
    padding: 8px 16px; border-radius: 8px;
    background: #1a1a2e; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    text-decoration: none; cursor: pointer; white-space: nowrap;
    display: flex; align-items: center; gap: 6px;
    transition: background 0.15s;
}
.btn-pdf:hover { background: #0d0d1a; }

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
    width: 100%; max-width: 500px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    animation: slideUp 0.2s ease;
    max-height: 90vh; overflow-y: auto;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 0.5px solid #f0ece6; gap: 12px;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1rem; flex-shrink: 0; }
.modal-close:hover { color: #555; }
.modal-body { padding: 1.25rem 1.5rem; }

.detail-section { margin-bottom: 1.25rem; }
.detail-section:last-of-type { margin-bottom: 0; }
.detail-section-title {
    font-size: 0.6875rem; font-weight: 700; color: #aaa;
    text-transform: uppercase; letter-spacing: 0.8px;
    margin-bottom: 8px;
}
.detail-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; font-size: 0.8125rem; padding: 5px 0;
    border-bottom: 0.5px solid #f5f4f0;
}
.detail-row:last-child { border-bottom: none; }
.detail-label { color: #888; flex-shrink: 0; }
.detail-val { color: #1a1a2e; font-weight: 500; text-align: right; }

.text-red { color: #e53935 !important; }
.fw-600   { font-weight: 600; }

.modal-footer {
    display: flex; justify-content: flex-end;
    padding-top: 1rem; margin-top: 1rem;
    border-top: 0.5px solid #f0ece6;
}
</style>
