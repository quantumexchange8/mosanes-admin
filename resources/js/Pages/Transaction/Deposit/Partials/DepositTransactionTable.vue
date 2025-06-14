<script setup>
import InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import Button from '@/Components/Button.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { ref, onMounted, watch, watchEffect, computed } from "vue";
import {usePage} from '@inertiajs/vue3';
import OverlayPanel from 'primevue/overlaypanel';
import Dialog from 'primevue/dialog';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {FilterMatchMode} from "primevue/api";
import { transactionFormat, generalFormat } from '@/Composables/index.js';
import Empty from '@/Components/Empty.vue';
import Loader from "@/Components/Loader.vue";
import Badge from '@/Components/Badge.vue';
import {IconSearch, IconCircleXFilled, IconAdjustments, IconX} from '@tabler/icons-vue';
import Slider from 'primevue/slider';
import MultiSelect from 'primevue/multiselect';
import IconField from 'primevue/iconfield';
import { CalendarIcon } from '@/Components/Icons/outline'

const { formatDateTime, formatAmount } = transactionFormat();
const { formatRgbaColor } = generalFormat();

const props = defineProps({
  selectedType: String,
  copyToClipboard: Function,
  groups: Array,
});

const visible = ref(false);
const transactions = ref();
const dt = ref();
const loading = ref(false);
const totalTransaction = ref(null);
const totalTransactionAmount = ref(null);
const minFilterAmount = ref(0);
const maxFilterAmount = ref(0);
const maxAmount = ref(null);
const filteredValueCount = ref(0);
const months = ref([]);

