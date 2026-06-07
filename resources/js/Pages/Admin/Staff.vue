<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    staff: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ period: 'month', periodLabel: 'This month' }),
    },
    summary: {
        type: Object,
        default: () => ({
            staffCount: 0,
            resolvedCount: 0,
            activeCount: 0,
            pendingReviewCount: 0,
        }),
    },
    charts: {
        type: Object,
        default: () => ({
            progress: [],
            priority: [],
        }),
    },
    heatmap: {
        type: Object,
        default: () => ({
            total: 0,
            days: [],
        }),
    },
});

const showStatistics = ref(false);
const selectedStaffId = ref(null);
const showStaffDropdown = ref(false);
const staffSearch = ref('');
const showAccomplishmentModal = ref(false);
const accomplishmentPeriod = ref('week');

const periodTabs = computed(() => [
    { label: 'Today', value: 'day' },
    { label: 'This Month', value: 'month' },
    { label: 'This Year', value: 'year' },
    { label: 'All Time', value: 'all' },
]);

const summaryCards = computed(() => [
    { label: 'Staff Members', value: props.summary.staffCount, color: 'bg-blue-500' },
    { label: `Resolved ${props.filters.periodLabel}`, value: props.summary.resolvedCount, color: 'bg-green-500' },
    { label: 'In Progress', value: props.summary.activeCount, color: 'bg-yellow-500' },
    { label: 'For Review', value: props.summary.pendingReviewCount, color: 'bg-indigo-500' },
]);

const selectedStaff = computed(() => {
    return props.staff.find((member) => member.id === selectedStaffId.value) ?? null;
});

const filteredStaff = computed(() => {
    const search = staffSearch.value.trim().toLowerCase();

    if (!search) {
        return props.staff;
    }

    return props.staff.filter((member) => {
        return member.name.toLowerCase().includes(search)
            || member.email.toLowerCase().includes(search);
    });
});

const activeCharts = computed(() => {
    return selectedStaff.value?.charts ?? props.charts;
});

const activeHeatmap = computed(() => {
    return selectedStaff.value?.heatmap ?? props.heatmap;
});

const statisticsTitle = computed(() => {
    return selectedStaff.value ? selectedStaff.value.name : 'All Staff';
});

const startOfDay = (date) => {
    const result = new Date(date);
    result.setHours(0, 0, 0, 0);

    return result;
};

const endOfDay = (date) => {
    const result = new Date(date);
    result.setHours(23, 59, 59, 999);

    return result;
};

const accomplishmentRange = computed(() => {
    const now = new Date();
    const start = startOfDay(now);

    if (accomplishmentPeriod.value === 'week') {
        start.setDate(start.getDate() - ((start.getDay() + 6) % 7));
    } else {
        start.setDate(1);
    }

    return { start, end: endOfDay(now) };
});

const accomplishmentTickets = computed(() => (selectedStaff.value?.accomplishments ?? []).filter((ticket) => {
    const resolvedDate = new Date(ticket.resolved_at_iso);

    return !Number.isNaN(resolvedDate.getTime())
        && resolvedDate >= accomplishmentRange.value.start
        && resolvedDate <= accomplishmentRange.value.end;
}));

const accomplishmentPeriodLabel = computed(() => (
    accomplishmentPeriod.value === 'week' ? 'This Week' : 'This Month'
));

const formatReportDate = (date) => new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
}).format(date);

const accomplishmentDateRange = computed(() => (
    `${formatReportDate(accomplishmentRange.value.start)} - ${formatReportDate(accomplishmentRange.value.end)}`
));

watch(
    () => props.staff,
    () => {
        if (selectedStaffId.value && !props.staff.some((member) => member.id === selectedStaffId.value)) {
            selectedStaffId.value = null;
        }
    },
);

const changePeriod = (period) => {
    router.get(route('admin.staff.index'), { period }, {
        preserveScroll: true,
        preserveState: true,
        only: ['staff', 'filters', 'summary'],
    });
};

const selectStaff = (member) => {
    selectedStaffId.value = member.id;
    showStatistics.value = true;
    showStaffDropdown.value = false;
    staffSearch.value = '';
};

const selectAllStaff = () => {
    selectedStaffId.value = null;
    showStaffDropdown.value = false;
    staffSearch.value = '';
};

const openAccomplishmentModal = () => {
    if (!selectedStaff.value) {
        return;
    }

    showAccomplishmentModal.value = true;
};

const closeAccomplishmentModal = () => {
    showAccomplishmentModal.value = false;
};

const downloadAccomplishment = () => {
    if (!selectedStaff.value) {
        return;
    }

    window.location.href = route('admin.staff.accomplishment-report', {
        user: selectedStaff.value.id,
        period: accomplishmentPeriod.value,
    });
};

const chartTotal = (items) => items.reduce((total, item) => total + item.value, 0);

