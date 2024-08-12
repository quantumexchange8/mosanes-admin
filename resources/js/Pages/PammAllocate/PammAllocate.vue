<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, watch, computed } from "vue";
import { transactionFormat } from "@/Composables/index.js";
import { usePage } from "@inertiajs/vue3";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { 
  IconSearch, IconCircleXFilled, IconUserDollar, IconPremiumRights, 
  IconAdjustments, IconScanEye, IconTriangleFilled, IconTriangleInvertedFilled, 
  IconChevronLeft, IconChevronRight 
} from '@tabler/icons-vue';
import AddAssetMaster from '@/Pages/PammAllocate/Partials/AddAssetMaster.vue';
import InputText from 'primevue/inputtext';
import Button from '@/Components/Button.vue';
import Badge from '@/Components/Badge.vue';
import Dropdown from "primevue/dropdown";
import Action from "@/Pages/PammAllocate/Partials/Action.vue";
import OverlayPanel from 'primevue/overlaypanel';
import Calendar from 'primevue/calendar';
import AssetMaster from '@/Pages/PammAllocate/Partials/AssetMaster.vue';

const { formatDate, formatMonthDate, formatAmount } = transactionFormat();

const loading = ref(false);
const topThreeMasters = ref([]);
const totalAsset = ref(0);
const totalAssetComparison = ref(0);
const currentInvestor = ref(0);
const investorComparision = ref(0);
const today = new Date(new Date().setHours(0, 0, 0, 0));
const selectedDate = ref(today);
const isCalendarVisible = ref(false);
const profit = ref(0);
const loss = ref(0);

const groupsOptions = ref([]);

const getResults = async () => {
    loading.value = true;

    try {
        const response = await axios.get('/pamm_allocate/getMetrics');
        topThreeMasters.value = response.data.topThreeMasters;
        totalAsset.value = response.data.totalAsset;
        totalAssetComparison.value = response.data.totalAssetComparison;
        currentInvestor.value = response.data.currentInvestor;
        investorComparision.value = response.data.investorComparision;
    } catch (error) {
        console.error('Error getting metrics:', error);
    } finally {
        loading.value = false;
    }
};

const getOptions = async () => {
    try {
        const response = await axios.get('/pamm_allocate/getOptions');
        groupsOptions.value = response.data.groupsOptions;
    } catch (error) {
        console.error('Error getting options:', error);
    }
};

const getProfitLoss = async (date) => {
    try {
        const formattedDate = formatDate(date);
        const response = await axios.get('/pamm_allocate/getProfitLoss', { params: { date: formattedDate } });
        profit.value = response.data.profit;
        loss.value = response.data.loss;
    } catch (error) {
        console.error('Error getting profit/loss:', error);
    }
};

getResults();
getOptions();
// getProfitLoss(selectedDate);

function calculatePercentage(fund) {
  if (!totalAsset.value || !fund) {
    return 0;
  }
  return ((fund / totalAsset.value) * 100).toFixed(2);
}

function calculateProfitLossPercentage(profit, loss) {
  const total = profit + loss;
  if (total === 0) {
    return 0;
  }
  return ((profit / total) * 100).toFixed(2);
}

watch(selectedDate, (newDate) => {
    getProfitLoss(newDate); 
});

const toggleCalendar = () => {
  isCalendarVisible.value = !isCalendarVisible.value;
};

const changeDate = (days) => {
  const newDate = new Date(selectedDate.value);
  newDate.setDate(newDate.getDate() + days);

  if (newDate <= today) {
    selectedDate.value = newDate;
  }
};

