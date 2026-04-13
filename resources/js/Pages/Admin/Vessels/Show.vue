<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';
import Modal from '@/Components/Modal.vue'; 
import ViewLogModal from './Partials/ViewLogModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    vessel: Object,
    logs: Object,
    filters: Object,
    species_list: Array,
    origins_list: Array,
});

const formattedArrivalDate = computed(() => {
    if (!props.vessel.arrival_date) return 'N/A';
    const date = new Date(props.vessel.arrival_date);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const speciesFilter = ref(props.filters.species || '');

const debouncedSearch = debounce((val) => {
    router.get(route('vessels.show', props.vessel.id), { 
        search: val,
        status: statusFilter.value,
        species: speciesFilter.value
    }, { preserveState: true, replace: true });
}, 300);

watch(search, (val) => debouncedSearch(val));

const updateFilter = () => {
    router.get(route('vessels.show', props.vessel.id), { 
        search: search.value,
        status: statusFilter.value,
        species: speciesFilter.value
    }, { preserveState: true, replace: true });
};

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

// --- Edit/Add Modal Logic ---
const showEditModal = ref(false);
const isEditing = ref(false);
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
    vol_cbm: '',
    l_ref: '',
    d_ref: '',
    buyer_name: '',
    remarks: '',
});

const openAddModal = () => {
    isEditing.value = false;
    form.reset();
    showEditModal.value = true;
};

const openEditModal = (log) => {
    isEditing.value = true;
    editingLogId.value = log.id;
    populateForm(log);
    showEditModal.value = true;
};

const populateForm = (log) => {
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
    form.buyer_name = log.buyer_name ?? '';
    form.remarks = log.remarks ?? '';
};

const closeEditModal = () => {
    showEditModal.value = false;
    form.reset();
    isEditing.value = false;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('logs.update', editingLogId.value), {
            onSuccess: () => closeEditModal(),
        });
    } else {
        form.post(route('vessels.logs.store', props.vessel.id), {
            onSuccess: () => closeEditModal(),
        });
    }
};

