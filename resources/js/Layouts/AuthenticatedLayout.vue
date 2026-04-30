<template>
    <v-app :theme="'light'">
        <!-- Sidebar -->
        <v-navigation-drawer
            v-model="drawer"
            :rail="false"
            permanent
            width="250"
            style="background-color: #1a1d2e; border-right: none;"
        >
            <!-- Logo -->
            <div class="px-4 py-5">
                <div class="d-flex align-center gap-3">
                    <div
                        style="
                            width: 36px; height: 36px;
                            background: #6c63ff;
                            border-radius: 10px;
                            display: flex; align-items: center; justify-content: center;
                            font-weight: 700; color: white; font-size: 16px;
                        "
                    >A</div>
                    <span style="color: white; font-weight: 700; font-size: 18px; letter-spacing: 0.3px;">AniRent</span>
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
                    <div class="d-flex align-center gap-3 mb-3">
                        <v-avatar
                            size="36"
                            :color="avatarColor"
                            style="flex-shrink: 0;"
                        >
                            <span style="font-size: 13px; font-weight: 700; color: white;">{{ userInitials }}</span>
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
        <v-main style="background-color: #f5f6fa; min-height: 100vh;">
            <div class="pa-6" style="max-width: 1200px;">
                <slot />
            </div>
        </v-main>
    </v-app>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { VListItem, VBadge, VIcon } from 'vuetify/components'

const page = usePage()

const isAdmin = computed(() => page.props.auth.user.role === 'admin')

const userInitials = computed(() => {
    const name = page.props.auth.user.name || ''
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

const avatarColor = computed(() => {
    const colors = ['#6c63ff', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6']
    const name = page.props.auth.user.name || ''
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