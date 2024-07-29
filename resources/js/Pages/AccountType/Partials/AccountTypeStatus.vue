<script setup>
import InputSwitch from 'primevue/inputswitch';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { useConfirm } from "primevue/useconfirm";
import { trans } from 'laravel-vue-i18n';
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    accountTypeId: Number,
})

const account_type = ref();
const disabling = ref(true);
const checked = ref();
const getData = async () => {
    try {
        disabling.value = true;
        const response_account = await axios.get(`/account_type/findAccountType/${props.accountTypeId}`);
        account_type.value = response_account.data.account_type;
        checked.value = account_type.value.status === 'active';

    } catch (error) {
        console.error('Error getting leverages:', error);
    } finally {
        disabling.value = false;
    }
}

const header = ref()
const content = ref()
const btnContent = ref()
const variant = ref()

watch((checked), (newValue) => {
    console.log(newValue);
    if (newValue) {
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
    console.log(variant.value);
})

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

onMounted(() => {
    getData()
})

watch(() => props.accountTypeId, () => {
    getData()
})
</script>

<template>
    <div
        class="p-2.5 flex items-center hover:bg-gray-100 rounded-full"
    >
        <InputSwitch
            v-model="checked"
            @click="requireConfirmation()"
            :disabled="disabling"
        />
    </div>

    <ConfirmationDialog :variant="variant" />
</template>