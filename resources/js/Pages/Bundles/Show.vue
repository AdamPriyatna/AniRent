<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    bundle: Object,
})

// ─── Booking form ─────────────────────────────────────────────────────────────
const showBookingModal = ref(false)

const today    = new Date().toISOString().split('T')[0]
const maxDate  = (() => {
    const d = new Date(); d.setDate(d.getDate() + 5)
    return d.toISOString().split('T')[0]
})()

const form = useForm({
    unit_id:                null,
    bundle_id:              props.bundle.id,
    tanggal_mulai:          today,
    tanggal_selesai_rencana: '',
})

// Hitung durasi
const durasi = computed(() => {
    if (!form.tanggal_mulai || !form.tanggal_selesai_rencana) return 0
    const start = new Date(form.tanggal_mulai)
    const end   = new Date(form.tanggal_selesai_rencana)
    return Math.ceil((end - start) / 86400000)
})

// Validasi durasi max 5 hari
const durasiError = computed(() => {
    if (durasi.value > 5) return 'Durasi maksimal 5 hari.'
    if (durasi.value < 1) return 'Tanggal selesai harus setelah tanggal mulai.'
    return null
})

// Cek apakah bundle bisa dibook (cek status bundle & status tiap unit di dalamnya)
const isBundleAvailable = computed(() => {
    if (props.bundle.status !== 'tersedia') return false
    // Jika ada satu saja unit yang tidak tersedia, bundle jadi tidak tersedia
    return props.bundle.units.every(u => u.status === 'tersedia')
})

const canBook = computed(() =>
    isBundleAvailable.value &&
    durasi.value >= 1 &&
    durasi.value <= 5 &&
    !durasiError.value
)

function submitBooking() {
    if (!canBook.value) return
    form.post(route('bookings.store'), {
        onSuccess: () => { showBookingModal.value = false },
    })
}

