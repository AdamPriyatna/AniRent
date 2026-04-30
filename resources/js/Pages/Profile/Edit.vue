<script setup>
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    user:    Object,
    profile: Object,
})

// ─── Avatar ──────────────────────────────────────────────────────────────────
const avatarColors = ['#6c63ff','#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6']
const avatarColor  = computed(() =>
    avatarColors[(props.user?.name?.charCodeAt(0) ?? 0) % avatarColors.length]
)
const initials = computed(() =>
    (props.user?.name || '').split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase()
)

// ─── Section tabs ─────────────────────────────────────────────────────────────
const activeSection = ref('info')

// ─── Form 1: Informasi Akun (name, email) ────────────────────────────────────
const infoForm = useForm({
    name:  props.user?.name  ?? '',
    email: props.user?.email ?? '',
})

function submitInfo() {
    infoForm.patch(route('profile.update'), {
        onSuccess: () => infoForm.reset('password'),
    })
}

// ─── Form 2: Detail Profil (foto, alamat, no_telepon, nim_nip) ───────────────
const fotoPreview   = ref(null)
const existingFoto  = ref(props.profile?.foto ?? null)

const detailForm = useForm({
    no_telepon: props.profile?.no_telepon ?? '',
    nim_nip:    props.profile?.nim_nip    ?? '',
    alamat:     props.profile?.alamat     ?? '',
    foto:       null,
    hapus_foto: '0',
    _method:    'PATCH',
})

function onFotoChange(e) {
    const file = e.target.files[0]
    if (!file) return
    detailForm.foto    = file
    detailForm.hapus_foto = '0'
    fotoPreview.value  = URL.createObjectURL(file)
    existingFoto.value = null
}

function removeFoto() {
    fotoPreview.value      = null
    detailForm.foto        = null
    detailForm.hapus_foto  = '1'
    existingFoto.value     = null
}

function submitDetail() {
    detailForm.post(route('profile.detail'), { 
        forceFormData: true,
        onSuccess: () => {
            fotoPreview.value = null
            existingFoto.value = props.profile?.foto
        }
    })
}

// ─── Form 3: Ganti Password ───────────────────────────────────────────────────
const pwForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
})

function submitPassword() {
    pwForm.patch(route('profile.password'), {
        onSuccess: () => pwForm.reset(),
    })
}

// ─── Form 4: Hapus Akun ───────────────────────────────────────────────────────
const showDeleteModal  = ref(false)
const deleteForm       = useForm({ password: '' })

function doDelete() {
    deleteForm.delete(route('profile.destroy'))
}

// ─── Helpers ─────────────────────────────────────────────────────────────────
const currentFotoSrc = computed(() => {
    if (fotoPreview.value)  return fotoPreview.value
    if (existingFoto.value) return `/storage/${existingFoto.value}`
    return null
})

const sections = [
    { key: 'info',     label: 'Informasi Akun',  icon: '👤' },
    { key: 'detail',   label: 'Detail Profil',   icon: '📋' },
    { key: 'password', label: 'Ganti Password',  icon: '🔒' },
]
</script>

