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
.page-title { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.page-sub { font-size: 0.875rem; color: #888; }

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

.filter-bar { display: flex; gap: 10px; margin-bottom: 1.25rem; flex-wrap: wrap; }
.input-search {
    flex: 1; min-width: 200px; padding: 8px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none;
}
.input-search:focus { border-color: #534AB7; }
.input-select {
    padding: 8px 12px; border: 0.5px solid #d0ccc4;
    border-radius: 8px; font-size: 0.875rem; outline: none;
    background: #fff; cursor: pointer;
}
.input-select:focus { border-color: #534AB7; }

.table-wrap { border: 0.5px solid #e0ddd5; border-radius: 12px; overflow: hidden; }
.table { width: 100%; border-collapse: collapse; }
.table thead tr { background: #F8F7F4; }
.table th {
    padding: 11px 14px; text-align: left;
    font-size: 0.75rem; font-weight: 600; color: #888;
    border-bottom: 0.5px solid #e0ddd5; white-space: nowrap;
}
.table td {
    padding: 12px 14px; font-size: 0.8125rem;
    border-bottom: 0.5px solid #f0ece6; vertical-align: middle;
}
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover { background: #FAFAF8; }
.empty-row { text-align: center; color: #aaa; padding: 32px !important; }
.text-muted { color: #aaa; font-size: 0.75rem; }

/* Bundle thumb */
.bundle-thumb {
    width: 52px; height: 52px; border-radius: 10px;
    overflow: hidden; border: 0.5px solid #e0ddd5; background: #f8f7f4;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.bundle-thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder { font-size: 22px; }

.bundle-name { font-weight: 500; color: #1a1a2e; }
.bundle-desc { font-size: 0.75rem; color: #aaa; margin-top: 2px; }

/* Unit tags */
.unit-tags { display: flex; gap: 4px; flex-wrap: wrap; }
.unit-tag {
    background: #EEEDFE; color: #534AB7;
    font-size: 0.6875rem; font-weight: 600;
    padding: 2px 7px; border-radius: 6px; font-family: monospace;
}
.unit-tag.more { background: #F1EFE8; color: #888; font-family: sans-serif; font-weight: 400; }

/* Price */
.price { font-weight: 600; color: #1a1a2e; font-size: 0.875rem; }
.price-sub { font-size: 0.6875rem; color: #aaa; }

/* Pills */
.pill { font-size: 0.6875rem; padding: 3px 10px; border-radius: 10px; font-weight: 500; }
.pill-teal  { background: #E1F5EE; color: #0F6E56; }
.pill-amber { background: #FAEEDA; color: #633806; }

/* Actions */
.action-btns { display: flex; gap: 6px; }
.btn-edit {
    padding: 5px 12px; border-radius: 6px;
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 500;
    text-decoration: none; border: none; cursor: pointer;
}
.btn-edit:hover { background: #CECBF6; }
.btn-delete {
    padding: 5px 12px; border-radius: 6px;
    background: #FAECE7; color: #993C1D;
    font-size: 0.75rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-delete:hover:not(:disabled) { background: #F5C4B3; }
.btn-delete:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-primary {
    padding: 8px 16px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    text-decoration: none; border: none; cursor: pointer; white-space: nowrap;
}
.btn-primary:hover { background: #3C3489; }

.btn-outline {
    padding: 8px 16px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.8125rem; font-weight: 500;
    cursor: pointer; white-space: nowrap;
}
.btn-outline:hover { background: #EEEDFE; }

.btn-danger {
    padding: 8px 16px; border-radius: 8px;
    background: #e53935; color: #fff;
    font-size: 0.8125rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-danger:hover:not(:disabled) { background: #c62828; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

.pagination { display: flex; gap: 4px; margin-top: 1.25rem; flex-wrap: wrap; }
.page-btn {
    padding: 6px 11px; border-radius: 6px;
    border: 0.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #534AB7; text-decoration: none; cursor: pointer;
}
.page-btn.active { background: #534AB7; color: #fff; border-color: #534AB7; }
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
    background: #fff; border-radius: 14px;
    width: 100%; max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    animation: slideUp 0.2s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem 0;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1rem; }
.modal-close:hover { color: #555; }
.modal-body { padding: 1rem 1.5rem 1.5rem; }
.delete-msg { font-size: 0.875rem; color: #3a3a4a; line-height: 1.6; margin-bottom: 1.25rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
</style>
