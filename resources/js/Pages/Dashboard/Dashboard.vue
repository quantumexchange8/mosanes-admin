<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IllustrationGreetings from '@/Pages/Dashboard/Partials/IllustrationGreetings.vue';
import Button from '@/Components/Button.vue';
import { IconRefresh, IconChevronRight, IconChevronDown, IconCaretUpFilled } from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import { DepositIcon, WithdrawalIcon, RebateIcon, MemberIcon, AgentIcon } from '@/Components/Icons/solid';
import Badge from '@/Components/Badge.vue';
import Vue3Autocounter from 'vue3-autocounter';
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dropdown from "primevue/dropdown";
import dayjs from "dayjs";
import DashboardForum from "@/Pages/Dashboard/Partials/DashboardForum.vue";

const props = defineProps({
    postCounts: Number
})

const page = usePage();

const { formatAmount } = transactionFormat();

const counterDuration = ref(10);
const accountBalanceDuration = ref(10);
const balance = ref(99999.00)
const equity = ref(99999.00)
const pendingWithdrawal = ref(0)
const netAsset = ref(99999.00)
const totalDeposit = ref(99999.00)
const totalWithdrawal = ref(99999.00)
const totalRebate = ref(99999.00)
const totalMember = ref(999);
const totalAgent = ref(999);
const todayDeposit = ref(0);
const todayWithdrawal = ref(0);
const todayAgent = ref(0);
const todayMember = ref(0);
const pendingWithdrawalCount = ref(0);
const pendingBonus = ref(0);
const pendingBonusCount = ref(0);
const trade_lot = ref(0);
const volume = ref(0);
const counterEquity = ref(null);
const counterBalance = ref(null);

const selectedMonth = ref('');
const currentYear = dayjs().year();
const transactionMonth = ref([]);

// Populate historyPeriodOptions with all months of the current year
for (let month = 1; month <= 12; month++) {
    transactionMonth.value.push({
        value: dayjs().month(month - 1).year(currentYear).format('MM/YYYY')
    });
}

selectedMonth.value = dayjs().format('MM/YYYY');

const updateBalEquity = () => {
    counterEquity.value.reset();
    counterBalance.value.reset();
    getAccountData();
}

const getAccountData = async () => {
    try {
        const response = await axios.get('/getAccountData');
        balance.value = response.data.totalBalance;
        equity.value = response.data.totalEquity;

        accountBalanceDuration.value = 1
    } catch (error) {
        console.error('Error accounts data:', error);
    }
};

const getPendingData = async () => {
    try {
        const response = await axios.get('/getPendingData');
        pendingWithdrawal.value = parseFloat(response.data.pendingWithdrawal);
        pendingWithdrawalCount.value = parseFloat(response.data.pendingWithdrawal);
        pendingBonus.value = parseFloat(response.data.pendingBonus);
        pendingBonusCount.value = parseFloat(response.data.pendingBonusCount);
    } catch (error) {
        console.error('Error pending data:', error);
    } finally {
        counterDuration.value = 1
    }
};

const getAssetData = async () => {
    try {
        // Base URL
        let url = '/getAssetData';

        // Make the GET request
        const response = await axios.get(url);

        // Process the response
        // totalDeposit.value = parseFloat(response.data.totalDeposit);
        // totalWithdrawal.value = parseFloat(response.data.totalWithdrawal);
        totalRebate.value = parseFloat(response.data.totalRebatePayout);
        netAsset.value = parseFloat(totalDeposit.value - totalWithdrawal.value);
    } catch (error) {
        console.error('Error fetching asset data:', error);
    } finally {
        counterDuration.value = 1
    }
};

const getDashboardData = async () => {
    try {
        const response = await axios.get('/getDashboardData');
        totalDeposit.value = response.data.totalDeposit;
        totalWithdrawal.value = response.data.totalWithdrawal;
        totalAgent.value = response.data.totalAgent;
        totalMember.value = response.data.totalMember;
        todayDeposit.value = response.data.todayDeposit;
        todayWithdrawal.value = response.data.todayWithdrawal;
        todayAgent.value = response.data.todayAgent;
        todayMember.value = response.data.todayMember;
    } catch (error) {
        console.error('Error pending counts:', error);
    } finally {
        counterDuration.value = 1
    }
};

getDashboardData();

// Watch for changes to selectedMonth
watch(selectedMonth, (newMonth) => {
    getAssetData(newMonth);
});

getAccountData();
getPendingData();
getAssetData(selectedMonth.value);

const goToTransactionPage = (type) => {
    // Navigate to the transaction page with a query parameter
    window.location.href = `/transaction?type=${type}`;
};

</script>

