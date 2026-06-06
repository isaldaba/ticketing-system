<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    availableTickets: {
        type: Array,
        default: () => [],
    },
    myTickets: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ available: 0, inProgress: 0, pendingReview: 0, resolved: 0 }),
    },
});

const { auth } = usePage().props;
const selectedTicket = ref(
    props.myTickets.find((ticket) => ticket.status === 'in_progress')
        ?? props.availableTickets[0]
        ?? props.myTickets[0]
        ?? null,
);
const showSubmitModal = ref(false);
const imageViewerUrl = ref(null);
const claimForm = useForm({});
const submitForm = useForm({
    resolution_note: '',
    user_remarks: '',
    resolution_image: null,
});
const MS_PER_DAY = 1000 * 60 * 60 * 24;

const statCards = computed(() => [
    { label: 'Available Tickets', value: props.stats.available, color: 'bg-yellow-500' },
    { label: 'My In Progress', value: props.stats.inProgress, color: 'bg-blue-500' },
    { label: 'For Admin Review', value: props.stats.pendingReview, color: 'bg-indigo-500' },
    { label: 'Resolved', value: props.stats.resolved, color: 'bg-green-500' },
]);

const inProgressTickets = computed(() => props.myTickets.filter((ticket) => ticket.status === 'in_progress'));
const pendingReviewTickets = computed(() => props.myTickets.filter((ticket) => ticket.status === 'pending_review'));
const resolvedTickets = computed(() => props.myTickets.filter((ticket) => ticket.status === 'resolved'));

const parseDateValue = (value) => {
    if (!value) {
        return null;
    }

    const parsedDate = new Date(value);

    return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
};

const countDays = (start, end) => {
    if (!start || !end) {
        return 0;
    }

    return Math.max(0, Math.ceil((end.getTime() - start.getTime()) / MS_PER_DAY));
};

const pluralizeDays = (value) => `${value} day${value === 1 ? '' : 's'}`;

const isSameCalendarDate = (firstDate, secondDate) => (
    Boolean(firstDate && secondDate)
    && firstDate.toDateString() === secondDate.toDateString()
);

const compareTicketsByDueDate = (firstTicket, secondTicket) => {
    const firstDueDate = parseDateValue(firstTicket.due_date_iso);
    const secondDueDate = parseDateValue(secondTicket.due_date_iso);

    if (firstDueDate && secondDueDate && firstDueDate.getTime() !== secondDueDate.getTime()) {
        return firstDueDate.getTime() - secondDueDate.getTime();
    }

    if (firstDueDate && !secondDueDate) {
        return -1;
    }

    if (!firstDueDate && secondDueDate) {
        return 1;
    }

    return firstTicket.id - secondTicket.id;
};

const roadmapStageMeta = {
    open: {
        label: 'Ready to start',
        description: 'Available for staff pickup.',
        cardClass: 'border-amber-200 bg-amber-50/70',
        dotClass: 'bg-amber-500',
    },
    in_progress: {
        label: 'Being implemented',
        description: 'Currently under active work.',
        cardClass: 'border-sky-200 bg-sky-50/80',
        dotClass: 'bg-sky-500',
    },
    pending_review: {
        label: 'Waiting for approval',
        description: 'Submitted and waiting for admin review.',
        cardClass: 'border-indigo-200 bg-indigo-50/80',
        dotClass: 'bg-indigo-500',
    },
};

const roadmapTickets = computed(() => {
    const currentDate = new Date();

    return [...props.availableTickets, ...props.myTickets]
        .filter((ticket) => ticket.status !== 'resolved')
        .sort(compareTicketsByDueDate)
        .map((ticket) => {
            const dueDate = parseDateValue(ticket.due_date_iso);
            const stage = roadmapStageMeta[ticket.status] ?? roadmapStageMeta.open;

            let deadlineLabel = 'No target finish';
            let deadlineClass = 'bg-slate-100 text-slate-600';

            if (dueDate) {
                if (currentDate.getTime() > dueDate.getTime()) {
                    deadlineLabel = `${pluralizeDays(countDays(dueDate, currentDate))} overdue`;
                    deadlineClass = 'bg-rose-100 text-rose-700';
                } else if (isSameCalendarDate(currentDate, dueDate)) {
                    deadlineLabel = 'Due today';
                    deadlineClass = 'bg-amber-100 text-amber-700';
                } else {
                    const daysLeft = countDays(currentDate, dueDate);

                    deadlineLabel = `${pluralizeDays(daysLeft)} left`;
                    deadlineClass = daysLeft <= 2
                        ? 'bg-amber-100 text-amber-700'
                        : 'bg-emerald-100 text-emerald-700';
                }
            }

            return {
                ...ticket,
                roadmapStage: stage.label,
                roadmapDescription: stage.description,
                roadmapCardClass: stage.cardClass,
                roadmapDotClass: stage.dotClass,
                roadmapDeadlineLabel: deadlineLabel,
                roadmapDeadlineClass: deadlineClass,
            };
        });
});

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

