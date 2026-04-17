<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import ImageLightbox from '@/Components/ImageLightbox.vue';

const props = defineProps({
    vessel: Object,
    logs: Object,
    filters: Object,
    species_list: Array,
    origins_list: Array,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const unloadStatus = ref(props.filters?.unload_status || '');
const speciesFilter = ref(props.filters?.species || '');
const originFilter = ref(props.filters?.origin || '');

const applyFilters = () => {
    if (!props.vessel) return;
    router.get(route('buyer.index'), {
        search: search.value,
        unload_status: unloadStatus.value,
        species: speciesFilter.value,
        origin: originFilter.value,
    }, { preserveState: true, replace: true });
};

const debouncedSearch = debounce(() => applyFilters(), 300);
watch(search, () => debouncedSearch());

const progressPercent = computed(() => {
    if (!props.stats || !props.stats.total) return 0;
    return Math.round((props.stats.unloaded / props.stats.total) * 100);
});

// Image preview modal
const previewImages = ref(null);
const showPreview = ref(false);

const openPreview = (images) => {
    previewImages.value = images;
    showPreview.value = true;
};
</script>

<template>
    <Head title="My Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-extrabold text-xl text-slate-900 dark:text-white">My Inventory</h2>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ vessel ? 'Active Shipment' : 'No active shipment' }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('buyer.dashboard')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg hover:bg-slate-50 transition">
                        Daily View
                    </Link>
                    <a v-if="vessel" :href="route('buyer.pdf', vessel.id)" class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </a>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <div v-if="!vessel" class="text-center py-20 text-slate-500 dark:text-slate-400">
                    <p class="text-lg font-semibold">No shipment is currently available for viewing.</p>
                </div>

                <template v-else>
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Logs</p>
                            <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                            <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Offloaded</p>
                            <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400 mt-1">{{ stats.unloaded }}</p>
                            <div class="mt-2 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                <div class="bg-emerald-500 h-1.5 rounded-full transition-all" :style="{ width: progressPercent + '%' }"></div>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">{{ progressPercent }}% complete</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
                            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Volume</p>
                            <p class="text-2xl font-extrabold font-mono text-blue-700 dark:text-blue-400 mt-1">{{ stats.total_volume }}</p>
                            <p class="text-[10px] text-slate-500 mt-1">CBM (m³)</p>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="col-span-2 sm:col-span-1">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search LOG# or TAG..."
                                    class="w-full h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-sm font-mono focus:border-blue-500 focus:ring-blue-500 placeholder:text-slate-400"
                                />
                            </div>
                            <select v-model="unloadStatus" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                                <option value="">All Items</option>
                                <option value="unloaded">Unloaded</option>
                                <option value="not_unloaded">Waiting</option>
                            </select>
                            <select v-model="speciesFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                                <option value="">All Species</option>
                                <option v-for="s in species_list" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <select v-model="originFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                                <option value="">All Origins</option>
                                <option v-for="o in origins_list" :key="o" :value="o">{{ o }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase">SN</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase">TAG</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase">LOG#</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase">Species</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase">Origin</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">Length</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">DIA</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">Volume (CBM)</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-center">Status</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-center">Photos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr
                                        v-for="log in logs.data"
                                        :key="log.id"
                                        class="transition-colors"
                                        :class="log.inspection ? 'bg-emerald-50/30 dark:bg-emerald-900/5 hover:bg-emerald-50/60 dark:hover:bg-emerald-900/10' : 'hover:bg-slate-50 dark:hover:bg-slate-700/30'"
                                    >
                                        <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ log.serial_no }}</td>
                                        <td class="px-4 py-3 text-sm font-mono font-bold text-slate-900 dark:text-white">{{ log.tag_no }}</td>
                                        <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ log.log_no || '-' }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold uppercase text-slate-700 dark:text-slate-300">{{ log.species }}</td>
                                        <td class="px-4 py-3 text-xs text-slate-500 uppercase">{{ log.origin || '-' }}</td>
                                        <td class="px-4 py-3 text-right text-xs font-mono font-semibold dark:text-slate-300">{{ parseFloat(log.length).toFixed(2) }}</td>
                                        <td class="px-4 py-3 text-right text-xs font-mono font-semibold dark:text-slate-300">{{ parseFloat(log.diameter).toFixed(2) }}</td>
                                        <td class="px-4 py-3 text-right text-xs font-mono font-bold dark:text-slate-300">{{ parseFloat(log.vol_cbm).toFixed(3) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span v-if="log.inspection" class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Offloaded
                                            </span>
                                            <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Waiting
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                v-if="log.inspection?.images && Object.keys(log.inspection.images).length > 0"
                                                @click="openPreview(log.inspection.images)"
                                                class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-semibold"
                                            >
                                                {{ Object.keys(log.inspection.images).length }} photo(s)
                                            </button>
                                            <span v-else class="text-xs text-slate-400">-</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!logs.data.length">
                                        <td colspan="10" class="px-6 py-12 text-center text-sm text-slate-400">No items match filters</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-3">
                        <div
                            v-for="log in logs.data"
                            :key="log.id"
                            class="border rounded-lg p-4 relative overflow-hidden"
                            :class="log.inspection
                                ? 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800'
                                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700'"
                        >
                            <div class="absolute left-0 top-0 bottom-0 w-1" :class="log.inspection ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                            <div class="pl-3">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-lg font-mono font-extrabold text-slate-900 dark:text-white">{{ log.tag_no }}</p>
                                        <p class="text-xs text-slate-500 font-mono">SN: {{ log.serial_no }} | LOG#: {{ log.log_no || '-' }}</p>
                                    </div>
                                    <span v-if="log.inspection" class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">OFFLOADED</span>
                                    <span v-else class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded-full">WAITING</span>
                                </div>
                                <div class="grid grid-cols-4 gap-2 text-xs text-slate-500 mt-2">
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">Species</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-300 uppercase">{{ log.species }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">Length</span>
                                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ log.length }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">DIA</span>
                                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ log.diameter }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold text-slate-400">CBM</span>
                                        <span class="font-mono font-extrabold text-slate-900 dark:text-white">{{ parseFloat(log.vol_cbm).toFixed(3) }}</span>
                                    </div>
                                </div>
                                <!-- Image thumbnails -->
                                <div v-if="log.inspection?.images && Object.keys(log.inspection.images).length > 0" class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <ImageLightbox :images="log.inspection.images" />
                                </div>
                            </div>
                        </div>
                        <div v-if="!logs.data.length" class="text-center py-12 text-slate-400">No items match filters</div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex flex-wrap gap-1 justify-center" v-if="logs.links.length > 3">
                        <template v-for="(link, k) in logs.links" :key="k">
                            <div v-if="link.url === null" class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                            <Link v-else class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold border rounded transition-all" :class="{ 'bg-blue-500 text-white border-blue-500': link.active, 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100': !link.active }" :href="link.url" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Image Preview Modal (for desktop table clicks) -->
        <Teleport to="body">
            <div v-if="showPreview && previewImages" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm p-8" @click.self="showPreview = false">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 max-w-2xl w-full shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white">Inspection Photos</h3>
                        <button @click="showPreview = false" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <ImageLightbox :images="previewImages" />
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
