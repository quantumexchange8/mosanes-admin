<script setup>
import InputText from 'primevue/inputtext';
import Button from '@/Components/Button.vue';
import { CalendarIcon } from '@/Components/Icons/outline'
import { ref, onMounted, watch, watchEffect, computed } from "vue";
import {usePage} from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {FilterMatchMode} from "primevue/api";
import { transactionFormat } from '@/Composables/index.js';
import Empty from '@/Components/Empty.vue';
import Loader from "@/Components/Loader.vue";
import {IconSearch, IconCircleXFilled, IconAdjustments, IconX} from '@tabler/icons-vue';
import Calendar from 'primevue/calendar';

const { formatDate, formatDateTime, formatAmount } = transactionFormat();

const props = defineProps({
  selectedMonths: Array,
  selectedType: String,
});

watch(() => props.selectedMonths, () => {
    getResults(props.selectedType, props.selectedMonths, selectedDate.value);
});

const visible = ref(false);
const transactions = ref();
const dt = ref();
const loading = ref(false);
const totalTransaction = ref(0);
const totalTransactionAmount = ref(0);
const maxAmount = ref(0);

const getResults = async (type, selectedMonths = [], selectedDate = []) => {
    loading.value = true;

    try {
        let response;

        // Fetch data for payout type from a different endpoint or table
        let url = `/transaction/getTransactionListingData?type=${type}`;

        // Convert the array to a comma-separated string if not empty
        if (props.selectedMonths && props.selectedMonths.length > 0) {
            url += `&selectedMonths=${props.selectedMonths.join(',')}`;
        }

        // Add selectedDate parameter if type is 'payout'
        if (selectedDate) {
            const [startDate, endDate] = selectedDate;
            url += `&startDate=${startDate}&endDate=${endDate}`;
        }

        response = await axios.get(url);
        transactions.value = response.data.transactions;
        totalTransaction.value = transactions.value?.length;
        totalTransactionAmount.value = transactions.value.filter(item => ['success', 'approved'].includes(item.status)).reduce((acc, item) => acc + parseFloat(item.transaction_amount || 0), 0);
        maxAmount.value = transactions.value?.length ? Math.max(...transactions.value.map(item => parseFloat(item.transaction_amount || 0))) : 0;

    } catch (error) {
        console.error('Error fetching transactions:', error);
    } finally {
        loading.value = false;
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

// Get current date
const today = new Date();

// Define minDate and maxDate
const minDate = ref(new Date(today.getFullYear(), today.getMonth(), 1));
const maxDate = ref(today);

// Function to get the start of the month from a date string
const getStartOfMonth = (dateStr) => {
    const [month, year] = dateStr.split('/');
    return new Date(year, month - 1, 1);
};

// Reactive variable for selected date range
const selectedDate = ref([minDate.value, maxDate.value]);

// Clear date selection
const clearDate = () => {
    selectedDate.value = [];
};

watch( () => props.selectedMonths, (newValue) => {
    // Compute minDate
    let computedMinDate = new Date(today.getFullYear(), today.getMonth(), 1); // Default to start of current month

    if (newValue.length > 0) {
        // Convert selectedMonths to start-of-month dates
        const startDates = newValue.map(dateStr => getStartOfMonth(dateStr));

        // Find the earliest date
        const earliestDate = new Date(Math.min(...startDates.map(date => date.getTime())));

        computedMinDate = earliestDate;
    }

    minDate.value = computedMinDate;
    maxDate.value = today;

    // Update selectedDate
    selectedDate.value = [minDate.value, maxDate.value];

    // Clear date selection if no months are selected
    if (newValue.length === 0) {
        clearDate();
    } else {
        // Call function to get results
        getResults(props.selectedType, newValue, [minDate.value, maxDate.value]);
    }
  },
  { immediate: true } // Run immediately on initial value
);

// Watch for changes in selectedDate
watch(selectedDate, (newDateRange) => {
    // Implement logic to handle changes in the selected date range
    if (Array.isArray(newDateRange)) {
        const [startDate, endDate] = newDateRange;

        // Check if both dates are valid
        if (startDate && endDate) {
            // Both dates are valid, call function with the full range
            getResults(props.selectedType, props.selectedMonths, [startDate, endDate]);
        } else if (startDate || endDate) {
            // Only one date is provided
            // Use the same date for both start and end if one is null
            getResults(props.selectedType, props.selectedMonths, [startDate || endDate, endDate || startDate]);
        } else if (!(startDate && endDate)) {
            getResults(props.selectedType, props.selectedMonths);
        }
    } else {
        // Handle unexpected formats or types
        console.warn('Invalid date range format:', newDateRange);
    }
});

onMounted(() => {
    getResults(props.selectedType, props.selectedMonths, selectedDate.value);
})

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
});

