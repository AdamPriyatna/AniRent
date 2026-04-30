<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'


const props = defineProps({
    units: Object,
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

function doSearch() {
    router.get(route('units.index'), {
        search: search.value,
    }, { preserveState: true, replace: true })
}

function resetSearch() {
    search.value = ''
    doSearch()
}

const statusColor = (status) => {
    const map = {
        tersedia: 'success',
        dipinjam: 'warning',
        rusak: 'error',
    }
    return map[status] ?? 'default'
}

const statusLabel = (status) => {
    const map = { tersedia: 'Tersedia', dipinjam: 'Dipinjam', rusak: 'Rusak' }
    return map[status] ?? status
}

const statusIcon = (status) => {
    const map = {
        tersedia: 'mdi-check-circle',
        dipinjam: 'mdi-clock-outline',
        rusak: 'mdi-alert-circle',
    }
    return map[status] ?? 'mdi-help-circle'
}
</script>

<template>
    <Head title="Katalog Unit" />

        <v-container fluid class="pa-6" style="max-width: 1280px">

            <!-- Hero Header -->
            <v-row class="mb-6 align-center">
                <v-col cols="12" md="7">
                    <div class="d-flex align-center gap-3 mb-2">
                        <v-icon icon="mdi-costume" color="deep-purple-accent-2" size="32" />
                        <h1 class="text-h5 font-weight-bold text-grey-darken-4">Katalog Unit</h1>
                    </div>
                    <p class="text-body-2 text-grey-darken-1 mb-0">
                        Jelajahi koleksi kostum dan properti yang tersedia untuk disewa di AniRent.
                    </p>
                </v-col>
                <v-col cols="12" md="5">
                    <!-- Search Bar -->
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        placeholder="Cari nama unit..."
                        variant="outlined"
                        density="comfortable"
                        rounded="lg"
                        hide-details
                        clearable
                        @keyup.enter="doSearch"
                        @click:clear="resetSearch"
                        bg-color="white"
                        color="deep-purple-accent-2"
                    >
                        <template #append-inner>
                            <v-btn
                                icon="mdi-magnify"
                                size="small"
                                variant="flat"
                                color="deep-purple-accent-2"
                                rounded="md"
                                @click="doSearch"
                            />
                        </template>
                    </v-text-field>
                </v-col>
            </v-row>

            <!-- Active Filter Chip -->
            <v-row v-if="search" class="mb-4">
                <v-col>
                    <v-chip
                        closable
                        color="deep-purple-accent-2"
                        variant="tonal"
                        size="small"
                        prepend-icon="mdi-magnify"
                        @click:close="resetSearch"
                    >
                        "{{ search }}"
                    </v-chip>
                    <span class="text-body-2 text-grey ms-2">
                        {{ units.total }} unit ditemukan
                    </span>
                </v-col>
            </v-row>

            <!-- Empty State -->
            <v-row v-if="units.data.length === 0" justify="center" class="mt-10">
                <v-col cols="12" sm="6" md="4" class="text-center">
                    <v-icon icon="mdi-package-variant-remove" size="80" color="grey-lighten-1" class="mb-4" />
                    <h2 class="text-h6 font-weight-medium text-grey-darken-1 mb-2">Unit Tidak Ditemukan</h2>
                    <p class="text-body-2 text-grey mb-4">
                        Tidak ada unit yang cocok dengan pencarianmu saat ini.
                    </p>
                    <v-btn
                        v-if="search"
                        variant="tonal"
                        color="deep-purple-accent-2"
                        rounded="lg"
                        prepend-icon="mdi-refresh"
                        @click="resetSearch"
                    >
                        Reset Pencarian
                    </v-btn>
                </v-col>
            </v-row>

            <!-- Unit Cards Grid -->
            <v-row v-else>
                <v-col
                    v-for="unit in units.data"
                    :key="unit.id"
                    cols="12"
                    sm="6"
                    md="4"
                    lg="3"
                >
                    <v-card
                        rounded="xl"
                        elevation="0"
                        border
                        class="unit-card h-100"
                        @click="router.get(route('units.show', unit.id))"
                    >
                        <!-- Foto Unit -->
                        <div class="unit-img-wrap">
                            <v-img
                                v-if="unit.foto"
                                :src="`/storage/${unit.foto}`"
                                :alt="unit.nama_unit"
                                height="200"
                                cover
                                class="unit-img"
                            >
                                <template #error>
                                    <div class="unit-img-placeholder d-flex flex-column align-center justify-center h-100">
                                        <v-icon icon="mdi-image-broken-variant" size="40" color="grey-lighten-1" />
                                    </div>
                                </template>
                            </v-img>
                            <div v-else class="unit-img-placeholder d-flex flex-column align-center justify-center">
                                <v-icon icon="mdi-hanger" size="48" color="grey-lighten-2" />
                            </div>

                            <!-- Status Badge overlay -->
                            <div class="unit-status-overlay">
                                <v-chip
                                    :color="statusColor(unit.status)"
                                    :prepend-icon="statusIcon(unit.status)"
                                    size="x-small"
                                    variant="flat"
                                    class="font-weight-medium"
                                >
                                    {{ statusLabel(unit.status) }}
                                </v-chip>
                            </div>
                        </div>

                        <v-card-text class="pa-4">
                            <!-- Kode Unit -->
                            <v-chip
                                color="deep-purple-accent-2"
                                variant="tonal"
                                size="x-small"
                                class="mb-2 font-weight-bold"
                                style="font-family: monospace"
                            >
                                {{ unit.kode_unit }}
                            </v-chip>

                            <!-- Nama Unit -->
                            <h3 class="text-body-1 font-weight-semibold text-grey-darken-4 mb-1" style="line-height: 1.3">
                                {{ unit.nama_unit }}
                            </h3>

                            <!-- Harga Sewa -->
                            <div class="mb-2 text-primary font-weight-bold">
                                Rp {{ (unit.harga_sewa ?? 0).toLocaleString('id-ID') }} / hari
                            </div>

                            <!-- Deskripsi -->
                            <p v-if="unit.deskripsi" class="text-caption text-grey mb-3" style="line-height: 1.5; min-height: 36px">
                                {{ unit.deskripsi?.substring(0, 70) }}{{ unit.deskripsi?.length > 70 ? '...' : '' }}
                            </p>
                            <div v-else class="mb-3" style="min-height: 36px" />

                            <!-- Kategori Tags -->
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <v-chip
                                    v-for="cat in unit.categories"
                                    :key="cat.id"
                                    size="x-small"
                                    variant="outlined"
                                    color="grey-darken-1"
                                >
                                    {{ cat.nama_kategori }}
                                </v-chip>
                            </div>

                            <!-- Kondisi -->
                            <div v-if="unit.kondisi" class="d-flex align-center gap-1">
                                <v-icon icon="mdi-star-check-outline" size="14" color="amber-darken-2" />
                                <span class="text-caption text-grey-darken-1">{{ unit.kondisi }}</span>
                            </div>
                        </v-card-text>

                        <v-divider />

                        <v-card-actions class="pa-3">
                            <v-btn
                                @click.stop="router.get(route('units.show', unit.id))"
                                variant="tonal"
                                color="deep-purple-accent-2"
                                size="small"
                                rounded="lg"
                                block
                                :disabled="unit.status !== 'tersedia'"
                                prepend-icon="mdi-eye-outline"
                            >
                                {{ unit.status === 'tersedia' ? 'Lihat Detail' : 'Tidak Tersedia' }}
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-col>
            </v-row>

            <!-- Pagination -->
            <v-row v-if="units.last_page > 1" class="mt-6" justify="center">
                <v-col cols="auto">
                    <div class="d-flex align-center gap-2 flex-wrap justify-center">
                        <template v-for="link in units.links" :key="link.label">
                            <v-btn
                                v-if="link.url"
                                :href="link.url"
                                :variant="link.active ? 'flat' : 'outlined'"
                                :color="link.active ? 'deep-purple-accent-2' : 'grey'"
                                size="small"
                                rounded="lg"
                                min-width="36"
                                v-html="link.label"
                            />
                            <v-btn
                                v-else
                                variant="outlined"
                                color="grey-lighten-1"
                                size="small"
                                rounded="lg"
                                min-width="36"
                                disabled
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </v-col>
            </v-row>

            <!-- Total Info -->
            <v-row class="mt-4" justify="center" v-if="units.data.length > 0">
                <v-col cols="auto">
                    <p class="text-caption text-grey text-center">
                        Menampilkan {{ units.from }}–{{ units.to }} dari {{ units.total }} unit
                    </p>
                </v-col>
            </v-row>

        </v-container>

</template>

<style scoped>
.unit-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}
.unit-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(83, 74, 183, 0.13) !important;
}

.unit-img-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 16px 16px 0 0;
}

.unit-img {
    border-radius: 16px 16px 0 0;
}

.unit-img-placeholder {
    height: 200px;
    background: linear-gradient(135deg, #f8f7ff 0%, #eeedfe 100%);
    border-radius: 16px 16px 0 0;
}

.unit-status-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
}

.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
</style>