const chartStyle = (items) => {
    const total = chartTotal(items);

    if (!total) {
        return { background: '#e5e7eb' };
    }

    let start = 0;
    const segments = items
        .filter((item) => item.value > 0)
        .map((item) => {
            const end = start + (item.value / total) * 100;
            const segment = `${item.color} ${start}% ${end}%`;
            start = end;
            return segment;
        });

    return { background: `conic-gradient(${segments.join(', ')})` };
};

const percent = (value, items) => {
    const total = chartTotal(items);

    if (!total) {
        return '0%';
    }

    return `${Math.round((value / total) * 100)}%`;
};

const heatmapWeeks = computed(() => {
    const days = activeHeatmap.value.days ?? [];

    if (!days.length) {
        return [];
    }

    const firstDay = new Date(`${days[0].date}T00:00:00`).getDay();
    const cells = [
        ...Array.from({ length: firstDay }, () => null),
        ...days,
    ];

    const weeks = [];

    for (let index = 0; index < cells.length; index += 7) {
        weeks.push(cells.slice(index, index + 7));
    }

    return weeks;
});

const monthLabels = computed(() => {
    return heatmapWeeks.value
        .map((week, index) => {
            const firstRealDay = week.find((day) => day);

            if (!firstRealDay) {
                return { index, label: '' };
            }

            const date = new Date(`${firstRealDay.date}T00:00:00`);

            if (date.getDate() > 7) {
                return { index, label: '' };
            }

            return {
                index,
                label: date.toLocaleString('en', { month: 'short' }),
            };
        })
        .filter((month) => month.label);
});

const heatmapClass = (count) => {
    if (!count) {
        return 'bg-gray-100';
    }

    if (count === 1) {
        return 'bg-green-200';
    }

    if (count <= 3) {
        return 'bg-green-400';
    }

    if (count <= 6) {
        return 'bg-green-600';
    }

    return 'bg-green-800';
};

const heatmapTitle = (day) => {
    if (!day) {
        return '';
    }

    return `${day.count} resolved ticket${day.count === 1 ? '' : 's'} on ${day.label}`;
};
</script>

