<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    vessels: Array,
});

const form = useForm({
    file: null,
    vessel_name: '',
});

const showVesselNameModal = ref(false);

const uploadInput = ref(null);
const isDragging = ref(false);

const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.file = file;
    }
};

const handleDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file) {
        form.file = file;
    }
};

const triggerFileInput = () => {
    uploadInput.value.click();
};

const promptVesselName = () => {
    if (!form.file) return;
    showVesselNameModal.value = true;
};

const submitImport = () => {
    if (!form.vessel_name.trim()) return;
    showVesselNameModal.value = false;
    form.post(route('vessels.store'), {
        onSuccess: () => {
            form.reset();
            if (uploadInput.value) uploadInput.value.value = null;
        },
    });
};

const toggleAccess = (vessel, type) => {
    let payload = {};
    if (type === 'surveyor') {
        payload.surveyor_access = !vessel.surveyor_access;
    } else {
        payload.buyer_access = !vessel.buyer_access;
    }

    router.put(route('vessels.update', vessel.id), payload, {
        preserveScroll: true,
    });
};

// --- Edit Vessel Logic ---
const showEditModal = ref(false);
const editingVesselId = ref(null);
const editForm = useForm({
    vessel_name: '',
    arrival_date: '',
    status: '',
});

const openEditModal = (vessel) => {
    editingVesselId.value = vessel.id;
    editForm.vessel_name = vessel.vessel_name || vessel.vessel_code;
    editForm.arrival_date = vessel.arrival_date ? new Date(vessel.arrival_date).toISOString().split('T')[0] : '';
    editForm.status = vessel.status;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('vessels.update', editingVesselId.value), {
        onSuccess: () => closeEditModal(),
    });
};

