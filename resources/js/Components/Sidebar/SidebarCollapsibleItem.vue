<script setup>
import { Link } from '@inertiajs/vue3'
import {EmptyCircleIcon} from "@/Components/Icons/outline.jsx";
import {sidebarState} from "@/Composables/index.js";
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    href: String,
    title: String,
    active: {
        type: Boolean,
        default: false,
    },
    external: {
        type: Boolean,
        default: false,
    },
    pendingCounts: Number
})

const { external } = props

const Tag = external ? 'a' : Link
</script>

<template>
    <li
        :class="[
            'text-sm rounded-lg hover:cursor-pointer hover:bg-primary-50 mt-1',
        ]"
    >
        <component
            :is="Tag"
            :href="href"
            v-bind="$attrs"
            :class="[
                'p-3 flex gap-3 items-center hover:text-primary-500 w-full',
                {
                    'text-primary-500': active,
                    'text-gray-950': !active,
                },
            ]"
        >
            <div class="p-1 flex items-center justify-center">
                <EmptyCircleIcon
                    aria-hidden="true"
                    class="flex-shrink-0 w-2.5 h-2.5"
                />
            </div>
            <span v-show="sidebarState.isOpen || sidebarState.isHovered" class="text-sm font-medium w-full">
                {{ title }}
            </span>
            <Badge
                v-if="pendingCounts > 0 && (sidebarState.isOpen || sidebarState.isHovered)"
                class="text-xs text-white"
                :pill="true"
            >
                {{ pendingCounts }}
            </Badge>
        </component>
    </li>
</template>
