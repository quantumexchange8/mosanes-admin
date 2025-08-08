<script setup>
import { IconPencilMinus, IconTrash, IconDotsVertical } from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import { h, ref } from "vue";
import TieredMenu from "primevue/tieredmenu";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    post: Object,
});

const emit = defineEmits(['edit', 'delete']);
const menu = ref();

const currentUser = usePage().props.auth.user;
const isCurrentUserOwner = currentUser.id === props.post.user_id;

const items = ref([]);

if (isCurrentUserOwner) {
    items.value = [
        {
            label: 'edit',
            icon: h(IconPencilMinus),
            command: () => {
                emit('edit', props.post);
            },
        },
        {
            label: 'delete',
            icon: h(IconTrash),
            command: () => {
                emit('delete', props.post);
            },
        }
    ];
} else {
    items.value = [
        {
            label: 'delete',
            icon: h(IconTrash),
            command: () => {
                emit('delete', props.post);
            },
        }
    ];
}

const toggle = (event) => {
    event.stopPropagation();
    menu.value.toggle(event);
};
</script>

<template>
    <Button
        type="button"
        variant="gray-text"
        size="sm"
        iconOnly
        pill
        @click.stop="toggle($event)"
        aria-haspopup="true"
        aria-controls="overlay_tmenu"
    >
        <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
    </Button>
    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
            >
                <component :is="item.icon" size="20" stroke-width="1.25" :color="item.label === 'delete' ? '#F04438' : '#667085'" />
                <span class="font-medium" :class="{'text-error-500': item.label === 'delete'}">{{ $t(`public.${item.label}`) }}</span>
            </div>
        </template>
    </TieredMenu>
</template>
