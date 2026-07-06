<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    tickets: {
        type: Array,
        default: () => [],
    },
    staffMembers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ status: 'open' }),
    },
    counts: {
        type: Object,
        default: () => ({ all: 0, guestReview: 0, guestRejected: 0, open: 0, inProgress: 0, pendingReview: 0, resolved: 0 }),
    },
});

const selectedTicket = ref(props.tickets[0] ?? null);
const showReturnModal = ref(false);
const showApproveModal = ref(false);
const showPublishModal = ref(false);
const showRejectGuestModal = ref(false);
const imageViewerUrl = ref(null);
const { auth } = usePage().props;
const publishForm = useForm({
    priority: 'medium',
    due_date: '',
    assigned_to: '',
});
const rejectGuestForm = useForm({
    admin_note: '',
});
const approveForm = useForm({});
const returnForm = useForm({
    admin_note: '',
});
const reportDatesForm = useForm({
    submitted_at: '',
    resolved_at: '',
});

const priorityClasses = {
    low: 'bg-slate-100 text-slate-700',
    medium: 'bg-blue-100 text-blue-700',
    high: 'bg-orange-100 text-orange-700',
    critical: 'bg-red-100 text-red-700',
};

const statusClasses = {
    guest_review: 'bg-purple-100 text-purple-700',
    guest_rejected: 'bg-red-100 text-red-700',
    open: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-700',
    pending_review: 'bg-indigo-100 text-indigo-700',
    resolved: 'bg-green-100 text-green-700',
};

const filterTabs = computed(() => [
    { label: 'Guest Review', value: 'guest_review', count: props.counts.guestReview },
    { label: 'Rejected', value: 'guest_rejected', count: props.counts.guestRejected },
    { label: 'Open', value: 'open', count: props.counts.open },
    { label: 'In Progress', value: 'in_progress', count: props.counts.inProgress },
    { label: 'For Review', value: 'pending_review', count: props.counts.pendingReview },
    { label: 'Resolved', value: 'resolved', count: props.counts.resolved },
    { label: 'All', value: 'all', count: props.counts.all },
]);

const statusLabels = {
    guest_review: 'Guest review',
    guest_rejected: 'Rejected',
    open: 'Open',
    in_progress: 'In progress',
    pending_review: 'For review',
    resolved: 'Resolved',
};

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
];

const changeFilter = (status) => {
    selectedTicket.value = null;
    router.get(route('admin.tickets.index'), { status }, {
        preserveScroll: true,
        preserveState: true,
        only: ['tickets', 'filters', 'counts'],
        onSuccess: (page) => {
            selectedTicket.value = page.props.tickets[0] ?? null;
        },
    });
};

const syncReportDatesForm = (ticket = selectedTicket.value) => {
    reportDatesForm.submitted_at = ticket?.submitted_at_value ?? '';
    reportDatesForm.resolved_at = ticket?.resolved_at_value ?? '';
    reportDatesForm.clearErrors();
};

watch(selectedTicket, (ticket) => {
    syncReportDatesForm(ticket);
}, { immediate: true });

const selectTicket = (ticket) => {
    selectedTicket.value = ticket;
};

const openPublishModal = () => {
    if (!selectedTicket.value) {
        return;
    }

    publishForm.priority = selectedTicket.value.priority ?? 'medium';
    publishForm.due_date = '';
    publishForm.assigned_to = selectedTicket.value.assigned_to ?? '';
    publishForm.clearErrors();
    showPublishModal.value = true;
};

const closePublishModal = () => {
    showPublishModal.value = false;
    publishForm.reset();
    publishForm.clearErrors();
};

const publishGuestTicket = () => {
    if (!selectedTicket.value) {
        return;
    }

    publishForm.patch(route('admin.tickets.publish', selectedTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTicket.value = null;
            closePublishModal();
        },
    });
};

const openRejectGuestModal = () => {
    rejectGuestForm.reset();
    rejectGuestForm.clearErrors();
    showRejectGuestModal.value = true;
};

const closeRejectGuestModal = () => {
    showRejectGuestModal.value = false;
    rejectGuestForm.reset();
    rejectGuestForm.clearErrors();
};

const openImageViewer = (url) => {
    imageViewerUrl.value = url;
};

const closeImageViewer = () => {
    imageViewerUrl.value = null;
};

const rejectGuestTicket = () => {
    if (!selectedTicket.value) {
        return;
    }

    rejectGuestForm.patch(route('admin.tickets.reject', selectedTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTicket.value = null;
            closeRejectGuestModal();
        },
    });
};

