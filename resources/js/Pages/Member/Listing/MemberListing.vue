<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddMember from "@/Pages/Member/Listing/Partials/AddMember.vue";
</script>

<template>
    <AuthenticatedLayout title="Member Listing">
        <div class="w-full flex flex-col items-end self-stretch">
            <AddMember />
        </div>
    </AuthenticatedLayout>
</template>
