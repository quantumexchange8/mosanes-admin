<script setup>
import {
    IconPencilMinus,
    IconTrash,
    IconDots,
} from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import {h, ref, watch} from "vue";
import TieredMenu from "primevue/tieredmenu";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import axios from 'axios';

const props = defineProps({
    comment: Object,
})
const menu = ref();
const comment = ref()
const emit = defineEmits(['edit', 'comment-deleted']);

watch(() => [props.comment], ([newComment]) => {
        comment.value = newComment;
    }, { immediate: true }
);

const items = ref([
    {
        label: 'edit',
        icon: h(IconPencilMinus),
        command: () => {
            emit('edit', props.comment);
        },
    },
    {
        label: 'delete',
        icon: h(IconTrash),
        color: 'red',
        command: () => {
            requireConfirmation('delete');
        },
    },
]);

const confirm = useConfirm();

const requireConfirmation = (type) => {
    const messages = {
        delete: {
            group: 'headless-error',
            actionType: 'delete',
            header: trans('public.delete_comment_header'),
            text: trans('public.delete_comment_message'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.delete'),
            action: async () => {
                const response = await axios.delete(route('member.deleteComment'), {
                    data: {
                        id: props.comment.id,
                    },
                })
                if (response.data.success) {
                    emit('comment-deleted', response.data);
                }
            }
        },
    };

    const { group, actionType, header, message, text, dynamicText, suffix, cancelButton, acceptButton, action } = messages[type] || {};

    confirm.require({
        group,
        actionType,
        header,
        message,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
};


const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <Button
        variant="gray-text"
        size="sm"
        type="button"
        iconOnly
        pill
        @click="toggle"
        aria-haspopup="true"
        aria-controls="overlay_tmenu"
    >
        <IconDots size="16" stroke-width="1.25" color="#667085" />
    </Button>
    <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup>
        <template #item="{ item, props }">
            <div
                class="flex items-center gap-3 self-stretch"
                v-bind="props.action"
                :class="{
                    'text-error-500': item.color === 'red',
                    'text-gray-950': item.color !== 'red'
                }"
            >
                <component :is="item.icon" size="20" stroke-width="1.25" />
                <span class="font-medium">{{ $t('public.' + item.label) }}</span>
            </div>
        </template>
    </TieredMenu>

</template>
