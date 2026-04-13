<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    vessels: Array,
});
</script>

<template>
    <Head title="Buyer Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                My Shipments
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="vessels.length === 0" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-slate-500">
                    No active shipments found.
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="vessel in vessels" :key="vessel.id" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg hover:shadow-lg transition">
                        <div class="p-4 sm:p-6">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">{{ vessel.vessel_name }}</h3>
                            <div class="text-sm text-slate-500 mb-4">Arrival: {{ new Date(vessel.arrival_date).toLocaleDateString() }}</div>
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-1 text-slate-600 dark:text-slate-400">
                                    <span>Survey Progress</span>
                                    <span>{{ vessel.surveyed_logs_count }} / {{ vessel.logs_count }}</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2.5 dark:bg-slate-700">
                                    <div class="bg-secondary h-2.5 rounded-full" :style="{ width: (vessel.logs_count > 0 ? (vessel.surveyed_logs_count / vessel.logs_count * 100) : 0) + '%' }"></div>
                                </div>
                            </div>

                            <Link :href="route('buyer.show', vessel.id)" class="inline-flex items-center px-4 py-2 bg-slate-800 dark:bg-slate-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-slate-800 uppercase tracking-widest hover:bg-slate-700 dark:hover:bg-white focus:bg-slate-700 dark:focus:bg-white active:bg-slate-900 dark:active:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition ease-in-out duration-150">
                                View Report
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
