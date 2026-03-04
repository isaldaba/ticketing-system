<script setup>
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const { auth } = usePage().props;

onMounted(() => {
    if (auth.user.role === 'admin') {
        router.replace(route('admin.dashboard'));
    } else {
        router.replace(route('staff.dashboard'));
    }
});
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-100">
        <p class="text-gray-500">Redirecting...</p>
    </div>
</template>