const claimTicket = (ticket) => {
    claimForm.patch(route('staff.tickets.claim', ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTicket.value = null;
        },
    });
};

const openSubmitModal = () => {
    submitForm.reset();
    submitForm.clearErrors();
    showSubmitModal.value = true;
};

const closeSubmitModal = () => {
    showSubmitModal.value = false;
    submitForm.reset();
    submitForm.clearErrors();
};

const openImageViewer = (url) => {
    imageViewerUrl.value = url;
};

const closeImageViewer = () => {
    imageViewerUrl.value = null;
};

const submitTicket = () => {
    if (!selectedTicket.value) {
        return;
    }

    submitForm.post(route('staff.tickets.submit', selectedTicket.value.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            selectedTicket.value = null;
            closeSubmitModal();
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Dashboard
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Pick available tickets, work them, then send them to admin for review.
                    </p>
                </div>
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                    User
                </span>
            </div>
        </template>

        <div class="dashboard-surface py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="soft-gradient-banner animate-rise mb-8 rounded-xl">
                    <div class="relative px-8 py-7 text-white">
                        <h3 class="text-2xl font-bold">Welcome, {{ auth.user.name }}!</h3>
                        <p class="mt-1 text-indigo-100">Choose a ticket you can resolve and submit your work back to admin.</p>
                    </div>
                </div>

                <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="stat in statCards" :key="stat.label" class="stat-card animate-rise-delay-1 rounded-xl">
                        <div :class="[stat.color, 'h-1.5']"></div>
                        <div class="relative p-6">
                            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>

                <div class="panel-card animate-rise-delay-2 mb-8 overflow-hidden rounded-xl">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">Implementation Roadmap</h3>
                                <p class="mt-1 text-sm text-gray-500">Visible tickets arranged by target finish so staff can see what needs attention next.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                {{ roadmapTickets.length }} active ticket{{ roadmapTickets.length === 1 ? '' : 's' }}
                            </span>
                        </div>
                    </div>
                    <div v-if="roadmapTickets.length" class="overflow-x-auto px-6 py-6">
                        <div class="flex min-w-max items-stretch gap-5">
                            <div
                                v-for="(ticket, index) in roadmapTickets"
                                :key="ticket.id"
                                class="flex w-72 shrink-0 flex-col"
                            >
                                <div class="mb-3 flex items-center gap-3 px-1">
                                    <span class="h-3 w-3 shrink-0 rounded-full" :class="ticket.roadmapDotClass"></span>
                                    <span v-if="index !== roadmapTickets.length - 1" class="h-px flex-1 bg-slate-200"></span>
                                </div>
                                <button
                                    type="button"
                                    class="flex h-full flex-col rounded-2xl border p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                    :class="[ticket.roadmapCardClass, { 'ring-2 ring-indigo-400 ring-offset-2': selectedTicket?.id === ticket.id }]"
                                    @click="selectedTicket = ticket"
                                >
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900">{{ ticket.title }}</p>
                                            <p class="mt-1 text-xs font-medium text-gray-500">{{ ticket.requester_name }}</p>
                                        </div>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="ticket.roadmapDeadlineClass">
                                            {{ ticket.roadmapDeadlineLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[ticket.priority]">
                                            {{ ticket.priority }}
                                        </span>
                                        <span class="rounded-full bg-white/80 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                            {{ ticket.roadmapStage }}
                                        </span>
                                    </div>

                                    <div class="mt-4 space-y-1 text-xs text-gray-600">
                                        <p v-if="ticket.due_date"><span class="font-semibold text-gray-800">Target finish:</span> {{ ticket.due_date }}</p>
                                        <p v-else><span class="font-semibold text-gray-800">Target finish:</span> Not set</p>
                                        <p><span class="font-semibold text-gray-800">Status:</span> {{ statusLabels[ticket.status] }}</p>
                                    </div>

                                    <p class="mt-4 text-sm leading-6 text-gray-600">{{ ticket.roadmapDescription }}</p>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="px-6 py-12 text-center text-sm text-gray-400">
                        No active tickets are available for the roadmap right now.
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_420px]">
                    <div class="space-y-6">
                        <div class="panel-card animate-rise-delay-2 overflow-hidden rounded-xl">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">Available Tickets</h3>
                            </div>
                            <div v-if="availableTickets.length" class="divide-y divide-gray-100">
                                <div v-for="ticket in availableTickets" :key="ticket.id" class="px-6 py-4 transition hover:bg-indigo-50/60">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <button type="button" class="min-w-0 text-left" @click="selectedTicket = ticket">
                                            <p class="truncate font-semibold text-gray-900">{{ ticket.title }}</p>
                                            <p class="mt-1 text-sm text-gray-500">{{ ticket.requester_name }}</p>
                                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-gray-500">
                                                <p v-if="ticket.due_date">Target finish: {{ ticket.due_date }}</p>
                                                <p>In system: {{ ticket.created_at }}</p>
                                            </div>
                                        </button>
                                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[ticket.priority]">{{ ticket.priority }}</span>
                                            <SecondaryButton type="button" @click="claimTicket(ticket)">Pick Ticket</SecondaryButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="px-6 py-12 text-center text-sm text-gray-400">No available tickets right now.</div>
                        </div>

                        <div class="panel-card animate-rise-delay-3 overflow-hidden rounded-xl">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">My In Progress</h3>
                            </div>
                            <div v-if="inProgressTickets.length" class="divide-y divide-gray-100">
                                <button
                                    v-for="ticket in inProgressTickets"
                                    :key="ticket.id"
                                    type="button"
                                    class="block w-full px-6 py-4 text-left transition hover:bg-gray-50"
                                    :class="{ 'bg-indigo-50': selectedTicket?.id === ticket.id }"
                                    @click="selectedTicket = ticket"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-gray-900">{{ ticket.title }}</p>
                                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-gray-500">
                                                <p v-if="ticket.due_date">Target finish: {{ ticket.due_date }}</p>
                                                <p>In system: {{ ticket.created_at }}</p>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[ticket.priority]">{{ ticket.priority }}</span>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[ticket.status]">{{ statusLabels[ticket.status] }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div v-else class="px-6 py-12 text-center text-sm text-gray-400">No tickets in progress.</div>
                        </div>

                        <div class="panel-card animate-rise-delay-3 overflow-hidden rounded-xl">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">For Admin Review</h3>
                            </div>
                            <div v-if="pendingReviewTickets.length" class="divide-y divide-gray-100">
                                <button
                                    v-for="ticket in pendingReviewTickets"
                                    :key="ticket.id"
                                    type="button"
                                    class="block w-full px-6 py-4 text-left transition hover:bg-gray-50"
                                    :class="{ 'bg-indigo-50': selectedTicket?.id === ticket.id }"
                                    @click="selectedTicket = ticket"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-gray-900">{{ ticket.title }}</p>
                                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-gray-500">
                                                <p v-if="ticket.due_date">Target finish: {{ ticket.due_date }}</p>
                                                <p>In system: {{ ticket.created_at }}</p>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[ticket.priority]">{{ ticket.priority }}</span>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[ticket.status]">{{ statusLabels[ticket.status] }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div v-else class="px-6 py-12 text-center text-sm text-gray-400">No tickets waiting for admin review.</div>
                        </div>

                        <div class="panel-card animate-rise-delay-3 overflow-hidden rounded-xl">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">Resolved Tickets</h3>
                            </div>
                            <div v-if="resolvedTickets.length" class="divide-y divide-gray-100">
                                <button
                                    v-for="ticket in resolvedTickets"
                                    :key="ticket.id"
                                    type="button"
                                    class="block w-full px-6 py-4 text-left transition hover:bg-gray-50"
                                    :class="{ 'bg-indigo-50': selectedTicket?.id === ticket.id }"
                                    @click="selectedTicket = ticket"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-gray-900">{{ ticket.title }}</p>
                                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-gray-500">
                                                <p v-if="ticket.due_date">Target finish: {{ ticket.due_date }}</p>
                                                <p>In system: {{ ticket.created_at }}</p>
                                            </div>
                                        </div>
                                        <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[ticket.priority]">{{ ticket.priority }}</span>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[ticket.status]">{{ statusLabels[ticket.status] }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <div v-else class="px-6 py-12 text-center text-sm text-gray-400">No resolved tickets yet.</div>
                        </div>
                    </div>

                    <div class="panel-card animate-rise-delay-3 rounded-xl">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="font-semibold text-gray-900">Ticket Details</h3>
                        </div>
                        <div v-if="selectedTicket" class="p-6">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[selectedTicket.priority]">{{ selectedTicket.priority }}</span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[selectedTicket.status]">{{ statusLabels[selectedTicket.status] }}</span>
                            </div>

                            <h4 class="mt-4 text-lg font-semibold text-gray-900">{{ selectedTicket.title }}</h4>
                            <div class="mt-4 space-y-2 text-sm text-gray-600">
                                <p><span class="font-semibold text-gray-800">Requester:</span> {{ selectedTicket.requester_name }}</p>
                                <p v-if="selectedTicket.requester_email"><span class="font-semibold text-gray-800">Email:</span> {{ selectedTicket.requester_email }}</p>
                                <p v-if="selectedTicket.due_date"><span class="font-semibold text-gray-800">Target finish:</span> {{ selectedTicket.due_date }}</p>
                                <p><span class="font-semibold text-gray-800">In system:</span> {{ selectedTicket.created_at }}</p>
                            </div>

                            <div class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Concern</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm leading-6 text-gray-700">{{ selectedTicket.concern }}</p>
                            </div>

                            <div v-if="selectedTicket.admin_note" class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Admin Note</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-orange-100 bg-orange-50 p-4 text-sm leading-6 text-gray-700">{{ selectedTicket.admin_note }}</p>
                            </div>

                            <div v-if="selectedTicket.resolution_note" class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Your Resolution Note</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-gray-700">{{ selectedTicket.resolution_note }}</p>
                            </div>

                            <div v-if="selectedTicket.resolution_image_url" class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Resolution Image</p>
                                <button
                                    type="button"
                                    class="mt-2 block w-full overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    @click="openImageViewer(selectedTicket.resolution_image_url)"
                                >
                                    <img :src="selectedTicket.resolution_image_url" alt="Resolution documentation" class="max-h-80 w-full object-contain">
                                </button>
                            </div>

                            <div v-if="selectedTicket.user_remarks" class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Your Remarks</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-slate-200 bg-white p-4 text-sm leading-6 text-gray-700">{{ selectedTicket.user_remarks }}</p>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton type="button" @click="selectedTicket = null">Clear</SecondaryButton>
                                <SecondaryButton v-if="selectedTicket.status === 'open'" type="button" @click="claimTicket(selectedTicket)">Pick Ticket</SecondaryButton>
                                <PrimaryButton v-if="selectedTicket.status === 'in_progress'" type="button" @click="openSubmitModal">Submit to Admin</PrimaryButton>
                            </div>
                        </div>
                        <div v-else class="px-6 py-16 text-center text-sm text-gray-400">
                            Select a ticket to see the full concern.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showSubmitModal" max-width="lg" @close="closeSubmitModal">
            <form class="p-6" @submit.prevent="submitTicket">
                <h3 class="text-lg font-semibold text-gray-900">Submit Ticket to Admin</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Describe what you did and add any remarks the admin should consider.
                </p>

                <div class="mt-5">
                    <p class="mb-2 text-sm font-semibold text-gray-800">Resolution Note</p>
                    <textarea
                        v-model="submitForm.resolution_note"
                        class="block min-h-32 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        placeholder="Example: Reset the account access, verified login, and confirmed the requester can proceed."
                    ></textarea>
                    <InputError class="mt-2" :message="submitForm.errors.resolution_note" />
                </div>

                <div class="mt-5">
                    <p class="mb-2 text-sm font-semibold text-gray-800">Remarks</p>
                    <textarea
                        v-model="submitForm.user_remarks"
                        class="block min-h-28 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Example: The issue may come back if the user's password expires again next week."
                    ></textarea>
                    <InputError class="mt-2" :message="submitForm.errors.user_remarks" />
                </div>

                <div class="mt-5">
                    <p class="mb-2 text-sm font-semibold text-gray-800">Resolution Image</p>
                    <input
                        type="file"
                        accept="image/*"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                        @input="submitForm.resolution_image = $event.target.files[0] ?? null"
                    >
                    <p class="mt-2 text-xs text-gray-500">Optional. Upload a screenshot or photo for documentation.</p>
                    <InputError class="mt-2" :message="submitForm.errors.resolution_image" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeSubmitModal">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="submitForm.processing" :class="{ 'opacity-25': submitForm.processing }">Submit for Review</PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="Boolean(imageViewerUrl)" max-width="2xl" @close="closeImageViewer">
            <div class="relative bg-slate-950/95 p-4 text-white">
                <button
                    type="button"
                    class="absolute right-4 top-4 z-10 rounded-full bg-slate-900/80 p-2 text-white shadow-lg ring-1 ring-white/20 transition hover:bg-white hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-white"
                    @click="closeImageViewer"
                >
                    <span class="sr-only">Close image preview</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 8.586 15.657 2.93a1 1 0 1 1 1.414 1.414L11.414 10l5.657 5.657a1 1 0 0 1-1.414 1.414L10 11.414l-5.657 5.657a1 1 0 0 1-1.414-1.414L8.586 10 2.929 4.343A1 1 0 0 1 4.343 2.93L10 8.586Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div class="flex min-h-[70vh] items-center justify-center rounded-xl bg-slate-900/80 p-4 shadow-2xl">
                    <img
                        v-if="imageViewerUrl"
                        :src="imageViewerUrl"
                        alt="Resolution documentation preview"
                        class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl"
                    >
                </div>
                <p class="mt-3 text-center text-xs text-slate-300">Click outside, press Esc, or use the close button to exit.</p>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
