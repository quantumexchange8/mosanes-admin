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

const cashWalletData = {
    amount: 18270,
};

const rebateWalletData = {
    amount: 0,
};

</script>

<template>
    <!-- below xs -->
    <div class="flex flex-col gap-5 md:hidden">
        <div class="flex flex-col justify-center items-start px-6 py-5 gap-5 self-stretch rounded-2xl bg-primary-500">
            <div class="flex justify-between items-start self-stretch">
                <div class="w-10 h-10 p-2.5 flex justify-center items-center rounded-lg bg-primary-25 text-primary-500">
                    <IconWallet size="20" stroke-width="1.25" />
                </div>
                <WalletAdjustment type="cash" :cashWallet="cashWalletData" />
            </div>
            <div class="flex flex-col justify-center items-start px-5 py-4 gap-2 self-stretch bg-white bg-opacity-30">
                <div class="text-gray-100 text-xs font-medium">Cash Wallet Balance</div>
                <div class="text-white text-xl font-semibold">$ 18,270.00</div>
            </div>
        </div>
        <div class="self-stretch relative">            
            <div class="flex flex-col justify-center items-start px-6 py-5 gap-5 rounded-2xl bg-purple relative z-0">
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
        <div class="flex flex-col items-start h-[420px] px-5 py-6 gap-3 self-stretch rounded-2xl bg-white shadow-toast">
            <div class="flex justify-center items-center self-stretch">
                <div class="flex flex-col justify-center items-start pb-3 gap-3 w-1/2">
                    <DepositIcon class="w-[42px] h-[42px] relative" />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="self-stretch text-gray-500 text-xs">Total Deposit</div>
                        <div class="self-stretch text-gray-950 font-semibold">$ 10,000.00</div>
                    </div>
                </div>
                <div class="flex flex-col justify-center items-start pb-3 gap-3 w-1/2">
                    <WithdrawalIcon class="w-[42px] h-[42px] relative" />
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="self-stretch text-gray-500 text-xs">Total Withdrawal</div>
                        <div class="self-stretch text-gray-950 font-semibold">$ 2,100.00</div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-start flex-1 self-stretch overflow-auto" style="-ms-overflow-style: none; scrollbar-width: none;">
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
                        class="max-h-11 flex py-3 items-center gap-5 self-stretch border-b border-gray-200" 
                        :class="{ 'border-transparent': index === transactions.length - 1 }"
                    >
                        <div class="flex flex-col justify-center flex-1 text-gray-500 text-xs">{{ transaction.timestamp }}</div>
                        <div class="w-[120px] truncate text-right text-sm font-semibold">$ {{ transaction.amount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- between xs to xl -->
    <div class="hidden md:flex xl:hidden flex-col gap-5">
        <div class="flex justify-center items-center gap-5 self-stretch">
            <!-- Cash Wallet Section -->
            <div class="flex flex-col px-6 py-5 justify-center items-start gap-5 flex-1 rounded-2xl bg-primary-500 relative z-0">
                <div class="flex justify-between items-start self-stretch">
                    <div class="w-10 h-10 p-2.5 flex justify-center items-center rounded-lg bg-primary-25 text-primary-500">
                        <IconWallet size="20" stroke-width="1.25" />
                    </div>
                    <WalletAdjustment type="cash" :cashWallet="cashWalletData" />
                </div>
                <div class="flex flex-col justify-center items-start px-5 py-4 gap-2 self-stretch bg-white bg-opacity-30">
                    <div class="text-gray-100 text-xs font-medium">Cash Wallet Balance</div>
                    <div class="text-white text-xxl font-semibold">$ 18,270.00</div>
                </div>
            </div>

            <!-- Rebate Wallet Section -->
            <div class="relative flex flex-col justify-center items-start px-6 py-5 gap-5 flex-1 rounded-2xl bg-purple">
                <div class="flex justify-between items-start self-stretch">
                    <div class="w-10 h-10 p-2.5 flex justify-center items-center rounded-lg bg-[#FAFAFF] text-purple">
                        <IconWallet size="20" stroke-width="1.25" />
                    </div>
                    <WalletAdjustment type="rebate" :rebateWallet="rebateWalletData" />
                </div>
                <div class="flex flex-col justify-center items-start px-5 py-4 gap-2 self-stretch bg-white bg-opacity-30">
                    <div class="text-gray-100 text-xs font-medium">Rebate Wallet Balance</div>
                    <div class="text-white text-xxl font-semibold">$ 0.00</div>
                </div>
                <!-- Locked Section -->
                <div class="absolute inset-0 flex flex-col justify-center items-center rounded-2xl shadow-input backdrop-blur-sm z-10">
                    <LockedIcon class="w-[100px] h-[100px] flex-shrink-0" />
                    <div class="text-gray-950 text-center text-sm font-semibold">Rebate wallet is only available for agent.</div>
                </div>
            </div>
        </div>
        <div class="flex flex-col min-w-[400px] h-[400px] px-8 py-6 items-start gap-3 self-stretch rounded-2xl bg-white shadow-toast border border-gray-200 truncate">
            <div class="flex justify-center items-center gap-5 self-stretch">
                <div class="flex py-3 items-center gap-4 flex-1">
                    <DepositIcon class="w-[42px] h-[42px] relative" />
                    <div class="flex flex-col items-start gap-1 w-full">
                        <div class="self-stretch text-gray-500 text-xs">Total Deposit</div>
                        <div class="self-stretch text-lg text-gray-950 font-semibold">$ 10,000.00</div>
                    </div>
                </div>
                <div class="flex py-3 items-center gap-4 flex-1">
                    <WithdrawalIcon class="w-[42px] h-[42px] relative" />
                    <div class="flex flex-col items-start gap-1 w-full">
                        <div class="self-stretch text-gray-500 text-xs">Total Withdrawal</div>
                        <div class="self-stretch text-lg text-gray-950 font-semibold">$ 2,100.00</div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-start flex-1 self-stretch overflow-auto" style="-ms-overflow-style: none; scrollbar-width: none;">
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
                        class="max-h-11 flex py-3 items-center gap-5 self-stretch border-b border-gray-200" 
                        :class="{ 'border-transparent': index === transactions.length - 1 }"
                    >
                        <div class="flex flex-col justify-center flex-1 text-gray-500 text-xs">{{ transaction.timestamp }}</div>
                        <div class="w-[120px] truncate text-right text-sm font-semibold">$ {{ transaction.amount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- above xl -->
    <div class="hidden xl:flex flex-col gap-5">
        <div class="flex max-w-[1440px] max-h-fit items-start gap-5 self-stretch">
            <div class="flex flex-col items-center gap-5 flex-grow">
                <div class="flex flex-col justify-center items-start px-6 py-5 gap-5 self-stretch rounded-2xl bg-primary-500">
                    <div class="flex justify-between items-start self-stretch">
                        <div class="w-10 h-10 p-2.5 flex justify-center items-center rounded-lg bg-primary-25 text-primary-500">
                            <IconWallet size="20" stroke-width="1.25" />
                        </div>
                        <WalletAdjustment type="cash" :cashWallet="cashWalletData" />
                    </div>
                    <div class="flex flex-col justify-center items-start px-5 py-4 gap-2 self-stretch bg-white bg-opacity-30">
                        <div class="text-gray-100 text-xs font-medium">Cash Wallet Balance</div>
                        <div class="text-white text-xxl font-semibold">$ 18,270.00</div>
                    </div>
                </div>
                <div class="self-stretch relative">
                    <div class="flex flex-col justify-center items-start px-6 py-5 gap-5 rounded-2xl bg-purple relative z-0">
                        <div class="flex justify-between items-start self-stretch">
                            <div class="w-10 h-10 p-2.5 flex justify-center items-center rounded-lg bg-[#FAFAFF] text-purple">
                                <IconWallet size="20" stroke-width="1.25" />
                            </div>
                            <WalletAdjustment type="rebate" :rebateWallet="rebateWalletData" />
                        </div>
                        <div class="flex flex-col justify-center items-start px-5 py-4 gap-2 self-stretch bg-white bg-opacity-30">
                            <div class="text-gray-100 text-xs font-medium">Rebate Wallet Balance</div>
                            <div class="text-white text-xxl font-semibold">$ 0.00</div>
                        </div>
                    </div>
                    <!-- Locked Section -->
                    <div class="absolute inset-0 flex flex-col justify-center items-center rounded-2xl shadow-input backdrop-blur-sm z-10">
                        <LockedIcon class="w-[100px] h-[100px] flex-shrink-0" />
                        <div class="text-gray-950 text-center text-sm font-semibold">Rebate wallet is only available for agent.</div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col min-w-[540px] max-h-[420px] px-8 py-6 items-start gap-3 flex-grow self-stretch rounded-2xl bg-white shadow-toast">
                <div class="flex justify-center items-center gap-5 self-stretch">
                    <div class="flex py-3 items-center gap-4 flex-1">
                        <DepositIcon class="w-[42px] h-[42px] relative" />
                        <div class="flex flex-col items-start gap-1 w-full">
                            <div class="self-stretch text-gray-500 text-xs">Total Deposit</div>
                            <div class="self-stretch text-lg text-gray-950 font-semibold">$ 10,000.00</div>
                        </div>
                    </div>
                    <div class="flex py-3 items-center gap-4 flex-1">
                        <WithdrawalIcon class="w-[42px] h-[42px] relative" />
                        <div class="flex flex-col items-start gap-1 w-full">
                            <div class="self-stretch text-gray-500 text-xs">Total Withdrawal</div>
                            <div class="self-stretch text-lg text-gray-950 font-semibold">$ 2,100.00</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-start flex-1 self-stretch overflow-auto" style="-ms-overflow-style: none; scrollbar-width: none;">
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
                            class="max-h-11 flex py-3 items-center gap-5 self-stretch border-b border-gray-200" 
                            :class="{ 'border-transparent': index === transactions.length - 1 }"
                        >
                            <div class="flex flex-col justify-center flex-1 text-gray-500 text-xs">{{ transaction.timestamp }}</div>
                            <div class="w-[120px] truncate text-right text-sm font-semibold">$ {{ transaction.amount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
