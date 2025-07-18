<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {h, ref, watchEffect} from "vue";
import BillboardProfile from "@/Pages/Billboard/BillboardProfile/BillboardProfile.vue";
import BonusWithdrawal from "@/Pages/Billboard/BonusWithdrawal/BonusWithdrawal.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {IconCircleXFilled, IconSearch} from "@tabler/icons-vue";
import Loader from "@/Components/Loader.vue";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Button from "@/Components/Button.vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {wTrans} from "laravel-vue-i18n";
import {FilterMatchMode} from "primevue/api";
import dayjs from 'dayjs'
import {transactionFormat} from "@/Composables/index.js";
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import Dialog from "primevue/dialog";
import InputLabel from "@/Components/InputLabel.vue";
import Chip from "primevue/chip";
import Textarea from "primevue/textarea";
import Empty from "@/Components/Empty.vue";

const props = defineProps({
    profileCount: Number
})

const tabs = ref([
    {
        title: 'profile',
        component: h(BillboardProfile, {
            profileCount: props.profileCount
        }),
        rowCount: null
    },
    {
        title: 'bonus_withdrawal',
        component: h(BonusWithdrawal),
        rowCount: 0
    },
]);

const activeIndex = ref(tabs.value.findIndex(tab => tab.title === 'profile'));

const updateType = (event) => {
    activeIndex.value = event.index;
}

const getPendingCounts = async () => {
    try {
        const response = await axios.get('/getPendingCounts');
        tabs.value[1].rowCount = response.data.pendingBonusWithdrawal
    } catch (error) {
        console.error('Error pending counts:', error);
    }
};

getPendingCounts();

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getPendingCounts();
    }
});

const loading = ref(false);
const dt = ref();
const pendingWithdrawals = ref();
const paginator_caption = wTrans('public.paginator_caption');
const {formatAmount, formatDateTime} = transactionFormat();
const totalAmount = ref();
const filteredValueCount = ref(0);

const getResults = async () => {
    loading.value = true;

    try {
        const response = await axios.get('/billboard/getBonusWithdrawalData');
        pendingWithdrawals.value = response.data.pendingWithdrawals;
        totalAmount.value = response.data.totalAmount;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        loading.value = false;
    }
};

getResults();

const exportCSV = () => {
    dt.value.exportCSV();
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    user_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    user_email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    from: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults();
    }
});

const visible = ref(false);
const pendingData = ref();
const approvalAction = ref('');

const rowClicked = (data) => {
    pendingData.value = data;
    visible.value = true;
}

const handleApproval = (action) => {
    approvalAction.value = action;
}

const closeDialog = () => {
    visible.value = false;
    approvalAction.value = '';
}

const chips = ref({
    approve: [
        { label: 'Withdrawal successful' },
        { label: '您已成功提款' },
    ],
    reject: [
        { label: 'Withdrawal rejected' },
        { label: '提款已被拒絕' },
    ]
});

const handleChipClick = (label) => {
    form.remarks = label;
};

const form = useForm({
    id: '',
    action: '',
    remarks: '',
})

const submit = (transactionId) => {
    if (form.remarks === '') {
        form.remarks = approvalAction.value === 'approve' ? 'Withdrawal approved ' : 'Withdrawal rejected. Please submit again.'
    }

    form.id = transactionId;
    form.action = approvalAction.value;

    form.post(route('pending.withdrawalApproval'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
        },
    });
};

const handleFilter = (e) => {
    filteredValueCount.value = e.filteredValue.length;
};

</script>

