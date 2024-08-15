<script setup>
import Button from '@/Components/Button.vue';
import { IconDots, IconAlertCircleFilled } from '@tabler/icons-vue';
import {
    BalanceAdjustmentIcon,
    CreditAdjustmentIcon,
    DeleteIcon,
} from '@/Components/Icons/outline';
import { ref, computed } from 'vue';
import Dialog from 'primevue/dialog';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import RadioButton from 'primevue/radiobutton';
import Chip from 'primevue/chip';
import Textarea from 'primevue/textarea';
import { useForm } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Empty from '@/Components/Empty.vue';
import { useConfirm } from 'primevue/useconfirm';
import InputNumber from 'primevue/inputnumber';
import { trans } from "laravel-vue-i18n";
import { generalFormat, transactionFormat } from "@/Composables/index.js";

const props = defineProps({
    user_id: Number
})

const { formatAmount } = transactionFormat();
const { formatRgbaColor } = generalFormat()

const tradingAccounts = ref();
const form = useForm({
    id: '',
    action: '',
    amount: 0,
    remarks: '',
    type: '',
});

const dialogs = ref({
    visible: false,
    type: '', // 'balance' or 'credit'
    data: null,
    updateUrl: 'member.accountAdjustment', // Same URL for both types
});

const openDialog = (type, data = null) => {
    form.reset();
    dialogs.value.visible = true;
    dialogs.value.type = type;
    dialogs.value.data = data;
};

const closeDialog = () => {
    dialogs.value.visible = false;
    form.reset();
};

const updateDialogData = () => {
    form.id = dialogs.value.data.id;
    form.type = dialogs.value.type;
    form.post(route(dialogs.value.updateUrl), {
        onSuccess: () => {
            closeDialog();
        },
    });
};

const actionLabel = computed(() => {
    return dialogs.value.type === 'balance'
        ? { in: 'balance_in', out: 'balance_out' }
        : { in: 'credit_in', out: 'credit_out' };
});

const getTradingAccounts = async () => {
    try {
        const response = await axios.get(`/member/getTradingAccounts?id=${props.user_id}`);

        tradingAccounts.value = response.data.tradingAccounts;
        // console.log(tradingAccounts);
    } catch (error) {
        console.error('Error get trading accounts:', error);
    }
};
getTradingAccounts();

const chips = ref([
    { label: 'Fix account balance' },
    { label: '修改帳戶餘額' },
]);

const handleChipClick = (label) => {
    form.remarks = label;
};

const confirm = useConfirm();
const requireConfirmation = () => {
    confirm.require({
        group: 'headless-error',
        header: trans('public.delete_trading_account_header'),
        message: trans('public.delete_trading_account_message'),
        cancelButton: trans('public.cancel'),
        acceptButton: trans('public.delete_confirm'),
        accept: () => {
            form.post(route('member.accountDelete'));
        },
    });
};

// Function to check if an account is inactive for 90 days
function isInactive(date) {
  const updatedAtDate = new Date(date);
  const currentDate = new Date();

  // Get only the date part (remove time)
  const updatedAtDay = new Date(updatedAtDate.getFullYear(), updatedAtDate.getMonth(), updatedAtDate.getDate());
  const currentDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());

  // Calculate the difference in days by direct subtraction
  const diffDays = (currentDay - updatedAtDay) / (1000 * 60 * 60 * 24);

  // Determine if inactive (more than 90 days)
  return diffDays > 90;
}

</script>

