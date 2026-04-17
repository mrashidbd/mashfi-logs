<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    log: Object,
    shifts: Array,
});

const form = useForm({
    is_match: props.log.inspection ? !!props.log.inspection.is_match : true,
    shift: props.log.inspection?.shift || 'A',
    actual_length_ft: props.log.inspection?.actual_length_ft || '',
    actual_length_in: props.log.inspection?.actual_length_in || '',
    actual_mid_girth: props.log.inspection?.actual_mid_girth || '',
    surveyor_remarks: props.log.inspection?.surveyor_remarks || '',
    buyer_name: props.log.inspection?.buyer_name || null,
    butt_image: null,
    top_image: null,
});

// Shonatoni formula: (mid_girth² × length_ft) / 2304 = CFT
const calculatedVolCft = computed(() => {
    if (form.is_match) return null;
    const ft = parseFloat(form.actual_length_ft) || 0;
    const inch = parseFloat(form.actual_length_in) || 0;
    const totalFt = ft + (inch / 12);
    const midGirth = parseFloat(form.actual_mid_girth) || 0;

    if (totalFt > 0 && midGirth > 0) {
        return ((midGirth * midGirth * totalFt) / 2304).toFixed(3);
    }
    return '0.000';
});

const calculatedVolCbm = computed(() => {
    if (form.is_match) return parseFloat(props.log.vol_cbm).toFixed(3);
    const cft = parseFloat(calculatedVolCft.value);
    if (cft > 0) {
        return (cft / 27.74).toFixed(3);
    }
    return '0.000';
});

const showShiftModal = ref(false);
const showSuccess = ref(false);

onMounted(() => {
    const savedShift = localStorage.getItem('surveyor_shift');
    if (savedShift) {
        if (!props.log.inspection) {
            form.shift = savedShift;
        }
    }
    // Show shift modal once per login session (sessionStorage clears on browser close / logout)
    if (!sessionStorage.getItem('shift_prompted')) {
        showShiftModal.value = true;
    }
});

const selectShift = (shift) => {
    const shiftLetter = shift.match(/SHIFT ([ABC])/)?.[1] || shift;
    form.shift = shiftLetter;
    localStorage.setItem('surveyor_shift', shiftLetter);
    sessionStorage.setItem('shift_prompted', '1');
    showShiftModal.value = false;
};

const setNilBuyer = () => {
    form.buyer_name = 'NIL-BUYER';
};

