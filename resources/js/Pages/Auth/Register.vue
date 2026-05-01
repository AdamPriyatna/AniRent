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
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1.75rem;">
                        <!-- Icon Container -->
                        <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(to bottom right, #c084fc, #ec4899); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(168,85,247,0.6); flex-shrink: 0;">
                            <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <!-- Text -->
                        <span style="font-size: 24px; font-weight: 800; letter-spacing: 0.05em; background: linear-gradient(to right, #d8b4fe, #fbcfe8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Inter', sans-serif;">AniRent</span>
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
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image: url('/images/anime_bg.png');
    background-size: cover;
    background-position: center;
    position: relative;
    padding: 1.5rem;
}

.auth-page::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(26, 16, 60, 0.8), rgba(45, 27, 84, 0.7), rgba(26, 16, 60, 0.95));
    backdrop-filter: blur(2px);
    z-index: 0;
}

.auth-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    max-width: 920px;
    min-height: 580px;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 15px 40px 0 rgba(168, 85, 247, 0.2);
}

@media (max-width: 680px) {
    .auth-wrap { grid-template-columns: 1fr; }
    .auth-left { display: none !important; }
}

/* Left */
.auth-left {
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    background: rgba(26, 16, 60, 0.3);
    border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.deco {
    position: absolute;
    border-radius: 50%;
    opacity: 0.12;
}
.deco-1 {
    width: 300px; height: 300px;
    background: #ec4899;
    top: -80px; right: -80px;
    filter: blur(40px);
}
.deco-2 {
    width: 180px; height: 180px;
    background: #a855f7;
    bottom: 40px; left: -60px;
    filter: blur(30px);
}

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
    background: linear-gradient(135deg, #a855f7, #ec4899); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 500;
}
.brand-name { font-size: 14px; font-weight: 500; color: #fff; opacity: 0.9; letter-spacing: 1px;}

.tagline {
    font-size: 22px; font-weight: 700;
    color: #fff; line-height: 1.35; margin-bottom: 1rem;
}
.tagline-accent { 
    background: linear-gradient(to right, #fbcfe8, #d8b4fe);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.tagline-desc { font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.65; }

.info-box {
    position: relative; z-index: 1;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 12px; padding: 16px;
    backdrop-filter: blur(8px);
}
.info-box-title { font-size: 12px; color: rgba(255,255,255,0.6); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
.feature-list { display: flex; flex-direction: column; gap: 10px; }
.feature-item {
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; color: rgba(255,255,255,0.8);
}
.feature-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 8px currentColor; }

/* Right */
.auth-right {
    display: flex; align-items: center; justify-content: center;
    padding: 2.5rem 2rem;
    overflow-y: auto;
}
.form-wrap { width: 100%; max-width: 380px; }

.form-title { font-size: 22px; font-weight: 600; color: #fff; margin-bottom: 4px; }
.form-sub { font-size: 14px; color: rgba(255,255,255,0.6); margin-bottom: 1.75rem; }

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media (max-width: 400px) { .two-col { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1rem; }
.label {
    display: block; font-size: 11px; font-weight: 500;
    color: rgba(255,255,255,0.6); text-transform: uppercase;
    letter-spacing: 0.05em; margin-bottom: 6px;
}
.input {
    width: 100%; padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 10px;
    font-size: 14px; color: #fff; outline: none;
    background: rgba(0, 0, 0, 0.2); transition: all 0.2s;
}
.input::placeholder { color: rgba(255,255,255,0.3); }
.input:focus { border-color: #d8b4fe; background: rgba(0,0,0,0.4); box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-error { border-color: #ef4444 !important; }
.error-msg { font-size: 12px; color: #fca5a5; margin-top: 4px; }

.terms-text { font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 1.1rem; line-height: 1.5; }
.terms-link { color: #d8b4fe; text-decoration: none; transition: 0.2s; }
.terms-link:hover { color: #fff; text-decoration: underline; text-shadow: 0 0 8px rgba(216,180,254,0.8); }

.btn-submit {
    width: 100%; padding: 12px;
    border-radius: 10px; border: none;
    background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
    color: #fff;
    font-size: 15px; font-weight: 600;
    cursor: pointer; transition: 0.3s;
    box-shadow: 0 0 15px rgba(192,132,252,0.5);
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 0 25px rgba(192,132,252,0.8); }
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.divider {
    display: flex; align-items: center; gap: 12px;
    margin: 1.25rem 0;
}
.divider-line { flex: 1; height: 1px; background: rgba(255,255,255,0.1); }
.divider-text { font-size: 12px; color: rgba(255,255,255,0.4); }

.switch-text { text-align: center; font-size: 14px; color: rgba(255,255,255,0.6); }
.switch-link { color: #d8b4fe; font-weight: 600; text-decoration: none; transition: 0.2s; }
.switch-link:hover { color: #fff; text-decoration: underline; text-shadow: 0 0 8px rgba(216,180,254,0.8); }
</style>