const isNextButtonDisabled = computed(() => {
    return (selectedDate.value >= today);
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.pamm_allocate')">
        <div class="flex flex-col items-center gap-5 md:gap-8">
            <div class="flex justify-end items-center self-stretch">
                <AddAssetMaster :groupsOptions="groupsOptions" />
            </div>
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="w-full flex flex-col items-center py-5 px-4 gap-5 rounded-2xl bg-white shadow-toast md:col-span-1">
                    <div class="flex flex-col items-start self-stretch md:flex-shrink-0">
                        <div class="flex justify-center items-center py-2">
                            <span class="text-gray-500 text-sm">{{ $t('public.current_assets_under_management') + `($)` }}</span>
                        </div>
                        <div class="flex items-end gap-5">
                            <span class="text-gray-950 text-xl font-semibold md:text-xxl">{{ totalAsset ? formatAmount(totalAsset) : formatAmount(0) }}</span>
                            <div class="flex items-center pb-1.5 gap-2">
                                <div v-if="totalAsset" class="flex items-center gap-2">
                                    <div 
                                        class="flex items-center gap-2"
                                        :class="
                                            {
                                                'text-green': totalAssetComparison > 0,
                                                'text-pink': totalAssetComparison < 0
                                            }"
                                    >
                                        <IconTriangleFilled v-if="totalAssetComparison > 0" size="12" stroke-width="1.25" />
                                        <IconTriangleInvertedFilled v-if="totalAssetComparison < 0" size="12" stroke-width="1.25" />
                                        <span class="text-xs font-medium md:text-sm">  {{ `${formatAmount(Math.abs(totalAssetComparison))}%` }}</span>
                                    </div>
                                    <span class="text-gray-400 text-xs md:text-sm">{{ $t('public.compare_last_month') }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-xs md:text-sm">{{ $t('public.data_not_available') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 w-full">
                        <div v-for="index in 3" :key="index" class="grid grid-cols-3 items-center py-2 gap-3 md:gap-4">
                            <div class="w-full flex gap-3 md:gap-4">
                                <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                    <DefaultProfilePhoto />
                                </div>
                                <span class="truncate text-gray-950 text-sm font-medium md:text-base">
                                    {{ topThreeMasters[index - 1]?.asset_name || '------' }}
                                </span>
                            </div>
                            <div class="w-full h-1 bg-gray-100 rounded-full relative">
                                <div
                                    class="absolute top-0 left-0 h-full rounded-full bg-primary-500"
                                    :style="{ width: `${calculatePercentage(topThreeMasters[index - 1]?.total_fund)}%` }"
                                />
                            </div>
                            <span class="truncate text-gray-950 text-right text-sm font-medium md:text-base">
                                {{ topThreeMasters[index - 1]?.total_fund ? formatAmount(topThreeMasters[index - 1].total_fund) : formatAmount(0) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="w-full flex flex-col gap-4 md:col-span-1">
                    <div class="w-full flex flex-col items-center py-5 px-4 gap-3 rounded-2xl bg-white shadow-toast md:px-6 md:py-6">
                        <span class="self-stretch text-gray-500 text-sm">{{ $t('public.current_joining_investors') }}</span>
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <span class="text-gray-950 text-xl font-semibold md:text-xxl">{{ currentInvestor ? formatAmount(currentInvestor) : formatAmount(0) }}</span>
                            <div class="flex items-center pb-1.5 gap-2">
                                <div v-if="currentInvestor" class="flex items-center gap-2">
                                    <div 
                                        class="flex items-center gap-2"
                                        :class="
                                            {
                                                'text-green': investorComparision > 0,
                                                'text-pink': investorComparision < 0
                                            }"
                                    >
                                        <IconTriangleFilled v-if="investorComparision > 0" size="12" stroke-width="1.25" />
                                        <IconTriangleInvertedFilled v-if="investorComparision < 0" size="12" stroke-width="1.25" />
                                        <span class="text-xs font-medium md:text-sm">  {{ `${Math.abs(investorComparision)} ${$t('public.pax')}` }}</span>
                                    </div>
                                    <span class="text-gray-400 text-xs md:text-sm">{{ $t('public.compare_last_month') }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-xs md:text-sm">{{ $t('public.data_not_available') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full flex flex-col items-center px-4 pt-5 pb-6 gap-3 rounded-2xl bg-white shadow-toast md:px-6">
                        <!-- date selection -->
                         <div class="flex justify-between items-center self-stretch">
                            <Button 
                                type="button" 
                                variant="gray-text" 
                                size="sm" 
                                iconOnly
                                @click="changeDate(-1)"
                            >
                                <div class="w-full text-gray-500">
                                    <IconChevronLeft size="16" stroke-width="1.25" />
                                </div>
                            </Button>
                            <div class="w-full h-full flex justify-center items-center"  @click="toggleCalendar">
                                <span class="text-gray-950 text-center text-sm font-semibold">{{ formatMonthDate(selectedDate) }}</span>
                            </div>
                            <Button 
                                type="button" 
                                variant="gray-text" 
                                size="sm" 
                                iconOnly
                                @click="changeDate(1)"
                                :disabled="isNextButtonDisabled"
                                >
                                <IconChevronRight size="16" stroke-width="1.25" />
                            </Button>
                         </div>
                         <Calendar
                            v-if="isCalendarVisible"
                            v-model="selectedDate"
                            selectionMode="single"
                            :manualInput="false"
                            :maxDate="today"
                            dateFormat="M dd"
                            class="w-full"
                            inline
                        >
                        </Calendar>
                         <div class="flex flex-col items-center gap-1.5 self-stretch">
                            <div class="flex justify-between items-center self-stretch">
                                <span class="text-gray-500 text-xs">{{ $t('public.profit') }}</span>
                                <span class="text-gray-500 text-right text-xs">{{ $t('public.loss') }}</span>
                            </div>
                            <!-- bar -->
                            <div class="w-full h-1 flex-grow rounded-full relative"
                                :class="{
                                'bg-pink-500': profit || loss,
                                'bg-gray-100': !(profit || loss)
                            }">
                                <div
                                    class="absolute top-0 left-0 h-full rounded-full bg-primary-500"
                                    :style="{ width: `${calculateProfitLossPercentage(profit, loss)}%` }"
                                />
                            </div>
                            <div class="flex justify-between items-center self-stretch">
                                <span class="text-gray-950 text-sm font-medium">${{ formatAmount(profit) }}</span>
                                <span class="text-gray-950 text-right text-sm font-medium">${{ formatAmount(loss) }}</span>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col items-center gap-5 md:gap-8">
                <AssetMaster
                    :groupsOptions="groupsOptions"
                />
            </div>

        </div>


    </AuthenticatedLayout>
</template>
