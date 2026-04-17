<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    dailyData: Array,
});
</script>

<template>
    <Head title="Buyer Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-extrabold text-xl text-slate-900 dark:text-white">Daily OFFLOADING Progress</h2>
                    <p class="text-xs text-slate-500 mt-0.5">View offloading progress by date</p>
                </div>
                <Link :href="route('buyer.index')" class="px-5 py-2.5 bg-blue-600 text-white font-bold text-sm rounded-lg hover:bg-blue-700 transition shadow-sm">
                    View Full Inventory
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <div v-if="!dailyData || dailyData.length === 0" class="text-center py-20 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 mx-auto text-slate-300 dark:text-slate-600 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400 font-semibold">No offloading data available yet.</p>
                    <p class="text-xs text-slate-400 mt-1">Survey results will appear here once logs are inspected.</p>
                </div>

                <div class="space-y-6">
                    <div v-for="day in dailyData" :key="day.date" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
                        <!-- Date Header -->
                        <div class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 p-5 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ new Date(day.date).toLocaleDateString('en-GB', { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' }) }}
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-500 uppercase font-bold">Logs</p>
                                    <p class="text-lg font-extrabold text-slate-900 dark:text-white">{{ day.total_logs }}</p>
                                </div>
                                <div class="text-right border-l border-slate-200 dark:border-slate-700 pl-4">
                                    <p class="text-[10px] text-slate-500 uppercase font-bold">Volume</p>
                                    <p class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">{{ parseFloat(day.total_volume).toFixed(3) }} <span class="text-xs font-normal">m³</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Simplified Log List -->
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-for="log in day.logs.slice(0, 6)" :key="log.id" class="flex items-center justify-between bg-slate-50 dark:bg-slate-700/30 rounded-lg p-3 border border-slate-100 dark:border-slate-700">
                                    <div>
                                        <p class="font-mono font-bold text-sm text-slate-900 dark:text-white">{{ log.tag_no }}</p>
                                        <p class="text-xs text-slate-500 uppercase">{{ log.species }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">LOG#: {{ log.log_no || '-' }} · L: {{ log.length }} · D: {{ log.diameter }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-mono font-bold text-sm text-slate-700 dark:text-slate-300">{{ log.vol_cbm }}</p>
                                        <p class="text-[10px] text-slate-400">CBM</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="day.logs.length > 6" class="text-xs text-slate-400 mt-3 text-center">
                                +{{ day.logs.length - 6 }} more items
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
