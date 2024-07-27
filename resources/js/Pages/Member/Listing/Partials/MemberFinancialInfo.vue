<script setup>
import { IconWallet } from '@tabler/icons-vue';
import { LockedIcon, DepositIcon, WithdrawalIcon } from '@/Components/Icons/solid';
import { ref } from "vue";
import Empty from '@/Components/Empty.vue';
import WalletAdjustment from '@/Pages/Member/Listing/Partials/WalletAdjustment.vue'

const hasData = ref(false);
const transactions = ref([
    { timestamp: '2024/06/25 21:39:24', amount: 1500.00 },
    { timestamp: '2024/06/12 20:39:39', amount: 100.00 },
    { timestamp: '2024/05/29 19:04:13', amount: 300.00 },
    { timestamp: '2024/05/01 12:41:58', amount: 600.00 },
    { timestamp: '2024/03/18 16:39:56', amount: 800.00 },
    { timestamp: '2024/03/18 16:39:56', amount: 800.00 },
    { timestamp: '2024/03/18 16:39:56', amount: 800.00 },
    { timestamp: '2024/03/18 16:39:56', amount: 800.00 },
    { timestamp: '2024/03/18 16:39:56', amount: 800.00 },
    { timestamp: '2024/03/18 16:39:56', amount: 800.00 },
    { timestamp: '2024/02/03 01:02:37', amount: 200.00 }
]);

const rebateWalletData = {
    amount: 0,
};

</script>

<template>
    <div class="flex flex-col xl:flex-row items-start gap-5 self-stretch">
        <div class="flex flex-col md:flex-row xl:flex-col gap-5 w-full xl:max-w-[438px]">
            <!-- Overview -->
            <div class="flex flex-row md:flex-col xl:flex-row items-center gap-5 self-stretch w-full">
                <div class="py-5 px-6 flex flex-col md:flex-row xl:flex-col gap-5 items-start w-full bg-white shadow-toast rounded-2xl">
                    <DepositIcon class="w-[42px] h-[42px]" />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="text-gray-500 text-xs max-w-[90px] truncate">Total Deposit</div>
                        <div class="md:text-lg text-gray-950 font-semibold truncate">$ 10,000.00</div>
                    </div>
                </div>
                <div class="py-5 px-6 flex flex-col md:flex-row xl:flex-col gap-5 items-start w-full bg-white shadow-toast rounded-2xl">
                    <WithdrawalIcon class="w-[42px] h-[42px]" />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="text-gray-500 text-xs max-w-[90px] truncate">Total Withdrawal</div>
                        <div class="md:text-lg text-gray-950 font-semibold truncate">$ 10,000.00</div>
                    </div>
                </div>
            </div>

            <!-- Wallet -->
            <div class="w-full xl:w-[438px] h-[200px] self-stretch relative">
                <div class="flex flex-col justify-center items-start px-6 py-5 gap-5 rounded-2xl bg-logo relative z-0 h-[200px]">
                    <div class="flex justify-between items-start self-stretch">
                        <div class="w-10 h-10 p-2.5 flex justify-center items-center rounded-lg bg-[#FAFAFF] text-purple">
                            <IconWallet size="20" stroke-width="1.25" />
                        </div>
                        <WalletAdjustment type="rebate" :rebateWallet="rebateWalletData" />
                    </div>
                    <div class="flex flex-col justify-center items-start px-5 py-4 gap-2 self-stretch bg-white bg-opacity-30">
                        <div class="text-gray-100 text-xs font-medium">Rebate Wallet Balance</div>
                        <div class="text-white text-xl font-semibold">$ 0.00</div>
                    </div>
                </div>
                <!-- Locked Section -->
                <div class="absolute inset-0 flex flex-col justify-center items-center rounded-2xl shadow-input backdrop-blur-sm z-10">
                    <LockedIcon class="w-[100px] h-[100px] flex-shrink-0" />
                    <div class="text-gray-950 text-center text-sm font-semibold">Rebate wallet is only available for agent.</div>
                </div>
            </div>
        </div>
        <div class="bg-white flex flex-col p-4 md:py-6 md:px-8 w-full shadow-toast rounded-2xl max-h-[360px] md:max-h-[372px]">
            <div class="flex py-2 items-center self-stretch">
                <div class="text-gray-950 text-sm font-bold">Recent Transaction</div>
            </div>
            <div v-if="hasData" class="flex flex-col items-center flex-1 self-stretch">
                <Empty message="No Transactions Yet" />
            </div>
            <div v-else class="flex flex-col items-center flex-1 self-stretch overflow-auto" style="-ms-overflow-style: none; scrollbar-width: none;">
                <div
                    v-for="(transaction, index) in transactions"
                    :key="index"
                    class="flex py-2 items-center gap-5 self-stretch border-b border-gray-200"
                    :class="{ 'border-transparent': index === transactions.length - 1 }"
                >
                    <div class="flex flex-col items-start justify-center gap-1 w-full">
                        <span class="text-gray-950 text-sm font-semibold">8002913</span>
                        <span class="text-gray-500 text-xs"> {{ transaction.timestamp }}</span>
                    </div>
                    <div class="w-[120px] truncate text-right font-semibold">$ {{ transaction.amount }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
