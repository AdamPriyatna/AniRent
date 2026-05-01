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
    margin-bottom: 2rem; flex-wrap: wrap;
}
.avatar-wrap { flex-shrink: 0; }
.avatar-img-wrap {
    width: 80px; height: 80px; border-radius: 50%;
    overflow: hidden; border: 3px solid rgba(168, 85, 247, 0.4);
    box-shadow: 0 0 15px rgba(168, 85, 247, 0.5);
}
.avatar-img { width: 100%; height: 100%; object-fit: cover; }
.avatar-placeholder {
    width: 80px; height: 80px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 700; color: white;
    border: 3px solid rgba(255,255,255,0.2); box-shadow: 0 0 15px rgba(0,0,0,0.5);
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}
.header-info { flex: 1; min-width: 0; }
.page-title { font-size: 1.75rem; font-weight: 700; color: #fff; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
.page-sub   { font-size: 0.875rem; color: rgba(255,255,255,0.7); margin-bottom: 8px; }
.role-badge {
    display: inline-block; background: rgba(168, 85, 247, 0.2); color: #d8b4fe;
    font-size: 0.6875rem; font-weight: 600; padding: 4px 12px; border-radius: 12px;
    border: 1px solid rgba(168, 85, 247, 0.3); box-shadow: 0 0 10px rgba(168,85,247,0.2);
}

.alert-success {
    background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);
    border-radius: 8px; padding: 12px 16px; backdrop-filter: blur(8px);
    font-size: 0.8125rem; color: #86efac; margin-bottom: 1.5rem; box-shadow: 0 0 10px rgba(34,197,94,0.1);
}

