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
                                <img v-if="user.profile?.foto" :src="`/storage/${user.profile.foto}`" class="avatar" style="object-fit: cover;" />
                                <div v-else class="avatar" :style="{ background: avatarColor(user.name) }">
                                    {{ avatarInitials(user.name) }}
                                </div>
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
    font-size: 0.875rem; outline: none; background: rgba(0,0,0,0.4); color: white;
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
    background: rgba(15, 10, 30, 0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
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
.text-muted { color: rgba(255,255,255,0.5); }

.user-cell { display: flex; align-items: center; gap: 10px; }
.avatar {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: white; flex-shrink: 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}
.user-name { font-weight: 600; color: #fff; }

.pill { font-size: 0.6875rem; padding: 4px 10px; border-radius: 10px; font-weight: 600; border: 1px solid transparent; }
.pill-teal   { background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.3); }
.pill-purple { background: rgba(168, 85, 247, 0.2); color: #d8b4fe; border-color: rgba(168, 85, 247, 0.3); }

.action-btns { display: flex; gap: 6px; }
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
.btn-delete:hover:not(:disabled) { background: rgba(239, 68, 68, 0.4); color: #fff; }
.btn-delete:disabled { opacity: 0.4; cursor: not-allowed; }

.btn-primary {
    padding: 10px 20px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.8125rem; font-weight: 600;
    text-decoration: none; border: none; cursor: pointer; white-space: nowrap; box-shadow: 0 0 15px rgba(168,85,247,0.4); transition: 0.3s;
}
.btn-primary:hover:not(:disabled) { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-outline {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.8125rem; font-weight: 600;
    cursor: pointer; white-space: nowrap; transition: 0.2s; backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }

.btn-danger {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3);
    font-size: 0.8125rem; font-weight: 600;
    border: none; cursor: pointer; transition: 0.2s;
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

/* ─── Modal ─── */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }

.modal {
    background: rgba(20, 12, 45, 0.95); border-radius: 16px; border: 1px solid rgba(255,255,255,0.1);
    width: 100%; max-width: 500px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.6); color: white;
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
.modal-title { font-size: 1.2rem; font-weight: 600; color: #fff; }
.modal-close {
    background: none; border: none; cursor: pointer;
    color: rgba(255,255,255,0.5); font-size: 1.2rem; padding: 4px; transition: 0.2s;
}
.modal-close:hover { color: #fff; }

.modal-body { padding: 1.25rem 1.5rem; }

.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 8px; }
.req { color: #fca5a5; }
.form-hint { font-weight: 400; color: rgba(255,255,255,0.5); font-size: 0.75rem; }
.form-input {
    width: 100%; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.4); color: white;
    font-size: 0.875rem; outline: none; box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-input::placeholder { color: rgba(255,255,255,0.4); }
.form-input:focus { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.form-input.is-error { border-color: #fca5a5; }
.error-msg { color: #fca5a5; font-size: 0.75rem; margin-top: 6px; }

.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    margin-top: 1.5rem; padding-top: 1.25rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.delete-msg { font-size: 0.875rem; color: rgba(255,255,255,0.8); line-height: 1.6; }
</style>
