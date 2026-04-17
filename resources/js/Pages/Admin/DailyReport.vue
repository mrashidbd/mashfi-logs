<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    reports: Array,
    overview: Object,
    filters: Object,
    surveyors_list: Array,
    buyers_list: Array,
    species_list: Array,
    origins_list: Array,
});

const surveyorFilter = ref(props.filters?.surveyor || '');
const buyerFilter = ref(props.filters?.buyer || '');
const speciesFilter = ref(props.filters?.species || '');
const originFilter = ref(props.filters?.origin || '');

const applyFilters = () => {
    router.get(route('admin.daily-report'), {
        surveyor: surveyorFilter.value,
        buyer: buyerFilter.value,
        species: speciesFilter.value,
        origin: originFilter.value,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    surveyorFilter.value = '';
    buyerFilter.value = '';
    speciesFilter.value = '';
    originFilter.value = '';
    applyFilters();
};

const hasFilters = () => {
    return surveyorFilter.value || buyerFilter.value || speciesFilter.value || originFilter.value;
};
</script>

<template>
    <Head title="Admin Daily Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="border-l-4 border-blue-500 pl-4 py-1">
                    <h2 class="font-extrabold text-2xl uppercase tracking-tight text-slate-900 dark:text-slate-100 leading-tight">
                        Daily Survey Reports
                    </h2>
                    <p class="text-xs font-mono text-slate-500 uppercase tracking-widest mt-1">Shift-wise breakdown</p>
                </div>
                <Link :href="route('admin.welcome')" class="px-4 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    ← Dashboard
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Overview Stats -->
                <div v-if="overview" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Unloaded</p>
                        <p class="text-2xl font-extrabold text-slate-900 dark:text-white font-mono mt-1">{{ overview.total_unloaded }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm">
                        <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Survey Days</p>
                        <p class="text-2xl font-extrabold text-blue-700 dark:text-blue-400 font-mono mt-1">{{ overview.total_days }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm">
                        <p class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-widest">Unique Shifts</p>
                        <p class="text-2xl font-extrabold text-purple-700 dark:text-purple-400 font-mono mt-1">{{ overview.total_shifts }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm">
                        <p class="text-[10px] font-bold text-orange-600 dark:text-orange-400 uppercase tracking-widest">Buyers</p>
                        <p class="text-2xl font-extrabold text-orange-700 dark:text-orange-400 font-mono mt-1">{{ overview.total_buyers }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 shadow-sm col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Total Volume</p>
                        <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400 font-mono mt-1">{{ overview.total_volume?.toFixed(3) }} <span class="text-xs font-normal text-slate-500">m³</span></p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-sm">
                    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-between">
                        <h3 class="text-xs font-bold font-mono tracking-widest uppercase text-slate-800 dark:text-slate-200">Filters</h3>
                        <button v-if="hasFilters()" @click="clearFilters" class="text-xs text-red-500 hover:text-red-700 font-bold uppercase tracking-widest transition">Clear All</button>
                    </div>
                    <div class="p-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <select v-model="surveyorFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                            <option value="">All Surveyors</option>
                            <option v-for="s in surveyors_list" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                        <select v-model="buyerFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                            <option value="">All Buyers</option>
                            <option v-for="b in buyers_list" :key="b" :value="b">{{ b }}</option>
                        </select>
                        <select v-model="speciesFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                            <option value="">All Species</option>
                            <option v-for="s in species_list" :key="s" :value="s">{{ s }}</option>
                        </select>
                        <select v-model="originFilter" @change="applyFilters" class="h-10 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 text-sm font-mono focus:border-blue-500 focus:ring-0">
                            <option value="">All Origins</option>
                            <option v-for="o in origins_list" :key="o" :value="o">{{ o }}</option>
                        </select>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!reports || reports.length === 0" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 mx-auto text-slate-300 dark:text-slate-600 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    <p class="text-slate-500 dark:text-slate-400 font-semibold">No survey data available yet.</p>
                    <p class="text-xs text-slate-400 mt-1">Survey reports will appear here once inspections are completed.</p>
                </div>

                <!-- Daily Report Cards -->
                <div class="space-y-6">
                    <div v-for="report in reports" :key="report.date" class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-lg border border-slate-200 dark:border-slate-700">
                        <!-- Date Header (fixed mobile layout) -->
                        <div class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 p-5">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                <h3 class="text-lg sm:text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ new Date(report.date).toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </h3>
                                <div class="flex gap-3 sm:gap-4">
                                    <div class="text-left sm:text-right">
                                        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Total Surveyed</div>
                                        <div class="text-lg font-bold dark:text-slate-200">{{ report.total_logs }} <span class="text-xs font-normal text-slate-500">logs</span></div>
                                    </div>
                                    <div class="text-left sm:text-right border-l pl-3 sm:pl-4 border-slate-300 dark:border-slate-600">
                                        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Vol Evaluated</div>
                                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ parseFloat(report.total_volume).toFixed(3) }} <span class="text-xs font-normal">m³</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shift Breakdown -->
                        <div class="p-5">
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 mb-4 uppercase tracking-wider">Shift Breakdown</h4>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="shift in report.shifts" :key="shift.shift" class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-5 border border-slate-200 dark:border-slate-600 hover:shadow-md transition">

                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-500 dark:text-slate-400 font-bold text-xs uppercase tracking-widest">Shift</span>
                                            <span class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ shift.shift }}</span>
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
