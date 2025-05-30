<script setup>
import Button from '@/Components/Button.vue';
import { IconRefresh} from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import { ref } from 'vue';

const props = defineProps({
    postCounts: Number,
    groupMonths: Array,
})

const { formatAmount } = transactionFormat();

const accountLoading = ref(false);
const accountBalanceDuration = ref(10);
const balance = ref(99999.00)
const equity = ref(99999.00)
const counterEquity = ref(null);
const counterBalance = ref(null);

const updateBalEquity = () => {
    // Reset the counters if they are properly initialized and have a reset() method
    if (counterEquity.value && counterEquity.value.reset) {
        counterEquity.value.reset();
    }
    if (counterBalance.value && counterBalance.value.reset) {
        counterBalance.value.reset();
    }
    getAccountData();
};

const getAccountData = async () => {
    accountLoading.value = true;
    try {
        const response = await axios.get('/getAccountData');
        balance.value = response.data.totalBalance;
        equity.value = response.data.totalEquity;

        accountBalanceDuration.value = 1
    } catch (error) {
        console.error('Error accounts data:', error);
        accountLoading.value = false;
    } finally {
        accountBalanceDuration.value = 1
        accountLoading.value = false;
    }
};

getAccountData();

</script>

<template>
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
</template>