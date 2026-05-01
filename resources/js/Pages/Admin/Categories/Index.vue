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
.page-title { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.page-sub { font-size: 0.875rem; color: rgba(255,255,255,0.7); }

.alert-success {
    background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #86efac; margin-bottom: 1rem;
    backdrop-filter: blur(8px);
}
.alert-error {
    background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #fca5a5; margin-bottom: 1rem;
    backdrop-filter: blur(8px);
}

/* Grid */
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 1.5rem;
}

.cat-card {
    background: rgba(15, 10, 30, 0.75); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 14px; padding: 1.25rem;
    display: flex; flex-direction: column; gap: 8px;
    transition: 0.2s; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}
.cat-card:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(0,0,0,0.5); border-color: rgba(216,180,254,0.4); }

.cat-card-top {
    display: flex; justify-content: space-between; align-items: flex-start;
}
.cat-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border: 1px solid rgba(168, 85, 247, 0.3);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 600;
}
.cat-actions { display: flex; gap: 5px; }

.cat-name { font-size: 0.9375rem; font-weight: 600; color: #fff; }
.cat-desc { font-size: 0.8125rem; color: rgba(255,255,255,0.6); line-height: 1.5; flex: 1; }
.cat-footer { margin-top: 4px; }
.cat-count {
    font-size: 0.75rem; font-weight: 500;
    background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8);
    padding: 3px 10px; border-radius: 10px;
    display: inline-block; border: 1px solid rgba(255,255,255,0.05);
}

/* Add card */
.cat-card-add {
    border: 1.5px dashed rgba(255,255,255,0.3);
    align-items: center; justify-content: center;
    cursor: pointer; background: rgba(0,0,0,0.3);
    min-height: 160px;
}
.cat-card-add:hover { border-color: #d8b4fe; background: rgba(168,85,247,0.15); transform: translateY(-2px); }
.add-icon { font-size: 28px; color: rgba(255,255,255,0.5); margin-bottom: 6px; transition: 0.2s; }
.add-text { font-size: 0.8125rem; color: rgba(255,255,255,0.6); transition: 0.2s; }
.cat-card-add:hover .add-icon,
.cat-card-add:hover .add-text { color: #d8b4fe; }

/* Empty state */
.empty-state { text-align: center; padding: 60px 0; background: rgba(15,10,30,0.6); border-radius: 14px; border: 1px dashed rgba(255,255,255,0.2); backdrop-filter: blur(10px); }
.empty-icon { font-size: 48px; margin-bottom: 12px; filter: drop-shadow(0 0 10px rgba(255,255,255,0.2)); }
.empty-title { font-size: 1.1rem; font-weight: 600; color: #fff; margin-bottom: 6px; }
.empty-sub { font-size: 0.875rem; color: rgba(255,255,255,0.6); }

/* Pagination */
.pagination { display: flex; gap: 6px; flex-wrap: wrap; }
.page-btn {
    padding: 8px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3);
    font-size: 0.8125rem; color: rgba(255,255,255,0.7); text-decoration: none; cursor: pointer;
    transition: 0.2s; backdrop-filter: blur(8px);
}
.page-btn:hover:not(.disabled) { background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.3); }
.page-btn.active { background: linear-gradient(135deg, #a855f7, #ec4899); color: #fff; border: none; box-shadow: 0 0 10px rgba(168,85,247,0.4); }
.page-btn.disabled { color: rgba(255,255,255,0.3); pointer-events: none; border-color: transparent; }

/* Buttons */
.btn-primary {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.8125rem; font-weight: 600;
    border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 0 15px rgba(168,85,247,0.4);
}
.btn-primary:hover { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-outline {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: 0.2s; backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }

.btn-edit {
    padding: 6px 12px; border-radius: 6px;
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(168,85,247,0.3); cursor: pointer; transition: 0.2s;
}
.btn-edit:hover { background: rgba(168, 85, 247, 0.4); color: #fff; }
.btn-delete {
    padding: 6px 12px; border-radius: 6px;
    background: rgba(239, 68, 68, 0.2); color: #fca5a5;
    font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(239,68,68,0.3); cursor: pointer; transition: 0.2s;
}
.btn-delete:hover { background: rgba(239, 68, 68, 0.4); color: #fff; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    z-index: 50; padding: 1rem;
}
.modal {
    background: rgba(20, 12, 45, 0.95); border-radius: 16px; border: 1px solid rgba(255,255,255,0.1);
    padding: 1.5rem; width: 100%; max-width: 440px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6); color: white;
}
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.25rem;
}
.modal-title { font-size: 1.2rem; font-weight: 600; color: #fff; }
.modal-close {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(255,255,255,0.1); border: none; cursor: pointer;
    font-size: 12px; color: rgba(255,255,255,0.6); transition: 0.2s;
}
.modal-close:hover { background: rgba(255,255,255,0.2); color: #fff; }

.form-group { margin-bottom: 1.25rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 8px; }
.required { color: #fca5a5; }
.input {
    width: 100%; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.4); color: white;
    font-size: 0.875rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
}
.input::placeholder { color: rgba(255,255,255,0.4); }
.input:focus { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-error { border-color: #fca5a5 !important; }
.textarea { resize: vertical; min-height: 80px; }
.error-msg { font-size: 0.75rem; color: #fca5a5; margin-top: 6px; }

.modal-actions {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 1.5rem; padding-top: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}
</style>