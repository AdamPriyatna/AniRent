<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    nim_nip: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Daftar — AniRent" />

    <div class="auth-page">
        <div class="auth-wrap">

            <!-- Left panel -->
            <div class="auth-left">
                <div class="deco deco-1"></div>
                <div class="deco deco-2"></div>

                <div class="brand">
                    <div class="brand-badge">
                        <div class="brand-icon">A</div>
                        <span class="brand-name">AniRent</span>
                    </div>
                    <h1 class="tagline">
                        Bergabung dengan komunitas <span class="tagline-accent">anime lovers</span>
                    </h1>
                    <p class="tagline-desc">
                        Daftar sekarang dan nikmati kemudahan menyewa peralatan anime favoritmu kapan saja.
                    </p>
                </div>

                <div class="info-box">
                    <p class="info-box-title">Keuntungan jadi anggota</p>
                    <div class="feature-list">
                        <div class="feature-item">
                            <span class="feature-dot" style="background: #5DCAA5;"></span>
                            Akses ratusan unit tersedia
                        </div>
                        <div class="feature-item">
                            <span class="feature-dot" style="background: #7F77DD;"></span>
                            Pinjam hingga 2 unit sekaligus
                        </div>
                        <div class="feature-item">
                            <span class="feature-dot" style="background: #ED93B1;"></span>
                            Sistem booking yang mudah
                        </div>
                        <div class="feature-item">
                            <span class="feature-dot" style="background: #BA7517;"></span>
                            Durasi pinjam fleksibel hingga 5 hari
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right panel -->
            <div class="auth-right">
                <div class="form-wrap">

                    <h2 class="form-title">Buat akun baru</h2>
                    <p class="form-sub">Isi data diri kamu untuk mulai menyewa</p>

                    <form @submit.prevent="submit">

                        <!-- Nama -->
                        <div class="form-group">
                            <label class="label">Nama lengkap</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="input"
                                :class="{ 'input-error': form.errors.name }"
                                placeholder="Nama lengkap kamu"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <p v-if="form.errors.name" class="error-msg">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="label">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="input"
                                :class="{ 'input-error': form.errors.email }"
                                placeholder="email@contoh.com"
                                required
                                autocomplete="username"
                            />
                            <p v-if="form.errors.email" class="error-msg">{{ form.errors.email }}</p>
                        </div>

                        <!-- NIM / NIP -->
                        <div class="form-group">
                            <label class="label">NIM / NIP</label>
                            <input
                                v-model="form.nim_nip"
                                type="text"
                                class="input"
                                :class="{ 'input-error': form.errors.nim_nip }"
                                placeholder="Nomor induk mahasiswa / pegawai"
                            />
                            <p v-if="form.errors.nim_nip" class="error-msg">{{ form.errors.nim_nip }}</p>
                        </div>

                        <!-- Password -->
                        <div class="two-col">
                            <div class="form-group">
                                <label class="label">Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="input"
                                    :class="{ 'input-error': form.errors.password }"
                                    placeholder="Min. 8 karakter"
                                    required
                                    autocomplete="new-password"
                                />
                                <p v-if="form.errors.password" class="error-msg">{{ form.errors.password }}</p>
                            </div>
                            <div class="form-group">
                                <label class="label">Konfirmasi password</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="input"
                                    placeholder="Ulangi password"
                                    required
                                    autocomplete="new-password"
                                />
                            </div>
                        </div>

                        <!-- Syarat -->
                        <p class="terms-text">
                            Dengan mendaftar, kamu menyetujui
                            <a href="#" class="terms-link">Syarat & Ketentuan</a>
                            AniRent.
                        </p>

                        <button type="submit" class="btn-submit" :disabled="form.processing">
                            {{ form.processing ? 'Memproses...' : 'Daftar sekarang' }}
                        </button>

                    </form>

                    <div class="divider">
                        <span class="divider-line"></span>
                        <span class="divider-text">atau</span>
                        <span class="divider-line"></span>
                    </div>

                    <p class="switch-text">
                        Sudah punya akun?
                        <Link :href="route('login')" class="switch-link">Masuk di sini</Link>
                    </p>

                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }

