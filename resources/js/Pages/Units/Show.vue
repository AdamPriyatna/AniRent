<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    unit: Object,
})

// ─── Booking form ─────────────────────────────────────────────────────────────
const showBookingModal = ref(false)

const today    = new Date().toISOString().split('T')[0]
const maxDate  = (() => {
    const d = new Date(); d.setDate(d.getDate() + 5)
    return d.toISOString().split('T')[0]
})()

const form = useForm({
    unit_id:                props.unit.id,
    bundle_id:              null,
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

const canBook = computed(() =>
    props.unit.status === 'tersedia' &&
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
    dipinjam: { label: 'Dipinjam',  bg: '#FEF3C7', color: '#92400E' },
    rusak:    { label: 'Rusak',     bg: '#FEF2F2', color: '#B91C1C' },
}
function si(s) { return statusMap[s] ?? { label: s, bg: '#F1EFE8', color: '#888' } }
</script>

<template>
    <Head :title="unit.nama_unit" />

    <div class="page">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <Link :href="route('units.index')" class="bc-link">← Katalog Unit</Link>
            <span class="bc-sep">/</span>
            <span class="bc-current">{{ unit.nama_unit }}</span>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">✓ {{ $page.props.flash.success }}</div>
        <div v-if="$page.props.flash?.error"   class="alert-error">✗ {{ $page.props.flash.error }}</div>

        <!-- Content -->
        <div class="detail-layout">

            <!-- LEFT: Foto -->
            <div class="foto-col">
                <div class="foto-wrap">
                    <img
                        v-if="unit.foto"
                        :src="`/storage/${unit.foto}`"
                        :alt="unit.nama_unit"
                        class="foto-img"
                    />
                    <div v-else class="foto-placeholder">
                        <span class="placeholder-icon">🎭</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="status-badge-wrap">
                    <span
                        class="status-pill"
                        :style="{ background: si(unit.status).bg, color: si(unit.status).color }"
                    >
                        {{ si(unit.status).label }}
                    </span>
                </div>
            </div>

            <!-- RIGHT: Info + Booking -->
            <div class="info-col">

                <!-- Kode + Nama -->
                <div class="unit-kode">{{ unit.kode_unit }}</div>
                <h1 class="unit-nama">{{ unit.nama_unit }}</h1>

                <!-- Kategori -->
                <div class="kateg-row" v-if="unit.categories?.length">
                    <span
                        v-for="c in unit.categories"
                        :key="c.id"
                        class="kateg-pill"
                    >{{ c.nama_kategori }}</span>
                </div>

                <!-- Kondisi & Harga -->
                <div class="info-grid mb-4">
                    <div v-if="unit.kondisi" class="kondisi-row">
                        <span class="kondisi-label">Kondisi:</span>
                        <span class="kondisi-val">{{ unit.kondisi }}</span>
                    </div>
                    <div class="kondisi-row mt-2">
                        <span class="kondisi-label">Harga Sewa:</span>
                        <span class="kondisi-val font-weight-bold text-primary">Rp {{ (unit.harga_sewa ?? 0).toLocaleString('id-ID') }} / hari</span>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div v-if="unit.deskripsi" class="deskripsi">
                    {{ unit.deskripsi }}
                </div>

                <div class="divider"></div>

                <!-- Booking Rules Info -->
                <div class="rules-box">
                    <div class="rules-title">📋 Ketentuan Peminjaman</div>
                    <ul class="rules-list">
                        <li>Durasi maksimal <strong>5 hari</strong></li>
                        <li>Maksimal <strong>2 item aktif</strong> per anggota</li>
                        <li>Pengembalian diproses oleh Admin</li>
                        <li>Denda berlaku untuk keterlambatan</li>
                    </ul>
                </div>

                <!-- CTA Buttons -->
                <div class="cta-row">
                    <button
                        v-if="unit.status === 'tersedia'"
                        class="btn-book"
                        @click="openModal"
                    >
                        📅 Pinjam Unit Ini
                    </button>
                    <span v-else class="btn-unavail">
                        {{ unit.status === 'dipinjam' ? '⏳ Sedang Dipinjam' : '🚫 Unit Tidak Tersedia' }}
                    </span>

                    <Link :href="route('units.index')" class="btn-back-sm">Kembali</Link>
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
                        <h2 class="modal-title">Form Peminjaman</h2>
                        <p class="modal-sub">{{ unit.nama_unit }}</p>
                    </div>
                    <button class="modal-close" @click="showBookingModal = false">✕</button>
                </div>
                <div class="modal-body">

                    <!-- Info -->
                    <div class="booking-info-card">
                        <div class="bi-row">
                            <span class="bi-label">Unit</span>
                            <span class="bi-val">{{ unit.nama_unit }}</span>
                        </div>
                        <div class="bi-row">
                            <span class="bi-label">Kode</span>
                            <span class="bi-val code-tag">{{ unit.kode_unit }}</span>
                        </div>
                        <div class="bi-row mt-1">
                            <span class="bi-label">Harga/Hari</span>
                            <span class="bi-val text-primary font-weight-bold">Rp {{ (unit.harga_sewa ?? 0).toLocaleString('id-ID') }}</span>
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
                        {{ durasiError ?? `⏱ Durasi: ${durasi} hari` }}
                    </div>

                    <!-- Summary -->
                    <div v-if="durasi >= 1 && !durasiError" class="confirm-box">
                        <div class="cb-row">
                            <span>Mulai</span>
                            <strong>{{ fmt(form.tanggal_mulai) }}</strong>
                        </div>
                        <div class="cb-row">
                            <span>Selesai</span>
                            <strong>{{ fmt(form.tanggal_selesai_rencana) }}</strong>
                        </div>
                        <div class="cb-row">
                            <span>Durasi</span>
                            <strong>{{ durasi }} hari</strong>
                        </div>
                        <div class="cb-divider"></div>
                        <div class="cb-row cb-total">
                            <span>Total Biaya</span>
                            <strong class="text-primary">Rp {{ (durasi * (unit.harga_per_hari || 0)).toLocaleString('id-ID') }}</strong>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn-outline" @click="showBookingModal = false">Batal</button>
                        <button
                            class="btn-confirm"
                            :disabled="!canBook || form.processing"
                            @click="submitBooking"
                        >
                            {{ form.processing ? 'Memproses...' : '✓ Konfirmasi Pinjam' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.page { max-width: 960px; margin: 0 auto; padding: 2rem 1.5rem; }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 1.25rem; font-size: 0.8125rem; }
.bc-link { color: #534AB7; text-decoration: none; }
.bc-link:hover { text-decoration: underline; }
.bc-sep { color: #ccc; }
.bc-current { color: #888; }

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

/* Layout */
.detail-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 2rem; align-items: start;
}
@media (max-width: 700px) { .detail-layout { grid-template-columns: 1fr; } }

/* Foto */
.foto-col { position: sticky; top: 1rem; }
.foto-wrap { border-radius: 16px; overflow: hidden; background: #f5f4f0; }
.foto-img { width: 100%; height: 300px; object-fit: cover; display: block; }
.foto-placeholder {
    height: 300px; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #F0EFFE, #dddaf8);
}
.placeholder-icon { font-size: 64px; }
.status-badge-wrap { margin-top: 10px; }
.status-pill {
    display: inline-block; padding: 5px 14px; border-radius: 10px;
    font-size: 0.8125rem; font-weight: 600;
}

/* Info */
.info-col { }
.unit-kode {
    display: inline-block; background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 700; font-family: monospace;
    padding: 3px 10px; border-radius: 6px; margin-bottom: 8px;
}
.unit-nama { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; line-height: 1.2; }

.kateg-row { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
.kateg-pill {
    font-size: 0.75rem; padding: 3px 10px; border-radius: 8px;
    background: #f0ece6; color: #5F5E5A; font-weight: 500;
}

.kondisi-row { font-size: 0.8125rem; color: #888; margin-bottom: 10px; }
.kondisi-label { margin-right: 4px; }
.kondisi-val { color: #1a1a2e; font-weight: 500; }

.deskripsi {
    font-size: 0.9rem; color: #555; line-height: 1.65;
    margin-bottom: 1.25rem;
}

.divider { border: none; border-top: 0.5px solid #f0ece6; margin: 1.25rem 0; }

/* Rules */
.rules-box {
    background: #F9F8FF; border: 0.5px solid #dddaf8;
    border-radius: 10px; padding: 14px 16px; margin-bottom: 14px;
}
.rules-title { font-size: 0.875rem; font-weight: 600; color: #534AB7; margin-bottom: 8px; }
.rules-list { padding-left: 18px; margin: 0; }
.rules-list li { font-size: 0.8125rem; color: #555; margin-bottom: 4px; line-height: 1.5; }

.quota-warn {
    background: #FEF3C7; border: 0.5px solid #FDE68A;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #92400E; margin-bottom: 1rem;
}

/* CTA */
.cta-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 1rem; }
.btn-book {
    padding: 11px 28px; border-radius: 10px;
    background: #534AB7; color: #fff;
    font-size: 0.9375rem; font-weight: 600;
    border: none; cursor: pointer; transition: background 0.15s;
}
.btn-book:hover:not(:disabled) { background: #3C3489; }
.btn-book:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-unavail {
    padding: 11px 20px; border-radius: 10px;
    background: #f0ece6; color: #888;
    font-size: 0.9375rem; font-weight: 600;
}
.btn-back-sm {
    padding: 9px 18px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.875rem; font-weight: 500;
    text-decoration: none;
}
.btn-back-sm:hover { background: #EEEDFE; }

/* ─── MODAL ────────────────────────────────────────────────── */
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
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 1.25rem 1.5rem; border-bottom: 0.5px solid #f0ece6; gap: 10px;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.modal-sub   { font-size: 0.8125rem; color: #888; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1rem; flex-shrink: 0; }
.modal-close:hover { color: #555; }
.modal-body { padding: 1.25rem 1.5rem; }

/* Booking info card */
.booking-info-card {
    background: #F9F8FF; border: 0.5px solid #dddaf8;
    border-radius: 10px; padding: 12px 14px; margin-bottom: 1rem;
}
.bi-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: 0.8125rem; padding: 4px 0;
    border-bottom: 0.5px solid #f0ece6;
}
.bi-row:last-child { border-bottom: none; }
.bi-label { color: #888; }
.bi-val   { color: #1a1a2e; font-weight: 500; }
.code-tag {
    background: #EEEDFE; color: #534AB7;
    font-family: monospace; font-size: 0.75rem;
    padding: 1px 7px; border-radius: 4px;
}

/* Form */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media (max-width: 420px) { .two-col { grid-template-columns: 1fr; } }
.mt-1 { margin-top: 4px; }
.form-group { margin-bottom: 10px; }
.label { display: block; font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; margin-bottom: 5px; }
.req   { color: #D85A30; }
.input {
    width: 100%; padding: 8px 11px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none; box-sizing: border-box;
    transition: border-color 0.15s;
}
.input:focus { border-color: #534AB7; }
.input-error { border-color: #D85A30 !important; }
.error-msg { font-size: 0.75rem; color: #D85A30; margin-top: 3px; }

.durasi-row {
    font-size: 0.8125rem; font-weight: 600;
    padding: 6px 12px; border-radius: 8px; margin-bottom: 10px;
}
.durasi-ok  { background: #E1F5EE; color: #0F6E56; }
.durasi-err { background: #FEF2F2; color: #B91C1C; }

.confirm-box {
    background: #F9F8FF; border: 0.5px solid #dddaf8;
    border-radius: 10px; padding: 12px 14px; margin-bottom: 1rem;
}
.cb-row {
    display: flex; justify-content: space-between;
    font-size: 0.8125rem; padding: 4px 0; color: #555;
}
.cb-divider { height: 1px; background: #e0ddd5; margin: 8px 0; }
.cb-total { font-size: 0.875rem; color: #1a1a2e; }

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 1rem; border-top: 0.5px solid #f0ece6;
}
.btn-outline {
    padding: 8px 18px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.875rem; font-weight: 500; cursor: pointer;
}
.btn-outline:hover { background: #EEEDFE; }
.btn-confirm {
    padding: 9px 22px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.875rem; font-weight: 600;
    border: none; cursor: pointer;
}
.btn-confirm:hover:not(:disabled) { background: #3C3489; }
.btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