const approveTicket = () => {
    if (!selectedTicket.value) {
        return;
    }

    approveForm.patch(route('admin.tickets.approve', selectedTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTicket.value = null;
            closeApproveModal();
        },
    });
};

const openApproveModal = () => {
    showApproveModal.value = true;
};

const closeApproveModal = () => {
    showApproveModal.value = false;
};

const openReturnModal = () => {
    returnForm.reset();
    returnForm.clearErrors();
    showReturnModal.value = true;
};

const closeReturnModal = () => {
    showReturnModal.value = false;
    returnForm.reset();
    returnForm.clearErrors();
};

const returnTicket = () => {
    if (!selectedTicket.value) {
        return;
    }

    returnForm.patch(route('admin.tickets.return', selectedTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedTicket.value = null;
            closeReturnModal();
        },
    });
};

const updateReportDates = () => {
    if (!selectedTicket.value || selectedTicket.value.status !== 'resolved') {
        return;
    }

    const ticketId = selectedTicket.value.id;

    reportDatesForm.patch(route('admin.tickets.report-dates.update', ticketId), {
        preserveScroll: true,
        preserveState: true,
        only: ['tickets', 'filters', 'counts'],
        onSuccess: (page) => {
            selectedTicket.value = page.props.tickets.find((ticket) => ticket.id === ticketId) ?? null;
        },
    });
};
</script>

