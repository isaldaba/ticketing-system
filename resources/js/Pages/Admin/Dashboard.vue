<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const { auth } = usePage().props;

const stats = [
    { label: 'Total Tickets', value: 0, color: 'bg-blue-500' },
    { label: 'Open Tickets', value: 0, color: 'bg-yellow-500' },
    { label: 'Resolved Tickets', value: 0, color: 'bg-green-500' },
    { label: 'Staff Members', value: 0, color: 'bg-purple-500' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Admin Dashboard
                </h2>
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                    Admin
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Welcome Banner -->
                <div class="mb-8 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
                    <div class="px-8 py-6 text-white">
                        <h3 class="text-2xl font-bold">Welcome back, {{ auth.user.name }}! 👋</h3>
                        <p class="mt-1 text-indigo-100">Here's an overview of the ticketing system.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="overflow-hidden rounded-xl bg-white shadow-sm"
                    >
                        <div :class="[stat.color, 'h-1.5']"></div>
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h4 class="font-semibold text-gray-800">Quick Actions</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-6">
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-indigo-50 hover:border-indigo-300">
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">New Ticket</span>
                            </button>
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-indigo-50 hover:border-indigo-300">
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Manage Staff</span>
                            </button>
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-indigo-50 hover:border-indigo-300">
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Reports</span>
                            </button>
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-indigo-50 hover:border-indigo-300">
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Settings</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity placeholder -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h4 class="font-semibold text-gray-800">Recent Tickets</h4>
                        </div>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm">No tickets yet</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