<template>
    <AuthenticatedLayout :title="$t('public.dashboard')">
        <div class="w-full flex flex-col items-center gap-5">
            <div
                class="w-full h-40 py-6 pl-4 md:p-8 flex justify-between self-stretch rounded-2xl bg-primary-100 shadow-toast relative bg-[left_-1px] bg-no-repeat xl:bg-[length:1440px]"
                style="background-image: url('/img/background-greetings.svg');"
            >
                <div class="w-3/4 md:w-full flex flex-col items-start gap-1">
                    <div class="self-stretch text-primary-900 text-base md:text-xxl font-bold">
                        {{ $t('public.greeting') }}
                    </div>
                    <div class="self-stretch text-primary-900 text-xs md:text-base">
                        {{ $t('public.greeting_caption') }}
                    </div>
                </div>

                <div class="absolute right-0 bottom-0">
                    <IllustrationGreetings />
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold text-sm md:text-base">
                            Withdrawal Request ($)
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('pending')"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>

                    <div class="self-stretch text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="pendingWithdrawal" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Badge class="text-white text-sm">
                            {{ pendingWithdrawalCount }}
                        </Badge>
                        <div class="text-gray-500 text-xs md:text-sm">
                            {{ $t('public.pending_withdrawal_caption') }}
                        </div>
                    </div>
                </div>

                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold text-sm md:text-base">
                            Bonus Request ($)
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('billboard')"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>

                    <div class="self-stretch text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="pendingBonus" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Badge class="text-white text-sm">
                            {{ pendingBonusCount }}
                        </Badge>
                        <div class="text-gray-500 text-xs md:text-sm">
                            {{ $t('public.pending_withdrawal_caption') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center gap-6 w-full">
                        <AgentIcon class="w-12 h-12" />
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                Total Agent
                            </div>
                            <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalAgent" :duration="1" separator="," decimalSeparator="." :decimals="0" :autoinit="true" />
                            </div>
                            <div class="flex items-center gap-2">
                                <IconCaretUpFilled 
                                    class="w-3 h-3 md:w-4 md:h-4"
                                    :class="{'text-success-500': todayAgent > 0, 'text-gray-950': todayAgent <= 0}"
                                />
                                    {{ todayAgent }}

                                <div class="text-gray-500 text-xs md:text-sm">
                                    Today
                                </div>
                            </div>
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('member.listing', { user_type: 'agent' })"
                            class="ml-auto"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>
                </div>

                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center gap-6 w-full">
                        <MemberIcon class="w-12 h-12"/>
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                Total Member
                            </div>
                            <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalMember" :duration="1" separator="," decimalSeparator="." :decimals="0" :autoinit="true" />
                            </div>
                            <div class="flex items-center gap-2">
                                <IconCaretUpFilled 
                                    class="w-3 h-3 md:w-4 md:h-4"
                                    :class="{'text-success-500': todayMember > 0, 'text-gray-950': todayMember <= 0}"
                                />
                                    {{ todayMember }}

                                <div class="text-gray-500 text-xs md:text-sm">
                                    Today
                                </div>
                            </div>
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('member.listing')"
                            class="ml-auto"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center gap-6 w-full">
                        <DepositIcon class="w-12 h-12"/>
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                {{ $t('public.total_deposit') }} ($)
                            </div>
                            <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalDeposit" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                            </div>
                            <div class="flex items-center gap-2">
                                <IconCaretUpFilled 
                                    class="w-3 h-3 md:w-4 md:h-4"
                                    :class="{'text-success-500': todayDeposit > 0, 'text-gray-950': todayDeposit <= 0}"
                                />
                                    {{ todayDeposit }}

                                <div class="text-gray-500 text-xs md:text-sm">
                                    Today
                                </div>
                            </div>
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('transaction.deposit')"
                            class="ml-auto"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>
                </div>
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center gap-6 w-full">
                        <WithdrawalIcon class="w-12 h-12"/>
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                {{ $t('public.total_withdrawal') }} ($)
                            </div>
                            <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalWithdrawal" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                            </div>
                            <div class="flex items-center gap-2">
                                <IconCaretUpFilled 
                                    class="w-3 h-3 md:w-4 md:h-4"
                                    :class="{'text-success-500': todayWithdrawal > 0, 'text-gray-950': todayWithdrawal <= 0}"
                                />
                                    {{ todayWithdrawal }}

                                <div class="text-gray-500 text-xs md:text-sm">
                                    Today
                                </div>
                            </div>
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('transaction.withdrawal')"
                            class="ml-auto"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center gap-6 w-full">
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                {{ $t('public.account_listing') }}
                            </div>
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('member.account_listing')"
                            class="ml-auto"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>
                </div>
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center gap-6 w-full">
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                Edit Forum
                            </div>
                        </div>
                        <Button
                            external
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            :href="route('member.forum')"
                            class="ml-auto"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>
                </div>
            </div>

            <div class="p-4 md:py-6 md:px-8 flex flex-col items-center gap-8 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                <div class="flex items-center self-stretch">
                    <div class="flex-1 text-gray-950 font-semibold">
                        {{ $t('public.account_balance_equity') }}
                    </div>
                    <Button
                        variant="gray-text"
                        size="sm"
                        type="button"
                        iconOnly
                        v-slot="{ iconSizeClasses }"
                        @click="updateBalEquity()"
                    >
                        <IconRefresh size="16" stroke-width="1.25" color="#667085" />
                    </Button>
                </div>

                <div class="w-full h-full flex flex-row justify-center items-center gap-2 md:gap-5 ">
                    <div class="w-full h-full grid grid-cols-1 justify-center items-center py-3 px-0.5 gap-1 bg-gray-50 md:px-0">
                        <span class="w-full truncate text-gray-500 text-center text-xxs md:text-sm">{{ $t('public.balance') }}</span>
                        <span v-if="(balance || balance === 0) && !accountLoading" class="w-full truncate text-gray-950 text-center font-semibold md:text-xl">
                            $ {{ formatAmount(balance) }}
                        </span>
                        <span v-else class="self-stretch truncate text-gray-950 text-right font-semibold md:text-xl animate-pulse flex justify-center items-center">
                            <div class="h-2.5 bg-gray-200 rounded-full w-1/3"></div>
                        </span>
                    </div>

                    <div class="w-full h-full grid grid-cols-1 justify-center items-center py-3 px-0.5 gap-1 bg-gray-50 md:px-0">
                        <span class="w-full truncate text-gray-500 text-center text-xxs md:text-sm">{{ $t('public.equity') }}</span>
                        <span v-if="(equity || equity === 0) && !accountLoading" class="w-full truncate text-gray-950 text-center font-semibold md:text-xl">
                            $ {{ formatAmount(equity) }}
                        </span>
                        <span v-else class="self-stretch truncate text-gray-950 text-right font-semibold md:text-xl animate-pulse flex justify-center items-center">
                            <div class="h-2.5 bg-gray-200 rounded-full w-1/3"></div>
                        </span>
                    </div>
                </div>
            </div>

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
                        @click="updateBalEquity()"
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
                    <!-- <Button 
                        variant="gray-text" 
                        size="sm" 
                        type="button" 
                        iconOnly 
                        v-slot="{ iconSizeClasses }"
                        @click="updateTradeLotVolume()"
                    >
                        <IconRefresh size="16" stroke-width="1.25" color="#374151" />
                    </Button> -->

                    <!-- <Select 
                        v-model="selectedPnlMonth" 
                        :options="pnlMonths" 
                        optionLabel="name" 
                        optionValue="value"
                        :placeholder="$t('public.month_placeholder')"
                        class="w-60 font-normal truncate" scroll-height="236px" 
                    />
                    <Button 
                        variant="gray-text" 
                        size="sm" 
                        type="button" 
                        iconOnly 
                        v-slot="{ iconSizeClasses }"
                        @click="updatePnL()"
                    >
                        <IconRefresh size="16" stroke-width="1.25" color="#374151" />
                    </Button> -->
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
                        @click="updateBalEquity()"
                    >
                        <IconRefresh size="16" stroke-width="1.25" color="#667085" />
                    </Button>
                </div>

                <div class="w-full max-h-[770px] grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-1 gap-3 md:gap-6">
                    <div
                        v-if="teamLoading"
                        class="w-full flex flex-col items-center rounded bg-white overflow-hidden"
                    >
                        <div class="w-full flex py-1 px-3 items-center gap-3 bg-gray-500 md:py-2 md:px-4">
                            <span class="w-full text-white font-medium truncate animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-20"></div>
                            </span>
                            <div class="flex items-center gap-2 text-white">
                                <IconUserFilled size="20" stroke-width="1.25" />
                                <span class="text-right font-medium animate-pulse">
                                    <div class="h-3 bg-gray-200 rounded-full w-5"></div>
                                </span>
                            </div>
                        </div>
                        <div class="w-full flex flex-col items-center p-3 gap-2 md:p-4 md:gap-3">
                            <div class="w-full grid grid-cols-3 gap-2">
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.team_deposit') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                        <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                                    </span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.team_withdrawal') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                        <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                                    </span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.team_net_balance') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                        <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                                    </span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_team_account_equity') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                        <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                                    </span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_team_adjustment_in') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                        <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                                    </span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_team_adjustment_out') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                        <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        v-for="team in teams"
                        :key="team.id"
                        class="w-full flex flex-col items-center rounded bg-white shadow-card overflow-hidden"
                    >
                        <div 
                            class="w-full flex py-1 px-3 items-center gap-3 md:py-2 md:px-4"
                            :style="{'backgroundColor': `#${team.color}`}"
                        >
                            <span class="w-full text-white font-medium truncate">{{ team.name }}</span>
                            <div class="flex items-center gap-2 text-white">
                                <IconUserFilled size="20" stroke-width="1.25" />
                                <span class="text-right font-medium">{{ formatAmount(team.member_count, 0) }}</span>
                            </div>
                        </div>
                        <div class="w-full flex flex-col items-center p-3 gap-2 md:p-4 md:gap-3">
                            <div class="w-full grid grid-cols-3 gap-2 ">
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.team_deposit') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(team?.deposit || 0) }}</span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.team_withdrawal') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(team?.withdrawal || 0) }}</span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.team_net_balance') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(team?.net_balance || 0) }}</span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_team_account_equity') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(team?.account_equity || 0) }}</span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_team_adjustment_in') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(team?.adjustment_in || 0) }}</span>
                                </div>
                                <div class="w-full flex flex-col items-start gap-1">
                                    <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_team_adjustment_out') }}</span>
                                    <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(team?.adjustment_out || 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
