<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import Modal from '@/Components/Modal.vue';
import ViewLogModal from '@/Pages/Admin/Vessels/Partials/ViewLogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    vessel: Object,
    logs: Object,
    filters: Object,
    species_list: Array,
    origins_list: Array,
    buyers_list: Array,
    surveyors_list: Array,
});

const search = ref(props.filters?.search || '');
const speciesFilter = ref(props.filters?.species || '');
const originFilter = ref(props.filters?.origin || '');
const buyerFilter = ref(props.filters?.buyer || '');
const surveyStatusFilter = ref(props.filters?.survey_status || '');
const matchStatusFilter = ref(props.filters?.match_status || '');
const surveyorFilter = ref(props.filters?.surveyor || '');

const hasActiveFilters = computed(() => {
    return search.value || speciesFilter.value || originFilter.value || buyerFilter.value || surveyStatusFilter.value || matchStatusFilter.value || surveyorFilter.value;
});

const applyFilters = () => {
    router.get(route('admin.survey-data'), {
        search: search.value,
        species: speciesFilter.value,
        origin: originFilter.value,
        buyer: buyerFilter.value,
        survey_status: surveyStatusFilter.value,
        match_status: matchStatusFilter.value,
        surveyor: surveyorFilter.value,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    search.value = '';
    speciesFilter.value = '';
    originFilter.value = '';
    buyerFilter.value = '';
    surveyStatusFilter.value = '';
    matchStatusFilter.value = '';
    surveyorFilter.value = '';
    applyFilters();
};

const debouncedSearch = debounce(() => applyFilters(), 300);
watch(search, () => debouncedSearch());

const fmt = (val, digits = 2) => {
    if (val === null || val === undefined || val === '') return '-';
    return parseFloat(val).toFixed(digits);
};
const fmtVol = (val) => fmt(val, 3);

// --- View Modal Logic ---
const showViewModal = ref(false);
const viewingLog = ref(null);

const openViewModal = (log) => {
    viewingLog.value = log;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    viewingLog.value = null;
};

const switchToEditFromView = () => {
    const log = viewingLog.value;
    closeViewModal();
    openEditModal(log);
};

// --- Edit Modal Logic ---
const showEditModal = ref(false);
const editingLogId = ref(null);

const form = useForm({
    serial_no: '',
    tag_no: '',
    log_no: '',
    species: '',
    origin: '',
    length: '',
    girth_butt: '',
    girth_top: '',
    diameter: '',
    l_ref: '',
    d_ref: '',
    calc_length: '',
    vol_cbm: '',
    buyer_name: '',
    remarks: '',
});

const openEditModal = (log) => {
    editingLogId.value = log.id;
    form.serial_no = log.serial_no;
    form.tag_no = log.tag_no;
    form.log_no = log.log_no ?? '';
    form.species = log.species;
    form.origin = log.origin ?? '';
    form.length = log.length;
    form.girth_butt = log.girth_butt;
    form.girth_top = log.girth_top;
    form.diameter = log.diameter;
    form.vol_cbm = log.vol_cbm;
    form.l_ref = log.l_ref ?? '';
    form.d_ref = log.d_ref ?? '';
    form.calc_length = log.calc_length ?? '';
    form.buyer_name = log.buyer_name ?? '';
    form.remarks = log.remarks ?? '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    form.reset();
};

const submitForm = () => {
    form.put(route('logs.update', editingLogId.value), {
        onSuccess: () => closeEditModal(),
    });
};
</script>

<template>
    <Head title="Survey Data" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="border-l-4 border-emerald-500 pl-4 py-1">
                    <h2 class="font-extrabold text-2xl uppercase tracking-tight text-slate-900 dark:text-slate-100 leading-tight">
                        Survey Data
                    </h2>
                    <p class="text-xs font-mono text-slate-500 uppercase tracking-widest mt-1">
                        {{ vessel?.vessel_name || 'No Active Vessel' }} — Inspection Records
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.welcome')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-slate-50 transition">
                        ← Dashboard
                    </Link>
                    <a v-if="vessel" :href="route('admin.survey-data.pdf')" class="px-4 py-2 bg-red-600 text-white text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-red-700 transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </a>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div v-if="!vessel" class="text-center py-20 text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                    <p class="text-lg font-semibold">No active vessel. Enable surveyor access on a vessel first.</p>
                </div>

                <template v-else>
                    <!-- Filters -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-sm shadow-sm">
                        <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                            <h3 class="text-xs font-bold font-mono tracking-widest uppercase text-slate-800 dark:text-slate-200">Filters</h3>
                            <button v-if="hasActiveFilters" @click="clearFilters" class="text-xs text-red-500 hover:text-red-700 font-bold uppercase tracking-widest transition">Clear All</button>
                        </div>
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                            <div class="relative col-span-2 sm:col-span-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                </div>
                                <input v-model="search" type="text" placeholder="Search..." class="w-full pl-10 h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono placeholder:text-slate-400 focus:border-emerald-500 focus:ring-0 transition-colors" />
                            </div>
                            <select v-model="speciesFilter" @change="applyFilters" class="h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-emerald-500 focus:ring-0">
                                <option value="">ALL SPECIES</option>
                                <option v-for="s in species_list" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <select v-model="originFilter" @change="applyFilters" class="h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-emerald-500 focus:ring-0">
                                <option value="">ALL ORIGINS</option>
                                <option v-for="o in origins_list" :key="o" :value="o">{{ o }}</option>
                            </select>
                            <select v-model="buyerFilter" @change="applyFilters" class="h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-emerald-500 focus:ring-0">
                                <option value="">ALL BUYERS</option>
                                <option v-for="b in buyers_list" :key="b" :value="b">{{ b }}</option>
                            </select>
                            <select v-model="surveyStatusFilter" @change="applyFilters" class="h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-emerald-500 focus:ring-0 uppercase">
                                <option value="">ALL SURVEY</option>
                                <option value="surveyed">SURVEYED</option>
                                <option value="not_surveyed">NOT SURVEYED</option>
                            </select>
                            <select v-model="matchStatusFilter" @change="applyFilters" class="h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-emerald-500 focus:ring-0 uppercase">
                                <option value="">ALL RESULT</option>
                                <option value="matched">MATCHED</option>
                                <option value="mismatched">MISMATCHED</option>
                            </select>
                            <select v-model="surveyorFilter" @change="applyFilters" class="h-10 rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-emerald-500 focus:ring-0">
                                <option value="">ALL SURVEYORS</option>
                                <option v-for="s in surveyors_list" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-sm shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-700">
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">SN</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">LOG#</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">TAG REF</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">SPECIES</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">ORIGIN</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider text-right">LENGTH</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider text-right">DIA</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider text-right">Volume (CBM)</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">BUYER</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider text-center">STATE</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">SURVEYOR</th>
                                        <th class="px-3 py-3 text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider text-right">IO</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr v-for="log in logs.data" :key="log.id" class="group hover:bg-slate-50 dark:hover:bg-slate-700/30 text-slate-800 dark:text-slate-300 transition-colors">
                                        <td class="px-3 py-2.5 text-xs font-mono">{{ log.serial_no }}</td>
                                        <td class="px-3 py-2.5 text-xs font-mono text-slate-500">{{ log.log_no || '-' }}</td>
                                        <td class="px-3 py-2.5 text-sm font-mono font-bold tracking-tighter">{{ log.tag_no }}</td>
                                        <td class="px-3 py-2.5 text-xs font-semibold uppercase">{{ log.species }}</td>
                                        <td class="px-3 py-2.5 text-xs font-mono text-slate-500 uppercase">{{ log.origin || '-' }}</td>
                                        <td class="px-3 py-2.5 text-right text-xs font-mono">
                                            {{ fmt(log.length) }}
                                            <span v-if="log.l_ref" class="text-red-500 text-[10px] ml-0.5">-{{ fmt(log.l_ref) }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-right text-xs font-mono">{{ fmt(log.diameter) }}</td>
                                        <td class="px-3 py-2.5 text-right">
                                            <div class="text-xs font-mono font-bold text-slate-900 dark:text-slate-100">{{ fmtVol(log.vol_cbm) }}</div>
                                            <div v-if="log.inspection && !log.inspection.is_match && log.inspection.actual_vol_cbm" class="text-[10px] font-mono font-bold text-red-500 bg-red-50 dark:bg-red-900/20 mt-0.5 rounded-sm px-1">
                                                ACT: {{ fmtVol(log.inspection.actual_vol_cbm) }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 truncate max-w-[100px]">{{ log.buyer_name || '-' }}</td>
                                        <td class="px-3 py-2.5 text-center">
                                            <span v-if="!log.inspection" class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold font-mono tracking-widest uppercase rounded border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">PENDING</span>
                                            <span v-else-if="log.inspection.is_match" class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold font-mono tracking-widest uppercase rounded border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400">✓ MATCH</span>
                                            <span v-else class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold font-mono tracking-widest uppercase rounded border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400">⚠ DIFF</span>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs text-slate-500">{{ log.inspection?.surveyor?.name || '-' }}</td>
                                        <td class="px-3 py-2.5 text-right">
                                            <div class="flex justify-end gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openViewModal(log)" class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 rounded-sm transition-all" title="View Details">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </button>
                                                <button @click="openEditModal(log)" class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-sm transition-all" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="logs.data.length === 0">
                                        <td colspan="12" class="px-6 py-12 text-center text-sm font-mono text-slate-400">No data matches current query</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden space-y-3">
                        <div v-for="log in logs.data" :key="log.id" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden relative rounded-sm">
                            <div class="absolute left-0 top-0 bottom-0 w-1" :class="!log.inspection ? 'bg-slate-300' : (log.inspection.is_match ? 'bg-emerald-500' : 'bg-red-500')"></div>
                            <div class="p-4 pl-5">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">TAG REF</p>
                                        <h3 class="font-mono font-extrabold text-lg text-slate-900 dark:text-slate-100 tracking-tighter leading-none">{{ log.tag_no }}</h3>
                                        <p class="text-xs font-mono text-slate-500 mt-1">SN: {{ log.serial_no }} | LOG#: {{ log.log_no || '-' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span v-if="!log.inspection" class="inline-flex px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 text-[10px] font-bold font-mono uppercase rounded">PENDING</span>
                                        <span v-else-if="log.inspection.is_match" class="inline-flex px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold font-mono uppercase rounded">MATCH</span>
                                        <span v-else class="inline-flex px-1.5 py-0.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-[10px] font-bold font-mono uppercase rounded">DIFF</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Species</p>
                                        <p class="font-bold text-xs text-slate-800 dark:text-slate-200 uppercase truncate">{{ log.species }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Origin</p>
                                        <p class="font-bold text-xs text-slate-800 dark:text-slate-200 uppercase truncate">{{ log.origin || '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Volume (CBM)</p>
                                        <p class="font-extrabold font-mono text-sm text-slate-900 dark:text-white">{{ fmtVol(log.vol_cbm) }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Length</p>
                                        <p class="font-mono text-xs text-slate-700 dark:text-slate-300">
                                            {{ fmt(log.length) }}
                                            <span v-if="log.l_ref" class="text-red-500 text-[10px] block">-{{ fmt(log.l_ref) }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">DIA</p>
                                        <p class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ fmt(log.diameter) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Buyer</p>
                                        <p class="font-mono text-xs text-slate-700 dark:text-slate-300 truncate">{{ log.buyer_name || '-' }}</p>
                                    </div>
                                </div>
                                <div v-if="log.inspection?.surveyor" class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-500 flex items-center justify-between">
                                    <span>Surveyor: <span class="font-bold text-slate-700 dark:text-slate-300">{{ log.inspection.surveyor.name }}</span></span>
                                    <div class="flex gap-1">
                                        <button @click="openViewModal(log)" class="p-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-sm border border-slate-200 dark:border-slate-600 hover:bg-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </button>
                                        <button @click="openEditModal(log)" class="p-1.5 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 rounded-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!logs?.data?.length" class="text-center py-12 text-sm font-mono text-slate-400">No data matches current query</div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex flex-wrap gap-1 justify-center" v-if="logs.links.length > 3">
                        <template v-for="(link, k) in logs.links" :key="k">
                            <div v-if="link.url === null" class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-sm" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                            <Link v-else class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold border rounded-sm transition-all" :class="{ 'bg-emerald-600 text-white border-emerald-600 z-10 scale-105': link.active, 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700': !link.active }" :href="link.url" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <ViewLogModal 
            :show="showViewModal" 
            :log="viewingLog" 
            @close="closeViewModal" 
            @edit="switchToEditFromView"
        />

        <!-- Edit Form Modal -->
        <Modal :show="showEditModal" @close="closeEditModal" maxWidth="2xl">
            <div class="p-8 dark:bg-slate-900 dark:text-slate-200 border-t-4 border-emerald-500">
                <h2 class="text-xl font-extrabold uppercase tracking-tight text-slate-900 dark:text-slate-100">
                    Edit Log Entry
                </h2>
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-500 mt-1">
                    REF: {{ form.tag_no }}
                </p>
                <div class="h-px bg-slate-200 dark:bg-slate-800 my-4"></div>

                <form @submit.prevent="submitForm" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="edit_serial_no" value="Serial No" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="edit_serial_no" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-mono" v-model="form.serial_no" required />
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="edit_tag_no" value="Tag Number" class="font-mono text-[10px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-bold mb-1" />
                        <TextInput id="edit_tag_no" type="text" class="mt-1 block w-full rounded-sm border-emerald-300 focus:border-emerald-500 focus:ring-0 shadow-sm font-mono font-bold text-lg" v-model="form.tag_no" required />
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="edit_species" value="Species" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="edit_species" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-bold uppercase" v-model="form.species" required />
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="edit_length" value="Length (m)" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="edit_length" type="number" step="0.001" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-mono font-bold text-right" v-model="form.length" required />
                    </div>
                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="edit_diameter" value="Diameter (cm)" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="edit_diameter" type="number" step="0.1" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-mono font-bold text-right" v-model="form.diameter" required />
                    </div>
                    <div class="md:col-span-2 bg-slate-900 dark:bg-slate-800 p-4 rounded-sm border border-slate-700">
                        <InputLabel for="edit_vol_cbm" value="Total Volume (CBM)" class="font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1" />
                        <TextInput id="edit_vol_cbm" type="number" step="0.000001" class="mt-1 block w-full rounded-sm border-slate-600 bg-slate-800 dark:bg-slate-900 text-white focus:border-emerald-500 focus:ring-0 shadow-inner font-mono font-extrabold text-xl text-right" v-model="form.vol_cbm" required />
                    </div>

                    <div class="md:col-span-2 mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" @click="closeEditModal" class="px-6 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 rounded-sm transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-emerald-600 text-white text-xs font-bold uppercase tracking-widest rounded-sm disabled:opacity-50 hover:bg-emerald-700 hover:shadow-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