.auth-page {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    background: #0f0f1a;
    padding: 1.5rem;
}

.auth-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%; max-width: 920px;
    min-height: 580px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 24px 80px rgba(0,0,0,0.4);
}

@media (max-width: 680px) {
    .auth-wrap { grid-template-columns: 1fr; }
    .auth-left { display: none; }
}

/* Left */
.auth-left {
    background: #1a1a2e;
    padding: 2.5rem 2rem;
    display: flex; flex-direction: column;
    justify-content: space-between;
    position: relative; overflow: hidden;
}
.deco { position: absolute; border-radius: 50%; opacity: 0.12; }
.deco-1 { width: 300px; height: 300px; background: #993556; top: -80px; right: -80px; }
.deco-2 { width: 180px; height: 180px; background: #534AB7; bottom: 40px; left: -60px; }

.brand { position: relative; z-index: 1; }
.brand-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,0.08);
    border: 0.5px solid rgba(255,255,255,0.15);
    border-radius: 20px; padding: 5px 14px 5px 5px;
    margin-bottom: 1.75rem;
}
.brand-icon {
    width: 28px; height: 28px; border-radius: 8px;
    background: #534AB7; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 500;
}
.brand-name { font-size: 14px; font-weight: 500; color: #fff; opacity: 0.9; }

.tagline {
    font-size: 22px; font-weight: 500;
    color: #fff; line-height: 1.35; margin-bottom: 1rem;
}
.tagline-accent { color: #ED93B1; }
.tagline-desc { font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.65; }

.info-box {
    position: relative; z-index: 1;
    background: rgba(255,255,255,0.06);
    border: 0.5px solid rgba(255,255,255,0.12);
    border-radius: 12px; padding: 16px;
}
.info-box-title { font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
.feature-list { display: flex; flex-direction: column; gap: 10px; }
.feature-item {
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; color: rgba(255,255,255,0.6);
}
.feature-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* Right */
.auth-right {
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    padding: 2.5rem 2rem;
    overflow-y: auto;
}
.form-wrap { width: 100%; max-width: 380px; }

.form-title { font-size: 22px; font-weight: 500; color: #1a1a2e; margin-bottom: 4px; }
.form-sub { font-size: 14px; color: #888; margin-bottom: 1.75rem; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media (max-width: 400px) { .two-col { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1rem; }
.label {
    display: block; font-size: 11px; font-weight: 500;
    color: #888; text-transform: uppercase;
    letter-spacing: 0.05em; margin-bottom: 6px;
}
.input {
    width: 100%; padding: 10px 14px;
    border: 0.5px solid #d0ccc4; border-radius: 10px;
    font-size: 14px; color: #1a1a2e; outline: none;
    background: #F8F7F4; transition: all 0.15s;
}
.input:focus { border-color: #534AB7; background: #fff; }
.input-error { border-color: #D85A30 !important; }
.error-msg { font-size: 12px; color: #D85A30; margin-top: 4px; }

.terms-text { font-size: 12px; color: #aaa; margin-bottom: 1.1rem; line-height: 1.5; }
.terms-link { color: #534AB7; text-decoration: none; }
.terms-link:hover { text-decoration: underline; }

.btn-submit {
    width: 100%; padding: 12px;
    border-radius: 10px; border: none;
    background: #534AB7; color: #fff;
    font-size: 15px; font-weight: 500;
    cursor: pointer; transition: background 0.15s;
}
.btn-submit:hover { background: #3C3489; }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.divider {
    display: flex; align-items: center; gap: 12px;
    margin: 1.25rem 0;
}
.divider-line { flex: 1; height: 0.5px; background: #e0ddd5; }
.divider-text { font-size: 12px; color: #bbb; }

.switch-text { text-align: center; font-size: 14px; color: #888; }
.switch-link { color: #534AB7; font-weight: 500; text-decoration: none; }
.switch-link:hover { text-decoration: underline; }
</style>