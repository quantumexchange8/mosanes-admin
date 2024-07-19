<script setup>
import { ref } from 'vue';
import { IconDotsVertical, IconReportSearch, IconPencilMinus, IconTrash } from '@tabler/icons-vue';
import Button from '@/Components/Button.vue';
import Dialog from 'primevue/dialog';
import EditGroup from '@/Pages/Group/Partials/EditGroup.vue';
import DropdownOverlay from '@/Components/Dropdown.vue';
import { useConfirm } from "primevue/useconfirm";
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    group: Object,
    indexNum: Number,
})

const visible = ref(false);
const dialogTitle = ref('');

const openDialog = (title) => {
    visible.value = true;
    dialogTitle.value = title;
}

const confirm = useConfirm();
const requireConfirmation = (name) => {
    console.log(confirm)
    confirm.require({
        group: 'headless',
        header: 'Are you sure?'+name,
        message: 'Are you sure you want to delete this member? \n' +
            'This action cannot be undone.',
        acceptButton: 'Yes, delete it',
        accept: () => {
            // route or function
            router.visit(route('group.delete', 1), {method: 'delete'})
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
                        Transactions
                    </div>
                </div>

                <div 
                    class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer"
                    @click="openDialog('edit_group')"
                >
                    <IconPencilMinus size="20" stroke-width="1.25" color="#667085" />
                    <div class="text-gray-950 text-sm font-medium">
                        Edit
                    </div>
                </div>

                <div class="h-1 self-stretch bg-gray-200"></div>

                <div 
                    class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer"
                    @click="requireConfirmation(group.groupName)"
                >
                    <IconTrash size="20" stroke-width="1.25" color="#F04438" />
                    <div class="text-error-500 text-sm font-medium">
                        Delete
                    </div>
                </div>
            </div>
        </template>
    </DropdownOverlay>

    <Dialog
        v-model:visible="visible"
        modal
        :header="dialogTitle"
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
            test
        </template>
    </Dialog>

    <ConfirmationDialog variant="error" />
</template>