function openModal() {
    form.tanggal_mulai = today
    form.tanggal_selesai_rencana = ''
    form.clearErrors()
    showBookingModal.value = true
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
function fmt(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

const statusMap = {
    tersedia: { label: 'Tersedia',  bg: '#E1F5EE', color: '#0F6E56' },
    disewa:   { label: 'Sedang Disewa', bg: '#FEF3C7', color: '#92400E' },
    rusak:    { label: 'Rusak',     bg: '#FEF2F2', color: '#B91C1C' },
}
function si(s) { return statusMap[s] ?? { label: s, bg: '#F1EFE8', color: '#888' } }
</script>

<template>
    <Head :title="bundle.nama_bundle" />

    <div class="page">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <Link :href="route('bundles.index')" class="bc-link">← Paket Bundle</Link>
            <span class="bc-sep">/</span>
            <span class="bc-current">{{ bundle.nama_bundle }}</span>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">✓ {{ $page.props.flash.success }}</div>
        <div v-if="$page.props.flash?.error"   class="alert-error">✗ {{ $page.props.flash.error }}</div>

        <!-- Content -->
        <div class="detail-layout">

            <!-- LEFT: Foto -->
            <div class="foto-col">
                <div class="foto-wrap">
                    <v-img
                        v-if="bundle.foto"
                        :src="`/storage/${bundle.foto}`"
                        :alt="bundle.nama_bundle"
                        height="340"
                        cover
                        class="foto-img"
                    />
                    <div v-else class="foto-placeholder">
                        <span class="placeholder-icon">📦</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="status-badge-wrap">
                    <span
                        class="status-pill"
                        :style="{ background: si(bundle.status).bg, color: si(bundle.status).color }"
                    >
                        {{ isBundleAvailable ? 'Siap Disewa' : si(bundle.status).label }}
                    </span>
                    <p v-if="!isBundleAvailable && bundle.status === 'tersedia'" class="text-caption text-error mt-2">
                        Beberapa unit dalam paket ini sedang dipinjam.
                    </p>
                </div>
            </div>

            <!-- RIGHT: Info + Booking -->
            <div class="info-col">
                <div class="tag-label">PAKET BUNDLE HEMAT</div>
                <h1 class="unit-nama">{{ bundle.nama_bundle }}</h1>

                <!-- Harga -->
                <div class="price-section mb-5">
                    <div class="price-val">Rp {{ bundle.harga_per_hari.toLocaleString('id-ID') }}</div>
                    <div class="price-unit">per hari (Satu Paket)</div>
                </div>

                <!-- Deskripsi -->
                <div class="deskripsi">
                    {{ bundle.deskripsi }}
                </div>

                <div class="divider"></div>

                <!-- Units Included -->
                <div class="units-section mb-6">
                    <h3 class="section-title">Isi Paket:</h3>
                    <div class="unit-list">
                        <div v-for="u in bundle.units" :key="u.id" class="unit-item">
                            <v-icon icon="mdi-check-circle" color="success" size="18" class="mr-2" />
                            <div class="unit-info">
                                <span class="unit-name">{{ u.nama_unit }}</span>
                                <span class="unit-status-chip" :class="u.status">
                                    {{ u.status === 'tersedia' ? 'Tersedia' : 'Dipinjam' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Rules Info -->
                <div class="rules-box">
                    <div class="rules-title">📋 Ketentuan Bundle</div>
                    <ul class="rules-list">
                        <li>Mendapatkan seluruh unit di atas sekaligus.</li>
                        <li>Durasi maksimal <strong>5 hari</strong>.</li>
                        <li>Harga sudah termasuk diskon paket.</li>
                    </ul>
                </div>

                <!-- CTA Buttons -->
                <div class="cta-row">
                    <button
                        v-if="isBundleAvailable"
                        class="btn-book"
                        @click="openModal"
                    >
                        📅 Sewa Paket Ini
                    </button>
                    <span v-else class="btn-unavail">
                        🚫 Paket Tidak Tersedia Saat Ini
                    </span>

                    <Link :href="route('bundles.index')" class="btn-back-sm">Kembali</Link>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ BOOKING MODAL ═══ -->
    <Teleport to="body">
        <div v-if="showBookingModal" class="modal-overlay" @click.self="showBookingModal = false">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Sewa Paket Bundle</h2>
                        <p class="modal-sub">{{ bundle.nama_bundle }}</p>
                    </div>
                    <button class="modal-close" @click="showBookingModal = false">✕</button>
                </div>
                <div class="modal-body">

                    <!-- Info -->
                    <div class="booking-info-card">
                        <div class="bi-row">
                            <span class="bi-label">Paket</span>
                            <span class="bi-val">{{ bundle.nama_bundle }}</span>
                        </div>
                        <div class="bi-row mt-1">
                            <span class="bi-label">Harga Paket</span>
                            <span class="bi-val text-primary font-weight-bold">Rp {{ bundle.harga_per_hari.toLocaleString('id-ID') }} / hari</span>
                        </div>
                    </div>

                    <!-- Date fields -->
                    <div class="two-col mt-1">
                        <div class="form-group">
                            <label class="label">Tanggal Mulai <span class="req">*</span></label>
                            <input
                                v-model="form.tanggal_mulai"
                                type="date"
                                class="input"
                                :class="{ 'input-error': form.errors.tanggal_mulai }"
                                :min="today"
                            />
                            <p v-if="form.errors.tanggal_mulai" class="error-msg">{{ form.errors.tanggal_mulai }}</p>
                        </div>

                        <div class="form-group">
                            <label class="label">Tanggal Selesai <span class="req">*</span></label>
                            <input
                                v-model="form.tanggal_selesai_rencana"
                                type="date"
                                class="input"
                                :class="{ 'input-error': form.errors.tanggal_selesai_rencana || durasiError }"
                                :min="form.tanggal_mulai"
                                :max="maxDate"
                            />
                            <p v-if="form.errors.tanggal_selesai_rencana" class="error-msg">{{ form.errors.tanggal_selesai_rencana }}</p>
                        </div>
                    </div>

                    <div v-if="form.errors.item" class="quota-warn mt-2">
                        {{ form.errors.item }}
                    </div>

                    <!-- Durasi indicator -->
                    <div v-if="durasi > 0" class="durasi-row" :class="durasiError ? 'durasi-err' : 'durasi-ok'">
                        {{ durasiError ?? `⏱ Durasi Sewa: ${durasi} hari` }}
                    </div>

                    <!-- Summary -->
                    <div v-if="durasi >= 1 && !durasiError" class="confirm-box">
                        <div class="cb-row">
                            <span>Sewa Dari</span>
                            <strong>{{ fmt(form.tanggal_mulai) }}</strong>
                        </div>
                        <div class="cb-row">
                            <span>Hingga</span>
                            <strong>{{ fmt(form.tanggal_selesai_rencana) }}</strong>
                        </div>
                        <div class="cb-row">
                            <span>Lama Sewa</span>
                            <strong>{{ durasi }} hari</strong>
                        </div>
                        <div class="cb-divider"></div>
                        <div class="cb-row cb-total">
                            <span>Total Biaya Paket</span>
                            <strong class="text-primary">Rp {{ (durasi * (bundle.harga_per_hari || 0)).toLocaleString('id-ID') }}</strong>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn-outline" @click="showBookingModal = false">Batal</button>
                        <button
                            class="btn-confirm"
                            :disabled="!canBook || form.processing"
                            @click="submitBooking"
                        >
                            {{ form.processing ? 'Memproses...' : '✓ Konfirmasi Sewa Paket' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; padding: 2rem 1.5rem; }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 1.5rem; font-size: 0.8125rem; }
.bc-link { color: #534AB7; text-decoration: none; }
.bc-sep { color: #ccc; }
.bc-current { color: #888; }

.alert-success { background: #E1F5EE; border: 0.5px solid #5DCAA5; border-radius: 8px; padding: 10px 14px; font-size: 0.8125rem; color: #085041; margin-bottom: 1rem; }
.alert-error { background: #FAECE7; border: 0.5px solid #f59e80; border-radius: 8px; padding: 10px 14px; font-size: 0.8125rem; color: #993C1D; margin-bottom: 1rem; }

/* Layout */
.detail-layout { display: grid; grid-template-columns: 380px 1fr; gap: 2.5rem; align-items: start; }
@media (max-width: 800px) { .detail-layout { grid-template-columns: 1fr; } }

/* Foto */
.foto-col { position: sticky; top: 1.5rem; }
.foto-wrap { border-radius: 20px; overflow: hidden; background: #f5f4f0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
.foto-img { width: 100%; display: block; }
.foto-placeholder { height: 340px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #F0EFFE, #dddaf8); }
.placeholder-icon { font-size: 80px; }
.status-badge-wrap { margin-top: 15px; }
.status-pill { display: inline-block; padding: 6px 16px; border-radius: 12px; font-size: 0.875rem; font-weight: 700; }

/* Info */
.tag-label { color: #534AB7; font-weight: 800; font-size: 0.75rem; letter-spacing: 1px; margin-bottom: 8px; }
.unit-nama { font-size: 2rem; font-weight: 800; color: #1a1a2e; margin-bottom: 12px; line-height: 1.2; }

.price-section { display: flex; flex-direction: column; }
.price-val { font-size: 1.75rem; font-weight: 800; color: #534AB7; }
.price-unit { font-size: 0.875rem; color: #888; }

.deskripsi { font-size: 0.95rem; color: #555; line-height: 1.7; margin-bottom: 1.5rem; }
.divider { border: none; border-top: 0.5px solid #f0ece6; margin: 1.5rem 0; }

/* Units */
.section-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; }
.unit-list { display: flex; flex-direction: column; gap: 10px; }
.unit-item { display: flex; align-items: center; padding: 12px 16px; background: #fff; border: 0.5px solid #e0ddd5; border-radius: 12px; }
.unit-info { flex: 1; display: flex; justify-content: space-between; align-items: center; }
.unit-name { font-size: 0.9375rem; font-weight: 600; color: #333; }
.unit-status-chip { font-size: 0.6875rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; }
.unit-status-chip.tersedia { background: #E1F5EE; color: #0F6E56; }
.unit-status-chip.dipinjam { background: #FEF2F2; color: #B91C1C; }

/* Rules */
.rules-box { background: #F9F8FF; border: 0.5px solid #dddaf8; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; }
.rules-title { font-size: 0.9375rem; font-weight: 700; color: #534AB7; margin-bottom: 8px; }
.rules-list { padding-left: 20px; margin: 0; }
.rules-list li { font-size: 0.875rem; color: #555; margin-bottom: 6px; }

/* CTA */
.cta-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 1.5rem; }
.btn-book { padding: 14px 36px; border-radius: 12px; background: #534AB7; color: #fff; font-size: 1rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 8px 16px rgba(83, 74, 183, 0.2); }
.btn-book:hover { background: #3C3489; transform: translateY(-2px); box-shadow: 0 12px 20px rgba(83, 74, 183, 0.25); }
.btn-unavail { padding: 14px 24px; border-radius: 12px; background: #f0ece6; color: #888; font-size: 1rem; font-weight: 700; }
.btn-back-sm { padding: 12px 24px; border-radius: 12px; background: transparent; color: #534AB7; border: 1.5px solid #534AB7; font-size: 0.9375rem; font-weight: 600; text-decoration: none; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 1rem; animation: fadeIn 0.15s ease; }
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }
.modal { background: #fff; border-radius: 20px; width: 100%; max-width: 500px; box-shadow: 0 30px 70px rgba(0,0,0,0.25); animation: slideUp 0.25s cubic-bezier(0.2, 0.8, 0.2, 1); overflow: hidden; }
@keyframes slideUp { from { opacity: 0; transform: translateY(30px) } to { opacity: 1; transform: translateY(0) } }
.modal-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 1.5rem 1.75rem; border-bottom: 0.5px solid #f0ece6; }
.modal-title { font-size: 1.15rem; font-weight: 800; color: #1a1a2e; }
.modal-sub { font-size: 0.875rem; color: #888; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1.25rem; }
.modal-body { padding: 1.5rem 1.75rem; }

.booking-info-card { background: #F9F8FF; border: 0.5px solid #dddaf8; border-radius: 12px; padding: 14px 16px; margin-bottom: 1.25rem; }
.bi-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; padding: 5px 0; }
.bi-label { color: #888; }
.bi-val { color: #1a1a2e; font-weight: 600; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-group { margin-bottom: 12px; }
.label { display: block; font-size: 0.875rem; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
.req { color: #D85A30; }
.input { width: 100%; padding: 10px 14px; border: 1.5px solid #e0ddd5; border-radius: 10px; font-size: 0.9375rem; outline: none; transition: border-color 0.15s; }
.input:focus { border-color: #534AB7; }
.input-error { border-color: #D85A30 !important; }
.error-msg { font-size: 0.75rem; color: #D85A30; margin-top: 4px; }

.durasi-row { font-size: 0.875rem; font-weight: 700; padding: 8px 14px; border-radius: 10px; margin-bottom: 12px; text-align: center; }
.durasi-ok { background: #E1F5EE; color: #0F6E56; }
.durasi-err { background: #FEF2F2; color: #B91C1C; }

.confirm-box { background: #F9F8FF; border: 1px dashed #534AB7; border-radius: 12px; padding: 14px 16px; margin-bottom: 1.5rem; }
.cb-row { display: flex; justify-content: space-between; font-size: 0.875rem; padding: 4px 0; color: #555; }
.cb-divider { height: 1px; background: #dddaf8; margin: 8px 0; }
.cb-total { font-size: 1rem; color: #1a1a2e; }

.quota-warn { background: #FEF3C7; border-radius: 10px; padding: 12px 16px; font-size: 0.875rem; color: #92400E; margin-bottom: 1.25rem; text-align: center; }

.modal-footer { display: flex; justify-content: flex-end; gap: 12px; padding-top: 1.25rem; border-top: 0.5px solid #f0ece6; }
.btn-confirm { padding: 11px 26px; border-radius: 10px; background: #534AB7; color: #fff; font-size: 0.9375rem; font-weight: 700; border: none; cursor: pointer; }
.btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
