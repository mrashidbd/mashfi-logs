<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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

// Image error handling
const handleImageError = (event) => {
    event.target.style.display = 'none'; // Hide image if it fails to load
    // Optionally show a placeholder, but hiding is cleaner for now as per user request implicit implication of "check if exist"
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
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Species</p>
                        <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.species }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Origin</p>
                        <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.origin || 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Buyer Name</p>
                        <p class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ log.buyer_name || 'N/A' }}</p>
                    </div>
                </div>

                <!-- Measurements Card -->
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-5 space-y-4 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 pb-2">Measurements</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Length (m)</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.length }}</p>
                            <p v-if="log.inspection && !log.inspection.is_match" class="text-sm font-mono text-red-600 dark:text-red-400 mt-1">
                                Actual: {{ log.inspection.actual_length }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Diameter (cm)</p>
                            <p class="text-base font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.diameter }}</p>
                            <p v-if="log.inspection && !log.inspection.is_match" class="text-sm font-mono text-red-600 dark:text-red-400 mt-1">
                                Actual: {{ log.inspection.actual_diameter }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Girth Butt</p>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">{{ log.girth_butt }}</p>
                            <p v-if="log.inspection && !log.inspection.is_match" class="text-sm font-mono text-red-600 dark:text-red-400 mt-1">
                                Actual: {{ log.inspection.actual_gb }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Girth Top</p>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">{{ log.girth_top }}</p>
                            <p v-if="log.inspection && !log.inspection.is_match" class="text-sm font-mono text-red-600 dark:text-red-400 mt-1">
                                Actual: {{ log.inspection.actual_pb }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Volume (CBM)</p>
                        <p class="text-lg font-mono font-bold text-slate-900 dark:text-slate-100">{{ log.vol_cbm }}</p>
                        <p v-if="log.inspection && !log.inspection.is_match" class="text-sm font-mono text-red-600 dark:text-red-400 mt-1">
                            Actual: {{ log.inspection.actual_vol_cbm }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">L_REF</p>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">{{ log.l_ref || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">D_REF</p>
                            <p class="text-base font-mono font-semibold text-slate-900 dark:text-slate-100">{{ log.d_ref || 'N/A' }}</p>
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

                <!-- Inspection Images -->
                <div v-if="log.inspection && log.inspection.images && Object.keys(log.inspection.images).length > 0" class="lg:col-span-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg p-5 space-y-4 shadow-sm border border-slate-100 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-200 dark:border-slate-600 pb-2">Inspection Images</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <template v-for="(url, type) in log.inspection.images" :key="type">
                             <a 
                                :href="`/storage/${url}`"
                                target="_blank"
                                class="group relative aspect-square rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-600 hover:ring-2 hover:ring-blue-500 transition"
                            >
                                <img 
                                    :src="`/storage/${url}`" 
                                    :alt="`${type} End Image`"
                                    class="w-full h-full object-cover group-hover:scale-105 transition"
                                    @error="handleImageError"
                                >
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center">
                                    <div class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded capitalize">
                                        {{ type }}
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-white opacity-0 group-hover:opacity-100 transition">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                    </svg>
                                </div>
                            </a>
                        </template>
                    </div>
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
