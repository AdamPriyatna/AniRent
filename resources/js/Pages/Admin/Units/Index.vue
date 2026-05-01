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
                            <th>Harga Sewa</th>
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
                            <td class="unit-price">Rp {{ (unit.harga_sewa ?? 0).toLocaleString('id-ID') }}</td>
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
.page-title { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.page-sub { font-size: 0.875rem; color: rgba(255,255,255,0.7); }

.alert-success {
    background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #86efac; margin-bottom: 1rem;
    backdrop-filter: blur(8px);
}

.filter-bar {
    display: flex; gap: 10px; margin-bottom: 1.25rem; flex-wrap: wrap;
}
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

.code-badge {
    background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.75rem; font-weight: 600;
    padding: 4px 8px; border-radius: 6px;
    font-family: monospace; white-space: nowrap;
    border: 1px solid rgba(168, 85, 247, 0.3);
}
.unit-name { font-weight: 600; color: #fff; }
.unit-desc { font-size: 0.75rem; color: rgba(255,255,255,0.5); margin-top: 4px; line-height: 1.4; }
.unit-price { font-weight: 600; color: #d8b4fe; }
.text-muted { color: rgba(255,255,255,0.6); }

.tags { display: flex; gap: 6px; flex-wrap: wrap; }
.tag {
    background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.9);
    font-size: 0.6875rem; padding: 3px 8px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.05);
}

.pill { font-size: 0.6875rem; padding: 4px 10px; border-radius: 10px; font-weight: 600; border: 1px solid transparent; }
.pill-teal  { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.pill-amber { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border-color: rgba(245, 158, 11, 0.3); }
.pill-coral { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
.pill-gray  { background: rgba(255, 255, 255, 0.1); color: rgba(255,255,255,0.7); }

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
.btn-delete:hover { background: rgba(239, 68, 68, 0.4); color: #fff; }

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

.pagination { display: flex; gap: 6px; margin-top: 1.5rem; flex-wrap: wrap; }
.page-btn {
    padding: 8px 14px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3);
    font-size: 0.8125rem; color: rgba(255,255,255,0.7); text-decoration: none;
    transition: 0.2s; backdrop-filter: blur(8px);
}
.page-btn:hover:not(.disabled) { background: rgba(255,255,255,0.1); color: #fff; border-color: rgba(255,255,255,0.3); }
.page-btn.active { background: linear-gradient(135deg, #a855f7, #ec4899); color: #fff; border: none; box-shadow: 0 0 10px rgba(168,85,247,0.4); }
.page-btn.disabled { color: rgba(255,255,255,0.3); pointer-events: none; border-color: transparent; }
</style>