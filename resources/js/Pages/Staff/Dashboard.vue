<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { auth } = usePage().props;

const stats = [
    { label: 'My Open Tickets', value: 0, color: 'bg-yellow-500' },
    { label: 'In Progress', value: 0, color: 'bg-blue-500' },
    { label: 'Resolved Today', value: 0, color: 'bg-green-500' },
];
</script>

<template>
    <Head title="Staff Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Staff Dashboard
                </h2>
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                    Staff
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Welcome Banner -->
                <div class="mb-8 overflow-hidden rounded-xl bg-gradient-to-r from-blue-500 to-cyan-500 shadow-lg">
                    <div class="px-8 py-6 text-white">
                        <h3 class="text-2xl font-bold">Welcome, {{ auth.user.name }}! 👋</h3>
                        <p class="mt-1 text-blue-100">Here are your assigned tickets and tasks.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-3">
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

                <!-- Actions and Tickets -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Quick Actions -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h4 class="font-semibold text-gray-800">Quick Actions</h4>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-6">
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-blue-50 hover:border-blue-300">
                                <svg class="mb-2 h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">New Ticket</span>
                            </button>
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-blue-50 hover:border-blue-300">
                                <svg class="mb-2 h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">My Tickets</span>
                            </button>
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-blue-50 hover:border-blue-300">
                                <svg class="mb-2 h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Notifications</span>
                            </button>
                            <button class="flex flex-col items-center rounded-lg border border-gray-200 p-4 text-center transition hover:bg-blue-50 hover:border-blue-300">
                                <svg class="mb-2 h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">My Profile</span>
                            </button>
                        </div>
                    </div>

                    <!-- My Tickets placeholder -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h4 class="font-semibold text-gray-800">My Assigned Tickets</h4>
                        </div>
                        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm">No tickets assigned yet</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
