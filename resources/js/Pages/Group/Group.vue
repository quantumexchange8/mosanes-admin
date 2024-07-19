;<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue';
import { transactionFormat } from '@/Composables/index.js';
import { IconWallet, IconCreditCardPay, IconReceiptTax, IconUserFilled, IconRefresh, IconDotsVertical } from '@tabler/icons-vue';
import Button from '@/Components/Button.vue';
import Calendar from 'primevue/calendar';
import ProfilePhoto from "@/Components/ProfilePhoto.vue";
import NewGroup from '@/Pages/Group/Partials/NewGroup.vue';


const { formatAmount } = transactionFormat();

const totalNetBalance = ref(60340);
const totalDeposit = ref(319000);
const totalWithdrawal = ref(236000);
const totalFeeCharges = ref(22660);

const date = ref('');
const groupName = ref('ABC Group');
const groupMemberCount = ref(1658);
const groupLeaderName = ref('Alice Johnson');
const groupLeaderEmail = ref('alicealicejs@gmail.com');
const groupDeposit = ref(88000);
const groupWithdrawal = ref(64000)
const groupChargesPercent = ref(10)
const groupFeeCharges = ref(8800)
const groupNetBalance = ref(15200)
const groupAccountBalance = ref(24000)
const groupAccountEquity = ref(17000)
</script>

