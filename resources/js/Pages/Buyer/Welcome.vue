<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeVessel: Object,
    stats: Object,
});

const user = usePage().props.auth.user;

const progressPercent = computed(() => {
    if (!props.stats || !props.stats.total_logs) return 0;
    return Math.round((props.stats.offloaded / props.stats.total_logs) * 100);
});
</script>

<template>
    <Head title="Buyer Dashboard" />

    <AuthenticatedLayout>
        <div class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Welcome Banner -->
                <div class="relative overflow-hidden bg-gradient-to-br from-blue-900 via-slate-800 to-blue-950 dark:from-blue-950 dark:via-slate-900 dark:to-slate-950 rounded-2xl p-8 sm:p-10 shadow-xl border border-blue-700/30">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-blue-500/10 to-transparent rounded-full -translate-y-20 translate-x-20"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-emerald-500/10 to-transparent rounded-full translate-y-16 -translate-x-16"></div>
                    <div class="relative">
                        <p class="text-xs font-mono text-blue-300/60 uppercase tracking-[0.3em] mb-2">Buyer Portal</p>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                            Welcome, <span class="text-blue-400">{{ user.name }}</span>
                        </h1>
                        <p class="mt-3 text-blue-200/60 text-sm max-w-xl leading-relaxed">
                            Track your shipment inventory, monitor offloading progress, and download reports from your buyer dashboard.
                        </p>
                    </div>

                    <!-- Active Vessel Badge -->
                    <div v-if="activeVessel" class="mt-6 inline-flex items-center gap-3 bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl px-5 py-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.7)] animate-pulse"></span>
                        <div>
                            <p class="text-[10px] text-blue-300/60 uppercase tracking-widest font-bold">Active Shipment</p>
                            <p class="text-white font-bold text-lg leading-tight">{{ activeVessel.vessel_name || activeVessel.vessel_code }}</p>
                        </div>
                    </div>
                    <div v-else class="mt-6 inline-flex items-center gap-2 bg-amber-500/10 border border-amber-500/20 rounded-xl px-5 py-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <p class="text-amber-400 text-sm font-semibold">No shipment currently active.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-blue-600 dark:text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Logs</p>
                        </div>
                        <p class="text-3xl font-extrabold text-slate-900 dark:text-white font-mono">{{ stats.total_logs }}</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-emerald-600 dark:text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Offloaded</p>
                        </div>
                        <p class="text-3xl font-extrabold text-emerald-700 dark:text-emerald-400 font-mono">{{ stats.offloaded }}</p>
                        <div class="mt-2 w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all" :style="{ width: progressPercent + '%' }"></div>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">{{ progressPercent }}% complete</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-purple-600 dark:text-purple-400"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                            </div>
                            <p class="text-[10px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-widest">Total Volume</p>
                        </div>
                        <p class="text-3xl font-extrabold text-purple-700 dark:text-purple-400 font-mono">{{ stats.total_volume }}</p>
                        <p class="text-[10px] text-slate-500 mt-1">CBM (m³)</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <Link :href="route('buyer.index')" class="group bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 shadow-sm hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700 transition-all hover:-translate-y-0.5">
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/40 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-blue-600 dark:text-blue-400"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm uppercase tracking-wide">Full Inventory</h3>
                        <p class="text-xs text-slate-500 mt-1">View all your logs with filtering & status</p>
                    </Link>

                    <Link :href="route('buyer.dashboard')" class="group bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 shadow-sm hover:shadow-lg hover:border-emerald-300 dark:hover:border-emerald-700 transition-all hover:-translate-y-0.5">
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-emerald-600 dark:text-emerald-400"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm uppercase tracking-wide">Daily OFFLOADING View</h3>
                        <p class="text-xs text-slate-500 mt-1">View offloading progress by date</p>
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
