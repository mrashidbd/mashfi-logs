<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    log: Object,
    shifts: Array,
});

const form = useForm({
    is_match: props.log.inspection ? !!props.log.inspection.is_match : true,
    shift: props.log.inspection?.shift || 'A',
    actual_length: props.log.inspection?.actual_length || props.log.length,
    actual_gb: props.log.inspection?.actual_gb || props.log.girth_butt,
    actual_pb: props.log.inspection?.actual_pb || props.log.girth_top,
    actual_diameter: props.log.inspection?.actual_diameter || props.log.diameter,
    actual_l_ref: props.log.inspection?.actual_l_ref || props.log.l_ref || '',
    actual_d_ref: props.log.inspection?.actual_d_ref || props.log.d_ref || '',
    buyer_name: props.log.inspection?.buyer_name || props.log.buyer_name || '',
    surveyor_remarks: props.log.inspection?.surveyor_remarks || '',
    butt_image: null,
    top_image: null,
});

// Auto-calculate diameter if GB/PB changes
watch(() => [form.actual_gb, form.actual_pb], ([gb, pb]) => {
    if (!form.is_match && gb && pb) {
        // Simple Average? Or specific formula?
        // User said: "(GB+PB) average dia ... will be calculated automatically"
        // Usually Girth = Circumference. Diameter = Circumference / pi.
        // If GB/PB are Girths (Circumference):
        // Dia_butt = GB / pi
        // Dia_top = PB / pi
        // Avg Dia = (Dia_butt + Dia_top) / 2
        
        // OR if they are Diameters (sometimes labeled Girth but meaningful diameter):
        // Context: "Circumference (girth) measured... usually in cm" (User metadata)
        // So they are circumferences?
        // "DIAMETER ... Average diameter ... usually calculated from girth"
        
        // Let's implement standard Girth -> Diameter formula: D = G / pi
        // But user provided example: GB=80, PB=75, Diameter=77?
        // (80+75)/2 = 77.5. Close to 77. 
        // If it was circumference: 77cm diam -> ~240cm girth. 
        // So 80/75 must be DIAMETERS or values that average directly?
        // Wait, metadata said "Circumference (girth)... Example 80.0". 
        // If 80 is circumference, Diameter = 80/3.14 = 25cm. 
        // But Example Diameter is 77.0. 
        // 80 and 77 are close. So GB/PB are likely DIAMETERS at ends, NOT circumferences, despite label "Girth".
        // OR the unit is different (mm vs cm)? No, all cm.
        
        // **CRITICAL OBSERVATION**: In timber, "Girth" sometimes means Diameter in loose terms, OR specific quarters.
        // Given 80, 75 -> 77. It is almost certainly (80+75)/2 = 77.5.
        // Maybe truncated? Or rounding?
        // Let's assume (GB+PB)/2 for now as it matches the numbers best.
        
        form.actual_diameter = ((parseFloat(gb) + parseFloat(pb)) / 2).toFixed(1);
    }
});

const calculatedVolume = computed(() => {
    if (form.is_match) return props.log.vol_cbm;
    
    // Vol = L * pi * (r^2)
    // D in cm -> m: /100
    // r = d/2
    if (form.actual_length && form.actual_diameter) {
        const d_m = form.actual_diameter / 100;
        const vol = form.actual_length * Math.PI * Math.pow(d_m / 2, 2);
        return vol.toFixed(6);
    }
    return 0;
});

const showShiftModal = ref(false);

import { onMounted } from 'vue';

onMounted(() => {
    const savedShift = localStorage.getItem('surveyor_shift');
    if (savedShift) {
        // Only override if not already set by backend (i.e. if creating new inspection)
        if (!props.log.inspection) {
            form.shift = savedShift;
        }
    } else {
        showShiftModal.value = true;
    }
});

const selectShift = (shift) => {
    // Extract just the letter (A, B, or C) from the full shift text
    const shiftLetter = shift.match(/SHIFT ([ABC])/)?.[1] || shift;
    form.shift = shiftLetter;
    localStorage.setItem('surveyor_shift', shiftLetter);
    showShiftModal.value = false;
};

// Computed property to get full shift display text
const shiftDisplayText = computed(() => {
    const shiftMap = {
        'A': 'A (12:00 AM - 08:00 AM)',
        'B': 'B (08:00 AM - 04:00 PM)',
        'C': 'C (04:00 PM - 12:00 AM)'
    };
    return shiftMap[form.shift] || form.shift || 'Not Selected';
});

const submit = () => {
    console.log('Submit called', form.data());
    form.post(route('inspections.store', props.log.id), {
        onSuccess: () => {
            console.log('Success!');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        },
    });
};
</script>

