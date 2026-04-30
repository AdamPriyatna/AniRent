<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    bundles: Object,
})

const search = ref('')

function doSearch() {
    router.get(route('bundles.index'), {
        search: search.value,
    }, { preserveState: true, replace: true })
}

function resetSearch() {
    search.value = ''
    doSearch()
}

function formatRupiah(val) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <Head title="Paket Bundle Hemat" />

    <v-container fluid class="pa-6" style="max-width: 1280px">

        <!-- Hero Header -->
        <v-row class="mb-8 align-center">
            <v-col cols="12" md="8">
                <div class="d-flex align-center gap-3 mb-2">
                    <v-icon icon="mdi-package-variant-closed" color="deep-purple-accent-2" size="36" />
                    <h1 class="text-h4 font-weight-bold text-grey-darken-4">Paket Bundle Hemat</h1>
                </div>
                <p class="text-body-1 text-grey-darken-1 mb-0">
                    Sewa lebih banyak, bayar lebih hemat dengan paket bundle pilihan AniRent.
                </p>
            </v-col>
        </v-row>

        <!-- Bundle Grid -->
        <v-row v-if="bundles.data.length > 0">
            <v-col
                v-for="bundle in bundles.data"
                :key="bundle.id"
                cols="12"
                sm="6"
                md="4"
                lg="4"
            >
                <v-card
                    rounded="xl"
                    elevation="0"
                    border
                    class="bundle-card h-100"
                    @click="router.get(route('units.index'))" 
                >
                    <!-- Foto Bundle -->
                    <div class="bundle-img-wrap">
                        <v-img
                            v-if="bundle.foto"
                            :src="`/storage/${bundle.foto}`"
                            :alt="bundle.nama_bundle"
                            height="240"
                            cover
                            class="bundle-img"
                        />
                        <div v-else class="bundle-img-placeholder d-flex flex-column align-center justify-center h-100">
                            <v-icon icon="mdi-package-variant" size="64" color="grey-lighten-2" />
                        </div>
                        
                        <!-- Price Badge -->
                        <div class="price-badge">
                            <span class="price-val">{{ formatRupiah(bundle.harga_per_hari) }}</span>
                            <span class="price-unit">/ hari</span>
                        </div>
                    </div>

                    <v-card-text class="pa-5">
                        <h3 class="text-h6 font-weight-bold mb-2 text-grey-darken-4 line-clamp-1">
                            {{ bundle.nama_bundle }}
                        </h3>

                        <p class="text-body-2 text-grey-darken-1 mb-4 line-clamp-2">
                            {{ bundle.deskripsi }}
                        </p>

                        <!-- Units Included -->
                        <div class="mb-2">
                            <div class="text-caption font-weight-bold text-grey mb-2">UNIT TERMASUK:</div>
                            <div class="d-flex flex-wrap gap-2">
                                <v-chip
                                    v-for="unit in bundle.units"
                                    :key="unit.id"
                                    size="x-small"
                                    variant="tonal"
                                    color="deep-purple-accent-1"
                                    class="font-weight-medium"
                                >
                                    {{ unit.nama_unit }}
                                </v-chip>
                            </div>
                        </div>
                    </v-card-text>

                    <v-divider />

                    <v-card-actions class="pa-4">
                        <v-btn
                            block
                            variant="flat"
                            color="deep-purple-accent-2"
                            rounded="lg"
                            prepend-icon="mdi-arrow-right-circle-outline"
                        >
                            Pilih Paket Ini
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
            <v-icon icon="mdi-package-variant-closed" size="80" color="grey-lighten-2" class="mb-4" />
            <h3 class="text-h6 text-grey-darken-1">Belum ada paket bundle tersedia.</h3>
            <p class="text-body-2 text-grey">Silakan cek kembali nanti atau hubungi admin.</p>
        </div>

        <!-- Pagination -->
        <v-row v-if="bundles.last_page > 1" class="mt-8" justify="center">
            <v-pagination
                v-model="bundles.current_page"
                :length="bundles.last_page"
                @update:model-value="v => router.get(route('bundles.index', { page: v }))"
                rounded="circle"
                active-color="deep-purple-accent-2"
                density="comfortable"
            />
        </v-row>

    </v-container>
</template>

<style>
/* Layout Global Fix */
#app { background-color: #FAFAF8; }
</style>

<style scoped>
.bundle-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    background: #fff;
    overflow: hidden;
}

.bundle-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(83, 74, 183, 0.12) !important;
    border-color: #B39DDB !important;
}

.bundle-img-wrap {
    position: relative;
    background: #f5f5f5;
    overflow: hidden;
}

.bundle-img-placeholder {
    background: linear-gradient(135deg, #f8f7f4 0%, #eeeeee 100%);
}

.price-badge {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    padding: 6px 14px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.price-val {
    font-weight: 800;
    color: #534AB7;
    font-size: 1.1rem;
    line-height: 1.2;
}

.price-unit {
    font-size: 0.65rem;
    color: #888;
    text-transform: uppercase;
    font-weight: 600;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
</style>
