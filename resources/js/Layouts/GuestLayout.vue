<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import { IconWorld } from '@tabler/icons-vue';
</script>

<template>
    <div 
        style="background-image: url('/img/background-login.svg'); background-repeat: repeat-x;"
    >
        <div class="flex flex-col min-h-screen">
            <div class="flex py-3 px-5 xs:px-10 justify-end items-center">
                <div class="w-[60px] h-[60px] p-[17.5px] flex items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-100 text-gray-800">
                    <IconWorld size="25" stroke-width="1.25" />
                </div>
            </div>
            <div class="flex flex-1 flex-col justify-center items-center py-12 md:px-8 md:gap-[60px]">
                <div class="w-full flex sm:flex-1 justify-center">
                    <div class="w-full max-w-xs sm:max-w-none sm:w-[360px] flex flex-col justify-center items-center mx-5">
                        <slot />
                    </div>
                </div>
                <div class="text-center text-gray-500 text-xs mt-auto">© 2024 mosanes. All rights reserved.</div>
            </div>
        </div>
    </div>
</template>