const submit = () => {
    form.post(route('inspections.store', props.log.id), {
        onSuccess: () => {
            showSuccess.value = true;
            setTimeout(() => {
                showSuccess.value = false;
                // Navigate back to previous screen (index or search)
                window.history.back();
            }, 2000);
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
};

// Existing images from previous inspection
const existingImages = computed(() => props.log.inspection?.images || {});

const fmt = (val, digits = 2) => {
    if (val === null || val === undefined || val === '') return '-';
    return parseFloat(val).toFixed(digits);
};
</script>

<template>
    <Head title="Inspect Log" />

    <AuthenticatedLayout>

        <!-- Success Overlay -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showSuccess" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-10 max-w-sm w-full mx-4 text-center animate-bounce-once">
                        <div class="w-20 h-20 mx-auto bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-10 text-emerald-600 dark:text-emerald-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-2">Inspection Saved!</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Log <span class="font-mono font-bold text-amber-600 dark:text-amber-400">{{ log.tag_no }}</span> has been recorded successfully.</p>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <div class="pt-4 pb-32">
            <div class="max-w-md mx-auto px-4 sm:px-6">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Error Display -->
                        <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <h4 class="font-bold text-red-800 dark:text-red-400 mb-2">Validation Errors:</h4>
                            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">
                                <li v-for="(error, key) in form.errors" :key="key">{{ key }}: {{ error }}</li>
                            </ul>
                        </div>

                        <!-- Shift Display / Change -->
                        <div class="bg-green-50 dark:bg-slate-700 rounded-lg flex justify-between items-center border border-green-100 dark:border-slate-900">
                            <div class="ml-4 py-2">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Current Shift</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="font-bold text-2xl text-slate-800 dark:text-gray-200">{{ form.shift || '?' }}</div>
                                    <div v-if="form.shift" class="px-2 py-1 bg-green-100 dark:bg-green-900/30 rounded text-xs text-green-700 dark:text-green-400">
                                        {{ form.shift === 'A' ? '12:00 AM - 08:00 AM' : form.shift === 'B' ? '08:00 AM - 04:00 PM' : '04:00 PM - 12:00 AM' }}
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="showShiftModal = true" class="text-sm px-4 py-6 rounded-lg bg-green-100 dark:bg-green-400/10 text-green-600 hover:underline dark:text-green-400 mr-1">Change</button>
                        </div>

                        <!-- Shift Selection Modal -->
                        <div v-if="showShiftModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm">
                            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl max-w-sm w-full p-6">
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Select Session Shift</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Please select your working shift for this session.</p>

                                <div class="space-y-3">
                                    <button
                                        v-for="shift in shifts"
                                        :key="shift"
                                        type="button"
                                        @click="selectShift(shift)"
                                        class="w-full text-left px-4 py-3 rounded border transition"
                                        :class="'SHIFT ' + form.shift === shift.substring(0, 7)
                                            ? 'border-secondary bg-orange-50 text-secondary font-bold dark:bg-orange-900/40 dark:text-orange-400 dark:border-orange-500'
                                            : 'border-slate-200 hover:border-slate-300 dark:border-slate-600 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:bg-slate-700'"
                                    >
                                        {{ shift }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Original Data Card -->
                        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 rounded-xl p-5 shadow-md border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-300 dark:border-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 uppercase text-base tracking-wide">Original Specs</h3>
                            </div>

                            <div class="space-y-3">
                                <!-- Tag & Log Number -->
                                <div class="bg-white dark:bg-slate-950/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">DF10-TAG</div>
                                            <div class="font-mono font-extrabold text-lg text-amber-600 dark:text-amber-400">{{ log.tag_no }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">LOG#</div>
                                            <div class="font-mono font-bold text-lg text-slate-700 dark:text-slate-300">{{ log.log_no || '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Species & Origin -->
                                <div class="bg-white dark:bg-slate-950/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Species</div>
                                            <div class="font-bold text-slate-900 dark:text-gray-100">{{ log.species }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Origin</div>
                                            <div class="font-semibold text-slate-700 dark:text-gray-300 text-sm">{{ log.origin || 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Measurements -->
                                <div class="bg-white dark:bg-slate-950/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <div class="grid grid-cols-3 gap-3">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Length</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200">
                                                {{ fmt(log.length) }}
                                                <span v-if="log.l_ref" class="text-red-500 text-xs font-mono ml-1">-{{ fmt(log.l_ref) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">DIA</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200">
                                                {{ fmt(log.diameter) }}
                                                <span v-if="log.d_ref" class="text-red-500 text-xs font-mono ml-1">-{{ fmt(log.d_ref) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Calc.Len</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200 font-mono">{{ fmt(log.calc_length) }}</div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 mt-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">GB</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200 font-mono">{{ fmt(log.girth_butt) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">PB</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200 font-mono">{{ fmt(log.girth_top) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Volume -->
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800">
                                    <div class="flex justify-between items-center">
                                        <div class="text-xs uppercase tracking-wide text-green-700 dark:text-green-400 font-bold">Volume (CBM)</div>
                                        <div class="text-xl font-bold text-green-800 dark:text-green-300">{{ fmt(log.vol_cbm, 3) }} <span class="text-sm font-normal">m³</span></div>
                                    </div>
                                </div>

                                <!-- Buyer -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-400 font-bold">Buyer</div>
                                            <div class="font-semibold text-blue-900 dark:text-blue-300 mt-0.5">
                                                <span v-if="form.buyer_name === 'NIL-BUYER'" class="text-red-600 dark:text-red-400 font-bold">NIL-BUYER</span>
                                                <span v-else>{{ log.buyer_name || 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            @click="setNilBuyer"
                                            class="px-3 py-1.5 text-xs font-bold uppercase tracking-wide rounded-lg transition"
                                            :class="form.buyer_name === 'NIL-BUYER'
                                                ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700'
                                                : 'bg-blue-100 dark:bg-blue-800/40 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-700 hover:bg-blue-200 dark:hover:bg-blue-800/60'"
                                        >
                                            {{ form.buyer_name === 'NIL-BUYER' ? '✓ NIL-BUYER' : 'Set NIL-BUYER' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Match/Mismatch Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <button
                                type="button"
                                @click="form.is_match = true"
                                class="flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all duration-200"
                                :class="form.is_match
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-gray-400 hover:border-green-300 dark:hover:border-green-700'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-bold">Specs Matched</span>
                            </button>

                            <button
                                type="button"
                                @click="form.is_match = false"
                                class="flex flex-col items-center justify-center p-4 rounded-lg border-2 transition-all duration-200"
                                :class="!form.is_match
                                    ? 'border-red-500 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400'
                                    : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-gray-400 hover:border-red-300 dark:hover:border-red-700'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="font-bold">Mismatch Found</span>
                            </button>
                        </div>

                        <!-- Re-measurement Fields (Only if mismatch) -->
                        <div v-if="!form.is_match" class="space-y-4 border-l-4 border-red-400 dark:border-red-600 pl-4 bg-red-50/50 dark:bg-red-900/10 rounded-r-lg py-4 pr-4">
                            <h4 class="text-sm font-bold text-red-700 dark:text-red-400 uppercase tracking-wider flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Re-measurement (Shonatoni)
                            </h4>

                            <!-- Existing values reference -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg p-3 border border-slate-200 dark:border-slate-700 text-xs">
                                <p class="font-bold text-slate-500 uppercase tracking-wider mb-2">Original Reference</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <span class="text-slate-400">Length</span>
                                        <div class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ fmt(log.length) }}
                                            <span v-if="log.l_ref" class="text-red-500 text-[10px] block">-{{ fmt(log.l_ref) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-slate-400">DIA</span>
                                        <div class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ fmt(log.diameter) }}
                                            <span v-if="log.d_ref" class="text-red-500 text-[10px] block">-{{ fmt(log.d_ref) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-slate-400">Vol CBM</span>
                                        <div class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ fmt(log.vol_cbm, 3) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Length: Foot + Inch -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400 mb-1">Length</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative">
                                        <input v-model="form.actual_length_ft" type="number" min="0" step="1" placeholder="0" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-lg font-mono text-right pr-10 dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">FT</span>
                                    </div>
                                    <div class="relative">
                                        <input v-model="form.actual_length_in" type="number" min="0" max="11.9" step="0.5" placeholder="0" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-lg font-mono text-right pr-10 dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">IN</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mid-Girth -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400 mb-1">Mid-Girth</label>
                                <div class="relative">
                                    <input v-model="form.actual_mid_girth" type="number" min="0" step="0.5" placeholder="0" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-lg font-mono text-right pr-14 dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">INCH</span>
                                </div>
                            </div>

                            <!-- Calculated Volume -->
                            <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-4 rounded-lg border border-amber-200 dark:border-amber-800">
                                <label class="block text-xs uppercase text-amber-700 dark:text-amber-400 font-bold mb-2">Calculated Volume (Shonatoni)</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-[10px] text-amber-600 dark:text-amber-500 uppercase font-bold">CFT</div>
                                        <div class="text-2xl font-mono font-extrabold text-amber-800 dark:text-amber-300">{{ calculatedVolCft }}</div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-amber-600 dark:text-amber-500 uppercase font-bold">CBM</div>
                                        <div class="text-2xl font-mono font-extrabold text-amber-800 dark:text-amber-300">{{ calculatedVolCbm }}</div>
                                    </div>
                                </div>
                                <p class="text-[10px] text-amber-600/70 mt-2 font-mono">(girth² × length_ft) / 2304 = CFT | CFT / 27.74 = CBM</p>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Surveyor Remarks</label>
                            <textarea v-model="form.surveyor_remarks" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300"></textarea>
                        </div>

                        <!-- Image Upload -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400 mb-1">Butt End Image</label>
                                <!-- Existing preview -->
                                <div v-if="existingImages.butt && !form.butt_image" class="mb-2 rounded-lg overflow-hidden aspect-square bg-slate-200 dark:bg-slate-700">
                                    <img :src="`/storage/${existingImages.butt}`" alt="Butt end" class="w-full h-full object-cover">
                                </div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    capture="environment"
                                    @change="e => form.butt_image = e.target.files[0]"
                                    class="block w-full text-sm text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 dark:file:bg-amber-900/30 dark:file:text-amber-400"
                                />
                                <div v-if="form.butt_image" class="mt-1 text-xs text-green-600 font-semibold">✓ New image selected</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400 mb-1">Top End Image</label>
                                <!-- Existing preview -->
                                <div v-if="existingImages.top && !form.top_image" class="mb-2 rounded-lg overflow-hidden aspect-square bg-slate-200 dark:bg-slate-700">
                                    <img :src="`/storage/${existingImages.top}`" alt="Top end" class="w-full h-full object-cover">
                                </div>
                                <input
                                    type="file"
                                    accept="image/*"
                                    capture="environment"
                                    @change="e => form.top_image = e.target.files[0]"
                                    class="block w-full text-sm text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 dark:file:bg-amber-900/30 dark:file:text-amber-400"
                                />
                                <div v-if="form.top_image" class="mt-1 text-xs text-green-600 font-semibold">✓ New image selected</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Fixed Bottom Footer (Actions) -->
        <div class="fixed bottom-0 left-0 w-full bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 p-4 z-50">
            <div class="max-w-md mx-auto grid grid-cols-3 gap-4">
                <Link :href="route('surveyor.search')" class="col-span-1 flex items-center justify-center py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="col-span-2 flex items-center justify-center py-3 bg-green-600 hover:bg-green-800 text-white font-bold rounded-lg shadow-md transition disabled:opacity-50 text-lg"
                >
                    <span>Save</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