const deleteLog = (logId) => {
    if (confirm('Are you sure you want to delete this log?')) {
        router.delete(route('logs.destroy', logId), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="`Vessel ${vessel.vessel_code}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <!-- Industrial Manifest Header Component -->
                <div class="border-l-4 border-slate-900 dark:border-white pl-4 py-1">
                    <p class="text-xs font-mono font-bold text-slate-500 uppercase tracking-widest mb-1">
                        Sys-Id: {{ vessel.id }}
                    </p>
                    <h2 class="font-extrabold text-3xl uppercase tracking-tighter text-slate-900 dark:text-slate-100 leading-none">
                        Vessel: {{ vessel.vessel_code }} 
                    </h2>
                    <div class="mt-3 inline-flex items-center gap-2 bg-slate-900 dark:bg-slate-800 text-slate-100 px-3 py-1 text-xs font-mono tracking-widest uppercase rounded-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3 text-emerald-400">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        ARRIVAL: {{ formattedArrivalDate }}
                    </div>
                </div>

                <div class="flex gap-3 w-full sm:w-auto">
                    <Link :href="route('vessels.index')" class="flex-1 sm:flex-none text-center px-6 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-700 dark:text-slate-300 rounded-sm transition text-xs font-bold uppercase tracking-widest shadow-sm hover:shadow-md">
                        Return
                    </Link>
                    <button @click="openAddModal" class="flex-1 sm:flex-none px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:opacity-90 rounded-sm transition text-xs font-bold uppercase tracking-widest shadow-sm hover:-translate-y-0.5 hover:shadow-md">
                        + Add Input
                    </button>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters / Control Strip -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm">
                    <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-xs font-bold font-mono tracking-widest uppercase text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg>
                            Data Controls
                        </h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="relative">
                            <label class="sr-only">Search</label>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 text-slate-400">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Search SN, Tag, Ref..." 
                                class="w-full pl-10 h-10 rounded-sm border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-sm font-mono placeholder:text-slate-400 focus:border-slate-800 focus:ring-0 shadow-none transition-colors"
                            >
                        </div>
                        <div>
                            <select v-model="speciesFilter" @change="updateFilter" class="w-full h-10 rounded-sm border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-sm font-mono focus:border-slate-800 focus:ring-0 shadow-none transition-colors">
                                <option value="">ALL SPECIES // DEFAULT</option>
                                <option v-for="s in species_list" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div>
                            <select v-model="statusFilter" @change="updateFilter" class="w-full h-10 rounded-sm border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-sm font-mono focus:border-slate-800 focus:ring-0 shadow-none transition-colors uppercase">
                                <option value="">ALL STATE</option>
                                <option value="pending">PENDING</option>
                                <option value="verified">VERIFIED (MATCH)</option>
                                <option value="mismatch">MISMATCH (WARN)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-sm shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left border-collapse table-fixed">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-800">
                                    <th class="w-16 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">SN</th>
                                    <th class="w-32 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Tag Ref</th>
                                    <th class="w-auto px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Species</th>
                                    <th class="w-24 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider text-right">Length</th>
                                    <th class="w-24 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider text-right">Dia</th>
                                    <th class="w-28 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider text-right">CBM</th>
                                    <th class="w-28 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider text-center">State</th>
                                    <th class="w-32 px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider text-right">IO</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="log in logs.data" :key="log.id" class="group hover:bg-slate-50 dark:hover:bg-slate-800/40 text-slate-800 dark:text-slate-300 transition-colors">
                                    <td class="px-4 py-3 text-xs font-mono tracking-tighter">{{ log.serial_no }}</td>
                                    <td class="px-4 py-3 text-sm font-mono font-bold tracking-tighter" :title="log.tag_no">{{ log.tag_no }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold truncate uppercase tracking-wide" :title="log.species">{{ log.species }}</td>
                                    
                                    <!-- Length Check -->
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-xs font-mono font-semibold">{{ log.length }}</div>
                                        <div v-if="log.inspection && !log.inspection.is_match" class="text-[10px] font-mono font-bold text-red-500 bg-red-50 dark:bg-red-900/20 mt-1 rounded-sm px-1">
                                            ACT: {{ log.inspection.actual_length }}
                                        </div>
                                    </td>

                                    <!-- Dia Check -->
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-xs font-mono font-semibold">{{ log.diameter }}</div>
                                        <div v-if="log.inspection && !log.inspection.is_match" class="text-[10px] font-mono font-bold text-red-500 bg-red-50 dark:bg-red-900/20 mt-1 rounded-sm px-1">
                                            ACT: {{ log.inspection.actual_diameter }}
                                        </div>
                                    </td>

                                    <!-- Volume Check -->
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-xs font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.vol_cbm }}</div>
                                        <div v-if="log.inspection && !log.inspection.is_match" class="text-[10px] font-mono font-bold text-red-500 bg-red-50 dark:bg-red-900/20 mt-1 rounded-sm px-1">
                                            ACT: {{ log.inspection.actual_vol_cbm }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span v-if="!log.inspection" class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold font-mono tracking-widest uppercase rounded border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            PENDING
                                        </span>
                                        <span v-else-if="log.inspection.is_match" class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold font-mono tracking-widest uppercase rounded border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 shadow-sm scale-110">
                                            ✓ MATCH
                                        </span>
                                        <span v-else class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold font-mono tracking-widest uppercase rounded border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400">
                                            ⚠ DIFF
                                        </span>
                                    </td>
                                    
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openViewModal(log)" class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 rounded-sm transition-all" title="View Inspector Data">
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
                                            <button @click="deleteLog(log.id)" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-sm transition-all" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-sm font-mono text-slate-400 select-none">
                                        > NO DATA MATCHES CURRENT QUERY
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View (Fragmented Receipt Style) -->
                <div class="md:hidden space-y-4">
                    <div v-for="log in logs.data" :key="log.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                         <!-- Indicator Bar -->
                        <div class="absolute left-0 top-0 bottom-0 w-1" :class="!log.inspection ? 'bg-slate-300' : (log.inspection.is_match ? 'bg-emerald-500' : 'bg-red-500')"></div>

                        <div class="p-4 pl-5">
                            <div class="flex justify-between items-start mb-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div>
                                    <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Tag Ref</p>
                                    <h3 class="font-mono font-extrabold text-xl text-slate-900 dark:text-slate-100 tracking-tighter leading-none">{{ log.tag_no }}</h3>
                                    <p class="text-xs font-mono text-slate-500 mt-1">SN: {{ log.serial_no }}</p>
                                </div>
                                <div class="text-right">
                                    <span v-if="!log.inspection" class="inline-flex px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-[10px] font-bold font-mono tracking-widest uppercase rounded">PENDING</span>
                                    <span v-else-if="log.inspection.is_match" class="inline-flex px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold font-mono tracking-widest uppercase rounded">MATCH</span>
                                    <span v-else class="inline-flex px-1.5 py-0.5 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-[10px] font-bold font-mono tracking-widest uppercase rounded">DIFF</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div>
                                    <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Species</p>
                                    <p class="font-bold text-sm text-slate-800 dark:text-slate-200 uppercase">{{ log.species }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">Dims</p>
                                    <p class="font-bold font-mono text-sm text-slate-800 dark:text-slate-200">{{ log.length }}m × {{ log.diameter }}cm</p>
                                </div>
                            </div>
                            
                            <!-- Inspection Details block if mismatch -->
                            <div v-if="log.inspection && !log.inspection.is_match" class="bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/50 p-2 rounded-sm mb-4">
                                <p class="text-[10px] font-bold text-red-600 dark:text-red-400 uppercase tracking-widest mb-1 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    Surveyor Diff
                                </p>
                                <div class="grid grid-cols-3 gap-2 font-mono text-[10px]">
                                    <div>L: <span class="text-red-600 dark:text-red-400 font-bold">{{ log.inspection.actual_length }}</span></div>
                                    <div>D: <span class="text-red-600 dark:text-red-400 font-bold">{{ log.inspection.actual_diameter }}</span></div>
                                    <div>V: <span class="text-red-600 dark:text-red-400 font-bold">{{ log.inspection.actual_vol_cbm }}</span></div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                                <div class="mr-auto">
                                    <p class="text-[10px] uppercase font-mono tracking-widest text-slate-400 mb-0.5">CBM</p>
                                    <p class="font-extrabold font-mono text-lg text-slate-900 dark:text-white leading-none">{{ log.vol_cbm }}</p>
                                </div>
                                <div class="flex items-end gap-1.5">
                                    <button @click="openViewModal(log)" class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-sm border border-slate-200 dark:border-slate-700 hover:bg-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    </button>
                                    <button @click="openEditModal(log)" class="p-2 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 rounded-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination (Industrial styling) -->
                <div class="mt-6 flex flex-wrap gap-1 justify-center sm:justify-start" v-if="logs.links.length > 3">
                    <template v-for="(link, k) in logs.links" :key="k">
                        <!-- Removed arbitrary inline flex properties because standard div works -->
                        <div v-if="link.url === null" class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 rounded-sm shadow-sm" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                        <Link v-else class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold border rounded-sm transition-all shadow-sm" :class="{ 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-slate-900 dark:border-white z-10 scale-105': link.active, 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700': !link.active }" :href="link.url" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                    </template>
                </div>
            </div>
        </div>

        <ViewLogModal 
            :show="showViewModal" 
            :log="viewingLog" 
            @close="closeViewModal" 
            @edit="switchToEditFromView"
        />

        <!-- Edit/Add Form Modal (Brutalist style) -->
        <Modal :show="showEditModal" @close="closeEditModal" maxWidth="2xl">
            <div class="p-8 dark:bg-slate-900 dark:text-slate-200 border-t-4 border-slate-900 dark:border-white">
                <h2 class="text-xl font-extrabold uppercase tracking-tight text-slate-900 dark:text-slate-100">
                    {{ isEditing ? 'Edit Log Entry' : 'Manual Entry' }}
                </h2>
                <p class="text-[10px] font-mono uppercase tracking-widest text-slate-500 mt-1">
                    {{ isEditing ? 'REF: ' + form.tag_no : 'NEW RECORD INBOUND' }}
                </p>
                <div class="h-px bg-slate-200 dark:bg-slate-800 my-4"></div>

                <form @submit.prevent="submitForm" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="serial_no" value="Serial No" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="serial_no" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-mono" v-model="form.serial_no" required />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="tag_no" value="Tag Number" class="font-mono text-[10px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-bold mb-1" />
                        <TextInput id="tag_no" type="text" class="mt-1 block w-full rounded-sm border-emerald-300 focus:border-emerald-500 focus:ring-0 shadow-sm font-mono font-bold text-lg" v-model="form.tag_no" required />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="species" value="Species" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="species" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-bold uppercase" v-model="form.species" required />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="log_no" value="Log No (Opt)" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="log_no" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none" v-model="form.log_no" />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="length" value="Length (m)" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="length" type="number" step="0.001" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-mono font-bold text-right" v-model="form.length" required />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="diameter" value="Diameter (cm)" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                        <TextInput id="diameter" type="number" step="0.1" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-mono font-bold text-right" v-model="form.diameter" required />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="girth_butt" value="Girth B." class="font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1" />
                        <TextInput id="girth_butt" type="number" step="0.1" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none text-right font-mono" v-model="form.girth_butt" required />
                    </div>

                    <div class="bg-slate-50/50 dark:bg-slate-800/20 p-3 border border-slate-100 dark:border-slate-800 rounded-sm">
                        <InputLabel for="girth_top" value="Girth T." class="font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1" />
                        <TextInput id="girth_top" type="number" step="0.1" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none text-right font-mono" v-model="form.girth_top" required />
                    </div>

                    <div class="md:col-span-2 bg-slate-900 dark:bg-slate-800 p-4 rounded-sm border border-slate-700">
                        <InputLabel for="vol_cbm" value="Total Volume (CBM)" class="font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1" />
                        <TextInput id="vol_cbm" type="number" step="0.000001" class="mt-1 block w-full rounded-sm border-slate-600 bg-slate-800 dark:bg-slate-900 text-white focus:border-emerald-500 focus:ring-0 shadow-inner font-mono font-extrabold text-xl text-right" v-model="form.vol_cbm" required />
                    </div>
                    
                    <div class="p-2 border border-dashed border-red-200 dark:border-red-900/50 rounded-sm">
                        <InputLabel for="l_ref" value="L-Loss (Opt)" class="font-mono text-[10px] uppercase tracking-widest text-red-500 mb-1" />
                        <TextInput id="l_ref" type="number" step="0.000001" class="mt-1 block w-full rounded-sm border-red-100 dark:border-red-900/30 dark:bg-slate-800 focus:border-red-500 focus:ring-0 shadow-none text-right font-mono" v-model="form.l_ref" />
                    </div>

                    <div class="p-2 border border-dashed border-red-200 dark:border-red-900/50 rounded-sm">
                        <InputLabel for="d_ref" value="D-Decomp (Opt)" class="font-mono text-[10px] uppercase tracking-widest text-red-500 mb-1" />
                        <TextInput id="d_ref" type="number" step="0.000001" class="mt-1 block w-full rounded-sm border-red-100 dark:border-red-900/30 dark:bg-slate-800 focus:border-red-500 focus:ring-0 shadow-none text-right font-mono" v-model="form.d_ref" />
                    </div>
                    
                    <div class="md:col-span-2 space-y-3 p-4 border border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/10 rounded-sm mt-2">
                        <div>
                             <InputLabel for="origin" value="Lineplate / Origin" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                             <TextInput id="origin" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 shadow-none" v-model="form.origin" />
                        </div>

                         <div>
                             <InputLabel for="buyer_name" value="Buyer Profile" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                             <TextInput id="buyer_name" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 shadow-none" v-model="form.buyer_name" />
                        </div>

                         <div>
                             <InputLabel for="remarks" value="Inspector Remarks" class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1" />
                             <TextInput id="remarks" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 shadow-none text-sm placeholder:text-slate-300 italic" v-model="form.remarks" placeholder="Add additional context..." />
                        </div>
                    </div>

                    <div class="md:col-span-2 mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" @click="closeEditModal" class="px-6 py-2.5 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 rounded-sm transition">
                            Abort
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold uppercase tracking-widest rounded-sm disabled:opacity-50 hover:shadow-lg transition">
                            {{ isEditing ? 'Commit Update' : 'Write Record' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
