<script setup>
import { ref, reactive, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    users:   Object,
    filters: Object,
})

const page = usePage()

// ─── Search & Filter ───────────────────────────────────────────────────────────
const search = ref(props.filters?.search ?? '')
const role   = ref(props.filters?.role   ?? '')

function doSearch() {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: role.value,
    }, { preserveState: true, replace: true })
}

function resetFilter() {
    search.value = ''
    role.value   = ''
    doSearch()
}

// ─── Add/Edit Modal ────────────────────────────────────────────────────────────
const showModal    = ref(false)
const isEditing    = ref(false)
const isSubmitting = ref(false)

const form = reactive({
    id:                   null,
    name:                 '',
    email:                '',
    role:                 'anggota',
    password:             '',
    password_confirmation: '',
})

const errors = reactive({})

function clearErrors() {
    Object.keys(errors).forEach(k => delete errors[k])
}

function openCreate() {
    isEditing.value = false
    form.id                   = null
    form.name                 = ''
    form.email                = ''
    form.role                 = 'anggota'
    form.password             = ''
    form.password_confirmation = ''
    clearErrors()
    showModal.value = true
}

function openEdit(user) {
    isEditing.value = true
    form.id                   = user.id
    form.name                 = user.name
    form.email                = user.email
    form.role                 = user.role
    form.password             = ''
    form.password_confirmation = ''
    clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

function submitForm() {
    clearErrors()
    isSubmitting.value = true

    if (isEditing.value) {
        router.put(route('admin.users.update', form.id), {
            name:                 form.name,
            email:                form.email,
            role:                 form.role,
            password:             form.password,
            password_confirmation: form.password_confirmation,
        }, {
            onSuccess: () => { showModal.value = false },
            onError: (e)  => { Object.assign(errors, e) },
            onFinish: ()  => { isSubmitting.value = false },
        })
    } else {
        router.post(route('admin.users.store'), {
            name:                 form.name,
            email:                form.email,
            role:                 form.role,
            password:             form.password,
            password_confirmation: form.password_confirmation,
        }, {
            onSuccess: () => { showModal.value = false },
            onError: (e)  => { Object.assign(errors, e) },
            onFinish: ()  => { isSubmitting.value = false },
        })
    }
}

// ─── Delete Confirm Modal ──────────────────────────────────────────────────────
const showDeleteModal  = ref(false)
const userToDelete     = ref(null)
const isDeleting       = ref(false)

function confirmDelete(user) {
    userToDelete.value  = user
    showDeleteModal.value = true
}

function cancelDelete() {
    showDeleteModal.value = false
    userToDelete.value  = null
}

function doDelete() {
    if (!userToDelete.value) return
    isDeleting.value = true
    router.delete(route('admin.users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false
            userToDelete.value  = null
        },
        onFinish: () => { isDeleting.value = false },
    })
}

// ─── Helpers ───────────────────────────────────────────────────────────────────
const roleLabel = (r) => r === 'admin' ? 'Admin' : 'Anggota'
const roleClass = (r) => r === 'admin' ? 'pill-purple' : 'pill-teal'

