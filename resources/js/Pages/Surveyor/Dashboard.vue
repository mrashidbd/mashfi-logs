<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    vessel: Object,
    logs: Object,
});
</script>

<template>
    <Head title="Surveyor Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                Surveyor Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="!vessel" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-10 text-center">
                    <div class="text-6xl mb-4">🚢🚫</div>
                    <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300">No Active Vessel</h3>
                    <p class="text-slate-500 mt-2">There are no vessels currently open for survey. Please contact the administrator.</p>
                </div>

                <div v-else class="space-y-6">
                    <!-- Vessel Info Card -->
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-secondary">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                            Current Assignment: {{ vessel.vessel_name || vessel.vessel_code }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            Arrival: {{ new Date(vessel.arrival_date).toLocaleDateString() }}
                        </p>
                    </div>

                    <!-- Log List (Mobile Optimized) -->
                    <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="font-semibold text-slate-700 dark:text-slate-300">Inspection List</h4>
                        </div>
                        
                        <div class="divide-y divide-slate-200 dark:divide-slate-700">
                            <div v-for="log in logs.data" :key="log.id" class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                <Link :href="route('surveyor.show', log.id)" class="block">

                                    <!-- TAG/LOG Section -->
                                    <div class="flex flex-col">
                                        <!-- Serial Number Column -->
                                        <div class="text-xs text-slate-400 flex justify-center items-center">
                                            <span class="flex justify-center items-center px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded-full">#{{ log.serial_no }}</span>
                                        </div>

                                        <div class="flex flex-col">
                                            <div>
                                                <!-- TAG & LOG Numbers Column -->
                                                <div class="relative flex items-center justify-between">
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-medium text-slate-500 dark:text-slate-500 uppercase tracking-wide">Tag No</span>
                                                        <span class="font-mono font-bold text-xl text-primary dark:text-secondary">
                                                            {{ log.tag_no }}
                                                        </span>
                                                    </div>
                                                    <div class="absolute left-1/2 -translate-x-1/2 w-px h-10 bg-slate-300 dark:bg-slate-600"></div>
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-medium text-slate-500 dark:text-slate-500 uppercase tracking-wide">Log No</span>
                                                        <span class="font-mono font-bold text-xl text-slate-700 dark:text-slate-300">
                                                            {{ log.log_no }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex justify-center items-center">
                                                <!-- Status Badge - Independent & Centered -->
                                                <div class="flex items-center justify-center">
                                                    <!-- MATCHED Status -->
                                                    <div v-if="log.inspection && log.inspection.is_match" class="flex items-center gap-2 px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-full">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <span class="text-xs font-bold">MATCHED</span>
                                                    </div>
                                                    <!-- MISMATCH Status -->
                                                    <div v-else-if="log.inspection && !log.inspection.is_match" class="flex items-center gap-2 px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-full">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <span class="text-xs font-bold">MISMATCH</span>
                                                    </div>
                                                    <!-- PENDING Status -->
                                                    <div v-else class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span class="text-xs font-bold">PENDING</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                    
                                </Link>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="p-4" v-if="logs.links.length > 3">
                            <!-- Simplified pagination for mobile -->
                            <div class="flex justify-between">
                                <Link 
                                    v-if="logs.prev_page_url" 
                                    :href="logs.prev_page_url"
                                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 dark:text-slate-200 rounded hover:bg-slate-200 dark:hover:bg-slate-600"
                                >Previous</Link>
                                <div v-else></div>
                                
                                <Link 
                                    v-if="logs.next_page_url" 
                                    :href="logs.next_page_url"
                                    class="px-4 py-2 bg-primary text-white rounded hover:bg-slate-700"
                                >Next</Link>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
