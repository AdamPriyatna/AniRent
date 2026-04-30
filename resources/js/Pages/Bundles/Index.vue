<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bundles: Object,
})

const search = ref('')

// ─── Direct Booking Modal ─────────────────────────────────────────────────────
const showModal = ref(false)
const selectedBundle = ref(null)

const today    = new Date().toISOString().split('T')[0]
const maxDate  = (() => {
    const d = new Date(); d.setDate(d.getDate() + 5)
    return d.toISOString().split('T')[0]
})()

const form = useForm({
    unit_id:                null,
    bundle_id:              null,
    tanggal_mulai:          today,
    tanggal_selesai_rencana: '',
})

const durasi = computed(() => {
    if (!form.tanggal_mulai || !form.tanggal_selesai_rencana) return 0
    const start = new Date(form.tanggal_mulai)
    const end   = new Date(form.tanggal_selesai_rencana)
    return Math.ceil((end - start) / 86400000)
})

const durasiError = computed(() => {
    if (durasi.value > 5) return 'Durasi maksimal 5 hari.'
    if (durasi.value < 1) return 'Tanggal selesai harus setelah tanggal mulai.'
    return null
})

function openBooking(bundle) {
    selectedBundle.value = bundle
    form.bundle_id = bundle.id
    form.tanggal_mulai = today
    form.tanggal_selesai_rencana = ''
    form.clearErrors()
    showModal.value = true
}

function submitBooking() {
    form.post(route('bookings.store'), {
        onSuccess: () => { showModal.value = false },
    })
}

function fmt(d) {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
}

function doSearch() {
    router.get(route('bundles.index'), {
        search: search.value,
    }, { preserveState: true, replace: true })
}

function resetSearch() {
    search.value = ''
    doSearch()
}

