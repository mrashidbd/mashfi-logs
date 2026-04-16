<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    vessel: Object,
});
</script>

<template>
    <Head title="Welcome" />

    <AuthenticatedLayout>
        <div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
            <div class="max-w-lg w-full text-center space-y-8">
                <!-- Icon -->
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center shadow-lg border border-amber-200 dark:border-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-amber-600 dark:text-amber-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>

                <!-- Welcome Text -->
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Welcome, Surveyor
                    </h1>
                    <p class="mt-3 text-slate-500 dark:text-slate-400 text-base leading-relaxed">
                        Ready to inspect wood logs? Use the search tool to find and verify logs as they are unloaded from the vessel.
                    </p>
                </div>

                <!-- Active Vessel Info -->
                <div v-if="vessel" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center gap-3 justify-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)] animate-pulse"></span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Active Vessel</span>
                    </div>
                    <p class="mt-2 text-xl font-extrabold text-slate-900 dark:text-white">
                        {{ vessel.vessel_name || vessel.vessel_code }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1 font-mono">
                        Arrival: {{ new Date(vessel.arrival_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                    </p>
                </div>

                <div v-else class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-5">
                    <p class="text-amber-700 dark:text-amber-400 font-semibold text-sm">
                        🚢 No vessel is currently open for survey. Please contact the administrator.
                    </p>
                </div>

                <!-- Action Buttons -->
                <div v-if="vessel" class="flex flex-col gap-3">
                    <Link
                        :href="route('surveyor.search')"
                        class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Start Inspection
                    </Link>

                    <Link
                        :href="route('surveyor.dashboard')"
                        class="w-full py-3 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-semibold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        View Inspection List
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
