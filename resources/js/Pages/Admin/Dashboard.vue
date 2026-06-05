<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            open: 0,
            pendingReview: 0,
            resolved: 0,
            highPriority: 0,
        }),
    },
    recentTickets: {
        type: Array,
        default: () => [],
    },
});

const { auth } = usePage().props;
const showNewTicketModal = ref(false);

const form = useForm({
    title: '',
    requester_name: auth.user.name,
    requester_email: auth.user.email,
    priority: 'medium',
    due_date: '',
    concern: '',
});

const statCards = computed(() => [
    { label: 'Total Tickets', value: props.stats.total, color: 'bg-blue-500' },
    { label: 'Open Tickets', value: props.stats.open, color: 'bg-yellow-500' },
    { label: 'For Review', value: props.stats.pendingReview, color: 'bg-indigo-500' },
    { label: 'Resolved Tickets', value: props.stats.resolved, color: 'bg-green-500' },
]);

const priorityClasses = {
    low: 'bg-slate-100 text-slate-700',
    medium: 'bg-blue-100 text-blue-700',
    high: 'bg-orange-100 text-orange-700',
    critical: 'bg-red-100 text-red-700',
};

const statusClasses = {
    open: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-700',
    pending_review: 'bg-indigo-100 text-indigo-700',
    resolved: 'bg-green-100 text-green-700',
};

const statusLabels = {
    open: 'Open',
    in_progress: 'In progress',
    pending_review: 'For review',
    resolved: 'Resolved',
};

const openNewTicketModal = () => {
    form.clearErrors();
    showNewTicketModal.value = true;
};

const closeNewTicketModal = () => {
    showNewTicketModal.value = false;
    form.reset();
    form.requester_name = auth.user.name;
    form.requester_email = auth.user.email;
    form.priority = 'medium';
    form.due_date = '';
    form.clearErrors();
};

const submitTicket = () => {
    form.post(route('admin.tickets.store'), {
        preserveScroll: true,
        onSuccess: () => closeNewTicketModal(),
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                    Admin
                </span>
            </div>
        </template>

        <div class="dashboard-surface py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="soft-gradient-banner animate-rise mb-8 rounded-xl">
                    <div class="relative px-8 py-7 text-white">
                        <h3 class="text-2xl font-bold">Welcome back, {{ auth.user.name }}!</h3>
                        <p class="mt-1 text-indigo-100">Here's an overview of the ticketing system.</p>
                    </div>
                </div>

                <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="stat in statCards"
                        :key="stat.label"
                        class="stat-card animate-rise-delay-1 rounded-xl"
                    >
                        <div :class="[stat.color, 'h-1.5']"></div>
                        <div class="relative p-6">
                            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="panel-card animate-rise-delay-2 overflow-hidden rounded-xl">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h4 class="font-semibold text-gray-800">Quick Actions</h4>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-3">
                            <button
                                type="button"
                                class="action-tile flex min-h-28 flex-col items-center justify-center rounded-lg border border-gray-200 p-4 text-center transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                @click="openNewTicketModal"
                            >
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">New Ticket</span>
                            </button>
                            <Link
                                :href="route('admin.tickets.index')"
                                class="action-tile flex min-h-28 flex-col items-center justify-center rounded-lg border border-gray-200 p-4 text-center transition hover:border-indigo-300 hover:bg-indigo-50"
                            >
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Ticket List</span>
                            </Link>
                            <Link
                                :href="route('admin.staff.index')"
                                class="action-tile flex min-h-28 flex-col items-center justify-center rounded-lg border border-gray-200 p-4 text-center transition hover:border-indigo-300 hover:bg-indigo-50"
                            >
                                <svg class="mb-2 h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Staff</span>
                            </Link>
                        </div>
                    </div>

                    <div class="panel-card animate-rise-delay-3 overflow-hidden rounded-xl">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h4 class="font-semibold text-gray-800">Recent Tickets</h4>
                        </div>
                        <div v-if="recentTickets.length" class="divide-y divide-gray-100">
                            <div
                                v-for="ticket in recentTickets"
                                :key="ticket.id"
                                class="px-6 py-4 transition hover:bg-indigo-50/60"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-gray-900">{{ ticket.title }}</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ ticket.requester_name }} - {{ ticket.created_at }}
                                        </p>
                                        <p v-if="ticket.due_date" class="mt-1 text-xs font-medium text-gray-500">
                                            Due {{ ticket.due_date }}
                                        </p>
                                        <p class="mt-1 text-xs font-medium text-gray-500">
                                            In system: {{ ticket.created_at }}
                                        </p>
                                        <p class="mt-1 text-xs font-medium text-gray-500">
                                            {{ ticket.status === 'resolved' ? 'Was open for' : 'Open for' }}: {{ ticket.open_for }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 flex-wrap justify-end gap-2">
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                            :class="priorityClasses[ticket.priority]"
                                        >
                                            {{ ticket.priority }}
                                        </span>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                            :class="statusClasses[ticket.status]"
                                        >
                                            {{ statusLabels[ticket.status] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-16 text-gray-400">
                            <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm">No tickets yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showNewTicketModal" max-width="xl" @close="closeNewTicketModal">
            <form class="p-6" @submit.prevent="submitTicket">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">New Ticket</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Record the concern so it can be tracked from the admin dashboard.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        @click="closeNewTicketModal"
                    >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <InputLabel for="title" value="Ticket title" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            class="mt-1 block w-full"
                            type="text"
                            required
                            autofocus
                            placeholder="Example: Cannot access payroll account"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="requester_name" value="Requester name" />
                            <TextInput
                                id="requester_name"
                                v-model="form.requester_name"
                                class="mt-1 block w-full"
                                type="text"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.requester_name" />
                        </div>
                        <div>
                            <InputLabel for="requester_email" value="Requester email" />
                            <TextInput
                                id="requester_email"
                                v-model="form.requester_email"
                                class="mt-1 block w-full"
                                type="email"
                            />
                            <InputError class="mt-2" :message="form.errors.requester_email" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="priority" value="Priority" />
                        <select
                            id="priority"
                            v-model="form.priority"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.priority" />
                    </div>

                    <div>
                        <InputLabel for="due_date" value="Target finish date" />
                        <TextInput
                            id="due_date"
                            v-model="form.due_date"
                            class="mt-1 block w-full"
                            type="date"
                        />
                        <InputError class="mt-2" :message="form.errors.due_date" />
                    </div>

                    <div>
                        <InputLabel for="concern" value="Concern" />
                        <textarea
                            id="concern"
                            v-model="form.concern"
                            class="mt-1 block min-h-36 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="State the issue, affected system, error messages, and anything already tried."
                        ></textarea>
                        <InputError class="mt-2" :message="form.errors.concern" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <SecondaryButton type="button" @click="closeNewTicketModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                        Create Ticket
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
