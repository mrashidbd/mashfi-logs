<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    reports: Array,
});
</script>

<template>
    <Head title="Admin Daily Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
                    Daily Survey Reports
                </h2>
                <!-- Link to return to shipments -->
                <Link :href="route('vessels.index')" class="text-sm text-secondary hover:underline">
                    Back to Shipments
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="!reports || reports.length === 0" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-slate-500">
                    No survey data available yet.
                </div>

                <div class="space-y-8">
                    <div v-for="report in reports" :key="report.date" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 dark:border-slate-700">
                        <div class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 p-6 flex justify-between items-center">
                            <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                {{ new Date(report.date).toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                            </h3>
                            <div class="flex gap-4">
                                <div class="text-right">
                                    <div class="text-sm text-slate-500 uppercase tracking-widest font-bold">Total Surveyed</div>
                                    <div class="text-xl font-bold dark:text-slate-200">{{ report.total_logs }} <span class="text-sm font-normal text-slate-500">logs</span></div>
                                </div>
                                <div class="text-right border-l pl-4 border-slate-300 dark:border-slate-600">
                                    <div class="text-sm text-slate-500 uppercase tracking-widest font-bold">Vol Evaluated</div>
                                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ parseFloat(report.total_volume).toFixed(3) }} <span class="text-sm font-normal">m³</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 uppercase tracking-wider">Shift Breakdown</h4>
                            
                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div v-for="shift in report.shifts" :key="shift.shift" class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-5 border border-slate-200 dark:border-slate-600 hover:shadow-md transition">
                                    
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-500 dark:text-slate-400 font-bold text-xs uppercase tracking-widest">Shift</span>
                                            <span class="text-3xl font-black text-secondary">{{ shift.shift }}</span>
                                        </div>
                                        <div class="bg-white dark:bg-slate-800 rounded p-2 text-center shadow-sm border border-slate-100 dark:border-slate-700">
                                            <div class="text-xs text-slate-500 font-bold uppercase">Volume</div>
                                            <div class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ parseFloat(shift.total_volume).toFixed(3) }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                        <div class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded p-2 text-center">
                                            <div class="text-xs uppercase font-bold text-green-600/70 dark:text-green-400/70">Matched</div>
                                            <span class="font-bold text-lg">{{ shift.match_count }}</span>
                                        </div>
                                        <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded p-2 text-center">
                                            <div class="text-xs uppercase font-bold text-amber-600/70 dark:text-amber-400/70">Mismatched</div>
                                            <span class="font-bold text-lg">{{ shift.mismatch_count }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 text-xs">
                                        <span class="text-slate-500 font-bold uppercase tracking-wider">Surveyor(s): </span>
                                        <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ shift.surveyors.join(', ') }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
