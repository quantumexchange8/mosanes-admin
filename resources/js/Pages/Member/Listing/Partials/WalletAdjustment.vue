<script setup>
import { ref } from "vue";
import { CreditCardEdit01Icon } from '@/Components/Icons/outline';
import Button from '@/Components/Button.vue';
import Dialog from 'primevue/dialog';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import RadioButton from 'primevue/radiobutton';
import Chip from 'primevue/chip';
import Textarea from 'primevue/textarea';
import { useForm } from '@inertiajs/vue3';
import { transactionFormat } from "@/Composables/index.js";

const props = defineProps({
    type: String,
    cashWallet: {
        type: Object,
        default: null,
    },
    rebateWallet: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    action: '',
    amount: '',
    remarks: '',
    walletType: props.type,
});

const { formatAmount } = transactionFormat();

const dialogs = ref({
    visible: false,
});

const openDialog = () => {
    form.reset();
    dialogs.value.visible = true;
};

const closeDialog = () => {
    dialogs.value.visible = false;
    form.reset();
};

const updateDialogData = () => {
    form.post(route('member.walletAdjustment'), {
        onSuccess: () => {
            closeDialog();
        },
    });
};

const chips = ref([
    { label: 'Fix account balance' },
    { label: '修改帳戶餘額' },
    { label: 'Fix withdrawal balance' },
    { label: '修改提款數額' },
]);

const handleChipClick = (label) => {
    form.remarks = label;
};
</script>

<template>
    <Button iconOnly size="sm" variant="gray-outlined" pill @click.prevent="openDialog">
        <CreditCardEdit01Icon class="w-4 h-4 text-gray-950" />
    </Button>

    <Dialog v-model:visible="dialogs.visible" modal header="Wallet Adjustment" :class="props.type === 'cash' ? 'dialog-xs md:dialog-sm' : 'dialog-xs md:dialog-sm bg-purple'">
        <form @submit.prevent="updateDialogData">
            <div class="flex flex-col gap-5">
                <div :class="props.type === 'cash' ? 'bg-primary-500' : 'bg-purple'" class="flex flex-col justify-center items-center px-8 py-4 gap-2 self-stretch">
                    <div class="text-gray-100 text-center text-xs font-medium">{{ props.type === 'cash' ? 'Cash Wallet Balance' : 'Rebate Wallet Balance' }}</div>
                    <div class="text-white text-center text-xl font-semibold">$ {{ formatAmount(props.type === 'cash' ? props.cashWallet.amount : props.rebateWallet.amount) }}</div>
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" value="Action" />
                    <div class="flex items-center gap-10">
                        <div class="flex items-center gap-2">
                            <div class="flex w-10 h-10 p-2.5 justify-center items-center rounded-[50px]">
                                <RadioButton v-model="form.action" inputId="deposit" name="Deposit" value="deposit" />
                            </div>
                            <div class="text-gray-950 text-sm">Deposit</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex w-10 h-10 p-2.5 justify-center items-center rounded-[50px]">
                                <RadioButton v-model="form.action" inputId="withdrawal" name="Withdrawal" value="withdrawal" />
                            </div>
                            <div class="text-gray-950 text-sm">Withdrawal</div>
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
                <div v-if="props.type === 'cash'" class="flex flex-col h-[188px] items-start gap-3 self-stretch">
                    <InputLabel for="remarks">Remarks (optional)</InputLabel>
                    <div class="flex items-center content-center gap-2 self-stretch flex-wrap">
                        <div v-for="(chip, index) in chips" :key="index">
                            <Chip
                                :label="chip.label"
                                :class="{
                                    'border-primary-300 bg-primary-50 hover:bg-primary-50 text-primary-500': form.remarks === chip.label,
                                    // 'text-gray-400': form.processing
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
                        placeholder="Wallet balance adjustment :)"
                        :invalid="form.errors.remarks"
                        rows="5" 
                        cols="30" 
                    />
                </div>
                <div v-else class="flex flex-col h-[100px] items-start gap-1 self-stretch">
                    <InputLabel for="remarks">Remarks (optional)</InputLabel>
                    <Textarea 
                        id="remarks"
                        type="text"
                        class="flex flex-1 self-stretch"
                        v-model="form.remarks"
                        autofocus
                        placeholder="Wallet balance adjustment :)"
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
</template>