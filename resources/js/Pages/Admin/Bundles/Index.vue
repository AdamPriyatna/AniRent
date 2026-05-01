<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bundles: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

function doSearch() {
    router.get(route('admin.bundles.index'), {
        search: search.value,
        status: status.value,
    }, { preserveState: true, replace: true })
}

function resetFilter() {
    search.value = ''
    status.value = ''
    doSearch()
}

// ─── Delete ─────────────────────────────────────────────────────────────────
const showDeleteModal = ref(false)
const bundleToDelete  = ref(null)
const isDeleting      = ref(false)

function confirmDelete(bundle) {
    bundleToDelete.value  = bundle
    showDeleteModal.value = true
}

function cancelDelete() {
    showDeleteModal.value = false
    bundleToDelete.value  = null
}

function doDelete() {
    if (!bundleToDelete.value) return
    isDeleting.value = true
    router.delete(route('admin.bundles.destroy', bundleToDelete.value.id), {
        onSuccess: () => { showDeleteModal.value = false; bundleToDelete.value = null },
        onFinish:  () => { isDeleting.value = false },
    })
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
const statusClass = (s) => s === 'tersedia' ? 'pill-teal' : 'pill-amber'
const statusLabel = (s) => s === 'tersedia' ? 'Tersedia' : 'Disewa'

function formatRupiah(val) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <Head title="Manajemen Bundle" />

    <div class="page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Manajemen Bundle</h1>
                <p class="page-sub">Kelola paket sewa (bundle) unit AniRent</p>
            </div>
            <Link :href="route('admin.bundles.create')" class="btn-primary">
                + Tambah Bundle
            </Link>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">{{ $page.props.flash.success }}</div>
        <div v-if="$page.props.flash?.error"   class="alert-error">{{ $page.props.flash.error }}</div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama bundle..."
                class="input-search"
                @keyup.enter="doSearch"
            />
            <select v-model="status" class="input-select" @change="doSearch">
                <option value="">Semua Status</option>
                <option value="tersedia">Tersedia</option>
                <option value="disewa">Disewa</option>
            </select>
            <button class="btn-primary" @click="doSearch">Cari</button>
            <button v-if="search || status" class="btn-outline" @click="resetFilter">Reset</button>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Bundle</th>
                        <th>Unit Termasuk</th>
                        <th>Harga/Hari</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="bundles.data.length === 0">
                        <td colspan="6" class="empty-row">Tidak ada bundle ditemukan</td>
                    </tr>
                    <tr v-for="bundle in bundles.data" :key="bundle.id">
                        <!-- Foto -->
                        <td>
                            <div class="bundle-thumb">
                                <img
                                    v-if="bundle.foto"
                                    :src="`/storage/${bundle.foto}`"
                                    :alt="bundle.nama_bundle"
                                />
                                <div v-else class="thumb-placeholder">
                                    <span>📦</span>
                                </div>
                            </div>
                        </td>

                        <!-- Nama & Deskripsi -->
                        <td>
                            <div class="bundle-name">{{ bundle.nama_bundle }}</div>
                            <div class="bundle-desc">
                                {{ bundle.deskripsi?.substring(0, 55) }}{{ bundle.deskripsi?.length > 55 ? '...' : '' }}
                            </div>
                        </td>

                        <!-- Units -->
                        <td>
                            <div class="unit-tags">
                                <span v-for="unit in bundle.units.slice(0, 3)" :key="unit.id" class="unit-tag">
                                    {{ unit.kode_unit }}
                                </span>
                                <span v-if="bundle.units.length > 3" class="unit-tag more">
                                    +{{ bundle.units.length - 3 }}
                                </span>
                                <span v-if="bundle.units.length === 0" class="text-muted">—</span>
                            </div>
                        </td>

                        <!-- Harga -->
                        <td>
                            <span class="price">{{ formatRupiah(bundle.harga_per_hari) }}</span>
                            <div class="price-sub">per hari</div>
                        </td>

                        <!-- Status -->
                        <td>
                            <span :class="['pill', statusClass(bundle.status)]">
                                {{ statusLabel(bundle.status) }}
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td>
                            <div class="action-btns">
                                <Link :href="route('admin.bundles.edit', bundle.id)" class="btn-edit">Edit</Link>
                                <button
                                    class="btn-delete"
                                    @click="confirmDelete(bundle)"
                                    :disabled="bundle.status === 'disewa'"
                                    :title="bundle.status === 'disewa' ? 'Tidak dapat dihapus saat disewa' : ''"
                                >Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="bundles.last_page > 1">
            <a
                v-for="link in bundles.links"
                :key="link.label"
                :href="link.url ?? '#'"
                class="page-btn"
                :class="{ active: link.active, disabled: !link.url }"
                v-html="link.label"
                @click.prevent="link.url && router.get(link.url)"
            />
        </div>
    </div>

    <!-- Delete Modal -->
    <Teleport to="body">
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Hapus Bundle</h2>
                    <button class="modal-close" @click="cancelDelete">✕</button>
                </div>
                <div class="modal-body">
                    <p class="delete-msg">
                        Yakin ingin menghapus bundle
                        <strong>{{ bundleToDelete?.nama_bundle }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="modal-footer">
                        <button class="btn-outline" @click="cancelDelete">Batal</button>
                        <button class="btn-danger" :disabled="isDeleting" @click="doDelete">
                            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.page { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; }

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

.filter-bar { display: flex; gap: 10px; margin-bottom: 1.25rem; flex-wrap: wrap; }
.input-search {
    flex: 1; min-width: 200px; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;
    font-size: 0.875rem; outline: none;
    background: rgba(0,0,0,0.4); color: white;
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

.table-wrap { 
    border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; overflow: hidden; 
    background: rgba(15, 10, 30, 0.75);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
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
.text-muted { color: rgba(255,255,255,0.5); font-size: 0.75rem; }

/* Bundle thumb */
.bundle-thumb {
    width: 52px; height: 52px; border-radius: 10px;
    overflow: hidden; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.bundle-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder { font-size: 22px; }

.bundle-name { font-weight: 600; color: #fff; }
.bundle-desc { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 4px; line-height: 1.4; }

/* Unit tags */
.unit-tags { display: flex; gap: 4px; flex-wrap: wrap; }
.unit-tag {
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.6875rem; font-weight: 600;
    padding: 2px 7px; border-radius: 6px; font-family: monospace;
    border: 1px solid rgba(168, 85, 247, 0.3);
}
.unit-tag.more { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8); font-family: sans-serif; font-weight: 400; border: 1px solid rgba(255,255,255,0.05); }

/* Price */
.price { font-weight: 600; color: #d8b4fe; font-size: 0.875rem; }
.price-sub { font-size: 0.6875rem; color: rgba(255,255,255,0.5); }

/* Pills */
.pill { font-size: 0.6875rem; padding: 4px 10px; border-radius: 10px; font-weight: 600; border: 1px solid transparent; }
.pill-teal  { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.pill-amber { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border-color: rgba(245, 158, 11, 0.3); }

/* Actions */
.action-btns { display: flex; gap: 6px; }
.btn-edit {
    padding: 6px 12px; border-radius: 6px;
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.75rem; font-weight: 600;
    text-decoration: none; border: 1px solid rgba(168, 85, 247, 0.3); cursor: pointer; transition: 0.2s;
}
.btn-edit:hover { background: rgba(168, 85, 247, 0.4); color: #fff; }
.btn-delete {
    padding: 6px 12px; border-radius: 6px;
    background: rgba(239, 68, 68, 0.2); color: #fca5a5;
    font-size: 0.75rem; font-weight: 600;
    border: 1px solid rgba(239, 68, 68, 0.3); cursor: pointer; transition: 0.2s;
}
.btn-delete:hover:not(:disabled) { background: rgba(239, 68, 68, 0.4); color: #fff; }
.btn-delete:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-primary {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.8125rem; font-weight: 600;
    text-decoration: none; border: none; cursor: pointer; white-space: nowrap;
    box-shadow: 0 0 15px rgba(168,85,247,0.4); transition: 0.3s;
}
.btn-primary:hover { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }

.btn-outline {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.8125rem; font-weight: 600;
    cursor: pointer; white-space: nowrap; transition: 0.2s;
    backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }

.btn-danger {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3);
    font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-danger:hover:not(:disabled) { background: rgba(239, 68, 68, 0.4); color: #fff; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

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
    background: rgba(20, 12, 45, 0.95); border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.1);
    width: 100%; max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    animation: slideUp 0.2s ease; color: white;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem 0;
}
.modal-title { font-size: 1.2rem; font-weight: 600; color: #fff; }
.modal-close { background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.5); font-size: 1.2rem; transition: 0.2s; }
.modal-close:hover { color: #fff; }
.modal-body { padding: 1rem 1.5rem 1.5rem; }
.delete-msg { font-size: 0.875rem; color: rgba(255,255,255,0.8); line-height: 1.6; margin-bottom: 1.25rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
</style>
