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
                    <div class="brand-badge">
                        <div class="brand-icon">A</div>
                        <span class="brand-name">AniRent</span>
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
* { box-sizing: border-box; margin: 0; padding: 0; }

.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f0f1a;
    padding: 1.5rem;
}

.auth-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
    max-width: 900px;
    min-height: 560px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.4);
}

@media (max-width: 680px) {
    .auth-wrap { grid-template-columns: 1fr; }
    .auth-left { display: none; }
}

/* Left */
.auth-left {
    background: #1a1a2e;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.deco {
    position: absolute;
    border-radius: 50%;
    opacity: 0.12;
}
.deco-1 {
    width: 300px; height: 300px;
    background: #534AB7;
    top: -80px; right: -80px;
}
.deco-2 {
    width: 180px; height: 180px;
    background: #993556;
    bottom: 40px; left: -60px;
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
    background: #534AB7; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 500;
}
.brand-name { font-size: 14px; font-weight: 500; color: #fff; opacity: 0.9; }

.tagline {
    font-size: 24px; font-weight: 500;
    color: #fff; line-height: 1.35;
    margin-bottom: 1rem;
}
.tagline-accent { color: #7F77DD; }
.tagline-desc {
    font-size: 13px; color: rgba(255,255,255,0.45);
    line-height: 1.65;
}

.feature-list {
    position: relative; z-index: 1;
    display: flex; flex-direction: column; gap: 10px;
}
.feature-item {
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; color: rgba(255,255,255,0.55);
}
.feature-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
}

/* Right */
.auth-right {
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    padding: 2.5rem 2rem;
}
.form-wrap { width: 100%; max-width: 360px; }

.status-msg {
    background: #E1F5EE; border: 0.5px solid #5DCAA5;
    border-radius: 8px; padding: 10px 14px;
    font-size: 13px; color: #085041; margin-bottom: 1.25rem;
}

.form-title { font-size: 22px; font-weight: 500; color: #1a1a2e; margin-bottom: 4px; }
.form-sub { font-size: 14px; color: #888; margin-bottom: 1.75rem; }

.form-group { margin-bottom: 1.1rem; }
.label {
    display: block; font-size: 11px; font-weight: 500;
    color: #888; text-transform: uppercase;
    letter-spacing: 0.05em; margin-bottom: 6px;
}
.input {
    width: 100%; padding: 11px 14px;
    border: 0.5px solid #d0ccc4; border-radius: 10px;
    font-size: 14px; color: #1a1a2e; outline: none;
    background: #F8F7F4; transition: all 0.15s;
}
.input:focus { border-color: #534AB7; background: #fff; }
.input-error { border-color: #D85A30 !important; }
.error-msg { font-size: 12px; color: #D85A30; margin-top: 4px; }

.form-row {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.25rem;
}
.checkbox-label {
    display: flex; align-items: center; gap: 7px;
    font-size: 13px; color: #666; cursor: pointer;
}
.checkbox { accent-color: #534AB7; }
.forgot-link { font-size: 13px; color: #534AB7; text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

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