<template>
    <AuthenticatedLayout :title="$t('public.bonus_withdrawal')">
        <div class="p-6 flex flex-col items-center justify-center self-stretch gap-6 border border-gray-200 bg-white shadow-table rounded-2xl">
            <DataTable
                v-model:filters="filters"
                :value="pendingWithdrawals"
                :paginator="pendingWithdrawals?.length > 0 && filteredValueCount > 0"
                removableSort
                :rows="50"
                :rowsPerPageOptions="[10, 20, 50, 100]"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                :currentPageReportTemplate="paginator_caption"
                :globalFilterFields="['user_name', 'user_email', 'from']"
                ref="dt"
                :loading="loading"
                selectionMode="single"
                @row-click="rowClicked($event.data)"
                @filter="handleFilter"
            >
                <template #header>
                    <div class="flex flex-col md:flex-row gap-3 md:justify-between items-center self-stretch md:pb-6">
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
                        <Button
                            variant="primary-outlined"
                            @click="exportCSV($event)"
                            class="w-full md:w-auto"
                        >
                            {{ $t('public.export') }}
                        </Button>
                    </div>
                </template>
                <template #empty><Empty :title="$t('public.empty_pending_request_title')" :message="$t('public.empty_pending_request_message')" /></template>
                <template #loading>
                    <div class="flex flex-col gap-2 items-center justify-center">
                        <Loader />
                        <span class="text-sm text-gray-700">{{ $t('public.loading_transactions_caption') }}</span>
                    </div>
                </template>
                <template v-if="pendingWithdrawals?.length > 0 && filteredValueCount > 0">
                    <Column field="created_at" sortable style="width: 25%" class="hidden md:table-cell">
                        <template #header>
                            <span class="hidden md:block">{{ $t('public.requested_date') }}</span>
                        </template>
                        <template #body="slotProps">
                            {{ dayjs(slotProps.data.created_at).format('YYYY/MM/DD HH:mm:ss') }}
                        </template>
                    </Column>
                    <Column field="name" sortable :header="$t('public.member')" style="width: 25%" class="hidden md:table-cell">
                        <template #body="slotProps">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                    <div v-if="slotProps.data.user_profile_photo">
                                        <img :src="slotProps.data.user_profile_photo" alt="Profile Photo" />
                                    </div>
                                    <div v-else>
                                        <DefaultProfilePhoto />
                                    </div>
                                </div>
                                <div class="flex flex-col items-start">
                                    <div class="font-medium">
                                        {{ slotProps.data.user_name }}
                                    </div>
                                    <div class="text-gray-500 text-xs">
                                        {{ slotProps.data.user_email }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="amount" header="" sortable style="width: 25%" class="hidden md:table-cell">
                        <template #header>
                            <span class="hidden md:block items-center justify-center">{{ $t('public.amount') }} ($)</span>
                        </template>
                        <template #body="slotProps">
                            {{ formatAmount(slotProps.data.amount) }}
                        </template>
                    </Column>
                    <ColumnGroup type="footer">
                        <Row v-if="filteredValueCount > 0">
                            <Column class="hidden md:table-cell" :footer="$t('public.total') + ' ($) :'" :colspan="2" footerStyle="text-align:right" />
                            <Column class="hidden md:table-cell" :footer="formatAmount(totalAmount ? totalAmount : 0)" />
                            <Column class="md:hidden" footerStyle="text-align:right">
                                <template #footer>
                                    <div class="flex items-center justify-end">
                                        <div class="overflow-hidden text-right text-ellipsis font-semibold">
                                            {{ $t('public.total') + ' ($) :' }}
                                        </div>
                                        <div class="w-[120px] overflow-hidden text-right text-ellipsis font-semibold">
                                            {{ formatAmount(totalAmount ? totalAmount : 0) }}
                                        </div>
                                    </div>
                                </template>
                            </Column>
                        </Row>
                    </ColumnGroup>
                    <Column class="md:hidden">
                        <template #body="slotProps">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                        <DefaultProfilePhoto />
                                    </div>
                                    <div class="flex flex-col items-start">
                                        <div class="text-sm font-semibold">
                                            {{ slotProps.data.user_name }}
                                        </div>
                                        <div class="text-gray-500 text-xs">
                                            {{ formatDateTime(slotProps.data.created_at) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-hidden text-right text-ellipsis font-semibold">
                                    {{ slotProps.data.amount ?  '$&nbsp;' + formatAmount(slotProps.data.amount) : '' }}
                                </div>
                            </div>
                        </template>
                    </Column>
                </template>
            </DataTable>
        </div>
    </AuthenticatedLayout>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.withdrawal_request', {action: ''})"
        class="dialog-xs md:dialog-md"
        :dismissableMask="true"
    >
        <template
            v-if="!approvalAction"
        >
            <div class="flex flex-col items-center gap-4 divide-y self-stretch">
                <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full">
                    <div class="flex items-center gap-3 self-stretch w-full">
                        <div class="w-9 h-9 rounded-full overflow-hidden grow-0 shrink-0">
                            <div v-if="pendingData.user_profile_photo">
                                <img :src="pendingData.user_profile_photo" alt="Profile Photo" />
                            </div>
                            <div v-else>
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div class="flex flex-col items-start w-full">
                            <span class="text-gray-950 text-sm font-medium">{{ pendingData.user_name }}</span>
                            <span class="text-gray-500 text-xs">{{ pendingData.user_email }}</span>
                        </div>
                    </div>
                    <div class="min-w-[180px] text-gray-950 font-semibold text-xl md:text-right">
                        $ {{ formatAmount(pendingData.amount) }}
                    </div>
                </div>

                <div class="flex flex-col gap-3 items-start w-full pt-4">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-gray-500 text-xs font-medium">
                            {{ $t('public.requested_date') }}
                        </div>
                        <div class="text-gray-950 text-sm font-medium">
                            {{ dayjs(pendingData.created_at).format('YYYY/MM/DD HH:mm:ss') }}
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-gray-500 text-xs font-medium">
                            {{ $t('public.from') }}
                        </div>
                        <div class="text-gray-950 text-sm font-medium">
                            {{ $t(`public.${pendingData.from}`) }}
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-gray-500 text-xs font-medium">
                            {{ $t('public.balance') }}
                        </div>
                        <div class="text-gray-950 text-sm font-medium">
                            $ {{ formatAmount(pendingData.balance) }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 items-start w-full pt-4">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-gray-500 text-xs font-medium">
                            {{ $t('public.wallet_name') }}
                        </div>
                        <div class="text-gray-950 text-sm font-medium">
                            {{ pendingData.wallet_name }}
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="min-w-[140px] text-gray-500 text-xs font-medium">
                            {{ $t('public.receiving_address') }}
                        </div>
                        <div class="text-gray-950 text-sm break-words font-medium">
                            {{ pendingData.wallet_address }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end items-center pt-5 gap-4 self-stretch sm:pt-7">
                <Button
                    type="button"
                    variant="error-flat"
                    class="w-full md:w-[120px]"
                    @click="handleApproval('reject')"
                >
                    {{ $t('public.reject') }}
                </Button>
                <Button
                    variant="success-flat"
                    class="w-full md:w-[120px]"
                    @click="handleApproval('approve')"
                >
                    {{ $t('public.approve') }}
                </Button>
            </div>
        </template>

        <template
            v-if="approvalAction"
            #container="{ closeCallback }"
        >
            <div class="pt-7 px-4 md:pt-8 md:px-7 flex flex-col gap-3 md:gap-5 self-stretch w-full">
                <div class="flex flex-col items-center self-stretch gap-2">
                    <span class="capitalize text-gray-950 text-lg font-semibold">{{ $t('public.withdrawal_request', {action: $t(`public.${approvalAction}`)}) }}</span>
                    <div class="text-gray-500 text-sm">
                        {{ $t('public.withdrawal_request_caption_1') }}
                        <span class="font-semibold lowercase" :class="[approvalAction === 'approve' ? 'text-success-500' : 'text-error-500']">{{ $t(`public.${approvalAction}`) }}</span>
                        {{ $t('public.withdrawal_request_caption_2') }}
                    </div>
                </div>

                <div class="flex flex-col-reverse md:flex-row md:items-center gap-3 py-4 self-stretch w-full">
                    <div class="flex items-center gap-3 self-stretch w-full">
                        <div class="w-9 h-9 rounded-full overflow-hidden grow-0 shrink-0">
                            <div v-if="pendingData.user_profile_photo">
                                <img :src="pendingData.user_profile_photo" alt="Profile Photo" />
                            </div>
                            <div v-else>
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div class="flex flex-col items-start w-full">
                            <span class="text-gray-950 text-sm font-medium">{{ pendingData.user_name }}</span>
                            <span class="text-gray-500 text-xs">{{ pendingData.user_email }}</span>
                        </div>
                    </div>
                    <div class="min-w-[180px] text-gray-950 font-semibold text-xl md:text-right">
                        $ {{ formatAmount(pendingData.amount) }}
                    </div>
                </div>

                <div class="flex flex-col items-start gap-3 h-40 self-stretch">
                    <InputLabel for="remarks">{{ $t('public.remarks') }}</InputLabel>
                    <div class="flex items-center gap-2 self-stretch overflow-x-auto">
                        <div v-for="(chip, index) in chips[approvalAction]" :key="index">
                            <Chip
                                :label="chip.label"
                                class="w-full text-gray-950 whitespace-nowrap overflow-hidden"
                                :class="{
                                    'border-primary-300 bg-primary-50 text-primary-500 hover:bg-primary-50': form.remarks === chip.label,
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
                        :placeholder="approvalAction === 'approve' ? 'Withdrawal approved' : 'Withdrawal rejected. Please submit again.'"
                        :invalid="!!form.errors.remarks"
                        rows="5"
                        cols="30"
                    />
                </div>
            </div>

            <div class="flex justify-end items-center py-5 px-4 gap-4 self-stretch sm:p-7">
                <Button
                    type="button"
                    variant="gray-tonal"
                    class="w-full md:w-[120px]"
                    @click="closeDialog"
                >
                    {{ $t('public.cancel') }}
                </Button>
                <Button
                    variant="primary-flat"
                    class="w-full md:w-[120px]"
                    @click="submit(pendingData.id)"
                >
                    {{ $t('public.confirm') }}
                </Button>
            </div>
        </template>
    </Dialog>
</template>