<template>
    <Head title="Ticket List" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Ticket List
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Review submitted tickets and decide whether they are resolved or need more work.
                    </p>
                </div>
                <Link
                    :href="route('admin.dashboard')"
                    class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    Dashboard
                </Link>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap gap-2">
                    <button
                        v-for="tab in filterTabs"
                        :key="tab.value"
                        type="button"
                        class="rounded-md border px-4 py-2 text-sm font-semibold transition"
                        :class="filters.status === tab.value ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
                        @click="changeFilter(tab.value)"
                    >
                        {{ tab.label }} {{ tab.count }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_420px]">
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="font-semibold text-gray-900">Tickets</h3>
                        </div>

                        <div v-if="tickets.length" class="divide-y divide-gray-100">
                            <button
                                v-for="ticket in tickets"
                                :key="ticket.id"
                                type="button"
                                class="block w-full px-6 py-4 text-left transition hover:bg-gray-50"
                                :class="{ 'bg-indigo-50': selectedTicket?.id === ticket.id }"
                                @click="selectTicket(ticket)"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900">{{ ticket.title }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ ticket.requester_name }}</p>
                                        <p v-if="ticket.due_date" class="mt-2 text-xs font-semibold text-gray-500">
                                            Target finish: {{ ticket.due_date }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">
                                            Taken by: {{ ticket.assignee_name ?? 'Unassigned' }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">
                                            In system: {{ ticket.created_at }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-gray-500">
                                            {{ ticket.status === 'resolved' ? 'Was open for' : 'Open for' }}: {{ ticket.open_for }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[ticket.priority]">
                                            {{ ticket.priority }}
                                        </span>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="statusClasses[ticket.status]">
                                            {{ statusLabels[ticket.status] }}
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <div v-else class="px-6 py-16 text-center text-gray-400">
                            <p class="text-sm">No tickets found for this view.</p>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="font-semibold text-gray-900">Ticket Details</h3>
                        </div>

                        <div v-if="selectedTicket" class="p-6">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="priorityClasses[selectedTicket.priority]">
                                    {{ selectedTicket.priority }}
                                </span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="statusClasses[selectedTicket.status]">
                                    {{ statusLabels[selectedTicket.status] }}
                                </span>
                            </div>

                            <h4 class="mt-4 text-lg font-semibold text-gray-900">{{ selectedTicket.title }}</h4>
                            <div class="mt-4 space-y-2 text-sm text-gray-600">
                                <p><span class="font-semibold text-gray-800">Requester:</span> {{ selectedTicket.requester_name }}</p>
                                <p v-if="selectedTicket.requester_email"><span class="font-semibold text-gray-800">Email:</span> {{ selectedTicket.requester_email }}</p>
                                <p v-if="selectedTicket.due_date"><span class="font-semibold text-gray-800">Target finish:</span> {{ selectedTicket.due_date }}</p>
                                <p v-if="selectedTicket.assignee_name"><span class="font-semibold text-gray-800">Assigned to:</span> {{ selectedTicket.assignee_name }}</p>
                                <p v-if="selectedTicket.submitted_at"><span class="font-semibold text-gray-800">Submitted:</span> {{ selectedTicket.submitted_at }}</p>
                                <p v-if="selectedTicket.resolved_at"><span class="font-semibold text-gray-800">Resolved:</span> {{ selectedTicket.resolved_at }}</p>
                                <p><span class="font-semibold text-gray-800">In system:</span> {{ selectedTicket.created_at }}</p>
                                <p><span class="font-semibold text-gray-800">{{ selectedTicket.status === 'resolved' ? 'Was open for:' : 'Open for:' }}</span> {{ selectedTicket.open_for }}</p>
                            </div>

                            <div v-if="selectedTicket.status === 'resolved'" class="mt-6 rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                                <div>
                                    <p class="text-sm font-semibold text-emerald-900">Report Dates</p>
                                    <p class="mt-1 text-sm leading-6 text-emerald-800">
                                        Update these timestamps when the accomplishment report needs corrected submission or resolution timing.
                                    </p>
                                </div>

                                <form class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2" @submit.prevent="updateReportDates">
                                    <div>
                                        <label for="report-submitted-at" class="mb-2 block text-sm font-medium text-gray-700">Submitted at</label>
                                        <input
                                            id="report-submitted-at"
                                            v-model="reportDatesForm.submitted_at"
                                            type="date"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <InputError class="mt-2" :message="reportDatesForm.errors.submitted_at" />
                                    </div>

                                    <div>
                                        <label for="report-resolved-at" class="mb-2 block text-sm font-medium text-gray-700">Resolved at</label>
                                        <input
                                            id="report-resolved-at"
                                            v-model="reportDatesForm.resolved_at"
                                            type="date"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                        <InputError class="mt-2" :message="reportDatesForm.errors.resolved_at" />
                                    </div>

                                    <div class="sm:col-span-2 flex justify-end gap-3">
                                        <SecondaryButton type="button" @click="syncReportDatesForm()">
                                            Reset
                                        </SecondaryButton>
                                        <PrimaryButton :disabled="reportDatesForm.processing" :class="{ 'opacity-25': reportDatesForm.processing }">
                                            Save Report Dates
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>

                            <div class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Concern</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                                    {{ selectedTicket.concern }}
                                </p>
                            </div>

                            <div v-if="selectedTicket.resolution_note" class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">User Resolution Note</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm leading-6 text-gray-700">
                                    {{ selectedTicket.resolution_note }}
                                </p>
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
                                <p class="text-sm font-semibold text-gray-800">User Remarks</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-slate-200 bg-white p-4 text-sm leading-6 text-gray-700">
                                    {{ selectedTicket.user_remarks }}
                                </p>
                            </div>

                            <div v-if="selectedTicket.admin_note" class="mt-6">
                                <p class="text-sm font-semibold text-gray-800">Admin Note</p>
                                <p class="mt-2 whitespace-pre-line rounded-lg border border-red-100 bg-red-50 p-4 text-sm leading-6 text-gray-700">
                                    {{ selectedTicket.admin_note }}
                                </p>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <SecondaryButton type="button" @click="selectedTicket = null">
                                    Clear
                                </SecondaryButton>
                                <button
                                    v-if="selectedTicket.status === 'guest_review'"
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-red-300 bg-red-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-700 shadow-sm transition hover:border-red-400 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    @click="openRejectGuestModal"
                                >
                                    Reject Request
                                </button>
                                <PrimaryButton
                                    v-if="selectedTicket.status === 'guest_review'"
                                    :disabled="publishForm.processing"
                                    :class="{ 'opacity-25': publishForm.processing }"
                                    @click="openPublishModal"
                                >
                                    Add to Tickets
                                </PrimaryButton>
                                <button
                                    v-if="selectedTicket.status === 'pending_review'"
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-amber-800 shadow-sm transition hover:border-amber-400 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                    @click="openReturnModal"
                                >
                                    Return as Unresolved
                                </button>
                                <PrimaryButton
                                    v-if="selectedTicket.status === 'pending_review'"
                                    :disabled="approveForm.processing"
                                    :class="{ 'opacity-25': approveForm.processing }"
                                    @click="openApproveModal"
                                >
                                    Approve Resolved
                                </PrimaryButton>
                            </div>
                        </div>

                        <div v-else class="px-6 py-16 text-center text-gray-400">
                            <p class="text-sm">Select a ticket to see the full concern.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showReturnModal" max-width="lg" @close="closeReturnModal">
            <form class="p-6" @submit.prevent="returnTicket">
                <h3 class="text-lg font-semibold text-gray-900">Return as Unresolved</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Add what still needs to be fixed before the ticket can be approved.
                </p>

                <div class="mt-5">
                    <textarea
                        v-model="returnForm.admin_note"
                        class="block min-h-32 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        placeholder="Example: Please include the affected device name and confirm the account was tested again."
                    ></textarea>
                    <InputError class="mt-2" :message="returnForm.errors.admin_note" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeReturnModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="returnForm.processing" :class="{ 'opacity-25': returnForm.processing }">
                        Send Back
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showPublishModal" max-width="lg" @close="closePublishModal">
            <form class="p-6" @submit.prevent="publishGuestTicket">
                <h3 class="text-lg font-semibold text-gray-900">Add Guest Request to Tickets</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Set the working details and assign the request now, or leave it open for staff pickup.
                </p>

                <div v-if="selectedTicket" class="mt-5 space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                    <p><span class="font-semibold text-gray-800">Ticket title:</span> {{ selectedTicket.title }}</p>
                    <p><span class="font-semibold text-gray-800">Requester:</span> {{ selectedTicket.requester_name }}</p>
                    <p><span class="font-semibold text-gray-800">Email:</span> {{ auth.user.email }}</p>
                    <div>
                        <p class="font-semibold text-gray-800">Concern</p>
                        <p class="mt-1 whitespace-pre-line text-gray-600">{{ selectedTicket.concern }}</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="publish-priority" class="mb-2 block text-sm font-medium text-gray-700">Priority</label>
                        <select
                            id="publish-priority"
                            v-model="publishForm.priority"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="publishForm.errors.priority" />
                    </div>

                    <div>
                        <label for="publish-due-date" class="mb-2 block text-sm font-medium text-gray-700">Target finish date</label>
                        <input
                            id="publish-due-date"
                            v-model="publishForm.due_date"
                            type="date"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <InputError class="mt-2" :message="publishForm.errors.due_date" />
                    </div>
                </div>

                <div class="mt-5">
                    <label for="publish-assigned-to" class="mb-2 block text-sm font-medium text-gray-700">Assign to staff</label>
                    <select
                        id="publish-assigned-to"
                        v-model="publishForm.assigned_to"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :disabled="!staffMembers.length"
                    >
                        <option value="">Leave unassigned for staff pickup</option>
                        <option v-for="member in staffMembers" :key="member.id" :value="member.id">
                            {{ member.name }}{{ member.email ? ` (${member.email})` : '' }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="publishForm.errors.assigned_to" />
                    <p v-if="!staffMembers.length" class="mt-2 text-sm text-amber-700">
                        No staff accounts are available for assignment.
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closePublishModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="publishForm.processing" :class="{ 'opacity-25': publishForm.processing }">
                        Add to Tickets
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showRejectGuestModal" max-width="lg" @close="closeRejectGuestModal">
            <form class="p-6" @submit.prevent="rejectGuestTicket">
                <h3 class="text-lg font-semibold text-gray-900">Reject Guest Request</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Reject this request if it does not need to become a ticket. You can leave a short internal note.
                </p>

                <div v-if="selectedTicket" class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm">
                    <p><span class="font-semibold text-gray-800">Ticket title:</span> {{ selectedTicket.title }}</p>
                    <p class="mt-2"><span class="font-semibold text-gray-800">Requester:</span> {{ selectedTicket.requester_name }}</p>
                    <div class="mt-3">
                        <p class="font-semibold text-gray-800">Concern</p>
                        <p class="mt-1 whitespace-pre-line text-gray-600">{{ selectedTicket.concern }}</p>
                    </div>
                </div>

                <div class="mt-5">
                    <label for="reject-admin-note" class="mb-2 block text-sm font-medium text-gray-700">Admin note</label>
                    <textarea
                        id="reject-admin-note"
                        v-model="rejectGuestForm.admin_note"
                        class="block min-h-28 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Example: This is a duplicate request or does not require L1 support."
                    ></textarea>
                    <InputError class="mt-2" :message="rejectGuestForm.errors.admin_note" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeRejectGuestModal">
                        Cancel
                    </SecondaryButton>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-red-700 focus:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-800"
                        :disabled="rejectGuestForm.processing"
                        :class="{ 'opacity-25': rejectGuestForm.processing }"
                    >
                        Reject Request
                    </button>
                </div>
            </form>
        </Modal>

        <Modal :show="showApproveModal" max-width="md" @close="closeApproveModal">
            <form class="p-6" @submit.prevent="approveTicket">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Approve this ticket?
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Are you sure you want to approve this ticket as resolved? This will mark it complete for the assigned staff.
                        </p>
                        <p v-if="selectedTicket" class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-800">
                            {{ selectedTicket.title }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeApproveModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="approveForm.processing" :class="{ 'opacity-25': approveForm.processing }">
                        Approve Ticket
                    </PrimaryButton>
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
