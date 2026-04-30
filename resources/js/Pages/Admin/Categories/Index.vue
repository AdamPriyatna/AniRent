<script setup>
import { ref, reactive } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'


const props = defineProps({
    categories: Object,
})

// Modal state
const showModal = ref(false)
const isEdit = ref(false)
const editId = ref(null)

const form = useForm({
    nama_kategori: '',
    deskripsi: '',
})

function openCreate() {
    isEdit.value = false
    editId.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

function openEdit(cat) {
    isEdit.value = true
    editId.value = cat.id
    form.nama_kategori = cat.nama_kategori
    form.deskripsi = cat.deskripsi ?? ''
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    form.reset()
    form.clearErrors()
}

function submit() {
    if (isEdit.value) {
        form.put(route('admin.categories.update', editId.value), {
            onSuccess: () => closeModal(),
        })
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => closeModal(),
        })
    }
}

function hapus(id) {
    if (confirm('Yakin ingin menghapus kategori ini? Kategori yang masih digunakan unit tidak bisa dihapus.')) {
        router.delete(route('admin.categories.destroy', id))
    }
}
</script>

<template>
    <Head title="Manajemen Kategori" />

        <div class="page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Manajemen Kategori</h1>
                    <p class="page-sub">Kelola kategori unit yang tersedia di AniRent</p>
                </div>
                <button class="btn-primary" @click="openCreate">+ Tambah Kategori</button>
            </div>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="alert-success">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="alert-error">
                {{ $page.props.flash.error }}
            </div>

            <!-- Grid kategori -->
            <div v-if="categories.data.length > 0" class="cat-grid">
                <div v-for="cat in categories.data" :key="cat.id" class="cat-card">
                    <div class="cat-card-top">
                        <div class="cat-icon">{{ cat.nama_kategori.charAt(0) }}</div>
                        <div class="cat-actions">
                            <button class="btn-edit" @click="openEdit(cat)">Edit</button>
                            <button class="btn-delete" @click="hapus(cat.id)">Hapus</button>
                        </div>
                    </div>
                    <h3 class="cat-name">{{ cat.nama_kategori }}</h3>
                    <p class="cat-desc">{{ cat.deskripsi ?? 'Tidak ada deskripsi' }}</p>
                    <div class="cat-footer">
                        <span class="cat-count">{{ cat.units_count }} unit</span>
                    </div>
                </div>

                <!-- Card tambah -->
                <div class="cat-card cat-card-add" @click="openCreate">
                    <div class="add-icon">+</div>
                    <p class="add-text">Tambah kategori baru</p>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="empty-state">
                <p class="empty-icon">🗂️</p>
                <p class="empty-title">Belum ada kategori</p>
                <p class="empty-sub">Mulai dengan menambahkan kategori pertama</p>
                <button class="btn-primary" style="margin-top: 1rem" @click="openCreate">
                    + Tambah Kategori
                </button>
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="categories.last_page > 1">
                <a
                    v-for="link in categories.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="page-btn"
                    :class="{ active: link.active, disabled: !link.url }"
                    v-html="link.label"
                    @click.prevent="link.url && router.get(link.url)"
                />
            </div>

        </div>

        <!-- Modal Create / Edit -->
        <Teleport to="body">
            <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                <div class="modal">
                    <div class="modal-header">
                        <h2 class="modal-title">
                            {{ isEdit ? 'Edit Kategori' : 'Tambah Kategori' }}
                        </h2>
                        <button class="modal-close" @click="closeModal">✕</button>
                    </div>

                    <form @submit.prevent="submit">

                        <div class="form-group">
                            <label class="label">Nama Kategori <span class="required">*</span></label>
                            <input
                                v-model="form.nama_kategori"
                                type="text"
                                class="input"
                                :class="{ 'input-error': form.errors.nama_kategori }"
                                placeholder="Contoh: Cosplay, Game & Console..."
                                maxlength="100"
                                autofocus
                            />
                            <p v-if="form.errors.nama_kategori" class="error-msg">
                                {{ form.errors.nama_kategori }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="label">Deskripsi</label>
                            <textarea
                                v-model="form.deskripsi"
                                class="input textarea"
                                placeholder="Deskripsi singkat kategori ini..."
                                rows="3"
                            ></textarea>
                            <p v-if="form.errors.deskripsi" class="error-msg">
                                {{ form.errors.deskripsi }}
                            </p>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn-outline" @click="closeModal">
                                Batal
                            </button>
                            <button type="submit" class="btn-primary" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Update' : 'Simpan') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </Teleport>


</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; padding: 2rem 1.5rem; }

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
.alert-error {
    background: #FAECE7; border: 0.5px solid #F0997B;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #712B13; margin-bottom: 1rem;
}

