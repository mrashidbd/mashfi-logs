<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    vessel: Object,
});

const searchQuery = ref('');
const results = ref([]);
const isSearching = ref(false);
const hasSearched = ref(false);

const performSearch = debounce(async (val) => {
    if (val.length < 2) {
        results.value = [];
        hasSearched.value = false;
        isSearching.value = false;
        return;
    }

    isSearching.value = true;
    hasSearched.value = true;

    try {
        const response = await fetch(route('surveyor.search.query') + '?q=' + encodeURIComponent(val));
        results.value = await response.json();
    } catch (error) {
        console.error('Search failed:', error);
        results.value = [];
    } finally {
        isSearching.value = false;
    }
}, 300);

const handleSearch = () => {
    performSearch(searchQuery.value);
};

const clearSearch = () => {
    searchQuery.value = '';
    results.value = [];
    hasSearched.value = false;
};
</script>

<template>
    <Head title="Search Logs" />

    <AuthenticatedLayout>
        <div class="py-6 pb-24">
            <div class="max-w-lg mx-auto px-4">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">Search Log</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                            {{ vessel?.vessel_name || vessel?.vessel_code }}
                        </p>
                    </div>
                    <Link :href="route('surveyor.dashboard')" class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </Link>
                </div>

                <!-- Search Bar -->
                <div class="relative mb-6">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg v-if="!isSearching" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <svg v-else class="size-6 text-amber-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <input
                        v-model="searchQuery"
                        @input="handleSearch"
                        type="text"
                        inputmode="search"
                        placeholder="Enter LOG# or DF10-TAG..."
                        class="w-full pl-14 pr-12 py-4 text-lg font-mono bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:focus:border-amber-500 transition placeholder:text-slate-400"
                        autofocus
                    >
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Instructions -->
                <div v-if="!hasSearched" class="text-center py-12 space-y-3">
                    <div class="w-16 h-16 mx-auto bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75H6A2.25 2.25 0 0 0 3.75 6v1.5M16.5 3.75H18A2.25 2.25 0 0 1 20.25 6v1.5m0 9V18A2.25 2.25 0 0 1 18 20.25h-1.5m-9 0H6A2.25 2.25 0 0 1 3.75 18v-1.5M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">
                        Enter the <span class="font-bold text-slate-700 dark:text-slate-300">Log Number</span> or <span class="font-bold text-slate-700 dark:text-slate-300">DF10-TAG</span> from the unloaded log to begin inspection.
                    </p>
                </div>

                <!-- Results -->
                <div v-else class="space-y-3">
                    <!-- No results -->
                    <div v-if="results.length === 0 && !isSearching" class="text-center py-8 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mx-auto text-slate-300 dark:text-slate-600 mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm">No logs found matching "{{ searchQuery }}"</p>
                    </div>

                    <!-- Result Items -->
                    <Link
                        v-for="log in results"
                        :key="log.id"
                        :href="route('surveyor.show', log.id)"
                        class="block bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700 transition-all"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-xs font-mono text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">#{{ log.serial_no }}</span>
                                    <!-- Status Badge -->
                                    <span v-if="log.inspection && log.inspection.is_match" class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded">✓ DONE</span>
                                    <span v-else-if="log.inspection && !log.inspection.is_match" class="text-[10px] font-bold text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/30 px-1.5 py-0.5 rounded">⚠ DIFF</span>
                                    <span v-else class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">PENDING</span>
                                </div>
                                <div class="flex items-baseline gap-4">
                                    <div>
                                        <p class="text-[10px] font-mono text-slate-400 uppercase">DF10-TAG</p>
                                        <p class="text-lg font-mono font-extrabold text-amber-600 dark:text-amber-400">{{ log.tag_no }}</p>
                                    </div>
                                    <div class="w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
                                    <div>
                                        <p class="text-[10px] font-mono text-slate-400 uppercase">LOG#</p>
                                        <p class="text-lg font-mono font-bold text-slate-700 dark:text-slate-300">{{ log.log_no || '-' }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-3 text-xs text-slate-500">
                                    <span class="font-semibold uppercase">{{ log.species }}</span>
                                    <span v-if="log.origin">• {{ log.origin }}</span>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-slate-400 mt-4 flex-shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
