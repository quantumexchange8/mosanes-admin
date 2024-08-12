<script setup>
import { ref } from 'vue';
import { IconDotsVertical, IconReportSearch, IconPencilMinus, IconTrash } from '@tabler/icons-vue';
import Button from '@/Components/Button.vue';
import Dialog from 'primevue/dialog';
import EditGroup from '@/Pages/Group/Partials/EditGroup.vue';
import DropdownOverlay from '@/Components/Dropdown.vue';
import { useConfirm } from "primevue/useconfirm";
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { router } from '@inertiajs/vue3';
import GroupTransactions from '@/Pages/Group/Partials/GroupTransactions.vue';
import { trans } from 'laravel-vue-i18n';

const props = defineProps({
    group: Object,
})

const visible = ref(false);
const dialogTitle = ref('');

const openDialog = (title) => {
    visible.value = true;
    dialogTitle.value = title;
}

const confirm = useConfirm();
const requireConfirmation = (id) => {
    confirm.require({
        group: 'headless-error',
        header: trans('public.delete_group_header'),
        message: trans('public.delete_group_caption'),
        cancelButton: trans('public.cancel'),
        acceptButton: trans('public.delete_confirm'),
        accept: () => {
            router.visit(route('group.delete', id), {method: 'delete'})
        },
    });
};
</script>

<template>
    <DropdownOverlay>
        <template #trigger>
            <Button
                variant="gray-text"
                size="sm"
                type="button"
                iconOnly
                pill
                v-slot="{ iconSizeClasses }"
            >
                <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
            </Button>
        </template>

        <template #content>
            <div class="py-1 flex flex-col items-center">
                <div 
                    class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer"
                    @click="openDialog('view_group_transactions')"
                >
                    <IconReportSearch size="20" stroke-width="1.25" color="#667085" />
                    <div class="text-gray-950 text-sm font-medium">
                        {{ $t('public.transaction') }}
                    </div>
                </div>

                <div 
                    class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer"
                    @click="openDialog('edit_group')"
                >
                    <IconPencilMinus size="20" stroke-width="1.25" color="#667085" />
                    <div class="text-gray-950 text-sm font-medium">
                        {{ $t('public.edit') }}
                    </div>
                </div>

                <div class="h-1 self-stretch bg-gray-200"></div>

                <div 
                    class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer"
                    @click="requireConfirmation(group.id)"
                >
                    <IconTrash size="20" stroke-width="1.25" color="#F04438" />
                    <div class="text-error-500 text-sm font-medium">
                        {{ $t('public.delete') }}
                    </div>
                </div>
            </div>
        </template>
    </DropdownOverlay>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${dialogTitle}`)"
        class="dialog-xs"
        :class="[
            {'sm:dialog-md': dialogTitle === 'edit_group'},
            {'sm:dialog-lg': dialogTitle === 'view_group_transactions'},
        ]"
    >
        <template v-if="dialogTitle === 'edit_group'">
            <EditGroup :group="group" @closeDialog = "visible = $event" />
        </template>

        <template v-if="dialogTitle === 'view_group_transactions'">
            <GroupTransactions />
        </template>
    </Dialog>
</template>