// Function to get the current month and year as a string
const getCurrentMonthYear = () => {
  const date = new Date();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${month}/${year}`;
};

const selectedMonths = ref([getCurrentMonthYear()]);

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
    getResults(props.selectedType, selectedMonths.value);
});

// Update watch for selectedMonths
watch(selectedMonths, () => {
    getResults(props.selectedType, selectedMonths.value);
});

const getResults = async (type, selectedMonths = []) => {
    loading.value = true;
    type = 'deposit'
    
    try {
        let response;

        let url = `/transaction/getTransactionListingData?type=${type}`;

        // Convert the array to a comma-separated string if not empty
        if (selectedMonths && selectedMonths.length > 0) {
            const selectedMonthString = selectedMonths.join(',');
            url += `&selectedMonths=${selectedMonthString}`;
        }

        response = await axios.get(url);
        transactions.value = response.data.transactions;
        totalTransaction.value = transactions.value?.length;
        totalTransactionAmount.value = transactions.value.filter(item => ['successful'].includes(item.status)).reduce((acc, item) => acc + parseFloat(item.transaction_amount || 0), 0);
        maxAmount.value = transactions.value?.length ? Math.max(...transactions.value.map(item => parseFloat(item.transaction_amount || 0))) : 0;
        maxFilterAmount.value = maxAmount.value;
    } catch (error) {
        console.error('Error fetching transactions:', error);
    } finally {
        loading.value = false;
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    role: { value: null, matchMode: FilterMatchMode.EQUALS },
    group_name: { value: null, matchMode: FilterMatchMode.EQUALS },
    amount: { value: [minFilterAmount.value, maxFilterAmount.value], matchMode: FilterMatchMode.BETWEEN },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

// Watch minFilterAmount and maxFilterAmount to update the amount filter
watch([minFilterAmount, maxFilterAmount], ([newMin, newMax]) => {
    // Convert 0 to null for the amount filter
    filters.value.amount.value = [
        newMin === 0 ? null : newMin,
        newMax
    ];
});

// Watch the amount filter to update minFilterAmount
watch(() => filters.value.amount.value[0], (newMin) => {
    // Update minFilterAmount based on the first value of the amount filter
    filters.value.amount.value[0] = newMin === 0 ? null : newMin;
}, { immediate: true });

// overlay panel
const op = ref();
const filterCount = ref(0);

const toggle = (event) => {
    op.value.toggle(event);
}

const recalculateTotals = () => {
    const filtered = transactions.value.filter(transaction => {
        return (
            (!filters.value.name?.value || transaction.name.startsWith(filters.value.name.value)) &&
            (!filters.value.role?.value || transaction.role === filters.value.role.value) &&
            (!filters.value.group_name?.value || transaction.group_name === filters.value.group_name.value) &&
            (!filters.value.amount?.value[0] || !filters.value.amount?.value[1] || (transaction.transaction_amount >= filters.value.amount.value[0] && transaction.transaction_amount <= filters.value.amount.value[1])) &&
            (!filters.value.status?.value || transaction.status === filters.value.status.value)
        );
    });

    totalTransaction.value = filtered.length;
    totalTransactionAmount.value = filtered.filter(item => ['successful'].includes(item.status)).reduce((acc, item) => acc + parseFloat(item.transaction_amount || 0), 0);
    maxAmount.value = filtered.length ? Math.max(...filtered.map(item => parseFloat(item.transaction_amount || 0))) : 0;
};

watch(filters, () => {
    recalculateTotals();

    // Check if the amount filter is active by comparing against initial values
    const isAmountFilterActive = (filters.value.amount.value[0] !== null && filters.value.amount.value[0] !== minFilterAmount.value) ||
                                 (filters.value.amount.value[1] !== null && filters.value.amount.value[1] !== maxFilterAmount.value);

    // Count active filters
    filterCount.value = Object.entries(filters.value).reduce((count, [key, filter]) => {
        if (filter === filters.value.amount) {
            // The amount filter is considered active if it's not covering the full range
            return isAmountFilterActive ? count + 1 : count;
        }
        // Consider a filter active if its value is not null or empty
        return (filter.value !== null && filter.value !== '' && filter.value != []) ? count + 1 : count;
    }, 0);
}, { deep: true });

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
        role: { value: null, matchMode: FilterMatchMode.EQUALS },
        group_name: { value: null, matchMode: FilterMatchMode.EQUALS },
        amount: { value: [null, maxFilterAmount.value], matchMode: FilterMatchMode.BETWEEN },
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults(props.selectedType, selectedMonths.value);
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

const handleFilter = (e) => {
    filteredValueCount.value = e.filteredValue.length;
};

</script>

<template>
    <DataTable
        v-model:filters="filters"
        :value="transactions"
        :paginator="transactions?.length > 0 && filteredValueCount > 0"
        removableSort
        :rows="50"
        :rowsPerPageOptions="[10, 20, 50, 100]"
        tableStyle="md:min-width: 50rem"
        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
        :globalFilterFields="['name','email','to_meta_login','transaction_number']"
        ref="dt"
        selectionMode="single"
        @row-click="(event) => openDialog(event.data)"
        :loading="loading"
        @filter="handleFilter"
    >
        <template #header>
            <div class="flex flex-col md:flex-row gap-3 items-center self-stretch md:pb-6">
                <div class="flex flex-col gap-5 self-stretch md:flex-row md:justify-between md:items-center">
                    <IconField iconPosition="left" class="relative flex items-center w-full md:w-60">
                        <CalendarIcon class="z-10 w-5 h-5 text-gray-400" />
                        <MultiSelect 
                            v-model="selectedMonths" 
                            filter 
                            :options="months" 
                            :placeholder="$t('public.month_placeholder')" 
                            :maxSelectedLabels="1" 
                            :selectedItemsLabel="`${selectedMonths.length} ${$t('public.months_selected')}`" 
                            class="font-normal pl-12 w-full md:w-60"
                            >
                            <template #filtericon>{{ $t('public.select_all') }}</template>
                            <template #option="{ option }">
                                <span class="text-sm">
                                    <template v-if="option.startsWith('last_')">
                                        {{ $t(`public.${option}`) }}
                                    </template>
                                    <template v-else>
                                        {{ option }}
                                    </template>
                                </span>
                            </template>
                        </MultiSelect>
                    </IconField>
                </div>
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
                <div class="w-full grid grid-cols-2 gap-3">
                    <Button
                        variant="gray-outlined"
                        @click="toggle"
                        size="sm"
                        class="flex gap-3 items-center justify-center py-3 w-full md:w-[130px]"
                    >
                        <IconAdjustments size="20" color="#0C111D" stroke-width="1.25" />
                        <div class="text-sm text-gray-950 font-medium">
                            {{ $t('public.filter') }}
                        </div>
                        <Badge class="w-5 h-5 text-xs text-white" variant="numberbadge">
                            {{ filterCount }}
                        </Badge>
                    </Button>
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
        <template #empty><Empty :title="$t('public.empty_deposit_title')" :message="$t('public.empty_deposit_message')"/></template>
        <template #loading>
            <div class="flex flex-col gap-2 items-center justify-center">
                <Loader />
                <span class="text-sm text-gray-700">{{ $t('public.loading_transactions_caption') }}</span>
            </div>
        </template>
        <template v-if="transactions?.length > 0 && filteredValueCount > 0">
            <Column
                field="created_at"
                sortable
                :header="$t('public.date')"
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    <span v-if="slotProps.data.status === 'processing'">{{ formatDateTime(slotProps.data.created_at) }}</span>
                    <span v-else-if="slotProps.data.approved_at">{{ formatDateTime(slotProps.data.approved_at) }}</span>
                    <span v-else>-</span>
                </template>
            </Column>
            <Column
                field="id_number"
                sortable
                :header="$t('public.id')"
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    {{ slotProps.data.transaction_number }}
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
                            <template v-if="slotProps.data.profile_photo">
                                <img :src="slotProps.data.profile_photo" alt="profile_photo">
                            </template>
                            <template v-else>
                                <DefaultProfilePhoto />
                            </template>
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
                field="group_name" 
                :header="$t('public.group')" 
                style="width: 15%" 
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    <div class="flex items-center">
                        <div
                            v-if="slotProps.data.group_name"
                            class="flex justify-center items-center gap-2 rounded-sm py-1 px-2"
                            :style="{ backgroundColor: formatRgbaColor(slotProps.data.group_color, 1) }"
                        >
                            <div
                                class="text-white text-xs text-center"
                            >
                                {{ slotProps.data.group_name }}
                            </div>
                        </div>
                        <div v-else>
                            -
                        </div>
                    </div>
                </template>
            </Column>
            <Column
                field="to_meta_login"
                :header="$t('public.account')"
                class="hidden md:table-cell">
                <template #body="slotProps"
            >
                    {{ slotProps.data.to_meta_login }}
                </template>
            </Column>
            <Column
                field="transaction_amount"
                sortable
                :header="$t('public.amount') + '&nbsp;($)'"
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    {{ slotProps.data.transaction_amount ? formatAmount(slotProps.data.transaction_amount) : '-' }}
                </template>
            </Column>
            <Column
                field="status"
                :header="$t('public.status')"
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    <StatusBadge class="w-fit" :value="slotProps.data.status">
                        {{ $t('public.' + slotProps.data.status) }}
                    </StatusBadge>
                </template>
            </Column>
            <Column class="md:hidden">
                <template #body="slotProps">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                <template v-if="slotProps.data.profile_photo">
                                    <img :src="slotProps.data.profile_photo" alt="profile_photo">
                                </template>
                                <template v-else>
                                    <DefaultProfilePhoto />
                                </template>
                            </div>
                            <div class="flex flex-col items-start">
                                <div class="text-sm font-semibold">
                                    {{ slotProps.data.name }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    {{ formatDateTime(slotProps.data.created_at) }}
                                </div>
                            </div>
                        </div>
                        <div class="overflow-hidden text-right text-ellipsis font-semibold">
                        {{ slotProps.data.transaction_amount ?  '$&nbsp;' + formatAmount(slotProps.data.transaction_amount) : '-' }}
                        </div>
                    </div>
                </template>
            </Column>
        </template>
    </DataTable>

    <OverlayPanel ref="op">
        <div class="flex flex-col gap-5 w-60 py-5 px-4">
            <!-- Filter Role-->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 font-semibold">
                    {{ $t('public.filter_role') }}
                </div>
                <div class="flex flex-col gap-1 self-stretch">
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['role'].value" inputId="role_member" value="member" class="w-4 h-4" />
                        <label for="role_member">{{ $t('public.member') }}</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['role'].value" inputId="role_agent" value="agent" class="w-4 h-4" />
                        <label for="role_agent">{{ $t('public.agent') }}</label>
                    </div>
                </div>
            </div>

            <!-- Filter Group -->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 font-semibold">
                    {{ $t('public.filter_group') }}
                </div>
                <div class="flex flex-col gap-1 self-stretch">
                    <div v-for="group in groups" :key="group.id" class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton 
                            v-model="filters.group_name.value" 
                            :inputId="`group_${group.id}`" 
                            :value="group.name" 
                            class="w-4 h-4" 
                        />
                        <label :for="`group_${group.id}`" class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full overflow-hidden" :style="{ backgroundColor: `#${group.color}` }"></div>
                            <span>{{ group.name }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Filter Amount-->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 font-semibold">
                    {{ $t('public.filter_amount') }}
                </div>
                <div class="flex flex-col items-center gap-1 self-stretch">
                    <div class="h-4 self-stretch">
                        <Slider v-model="filters['amount'].value" :min="minFilterAmount" :max="maxFilterAmount" range />
                    </div>
                    <div class="flex justify-between items-center self-stretch">
                        <span class="text-gray-950 text-sm">${{ minFilterAmount }}</span>
                        <span class="text-gray-950 text-sm">${{ maxFilterAmount }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter Status-->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 font-semibold">
                    {{ $t('public.filter_status') }}
                </div>
                <div  class="flex flex-col gap-1 self-stretch">
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['status'].value" inputId="status_active" value="successful" class="w-4 h-4" />
                        <label for="status_active">{{ $t('public.successful') }}</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['status'].value" inputId="status_processing" value="processing" class="w-4 h-4" />
                        <label for="status_processing">{{ $t('public.processing') }}</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['status'].value" inputId="status_inactive" value="failed" class="w-4 h-4" />
                        <label for="status_inactive">{{ $t('public.failed') }}</label>
                    </div>
                </div>
            </div>

            <div class="flex w-full">
                <Button
                    type="button"
                    variant="primary-outlined"
                    class="flex justify-center w-full"
                    @click="clearFilter()"
                >
                    {{ $t('public.clear_all') }}
                </Button>
            </div>
        </div>
    </OverlayPanel>

    <Dialog v-model:visible="visible" modal :header="$t('public.deposit_details')" class="dialog-xs md:dialog-md" :dismissableMask="true">
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

        <div class="flex flex-col items-center py-4 gap-3 self-stretch border-b border-gray-200">
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.transaction_id') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium">{{ data.transaction_number }}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.requested_date') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium">{{ formatDateTime(data.created_at) }}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.approval_date') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium">{{ data.approved_at ? formatDateTime(data.approved_at) : '-'}}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.account') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium">{{ data.to_meta_login }}</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.status') }}</span>
                <StatusBadge :value="data.status">{{ $t('public.' + data.status) }}</StatusBadge>
            </div>
        </div>

        <div class="flex flex-col items-center py-4 gap-3 self-stretch border-b border-gray-200">
            <div v-if="data.status != 'failed'" class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.sent_address') }}</span>
                <div class="flex justify-center items-center self-stretch" @click="copyToClipboard(data.from_wallet_address)">
                    <span class="flex-grow overflow-hidden text-gray-950 text-ellipsis text-sm font-medium break-words">{{ data.from_wallet_address }}</span>
                </div>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.receiving_address') }}</span>
                <div class="flex justify-center items-center self-stretch" @click="copyToClipboard(data.to_wallet_address)">
                    <span class="flex-grow overflow-hidden text-gray-950 text-ellipsis text-sm font-medium break-words">{{ data.to_wallet_address }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center py-4 gap-3 self-stretch">
            <div class="flex flex-col md:flex-row items-start gap-1 self-stretch">
                <span class="self-stretch md:w-[140px] text-gray-500 text-xs">{{ $t('public.remarks') }}</span>
                <span class="self-stretch text-gray-950 text-sm font-medium">{{ data.remarks }}</span>
            </div>
        </div>

    </Dialog>

</template>
