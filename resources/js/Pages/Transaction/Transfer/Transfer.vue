<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { CalendarIcon } from '@/Components/Icons/outline'
import { HandIcon, CoinsIcon, RocketIcon } from '@/Components/Icons/solid';
import { ref, computed, onMounted } from "vue";
import MultiSelect from 'primevue/multiselect';
import IconField from 'primevue/iconfield';
import Vue3Autocounter from 'vue3-autocounter';
import TransferTransactionTable from "@/Pages/Transaction/Transfer/Partials/TransferTransactionTable.vue";

const totalTransaction = ref(999);
const totalTransactionAmount = ref(999);
const maxAmount = ref(999);
const counterDuration = ref(10);
const months = ref([]);
const selectedType = ref('transfer');

const getTransactionMonths = async () => {
    try {
        const monthsResponse = await axios.get('/transaction/getTransactionMonths');
        months.value = monthsResponse.data;
    } catch (error) {
        console.error('Error transaction months:', error);
    }
};

onMounted(() => {
    getTransactionMonths();
});

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

// Function to get the current month and year as a string
const getCurrentMonthYear = () => {
  const date = new Date();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${month}/${year}`;
};

// Reactive variables
const selectedMonths = ref([getCurrentMonthYear()]);

const handleUpdateTotals = (data) => {
  totalTransaction.value = data.totalTransaction;
  totalTransactionAmount.value = data.totalTransactionAmount;
  maxAmount.value = data.maxAmount;
  counterDuration.value = 1;
};

</script>

<template>
    <AuthenticatedLayout :title="$t('public.transfer')">
        <div class="flex flex-col gap-5 md:gap-8">
            <div class="flex flex-col gap-5 self-stretch md:flex-row md:justify-between md:items-center">
                <div> </div>
                <IconField iconPosition="left" class="relative flex items-center w-full md:w-60">
                    <CalendarIcon class="z-20 w-5 h-5 text-gray-400" />
                    <MultiSelect v-model="selectedMonths" filter :options="months" :placeholder="$t('public.month_placeholder')" :maxSelectedLabels="1" :selectedItemsLabel="`${selectedMonths.length} ${$t('public.months_selected')}`" class="w-full md:w-60">
                        <template #filtericon>{{ $t('public.select_all') }}</template>
                    </MultiSelect>
                </IconField>
            </div>
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
                <TransferTransactionTable :selectedMonths="selectedMonths" :selectedType="selectedType" @update-totals="handleUpdateTotals" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>