/* Grid */
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 1.5rem;
}

.cat-card {
    background: #fff; border: 0.5px solid #e0ddd5;
    border-radius: 14px; padding: 1.25rem;
    display: flex; flex-direction: column; gap: 8px;
    transition: box-shadow 0.15s, transform 0.15s;
}
.cat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); transform: translateY(-2px); }

.cat-card-top {
    display: flex; justify-content: space-between; align-items: flex-start;
}
.cat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: #EEEDFE; color: #534AB7;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 600;
}
.cat-actions { display: flex; gap: 5px; }

.cat-name { font-size: 0.9375rem; font-weight: 600; color: #1a1a2e; }
.cat-desc { font-size: 0.8125rem; color: #aaa; line-height: 1.5; flex: 1; }
.cat-footer { margin-top: 4px; }
.cat-count {
    font-size: 0.75rem; font-weight: 500;
    background: #F1EFE8; color: #5F5E5A;
    padding: 3px 10px; border-radius: 10px;
    display: inline-block;
}

/* Add card */
.cat-card-add {
    border: 1.5px dashed #d0ccc4;
    align-items: center; justify-content: center;
    cursor: pointer; background: transparent;
    min-height: 160px;
}
.cat-card-add:hover { border-color: #534AB7; background: #EEEDFE; transform: translateY(-2px); }
.add-icon { font-size: 28px; color: #ccc; margin-bottom: 6px; }
.add-text { font-size: 0.8125rem; color: #aaa; }
.cat-card-add:hover .add-icon,
.cat-card-add:hover .add-text { color: #534AB7; }

/* Empty state */
.empty-state { text-align: center; padding: 60px 0; }
.empty-icon { font-size: 48px; margin-bottom: 12px; }
.empty-title { font-size: 1rem; font-weight: 500; color: #555; margin-bottom: 6px; }
.empty-sub { font-size: 0.875rem; color: #aaa; }

/* Pagination */
.pagination { display: flex; gap: 4px; flex-wrap: wrap; }
.page-btn {
    padding: 6px 11px; border-radius: 6px;
    border: 0.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #534AB7; text-decoration: none; cursor: pointer;
}
.page-btn.active { background: #534AB7; color: #fff; border-color: #534AB7; }
.page-btn.disabled { color: #ccc; pointer-events: none; }

/* Buttons */
.btn-primary {
    padding: 8px 16px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-primary:hover { background: #3C3489; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline {
    padding: 8px 16px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.8125rem; font-weight: 500; cursor: pointer;
}
.btn-outline:hover { background: #EEEDFE; }
.btn-edit {
    padding: 4px 10px; border-radius: 6px;
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 500; border: none; cursor: pointer;
}
.btn-edit:hover { background: #CECBF6; }
.btn-delete {
    padding: 4px 10px; border-radius: 6px;
    background: #FAECE7; color: #993C1D;
    font-size: 0.75rem; font-weight: 500; border: none; cursor: pointer;
}
.btn-delete:hover { background: #F5C4B3; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex; align-items: center; justify-content: center;
    z-index: 50; padding: 1rem;
}
.modal {
    background: #fff; border-radius: 16px;
    padding: 1.5rem; width: 100%; max-width: 440px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.25rem;
}
.modal-title { font-size: 1.1rem; font-weight: 600; color: #1a1a2e; }
.modal-close {
    width: 28px; height: 28px; border-radius: 50%;
    background: #F1EFE8; border: none; cursor: pointer;
    font-size: 12px; color: #555;
}
.modal-close:hover { background: #e0ddd5; }

.form-group { margin-bottom: 1rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; margin-bottom: 6px; }
.required { color: #D85A30; }
.input {
    width: 100%; padding: 9px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none; transition: border-color 0.15s;
}
.input:focus { border-color: #534AB7; }
.input-error { border-color: #D85A30 !important; }
.textarea { resize: vertical; min-height: 80px; }
.error-msg { font-size: 0.75rem; color: #D85A30; margin-top: 4px; }

.modal-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 1.5rem; padding-top: 1rem;
    border-top: 0.5px solid #f0ece6;
}
</style>