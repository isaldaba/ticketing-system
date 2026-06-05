<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
});

const showGuestTicketModal = ref(false);
const showGuestTicketSuccessModal = ref(false);
const guestTicketForm = useForm({
    title: '',
    requester_name: '',
    priority: 'medium',
    concern: '',
});

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
];

const openGuestTicketModal = () => {
    guestTicketForm.clearErrors();
    showGuestTicketModal.value = true;
};

const closeGuestTicketModal = () => {
    showGuestTicketModal.value = false;
    guestTicketForm.reset();
    guestTicketForm.clearErrors();
};

const submitGuestTicket = () => {
    guestTicketForm.post(route('guest.tickets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeGuestTicketModal();
            showGuestTicketSuccessModal.value = true;
        },
    });
};

const features = [
    {
        title: 'Ticket Management',
        description: 'Create, assign, and track support tickets from submission to resolution in one place.',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        iconBg: 'bg-indigo-50',
        iconColor: 'text-indigo-600',
    },
    {
        title: 'Role-Based Access',
        description: 'Admins get full control; Staff see only their assigned workload. Clean and secure.',
        icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        iconBg: 'bg-purple-50',
        iconColor: 'text-purple-600',
    },
    {
        title: 'Status Tracking',
        description: 'Monitor ticket statuses  Open, In Progress, Resolved, Closed  at a glance.',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        iconBg: 'bg-yellow-50',
        iconColor: 'text-yellow-600',
    },
    {
        title: 'Priority Levels',
        description: 'Tag tickets as Low, Medium, High, or Critical to ensure the right issues are addressed first.',
        icon: 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z',
        iconBg: 'bg-red-50',
        iconColor: 'text-red-600',
    },
    {
        title: 'Assignment & Routing',
        description: 'Admins assign tickets to staff members, ensuring every request has an owner.',
        icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
    },
    {
        title: 'Secure Authentication',
        description: 'Built on Laravel Breeze with Inertia.js  fast, secure, and production-ready.',
        icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        iconBg: 'bg-green-50',
        iconColor: 'text-green-600',
    },
];

const steps = [
    { title: 'Submit Ticket', desc: 'User or staff submits a support request with details and priority.' },
    { title: 'Admin Assigns', desc: 'Admin reviews and assigns the ticket to a staff member.' },
    { title: 'Staff Works', desc: 'Staff updates the ticket status as they investigate and resolve.' },
    { title: 'Resolved & Closed', desc: 'Issue resolved, ticket closed, and requester notified.' },
];

const adminPerms = [
    'Manage all tickets',
    'Assign tickets to staff',
    'View reports & analytics',
    'Manage staff accounts',
    'Configure system settings',
];

const staffPerms = [
    'View assigned tickets',
    'Update ticket status',
    'Add notes & responses',
    'Mark tickets as resolved',
    'View personal dashboard',
];
</script>