function avatarInitials(name) {
    return (name || '')
        .split(' ')
        .map(w => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
}

const avatarColors = ['#6c63ff', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6']
function avatarColor(name) {
    return avatarColors[(name?.charCodeAt(0) ?? 0) % avatarColors.length]
}
</script>

<template>
    <Head title="Manajemen Anggota" />

    <div class="page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Manajemen Anggota</h1>
                <p class="page-sub">Kelola semua pengguna & anggota AniRent</p>
            </div>
            <button class="btn-primary" @click="openCreate">+ Tambah Anggota</button>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">
            {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="alert-error">
            {{ $page.props.flash.error }}
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama atau email..."
                class="input-search"
                @keyup.enter="doSearch"
            />
            <select v-model="role" class="input-select" @change="doSearch">
                <option value="">Semua Role</option>
                <option value="admin">Admin</option>
                <option value="anggota">Anggota</option>
            </select>
            <button class="btn-primary" @click="doSearch">Cari</button>
            <button v-if="search || role" class="btn-outline" @click="resetFilter">Reset</button>
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="users.data.length === 0">
                        <td colspan="6" class="empty-row">Tidak ada anggota ditemukan</td>
                    </tr>
                    <tr v-for="(user, i) in users.data" :key="user.id">
                        <td class="text-muted">{{ users.from + i }}</td>
                        <td>
                            <div class="user-cell">
                                <div
                                    class="avatar"
                                    :style="{ background: avatarColor(user.name) }"
                                >{{ avatarInitials(user.name) }}</div>
                                <span class="user-name">{{ user.name }}</span>
                            </div>
                        </td>
                        <td class="text-muted">{{ user.email }}</td>
                        <td>
                            <span :class="['pill', roleClass(user.role)]">
                                {{ roleLabel(user.role) }}
                            </span>
                        </td>
                        <td class="text-muted">
                            {{ new Date(user.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-edit" @click="openEdit(user)">Edit</button>
                                <button
                                    class="btn-delete"
                                    @click="confirmDelete(user)"
                                    :disabled="user.id === $page.props.auth.user.id"
                                    :title="user.id === $page.props.auth.user.id ? 'Tidak dapat menghapus akun sendiri' : ''"
                                >Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="users.last_page > 1">
            <a
                v-for="link in users.links"
                :key="link.label"
                :href="link.url ?? '#'"
                class="page-btn"
                :class="{ active: link.active, disabled: !link.url }"
                v-html="link.label"
                @click.prevent="link.url && router.get(link.url)"
            />
        </div>
    </div>

    <!-- ═══════════════════ ADD / EDIT MODAL ═══════════════════ -->
    <Teleport to="body">
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">{{ isEditing ? 'Edit Anggota' : 'Tambah Anggota' }}</h2>
                    <button class="modal-close" @click="closeModal">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="modal-body">

                    <!-- Name -->
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="form-input"
                            :class="{ 'is-error': errors.name }"
                            placeholder="Nama lengkap"
                        />
                        <p v-if="errors.name" class="error-msg">{{ errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label">Email <span class="req">*</span></label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="form-input"
                            :class="{ 'is-error': errors.email }"
                            placeholder="email@contoh.com"
                        />
                        <p v-if="errors.email" class="error-msg">{{ errors.email }}</p>
                    </div>

                    <!-- Role -->
                    <div class="form-group">
                        <label class="form-label">Role <span class="req">*</span></label>
                        <select
                            v-model="form.role"
                            class="form-input"
                            :class="{ 'is-error': errors.role }"
                        >
                            <option value="anggota">Anggota</option>
                            <option value="admin">Admin</option>
                        </select>
                        <p v-if="errors.role" class="error-msg">{{ errors.role }}</p>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label class="form-label">
                            Password <span class="req">*</span>
                            <span v-if="isEditing" class="form-hint">(kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="form-input"
                            :class="{ 'is-error': errors.password }"
                            placeholder="Minimal 8 karakter"
                        />
                        <p v-if="errors.password" class="error-msg">{{ errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span v-if="!isEditing" class="req">*</span></label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="form-input"
                            :class="{ 'is-error': errors.password_confirmation }"
                            placeholder="Ulangi password"
                        />
                        <p v-if="errors.password_confirmation" class="error-msg">{{ errors.password_confirmation }}</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-outline" @click="closeModal">Batal</button>
                        <button type="submit" class="btn-primary" :disabled="isSubmitting">
                            <span v-if="isSubmitting">Menyimpan...</span>
                            <span v-else>{{ isEditing ? 'Simpan Perubahan' : 'Tambah Anggota' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- ═══════════════════ DELETE CONFIRM MODAL ═══════════════════ -->
    <Teleport to="body">
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
            <div class="modal modal-sm">
                <div class="modal-header">
                    <h2 class="modal-title">Hapus Anggota</h2>
                    <button class="modal-close" @click="cancelDelete">✕</button>
                </div>
                <div class="modal-body">
                    <p class="delete-msg">
                        Yakin ingin menghapus anggota
                        <strong>{{ userToDelete?.name }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="modal-footer">
                        <button class="btn-outline" @click="cancelDelete">Batal</button>
                        <button class="btn-danger" :disabled="isDeleting" @click="doDelete">
                            <span v-if="isDeleting">Menghapus...</span>
                            <span v-else>Ya, Hapus</span>
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
.text-muted { color: #888; }

.user-cell { display: flex; align-items: center; gap: 10px; }
.avatar {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: white; flex-shrink: 0;
}
.user-name { font-weight: 500; color: #1a1a2e; }

.pill { font-size: 0.6875rem; padding: 3px 10px; border-radius: 10px; font-weight: 500; }
.pill-teal   { background: #E1F5EE; color: #0F6E56; }
.pill-purple { background: #EEEDFE; color: #534AB7; }

.action-btns { display: flex; gap: 6px; }
.btn-edit {
    padding: 5px 12px; border-radius: 6px;
    background: #EEEDFE; color: #534AB7;
    font-size: 0.75rem; font-weight: 500;
    border: none; cursor: pointer;
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
.btn-primary:hover:not(:disabled) { background: #3C3489; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

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

/* ─── Modal ─── */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }

.modal {
    background: #fff; border-radius: 14px;
    width: 100%; max-width: 500px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    animation: slideUp 0.2s ease;
}
.modal-sm { max-width: 400px; }
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem 0;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; }
.modal-close {
    background: none; border: none; cursor: pointer;
    color: #aaa; font-size: 1rem; padding: 4px;
}
.modal-close:hover { color: #555; }

.modal-body { padding: 1.25rem 1.5rem; }

.form-group { margin-bottom: 1rem; }
.form-label { display: block; font-size: 0.8125rem; font-weight: 500; color: #3a3a4a; margin-bottom: 6px; }
.req { color: #e53935; }
.form-hint { font-weight: 400; color: #aaa; font-size: 0.75rem; }
.form-input {
    width: 100%; padding: 9px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none; box-sizing: border-box;
    transition: border-color 0.15s;
}
.form-input:focus { border-color: #534AB7; }
.form-input.is-error { border-color: #e53935; }
.error-msg { color: #e53935; font-size: 0.75rem; margin-top: 4px; }

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 1.25rem; padding-top: 1rem;
    border-top: 0.5px solid #f0ece6;
}

.delete-msg { font-size: 0.875rem; color: #3a3a4a; line-height: 1.6; }
</style>