<template>
    <Head title="Inspect Log" />

    <AuthenticatedLayout>

        <div class="pt-4 pb-32">
            <div class="max-w-md mx-auto sm:px-6 lg:px-8">
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
                                <p class="text-sm text-slate-500 mb-6">Please select your working shift for this session. This will be applied to all your inspections until you change it.</p>
                                
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
                        <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 rounded-xl p-6 mb-6 shadow-md border border-slate-200 dark:border-slate-700">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-300 dark:border-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 uppercase text-base tracking-wide">Original Specs</h3>
                            </div>
                            
                            <div class="space-y-3">
                                <!-- Species & Origin Group -->
                                <div class="bg-white dark:bg-slate-950/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500 mb-1">Species</div>
                                            <div class="font-bold text-slate-900 dark:text-gray-100 text-lg">{{ log.species }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500 mb-1">Origin</div>
                                            <div class="font-semibold text-slate-700 dark:text-gray-300 text-sm">{{ log.origin || 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Measurements Group -->
                                <div class="bg-white dark:bg-slate-950/50 rounded-lg p-3 border border-slate-200 dark:border-slate-700">
                                    <div class="grid grid-cols-2 gap-y-3 gap-x-4">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500 mb-1">Length</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200">{{ log.length }} <span class="text-xs text-slate-500">m</span></div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500 mb-1">Diameter</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200">{{ log.diameter }} <span class="text-xs text-slate-500">cm</span></div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500 mb-1">Girth (Butt)</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200">{{ log.girth_butt }} <span class="text-xs text-slate-500">cm</span></div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-500 mb-1">Girth (Top)</div>
                                            <div class="font-semibold text-slate-800 dark:text-gray-200">{{ log.girth_top }} <span class="text-xs text-slate-500">cm</span></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Volume (Highlighted) -->
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800">
                                    <div class="flex justify-between items-center">
                                        <div class="text-xs uppercase tracking-wide text-green-700 dark:text-green-400 font-bold">Volume</div>
                                        <div class="text-xl font-bold text-green-800 dark:text-green-300">{{ log.vol_cbm }} <span class="text-sm font-normal">m³</span></div>
                                    </div>
                                </div>

                                <!-- Loss & Decomposition (If Present) -->
                                <div v-if="log.l_ref || log.d_ref" class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 border border-amber-200 dark:border-amber-800">
                                    <div class="grid grid-cols-2 gap-3 text-xs">
                                        <div v-if="log.l_ref">
                                            <div class="uppercase tracking-wide text-amber-700 dark:text-amber-400 mb-1">L_REF (Loss)</div>
                                            <div class="font-semibold text-amber-900 dark:text-amber-300">{{ log.l_ref }} <span class="text-xs">m³</span></div>
                                        </div>
                                        <div v-if="log.d_ref">
                                            <div class="uppercase tracking-wide text-amber-700 dark:text-amber-400 mb-1">D_REF (Decomp.)</div>
                                            <div class="font-semibold text-amber-900 dark:text-amber-300">{{ log.d_ref }} <span class="text-xs">m³</span></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buyer Name (If Present) -->
                                <div v-if="log.buyer_name" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs uppercase tracking-wide text-blue-700 dark:text-blue-400 font-bold">Buyer</div>
                                        <div class="font-semibold text-blue-900 dark:text-blue-300">{{ log.buyer_name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    

                        <!-- Match/Mismatch Selection -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Match Button -->
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

                            <!-- Mismatch Button -->
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

                        <!-- Actual Measurements (Only if mismatch) -->
                        <div v-if="!form.is_match" class="space-y-4 border-l-2 border-red-300 pl-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Actual Length (m)</label>
                                <input v-model="form.actual_length" type="number" step="0.001" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary text-lg dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300 dark:focus:ring-offset-slate-900">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Act. Butt (cm)</label>
                                    <input v-model="form.actual_gb" type="number" step="0.1" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Act. Top (cm)</label>
                                    <input v-model="form.actual_pb" type="number" step="0.1" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Actual Avg Diameter (cm)</label>
                                <input v-model="form.actual_diameter" type="number" step="0.1" readonly class="mt-1 block w-full bg-slate-100 dark:bg-slate-700 rounded-md border-slate-300 shadow-sm text-lg font-bold dark:text-gray-300">
                            </div>

                            <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded">
                                <label class="block text-xs uppercase text-slate-500">New Volume</label>
                                <div class="text-xl font-mono font-bold">{{ calculatedVolume }} CBM</div>
                            </div>

                            <!-- L_REF and D_REF Fields -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">L_REF (Loss) m³</label>
                                    <input v-model="form.actual_l_ref" type="number" step="0.000001" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">D_REF (Decomp.) m³</label>
                                    <input v-model="form.actual_d_ref" type="number" step="0.000001" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                </div>
                            </div>

                            <!-- Buyer Selection -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Buyer</label>
                                <select v-model="form.buyer_name" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300">
                                    <option :value="log.buyer_name || ''">{{ log.buyer_name || 'Keep Original' }}</option>
                                    <option value="NIL-BUYER">NIL-BUYER</option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select NIL-BUYER if this log has no buyer</p>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Surveyor Remarks</label>
                            <textarea v-model="form.surveyor_remarks" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-secondary focus:ring-secondary dark:bg-slate-900 dark:border-slate-600 dark:text-gray-300"></textarea>
                        </div>
                        
                        <!-- Image Upload (Camera) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Butt End Image</label>
                                <input 
                                    type="file" 
                                    accept="image/*" 
                                    capture="environment"
                                    @change="e => form.butt_image = e.target.files[0]"
                                    class="mt-1 block w-full text-sm text-slate-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-primary file:text-white
                                        hover:file:bg-slate-700
                                    "
                                />
                                <div v-if="form.butt_image && typeof form.butt_image !== 'string'" class="mt-2 text-xs text-green-600">
                                    New Image Selected
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-gray-400">Top End Image</label>
                                <input 
                                    type="file" 
                                    accept="image/*" 
                                    capture="environment"
                                    @change="e => form.top_image = e.target.files[0]"
                                    class="mt-1 block w-full text-sm text-slate-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-primary file:text-white
                                        hover:file:bg-slate-700
                                    "
                                />
                                <div v-if="form.top_image && typeof form.top_image !== 'string'" class="mt-2 text-xs text-green-600">
                                    New Image Selected
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Fixed Bottom Footer (Actions) -->
        <div class="fixed bottom-0 left-0 w-full bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 p-4 z-50">
            <div class="max-w-md mx-auto grid grid-cols-3 gap-4">
                <!-- Back Button -->
                <Link :href="route('surveyor.dashboard')" class="col-span-1 flex items-center justify-center py-3 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>

                <!-- Save Button -->
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
