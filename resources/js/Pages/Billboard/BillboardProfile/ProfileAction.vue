<script setup>
import StatusBadge from "@/Components/StatusBadge.vue";
import Button from "@/Components/Button.vue";
import {
    IconTrash,
    IconDotsVertical,
    IconListSearch,
    IconPencilMinus
} from "@tabler/icons-vue";
import {h, ref} from "vue";
import TieredMenu from "primevue/tieredmenu";

const props = defineProps({
    profile: Object
})

const menu = ref();
const visible = ref(false);
const dialogType = ref('');

const toggle = (event) => {
    menu.value.toggle(event);
};

const items = ref([
    {
        label: 'edit',
        icon: h(IconPencilMinus),
        command: () => {
            visible.value = true;
            dialogType.value = 'edit';
        },
    },
    {
        label: 'bonus_report',
        icon: h(IconListSearch),
        command: () => {
            visible.value = true;
            dialogType.value = 'bonus_report';
        },
    },
    {
        separator: true,
    },
    {
        label: 'delete_bonus_profile',
        icon: h(IconTrash),
        color: 'red',
        command: () => {
            requireConfirmation('disband');
        },
    },
]);
</script>

<template>
    <div class="flex justify-between items-center self-stretch">
        <StatusBadge :value="profile.bonus_badge">
            {{ $t(`public.${profile.sales_calculation_mode}`) }}
        </StatusBadge>

        <Button
            type="button"
            variant="gray-text"
            size="sm"
            icon-only
            pill
            @click="toggle"
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
                    :class="{
                        'text-error-500': item.color === 'red',
                        'text-gray-500': item.color !== 'red'
                    }"
                >
                    <component :is="item.icon" size="20" stroke-width="1.25" />
                    <span
                        class="font-medium"
                        :class="{
                            'text-error-500': item.color === 'red',
                            'text-gray-950': item.color !== 'red'
                        }"
                    >{{ $t(`public.${item.label}`) }}</span>
                </div>
            </template>
        </TieredMenu>
    </div>
</template>
