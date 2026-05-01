<template>
    <v-app style="background: transparent;" class="anime-dashboard">
        <!-- Anime Background -->
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0;">
            <img src="/images/anime_bg.png" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;" />
            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(26, 16, 60, 0.8), rgba(45, 27, 84, 0.7), rgba(26, 16, 60, 0.95)); backdrop-filter: blur(2px);"></div>
        </div>
        <!-- Sidebar -->
        <v-navigation-drawer
            v-model="drawer"
            :rail="false"
            permanent
            width="250"
            style="background: rgba(20, 12, 45, 0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-right: 1px solid rgba(255,255,255,0.08); z-index: 10;"
        >
            <!-- Logo -->
            <div class="px-4 py-5">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <!-- Icon Container -->
                    <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(to bottom right, #c084fc, #ec4899); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 15px rgba(168,85,247,0.6); flex-shrink: 0;">
                        <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <!-- Text -->
                    <span style="font-size: 24px; font-weight: 800; letter-spacing: 0.05em; background: linear-gradient(to right, #d8b4fe, #fbcfe8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Inter', sans-serif;">AniRent</span>
                </div>
            </div>

            <v-divider style="border-color: rgba(255,255,255,0.08);" />

            <!-- Admin Menu -->
            <template v-if="isAdmin">
                <div class="px-3 pt-4 pb-1">
                    <span style="color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Menu Utama</span>
                </div>

                <v-list density="compact" nav class="px-2">
                    <SidebarItem
                        v-for="item in adminMenuUtama"
                        :key="item.route"
                        :item="item"
                        :active="isActive(item.route)"
                    />
                </v-list>

                <div class="px-3 pt-3 pb-1">
                    <span style="color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Peminjaman</span>
                </div>

                <v-list density="compact" nav class="px-2">
                    <SidebarItem
                        v-for="item in adminMenuPeminjaman"
                        :key="item.route"
                        :item="item"
                        :active="isActive(item.route)"
                    />
                </v-list>

                <div class="px-3 pt-3 pb-1">
                    <span style="color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Pengguna</span>
                </div>

                <v-list density="compact" nav class="px-2">
                    <SidebarItem
                        v-for="item in adminMenuPengguna"
                        :key="item.route"
                        :item="item"
                        :active="isActive(item.route)"
                    />
                </v-list>
            </template>

            <!-- Member Menu -->
            <template v-else>
                <div class="px-3 pt-4 pb-1">
                    <span style="color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Menu</span>
                </div>

                <v-list density="compact" nav class="px-2">
                    <SidebarItem
                        v-for="item in memberMenuUtama"
                        :key="item.route"
                        :item="item"
                        :active="isActive(item.route)"
                    />
                </v-list>

                <div class="px-3 pt-3 pb-1">
                    <span style="color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Pinjaman Saya</span>
                </div>

                <v-list density="compact" nav class="px-2">
                    <SidebarItem
                        v-for="item in memberMenuPinjaman"
                        :key="item.route"
                        :item="item"
                        :active="isActive(item.route)"
                    />
                </v-list>

                <div class="px-3 pt-3 pb-1">
                    <span style="color: rgba(255,255,255,0.35); font-size: 10px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase;">Akun</span>
                </div>

                <v-list density="compact" nav class="px-2">
                    <SidebarItem
                        v-for="item in memberMenuAkun"
                        :key="item.route"
                        :item="item"
                        :active="isActive(item.route)"
                    />
                </v-list>
            </template>

            <!-- Spacer -->
            <template #append>
                <v-divider style="border-color: rgba(255,255,255,0.08);" />

                <!-- User Info -->
                <div class="px-4 py-4">
                    <div v-if="$page.props.auth.user" class="d-flex align-center gap-3 mb-3">
                        <v-avatar
                            size="36"
                            :color="avatarColor"
                            style="flex-shrink: 0;"
                        >
                            <v-img
                                v-if="$page.props.auth.user.foto"
                                :src="`/storage/${$page.props.auth.user.foto}`"
                                alt="avatar"
                                cover
                            />
                            <span v-else style="font-size: 13px; font-weight: 700; color: white;">{{ userInitials }}</span>
                        </v-avatar>
                        <div style="min-width: 0;">
                            <div style="color: white; font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div style="color: rgba(255,255,255,0.45); font-size: 11px;">
                                {{ isAdmin ? 'Administrator' : 'Anggota' }}
                            </div>
                        </div>
                    </div>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        style="
                            display: flex; align-items: center; gap: 8px;
                            color: rgba(255,255,255,0.45); font-size: 13px;
                            background: none; border: none; cursor: pointer;
                            padding: 4px 0; transition: color 0.2s;
                            width: 100%;
                        "
                        @mouseenter="e => e.currentTarget.style.color = 'rgba(255,255,255,0.75)'"
                        @mouseleave="e => e.currentTarget.style.color = 'rgba(255,255,255,0.45)'"
                    >
                        <v-icon size="16" style="color: inherit;">mdi-logout</v-icon>
                        Keluar
                    </Link>
                </div>
            </template>
        </v-navigation-drawer>

        <!-- Main Content -->
        <v-main style="background-color: transparent; min-height: 100vh; position: relative; z-index: 1;">
            <div class="pa-6" style="max-width: 1200px;">
                <slot />
            </div>
        </v-main>
    </v-app>
</template>

<script setup>
import { ref, computed, defineComponent, h } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { VListItem, VBadge, VIcon } from 'vuetify/components'

const page = usePage()
const drawer = ref(true)

const isAdmin = computed(() => page.props.auth.user?.role === 'admin')

const userInitials = computed(() => {
    const name = page.props.auth.user?.name || ''
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

const avatarColor = computed(() => {
    const colors = ['#6c63ff', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6']
    const name = page.props.auth.user?.name || ''
    return colors[name.charCodeAt(0) % colors.length]
})

const isActive = (routeName) => {
    try {
        return route().current(routeName) || route().current(routeName + '.*')
    } catch {
        return false
    }
}

// Admin menus
const adminMenuUtama = computed(() => [
    {
        label: 'Dashboard',
        route: 'dashboard',
        icon: 'mdi-view-dashboard-outline',
        badge: null,
        badgeColor: null,
    },
    {
        label: 'Unit',
        route: 'admin.units.index',
        icon: 'mdi-package-variant-closed',
        badge: page.props.stats?.totalUnit ?? null,
        badgeColor: '#6c63ff',
    },
    {
        label: 'Kategori',
        route: 'admin.categories.index',
        icon: 'mdi-tag-outline',
        badge: page.props.stats?.totalKategori ?? null,
        badgeColor: '#f59e0b',
    },
    {
        label: 'Bundle',
        route: 'admin.bundles.index',
        icon: 'mdi-layers-outline',
        badge: page.props.stats?.totalBundle ?? null,
        badgeColor: '#ef4444',
    },
])

const adminMenuPeminjaman = computed(() => [
    {
        label: 'Booking',
        route: 'admin.bookings.index',
        icon: 'mdi-calendar-clock-outline',
        badge: page.props.stats?.totalBooking ?? null,
        badgeColor: '#ef4444',
    },
    {
        label: 'Riwayat',
        route: 'admin.bookings.history',
        icon: 'mdi-history',
        badge: null,
        badgeColor: null,
    },
    {
        label: 'Laporan PDF',
        route: 'admin.bookings.report',
        icon: 'mdi-file-chart-outline',
        badge: null,
        badgeColor: null,
    },
])

const adminMenuPengguna = computed(() => [
    {
        label: 'Anggota',
        route: 'admin.users.index',
        icon: 'mdi-account-group-outline',
        badge: page.props.stats?.totalAnggota ?? null,
        badgeColor: '#10b981',
    },
])

// Member menus
const memberMenuUtama = computed(() => [
    {
        label: 'Dashboard',
        route: 'dashboard',
        icon: 'mdi-view-dashboard-outline',
        badge: null,
        badgeColor: null,
    },
    {
        label: 'Cari Unit',
        route: 'units.index',
        icon: 'mdi-magnify',
        badge: null,
        badgeColor: null,
    },
    {
        label: 'Bundle',
        route: 'bundles.index',
        icon: 'mdi-layers-outline',
        badge: null,
        badgeColor: null,
    },
])

const memberMenuPinjaman = computed(() => [
    {
        label: 'Pinjaman Saya',
        route: 'bookings.mine',
        icon: 'mdi-calendar-check-outline',
        badge: page.props.stats?.pinjamanAktif ?? null,
        badgeColor: '#f59e0b',
    },
])

const memberMenuAkun = computed(() => [
    {
        label: 'Profil Saya',
        route: 'profile.edit',
        icon: 'mdi-account-circle-outline',
        badge: null,
        badgeColor: null,
    },
])

// Sidebar item component
const SidebarItem = defineComponent({
    props: {
        item: Object,
        active: Boolean,
    },
    setup(props) {
        return () => {
            const dotColor = props.active ? '#6c63ff' : 'rgba(255,255,255,0.25)'

            return h(Link, {
                href: (() => { try { return route(props.item.route) } catch { return '#' } })(),
                style: 'text-decoration: none; display: block;',
            }, () => h('div', {
                style: `
                    display: flex; align-items: center; gap: 10px;
                    padding: 8px 10px; border-radius: 8px; cursor: pointer;
                    transition: background 0.15s;
                    background: ${props.active ? 'rgba(108, 99, 255, 0.18)' : 'transparent'};
                    margin-bottom: 2px;
                `,
                onMouseenter: (e) => {
                    if (!props.active) e.currentTarget.style.background = 'rgba(255,255,255,0.06)'
                },
                onMouseleave: (e) => {
                    if (!props.active) e.currentTarget.style.background = 'transparent'
                },
            }, [
                // Dot indicator
                h('div', {
                    style: `
                        width: 7px; height: 7px; border-radius: 50%;
                        background: ${dotColor}; flex-shrink: 0;
                        transition: background 0.15s;
                    `,
                }),
                // Label
                h('span', {
                    style: `
                        flex: 1; font-size: 13.5px; font-weight: ${props.active ? '600' : '400'};
                        color: ${props.active ? 'white' : 'rgba(255,255,255,0.55)'};
                        transition: color 0.15s;
                    `,
                }, props.item.label),
                // Badge
                props.item.badge !== null
                    ? h('div', {
                        style: `
                            background: ${props.item.badgeColor || '#6c63ff'};
                            color: white; font-size: 10px; font-weight: 700;
                            border-radius: 10px; padding: 1px 7px; min-width: 20px;
                            text-align: center;
                        `,
                    }, String(props.item.badge))
                    : null,
            ]))
        }
    },
})
</script>

<style>
/* Global anime theme overrides for Vuetify */
.anime-dashboard .v-card,
.anime-dashboard .v-sheet {
    background: rgba(15, 10, 30, 0.8) !important; /* Gelap dan solid agar kontras tinggi */
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.6) !important;
}

.anime-dashboard .v-table {
    background: transparent !important;
}
.anime-dashboard .v-table th {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #f8fafc !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.anime-dashboard .v-table tr:hover td {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Ensure inputs in cards look good */
.anime-dashboard .v-field {
    background: rgba(0, 0, 0, 0.4) !important;
    border-radius: 8px !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* Sidebar active item glow */
.anime-dashboard .v-list-item--active {
    background: linear-gradient(90deg, rgba(168, 85, 247, 0.25) 0%, transparent 100%) !important;
    border-left: 4px solid #a855f7;
}

/* Text Highlights */
.anime-dashboard .text-h1, 
.anime-dashboard .text-h2, 
.anime-dashboard .text-h3, 
.anime-dashboard .text-h4, 
.anime-dashboard .text-h5, 
.anime-dashboard .text-h6 {
    color: #ffffff !important;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}
</style>   