<template>
    <AuthenticatedLayout title="Group">
        <div class="w-full flex flex-col items-center gap-5">
            <div class="w-full p-4 md:py-6 md:px-8 flex flex-col items-center gap-5 self-stretch rounded-2xl bg-white shadow-toast">
                <div class="w-full flex flex-col justify-center items-start md:flex-row md:items-center md:gap-3 md:self-stretch">
                    <div class="flex flex-col justify-center shrink-0 self-stretch text-gray-950 text-sm font-semibold md:flex-1 md:text-base">
                        Total Net Balance ($)
                    </div>
                    <div class="self-stretch text-gray-950 text-xxl font-semibold md:flex-1 md:text-right">
                        {{ formatAmount(totalNetBalance) }}
                    </div>
                </div>

                <div class="w-full flex flex-col items-center gap-3 self-stretch md:flex-row md:gap-5">
                    <div class="py-4 px-6 flex flex-col items-center gap-2 self-stretch bg-green md:gap-3 md:flex-1">
                        <div class="self-stretch text-white text-lg font-semibold md:text-xl">
                            {{ formatAmount(totalDeposit) }}
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <IconWallet size="20" stroke-width="1.25" color="white" class="md:size-4 xl:size-5" />
                            <div class="text-white text-sm md:text-xs xl:text-sm">
                                Total Deposit ($)
                            </div>
                        </div>
                    </div>

                    <div class="py-4 px-6 flex flex-col items-center gap-2 self-stretch bg-pink md:gap-3 md:flex-1">
                        <div class="self-stretch text-white text-lg font-semibold md:text-xl">
                            {{ formatAmount(totalWithdrawal) }}
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <IconCreditCardPay size="20" stroke-width="1.25" color="white" class="md:size-4 xl:size-5" />
                            <div class="text-white text-sm md:text-xs xl:text-sm">
                                Total Withdrawal ($)
                            </div>
                        </div>
                    </div>

                    <div class="py-4 px-6 flex flex-col items-center gap-2 self-stretch bg-gray-500 md:gap-3 md:flex-1">
                        <div class="self-stretch text-white text-lg font-semibold md:text-xl">
                            {{ formatAmount(totalFeeCharges) }}
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <IconReceiptTax size="20" stroke-width="1.25" color="white" class="md:size-4 xl:size-5" />
                            <div class="text-white text-sm md:text-xs xl:text-sm">
                                Total Fee Charges ($)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full py-6 px-4 md:p-6 flex flex-col items-center gap-6 self-stretch rounded-2xl border border-solid border-gray-200 bg-white shadow-table">
                <div class="flex flex-col items-center gap-3 self-stretch md:flex-row md:justify-between">
                    <Calendar
                        v-model="date"
                        selectionMode="range"
                        :manualInput="false"
                        dateFormat="dd/mm/yy"
                        class="w-full md:w-auto"
                    />
                    <NewGroup />
                </div>

                <div class="flex flex-col items-center self-stretch">
                    <div class="py-2 px-4 flex items-center gap-3 self-stretch bg-purple">
                        <div class="flex-1 text-white font-semibold">
                            {{ groupName }}
                        </div>
                        <div class="flex items-center gap-2">
                            <IconUserFilled size="16" stroke-width="1.25" color="white" />
                            <div class="text-white text-right text-sm font-medium">
                                {{ formatAmount(groupMemberCount, 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="p-4 flex flex-col items-center gap-2 self-stretch">
                        <div class="min-w-[240px] pb-3 flex items-center gap-3 self-stretch border-b border-solid border-gray-200">
                            <div class="flex items-center gap-3 flex-1">
                                <div>
                                    <ProfilePhoto class="w-7 h-7" />
                                </div>
                                <div class="flex flex-col items-start flex-1">
                                    <div class="max-w-40 self-stretch overflow-hidden whitespace-nowrap text-gray-950 text-ellipsis text-sm font-medium md:max-w-[500px] xl:max-w-3xl">
                                        {{ groupLeaderName }} Lorem ipsum, dolor sit amet consectetur adipisicing elit. Impedit perferendis ipsa tempora assumenda maiores excepturi quod iste rerum consectetur, deleniti ex labore veritatis alias? Inventore architecto nihil explicabo soluta dicta!
                                    </div>
                                    <div class="max-w-40 self-stretch overflow-hidden whitespace-nowrap text-gray-500 text-ellipsis text-xs md:max-w-[500px] xl:max-w-3xl">
                                        {{ groupLeaderEmail }} Lorem ipsum dolor, sit amet consectetur adipisicing elit. Earum tenetur non doloribus expedita maxime inventore neque, placeat veritatis pariatur minima, praesentium quo aliquid deserunt similique ad ducimus dolorem accusamus nulla.
                                    </div>
                                </div>
                                <Button
                                    variant="gray-text"
                                    size="sm"
                                    type="button"
                                    iconOnly
                                    v-slot="{ iconSizeClasses }"
                                >
                                    <IconRefresh size="16" stroke-width="1.25" color="#667085" />
                                </Button>
                                <Button
                                    variant="gray-text"
                                    size="sm"
                                    type="button"
                                    iconOnly
                                    v-slot="{ iconSizeClasses }"
                                >
                                    <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
                                </Button>
                            </div>
                        </div>

                        <div class="py-3 grid grid-cols-2 items-start content-start gap-y-3 gap-x-5 self-stretch flex-wrap md:grid-cols-3 md:gap-2 xl:grid-cols-6">
                            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-1 md:min-w-[160px] xl:min-w-max">
                                <div class="text-gray-500 text-xs">
                                    Deposit ($)
                                </div>
                                <div class="text-gray-950 font-semibold">
                                    {{ formatAmount(groupDeposit) }}
                                </div>
                            </div>

                            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-1 md:min-w-[160px] xl:min-w-max">
                                <div class="text-gray-500 text-xs">
                                    Withdrawal ($)
                                </div>
                                <div class="text-gray-950 font-semibold">
                                    {{ formatAmount(groupWithdrawal) }}
                                </div>
                            </div>

                            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-1 md:min-w-[160px] xl:min-w-max">
                                <div class="text-gray-500 text-xs">
                                    {{ groupChargesPercent }}% Fee Charges ($)
                                </div>
                                <div class="text-gray-950 font-semibold">
                                    {{ formatAmount(groupFeeCharges) }}
                                </div>
                            </div>

                            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-1 md:min-w-[160px] xl:min-w-max">
                                <div class="text-gray-500 text-xs">
                                    Net Balance ($)
                                </div>
                                <div class="text-gray-950 font-semibold">
                                    {{ formatAmount(groupNetBalance) }}
                                </div>
                            </div>

                            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-1 md:min-w-[160px] xl:min-w-max">
                                <div class="text-gray-500 text-xs">
                                    Account Balance ($)
                                </div>
                                <div class="text-gray-950 font-semibold">
                                    {{ formatAmount(groupAccountBalance) }}
                                </div>
                            </div>

                            <div class="min-w-[100px] flex flex-col items-start gap-1 flex-1 md:min-w-[160px] xl:min-w-max">
                                <div class="text-gray-500 text-xs">
                                    Account Equity ($)
                                </div>
                                <div class="text-gray-950 font-semibold">
                                    {{ formatAmount(groupAccountEquity) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
