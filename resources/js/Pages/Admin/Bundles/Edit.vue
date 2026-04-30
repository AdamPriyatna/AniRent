<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bundle: Object,
    units:  Array,
})

const form = useForm({
    nama_bundle:    props.bundle.nama_bundle,
    deskripsi:      props.bundle.deskripsi ?? '',
    status:         props.bundle.status,
    harga_per_hari: props.bundle.harga_per_hari,
    foto:           null,
    hapus_foto:     '0',
    units:          props.bundle.units.map(u => u.id),
})

// Foto state
const existingFoto = ref(props.bundle.foto)
const preview      = ref(null)

function onFotoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    form.foto      = file
    form.hapus_foto = '0'
    preview.value  = URL.createObjectURL(file)
    existingFoto.value = null
}

function removeFoto() {
    preview.value      = null
    form.foto          = null
    form.hapus_foto    = '1'
    existingFoto.value = null
}

function toggleUnit(id) {
    const idx = form.units.indexOf(id)
    if (idx === -1) form.units.push(id)
    else form.units.splice(idx, 1)
}

function submit() {
    form.post(route('admin.bundles.update', props.bundle.id), {
        forceFormData: true,
        method: 'put',
    })
}
</script>

<template>
    <Head title="Edit Bundle" />

    <div class="page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Edit Bundle</h1>
                <p class="page-sub">Perbarui informasi paket sewa</p>
            </div>
            <Link :href="route('admin.bundles.index')" class="btn-outline">← Kembali</Link>
        </div>

        <form @submit.prevent="submit" class="form-layout">

            <!-- Left Column -->
            <div class="form-main">
                <div class="form-card">
                    <h3 class="section-title">Informasi Bundle</h3>

                    <!-- Nama Bundle -->
                    <div class="form-group">
                        <label class="label">Nama Bundle <span class="required">*</span></label>
                        <input
                            v-model="form.nama_bundle"
                            type="text"
                            class="input"
                            :class="{ 'input-error': form.errors.nama_bundle }"
                            placeholder="Contoh: Paket Naruto Full Set"
                            maxlength="255"
                        />
                        <p v-if="form.errors.nama_bundle" class="error-msg">{{ form.errors.nama_bundle }}</p>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group">
                        <label class="label">Deskripsi</label>
                        <textarea
                            v-model="form.deskripsi"
                            class="input textarea"
                            :class="{ 'input-error': form.errors.deskripsi }"
                            placeholder="Deskripsi bundle..."
                            rows="4"
                        ></textarea>
                        <p v-if="form.errors.deskripsi" class="error-msg">{{ form.errors.deskripsi }}</p>
                    </div>

                    <div class="two-col">
                        <!-- Harga -->
                        <div class="form-group">
                            <label class="label">Harga Sewa / Hari <span class="required">*</span></label>
                            <div class="input-prefix-wrap">
                                <span class="input-prefix">Rp</span>
                                <input
                                    v-model="form.harga_per_hari"
                                    type="number"
                                    min="0"
                                    class="input input-with-prefix"
                                    :class="{ 'input-error': form.errors.harga_per_hari }"
                                    placeholder="50000"
                                />
                            </div>
                            <p v-if="form.errors.harga_per_hari" class="error-msg">{{ form.errors.harga_per_hari }}</p>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label class="label">Status <span class="required">*</span></label>
                            <select
                                v-model="form.status"
                                class="input"
                                :class="{ 'input-error': form.errors.status }"
                            >
                                <option value="tersedia">Tersedia</option>
                                <option value="disewa">Disewa</option>
                            </select>
                            <p v-if="form.errors.status" class="error-msg">{{ form.errors.status }}</p>
                        </div>
                    </div>
                </div>

                <!-- Unit Selector -->
                <div class="form-card">
                    <h3 class="section-title">Pilih Unit <span class="required">*</span></h3>
                    <p class="section-hint">Pilih unit-unit yang termasuk dalam bundle ini</p>

                    <div class="unit-grid">
                        <div
                            v-for="unit in units"
                            :key="unit.id"
                            class="unit-card"
                            :class="{ 'unit-card-active': form.units.includes(unit.id) }"
                            @click="toggleUnit(unit.id)"
                        >
                            <div class="unit-card-check">
                                <span v-if="form.units.includes(unit.id)" class="check-icon">✓</span>
                                <span v-else class="check-icon-empty">+</span>
                            </div>
                            <div class="unit-card-info">
                                <div class="unit-kode">{{ unit.kode_unit }}</div>
                                <div class="unit-nama">{{ unit.nama_unit }}</div>
                            </div>
                            <div class="unit-status-badge" :class="unit.status === 'tersedia' ? 'badge-green' : 'badge-amber'">
                                {{ unit.status }}
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.units" class="error-msg mt-4">{{ form.errors.units }}</p>

                    <div v-if="form.units.length > 0" class="selected-summary">
                        {{ form.units.length }} unit dipilih
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-side">
                <!-- Foto -->
                <div class="form-card">
                    <h3 class="section-title">Foto Bundle</h3>

                    <div class="foto-area">
                        <!-- New preview -->
                        <div v-if="preview" class="foto-preview">
                            <img :src="preview" alt="preview" />
                            <button type="button" class="foto-remove" @click="removeFoto">✕</button>
                        </div>
                        <!-- Existing foto -->
                        <div v-else-if="existingFoto" class="foto-preview">
                            <img :src="`/storage/${existingFoto}`" :alt="bundle.nama_bundle" />
                            <button type="button" class="foto-remove" @click="removeFoto">✕</button>
                        </div>
                        <!-- Upload placeholder -->
                        <label v-else class="foto-upload">
                            <input type="file" accept="image/*" @change="onFotoChange" style="display:none" />
                            <span class="upload-icon">📷</span>
                            <span class="upload-text">Klik untuk upload foto</span>
                            <span class="upload-hint">JPG, PNG maks 2MB</span>
                        </label>
                    </div>
                    <p v-if="form.errors.foto" class="error-msg">{{ form.errors.foto }}</p>
                </div>

                <!-- Summary -->
                <div class="form-card summary-card">
                    <h3 class="section-title">Ringkasan</h3>
                    <div class="summary-row">
                        <span class="summary-label">Nama</span>
                        <span class="summary-val">{{ form.nama_bundle || '—' }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Harga</span>
                        <span class="summary-val">
                            {{ form.harga_per_hari ? 'Rp ' + Number(form.harga_per_hari).toLocaleString('id-ID') : '—' }}/hari
                        </span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Jumlah Unit</span>
                        <span class="summary-val">{{ form.units.length }} unit</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Status</span>
                        <span class="summary-val">
                            <span :class="form.status === 'tersedia' ? 'pill-teal' : 'pill-amber'" class="pill">
                                {{ form.status === 'tersedia' ? 'Tersedia' : 'Disewa' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>

        </form>

        <!-- Actions -->
        <div class="form-actions">
            <Link :href="route('admin.bundles.index')" class="btn-outline">Batal</Link>
            <button type="button" class="btn-primary" :disabled="form.processing" @click="submit">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </div>

    </div>
</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; padding: 2rem 1.5rem; }

.page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.75rem; gap: 1rem;
}
.page-title { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-sub { font-size: 0.875rem; color: #888; }

.form-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 720px) {
    .form-layout { grid-template-columns: 1fr; }
}
.form-main { display: flex; flex-direction: column; gap: 1.25rem; }
.form-side { display: flex; flex-direction: column; gap: 1.25rem; }

.form-card {
    background: #fff; border: 0.5px solid #e0ddd5;
    border-radius: 14px; padding: 1.5rem;
}

.section-title {
    font-size: 0.9375rem; font-weight: 600; color: #1a1a2e;
    margin-bottom: 1rem;
}
.section-hint { font-size: 0.8125rem; color: #aaa; margin-top: -8px; margin-bottom: 12px; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 500px) { .two-col { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1.25rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; margin-bottom: 6px; }
.required { color: #D85A30; }

.input {
    width: 100%; padding: 9px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none;
    transition: border-color 0.15s; box-sizing: border-box;
}
.input:focus { border-color: #534AB7; }
.input-error { border-color: #D85A30 !important; }
.textarea { resize: vertical; min-height: 90px; }
.error-msg { font-size: 0.75rem; color: #D85A30; margin-top: 4px; }
.mt-4 { margin-top: 1rem; }

.input-prefix-wrap { position: relative; display: flex; align-items: center; }
.input-prefix { position: absolute; left: 12px; font-size: 0.875rem; color: #888; pointer-events: none; }
.input-with-prefix { padding-left: 32px !important; }

.unit-grid { display: flex; flex-direction: column; gap: 8px; }
.unit-card {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; border-radius: 10px;
    border: 1.5px solid #e0ddd5; cursor: pointer;
    transition: all 0.15s; background: #fff;
}
.unit-card:hover { border-color: #534AB7; background: #f9f8ff; }
.unit-card-active { border-color: #534AB7 !important; background: #F0EFFE !important; }

.unit-card-check { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.check-icon {
    width: 22px; height: 22px; border-radius: 50%;
    background: #534AB7; color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700;
}
.check-icon-empty {
    width: 22px; height: 22px; border-radius: 50%;
    border: 1.5px solid #d0ccc4; color: #ccc;
    display: flex; align-items: center; justify-content: center; font-size: 14px;
}

.unit-card-info { flex: 1; min-width: 0; }
.unit-kode { font-size: 0.75rem; font-weight: 700; color: #534AB7; font-family: monospace; background: #EEEDFE; padding: 1px 6px; border-radius: 4px; display: inline-block; margin-bottom: 2px; }
.unit-nama { font-size: 0.8125rem; color: #1a1a2e; font-weight: 500; }

.unit-status-badge { font-size: 0.6875rem; padding: 2px 8px; border-radius: 8px; font-weight: 500; }
.badge-green { background: #E1F5EE; color: #0F6E56; }
.badge-amber { background: #FAEEDA; color: #633806; }

.selected-summary { margin-top: 12px; padding: 8px 12px; background: #F0EFFE; border-radius: 8px; font-size: 0.8125rem; color: #534AB7; font-weight: 500; }

.foto-area { margin-top: 4px; }
.foto-preview { position: relative; display: inline-block; width: 100%; }
.foto-preview img { width: 100%; max-width: 240px; height: 160px; object-fit: cover; border-radius: 10px; border: 0.5px solid #e0ddd5; }
.foto-remove {
    position: absolute; top: -8px; right: -8px;
    width: 22px; height: 22px; border-radius: 50%;
    background: #D85A30; color: #fff; border: none;
    font-size: 11px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.foto-upload {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 100%; height: 140px; border: 2px dashed #d0ccc4;
    border-radius: 10px; cursor: pointer; gap: 6px; transition: border-color 0.15s;
}
.foto-upload:hover { border-color: #534AB7; }
.upload-icon { font-size: 28px; }
.upload-text { font-size: 0.8125rem; color: #888; }
.upload-hint { font-size: 0.6875rem; color: #bbb; }

.summary-card { background: #F9F8FF; border-color: #dddaf8; }
.summary-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 10px; font-size: 0.8125rem; }
.summary-row:last-child { margin-bottom: 0; }
.summary-label { color: #888; flex-shrink: 0; }
.summary-val { color: #1a1a2e; font-weight: 500; text-align: right; }

.pill { font-size: 0.6875rem; padding: 2px 8px; border-radius: 8px; font-weight: 500; }
.pill-teal  { background: #E1F5EE; color: #0F6E56; }
.pill-amber { background: #FAEEDA; color: #633806; }

.form-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 1.5rem; padding-top: 1.25rem;
    border-top: 0.5px solid #f0ece6;
}
.btn-primary {
    padding: 9px 20px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.875rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-primary:hover:not(:disabled) { background: #3C3489; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline {
    padding: 9px 20px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.875rem; font-weight: 500;
    text-decoration: none; cursor: pointer;
}
.btn-outline:hover { background: #EEEDFE; }
</style>
