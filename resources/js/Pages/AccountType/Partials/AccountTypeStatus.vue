<script setup>
import InputSwitch from 'primevue/inputswitch';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { useConfirm } from "primevue/useconfirm";
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';

const props =defineProps({
    account_type: Object,
})

//axios get to load the data
const checked = props.account_type.status === 'active';
const header = ref()
const content = ref()
const btnContent = ref()
const variant = ref()

if (checked) {
    header.value = trans('public.deactivate_header');
    content.value = trans('public.deactivate_content');
    btnContent.value = trans('public.deactivate');
    variant.value = 'gray'
} else {
    header.value = trans('public.activate_header');
    content.value = trans('public.activate_content');
    btnContent.value = trans('public.confirm');
    variant.value = 'primary'
}

const confirm = useConfirm();
const requireConfirmation = () => {
    console.log(confirm)
    confirm.require({
        group: 'headless',
        header: header.value,
        message: content.value,
        acceptButton: btnContent.value,
        accept: () => {
            // route or function
            console.log('success');
        },
    });
};
</script>

<template>
    <div
        class="p-2.5 flex items-center hover:bg-gray-100 rounded-full"
        @click="requireConfirmation()"
    >
        <InputSwitch v-model="checked" />
    </div>

    <ConfirmationDialog :variant="variant" />
</template>