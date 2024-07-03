<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <div class="w-full bg-primary-100 overflow-hidden shadow-lg rounded-lg">
            <div class="p-5 text-primary-900">You're logged in!</div>
        </div>
    </AuthenticatedLayout>
</template>
