<script setup>
import Button from '@/Components/Button.vue';
import { IconRefresh} from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import { ref, watch } from 'vue';
import Dropdown from "primevue/dropdown";
import dayjs from "dayjs";

const props = defineProps({
    postCounts: Number,
    groupMonths: Array,
})

const { formatAmount } = transactionFormat();
const counterDuration = ref(10);
const netAsset = ref(99999.00)
const totalDeposit = ref(99999.00)
const totalWithdrawal = ref(99999.00)
const totalRebate = ref(99999.00)

const selectedMonth = ref('');
const currentYear = dayjs().year();
const transactionMonth = ref([]);

const tradeLotVolumeDuration = ref(10);
const counterTradeLot = ref(null);
const counterVolume = ref(null);
const trade_lot = ref(0);
const volume = ref(0);
const tradeLotVolumeLoading = ref(false);

// Populate historyPeriodOptions with all months of the current year
for (let month = 1; month <= 12; month++) {
    transactionMonth.value.push({
        value: dayjs().month(month - 1).year(currentYear).format('MM/YYYY')
    });
}

selectedMonth.value = dayjs().format('MM/YYYY');

const updateTradeLotVolume = () => {
    // Reset the selected month to the current month
    selectedMonth.value = dayjs().format('MM/YYYY');
    
    // Reset the trade lot and volume counters if they have a reset() method
    if (counterTradeLot.value && counterTradeLot.value.reset) {
        counterTradeLot.value.reset();
    }
    if (counterVolume.value && counterVolume.value.reset) {
        counterVolume.value.reset();
    }
    getTradeLotVolume();
};

const getAssetData = async () => {
    try {
        // Base URL
        let url = '/getAssetData';

        // Make the GET request
        const response = await axios.get(url);

        totalRebate.value = parseFloat(response.data.totalRebatePayout);
        netAsset.value = parseFloat(totalDeposit.value - totalWithdrawal.value);
    } catch (error) {
        console.error('Error fetching asset data:', error);
    } finally {
        counterDuration.value = 1
    }
};

const getTradeLotVolume = async () => {
    tradeLotVolumeLoading.value = true;
    try {
        const response = await axios.get(`/getTradeLotVolume?selectedMonth=${selectedMonth.value}`);
        
        // Process response data here
        trade_lot.value = response.data.totalTradeLots;
        volume.value = response.data.totalVolume;

        tradeLotVolumeDuration.value = 1;
    } catch (error) {
        console.error('Error fetching data:', error);
        tradeLotVolumeLoading.value = false;
    } finally {
        tradeLotVolumeDuration.value = 1
        tradeLotVolumeLoading.value = false;
    }
};

// Watch for changes to selectedMonth
watch(selectedMonth, (newMonth) => {
    getAssetData(newMonth);
});

getAssetData(selectedMonth.value);

</script>
<template>
    <div class="p-4 md:py-6 md:px-8 flex flex-col items-center gap-8 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
        <div class="flex items-center self-stretch">
            <div class="flex-1 text-gray-950 font-semibold">
            <Dropdown
                    v-model="selectedMonth"
                    :options="transactionMonth"
                    optionLabel="value"
                    optionValue="value"
                    :placeholder="$t('public.month_placeholder')"
                    scroll-height="236px"
                    :pt="{
                        root: 'inline-flex items-center justify-center relative rounded-lg bg-gray-100 px-3 py-2 gap-3 cursor-pointer overflow-hidden overflow-ellipsis whitespace-nowrap appearance-none',
                        input: 'text-sm font-medium block flex-auto relative focus:outline-none',
                        trigger: 'w-4 h-4 flex items-center justify-center shrink-0',
                    }"
                />
            </div>
            <Button
                variant="gray-text"
                size="sm"
                type="button"
                iconOnly
                v-slot="{ iconSizeClasses }"
                @click="updateTradeLotVolume()"
            >
                <IconRefresh size="16" stroke-width="1.25" color="#667085" />
            </Button>
        </div>

        <div class="w-full h-full flex flex-row justify-center items-center gap-2 md:gap-5 ">
            <div class="w-full h-full grid grid-cols-1 justify-center items-center py-3 px-0.5 gap-1 bg-gray-50 md:px-0">
                <span class="w-full truncate text-gray-500 text-center text-xxs md:text-sm">{{
                    $t('public.total_trade_lots') }}</span>
                <span v-if="(trade_lot || trade_lot === 0) && !tradeLotVolumeLoading"
                    class="w-full truncate text-gray-950 text-center font-semibold md:text-xl">
                    {{ formatAmount(trade_lot) }} Ł
                </span>
                <span v-else class="self-stretch truncate text-gray-950 text-right font-semibold md:text-xl animate-pulse flex justify-center items-center">
                    <div class="h-2.5 bg-gray-200 rounded-full w-1/3"></div>
                </span>
            </div>

            <div class="w-full h-full grid grid-cols-1 justify-center items-center py-3 px-0.5 gap-1 bg-gray-50 md:px-0">
                <span class="w-full truncate text-gray-500 text-center text-xxs md:text-sm">{{ $t('public.total_trade_volume') }}</span>
                <span v-if="(volume || volume === 0) && !tradeLotVolumeLoading" class="w-full truncate text-gray-950 text-center font-semibold md:text-xl">
                    {{ formatAmount(volume, 0) }}
                </span>
                <span v-else class="self-stretch truncate text-gray-950 text-right font-semibold md:text-xl animate-pulse flex justify-center items-center">
                    <div class="h-2.5 bg-gray-200 rounded-full w-1/3"></div>
                </span>
            </div>
        </div>
    </div>

    <div class="p-4 md:py-6 md:px-8 flex flex-col items-center gap-8 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
        <div class="w-full flex justify-between items-center">
            <div class="flex-1 text-gray-950 font-semibold">
            <Dropdown
                    v-model="selectedMonth"
                    :options="transactionMonth"
                    optionLabel="value"
                    optionValue="value"
                    :placeholder="$t('public.month_placeholder')"
                    scroll-height="236px"
                    :pt="{
                        root: 'inline-flex items-center justify-center relative rounded-lg bg-gray-100 px-3 py-2 gap-3 cursor-pointer overflow-hidden overflow-ellipsis whitespace-nowrap appearance-none',
                        input: 'text-sm font-medium block flex-auto relative focus:outline-none',
                        trigger: 'w-4 h-4 flex items-center justify-center shrink-0',
                    }"
                />
            </div>
        </div>

        <div class="w-full h-full flex flex-col justify-center items-center gap-2 md:gap-5 md:flex-row ">
            <div class="min-w-60 w-full h-full grid grid-cols-1 justify-center items-center p-3 gap-2 bg-gray-50">
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Swap P&L ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Markup P&L ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Gross P&L ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Broker P&L ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
            </div>

            <div class="min-w-60 w-full h-full grid grid-cols-1 justify-center items-center p-3 gap-2 bg-gray-50">
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Net P&L ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Losing Deals ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Win. Deals ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
                <div class="flex flex-row gap-1 w-full justify-between items-center">
                    <span class="text-xs text-gray-500 w-[140px]">Trader P&L ($)</span>
                    <span class="text-sm font-medium text-gray-950 self-stretch">0.00</span>
                </div>
            </div>
        </div>
    </div>
</template>