<template>
    <Head title="HelpDesk  L1 Ticketing System" />

    <div class="min-h-screen bg-white text-gray-800 font-sans">

        <!-- Navbar -->
        <nav class="fixed inset-x-0 top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">HelpDesk</span>
                </div>
                <div class="hidden items-center gap-8 md:flex">
                    <a href="#features" class="text-sm text-gray-600 hover:text-indigo-600 transition">Features</a>
                    <a href="#how-it-works" class="text-sm text-gray-600 hover:text-indigo-600 transition">How it Works</a>
                    <a href="#roles" class="text-sm text-gray-600 hover:text-indigo-600 transition">Roles</a>
                </div>
                <div class="flex items-center gap-3">
                    <Link v-if="canLogin" :href="route('login')" class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition">
                        Log in
                    </Link>
                    <Link v-if="canRegister" :href="route('register')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 transition">
                        Get Started
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-24 overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-purple-50">
            <div class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-indigo-100 opacity-60 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-purple-100 opacity-60 blur-3xl"></div>
            <div class="relative mx-auto max-w-7xl px-6">
                <div class="grid items-center gap-16 lg:grid-cols-2">
                    <div>
                        <span class="inline-block rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 mb-4">
                            L1 Support Ticketing System
                        </span>
                        <h1 class="text-5xl font-extrabold leading-tight text-gray-900 lg:text-6xl">
                            Resolve issues <span class="text-indigo-600">faster</span>, together.
                        </h1>
                        <p class="mt-6 text-lg text-gray-500 leading-relaxed max-w-lg">
                            A streamlined ticketing platform for your support team. Manage, assign, and resolve support requests with clarity  built for Admins and Staff.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-4">
                            <button
                                type="button"
                                class="rounded-lg bg-gray-900 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-gray-800 hover:shadow-xl"
                                @click="openGuestTicketModal"
                            >
                                Submit a Ticket
                            </button>
                            <Link :href="route('register')" class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-indigo-700 transition">
                                Create an Account
                            </Link>
                            <Link :href="route('login')" class="rounded-lg border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 hover:border-indigo-400 hover:text-indigo-600 transition">
                                Sign In
                            </Link>
                        </div>
                        <div class="mt-12 flex gap-10">
                            <div>
                                <p class="text-3xl font-bold text-gray-900">2</p>
                                <p class="text-sm text-gray-400">Role Types</p>
                            </div>
                            <div class="border-l border-gray-200 pl-10">
                                <p class="text-3xl font-bold text-gray-900">L1</p>
                                <p class="text-sm text-gray-400">Support Level</p>
                            </div>
                            <div class="border-l border-gray-200 pl-10">
                                <p class="text-3xl font-bold text-gray-900">&#8734;</p>
                                <p class="text-sm text-gray-400">Tickets</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Image Placeholder -->
                    <div class="relative">
                        <div class="overflow-hidden rounded-2xl shadow-2xl border border-gray-200 bg-gray-100">
                            <div class="flex items-center gap-2 bg-gray-200 px-4 py-3">
                                <span class="h-3 w-3 rounded-full bg-red-400"></span>
                                <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                                <span class="h-3 w-3 rounded-full bg-green-400"></span>
                                <span class="ml-3 flex-1 rounded bg-white px-3 py-1 text-xs text-gray-400">helpdesk.local/admin/dashboard</span>
                            </div>
                            <div class="bg-white p-6 space-y-4">
                                <div class="h-6 w-40 rounded bg-indigo-100"></div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="rounded-lg bg-indigo-50 p-4">
                                        <div class="h-3 w-16 rounded bg-indigo-200 mb-2"></div>
                                        <div class="h-7 w-10 rounded bg-indigo-400"></div>
                                    </div>
                                    <div class="rounded-lg bg-yellow-50 p-4">
                                        <div class="h-3 w-16 rounded bg-yellow-200 mb-2"></div>
                                        <div class="h-7 w-10 rounded bg-yellow-400"></div>
                                    </div>
                                    <div class="rounded-lg bg-green-50 p-4">
                                        <div class="h-3 w-16 rounded bg-green-200 mb-2"></div>
                                        <div class="h-7 w-10 rounded bg-green-400"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="(color, i) in ['yellow', 'blue', 'green', 'red']" :key="i"
                                        class="flex items-center gap-3 rounded-lg border border-gray-100 p-3">
                                        <span :class="`h-2 w-2 rounded-full bg-${color}-400 flex-shrink-0`"></span>
                                        <div class="h-3 flex-1 rounded bg-gray-100"></div>
                                        <div class="h-3 w-12 rounded bg-gray-100"></div>
                                    </div>
                                </div>
                                <!-- Image slot -->
                                <div class="flex h-24 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 text-xs text-gray-400">
                                    Screenshot coming soon
                                </div>
                            </div>
                        </div>
                        <div class="absolute -right-4 -top-4 rounded-xl bg-white px-4 py-3 shadow-lg border border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold text-gray-700">Ticket Resolved</p>
                                    <p class="text-xs text-gray-400">just now</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900">Everything your support team needs</h2>
                    <p class="mt-4 text-gray-500 max-w-xl mx-auto">Built to manage support requests efficiently with role-based access and a clean interface.</p>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="feature in features" :key="feature.title"
                        class="group rounded-2xl border border-gray-100 p-8 hover:border-indigo-200 hover:shadow-lg transition-all">
                        <div :class="[feature.iconBg, 'mb-5 inline-flex h-12 w-12 items-center justify-center rounded-xl']">
                            <svg class="h-6 w-6" :class="feature.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"/>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ feature.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how-it-works" class="py-24 bg-gray-50">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900">Simple workflow</h2>
                    <p class="mt-4 text-gray-500">From submission to resolution in a few steps.</p>
                </div>
                <div class="grid gap-8 lg:grid-cols-4">
                    <div v-for="(step, idx) in steps" :key="step.title" class="text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 text-white text-xl font-bold shadow-lg">
                            {{ idx + 1 }}
                        </div>
                        <h3 class="mb-2 font-semibold text-gray-900">{{ step.title }}</h3>
                        <p class="text-sm text-gray-500">{{ step.desc }}</p>
                    </div>
                </div>
                <!-- Ticket list mockup -->
                <div class="mt-16 overflow-hidden rounded-2xl border border-gray-200 shadow-xl">
                    <div class="flex items-center gap-2 bg-gray-200 px-4 py-3">
                        <span class="h-3 w-3 rounded-full bg-red-400"></span>
                        <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                        <span class="h-3 w-3 rounded-full bg-green-400"></span>
                        <span class="ml-3 flex-1 rounded bg-white px-3 py-1 text-xs text-gray-400">helpdesk.local/tickets</span>
                    </div>
                    <div class="bg-white">
                        <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                            <div class="h-5 w-32 rounded bg-gray-100"></div>
                            <div class="h-8 w-28 rounded-lg bg-indigo-500"></div>
                        </div>
                        <div class="divide-y divide-gray-50">
                            <div v-for="i in 5" :key="i" class="flex items-center gap-4 px-6 py-4">
                                <div class="h-4 w-4 rounded bg-gray-100 flex-shrink-0"></div>
                                <div class="h-3 flex-1 rounded bg-gray-100"></div>
                                <div class="h-3 w-24 rounded bg-gray-100"></div>
                                <div class="h-3 w-16 rounded bg-gray-100"></div>
                                <div class="h-6 w-20 rounded-full flex-shrink-0 bg-indigo-100"></div>
                            </div>
                        </div>
                        <!-- Image placeholder row -->
                        <div class="flex h-16 items-center justify-center border-t border-dashed border-gray-200 text-xs text-gray-400">
                            Screenshot placeholder  real UI coming soon
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Roles -->
        <section id="roles" class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-6">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900">Designed for two roles</h2>
                    <p class="mt-4 text-gray-500">Each role has a purpose-built dashboard and permissions.</p>
                </div>
                <div class="grid gap-8 lg:grid-cols-2">
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-700 p-8 text-white shadow-xl">
                        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10"></div>
                        <div class="absolute -bottom-8 -left-8 h-36 w-36 rounded-full bg-white/10"></div>
                        <span class="relative inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold mb-6">Admin</span>
                        <h3 class="relative text-2xl font-bold mb-3">Full Control</h3>
                        <p class="relative text-indigo-100 mb-6 text-sm leading-relaxed">Administrators manage all tickets, oversee staff activity, generate reports, and configure the system.</p>
                        <ul class="relative space-y-2 text-sm">
                            <li v-for="p in adminPerms" :key="p" class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-indigo-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ p }}
                            </li>
                        </ul>
                        <!-- Admin screenshot placeholder -->
                        <div class="relative mt-6 flex h-20 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-xs text-white/60">
                            Admin dashboard screenshot placeholder
                        </div>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 p-8 text-white shadow-xl">
                        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10"></div>
                        <div class="absolute -bottom-8 -left-8 h-36 w-36 rounded-full bg-white/10"></div>
                        <span class="relative inline-block rounded-full bg-white/20 px-3 py-1 text-xs font-semibold mb-6">Staff</span>
                        <h3 class="relative text-2xl font-bold mb-3">Focused Workflow</h3>
                        <p class="relative text-blue-100 mb-6 text-sm leading-relaxed">Staff members handle assigned tickets, update statuses, communicate with requesters, and log resolutions.</p>
                        <ul class="relative space-y-2 text-sm">
                            <li v-for="p in staffPerms" :key="p" class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-blue-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ p }}
                            </li>
                        </ul>
                        <!-- Staff screenshot placeholder -->
                        <div class="relative mt-6 flex h-20 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-xs text-white/60">
                            Staff dashboard screenshot placeholder
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-24 bg-indigo-600">
            <div class="mx-auto max-w-3xl px-6 text-center">
                <h2 class="text-4xl font-bold text-white">Ready to get started?</h2>
                <p class="mt-4 text-indigo-200">Sign up now and start managing your support tickets today.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <button
                        type="button"
                        class="rounded-lg bg-gray-900 px-8 py-3 text-sm font-semibold text-white shadow transition hover:-translate-y-0.5 hover:bg-gray-800 hover:shadow-lg"
                        @click="openGuestTicketModal"
                    >
                        Submit Guest Ticket
                    </button>
                    <Link :href="route('register')" class="rounded-lg bg-white px-8 py-3 text-sm font-semibold text-indigo-600 shadow hover:bg-indigo-50 transition">
                        Create Free Account
                    </Link>
                    <Link :href="route('login')" class="rounded-lg border border-white/40 px-8 py-3 text-sm font-semibold text-white hover:bg-white/10 transition">
                        Sign In
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 py-10 text-center text-sm text-gray-500">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="flex h-6 w-6 items-center justify-center rounded bg-indigo-600">
                    <svg class="h-3.5 w-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-white font-semibold">HelpDesk</span>
            </div>
            <p>&copy; 2026 HelpDesk Ticketing System. All rights reserved.</p>
        </footer>

        <Modal :show="showGuestTicketModal" max-width="xl" @close="closeGuestTicketModal">
            <form class="p-6" @submit.prevent="submitGuestTicket">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Submit Guest Ticket</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Send your concern to admin first. They will review it before adding it to the ticket list.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        @click="closeGuestTicketModal"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 8.586 15.657 2.93a1 1 0 1 1 1.414 1.414L11.414 10l5.657 5.657a1 1 0 0 1-1.414 1.414L10 11.414l-5.657 5.657a1 1 0 0 1-1.414-1.414L8.586 10 2.929 4.343A1 1 0 0 1 4.343 2.93L10 8.586Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <label for="guest-title" class="mb-2 block text-sm font-medium text-gray-700">Ticket title</label>
                        <input
                            id="guest-title"
                            v-model="guestTicketForm.title"
                            type="text"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="Example: Cannot access payroll account"
                        >
                        <InputError class="mt-2" :message="guestTicketForm.errors.title" />
                    </div>

                    <div>
                        <label for="guest-name" class="mb-2 block text-sm font-medium text-gray-700">Full name</label>
                        <input
                            id="guest-name"
                            v-model="guestTicketForm.requester_name"
                            type="text"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="Your full name"
                        >
                        <InputError class="mt-2" :message="guestTicketForm.errors.requester_name" />
                    </div>

                    <div>
                        <label for="guest-priority" class="mb-2 block text-sm font-medium text-gray-700">Priority</label>
                        <select
                            id="guest-priority"
                            v-model="guestTicketForm.priority"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="guestTicketForm.errors.priority" />
                    </div>

                    <div>
                        <label for="guest-concern" class="mb-2 block text-sm font-medium text-gray-700">Concern</label>
                        <textarea
                            id="guest-concern"
                            v-model="guestTicketForm.concern"
                            class="block min-h-36 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="State the issue, affected system, error messages, and anything already tried."
                        ></textarea>
                        <InputError class="mt-2" :message="guestTicketForm.errors.concern" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeGuestTicketModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :disabled="guestTicketForm.processing" :class="{ 'opacity-25': guestTicketForm.processing }">
                        Send to Admin
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <Modal :show="showGuestTicketSuccessModal" max-width="md" @close="showGuestTicketSuccessModal = false">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Ticket Request Submitted</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Your concern has been sent to admin for review. If admin approves it, it will be added to the ticket list.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <PrimaryButton type="button" @click="showGuestTicketSuccessModal = false">
                        Done
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </div>
</template>
