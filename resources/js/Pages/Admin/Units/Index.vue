<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'


const props = defineProps({
    units: Object,
    filters: Object,
    categories: Array,
})

const search = ref(props.filters?.search ?? '')
const kategori = ref(props.filters?.kategori ?? '')

function doSearch() {
    router.get(route('admin.units.index'), {
        search: search.value,
        kategori: kategori.value,
    }, { preserveState: true, replace: true })
}

function resetFilter() {
    search.value = ''
    kategori.value = ''
    doSearch()
}

function hapusUnit(id) {
    if (confirm('Yakin ingin menghapus unit ini?')) {
        router.delete(route('admin.units.destroy', id))
    }
}

const statusClass = (status) => {
    const map = {
        tersedia: 'pill-teal',
        dipinjam: 'pill-amber',
        rusak: 'pill-coral',
    }
    return map[status] ?? 'pill-gray'
}

const statusLabel = (status) => {
    const map = { tersedia: 'Tersedia', dipinjam: 'Dipinjam', rusak: 'Rusak' }
    return map[status] ?? status
}
</script>

<template>
    <Head title="Manajemen Unit" />

        <div class="page">

            <!-- Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Manajemen Unit</h1>
                    <p class="page-sub">Kelola semua unit yang tersedia di AniRent</p>
                </div>
                <Link :href="route('admin.units.create')" class="btn-primary">
                    + Tambah Unit
                </Link>
            </div>

            <!-- Flash message -->
            <div v-if="$page.props.flash?.success" class="alert-success">
                {{ $page.props.flash.success }}
            </div>

            <!-- Filter & Search -->
            <div class="filter-bar">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama unit..."
                    class="input-search"
                    @keyup.enter="doSearch"
                />
                <select v-model="kategori" class="input-select" @change="doSearch">
                    <option value="">Semua Kategori</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.nama_kategori }}
                    </option>
                </select>
                <button class="btn-primary" @click="doSearch">Cari</button>
                <button v-if="search || kategori" class="btn-outline" @click="resetFilter">Reset</button>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode Unit</th>
                            <th>Nama Unit</th>
                            <th>Kategori</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="units.data.length === 0">
                            <td colspan="6" class="empty-row">Tidak ada unit ditemukan</td>
                        </tr>
                        <tr v-for="unit in units.data" :key="unit.id">
                            <td>
                                <span class="code-badge">{{ unit.kode_unit }}</span>
                            </td>
                            <td>
                                <div class="unit-name">{{ unit.nama_unit }}</div>
                                <div class="unit-desc">{{ unit.deskripsi?.substring(0, 50) }}{{ unit.deskripsi?.length > 50 ? '...' : '' }}</div>
                            </td>
                            <td>
                                <div class="tags">
                                    <span v-for="cat in unit.categories" :key="cat.id" class="tag">
                                        {{ cat.nama_kategori }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-muted">{{ unit.kondisi ?? '-' }}</td>
                            <td>
                                <span :class="['pill', statusClass(unit.status)]">
                                    {{ statusLabel(unit.status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <Link :href="route('admin.units.edit', unit.id)" class="btn-edit">Edit</Link>
                                    <button class="btn-delete" @click="hapusUnit(unit.id)">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="units.last_page > 1">
                <Link
                    v-for="link in units.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="page-btn"
                    :class="{ active: link.active, disabled: !link.url }"
                    v-html="link.label"
                />
            </div>

        </div>

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

.filter-bar {
    display: flex; gap: 10px; margin-bottom: 1.25rem; flex-wrap: wrap;
}
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

.code-badge {
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 600;
    padding: 3px 8px; border-radius: 6px;
    font-family: monospace;
}
.unit-name { font-weight: 500; color: #1a1a2e; }
.unit-desc { font-size: 0.75rem; color: #aaa; margin-top: 2px; }
.text-muted { color: #888; }

.tags { display: flex; gap: 4px; flex-wrap: wrap; }
.tag {
    background: #F1EFE8; color: #5F5E5A;
    font-size: 0.6875rem; padding: 2px 7px; border-radius: 8px;
}

.pill { font-size: 0.6875rem; padding: 3px 10px; border-radius: 10px; font-weight: 500; }
.pill-teal  { background: #E1F5EE; color: #0F6E56; }
.pill-amber { background: #FAEEDA; color: #633806; }
.pill-coral { background: #FAECE7; color: #993C1D; }
.pill-gray  { background: #F1EFE8; color: #5F5E5A; }

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
.btn-delete:hover { background: #F5C4B3; }

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

.pagination { display: flex; gap: 4px; margin-top: 1.25rem; flex-wrap: wrap; }
.page-btn {
    padding: 6px 11px; border-radius: 6px;
    border: 0.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #534AB7; text-decoration: none;
}
.page-btn.active { background: #534AB7; color: #fff; border-color: #534AB7; }
.page-btn.disabled { color: #ccc; pointer-events: none; }
</style>