<template>
    <Head title="Staff Performance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Staff Performance
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Review how many tickets each staff member has resolved.
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
                        v-for="tab in periodTabs"
                        :key="tab.value"
                        type="button"
                        class="rounded-md border px-4 py-2 text-sm font-semibold transition"
                        :class="filters.period === tab.value ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
                        @click="changePeriod(tab.value)"
                    >
                        {{ tab.label }}
                    </button>
                    <button
                        type="button"
                        class="rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                        @click="showStatistics = !showStatistics"
                    >
                        {{ showStatistics ? 'Hide Statistics' : 'Show Statistics' }}
                    </button>
                    <button
                        v-if="selectedStaff"
                        type="button"
                        class="rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        @click="selectAllStaff"
                    >
                        All Staff
                    </button>
                </div>

                <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="stat in summaryCards" :key="stat.label" class="overflow-hidden rounded-xl bg-white shadow-sm">
                        <div :class="[stat.color, 'h-1.5']"></div>
                        <div class="p-6">
                            <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="showStatistics" class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">Staff Progress</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ statisticsTitle }} work status and resolved tickets for {{ filters.periodLabel.toLowerCase() }}.</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">
                            <div class="relative h-44 w-44 shrink-0 rounded-full" :style="chartStyle(activeCharts.progress)">
                                <div class="absolute inset-8 rounded-full bg-white"></div>
                            </div>
                            <div class="w-full space-y-3">
                                <div v-for="item in activeCharts.progress" :key="item.label" class="flex items-center justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="h-3 w-3 shrink-0 rounded-full" :style="{ backgroundColor: item.color }"></span>
                                        <span class="truncate text-sm font-medium text-gray-700">{{ item.label }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ item.value }} / {{ percent(item.value, activeCharts.progress) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <div>
                            <h3 class="font-semibold text-gray-900">Resolved Ticket Priority Mix</h3>
                            <p class="mt-1 text-sm text-gray-500">Priority percentages for {{ statisticsTitle }} resolved tickets in {{ filters.periodLabel.toLowerCase() }}.</p>
                        </div>

                        <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">
                            <div class="relative h-44 w-44 shrink-0 rounded-full" :style="chartStyle(activeCharts.priority)">
                                <div class="absolute inset-8 rounded-full bg-white"></div>
                            </div>
                            <div class="w-full space-y-3">
                                <div v-for="item in activeCharts.priority" :key="item.label" class="flex items-center justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="h-3 w-3 shrink-0 rounded-full" :style="{ backgroundColor: item.color }"></span>
                                        <span class="truncate text-sm font-medium text-gray-700">{{ item.label }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ item.value }} / {{ percent(item.value, activeCharts.priority) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">Resolved Ticket Activity</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ activeHeatmap.total }} resolved ticket{{ activeHeatmap.total === 1 ? '' : 's' }} in the last year for {{ statisticsTitle }}.
                                </p>
                            </div>
                            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                <div class="relative w-full sm:w-72">
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                                        @click="showStaffDropdown = !showStaffDropdown"
                                    >
                                        <span class="truncate">{{ selectedStaff?.name ?? 'Select a staff member' }}</span>
                                        <svg class="ms-2 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div
                                        v-if="showStaffDropdown"
                                        class="absolute right-0 z-20 mt-2 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg"
                                    >
                                        <div class="border-b border-gray-100 p-2">
                                            <input
                                                v-model="staffSearch"
                                                type="text"
                                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Search staff name"
                                            />
                                        </div>
                                        <div class="max-h-64 overflow-y-auto py-1">
                                            <button
                                                v-for="member in filteredStaff"
                                                :key="member.id"
                                                type="button"
                                                class="block w-full px-3 py-2 text-left transition hover:bg-indigo-50"
                                                :class="selectedStaffId === member.id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700'"
                                                @click="selectStaff(member)"
                                            >
                                                <span class="block text-sm font-semibold">{{ member.name }}</span>
                                                <span class="mt-0.5 block truncate text-xs text-gray-500">{{ member.email }}</span>
                                            </button>
                                            <p v-if="!filteredStaff.length" class="px-3 py-4 text-center text-sm text-gray-400">
                                                No staff found.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="!selectedStaff"
                                    @click="openAccomplishmentModal"
                                >
                                    Generate Accomplishment Report
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeHeatmap.days?.length" class="overflow-x-auto p-6">
                        <div class="min-w-max">
                            <div class="relative ml-9 h-5">
                                <span
                                    v-for="month in monthLabels"
                                    :key="`${month.index}-${month.label}`"
                                    class="absolute text-xs font-medium text-gray-500"
                                    :style="{ left: `${month.index * 16}px` }"
                                >
                                    {{ month.label }}
                                </span>
                            </div>

                            <div class="flex gap-2">
                                <div class="grid grid-rows-7 gap-1 pt-0 text-xs font-medium text-gray-500">
                                    <span class="h-3"></span>
                                    <span class="h-3">Mon</span>
                                    <span class="h-3"></span>
                                    <span class="h-3">Wed</span>
                                    <span class="h-3"></span>
                                    <span class="h-3">Fri</span>
                                    <span class="h-3"></span>
                                </div>

                                <div class="flex gap-1">
                                    <div v-for="(week, weekIndex) in heatmapWeeks" :key="weekIndex" class="grid grid-rows-7 gap-1">
                                        <span
                                            v-for="(day, dayIndex) in week"
                                            :key="`${weekIndex}-${dayIndex}`"
                                            class="h-3 w-3 rounded-sm"
                                            :class="day ? heatmapClass(day.count) : 'bg-transparent'"
                                            :title="heatmapTitle(day)"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-end gap-2 text-xs text-gray-500">
                                <span>Less</span>
                                <span class="h-3 w-3 rounded-sm bg-gray-100"></span>
                                <span class="h-3 w-3 rounded-sm bg-green-200"></span>
                                <span class="h-3 w-3 rounded-sm bg-green-400"></span>
                                <span class="h-3 w-3 rounded-sm bg-green-600"></span>
                                <span class="h-3 w-3 rounded-sm bg-green-800"></span>
                                <span>More</span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-16 text-center text-sm text-gray-400">
                        No resolved ticket activity yet.
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showAccomplishmentModal" max-width="2xl" @close="closeAccomplishmentModal">
            <div class="p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Staff Accomplishment Report</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ selectedStaff?.name }}</p>
                    </div>
                    <div class="inline-flex self-start rounded-lg bg-gray-100 p-1">
                        <button
                            v-for="period in [{ value: 'week', label: 'This Week' }, { value: 'month', label: 'This Month' }]"
                            :key="period.value"
                            type="button"
                            class="rounded-md px-3 py-2 text-xs font-semibold transition"
                            :class="accomplishmentPeriod === period.value ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                            @click="accomplishmentPeriod = period.value"
                        >
                            {{ period.label }}
                        </button>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50/60 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ accomplishmentPeriodLabel }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ accomplishmentDateRange }}</p>
                        </div>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                            {{ accomplishmentTickets.length }} resolved
                        </span>
                    </div>

                    <div v-if="accomplishmentTickets.length" class="mt-5 space-y-3">
                        <div v-for="ticket in accomplishmentTickets" :key="ticket.id" class="rounded-lg border border-white bg-white p-4 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="font-semibold text-gray-900">{{ ticket.title }}</p>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-700">{{ ticket.priority }}</span>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600">{{ ticket.resolution_note || 'Ticket resolved and approved by admin.' }}</p>
                            <p class="mt-2 text-xs font-medium text-gray-400">Resolved {{ ticket.resolved_at }}</p>
                        </div>
                    </div>
                    <p v-else class="mt-5 rounded-lg bg-white px-4 py-8 text-center text-sm text-gray-400">
                        No tickets were resolved during this period.
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeAccomplishmentModal">Close</SecondaryButton>
                    <PrimaryButton type="button" @click="downloadAccomplishment">
                        Download PDF
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
