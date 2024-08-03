<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, watchEffect } from "vue";
import { transactionFormat } from "@/Composables/index.js";
import { usePage } from "@inertiajs/vue3";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { IconSearch, IconCircleXFilled, IconUserDollar, IconPremiumRights, IconAdjustments } from '@tabler/icons-vue';
const { formatAmount } = transactionFormat();

const items = ref([
    { id: 1, name: 'Alice Johnson', value: '$120' },
    { id: 2, name: 'Bob Smith', value: '$150' },
    { id: 3, name: 'Charlie Brown', value: '$200' },
])

const masters = ref();

const getResults = async () => {
    try {
        const response = await axios.get('/pamm_allocate/getMasters');
        masters.value = response.data.masters;
    } catch (error) {
        console.error('Error get masters:', error);
    }
};

getResults();
</script>

<template>
    <AuthenticatedLayout :title="$t('public.pamm_allocate')">
        <div class="flex flex-col items-center gap-5 md:gap-8">
            <!-- add button -->
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="w-full flex flex-col items-center py-5 px-4 gap-5 rounded-2xl bg-white shadow-toast md:col-span-1">
                    <div class="flex flex-col items-start self-stretch md:flex-shrink-0">
                        <div class="flex justify-center items-center py-2">
                            <span class="text-gray-500 text-sm">{{ $t('public.current_assets_under_management') + `($)` }}</span>
                        </div>
                        <div class="flex items-end gap-5">
                            <span class="text-gray-950 text-xl font-semibold md:text-xxl"></span>
                            <div class="flex items-center pb-1.5 gap-2">

                                <span class="text-green text-xs font-medium md:text-sm"></span>
                                <span class="text-gray-400 text-xs md:text-sm">{{ $t('public.compare_last_month') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center items-center gap-3 self-stretch">
                        <div v-for="item in items" :key="item.id" class="flex items-center py-2 gap-3 self-stretch md:gap-4">
                            <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                <DefaultProfilePhoto />
                            </div>
                            <span class="min-w-[100px] max-w-[140px] overflow-hidden text-gray-950 text-ellipsis text-sm font-medium md:min-w-[140px] md:max-w-40 md:text-base">
                                {{ item.name }}
                            </span>
                            <!-- bar -->
                            <span class="w-[88px] text-gray-950 text-right text-sm font-medium md:w-[110px] md:text-base">
                                {{ item.value }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="w-full flex flex-col gap-4 md:col-span-1">
                    <div class="w-full flex flex-col items-center py-5 px-4 gap-3 rounded-2xl bg-white shadow-toast">
                        <span class="self-stretch text-gray-500 text-sm">{{ $t('public.current_joining_investors') }}</span>
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <span class="text-gray-950 text-xl font-semibold md:text-xxl"></span>
                            <div class="flex items-center pb-1.5 gap-2">

                                <span class="text-pink text-xs font-medium"></span>
                                <span class="text-gray-400 text-xs">{{ $t('public.compare_last_month') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full flex flex-col items-center px-4 pt-5 pb-5 gap-3 rounded-2xl bg-white shadow-toast">
                        <!-- date selection -->
                         <div class="flex flex-col items-center gap-1.5 self-stretch">
                            <div class="flex justify-between items-center self-stretch">
                                <span class="text-gray-500 text-xs">{{ $t('public.profit') }}</span>
                                <span class="text-gray-500 text-right text-xs">{{ $t('public.loss') }}</span>
                            </div>
                            <!-- bar -->
                            <div class="flex justify-between items-center self-stretch">
                                <span class="text-gray-950 text-sm font-medium"></span>
                                <span class="text-gray-950 text-right text-sm font-medium"></span>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- toolbar -->
            <div
                v-if="masters"
                class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-4 gap-5 self-stretch"
            >
                <div
                    v-for="(master, index) in masters"
                    :key="index"
                    class="w-full p-6 flex flex-col items-center gap-4 rounded-2xl bg-white shadow-toast"
                >
                    <div class="w-full flex items-center gap-4">
                        <div class="w-[42px] h-[42px] shrink-0 grow-0 rounded-full overflow-hidden">
                            <div v-if="master.profile_photo">
                                <img :src="master.profile_photo" alt="Profile Photo" />
                            </div>
                            <div v-else>
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="self-stretch truncate w-64 text-gray-950 font-bold">
                                {{ master.asset_name }}
                            </div>
                            <div class="self-stretch truncate w-24 text-gray-500 text-sm">
                                {{ master.trader_name }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-stretch">
                        <StatusBadge value="info">
                            $ {{ formatAmount(master.minimum_investment) }}
                        </StatusBadge>
                        <StatusBadge value="gray">
                            <div v-if="master.minimum_investment_period != 0">
                                {{ master.minimum_investment_period }} {{ $t('public.months') }}
                            </div>
                            <div v-else>
                                {{ $t('public.lock_free') }}
                            </div>
                        </StatusBadge>
                        <StatusBadge value="gray">
                            {{ master.performance_fee||master.performance_fee == 0 ? formatAmount(master.performance_fee, 0)+'%' : $t('public.zero') }} {{ $t('public.fee') }}
                        </StatusBadge>
                    </div>

                    <div class="py-2 flex justify-center items-center gap-2 self-stretch border-y border-solid border-gray-200">
                        <div class="w-full flex flex-col items-center">
                            <div class="self-stretch text-gray-950 text-center font-semibold">
                                {{ master.total_gain }}%
                            </div>
                            <div class="self-stretch text-gray-500 text-center text-xs">
                                {{ $t('public.total_gain') }}
                            </div>
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <div class="self-stretch text-gray-950 text-center font-semibold">
                                {{ master.monthly_gain }}%
                            </div>
                            <div class="self-stretch text-gray-500 text-center text-xs">
                                {{ $t('public.monthly_gain') }}
                            </div>
                        </div>
                        <div class="w-full flex flex-col items-center">
                            <div class="self-stretch text-center font-semibold">
                                <div
                                    v-if="master.latest_profit != 0"
                                    :class="(master.latest_profit < 0) ? 'text-error-500' : 'text-success-500'"
                                >
                                    {{ master.latest_profit }}
                                </div>
                                <div
                                    v-else
                                    class="text-gray-950"
                                >
                                    -
                                </div>
                            </div>
                            <div class="self-stretch text-gray-500 text-center text-xs">
                                {{ $t('public.lastest') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-1 self-stretch">
                        <div class="py-1 flex items-center gap-3 self-stretch">
                            <IconUserDollar size="20" stroke-width="1.25" />
                            <div class="w-full text-gray-950 text-sm font-medium">
                                {{ master.total_investors }} {{ $t('public.investors') }}
                            </div>
                        </div>
                        <div class="py-1 flex items-center gap-3 self-stretch">
                            <IconPremiumRights size="20" stroke-width="1.25" />
                            <div class="w-full text-gray-950 text-sm font-medium">
                                {{ $t('public.total_fund_size_caption') }}
                                <span class="text-primary-500">$ {{ formatAmount(master.total_fund) }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div v-else>
                loading
            </div>
        </div>
    </AuthenticatedLayout>
</template>