function formatRupiah(val) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <Head title="Paket Bundle Hemat" />

    <v-container fluid class="pa-6" style="max-width: 1280px">

        <!-- Hero Header -->
        <v-row class="mb-8 align-center">
            <v-col cols="12" md="8">
                <div class="d-flex align-center gap-3 mb-2">
                    <v-icon icon="mdi-package-variant-closed" color="deep-purple-accent-2" size="36" />
                    <h1 class="text-h4 font-weight-bold text-grey-darken-4">Paket Bundle Hemat</h1>
                </div>
                <p class="text-body-1 text-grey-darken-1 mb-0">
                    Sewa lebih banyak, bayar lebih hemat dengan paket bundle pilihan AniRent.
                </p>
            </v-col>
        </v-row>

        <!-- Bundle Grid -->
        <v-row v-if="bundles.data.length > 0">
            <v-col
                v-for="bundle in bundles.data"
                :key="bundle.id"
                cols="12"
                sm="6"
                md="4"
                lg="4"
            >
                <v-card
                    rounded="xl"
                    elevation="0"
                    border
                    class="bundle-card h-100"
                    @click="router.get(route('bundles.show', bundle.id))" 
                >
                    <!-- Foto Bundle -->
                    <div class="bundle-img-wrap">
                        <v-img
                            v-if="bundle.foto"
                            :src="`/storage/${bundle.foto}`"
                            :alt="bundle.nama_bundle"
                            height="240"
                            cover
                            class="bundle-img"
                        />
                        <div v-else class="bundle-img-placeholder d-flex flex-column align-center justify-center h-100">
                            <v-icon icon="mdi-package-variant" size="64" color="grey-lighten-2" />
                        </div>
                        
                        <!-- Price Badge -->
                        <div class="price-badge">
                            <span class="price-val">{{ formatRupiah(bundle.harga_per_hari) }}</span>
                            <span class="price-unit">/ hari</span>
                        </div>

                        <!-- Availability Overlay if not bookable -->
                        <div v-if="!bundle.units.every(u => u.status === 'tersedia')" class="unavail-overlay">
                            <span>Sesaat Lagi</span>
                        </div>
                    </div>

                    <v-card-text class="pa-5">
                        <h3 class="text-h6 font-weight-bold mb-2 text-grey-darken-4 line-clamp-1">
                            {{ bundle.nama_bundle }}
                        </h3>

                        <p class="text-body-2 text-grey-darken-1 mb-4 line-clamp-2">
                            {{ bundle.deskripsi }}
                        </p>

                        <!-- Units Included -->
                        <div class="mb-2">
                            <div class="text-caption font-weight-bold text-grey mb-2">UNIT TERMASUK:</div>
                            <div class="d-flex flex-wrap gap-2">
                                <v-chip
                                    v-for="unit in bundle.units"
                                    :key="unit.id"
                                    size="x-small"
                                    variant="tonal"
                                    color="deep-purple-accent-1"
                                    class="font-weight-medium"
                                >
                                    {{ unit.nama_unit }}
                                </v-chip>
                            </div>
                        </div>
                    </v-card-text>

                    <v-divider />

                    <v-card-actions class="pa-4">
                        <v-btn
                            block
                            variant="flat"
                            color="deep-purple-accent-2"
                            rounded="lg"
                            prepend-icon="mdi-calendar-check"
                            :disabled="!bundle.units.every(u => u.status === 'tersedia')"
                            @click.stop="openBooking(bundle)"
                        >
                            {{ bundle.units.every(u => u.status === 'tersedia') ? 'Sewa Langsung' : 'Beberapa Unit Dipinjam' }}
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>


        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <v-icon icon="mdi-package-variant-closed" size="80" color="grey-lighten-2" class="mb-4" />
            <h3 class="text-h6 text-grey-darken-1">Belum ada paket bundle tersedia.</h3>
            <p class="text-body-2 text-grey">Silakan cek kembali nanti atau hubungi admin.</p>
        </div>

        <!-- Pagination -->
        <v-row v-if="bundles.last_page > 1" class="mt-8" justify="center">
            <v-pagination
                v-model="bundles.current_page"
                :length="bundles.last_page"
                @update:model-value="v => router.get(route('bundles.index', { page: v }))"
                rounded="circle"
                active-color="deep-purple-accent-2"
                density="comfortable"
            />
        </v-row>

    </v-container>

    <!-- ═══ BOOKING MODAL ═══ -->
    <Teleport to="body">
        <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">Sewa Paket Bundle</h2>
                        <p class="modal-sub">{{ selectedBundle?.nama_bundle }}</p>
                    </div>
                    <button class="modal-close" @click="showModal = false">✕</button>
                </div>
                <div class="modal-body">

                    <!-- Info -->
                    <div class="booking-info-card">
                        <div class="bi-row">
                            <span class="bi-label">Paket</span>
                            <span class="bi-val">{{ selectedBundle?.nama_bundle }}</span>
                        </div>
                        <div class="bi-row mt-1">
                            <span class="bi-label">Harga Paket</span>
                            <span class="bi-val text-primary font-weight-bold">Rp {{ selectedBundle?.harga_per_hari.toLocaleString('id-ID') }} / hari</span>
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
                            <strong class="text-primary">Rp {{ (durasi * (selectedBundle?.harga_per_hari || 0)).toLocaleString('id-ID') }}</strong>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn-outline" @click="showModal = false">Batal</button>
                        <button
                            class="btn-confirm"
                            :disabled="durasi < 1 || durasi > 5 || form.processing"
                            @click="submitBooking"
                        >
                            {{ form.processing ? 'Memproses...' : '✓ Konfirmasi Sewa' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style>
/* Layout Global Fix */
#app { background-color: #FAFAF8; }
</style>

<style scoped>
.bundle-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    background: #fff;
    overflow: hidden;
}

.bundle-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(83, 74, 183, 0.12) !important;
    border-color: #B39DDB !important;
}

.bundle-img-wrap {
    position: relative;
    background: #f5f5f5;
    overflow: hidden;
}

.bundle-img-placeholder {
    background: linear-gradient(135deg, #f8f7f4 0%, #eeeeee 100%);
}

.unavail-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: grayscale(1) blur(1px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

.unavail-overlay span {
    background: #555;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 20px;
    text-transform: uppercase;
}

.price-badge {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    padding: 6px 14px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.price-val {
    font-weight: 800;
    color: #534AB7;
    font-size: 1.1rem;
    line-height: 1.2;
}

.price-unit {
    font-size: 0.65rem;
    color: #888;
    text-transform: uppercase;
    font-weight: 600;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }

/* Modal Styles */
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
.btn-outline { padding: 12px 24px; border-radius: 12px; background: transparent; color: #534AB7; border: 1.5px solid #534AB7; font-size: 0.9375rem; font-weight: 600; text-decoration: none; cursor: pointer; }
</style>