/* Section Tabs */
.section-tabs { display: flex; gap: 8px; margin-bottom: 1.5rem; flex-wrap: wrap; }
.section-tab {
    display: flex; align-items: center; gap: 6px;
    padding: 10px 18px; border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.15); background: rgba(15, 10, 30, 0.6);
    font-size: 0.875rem; color: rgba(255,255,255,0.7); cursor: pointer;
    transition: all 0.2s; backdrop-filter: blur(10px);
}
.section-tab:hover { border-color: rgba(216,180,254,0.4); color: #fff; background: rgba(255,255,255,0.05); }
.section-tab-active { border-color: #d8b4fe !important; background: rgba(168, 85, 247, 0.2) !important; color: #fff !important; font-weight: 600; box-shadow: 0 0 15px rgba(168,85,247,0.3); }

/* Form Card */
.form-card {
    background: rgba(15, 10, 30, 0.75); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 16px; padding: 2rem; color: white;
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.6);
}
.card-title { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 6px; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
.card-sub   { font-size: 0.875rem; color: rgba(255,255,255,0.5); margin-bottom: 1.75rem; }

.form-body { }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
@media (max-width: 520px) { .two-col { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1.25rem; }
.label { display: block; font-size: 0.8125rem; font-weight: 600; color: rgba(255,255,255,0.8); margin-bottom: 8px; }
.req { color: #fca5a5; }

.input {
    width: 100%; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: rgba(0,0,0,0.4); color: white;
    font-size: 0.875rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.input::placeholder { color: rgba(255,255,255,0.4); }
.input:focus { border-color: #d8b4fe; box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-error { border-color: #fca5a5 !important; }
.textarea { resize: vertical; min-height: 90px; }
.error-msg { font-size: 0.75rem; color: #fca5a5; margin-top: 6px; }

/* Foto */
.foto-area { margin-bottom: 4px; }
.foto-preview-wrap { position: relative; display: inline-block; }
.foto-preview-img {
    width: 110px; height: 110px; border-radius: 50%;
    object-fit: cover; border: 3px solid rgba(168, 85, 247, 0.4); box-shadow: 0 5px 15px rgba(0,0,0,0.5);
}
.foto-remove {
    position: absolute; top: 0; right: 0;
    width: 24px; height: 24px; border-radius: 50%;
    background: #ef4444; color: #fff; border: 2px solid #1a103c;
    font-size: 11px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); transition: 0.2s;
}
.foto-remove:hover { background: #dc2626; transform: scale(1.1); }
.foto-upload {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    width: 160px; height: 110px; border: 2px dashed rgba(255,255,255,0.3); background: rgba(0,0,0,0.2);
    border-radius: 12px; cursor: pointer; gap: 4px; transition: border-color 0.2s;
}
.foto-upload:hover { border-color: #d8b4fe; background: rgba(168, 85, 247, 0.1); }
.upload-icon { font-size: 24px; color: rgba(255,255,255,0.5); transition: 0.2s; }
.foto-upload:hover .upload-icon { color: #d8b4fe; }
.upload-text { font-size: 0.75rem; color: rgba(255,255,255,0.7); }
.upload-hint { font-size: 0.6875rem; color: rgba(255,255,255,0.4); }

/* Danger Zone */
.danger-zone {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; flex-wrap: wrap;
    margin-top: 2.5rem; padding: 1.25rem 1.5rem;
    background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 12px; box-shadow: inset 0 0 20px rgba(239, 68, 68, 0.05);
}
.danger-title { font-size: 0.9375rem; font-weight: 700; color: #fca5a5; margin-bottom: 4px; }
.danger-sub   { font-size: 0.8125rem; color: rgba(255,255,255,0.6); }

/* Actions */
.form-actions { display: flex; justify-content: flex-end; margin-top: 1.5rem; }
.btn-primary {
    padding: 10px 24px; border-radius: 8px;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%); color: #fff;
    font-size: 0.875rem; font-weight: 600;
    border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 0 15px rgba(168,85,247,0.4);
}
.btn-primary:hover:not(:disabled) { box-shadow: 0 0 25px rgba(168,85,247,0.7); transform: translateY(-1px); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-outline {
    padding: 10px 24px; border-radius: 8px;
    background: rgba(255,255,255,0.05); color: #d8b4fe;
    border: 1px solid rgba(216,180,254,0.3);
    font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: 0.2s; backdrop-filter: blur(8px);
}
.btn-outline:hover { background: rgba(216,180,254,0.15); color: #fff; border-color: rgba(216,180,254,0.6); }

.btn-danger {
    padding: 10px 20px; border-radius: 8px;
    background: rgba(239, 68, 68, 0.2); color: #fca5a5;
    font-size: 0.875rem; font-weight: 600;
    border: 1px solid rgba(239, 68, 68, 0.4); cursor: pointer; transition: 0.2s;
}
.btn-danger:hover:not(:disabled) { background: rgba(239, 68, 68, 0.4); color: #fff; box-shadow: 0 0 15px rgba(239,68,68,0.4); }
.btn-danger:disabled { opacity: 0.6; cursor: not-allowed; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(5px);
    display: flex; align-items: center; justify-content: center;
    padding: 1rem; animation: fadeIn 0.2s ease;
}
@keyframes fadeIn { from { opacity: 0; backdrop-filter: blur(0px); } to { opacity: 1; backdrop-filter: blur(5px); } }
.modal {
    background: rgba(20, 15, 35, 0.95); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 16px; width: 100%; max-width: 440px; color: white;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5), inset 0 0 20px rgba(168, 85, 247, 0.1);
    animation: slideUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.5rem 1.75rem; border-bottom: 1px solid rgba(255,255,255,0.1);
}
.modal-title { font-size: 1.125rem; font-weight: 700; color: #fff; }
.modal-close { background: rgba(255,255,255,0.1); width: 30px; height: 30px; border-radius: 50%; border: none; cursor: pointer; color: #fff; font-size: 0.875rem; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.modal-close:hover { background: #ef4444; transform: rotate(90deg); }
.modal-body { padding: 1.5rem 1.75rem; }
.delete-msg { font-size: 0.875rem; color: rgba(255,255,255,0.8); line-height: 1.6; margin-bottom: 1.5rem; }
.delete-msg strong { color: #fca5a5; }
.modal-footer {
    display: flex; justify-content: flex-end; gap: 12px;
    padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 1.5rem;
}
</style>
