<script setup>
import { ref } from 'vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import { EmptyCircleIcon, ChevronDownIcon } from '@/Components/Icons/outline'
import { sidebarState } from '@/Composables'

const props = defineProps({
    title: {
        type: String,
    },
    icon: {
        required: false,
    },
    active: {
        type: Boolean,
    },
})

const { active } = props

const isOpen = ref(active)

const beforeEnter = (el) => {
    el.style.maxHeight = `0px`
}

const enter = (el) => {
    el.style.maxHeight = `${el.scrollHeight}px`
}

const beforeLeave = (el) => {
    el.style.maxHeight = `${el.scrollHeight}px`
}

const leave = (el) => {
    el.style.maxHeight = `0px`
}
</script>

<template>
    <div class="relative w-full">
        <SidebarLink @click="isOpen = !isOpen" :title="title" :active="active">
            <template #icon>
                <slot name="icon">
                    <EmptyCircleIcon
                        aria-hidden="true"
                        class="flex-shrink-0 w-5 h-5"
                    />
                </slot>
            </template>

            <template #arrow>
                <span
                    v-show="sidebarState.isOpen || sidebarState.isHovered"
                    aria-hidden="true"
                    class="relative block w-5 h-5 ml-auto"
                >
                    <ChevronDownIcon
                        :class="[
                            {
                                'rotate-180': isOpen,
                            },
                        ]"
                    />
                </span>
            </template>
        </SidebarLink>

        <transition
            @before-enter="beforeEnter"
            @enter="enter"
            @before-leave="beforeLeave"
            @leave="leave"
            appear
        >
            <div
                class="overflow-hidden transition-all duration-200 max-h-0"
                v-show="
                    isOpen
                "
            >
                <ul
                    class="relative w-full"
                >
                    <slot />
                </ul>
            </div>
        </transition>
    </div>
</template>
