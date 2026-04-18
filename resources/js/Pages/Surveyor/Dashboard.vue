<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    vessel: Object,
    logs: Object,
    filters: Object,
    buyers_list: Array,
    species_list: Array,
    origins_list: Array,
});

const search = ref(props.filters?.search || '');
const surveyStatus = ref(props.filters?.survey_status || '');
const matchStatus = ref(props.filters?.match_status || '');
const buyerFilter = ref(props.filters?.buyer || '');
const speciesFilter = ref(props.filters?.species || '');
const originFilter = ref(props.filters?.origin || '');

const applyFilters = () => {
    router.get(route('surveyor.dashboard'), {
        search: search.value,
        survey_status: surveyStatus.value,
        match_status: matchStatus.value,
        buyer: buyerFilter.value,
        species: speciesFilter.value,
        origin: originFilter.value,
    }, { preserveState: true, replace: true });
};

const debouncedSearch = debounce((val) => {
    applyFilters();
}, 300);

watch(search, (val) => debouncedSearch(val));
</script>

<template>
    <Head title="Survey Index" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-extrabold text-xl text-slate-900 dark:text-white">Inspection Index</h2>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ vessel?.vessel_name || vessel?.vessel_code || 'No active vessel' }}</p>
                </div>
                <Link :href="route('surveyor.search')" class="px-4 py-2 bg-amber-500 text-white font-bold text-sm rounded-lg hover:bg-amber-600 transition">
                    Search & Inspect
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

                <div v-if="!vessel" class="text-center py-20 text-slate-500 dark:text-slate-400">
                    <p class="text-lg font-semibold">No active vessel for survey.</p>
                    <Link :href="route('surveyor.welcome')" class="text-amber-500 hover:underline mt-2 inline-block">← Go back</Link>
                </div>

                <template v-else>
                    <!-- Filters -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search LOG# or TAG..."
                                    class="w-full h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 text-sm font-mono focus:border-amber-500 focus:ring-amber-500 placeholder:text-slate-400"
                                />
                            </div>
                            <select v-model="surveyStatus" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-amber-500 focus:ring-0">
                                <option value="">All Status</option>
                                <option value="surveyed">Surveyed</option>
                                <option value="not_surveyed">Not Surveyed</option>
                            </select>
                            <select v-model="matchStatus" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-amber-500 focus:ring-0">
                                <option value="">All Result</option>
                                <option value="matched">Matched</option>
                                <option value="mismatched">Mismatched</option>
                            </select>
                            <select v-model="speciesFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-amber-500 focus:ring-0">
                                <option value="">All Species</option>
                                <option v-for="s in species_list" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <select v-model="buyerFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-amber-500 focus:ring-0">
                                <option value="">All Buyers</option>
                                <option v-for="b in buyers_list" :key="b" :value="b">{{ b }}</option>
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
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">Length</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">DIA</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">Volume (CBM)</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase">Buyer</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-center">Status</th>
                                        <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="log in logs?.data" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ log.serial_no }}</td>
                                        <td class="px-4 py-3 text-sm font-mono font-bold text-slate-900 dark:text-white">{{ log.tag_no }}</td>
                                        <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ log.log_no || '-' }}</td>
                                        <td class="px-4 py-3 text-sm font-semibold uppercase text-slate-700 dark:text-slate-300">{{ log.species }}</td>
                                        <td class="px-4 py-3 text-right text-xs font-mono dark:text-slate-300">
                                            {{ log.length }}
                                            <span v-if="log.l_ref" class="text-red-500 text-[10px] ml-0.5">-{{ log.l_ref }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs font-mono dark:text-slate-300">
                                            {{ log.diameter }}
                                            <span v-if="log.d_ref" class="text-red-500 text-[10px] ml-0.5">-{{ log.d_ref }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-xs font-mono font-bold dark:text-slate-300">{{ parseFloat(log.vol_cbm).toFixed(3) }}</td>
                                        <td class="px-4 py-3 text-xs font-mono dark:text-slate-300">{{ log.buyer_name || '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span v-if="!log.inspection" class="inline-flex px-2 py-0.5 text-[10px] font-bold font-mono uppercase rounded bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">PENDING</span>
                                            <span v-else-if="log.inspection.is_match" class="inline-flex px-2 py-0.5 text-[10px] font-bold font-mono uppercase rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">✓ MATCH</span>
                                            <span v-else class="inline-flex px-2 py-0.5 text-[10px] font-bold font-mono uppercase rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800">⚠ DIFF</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <Link :href="route('surveyor.show', log.id)" class="text-amber-600 dark:text-amber-400 hover:underline text-sm font-semibold">Inspect</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="!logs?.data?.length">
                                        <td colspan="10" class="px-6 py-12 text-center text-sm text-slate-400">No logs match filters</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-3">
                        <Link
                            v-for="log in logs?.data"
                            :key="log.id"
                            :href="route('surveyor.show', log.id)"
                            class="block bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 relative overflow-hidden"
                        >
                            <div class="absolute left-0 top-0 bottom-0 w-1" :class="!log.inspection ? 'bg-slate-300' : (log.inspection.is_match ? 'bg-emerald-500' : 'bg-red-500')"></div>
                            <div class="pl-3">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-lg font-mono font-extrabold text-amber-600 dark:text-amber-400">{{ log.tag_no }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">SN: {{ log.serial_no }} | LOG#: {{ log.log_no || '-' }}</p>
                                    </div>
                                    <span v-if="!log.inspection" class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded">PENDING</span>
                                    <span v-else-if="log.inspection.is_match" class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded">MATCH</span>
                                    <span v-else class="text-[10px] font-bold px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded">DIFF</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-500">
                                    <span class="font-bold uppercase text-slate-700 dark:text-slate-300">{{ log.species }}</span>
                                    <span>{{ log.length }}m × {{ log.diameter }}cm</span>
                                    <span class="font-bold">{{ parseFloat(log.vol_cbm).toFixed(3) }} CBM</span>
                                    <span v-if="log.buyer_name" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px] font-bold text-slate-600 dark:text-slate-400">{{ log.buyer_name }}</span>
                                </div>
                            </div>
                        </Link>
                        <div v-if="!logs?.data?.length" class="text-center py-12 text-slate-400">No logs match filters</div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex flex-wrap gap-1 justify-center" v-if="logs?.links?.length > 3">
                        <template v-for="(link, k) in logs.links" :key="k">
                            <div v-if="link.url === null" class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                            <Link v-else class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold border rounded transition-all" :class="{ 'bg-amber-500 text-white border-amber-500 z-10 scale-105': link.active, 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100': !link.active }" :href="link.url" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