const deleteVessel = (vessel) => {
    if (confirm('Are you sure? This will delete all logs associated with this vessel.')) {
        router.delete(route('vessels.destroy', vessel.id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Vessel Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="border-l-4 border-slate-900 dark:border-white pl-4 py-1">
                <h2 class="font-extrabold text-2xl uppercase tracking-tight text-slate-900 dark:text-slate-100 leading-tight">
                    Vessel Manifests
                </h2>
                <p class="text-xs font-mono text-slate-500 uppercase tracking-widest mt-1">System Administration / Import Module</p>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Import Section -->
                <div class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
                        <h3 class="text-xs font-bold font-mono tracking-widest uppercase text-slate-800 dark:text-slate-200">Import Interface</h3>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    </div>
                    <div class="p-6">
                        <form @submit.prevent="promptVesselName" class="flex flex-col gap-5">
                            <div 
                                class="relative border-2 border-dashed rounded-sm p-8 flex flex-col items-center justify-center text-center transition-all cursor-pointer group"
                                :class="[
                                    isDragging ? 'border-orange-500 bg-orange-50 dark:bg-slate-800/80 scale-[0.99] shadow-inner' : 'border-slate-300 dark:border-slate-700 hover:border-slate-800 dark:hover:border-slate-400',
                                    form.file ? 'bg-slate-50 dark:bg-slate-800/50' : ''
                                ]"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                @click="triggerFileInput"
                            >
                                <input 
                                    ref="uploadInput"
                                    type="file" 
                                    @change="handleFileUpload"
                                    class="hidden"
                                    accept=".xlsx,.xls,.csv"
                                />
                                
                                <div v-if="!form.file" class="space-y-3 pointer-events-none transition-transform group-hover:-translate-y-1">
                                    <div class="w-12 h-12 mx-auto bg-slate-100 dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden rotate-3 group-hover:rotate-6 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-slate-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-900 dark:text-slate-200 font-bold uppercase tracking-wide">
                                            Drop Manifest File Here
                                        </p>
                                        <p class="text-xs font-mono text-slate-500 mt-1">
                                            .XLSX, .XLS, or .CSV accepted
                                        </p>
                                    </div>
                                </div>
                                
                                <div v-else class="pointer-events-none flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 bg-emerald-50 max-w-min mx-auto bg-emerald-100 dark:bg-emerald-900/30 rounded-sm border border-emerald-200 dark:border-emerald-800 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-emerald-600 dark:text-emerald-400">
                                            <path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ form.file.name }}</p>
                                        <p class="text-xs font-mono text-slate-500 mt-1">Ready to process. Click to replace.</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.errors.file" class="text-red-500 text-xs font-mono uppercase tracking-wide border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 p-3 rounded-sm">{{ form.errors.file }}</div>
                            <div v-if="form.errors.vessel_name" class="text-red-500 text-xs font-mono uppercase tracking-wide border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 p-3 rounded-sm">{{ form.errors.vessel_name }}</div>

                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    :disabled="form.processing || !form.file"
                                    class="px-8 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold uppercase tracking-widest text-xs rounded-sm hover:-translate-y-0.5 hover:shadow-lg transition-all disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none disabled:cursor-not-allowed"
                                >
                                    {{ form.processing ? 'Processing...' : 'Execute Import' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Vessel List -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                        <h3 class="text-xs font-bold font-mono tracking-widest uppercase text-slate-800 dark:text-slate-200">Active Directory</h3>
                    </div>
                    
                    <div class="p-0">
                        <!-- Desktop Table -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-white dark:bg-slate-900 border-b-2 border-slate-200 dark:border-slate-800">
                                        <th scope="col" class="px-6 py-4 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Vessel Ref</th>
                                        <th scope="col" class="px-6 py-4 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Inventory</th>
                                        <th scope="col" class="px-6 py-4 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Dock Date</th>
                                        <th scope="col" class="px-6 py-4 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Surveyor Auth</th>
                                        <th scope="col" class="px-6 py-4 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">Buyer Auth</th>
                                        <th scope="col" class="px-6 py-4 text-xs font-mono font-bold text-slate-500 uppercase tracking-wider text-right">Commands</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    <tr v-for="vessel in vessels" :key="vessel.id" class="group hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link :href="route('vessels.show', vessel.id)" class="text-sm font-bold text-slate-900 dark:text-slate-100 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                                                {{ vessel.vessel_code }}
                                            </Link>
                                            <div class="text-xs font-mono text-slate-400 mt-1">SYS-ID: {{ vessel.id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600 dark:text-slate-400">
                                            <div class="inline-flex items-center px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                                {{ vessel.logs_count }} <span class="text-slate-400 ml-1 text-xs uppercase text-[10px]">UNITS</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-600 dark:text-slate-400">
                                            {{ formatDate(vessel.arrival_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button 
                                                @click="toggleAccess(vessel, 'surveyor')"
                                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-sm border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-1"
                                                :class="[vessel.surveyor_access ? 'bg-orange-500' : 'bg-slate-200 dark:bg-slate-700']"
                                            >
                                                <span 
                                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-sm bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                                    :class="[vessel.surveyor_access ? 'translate-x-4' : 'translate-x-0']"
                                                />
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button 
                                                @click="toggleAccess(vessel, 'buyer')"
                                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-sm border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-1"
                                                :class="[vessel.buyer_access ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700']"
                                            >
                                                <span 
                                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-sm bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                                    :class="[vessel.buyer_access ? 'translate-x-4' : 'translate-x-0']"
                                                />
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end gap-2 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                                 <Link :href="route('vessels.show', vessel.id)" class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-sm transition-all" title="View Details">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    </svg>
                                                </Link>
                                                <button @click="openEditModal(vessel)" class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-sm transition-all" title="Edit Vessel">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>
                                                <button @click="deleteVessel(vessel)" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-sm transition-all" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="vessels.length === 0">
                                        <td colspan="6" class="px-6 py-12 text-center text-sm font-mono text-slate-400">
                                            > No vessels registered. <br>System awaiting import.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (Receipt Style) -->
                        <div class="md:hidden bg-slate-50 dark:bg-[#0f172a] p-3 space-y-4">
                            <div v-for="vessel in vessels" :key="vessel.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                                <!-- Status Line Identifier -->
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-slate-800 dark:bg-slate-500"></div>
                                
                                <div class="p-4 pl-5">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1">Vessel Ref</p>
                                            <Link :href="route('vessels.show', vessel.id)" class="text-lg font-bold text-slate-900 dark:text-white leading-none">
                                                {{ vessel.vessel_code }}
                                            </Link>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-widest mb-1">Date</p>
                                            <p class="text-xs font-mono font-bold text-slate-800 dark:text-slate-200">{{ formatDate(vessel.arrival_date) }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 dark:border-slate-800 py-3 mb-3 font-mono text-xs">
                                        <div>
                                            <span class="text-slate-500 mr-2 block text-[10px] uppercase mb-1 tracking-wider">Inventory</span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ vessel.logs_count }} units</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500 mr-2 block text-[10px] uppercase mb-1 tracking-wider">Sys ID</span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200">#{{ vessel.id }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                                        <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/50 p-2 rounded-sm border border-slate-100 dark:border-slate-800">
                                            <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300 uppercase">Auth: Surveyor</span>
                                            <button 
                                                @click="toggleAccess(vessel, 'surveyor')"
                                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-sm border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                :class="[vessel.surveyor_access ? 'bg-orange-500' : 'bg-slate-300 dark:bg-slate-700']"
                                            >
                                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-sm bg-white ring-0 transition duration-200 ease-in-out" :class="[vessel.surveyor_access ? 'translate-x-4' : 'translate-x-0']" />
                                            </button>
                                        </div>
                                        <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/50 p-2 rounded-sm border border-slate-100 dark:border-slate-800">
                                            <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300 uppercase">Auth: Buyer</span>
                                            <button 
                                                @click="toggleAccess(vessel, 'buyer')"
                                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-sm border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                                :class="[vessel.buyer_access ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700']"
                                            >
                                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-sm bg-white ring-0 transition duration-200 ease-in-out" :class="[vessel.buyer_access ? 'translate-x-4' : 'translate-x-0']" />
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                        <Link :href="route('vessels.show', vessel.id)" class="flex-1 text-center py-2 bg-slate-900 border border-slate-900 dark:bg-white dark:border-white text-white dark:text-slate-900 text-xs font-bold uppercase tracking-widest rounded-sm">
                                            View
                                        </Link>
                                        <button @click="openEditModal(vessel)" class="flex-1 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 text-xs font-bold uppercase tracking-widest rounded-sm">
                                            Edit
                                        </button>
                                        <button @click="deleteVessel(vessel)" class="px-3 py-2 border border-red-200 dark:border-red-900/50 text-red-600 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 text-xs rounded-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="vessels.length === 0" class="text-center p-8 text-sm font-mono text-slate-500 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-sm">
                                > SYSTEM AWAITING IMPORT
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vessel Name Modal (before import) -->
        <Modal :show="showVesselNameModal" @close="showVesselNameModal = false">
            <div class="p-8 dark:bg-slate-900 dark:text-slate-200 border-t-4 border-emerald-500">
                <h2 class="text-xl font-extrabold uppercase tracking-tight text-slate-900 dark:text-slate-100">
                    Name This Vessel
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Enter a name for this vessel/shipment before importing the dataset.</p>
                <div class="h-px bg-slate-200 dark:bg-slate-800 my-4"></div>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="import_vessel_name" value="Vessel Name" class="font-mono text-xs uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1" />
                        <TextInput id="import_vessel_name" type="text" class="mt-1 block w-full rounded-sm border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 shadow-none font-bold text-lg" v-model="form.vessel_name" placeholder="e.g. MV Timber Star" required autofocus />
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-sm p-3 border border-slate-200 dark:border-slate-700">
                        <p class="text-xs font-mono text-slate-500"><span class="font-bold text-slate-700 dark:text-slate-300">File:</span> {{ form.file?.name }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="showVesselNameModal = false" class="px-6 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        Cancel
                    </button>
                    <button type="button" @click="submitImport" :disabled="form.processing || !form.vessel_name.trim()" class="px-6 py-2 bg-emerald-600 text-white text-xs font-bold uppercase tracking-widest hover:bg-emerald-700 hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ form.processing ? 'Importing...' : 'Start Import' }}
                    </button>
                </div>
            </div>
        </Modal>

         <!-- Edit Vessel Modal -->
        <Modal :show="showEditModal" @close="closeEditModal">
            <div class="p-8 dark:bg-slate-900 dark:text-slate-200 border-t-4 border-slate-900 dark:border-white">
                <h2 class="text-xl font-extrabold uppercase tracking-tight text-slate-900 dark:text-slate-100">
                    Edit Manifest Data
                </h2>
                <div class="h-px bg-slate-200 dark:bg-slate-800 my-4"></div>

                <form @submit.prevent="submitEdit" class="mt-6 space-y-5">
                    <div>
                        <InputLabel for="vessel_name" value="Vessel Ref" class="font-mono text-xs uppercase tracking-widest text-slate-600 mb-1" />
                        <TextInput id="vessel_name" type="text" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none font-bold" v-model="editForm.vessel_name" required />
                    </div>

                    <div>
                        <InputLabel for="arrival_date" value="Dock Date" class="font-mono text-xs uppercase tracking-widest text-slate-600 mb-1" />
                        <TextInput id="arrival_date" type="date" class="mt-1 block w-full rounded-sm border-slate-300 focus:border-slate-900 focus:ring-0 shadow-none dark:[color-scheme:dark] font-mono text-sm" v-model="editForm.arrival_date" required />
                    </div>

                    <div>
                        <InputLabel for="status" value="Processing Status" class="font-mono text-xs uppercase tracking-widest text-slate-600 mb-1" />
                        <select id="status" v-model="editForm.status" class="mt-1 block w-full font-bold border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 rounded-sm shadow-none focus:border-slate-900 focus:ring-0">
                            <option value="open">ACTIVE / OPEN</option>
                            <option value="closed">ARCHIVED / CLOSED</option>
                        </select>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                        <button type="button" @click="closeEditModal" class="px-6 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="editForm.processing" class="px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold uppercase tracking-widest hover:shadow-lg transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
