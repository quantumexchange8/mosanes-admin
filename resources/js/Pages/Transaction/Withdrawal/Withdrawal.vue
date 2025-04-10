<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { HandIcon, CoinsIcon, RocketIcon } from '@/Components/Icons/solid';
import { ref, computed} from "vue";
import Vue3Autocounter from 'vue3-autocounter';
import WithdrawalTransactionTable from "@/Pages/Transaction/Withdrawal/Partials/WithdrawalTransactionTable.vue";

const totalTransaction = ref(999);
const totalTransactionAmount = ref(999);
const maxAmount = ref(999);
const counterDuration = ref(10);
const months = ref([]);
const selectedType = ref('withdrawal');

const props = defineProps({
  groups: Array,
});

const groups = computed(() => props.groups);

// data overview
const dataOverviews = computed(() => [
    {
        icon: HandIcon,
        total: totalTransaction.value,
        label: 'total_transaction',
    },
    {
        icon: CoinsIcon,
        total: totalTransactionAmount.value,
        label: 'total_approved_amount',
    },
    {
        icon: RocketIcon,
        total: maxAmount.value,
        label: 'maximum_amount',
    },
]);

function copyToClipboard(text) {
    const textToCopy = text;

    const textArea = document.createElement('textarea');
    document.body.appendChild(textArea);

    textArea.value = textToCopy;
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            console.log('Copied to clipboard:', textToCopy);
        } else {
            console.error('Unable to copy to clipboard.');
        }
    } catch (err) {
        console.error('Copy to clipboard failed:', err);
    }

    document.body.removeChild(textArea);
}

const handleUpdateTotals = (data) => {
  totalTransaction.value = data.totalTransaction;
  totalTransactionAmount.value = data.totalTransactionAmount;
  maxAmount.value = data.maxAmount;
  counterDuration.value = 1;
};

</script>

<template>
    <AuthenticatedLayout :title="$t('public.withdrawal')">
        <div class="flex flex-col gap-5 md:gap-8">
            <div class="grid grid-cols-1 md:grid-cols-3 w-full gap-5">
                <div
                    v-for="(item, index) in dataOverviews"
                    :key="index"
                    class="flex justify-center items-center py-4 px-6 gap-5 self-stretch rounded-2xl bg-white shadow-toast md:flex-col md:flex-grow md:py-6 md:gap-3"
                >
                    <component :is="item.icon" class="w-12 h-12 grow-0 shrink-0" />
                    <div class="flex flex-col items-center gap-1 flex-grow md:flex-grow-0 md:self-stretch">
                        <div class="self-stretch text-gray-950 text-lg font-semibold md:text-xl md:text-center">
                            <vue3-autocounter ref="counter" :startAmount="0" :endAmount="item.total" :duration="counterDuration" separator="," decimalSeparator="." :autoinit="true" />
                        </div>
                        <span class="self-stretch text-gray-500 text-xs md:text-sm md:text-center">{{ $t('public.' + item.label) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-center py-6 px-4 gap-5 self-stretch rounded-2xl border border-gray-200 bg-white shadow-table md:py-6 md:gap-6">
                <WithdrawalTransactionTable :selectedType="selectedType" :groups="groups" @update-totals="handleUpdateTotals" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>