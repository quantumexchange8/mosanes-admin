<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div 
        class="min-h-screen flex flex-col justify-between md:justify-normal items-center py-12 md:px-8 md:gap-[60px]"
        style="background-image: url('/img/background-login.svg'); background-repeat: repeat-x;"
    >
        <div class="max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl flex-1 flex flex-col xs:justify-center items-center">
            <slot />
        </div>
        <div class="text-center text-gray-500 text-xs">© 2024 mosanes. All rights reserved.</div>
    </div>
</template>
