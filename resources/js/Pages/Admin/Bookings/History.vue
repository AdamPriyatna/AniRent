<script setup>
import { ref, computed } from 'vue'
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
.page-title { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.page-sub { font-size: 0.875rem; color: rgba(255,255,255,0.7); }

.alert-success {
    background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #86efac; margin-bottom: 1rem;
    backdrop-filter: blur(8px);
}

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
.member-email { font-size: 0.75rem; color: rgba(255,255,255,0.5); }

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

.denda-val { font-weight: 600; color: #fca5a5; font-size: 0.8125rem; }
.no-denda  { color: rgba(255,255,255,0.5); }

.pill-fine {
    font-size: 0.625rem; padding: 2px 8px; border-radius: 8px; font-weight: 600;
    display: inline-block; margin-top: 2px; border: 1px solid transparent;
}
.fine-paid   { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.fine-unpaid { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }

.btn-detail {
    padding: 6px 12px; border-radius: 6px;
    background: rgba(255,255,255,0.1); color: #fff;
    font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.2); cursor: pointer; transition: 0.2s;
}
.btn-detail:hover { background: rgba(255,255,255,0.2); }

.btn-primary {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.8125rem; font-weight: 600;
    border: none; cursor: pointer; white-space: nowrap; box-shadow: 0 0 15px rgba(168,85,247,0.4); transition: 0.3s;
}
.btn-primary:hover { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }

.header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.btn-pdf {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(255,255,255,0.1); color: #fff;
    font-size: 0.8125rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.3);
    text-decoration: none; cursor: pointer; white-space: nowrap;
    display: flex; align-items: center; gap: 6px; backdrop-filter: blur(8px);
    transition: 0.2s;
}
.btn-pdf:hover { background: rgba(255,255,255,0.2); }

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
    border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 500px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6); animation: slideUp 0.2s ease; color: white;
    max-height: 90vh; overflow-y: auto;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); gap: 12px;
}
.modal-title { font-size: 1.2rem; font-weight: 600; color: #fff; margin-bottom: 6px; }
.modal-close { background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.5); font-size: 1.2rem; flex-shrink: 0; transition: 0.2s; }
.modal-close:hover { color: #fff; }
.modal-body { padding: 1.25rem 1.5rem; }

.detail-section { margin-bottom: 1.25rem; }
.detail-section:last-of-type { margin-bottom: 0; }
.detail-section-title {
    font-size: 0.6875rem; font-weight: 700; color: rgba(255,255,255,0.5);
    text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px;
}
.detail-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; font-size: 0.8125rem; padding: 5px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.detail-row:last-child { border-bottom: none; }
.detail-label { color: rgba(255,255,255,0.6); flex-shrink: 0; }
.detail-val { color: #fff; font-weight: 500; text-align: right; }

.text-red { color: #fca5a5 !important; }
.fw-600   { font-weight: 600; }

.modal-footer {
    display: flex; justify-content: flex-end;
    padding-top: 1rem; margin-top: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}
</style>