<template>
    <Head title="Profil Saya" />

    <div class="page">

        <!-- Header with avatar -->
        <div class="profile-header">
            <div class="avatar-wrap">
                <div v-if="currentFotoSrc" class="avatar-img-wrap">
                    <img :src="currentFotoSrc" alt="Foto Profil" class="avatar-img" />
                </div>
                <div v-else class="avatar-placeholder" :style="{ background: avatarColor }">
                    {{ initials }}
                </div>
            </div>
            <div class="header-info">
                <h1 class="page-title">{{ user?.name }}</h1>
                <p class="page-sub">{{ user?.email }}</p>
                <span class="role-badge">{{ user?.role === 'admin' ? 'Administrator' : 'Anggota' }}</span>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="alert-success">✓ {{ $page.props.flash.success }}</div>

        <!-- Section Tabs -->
        <div class="section-tabs">
            <button
                v-for="s in sections"
                :key="s.key"
                class="section-tab"
                :class="{ 'section-tab-active': activeSection === s.key }"
                @click="activeSection = s.key"
            >
                <span>{{ s.icon }}</span> {{ s.label }}
            </button>
        </div>

        <!-- ═══ SECTION 1: Informasi Akun ═══ -->
        <div v-show="activeSection === 'info'" class="form-card">
            <h2 class="card-title">Informasi Akun</h2>
            <p class="card-sub">Perbarui nama dan alamat email akun Anda</p>

            <form @submit.prevent="submitInfo" class="form-body">
                <div class="form-group">
                    <label class="label">Nama Lengkap <span class="req">*</span></label>
                    <input
                        v-model="infoForm.name"
                        type="text"
                        class="input"
                        :class="{ 'input-error': infoForm.errors.name }"
                        placeholder="Nama lengkap Anda"
                    />
                    <p v-if="infoForm.errors.name" class="error-msg">{{ infoForm.errors.name }}</p>
                </div>

                <div class="form-group">
                    <label class="label">Email <span class="req">*</span></label>
                    <input
                        v-model="infoForm.email"
                        type="email"
                        class="input"
                        :class="{ 'input-error': infoForm.errors.email }"
                        placeholder="email@contoh.com"
                    />
                    <p v-if="infoForm.errors.email" class="error-msg">{{ infoForm.errors.email }}</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="infoForm.processing">
                        {{ infoForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- ═══ SECTION 2: Detail Profil ═══ -->
        <div v-show="activeSection === 'detail'" class="form-card">
            <h2 class="card-title">Detail Profil</h2>
            <p class="card-sub">Informasi tambahan untuk profil Anda</p>

            <form @submit.prevent="submitDetail" class="form-body">

                <!-- Foto profil -->
                <div class="form-group">
                    <label class="label">Foto Profil</label>
                    <div class="foto-area">
                        <div v-if="currentFotoSrc" class="foto-preview-wrap">
                            <img :src="currentFotoSrc" alt="Foto Profil" class="foto-preview-img" />
                            <button type="button" class="foto-remove" @click="removeFoto">✕</button>
                        </div>
                        <label v-else class="foto-upload">
                            <input type="file" accept="image/*" @change="onFotoChange" style="display:none" />
                            <span class="upload-icon">📷</span>
                            <span class="upload-text">Klik untuk upload foto</span>
                            <span class="upload-hint">JPG, PNG maks 2MB</span>
                        </label>
                    </div>
                    <p v-if="detailForm.errors.foto" class="error-msg">{{ detailForm.errors.foto }}</p>
                </div>

                <div class="two-col">
                    <div class="form-group">
                        <label class="label">No. Telepon</label>
                        <input
                            v-model="detailForm.no_telepon"
                            type="tel"
                            class="input"
                            :class="{ 'input-error': detailForm.errors.no_telepon }"
                            placeholder="08xxxxxxxxxx"
                            maxlength="20"
                        />
                        <p v-if="detailForm.errors.no_telepon" class="error-msg">{{ detailForm.errors.no_telepon }}</p>
                    </div>

                    <div class="form-group">
                        <label class="label">NIM / NIP</label>
                        <input
                            v-model="detailForm.nim_nip"
                            type="text"
                            class="input"
                            :class="{ 'input-error': detailForm.errors.nim_nip }"
                            placeholder="Nomor Induk Mahasiswa / Pegawai"
                            maxlength="50"
                        />
                        <p v-if="detailForm.errors.nim_nip" class="error-msg">{{ detailForm.errors.nim_nip }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Alamat</label>
                    <textarea
                        v-model="detailForm.alamat"
                        class="input textarea"
                        :class="{ 'input-error': detailForm.errors.alamat }"
                        placeholder="Alamat lengkap..."
                        rows="3"
                    ></textarea>
                    <p v-if="detailForm.errors.alamat" class="error-msg">{{ detailForm.errors.alamat }}</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="detailForm.processing">
                        {{ detailForm.processing ? 'Menyimpan...' : 'Simpan Profil' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- ═══ SECTION 3: Ganti Password ═══ -->
        <div v-show="activeSection === 'password'" class="form-card">
            <h2 class="card-title">Ganti Password</h2>
            <p class="card-sub">Pastikan gunakan password yang kuat dan unik</p>

            <form @submit.prevent="submitPassword" class="form-body">
                <div class="form-group">
                    <label class="label">Password Saat Ini <span class="req">*</span></label>
                    <input
                        v-model="pwForm.current_password"
                        type="password"
                        class="input"
                        :class="{ 'input-error': pwForm.errors.current_password }"
                        placeholder="Password yang sekarang"
                        autocomplete="current-password"
                    />
                    <p v-if="pwForm.errors.current_password" class="error-msg">{{ pwForm.errors.current_password }}</p>
                </div>

                <div class="form-group">
                    <label class="label">Password Baru <span class="req">*</span></label>
                    <input
                        v-model="pwForm.password"
                        type="password"
                        class="input"
                        :class="{ 'input-error': pwForm.errors.password }"
                        placeholder="Minimal 8 karakter"
                        autocomplete="new-password"
                    />
                    <p v-if="pwForm.errors.password" class="error-msg">{{ pwForm.errors.password }}</p>
                </div>

                <div class="form-group">
                    <label class="label">Konfirmasi Password Baru <span class="req">*</span></label>
                    <input
                        v-model="pwForm.password_confirmation"
                        type="password"
                        class="input"
                        :class="{ 'input-error': pwForm.errors.password_confirmation }"
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                    />
                    <p v-if="pwForm.errors.password_confirmation" class="error-msg">{{ pwForm.errors.password_confirmation }}</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" :disabled="pwForm.processing">
                        {{ pwForm.processing ? 'Memperbarui...' : 'Perbarui Password' }}
                    </button>
                </div>
            </form>

            <!-- Danger Zone -->
            <div class="danger-zone">
                <div class="danger-info">
                    <div class="danger-title">Hapus Akun</div>
                    <div class="danger-sub">Akun dan semua data Anda akan dihapus permanen.</div>
                </div>
                <button class="btn-danger" @click="showDeleteModal = true">Hapus Akun</button>
            </div>
        </div>
    </div>

    <!-- ═══ DELETE MODAL ═══ -->
    <Teleport to="body">
        <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
            <div class="modal">
                <div class="modal-header">
                    <h2 class="modal-title">Hapus Akun</h2>
                    <button class="modal-close" @click="showDeleteModal = false">✕</button>
                </div>
                <div class="modal-body">
                    <p class="delete-msg">
                        Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                        Semua data Anda termasuk riwayat pinjaman akan dihapus permanen.
                        Masukkan password untuk mengkonfirmasi.
                    </p>
                    <div class="form-group">
                        <label class="label">Password</label>
                        <input
                            v-model="deleteForm.password"
                            type="password"
                            class="input"
                            :class="{ 'input-error': deleteForm.errors.password }"
                            placeholder="Password Anda"
                            autocomplete="current-password"
                        />
                        <p v-if="deleteForm.errors.password" class="error-msg">{{ deleteForm.errors.password }}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-outline" @click="showDeleteModal = false">Batal</button>
                        <button class="btn-danger" :disabled="deleteForm.processing" @click="doDelete">
                            {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus Akun Saya' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.page { max-width: 680px; margin: 0 auto; padding: 2rem 1.5rem; }

/* Profile Header */
.profile-header {
    display: flex; align-items: center; gap: 1.25rem;
    margin-bottom: 1.5rem; flex-wrap: wrap;
}
.avatar-wrap { flex-shrink: 0; }
.avatar-img-wrap {
    width: 72px; height: 72px; border-radius: 50%;
    overflow: hidden; border: 3px solid #EEEDFE;
}
.avatar-img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 700; color: white;
    border: 3px solid rgba(255,255,255,0.3);
}
.header-info { flex: 1; min-width: 0; }
.page-title { font-size: 1.4rem; font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.page-sub   { font-size: 0.875rem; color: #888; margin-bottom: 6px; }
.role-badge {
    display: inline-block; background: #EEEDFE; color: #534AB7;
    font-size: 0.6875rem; font-weight: 600; padding: 3px 10px; border-radius: 10px;
}

.alert-success {
    background: #E1F5EE; border: 0.5px solid #5DCAA5;
    border-radius: 8px; padding: 10px 14px;
    font-size: 0.8125rem; color: #085041; margin-bottom: 1.25rem;
}

/* Section Tabs */
.section-tabs { display: flex; gap: 6px; margin-bottom: 1.25rem; flex-wrap: wrap; }
.section-tab {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px;
    border: 1.5px solid #e0ddd5; background: #fff;
    font-size: 0.8125rem; color: #888; cursor: pointer;
    transition: all 0.15s;
}
.section-tab:hover { border-color: #534AB7; color: #534AB7; }
.section-tab-active { border-color: #534AB7 !important; background: #EEEDFE !important; color: #534AB7 !important; font-weight: 600; }

/* Form Card */
.form-card {
    background: #fff; border: 0.5px solid #e0ddd5;
    border-radius: 14px; padding: 1.75rem;
}
.card-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 4px; }
.card-sub   { font-size: 0.8125rem; color: #aaa; margin-bottom: 1.5rem; }

.form-body { }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 520px) { .two-col { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1.25rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 500; color: #1a1a2e; margin-bottom: 6px; }
.req { color: #D85A30; }

.input {
    width: 100%; padding: 9px 12px;
    border: 0.5px solid #d0ccc4; border-radius: 8px;
    font-size: 0.875rem; outline: none;
    transition: border-color 0.15s; box-sizing: border-box;
}
.input:focus { border-color: #534AB7; }
.input-error { border-color: #D85A30 !important; }
.textarea { resize: vertical; min-height: 80px; }
.error-msg { font-size: 0.75rem; color: #D85A30; margin-top: 4px; }

/* Foto */
.foto-area { margin-bottom: 4px; }
.foto-preview-wrap { position: relative; display: inline-block; }
.foto-preview-img {
    width: 100px; height: 100px; border-radius: 50%;
    object-fit: cover; border: 3px solid #EEEDFE;
}
.foto-remove {
    position: absolute; top: 0; right: 0;
    width: 22px; height: 22px; border-radius: 50%;
    background: #D85A30; color: #fff; border: none;
    font-size: 11px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.foto-upload {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 160px; height: 110px; border: 2px dashed #d0ccc4;
    border-radius: 10px; cursor: pointer; gap: 4px; transition: border-color 0.15s;
}
.foto-upload:hover { border-color: #534AB7; }
.upload-icon { font-size: 24px; }
.upload-text { font-size: 0.75rem; color: #888; }
.upload-hint { font-size: 0.6875rem; color: #bbb; }

/* Danger Zone */
.danger-zone {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; flex-wrap: wrap;
    margin-top: 2rem; padding: 1rem 1.25rem;
    background: #FEF2F2; border: 0.5px solid #FECACA;
    border-radius: 10px;
}
.danger-title { font-size: 0.875rem; font-weight: 600; color: #B91C1C; margin-bottom: 2px; }
.danger-sub   { font-size: 0.8125rem; color: #7F1D1D; }

/* Actions */
.form-actions { display: flex; justify-content: flex-end; margin-top: 0.5rem; }
.btn-primary {
    padding: 9px 22px; border-radius: 8px;
    background: #534AB7; color: #fff;
    font-size: 0.875rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-primary:hover:not(:disabled) { background: #3C3489; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline {
    padding: 8px 18px; border-radius: 8px;
    background: transparent; color: #534AB7;
    border: 1.5px solid #534AB7;
    font-size: 0.875rem; font-weight: 500; cursor: pointer;
}
.btn-outline:hover { background: #EEEDFE; }
.btn-danger {
    padding: 8px 18px; border-radius: 8px;
    background: #e53935; color: #fff;
    font-size: 0.875rem; font-weight: 500;
    border: none; cursor: pointer;
}
.btn-danger:hover:not(:disabled) { background: #c62828; }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem; animation: fadeIn 0.15s ease;
}
@keyframes fadeIn { from { opacity: 0 } to { opacity: 1 } }
.modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 440px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    animation: slideUp 0.2s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) }
    to   { opacity: 1; transform: translateY(0) }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem; border-bottom: 0.5px solid #f0ece6;
}
.modal-title { font-size: 1rem; font-weight: 600; color: #1a1a2e; }
.modal-close { background: none; border: none; cursor: pointer; color: #aaa; font-size: 1rem; }
.modal-close:hover { color: #555; }
.modal-body { padding: 1.25rem 1.5rem; }
.delete-msg { font-size: 0.875rem; color: #3a3a4a; line-height: 1.6; margin-bottom: 1rem; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding-top: 1rem; border-top: 0.5px solid #f0ece6; margin-top: 1rem;
}
</style>
