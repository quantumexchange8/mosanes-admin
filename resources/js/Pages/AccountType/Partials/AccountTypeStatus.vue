<script setup>
import InputSwitch from 'primevue/inputswitch';
import { useConfirm } from "primevue/useconfirm";
import { trans } from 'laravel-vue-i18n';
import { onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    accountTypeId: Number,
})

const account_type = ref();
const disabling = ref(true);
const checked = ref();
const group = ref()
const header = ref()
const content = ref()
const btnContent = ref()
const confirm = useConfirm();

const getData = async () => {
    try {
        disabling.value = true;
        const response_account = await axios.get(`/account_type/findAccountType/${props.accountTypeId}`);
        account_type.value = response_account.data.account_type;
        checked.value = account_type.value.status === 'active';

    } catch (error) {
        console.error('Error getting data:', error);
    } finally {
        disabling.value = false;
    }
}

watch((checked), (newValue) => {
    if (newValue) {
        group.value = 'headless-gray'
        header.value = trans('public.deactivate_header');
        content.value = trans('public.deactivate_content');
        btnContent.value = trans('public.deactivate');
    } else {
        group.value = 'headless-primary'
        header.value = trans('public.activate_header');
        content.value = trans('public.activate_content');
        btnContent.value = trans('public.confirm');
    }
})

const requireConfirmation = () => {
    confirm.require({
        group: group.value,
        header: header.value,
        message: content.value,
        acceptButton: btnContent.value,
        accept: () => {
            router.visit(route('accountType.updateStatus', props.accountTypeId), {method: 'patch'})
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
            readonly
            @click="requireConfirmation()"
            :disabled="disabling"
        />
    </div>
</template>