<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ImageLightbox from '@/Components/ImageLightbox.vue';

const props = defineProps({
    show: Boolean,
    log: Object,
});

const emit = defineEmits(['close', 'edit']);

const close = () => {
    emit('close');
};

const edit = () => {
    emit('edit', props.log);
};
</script>

<template>
    <Modal :show="show" @close="close" max-width="4xl">
        <div class="p-6 bg-white dark:bg-slate-800 dark:text-slate-200 transition-colors" v-if="log">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Log Details</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tag No: <span class="font-mono font-bold">{{ log.tag_no }}</span></p>
                </div>
                <button @click="edit" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit
                </button>
            </div>

            <!-- Status Badge -->
            <div class="mb-6">
                <span v-if="!log.inspection" class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-300">
                    Pending Inspection
                </span>
                <span v-else-if="log.inspection.is_match" class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                    ✓ Verified (Match)
                </span>
                <span v-else class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                    ⚠ Mismatch Detected
                </span>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information Card -->
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-5 space-y-4 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 pb-2">Basic Information</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Serial No</p>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">{{ log.serial_no }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Log No</p>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">{{ log.log_no || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Species</p>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 uppercase">{{ log.species }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Origin</p>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.origin || 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Buyer Name</p>
                        <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.buyer_name || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Measurements Card -->
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-5 space-y-4 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 pb-2">Original Measurements</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Length</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.length }}</p>
                            <p v-if="log.l_ref" class="text-sm font-mono text-red-500 mt-0.5">L.REF: -{{ log.l_ref }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Diameter</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.diameter }}</p>
                            <p v-if="log.d_ref" class="text-sm font-mono text-red-500 mt-0.5">D.REF: -{{ log.d_ref }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Volume (CBM)</p>
                        <p class="text-lg font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.vol_cbm }}</p>
                    </div>

                    <div v-if="log.length_after_ref" class="bg-amber-50 dark:bg-amber-900/20 rounded p-3 border border-amber-200 dark:border-amber-800">
                        <p class="text-xs text-amber-700 dark:text-amber-400 uppercase tracking-wide font-bold">Calc. Length (after ref)</p>
                        <p class="text-base font-mono font-bold text-amber-800 dark:text-amber-300">{{ log.length_after_ref }}</p>
                    </div>
                </div>

                <!-- Re-measurement Data (Shonatoni) -->
                <div v-if="log.inspection && !log.inspection.is_match" class="lg:col-span-2 bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10 rounded-lg p-5 space-y-4 shadow-sm border border-red-100 dark:border-red-800">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-300 border-b border-red-200 dark:border-red-800 pb-2 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Re-measurement (Shonatoni Formula)
                    </h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide font-bold">Length (FT)</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.inspection.actual_length_ft || '0' }}'</p>
                        </div>
                        <div>
                            <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide font-bold">Length (IN)</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.inspection.actual_length_in || '0' }}"</p>
                        </div>
                        <div>
                            <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide font-bold">Mid-Girth (IN)</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.inspection.actual_mid_girth || '0' }}"</p>
                        </div>
                        <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-red-200 dark:border-red-700">
                            <p class="text-xs text-red-600 dark:text-red-400 uppercase tracking-wide font-bold">Volume</p>
                            <div class="flex gap-4 mt-1">
                                <div>
                                    <p class="text-[10px] text-slate-500 uppercase">CFT</p>
                                    <p class="text-lg font-mono font-extrabold text-red-700 dark:text-red-400">{{ log.inspection.actual_vol_cft || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-500 uppercase">CBM</p>
                                    <p class="text-lg font-mono font-extrabold text-red-700 dark:text-red-400">{{ log.inspection.actual_vol_cbm || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspection Information (if exists) -->
                <div v-if="log.inspection" class="lg:col-span-2 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-slate-700/50 dark:to-slate-800/50 rounded-lg p-5 space-y-4 shadow-sm border border-blue-100 dark:border-slate-600">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-blue-200 dark:border-slate-500 pb-2">Inspection Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 uppercase tracking-wide">Surveyor</p>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.inspection.surveyor?.name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 uppercase tracking-wide">Shift</p>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 uppercase">{{ log.inspection.shift || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 uppercase tracking-wide">Verified At</p>
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.inspection.verified_at ? new Date(log.inspection.verified_at).toLocaleString() : 'N/A' }}</p>
                        </div>
                    </div>

                    <div v-if="log.inspection.surveyor_remarks">
                        <p class="text-xs text-slate-600 dark:text-slate-400 uppercase tracking-wide">Surveyor Remarks</p>
                        <p class="text-base text-slate-900 dark:text-slate-100 mt-1 bg-white/50 dark:bg-slate-900/50 p-3 rounded">{{ log.inspection.surveyor_remarks }}</p>
                    </div>
                </div>

                <!-- Inspection Images with Lightbox -->
                <div v-if="log.inspection && log.inspection.images && Object.keys(log.inspection.images).length > 0" class="lg:col-span-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg p-5 space-y-4 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 pb-2">Inspection Images</h3>
                    
                    <ImageLightbox :images="log.inspection.images" />
                </div>

                <!-- Remarks -->
                <div v-if="log.remarks" class="lg:col-span-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg p-5 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 pb-2 mb-3">Remarks</h3>
                    <p class="text-base text-slate-700 dark:text-slate-300">{{ log.remarks }}</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close">Close</SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