<template>
    <div v-if="tradingAccounts?.length < 0">
        <Empty message="No Trading Account Yet" />
    </div>
    <div v-else class="grid md:grid-cols-2 gap-5">
        <div 
            v-for="tradingAccount in tradingAccounts" :key="tradingAccount.id"
            class="flex flex-col min-w-[300px] items-center px-5 py-4 gap-3 rounded-2xl border-l-8 bg-white shadow-toast"
            :style="{'borderColor': `#${tradingAccount.account_type_color}`}"
        >
            <div class="flex justify-between items-center self-stretch">
                <div class="flex items-center gap-4">
                    <div class="text-gray-950 text-lg font-semibold">#{{ tradingAccount.meta_login }}</div>
                    <div
                        class="flex px-2 py-1 justify-center items-center text-xs font-semibold hover:-translate-y-1 transition-all duration-300 ease-in-out rounded"
                        :style="{
                            backgroundColor: formatRgbaColor(tradingAccount.account_type_color, 0.15),
                            color: `#${tradingAccount.account_type_color}`,
                        }"
                    >
                        {{ tradingAccount.account_type_name }}
                    </div>
                    <div v-if="isInactive(tradingAccount.updated_at)" class="text-error-500">
                        <IconAlertCircleFilled :size="20" stroke-width="1.25" />
                    </div>
                </div>
                <Dropdown align="right">
                    <template #trigger>
                        <Button iconOnly variant="gray-text" type="button" class="inline-flex" size="sm">
                            <IconDots size="16" stroke-width="1.25" color="#667085" />
                        </Button>
                    </template>
                    <template #content>
                        <DropdownLink class="inline-flex items-center gap-3 cursor-pointer" @click.prevent="openDialog('balance', tradingAccount)">
                            <BalanceAdjustmentIcon class="w-5 h-5 text-gray-500" />
                            Balance Adjustment
                        </DropdownLink>
                        <DropdownLink class="inline-flex items-center gap-3 cursor-pointer" @click.prevent="openDialog('credit', tradingAccount)">
                            <CreditAdjustmentIcon class="w-5 h-5 text-gray-500" />
                            Credit Adjustment
                        </DropdownLink>
                        <div class="h-1 self-stretch bg-gray-200"></div>
                        <DropdownLink class="inline-flex items-center gap-3 cursor-pointer" @click.prevent="requireConfirmation()">
                            <DeleteIcon class="w-5 h-5 text-error-500" />
                            Delete Account
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
            <div class="grid grid-cols-2 gap-2 self-stretch">
                <div class="flex min-w-[100px] items-center gap-1 flex-1">
                    <div class="text-gray-500 text-xs">{{ $t('public.balance') }}:</div>
                    <div class="text-gray-950 text-xs font-medium">{{ formatAmount(tradingAccount.balance) }}</div>
                </div>
                <div class="flex min-w-[100px] items-center gap-1 flex-1">
                    <div class="text-gray-500 text-xs">{{ $t('public.equity') }}:</div>
                    <div class="text-gray-950 text-xs font-medium">{{ formatAmount(tradingAccount.credit) }}</div>
                </div>
                <div class="flex min-w-[100px] items-center gap-1 flex-1">
                    <div class="text-gray-500 text-xs">{{ tradingAccount.account_type == 'premium_account' ? $t('public.pamm') : $t('public.credit') }}:</div>
                    <div class="text-gray-950 text-xs font-medium">{{ tradingAccount.account_type == 'premium_account' ? 'Pamm not put yet' : formatAmount(tradingAccount.equity) }}</div>
                </div>
                <div class="flex min-w-[100px] items-center gap-1 flex-1">
                    <div class="text-gray-500 text-xs">{{ tradingAccount.account_type == 'premium_account' ? $t('public.mature_in') : $t('public.leverage') }}:</div>
                    <div class="text-gray-950 text-xs font-medium">{{ tradingAccount.account_type == 'premium_account' ? 'So this also not' : `1:${tradingAccount.leverage}` }}</div>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="dialogs.visible" modal :header="dialogs.type == 'balance' ? $t('public.account_balance_adjustment') : $t('public.account_credit_adjustment')" class="dialog-xs md:dialog-sm">
        <form @submit.prevent="updateDialogData">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col justify-center items-center px-8 py-4 gap-2 self-stretch bg-gray-200">
                    <div class="text-gray-500 text-center text-xs font-medium">
                        #{{ dialogs.data?.meta_login }} - {{ dialogs.type == 'balance' ? $t('public.current_account_balance') : $t('public.current_account_credit') }}
                    </div>
                    <div class="text-gray-950 text-center text-xl font-semibold">
                        $ {{ dialogs.type == 'balance' ? dialogs.data?.balance : dialogs.data?.credit }}
                    </div>
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" value="Action" />
                    <div class="flex items-center gap-10">
                        <div class="flex items-center gap-2">
                            <div class="flex w-10 h-10 p-2.5 justify-center items-center rounded-[50px]">
                                <RadioButton v-model="form.action" :inputId="actionLabel.in" :name="actionLabel.in" :value="actionLabel.in" class="w-4 h-4" />
                            </div>
                            <div class="text-gray-950 text-sm">
                                {{ $t('public.' + actionLabel.in) }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex w-10 h-10 p-2.5 justify-center items-center rounded-[50px]">
                                <RadioButton v-model="form.action" :inputId="actionLabel.out" :name="actionLabel.out" :value="actionLabel.out" class="w-4 h-4" />
                            </div>
                            <div class="text-gray-950 text-sm">
                                {{ $t('public.' + actionLabel.out) }}
                            </div>
                        </div>
                    </div>
                    <InputError :message="form.errors.action" />
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="amount" value="Amount" />
                    <InputNumber
                        v-model="form.amount"
                        inputId="currency-us"
                        prefix="$ "
                        class="w-full"
                        inputClass="py-3 px-4"
                        :min="0"
                        :step="100"
                        :minFractionDigits="2"
                        fluid
                        autofocus
                        :invalid="!!form.errors.amount"
                    />
                    <InputError :message="form.errors.amount" />
                </div>
                <div class="flex flex-col h-[150px] items-start gap-3 self-stretch">
                    <InputLabel for="remarks">Remarks (optional)</InputLabel>
                    <div class="flex items-center content-center gap-2 self-stretch flex-wrap">
                        <div v-for="(chip, index) in chips" :key="index">
                            <Chip
                                :label="chip.label"
                                class="hover:bg-gray-50"
                                :class="{
                                    'border-primary-300 bg-primary-50 hover:bg-primary-25 text-primary-500': form.remarks === chip.label,
                                    'text-gray-950': form.remarks !== chip.label,
                                }"
                                @click="handleChipClick(chip.label)"
                            />
                        </div>
                    </div>
                    <Textarea
                        id="remarks"
                        type="text"
                        class="flex flex-1 self-stretch"
                        v-model="form.remarks"
                        autofocus
                        placeholder="Account balance adjustment :)"
                        :invalid="form.errors.remarks"
                        rows="5"
                        cols="30"
                    />
                    <InputError :message="form.errors.remarks" />
                </div>
            </div>
            <div class="flex justify-end items-center pt-10 md:pt-7 gap-3 md:gap-4 self-stretch">
                <Button variant="gray-tonal" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="closeDialog">Cancel</Button>
                <Button variant="primary-flat" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="updateDialogData">Confirm</Button>
            </div>
        </form>
    </Dialog>
</template>
