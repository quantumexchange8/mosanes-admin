<script setup>
import ToastListItem from "@/Components/ToastListItem.vue";
import {onUnmounted} from "vue";
import {Inertia} from "@inertiajs/inertia";
import {usePage} from "@inertiajs/vue3";
import toast from "@/Composables/toast.js"

const pageToast = usePage().props.toast;

let removeFinishEventListener = Inertia.on("finish", () => {
    if (pageToast) {
        toast.add({
            title: pageToast.title,
            message: pageToast.message,
            type: pageToast.type,
        });
    }
});

onUnmounted(() => removeFinishEventListener());

function remove(index) {
    toast.remove(index);
}
</script>
<template>
    <TransitionGroup
        tag="div"
        enter-from-class="-translate-y-full opacity-0"
        enter-active-class="duration-300"
        leave-active-class="duration-300"
        leave-to-class="-translate-y-full opacity-0"
        class="fixed top-4 middle z-50 min-w-[320px] w-full max-w-[640px] space-y-4">
        <ToastListItem
            v-for="(item, index) in toast.items"
            :key="item.key"
            :title="item.title"
            :message="item.message"
            :type="item.type"
            @remove="remove(index)"
        />
    </TransitionGroup>
</template>
