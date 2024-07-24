<script setup>
import Button from '@/Components/Button.vue';
import { IconDots, IconAlertCircle } from '@tabler/icons-vue';
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
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

const hasData = ref(false);
const form = useForm({
    action: '',
    amount: '',
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
    form.type = dialogs.value.type
    form.post(route(dialogs.value.updateUrl), {
        onSuccess: () => {
            closeDialog();
        },
    });
};

const dialogTitle = computed(() => {
    return dialogs.value.type === 'balance' ? 'Current Account Balance' : 'Current Account Credit';
});

const currentAmount = computed(() => {
    return dialogs.value.data?.amount || '0.00';
});

const actionLabel = computed(() => {
    return dialogs.value.type === 'balance'
        ? { in: 'Deposit', out: 'Withdrawal' }
        : { in: 'Credit In', out: 'Credit Out' };
});

const items = ref([
    {
        id: '3003923',
        badgeVariant: 'info',
        badgeText: 'Demo',
        data: [
            { label: 'Balance', value: '$ 10,000.00' },
            { label: 'Credit', value: '$ 1,000.00' },
            { label: 'Equity', value: '$ 1,000.00' },
            { label: 'Since', value: '2024/06/23' }
        ]
    },
    {
        id: '8000381',
        badgeVariant: 'success',
        badgeText: 'Live',
        data: [
            { label: 'Balance', value: '$ 1,372.19' },
            { label: 'Credit', value: '$ 500.00' },
            { label: 'Equity', value: '$ 1,200.20' },
            { label: 'Since', value: '2024/03/19' }
        ]
    },
    {
        id: '8000618',
        badgeVariant: 'success',
        badgeText: 'Live',
        data: [
            { label: 'Balance', value: '$ 2,372.20' },
            { label: 'Credit', value: '$ 0.00' },
            { label: 'Equity', value: '$ 2,299.29' },
            { label: 'Since', value: '2024/02/24' }
        ]
    },
    {
        id: '8001827',
        badgeVariant: 'success',
        badgeText: 'Live',
        data: [
            { label: 'Balance', value: '$ 1,372.19' },
            { label: 'Credit', value: '$ 0.00' },
            { label: 'Equity', value: '$ 1,200.20' },
            { label: 'Since', value: '2024/01/01' }
        ]
    }
]);

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
        group: 'headless',
        header: 'Delete Trading Account',
        message: 'Are you sure you want to delete this trading account? This action cannot be undone.',
        acceptButton: 'Yes, delete it',
        accept: () => {
            form.post(route('member.accountDelete'));
        },
    });
};
</script>

<template>
    <div v-if="hasData">
        <Empty message="No Trading Account Yet" />
    </div>
    <div v-else class="grid md:grid-cols-2 gap-5">
        <div class="flex flex-col min-w-[300px] items-center px-5 py-4 gap-3 rounded-2xl bg-white shadow-toast" v-for="item in items" :key="item.id">
            <div class="flex justify-between items-center self-stretch">
                <div class="flex items-center gap-4">
                    <div class="text-gray-950 text-lg font-semibold">#{{ item.id }}</div>
                    <StatusBadge border-radius="20" :value="item.badgeText.toLowerCase()">{{ item.badgeText }}</StatusBadge>
                    <div class="text-error-500"><IconAlertCircle :size="20" stroke-width="1.25" /></div>
                </div>
                <Dropdown align="right">
                    <template #trigger>
                        <Button iconOnly variant="gray-text" type="button" class="inline-flex" size="sm">
                            <IconDots size="16" stroke-width="1.25" color="#667085" />                        
                        </Button>
                    </template>
                    <template #content>
                        <DropdownLink class="inline-flex items-center gap-3 cursor-pointer" @click.prevent="openDialog('balance', item)">
                            <BalanceAdjustmentIcon class="w-5 h-5 text-gray-500" />
                            Balance Adjustment
                        </DropdownLink>
                        <DropdownLink class="inline-flex items-center gap-3 cursor-pointer" @click.prevent="openDialog('credit', item)">
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
                <div class="flex min-w-[100px] items-center gap-1 flex-1" v-for="(data, index) in item.data" :key="index">
                    <div class="text-gray-500 text-xs">{{ data.label }}:</div>
                    <div class="text-gray-950 text-xs font-medium">{{ data.value }}</div>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="dialogs.visible" modal :header="dialogTitle" class="dialog-xs md:dialog-sm">
        <form @submit.prevent="updateDialogData">
            <div class="flex flex-col gap-5">
                <div class="flex flex-col justify-center items-center px-8 py-4 gap-2 self-stretch bg-gray-200">
                    <div class="text-gray-500 text-center text-xs font-medium">
                        #{{ dialogs.data?.id }} - {{ dialogTitle }}
                    </div>
                    <div class="text-gray-950 text-center text-xl font-semibold">
                        $ {{ currentAmount }}
                    </div>
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" value="Action" />
                    <div class="flex items-center gap-10">
                        <div class="flex items-center gap-2">
                            <div class="flex w-10 h-10 p-2.5 justify-center items-center rounded-[50px]">
                                <RadioButton v-model="form.action" :inputId="actionLabel.in.toLowerCase()" :name="actionLabel.in" :value="actionLabel.in.toLowerCase()" />
                            </div>
                            <div class="text-gray-950 text-sm">
                                {{ actionLabel.in }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex w-10 h-10 p-2.5 justify-center items-center rounded-[50px]">
                                <RadioButton v-model="form.action" :inputId="actionLabel.out.toLowerCase()" :name="actionLabel.out" :value="actionLabel.out.toLowerCase()" />
                            </div>
                            <div class="text-gray-950 text-sm">
                                {{ actionLabel.out }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="amount" value="Amount" />
                    <IconField iconPosition="left" class="w-full">
                        <div class="text-gray-950 text-sm">$</div>
                        <InputText 
                            id="amount"
                            type="number"
                            class="block w-full"
                            v-model="form.amount"
                            placeholder="Enter amount"
                            :invalid="form.errors.amount"
                        />
                    </IconField>
                    <InputError :message="form.errors.amount" />
                </div>
                <div class="flex flex-col h-[150px] items-start gap-3 self-stretch">
                    <InputLabel for="remarks">Remarks (optional)</InputLabel>
                    <div class="flex items-center content-center gap-2 self-stretch flex-wrap">
                        <div v-for="(chip, index) in chips" :key="index">
                            <Chip
                                :label="chip.label"
                                :class="{
                                    'border-primary-300 bg-primary-50 hover:bg-primary-50 text-primary-500': form.remarks === chip.label,
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
                </div>
            </div>
            <div class="flex justify-end items-center pt-10 md:pt-7 gap-3 md:gap-4 self-stretch">
                <Button variant="gray-tonal" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="closeDialog">Cancel</Button>
                <Button variant="primary-flat" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="updateDialogData">Confirm</Button>
            </div>
        </form>
    </Dialog>

    <ConfirmationDialog variant="error" />
</template>