const recalculateTotals = () => {
    const filtered = transactions.value.filter(transaction => {
        return (
            (!filters.value.name?.value || transaction.name.startsWith(filters.value.name.value))
        );
    });
    totalTransaction.value = filtered.length;
    totalTransactionAmount.value = filtered.filter(item => ['success', 'approved'].includes(item.status)).reduce((acc, item) => acc + parseFloat(item.transaction_amount || 0), 0);
    maxAmount.value = filtered?.length ? Math.max(...filtered.map(item => parseFloat(item.transaction_amount || 0))) : 0;
};

watch(filters, () => {
    recalculateTotals();
}, { deep: true });

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults(props.selectedType, props.selectedMonths, selectedDate.value);
    }
});

// dialog
const data = ref({});
const openDialog = (rowData) => {
    visible.value = true;
    data.value = rowData;
};

// Define emits
const emit = defineEmits(['update-totals']);

// Emit the totals whenever they change
watch([totalTransaction, totalTransactionAmount, maxAmount], () => {
    emit('update-totals', {
        totalTransaction: totalTransaction.value,
        totalTransactionAmount: totalTransactionAmount.value,
        maxAmount: maxAmount.value,
  });
});

</script>

<template>
    <DataTable
        v-model:filters="filters"
        :value="transactions"
        paginator
        removableSort
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50, 100]"
        tableStyle="md:min-width: 50rem"
        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
        :globalFilterFields="['name']"
        ref="dt"
        @row-click="(event) => openDialog(event.data)"
        :loading="loading"
        >
        <template #header>
            <div class="flex flex-col md:flex-row gap-3 items-center self-stretch">
                <div class="relative w-full md:w-60">
                    <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                        <IconSearch size="20" stroke-width="1.25" />
                    </div>
                    <InputText v-model="filters['global'].value" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full md:w-60" />
                    <div
                        v-if="filters['global'].value !== null"
                        class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                        @click="clearFilterGlobal"
                    >
                        <IconCircleXFilled size="16" />
                    </div>
                </div>
                <div class="w-full flex flex-col gap-3 md:flex-row">
                    <div class="relative w-full md:w-[272px]">
                        <Calendar
                            v-model="selectedDate"
                            selectionMode="range"
                            :manualInput="false"
                            :minDate="minDate"
                            :maxDate="maxDate"
                            dateFormat="dd/mm/yy"
                            showIcon
                            iconDisplay="input"
                            :placeholder="$t('public.date_placeholder')"
                            class="w-full md:w-[272px]"
                        />
                        <div
                            v-if="selectedDate && selectedDate.length > 0"
                            class="absolute top-2/4 -mt-2.5 right-4 text-gray-400 select-none cursor-pointer bg-white"
                            @click="clearDate"
                        >
                            <IconX size="20" />
                        </div>
                    </div>
                    <div class="w-full flex justify-end">
                        <Button
                            variant="primary-outlined"
                            @click="exportCSV($event)"
                            class="w-full md:w-auto"
                        >
                            {{ $t('public.export') }}
                        </Button>
                    </div>
                </div>
            </div>
        </template>
        <template #empty><Empty :title="$t('public.empty_payout_title')" :message="$t('public.empty_payout_message')"/></template>
        <template #loading>
            <div class="flex flex-col gap-2 items-center justify-center">
                <Loader />
                <span class="text-sm text-gray-700">{{ $t('public.loading_transactions_caption') }}</span>
            </div>
        </template>
        <Column
            field="created_at"
            sortable
            :header="$t('public.date')"
            class="hidden md:table-cell"
        >
            <template #body="slotProps">
                {{ formatDate(slotProps.data.created_at)}}
            </template>
        </Column>
        <Column
            field="name"
            sortable
            :header="$t('public.name')"
            class="hidden md:table-cell"
        >
            <template #body="slotProps">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                        <DefaultProfilePhoto />
                    </div>
                    <div class="flex flex-col items-start">
                        <div class="font-medium">
                            {{ slotProps.data.name }}
                        </div>
                        <div class="text-gray-500 text-xs">
                            {{ slotProps.data.email }}
                        </div>
                    </div>
                </div>
            </template>
        </Column>
        <Column
            field="volume"
            sortable
            :header="$t('public.volume_header')"
            class="hidden md:table-cell"
        >
            <template #body="slotProps">
                {{ formatAmount(slotProps.data.volume) }}
            </template>
        </Column>
        <Column
            field="payout"
            sortable
            :header="$t('public.payout_header')"
            class="hidden md:table-cell"
        >
            <template #body="slotProps">
                {{ formatAmount(slotProps.data.payout) }}
            </template>
        </Column>
        <Column class="md:hidden">
            <template #body="slotProps">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                            <DefaultProfilePhoto />
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="text-xs font-medium">
                                {{ slotProps.data.name }}
                            </div>
                            <div class="text-gray-500 text-xs">
                                {{ formatDateTime(slotProps.data.created_at) }}
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden text-right text-ellipsis font-semibold">
                        {{ formatAmount(slotProps.data.payout) }}
                    </div>
                </div>
            </template>
        </Column>
    </DataTable>

    <Dialog v-model:visible="visible" modal :header="$t('public.payout_details')" class="dialog-xs md:dialog-md">
        <div class="flex flex-col justify-center items-start pb-4 gap-3 self-stretch border-b border-gray-200 md:flex-row md:pt-4 md:justify-between">
            <!-- below md -->
            <span class="md:hidden self-stretch text-gray-950 text-xl font-semibold">{{ data.transaction_amount }}</span>
            <div class="flex items-center gap-3 self-stretch">
                <div class="w-9 h-9 rounded-full overflow-hidden grow-0 shrink-0">
                    <DefaultProfilePhoto />
                </div>
                <div class="flex flex-col items-start flex-grow">
                    <span class="self-stretch overflow-hidden text-gray-950 text-ellipsis text-sm font-medium">{{ data.name }}</span>
                    <span class="self-stretch overflow-hidden text-gray-500 text-ellipsis text-xs">{{ data.email }}</span>
                </div>
            </div>
            <!-- above md -->
            <span class="hidden md:block w-[180px] text-gray-950 text-right text-xl font-semibold">{{ data.transaction_amount }}</span>
        </div>

        <div class="flex justify-center items-center py-4 gap-3 self-stretch border-b border-gray-200 md:flex-col md:border-none">
            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-grow md:flex-row md:items-center md:self-stretch">
                <span class="self-stretch text-gray-500 text-xs font-medium md:w-[140px]">{{ $t('public.payout_date') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium md:flex-grow">2024/06/25</span>
            </div>
            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-grow md:flex-row md:items-center md:self-stretch">
                <span class="self-stretch text-gray-500 text-xs font-medium md:w-[140px]">{{ $t('public.total_trade_volume') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium md:flex-grow">22.92 Lot(s)</span>
            </div>
        </div>

        <div class="flex flex-col items-center pt-4 pb-4 gap-1 self-stretch md:pt-5 md:gap-0">
            <!-- below md -->
            <span class="md:hidden self-stretch text-gray-950 text-xs font-bold">{{ $t('public.products_traded') }}</span>
            <div class="md:hidden flex flex-col items-center self-stretch" v-for="(product, index) in products" :key="index">
                <div class="flex justify-between items-center py-2 self-stretch">
                    <div class="flex flex-col items-start flex-grow">
                    <span class="self-stretch overflow-hidden text-gray-950 text-ellipsis text-xs font-semibold">{{ product.name }}</span>
                    <div class="flex items-center gap-2 self-stretch">
                        <span class="text-gray-500 text-xs">{{ product.volume }} {{ $t('public.lots') }}</span>
                        <span class="text-gray-500 text-xs">•</span>
                        <span class="text-gray-500 text-xs">$ {{ formatAmount(product.amount) }}</span>
                    </div>
                    </div>
                    <span class="w-[100px] overflow-hidden text-gray-950 text-right text-ellipsis font-semibold"></span>
                </div>
            </div>
            <!-- above md -->
            <div class="hidden md:flex py-2 items-center self-stretch border-b border-gray-200 bg-gray-100 uppercase">
                <div class="flex items-center px-2 gap-2.5 flex-grow">
                    <span class="text-gray-950 text-xs font-semibold">
                        {{ $t('public.product') }}
                    </span>
                </div>
                <div class="flex items-center px-2 gap-2.5 flex-grow">
                    <span class="text-gray-950 text-xs font-semibold">
                        {{ $t('public.volume') }} (Ł)
                    </span>
                </div>
                <div class="flex items-center px-2 gap-2.5 flex-grow">
                    <span class="text-gray-950 text-xs font-semibold">
                        {{ $t('public.rebate') }} / Ł ($)
                    </span>
                </div>
                <div class="flex items-center px-2 gap-2.5 flex-grow">
                    <span class="text-gray-950 text-xs font-semibold">
                        {{ $t('public.total') }} ($)
                    </span>
                </div>
            </div>
            <div v-for="(product, index) in products" :key="index" class="hidden md:flex items-center py-3 self-stretch hover:bg-gray-50" :class="{'border-b border-gray-200': index !== products.length - 1}">
                <div class="flex items-center px-2 flex-grow">
                    <span class="flex-grow text-gray-950 text-sm">{{ product.name }}</span>
                </div>
                <div class="flex items-center px-2 flex-grow">
                    <span class="flex-grow text-gray-950 text-sm">{{ product.volume }}</span>
                </div>
                <div class="flex items-center px-2 flex-grow">
                    <span class="flex-grow text-gray-950 text-sm">{{ product.rebate }}</span>
                </div>
                <div class="flex items-center px-2 flex-grow">
                    <span class="flex-grow text-gray-950 text-sm">{{ formatAmount(product.amount) }}</span>
                </div>
            </div>
        </div>
    </Dialog>

</template>
