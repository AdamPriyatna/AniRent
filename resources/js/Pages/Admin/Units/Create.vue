<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'


const props = defineProps({
    categories: Array,
})

const form = useForm({
    kode_unit: '',
    nama_unit: '',
    deskripsi: '',
    kondisi: '',
    harga_sewa: 0,
    status: 'tersedia',
    foto: null,
    categories: [],
})

const preview = ref(null)

function onFotoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    form.foto = file
    preview.value = URL.createObjectURL(file)
}

function toggleKategori(id) {
    const idx = form.categories.indexOf(id)
    if (idx === -1) form.categories.push(id)
    else form.categories.splice(idx, 1)
}

function submit() {
    form.post(route('admin.units.store'), {
        forceFormData: true,
    })
}
</script>

<template>
    <Head title="Tambah Unit" />

        <div class="page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Tambah Unit Baru</h1>
                    <p class="page-sub">Isi data unit yang akan ditambahkan ke AniRent</p>
                </div>
                <Link :href="route('admin.units.index')" class="btn-outline">
                    ← Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="form-card">

                <div class="two-col">
                    <!-- Kode Unit -->
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
                        <p class="hint">Kode unik, tidak boleh sama dengan unit lain</p>
                    </div>

                    <!-- Nama Unit -->
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
                    <!-- Kondisi -->
                    <div class="form-group">
                        <label class="label">Kondisi</label>
                        <input
                            v-model="form.kondisi"
                            type="text"
                            class="input"
                            placeholder="Contoh: Baik, Baru, Bekas"
                            maxlength="100"
                        />
                        <p v-if="form.errors.kondisi" class="error-msg">{{ form.errors.kondisi }}</p>
                    </div>

                    <!-- Status -->
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
                    <label class="label">Harga Sewa / Hari (Rp) <span class="required">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">Rp</span>
                        <input
                            v-model="form.harga_sewa"
                            type="number"
                            min="0"
                            class="input input-with-prefix"
                            :class="{ 'input-error': form.errors.harga_sewa }"
                            placeholder="50000"
                        />
                    </div>
                    <p v-if="form.errors.harga_sewa" class="error-msg">{{ form.errors.harga_sewa }}</p>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label class="label">Deskripsi</label>
                    <textarea
                        v-model="form.deskripsi"
                        class="input textarea"
                        :class="{ 'input-error': form.errors.deskripsi }"
                        placeholder="Deskripsi unit..."
                        rows="3"
                    ></textarea>
                    <p v-if="form.errors.deskripsi" class="error-msg">{{ form.errors.deskripsi }}</p>
                </div>

                <!-- Kategori (multi-select) -->
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
                    <div class="foto-area">
                        <div v-if="preview" class="foto-preview">
                            <img :src="preview" alt="preview" />
                            <button type="button" class="foto-remove" @click="preview = null; form.foto = null">✕</button>
                        </div>
                        <label v-else class="foto-upload">
                            <input type="file" accept="image/*" @change="onFotoChange" style="display:none" />
                            <span class="upload-icon">+</span>
                            <span class="upload-text">Klik untuk upload foto</span>
                            <span class="upload-hint">JPG, PNG maks 2MB</span>
                        </label>
                    </div>
                    <p v-if="form.errors.foto" class="error-msg">{{ form.errors.foto }}</p>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <Link :href="route('admin.units.index')" class="btn-outline">Batal</Link>
                    <button type="submit" class="btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Unit' }}
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
.page-title { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.page-sub { font-size: 0.875rem; color: rgba(255,255,255,0.7); }

.form-card {
    background: rgba(15, 10, 30, 0.75); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 14px; padding: 1.75rem; color: white;
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.6);
}

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 580px) { .two-col { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1.25rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 8px; }
.required { color: #fca5a5; }
.hint { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 6px; }

.input {
    width: 100%; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.4); color: white;
    font-size: 0.875rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
}
.input::placeholder { color: rgba(255,255,255,0.4); }
.input:focus { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-error { border-color: #fca5a5 !important; }
.input option { background: #1a103c; color: white; }

.input-prefix-wrap {
    display: flex; align-items: center;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;
    overflow: hidden; transition: border-color 0.2s, box-shadow 0.2s; background: rgba(0,0,0,0.4);
}
.input-prefix-wrap:focus-within { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-prefix {
    background: rgba(255,255,255,0.1); padding: 10px 14px;
    border-right: 1px solid rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.8); font-size: 0.875rem; font-weight: 600;
}
.input-with-prefix { border: none !important; border-radius: 0 !important; background: transparent; }
.input-with-prefix:focus { box-shadow: none; }

.textarea { resize: vertical; min-height: 80px; }
.error-msg { font-size: 0.75rem; color: #fca5a5; margin-top: 6px; }

.kategori-grid { display: flex; gap: 8px; flex-wrap: wrap; }
.kat-chip {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05);
    font-size: 0.8125rem; color: rgba(255,255,255,0.8); cursor: pointer;
    transition: all 0.2s; backdrop-filter: blur(8px);
}
.kat-chip:hover { border-color: #d8b4fe; color: #fff; background: rgba(168, 85, 247, 0.1); }
.kat-chip-active { border-color: #d8b4fe !important; background: rgba(168, 85, 247, 0.25) !important; color: #fff !important; font-weight: 600; box-shadow: 0 0 10px rgba(168,85,247,0.3); }
.kat-check { font-size: 12px; font-weight: 700; }

.foto-area { margin-top: 4px; }
.foto-preview { position: relative; display: inline-block; }
.foto-preview img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
.foto-remove {
    position: absolute; top: -8px; right: -8px;
    width: 24px; height: 24px; border-radius: 50%;
    background: #ef4444; color: #fff; border: 2px solid #1a103c;
    font-size: 11px; cursor: pointer; display: flex;
    align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); transition: 0.2s;
}
.foto-remove:hover { background: #dc2626; transform: scale(1.1); }
.foto-upload {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 160px; height: 120px; border: 2px dashed rgba(255,255,255,0.3);
    border-radius: 10px; cursor: pointer; gap: 4px; background: rgba(0,0,0,0.2);
    transition: 0.2s;
}
.foto-upload:hover { border-color: #d8b4fe; background: rgba(168, 85, 247, 0.1); }
.upload-icon { font-size: 24px; color: rgba(255,255,255,0.5); transition: 0.2s; }
.foto-upload:hover .upload-icon { color: #d8b4fe; }
.upload-text { font-size: 0.75rem; color: rgba(255,255,255,0.7); }
.upload-hint { font-size: 0.6875rem; color: rgba(255,255,255,0.4); }

.form-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 2rem; padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}
.btn-primary {
    padding: 10px 24px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.875rem; font-weight: 600;
    border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 0 15px rgba(168,85,247,0.4);
}
.btn-primary:hover { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-outline {
    padding: 10px 24px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.875rem; font-weight: 600;
    text-decoration: none; cursor: pointer; transition: 0.2s; backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }
</style>