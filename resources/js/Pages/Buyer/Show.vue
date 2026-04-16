<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ImageLightbox from '@/Components/ImageLightbox.vue';

const props = defineProps({
    vessel: Object,
    logs: Object,
});
</script>

<template>
    <Head :title="'Shipment ' + vessel.vessel_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-extrabold text-xl text-slate-900 dark:text-white">{{ vessel.vessel_name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Arrival: {{ new Date(vessel.arrival_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('buyer.index')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg hover:bg-slate-50 transition">
                        Full Inventory
                    </Link>
                    <a :href="route('buyer.pdf', vessel.id)" class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        PDF
                    </a>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
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
                                    <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-right">CBM</th>
                                    <th class="px-4 py-3 text-xs font-mono font-bold text-slate-500 uppercase text-center">Photos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ log.serial_no }}</td>
                                    <td class="px-4 py-3 text-sm font-mono font-bold text-slate-900 dark:text-white">{{ log.tag_no }}</td>
                                    <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ log.log_no || '-' }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold uppercase text-slate-700 dark:text-slate-300">{{ log.species }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-500 uppercase">{{ log.origin || '-' }}</td>
                                    <td class="px-4 py-3 text-right text-xs font-mono font-semibold">{{ log.length }}</td>
                                    <td class="px-4 py-3 text-right text-xs font-mono font-semibold">{{ log.diameter }}</td>
                                    <td class="px-4 py-3 text-right text-xs font-mono font-bold">{{ log.vol_cbm }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span v-if="log.inspection?.images && Object.keys(log.inspection.images).length > 0" class="text-xs text-blue-600 dark:text-blue-400">
                                            {{ Object.keys(log.inspection.images).length }} photo(s)
                                        </span>
                                        <span v-else class="text-xs text-slate-400">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden space-y-3">
                    <div v-for="log in logs.data" :key="log.id" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-lg font-mono font-extrabold text-slate-900 dark:text-white">{{ log.tag_no }}</p>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full">UNLOADED</span>
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
                                <span class="font-mono font-extrabold text-slate-900 dark:text-white">{{ log.vol_cbm }}</span>
                            </div>
                        </div>
                        <div v-if="log.inspection?.images && Object.keys(log.inspection.images).length > 0" class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                            <ImageLightbox :images="log.inspection.images" />
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="flex flex-wrap gap-1 justify-center" v-if="logs.links.length > 3">
                    <template v-for="(link, k) in logs.links" :key="k">
                        <div v-if="link.url === null" class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold text-slate-300 dark:text-slate-600 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                        <Link v-else class="w-10 h-10 flex items-center justify-center text-xs font-mono font-bold border rounded transition-all" :class="{ 'bg-blue-500 text-white border-blue-500': link.active, 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100': !link.active }" :href="link.url" v-html="link.label.replace('Previous', '«').replace('Next', '»')" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
