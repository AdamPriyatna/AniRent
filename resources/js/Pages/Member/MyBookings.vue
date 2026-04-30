<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bookings: Object,
    stats:    Object,
    filters:  Object,
})

const activeTab = ref(props.filters?.tab ?? 'semua')

function setTab(tab) {
    activeTab.value = tab
    router.get(route('bookings.mine'), { tab }, { preserveState: true, replace: true })
}

// Detail modal
const showDetail = ref(false)
const detail     = ref(null)

function openDetail(b) { detail.value = b; showDetail.value = true }
function closeDetail()  { showDetail.value = false; detail.value = null }

// Helpers
function fmt(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

function rupiah(v) {
    if (!v) return '—'
    return 'Rp ' + Number(v).toLocaleString('id-ID')
}

function durasi(b) {
    if (!b.tanggal_mulai || !b.tanggal_selesai_aktual) return null
    const d = Math.ceil((new Date(b.tanggal_selesai_aktual) - new Date(b.tanggal_mulai)) / 86400000)
    return d + ' hari'
}

const statusMap = {
    booked:   { label: 'Menunggu',  cls: 'pill-blue',   icon: '📅' },
    active:   { label: 'Dipinjam',  cls: 'pill-teal',   icon: '✅' },
    late:     { label: 'Terlambat', cls: 'pill-red',    icon: '⚠️' },
    returned: { label: 'Selesai',   cls: 'pill-gray',   icon: '📦' },
}
function si(s) { return statusMap[s] ?? { label: s, cls: 'pill-gray', icon: '•' } }

function sisa(b) {
    if (!['booked','active','late'].includes(b.status)) return null
    const now  = new Date()
    const end  = new Date(b.tanggal_selesai_rencana)
    const diff = Math.ceil((end - now) / 86400000)
    if (diff < 0)  return { text: `${Math.abs(diff)} hari terlambat`, danger: true }
    if (diff === 0) return { text: 'Jatuh tempo hari ini', danger: true }
    return { text: `${diff} hari lagi`, danger: false }
}

const tabs = [
    { key: 'semua',  label: 'Semua',         count: props.stats.total   },
    { key: 'aktif',  label: 'Sedang Dipinjam', count: props.stats.aktif  },
    { key: 'selesai',label: 'Selesai',        count: props.stats.selesai },
]
</script>

<template>
    <Head title="Pinjaman Saya" />

    <div class="page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Pinjaman Saya</h1>
                <p class="page-sub">Riwayat semua peminjaman Anda di AniRent</p>
            </div>
            <Link :href="route('units.index')" class="btn-primary">+ Pinjam Baru</Link>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">{{ $page.props.flash.success }}</div>

        <!-- Stats Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-num">{{ stats.total }}</div>
                <div class="stat-lbl">Total Pinjaman</div>
            </div>
            <div class="stat-card stat-active">
                <div class="stat-num">{{ stats.aktif }}</div>
                <div class="stat-lbl">Sedang Dipinjam</div>
            </div>
            <div class="stat-card stat-done">
                <div class="stat-num">{{ stats.selesai }}</div>
                <div class="stat-lbl">Selesai</div>
            </div>
            <div class="stat-card stat-late" v-if="stats.late > 0">
                <div class="stat-num">{{ stats.late }}</div>
                <div class="stat-lbl">Terlambat ⚠️</div>
            </div>
        </div>

        <!-- Tab Filter -->
        <div class="tab-bar">
            <button
                v-for="t in tabs"
                :key="t.key"
                class="tab-btn"
                :class="{ 'tab-active': activeTab === t.key }"
                @click="setTab(t.key)"
            >
                {{ t.label }}
                <span class="tab-count">{{ t.count }}</span>
            </button>
        </div>

        <!-- Empty State -->
        <div v-if="bookings.data.length === 0" class="empty-state">
            <div class="empty-icon">📭</div>
            <div class="empty-title">Belum ada pinjaman</div>
            <p class="empty-sub">Mulai pinjam unit atau bundle favorit Anda sekarang</p>
            <Link :href="route('units.index')" class="btn-primary">Jelajahi Unit</Link>
        </div>

        <!-- Booking Cards -->
        <div v-else class="booking-list">
            <div
                v-for="b in bookings.data"
                :key="b.id"
                class="booking-card"
                :class="{ 'card-late': b.status === 'late' }"
            >
                <!-- Card Top -->
                <div class="card-top">
                    <div class="card-left">
                        <span class="code-badge">{{ b.kode_booking }}</span>
                        <span :class="['pill', si(b.status).cls]">
                            {{ si(b.status).icon }} {{ si(b.status).label }}
                        </span>
                    </div>
                    <div class="card-date">Dibuat {{ fmt(b.created_at) }}</div>
                </div>

                <!-- Card Body -->
                <div class="card-body">
                    <!-- Item info -->
                    <div class="item-info">
                        <div v-if="b.unit" class="item-name">
                            <span class="item-badge unit-badge">Unit</span>
                            {{ b.unit.nama_unit }}
                        </div>
                        <div v-else-if="b.bundle" class="item-name">
                            <span class="item-badge bundle-badge">Bundle</span>
                            {{ b.bundle.nama_bundle }}
                        </div>
                    </div>

                    <!-- Date row -->
                    <div class="date-row">
                        <div class="date-block">
                            <div class="date-lbl">Tanggal Mulai</div>
                            <div class="date-val">{{ fmt(b.tanggal_mulai) }}</div>
                        </div>
                        <div class="date-arrow">→</div>
                        <div class="date-block">
                            <div class="date-lbl">Tenggat</div>
                            <div class="date-val" :class="{ 'text-red': b.status === 'late' }">
                                {{ fmt(b.tanggal_selesai_rencana) }}
                            </div>
                        </div>
                        <div v-if="b.tanggal_selesai_aktual" class="date-block">
                            <div class="date-lbl">Dikembalikan</div>
                            <div class="date-val">{{ fmt(b.tanggal_selesai_aktual) }}</div>
                        </div>
                    </div>

                    <!-- Sisa hari / Late warning -->
                    <div v-if="sisa(b)" :class="['sisa-row', sisa(b).danger ? 'sisa-danger' : 'sisa-ok']">
                        {{ sisa(b).text }}
                    </div>

                    <!-- Denda (jika ada) -->
                    <div v-if="b.fine" class="denda-row">
                        <span class="denda-label">Denda keterlambatan:</span>
                        <span class="denda-amount">{{ rupiah(b.fine.jumlah_denda) }}</span>
                        <span :class="['denda-status', b.fine.status === 'paid' ? 'status-paid' : 'status-unpaid']">
                            {{ b.fine.status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                        </span>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="card-footer">
                    <span v-if="b.status === 'returned'" class="durasi-txt">
                        Durasi: {{ durasi(b) ?? '—' }}
                    </span>
                    <button class="btn-detail" @click="openDetail(b)">Lihat Detail</button>
                </div>
            </div>
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

    <!-- ═══ DETAIL MODAL ═══ -->
    <Teleport to="body">
        <div v-if="showDetail && detail" class="modal-overlay" @click.self="closeDetail">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Detail Pinjaman</h2>
                        <span class="code-badge">{{ detail.kode_booking }}</span>
                    </div>
                    <button class="modal-close" @click="closeDetail">✕</button>
                </div>
                <div class="modal-body">

                    <div class="d-section">
                        <div class="d-row">
                            <span class="d-lbl">Status</span>
                            <span :class="['pill', si(detail.status).cls]">{{ si(detail.status).icon }} {{ si(detail.status).label }}</span>
                        </div>
                        <div class="d-row">
                            <span class="d-lbl">Item</span>
                            <span class="d-val">{{ detail.unit?.nama_unit ?? detail.bundle?.nama_bundle ?? '—' }}</span>
                        </div>
                        <div class="d-row">
                            <span class="d-lbl">Tipe</span>
                            <span v-if="detail.unit"   class="item-badge unit-badge">Unit</span>
                            <span v-if="detail.bundle" class="item-badge bundle-badge">Bundle</span>
                        </div>
                    </div>

                    <div class="d-section">
                        <div class="d-section-title">Jadwal</div>
                        <div class="d-row">
                            <span class="d-lbl">Mulai Pinjam</span>
                            <span class="d-val">{{ fmt(detail.tanggal_mulai) }}</span>
                        </div>
                        <div class="d-row">
                            <span class="d-lbl">Tenggat Rencana</span>
                            <span class="d-val" :class="{ 'text-red': detail.status === 'late' }">{{ fmt(detail.tanggal_selesai_rencana) }}</span>
                        </div>
                        <div class="d-row" v-if="detail.tanggal_selesai_aktual">
                            <span class="d-lbl">Dikembalikan</span>
                            <span class="d-val">{{ fmt(detail.tanggal_selesai_aktual) }}</span>
                        </div>
                        <div class="d-row" v-if="durasi(detail)">
                            <span class="d-lbl">Durasi Aktual</span>
                            <span class="d-val">{{ durasi(detail) }}</span>
                        </div>
                    </div>

                    <div v-if="detail.fine" class="d-section denda-section">
                        <div class="d-section-title text-red">Denda Keterlambatan</div>
                        <div class="d-row">
                            <span class="d-lbl">Hari Terlambat</span>
                            <span class="d-val text-red">{{ detail.fine.hari_terlambat }} hari</span>
                        </div>
                        <div class="d-row">
                            <span class="d-lbl">Jumlah Denda</span>
                            <span class="d-val text-red fw-600">{{ rupiah(detail.fine.jumlah_denda) }}</span>
                        </div>
                        <div class="d-row">
                            <span class="d-lbl">Status Denda</span>
                            <span :class="['denda-status', detail.fine.status === 'paid' ? 'status-paid' : 'status-unpaid']">
                                {{ detail.fine.status === 'paid' ? '✓ Lunas' : '✗ Belum Lunas' }}
                            </span>
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
.page { max-width: 820px; margin: 0 auto; padding: 2rem 1.5rem; }

.page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
}
.page-title { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-sub   { font-size: 0.875rem; color: #888; }

.alert-success {
    background: #E1F5EE; border: 0.5px solid #5DCAA5;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #085041; margin-bottom: 1.25rem;
}

/* Stats */
.stats-row {
    display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.stat-card {
    flex: 1; min-width: 120px;
    background: #f8f7f4; border: 0.5px solid #e0ddd5;
    border-radius: 12px; padding: 14px 16px; text-align: center;
}
.stat-active { background: #E1F5EE; border-color: #A7F3D0; }
.stat-done   { background: #F0EFFE; border-color: #C4BFFA; }
.stat-late   { background: #FEF2F2; border-color: #FECACA; }
.stat-num  { font-size: 1.75rem; font-weight: 700; color: #1a1a2e; line-height: 1; }
.stat-lbl  { font-size: 0.75rem; color: #888; margin-top: 4px; }

/* Tabs */
.tab-bar { display: flex; gap: 6px; margin-bottom: 1.25rem; flex-wrap: wrap; }
.tab-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 16px; border-radius: 20px;
    border: 1.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #888; cursor: pointer;
    transition: all 0.15s;
}
.tab-btn:hover { border-color: #534AB7; color: #534AB7; }
.tab-active { border-color: #534AB7 !important; background: #534AB7 !important; color: #fff !important; }
.tab-count {
    background: rgba(255,255,255,0.25); color: inherit;
    font-size: 0.6875rem; font-weight: 600;
    padding: 1px 6px; border-radius: 8px;
}
.tab-active .tab-count { background: rgba(255,255,255,0.3); }

/* Empty State */
.empty-state {
    text-align: center; padding: 3rem 1rem;
    background: #fff; border: 0.5px solid #e0ddd5;
    border-radius: 14px; margin-top: 1rem;
}
.empty-icon  { font-size: 48px; margin-bottom: 1rem; }
.empty-title { font-size: 1.1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
.empty-sub   { font-size: 0.875rem; color: #aaa; margin-bottom: 1.25rem; }

/* Booking Cards */
.booking-list { display: flex; flex-direction: column; gap: 14px; }

.booking-card {
    background: #fff; border: 0.5px solid #e0ddd5;
    border-radius: 14px; overflow: hidden;
    transition: box-shadow 0.15s;
}
.booking-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.card-late { border-color: #FECACA !important; background: #FFFAFA; }

.card-top {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 16px; background: #f8f7f4;
    border-bottom: 0.5px solid #f0ece6; gap: 10px; flex-wrap: wrap;
}
.card-left { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.card-date { font-size: 0.75rem; color: #aaa; }

.card-body { padding: 14px 16px; }
.card-footer {
    padding: 10px 16px; border-top: 0.5px solid #f0ece6;
    display: flex; justify-content: space-between; align-items: center;
    background: #fafaf8;
}

/* Item name */
.item-info { margin-bottom: 12px; }
.item-name { display: flex; align-items: center; gap: 8px; font-size: 0.9375rem; font-weight: 600; color: #1a1a2e; }
.item-badge {
    font-size: 0.625rem; font-weight: 700; padding: 2px 7px;
    border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;
}
.unit-badge   { background: #EEEDFE; color: #534AB7; }
.bundle-badge { background: #FEF3C7; color: #92400E; }

/* Dates */
.date-row {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    margin-bottom: 10px;
}
.date-block { }
.date-lbl { font-size: 0.6875rem; color: #aaa; margin-bottom: 2px; }
.date-val { font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; }
.date-arrow { color: #ccc; font-size: 1rem; }
.text-red { color: #e53935 !important; }

/* Sisa hari */
.sisa-row {
    display: inline-block; font-size: 0.75rem; font-weight: 600;
    padding: 3px 10px; border-radius: 8px; margin-bottom: 8px;
}
.sisa-ok     { background: #E1F5EE; color: #065F46; }
.sisa-danger { background: #FEF2F2; color: #B91C1C; }

/* Denda */
.denda-row {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    background: #FEF2F2; border-radius: 8px; padding: 6px 10px;
    font-size: 0.8125rem; margin-top: 4px;
}
.denda-label  { color: #7F1D1D; }
.denda-amount { font-weight: 700; color: #B91C1C; }
.denda-status { font-size: 0.6875rem; font-weight: 600; padding: 2px 8px; border-radius: 8px; }
.status-paid    { background: #E1F5EE; color: #0F6E56; }
.status-unpaid  { background: #FEE2E2; color: #B91C1C; }

.durasi-txt { font-size: 0.8125rem; color: #aaa; }

/* Pills */
.pill { font-size: 0.6875rem; padding: 3px 10px; border-radius: 10px; font-weight: 500; }
.pill-blue  { background: #EFF6FF; color: #1D4ED8; }
.pill-teal  { background: #E1F5EE; color: #0F6E56; }
.pill-red   { background: #FEF2F2; color: #B91C1C; }
.pill-gray  { background: #F1EFE8; color: #5F5E5A; }

/* Code badge */
.code-badge {
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 600;
    padding: 3px 8px; border-radius: 6px;
    font-family: monospace; white-space: nowrap;
}

/* Buttons */
.btn-primary {
    padding: 8px 18px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    text-decoration: none; border: none; cursor: pointer;
}
.btn-primary:hover { background: #3C3489; }
.btn-outline {
    padding: 8px 18px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.8125rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
}
.btn-outline:hover { background: #EEEDFE; }
.btn-detail {
    padding: 5px 14px; border-radius: 7px;
    background: #F1EFE8; color: #534AB7;
    font-size: 0.75rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-detail:hover { background: #EEEDFE; }

/* Pagination */
.pagination { display: flex; gap: 4px; margin-top: 1.5rem; flex-wrap: wrap; }
.page-btn {
    padding: 6px 11px; border-radius: 6px;
    border: 0.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #534AB7; text-decoration: none; cursor: pointer;
}
.page-btn.active  { background: #534AB7; color: #fff; border-color: #534AB7; }
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
    width: 100%; max-width: 460px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    animation: slideUp 0.2s ease; max-height: 90vh; overflow-y: auto;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 1.25rem 1.5rem; border-bottom: 0.5px solid #f0ece6; gap: 10px;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1rem; flex-shrink: 0; }
.modal-close:hover { color: #555; }
.modal-body { padding: 1.25rem 1.5rem; }

.d-section { margin-bottom: 1rem; }
.d-section-title {
    font-size: 0.6875rem; font-weight: 700; color: #aaa;
    text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px;
}
.d-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; font-size: 0.8125rem; padding: 5px 0;
    border-bottom: 0.5px solid #f5f4f0;
}
.d-row:last-child { border-bottom: none; }
.d-lbl { color: #888; flex-shrink: 0; }
.d-val { color: #1a1a2e; font-weight: 500; text-align: right; }

.denda-section { background: #FEF2F2; border-radius: 10px; padding: 10px 12px; }
.fw-600 { font-weight: 600; }

.modal-footer {
    display: flex; justify-content: flex-end;
    padding-top: 1rem; margin-top: 1rem; border-top: 0.5px solid #f0ece6;
}
</style>
