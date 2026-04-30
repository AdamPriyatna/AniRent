<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout
})
const props = defineProps({
    unit: Object,
    categories: Array,
})

const form = useForm({
    kode_unit: props.unit.kode_unit,
    nama_unit: props.unit.nama_unit,
    deskripsi: props.unit.deskripsi ?? '',
    kondisi: props.unit.kondisi ?? '',
    status: props.unit.status,
    foto: null,
    categories: props.unit.categories?.map(c => c.id) ?? [],
    _method: 'PUT',
})

const preview = ref(null)
const existingFoto = ref(props.unit.foto)

function onFotoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    form.foto = file
    preview.value = URL.createObjectURL(file)
    existingFoto.value = null
}

function removeFoto() {
    preview.value = null
    form.foto = null
    existingFoto.value = null
}

function toggleKategori(id) {
    const idx = form.categories.indexOf(id)
    if (idx === -1) form.categories.push(id)
    else form.categories.splice(idx, 1)
}

function submit() {
    form.post(route('admin.units.update', props.unit.id), {
        forceFormData: true,
    })
}
</script>

<template>
    <Head :title="`Edit Unit - ${unit.nama_unit}`" />

        <div class="page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Edit Unit</h1>
                    <p class="page-sub">
                        <span class="code-badge">{{ unit.kode_unit }}</span>
                        {{ unit.nama_unit }}
                    </p>
                </div>
                <Link :href="route('admin.units.index')" class="btn-outline">← Kembali</Link>
            </div>

            <form @submit.prevent="submit" class="form-card">

                <div class="two-col">
                    <div class="form-group">
                        <label class="label">Kode Unit <span class="required">*</span></label>
                        <input
                            v-model="form.kode_unit"
                            type="text"
                            class="input"
                            :class="{ 'input-error': form.errors.kode_unit }"
                            placeholder="Contoh: COS-001"
                            maxlength="50"
                        />
                        <p v-if="form.errors.kode_unit" class="error-msg">{{ form.errors.kode_unit }}</p>
                    </div>

                    <div class="form-group">
                        <label class="label">Nama Unit <span class="required">*</span></label>
                        <input
                            v-model="form.nama_unit"
                            type="text"
                            class="input"
                            :class="{ 'input-error': form.errors.nama_unit }"
                            placeholder="Contoh: Kostum Naruto"
                            maxlength="255"
                        />
                        <p v-if="form.errors.nama_unit" class="error-msg">{{ form.errors.nama_unit }}</p>
                    </div>
                </div>

                <div class="two-col">
                    <div class="form-group">
                        <label class="label">Kondisi</label>
                        <input
                            v-model="form.kondisi"
                            type="text"
                            class="input"
                            placeholder="Contoh: Baik, Baru, Bekas"
                            maxlength="100"
                        />
                    </div>

                    <div class="form-group">
                        <label class="label">Status <span class="required">*</span></label>
                        <select v-model="form.status" class="input" :class="{ 'input-error': form.errors.status }">
                            <option value="tersedia">Tersedia</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="rusak">Rusak</option>
                        </select>
                        <p v-if="form.errors.status" class="error-msg">{{ form.errors.status }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Deskripsi</label>
                    <textarea
                        v-model="form.deskripsi"
                        class="input textarea"
                        placeholder="Deskripsi unit..."
                        rows="3"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="label">Kategori <span class="required">*</span></label>
                    <p class="hint" style="margin-bottom: 8px">Pilih satu atau lebih kategori</p>
                    <div class="kategori-grid">
                        <div
                            v-for="cat in categories"
                            :key="cat.id"
                            class="kat-chip"
                            :class="{ 'kat-chip-active': form.categories.includes(cat.id) }"
                            @click="toggleKategori(cat.id)"
                        >
                            <span class="kat-check">{{ form.categories.includes(cat.id) ? '✓' : '+' }}</span>
                            {{ cat.nama_kategori }}
                        </div>
                    </div>
                    <p v-if="form.errors.categories" class="error-msg">{{ form.errors.categories }}</p>
                </div>

                <!-- Foto -->
                <div class="form-group">
                    <label class="label">Foto Unit</label>

                    <!-- Foto existing -->
                    <div v-if="existingFoto && !preview" class="foto-existing">
                        <img :src="`/storage/${existingFoto}`" alt="foto unit" />
                        <button type="button" class="foto-remove" @click="removeFoto">✕</button>
                        <p class="hint" style="margin-top: 6px">Foto saat ini. Klik ✕ untuk hapus atau upload baru.</p>
                    </div>

                    <!-- Preview baru -->
                    <div v-else-if="preview" class="foto-preview">
                        <img :src="preview" alt="preview" />
                        <button type="button" class="foto-remove" @click="preview = null; form.foto = null">✕</button>
                    </div>

                    <!-- Upload area -->
                    <label v-else class="foto-upload">
                        <input type="file" accept="image/*" @change="onFotoChange" style="display:none" />
                        <span class="upload-icon">+</span>
                        <span class="upload-text">Klik untuk upload foto baru</span>
                        <span class="upload-hint">JPG, PNG maks 2MB</span>
                    </label>

                    <p v-if="form.errors.foto" class="error-msg">{{ form.errors.foto }}</p>
                </div>

                <div class="form-actions">
                    <Link :href="route('admin.units.index')" class="btn-outline">Batal</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Update Unit' }}
                    </button>
                </div>

            </form>
        </div>
 
</template>

<style scoped>
.page { max-width: 760px; margin: 0 auto; padding: 2rem 1.5rem; }
.page-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1.5rem; gap: 1rem;
}
.page-title { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-sub { font-size: 0.875rem; color: #888; display: flex; align-items: center; gap: 8px; }
.code-badge {
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 600;
    padding: 2px 8px; border-radius: 6px; font-family: monospace;
}
.form-card {
    background: #fff; border: 0.5px solid #e0ddd5;
    border-radius: 14px; padding: 1.75rem;
}
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 580px) { .two-col { grid-template-columns: 1fr; } }
.form-group { margin-bottom: 1.25rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; margin-bottom: 6px; }
.required { color: #D85A30; }
.hint { font-size: 0.75rem; color: #aaa; margin-top: 4px; }
.input {
    width: 100%; padding: 9px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none; transition: border-color 0.15s;
}
.input:focus { border-color: #534AB7; }
.input-error { border-color: #D85A30 !important; }
.textarea { resize: vertical; min-height: 80px; }
.error-msg { font-size: 0.75rem; color: #D85A30; margin-top: 4px; }
.kategori-grid { display: flex; gap: 8px; flex-wrap: wrap; }
.kat-chip {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 20px;
    border: 1.5px solid #d0ccc4; background: #fff;
    font-size: 0.8125rem; color: #555; cursor: pointer; transition: all 0.15s;
}
.kat-chip:hover { border-color: #534AB7; color: #534AB7; }
.kat-chip-active { border-color: #534AB7 !important; background: #EEEDFE !important; color: #534AB7 !important; font-weight: 500; }
.kat-check { font-size: 12px; font-weight: 700; }
.foto-existing { position: relative; display: inline-block; }
.foto-existing img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 0.5px solid #e0ddd5; display: block; }
.foto-preview { position: relative; display: inline-block; }
.foto-preview img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 0.5px solid #e0ddd5; }
.foto-remove {
    position: absolute; top: -8px; right: -8px;
    width: 22px; height: 22px; border-radius: 50%;
    background: #D85A30; color: #fff; border: none;
    font-size: 11px; cursor: pointer;
}
.foto-upload {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 160px; height: 120px; border: 2px dashed #d0ccc4;
    border-radius: 10px; cursor: pointer; gap: 4px; transition: border-color 0.15s;
}
.foto-upload:hover { border-color: #534AB7; }
.upload-icon { font-size: 24px; color: #ccc; }
.upload-text { font-size: 0.75rem; color: #888; }
.upload-hint { font-size: 0.6875rem; color: #bbb; }
.form-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 1.5rem; padding-top: 1.25rem;
    border-top: 0.5px solid #f0ece6;
}
.btn-primary {
    padding: 9px 20px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.875rem; font-weight: 500; border: none; cursor: pointer;
}
.btn-primary:hover { background: #3C3489; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline {
    padding: 9px 20px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.875rem; font-weight: 500; text-decoration: none; cursor: pointer;
}
.btn-outline:hover { background: #EEEDFE; }
</style>