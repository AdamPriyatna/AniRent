<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Masuk — AniRent" />

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
                        Sewa peralatan <span class="tagline-accent">anime & kreator</span> favoritmu
                    </h1>
                    <p class="tagline-desc">
                        Dari cosplay, manga, console game, hingga peralatan kreator — semua ada di AniRent.
                    </p>
                </div>

                <div class="feature-list">
                    <div class="feature-item">
                        <span class="feature-dot" style="background: #7F77DD;"></span>
                        Cosplay & aksesoris lengkap
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background: #1D9E75;"></span>
                        Koleksi manga & novel
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background: #D4537E;"></span>
                        Game console & judul anime
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background: #BA7517;"></span>
                        Peralatan kreator profesional
                    </div>
                </div>
            </div>

            <!-- Right panel -->
            <div class="auth-right">
                <div class="form-wrap">

                    <div v-if="status" class="status-msg">{{ status }}</div>

                    <h2 class="form-title">Selamat datang kembali</h2>
                    <p class="form-sub">Masuk ke akun AniRent kamu</p>

                    <form @submit.prevent="submit">

                        <div class="form-group">
                            <label class="label">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="input"
                                :class="{ 'input-error': form.errors.email }"
                                placeholder="email@contoh.com"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <p v-if="form.errors.email" class="error-msg">{{ form.errors.email }}</p>
                        </div>

                        <div class="form-group">
                            <label class="label">Password</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="input"
                                :class="{ 'input-error': form.errors.password }"
                                placeholder="Password kamu"
                                required
                                autocomplete="current-password"
                            />
                            <p v-if="form.errors.password" class="error-msg">{{ form.errors.password }}</p>
                        </div>

                        <div class="form-row">
                            <label class="checkbox-label">
                                <input v-model="form.remember" type="checkbox" class="checkbox" />
                                Ingat saya
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="forgot-link"
                            >
                                Lupa password?
                            </Link>
                        </div>

                        <button type="submit" class="btn-submit" :disabled="form.processing">
                            {{ form.processing ? 'Memproses...' : 'Masuk' }}
                        </button>

                    </form>

                    <div class="divider">
                        <span class="divider-line"></span>
                        <span class="divider-text">atau</span>
                        <span class="divider-line"></span>
                    </div>

                    <p class="switch-text">
                        Belum punya akun?
                        <Link :href="route('register')" class="switch-link">Daftar sekarang</Link>
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
    max-width: 900px;
    min-height: 560px;
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
    background: #a855f7;
    top: -80px; right: -80px;
    filter: blur(40px);
}
.deco-2 {
    width: 180px; height: 180px;
    background: #ec4899;
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
    font-size: 24px; font-weight: 700;
    color: #fff; line-height: 1.35;
    margin-bottom: 1rem;
}
.tagline-accent { 
    background: linear-gradient(to right, #fbcfe8, #d8b4fe);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.tagline-desc {
    font-size: 13px; color: rgba(255,255,255,0.7);
    line-height: 1.65;
}

.feature-list {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; gap: 10px;
}
.feature-item {
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; color: rgba(255,255,255,0.8);
}
.feature-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
    box-shadow: 0 0 8px currentColor;
}

/* Right */
.auth-right {
    display: flex; align-items: center; justify-content: center;
    padding: 2.5rem 2rem;
}
.form-wrap { width: 100%; max-width: 360px; }

.status-msg {
    background: rgba(34, 197, 94, 0.2); border: 1px solid rgba(34, 197, 94, 0.5);
    border-radius: 8px; padding: 10px 14px;
    font-size: 13px; color: #86efac; margin-bottom: 1.25rem;
}

.form-title { font-size: 22px; font-weight: 600; color: #fff; margin-bottom: 4px; }
.form-sub { font-size: 14px; color: rgba(255,255,255,0.6); margin-bottom: 1.75rem; }

.form-group { margin-bottom: 1.1rem; }
.label {
    display: block; font-size: 11px; font-weight: 500;
    color: rgba(255,255,255,0.6); text-transform: uppercase;
    letter-spacing: 0.05em; margin-bottom: 6px;
}
.input {
    width: 100%; padding: 11px 14px;
    border: 1px solid rgba(255,255,255,0.2); border-radius: 10px;
    font-size: 14px; color: #fff; outline: none;
    background: rgba(0, 0, 0, 0.2); transition: all 0.2s;
}
.input::placeholder { color: rgba(255,255,255,0.3); }
.input:focus { border-color: #d8b4fe; background: rgba(0,0,0,0.4); box-shadow: 0 0 10px rgba(216,180,254,0.3); }
.input-error { border-color: #ef4444 !important; }
.error-msg { font-size: 12px; color: #fca5a5; margin-top: 4px; }

.form-row {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.25rem;
}
.checkbox-label {
    display: flex; align-items: center; gap: 7px;
    font-size: 13px; color: rgba(255,255,255,0.7); cursor: pointer;
}
.checkbox { accent-color: #a855f7; }
.forgot-link { font-size: 13px; color: #d8b4fe; text-decoration: none; transition: 0.2s; }
.forgot-link:hover { color: #fff; text-decoration: underline; text-shadow: 0 0 8px rgba(216,180,254,0.8); }

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