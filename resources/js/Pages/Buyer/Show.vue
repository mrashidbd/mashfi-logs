<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    vessel: Object,
    logs: Object,
});
</script>

<template>
    <Head :title="`Report: ${vessel.vessel_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                    {{ vessel.vessel_name }}
                </h2>
                <div>
                     <a v-if="vessel.is_complete" :href="route('buyer.pdf', vessel.id)" class="px-4 py-2 bg-secondary text-white rounded shadow hover:bg-orange-600">
                        Download PDF Report
                    </a>
                    <span v-else class="text-sm text-slate-500 italic">
                        Survey in progress... (PDF unavailable)
                    </span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-slate-900 dark:text-slate-100">
                        <h3 class="text-lg font-medium mb-4">Inspected Items</h3>
                        

                        <!-- Desktop View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Tag No</th>
                                        <th class="px-4 py-3 text-left">Species</th>
                                        <th class="px-4 py-3 text-left">Buyer</th>
                                        <th class="px-4 py-3 text-left">Original Specs</th>
                                        <th class="px-4 py-3 text-left">Verification</th>
                                        <th class="px-4 py-3 text-left">Actual Specs</th>
                                        <th class="px-4 py-3 text-left">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                    <tr v-for="log in logs.data" :key="log.id">
                                        <td class="px-4 py-2 font-mono font-bold">{{ log.tag_no }}</td>
                                        <td class="px-4 py-2">{{ log.species }}</td>
                                        <td class="px-4 py-2">
                                            <span v-if="log.inspection.buyer_name === 'NIL-BUYER'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                NIL-BUYER
                                            </span>
                                            <span v-else class="text-slate-700 dark:text-slate-300">{{ log.inspection.buyer_name || log.buyer_name || 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-slate-500 dark:text-slate-400">
                                            {{ log.length }}m / {{ log.diameter }}cm <br>
                                            {{ log.vol_cbm }} CBM
                                            <span v-if="log.l_ref || log.d_ref" class="block text-xs text-amber-600 dark:text-amber-400 mt-1">
                                                <span v-if="log.l_ref">L: {{ log.l_ref }}</span>
                                                <span v-if="log.l_ref && log.d_ref"> | </span>
                                                <span v-if="log.d_ref">D: {{ log.d_ref }}</span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span v-if="log.inspection.is_match" class="text-green-600 font-bold">MATCH</span>
                                            <span v-else class="text-red-600 font-bold">MISMATCH</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div v-if="!log.inspection.is_match" class="font-bold text-slate-700 dark:text-slate-300">
                                                {{ log.inspection.actual_length }}m / {{ log.inspection.actual_diameter }}cm <br>
                                                {{ log.inspection.actual_vol_cbm }} CBM
                                                <span v-if="log.inspection.actual_l_ref || log.inspection.actual_d_ref" class="block text-xs text-amber-600 dark:text-amber-400 mt-1">
                                                    <span v-if="log.inspection.actual_l_ref">L: {{ log.inspection.actual_l_ref }}</span>
                                                    <span v-if="log.inspection.actual_l_ref && log.inspection.actual_d_ref"> | </span>
                                                    <span v-if="log.inspection.actual_d_ref">D: {{ log.inspection.actual_d_ref }}</span>
                                                </span>
                                            </div>
                                            <div v-else class="text-slate-400 italic">Same</div>
                                        </td>
                                        <td class="px-4 py-2 text-xs max-w-xs">{{ log.inspection.surveyor_remarks }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden space-y-4">
                            <div v-for="log in logs.data" :key="log.id" class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex flex-col">
                                        <span class="text-xl font-bold font-mono text-slate-900 dark:text-slate-100">{{ log.tag_no }}</span>
                                        <span class="text-sm text-slate-500">{{ log.species }}</span>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span v-if="log.inspection.is_match" class="px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 text-xs font-bold rounded">MATCH</span>
                                        <span v-else class="px-2 py-1 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 text-xs font-bold rounded">MISMATCH</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 text-sm mt-3">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase">Original</p>
                                        <p class="font-mono text-slate-700 dark:text-slate-300">{{ log.length }}m × {{ log.diameter }}cm</p>
                                        <p class="font-mono font-semibold">{{ log.vol_cbm }} CBM</p>
                                    </div>
                                    <div v-if="!log.inspection.is_match" class="text-right">
                                        <p class="text-xs text-slate-500 uppercase">Actual</p>
                                        <p class="font-mono text-red-600 dark:text-red-400 font-bold">{{ log.inspection.actual_length }}m × {{ log.inspection.actual_diameter }}cm</p>
                                        <p class="font-mono text-red-600 dark:text-red-400 font-bold">{{ log.inspection.actual_vol_cbm }} CBM</p>
                                    </div>
                                    <div v-else class="text-right">
                                        <p class="text-xs text-slate-500 uppercase">Actual</p>
                                        <p class="text-slate-400 italic">Verified Matches</p>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-600" v-if="log.inspection.surveyor_remarks">
                                    <p class="text-xs text-slate-500 uppercase mb-1">Remarks</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 p-2 rounded">{{ log.inspection.surveyor_remarks }}</p>
                                </div>
                            </div>
                        </div>

                         <div class="mt-4" v-if="logs.links.length > 3">
                            <div class="flex gap-1">
                                <template v-for="(link, k) in logs.links" :key="k">
                                    <div v-if="link.url === null" class="mr-1 mb-1 px-3 py-2 text-sm text-gray-400 border rounded" v-html="link.label" />
                                    <Link v-else class="mr-1 mb-1 px-3 py-2 text-sm border rounded hover:bg-white dark:hover:bg-slate-600 dark:border-slate-600" :class="{ 'bg-secondary text-white': link.active, 'bg-white text-slate-700 dark:bg-slate-700 dark:text-slate-200': !link.active }" :href="link.url" v-html="link.label" />
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
