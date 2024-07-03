<script setup>
import { sidebarState } from '@/Composables'
import {
    IconWorld,
    IconLogout
} from '@tabler/icons-vue';
import { Menu01Icon } from '@/Components/Icons/outline.jsx'
import ProfilePhoto from "@/Components/ProfilePhoto.vue";
import {Link} from "@inertiajs/vue3";

defineProps({
    title: String
})
</script>

<template>
    <nav
        aria-label="secondary"
        class="sticky top-0 z-10 py-2 px-3 md:px-5 bg-gray-25 flex items-center gap-3"
    >
        <div
            class="inline-flex items-center rounded-full hover:bg-gray-100 w-12 h-12 hover:select-none hover:cursor-pointer"
            @click="sidebarState.isOpen = !sidebarState.isOpen"
        >
            <Menu01Icon
                aria-hidden="true"
                class="text-gray-800"
            />
        </div>
        <div
            class="text-base md:text-lg font-semibold text-gray-950 w-full"
        >
            {{ title }}
        </div>
        <div class="flex items-center">
            <div class="w-12 h-12 p-3.5 flex items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-100 text-gray-800">
                <IconWorld size="20" stroke-width="1.25" />
            </div>
            <Link
                class="w-12 h-12 p-3.5 flex items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-100 text-gray-800"
                :href="route('logout')"
                method="post"
                as="button"
            >
                <IconLogout size="20" stroke-width="1.25" />
            </Link>
            <Link
                class="w-12 h-12 p-2 items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-100 hidden md:block"
                :href="route('profile.edit')"
            >
                <ProfilePhoto class="w-8 h-8" />
            </Link>
        </div>
    </nav>
</template>
