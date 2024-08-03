<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IllustrationGreetings from '@/Pages/Dashboard/Partials/IllustrationGreetings.vue';
import Button from '@/Components/Button.vue';
import { IconRefresh, IconChevronRight, IconChevronDown } from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import { DepositIcon, WithdrawalIcon, RebateIcon } from '@/Components/Icons/solid';
import Badge from '@/Components/Badge.vue';
import Vue3Autocounter from 'vue3-autocounter';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const { formatAmount } = transactionFormat();

const counterDuration = ref(0.3);
const balance = ref(9879879.61)
const equity = ref(945789.39)
const pendingWithdrawal = ref(16976.96)
const netAsset = ref(768709.42)
const totalDeposit = ref(1802283.9)
const totalWithdrawal = ref(937270.2)
const totalRebate = ref(96304.28);

const pendingWithdrawalCount = ref(6)
const counterEquity = ref(null);
const counterBalance = ref(null);

const updateBalEquity = () => {
    counterEquity.value.reset();
    counterBalance.value.reset();
}
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

                    <div class="flex items-center gap-3 self-stretch">
                        <div class="flex flex-col items-start gap-2 flex-1">
                            <div class="self-stretch text-gray-500 text-xs md:text-sm">
                                {{ $t('public.balance') }} ($)
                            </div>
                            <div class="text-gray-950 text-lg md:text-xl font-semibold">
                                <vue3-autocounter ref="counterBalance" :startAmount="0" :endAmount="balance" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                            </div>
                        </div>

                        <div class="w-px h-14 rounded-xl bg-gray-300"></div>

                        <div class="flex flex-col items-end gap-2 flex-1">
                            <div class="self-stretch text-gray-500 text-right text-xs md:text-sm">
                                {{ $t('public.equity') }} ($)
                            </div>
                            <div class="text-gray-950 text-lg md:text-xl font-semibold">
                                <vue3-autocounter ref="counterEquity" :startAmount="0" :endAmount="equity" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold text-sm md:text-base">
                            {{ $t('public.pending_withdrawal') }} ($)
                        </div>
                        <Button
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            v-slot="{ iconSizeClasses }"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                        </Button>
                    </div>

                    <div class="self-stretch text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="pendingWithdrawal" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
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
            </div>

            <div class="w-full h-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-5 flex-1 self-stretch rounded-2xl bg-white shadow-toast">
                    <div class="flex items-start gap-5 self-stretch">
                        <div class="flex flex-col items-start flex-1">
                            <div class="py-2 text-gray-500 text-sm">
                                {{ $t('public.net_asset') }} ($)
                            </div>
                            <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="netAsset" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                            </div>
                        </div>
                        <Button
                            variant="gray-tonal"
                            size="sm"
                            type="button"
                            v-slot="{ iconSizeClasses }"
                        >
                            <div class="pr-2 text-gray-950">06/2024</div>
                            <IconChevronDown size="16" stroke-width="1.25" color="#667085" />
                        </Button>
                    </div>

                    <div class="flex flex-col justify-center items-center gap-4 self-stretch">
                        <div class="py-3 flex items-center gap-4 self-stretch rounded-xl">
                            <DepositIcon />

                            <div class="flex flex-col items-start gap-1 flex-1">
                                <div class="self-stretch text-gray-500 text-xs">
                                    {{ $t('public.total_deposit') }} ($)
                                </div>
                                <div class="self-stretch text-gray-950 text-base md:text-lg font-semibold">
                                    <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalDeposit" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                                </div>
                            </div>
                            <Button
                                variant="gray-outlined"
                                size="sm"
                                type="button"
                            >
                                {{ $t('public.details') }}
                            </Button>
                        </div>

                        <div class="py-3 flex items-center gap-4 self-stretch rounded-xl">
                            <WithdrawalIcon />

                            <div class="flex flex-col items-start gap-1 flex-1">
                                <div class="self-stretch text-gray-500 text-xs">
                                    {{ $t('public.total_withdrawal') }} ($)
                                </div>
                                <div class="self-stretch text-gray-950 text-base md:text-lg font-semibold">
                                    <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalWithdrawal" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                                </div>
                            </div>
                            <Button
                                variant="gray-outlined"
                                size="sm"
                                type="button"
                            >
                                {{ $t('public.details') }}
                            </Button>
                        </div>

                        <div class="py-3 flex items-center gap-4 self-stretch rounded-xl">
                            <RebateIcon />

                            <div class="flex flex-col items-start gap-1 flex-1">
                                <div class="self-stretch text-gray-500 text-xs">
                                    {{ $t('public.total_rebate_payout') }} ($)
                                </div>
                                <div class="self-stretch text-gray-950 text-base md:text-lg font-semibold">
                                    <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalRebate" :duration="counterDuration" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                                </div>
                            </div>
                            <Button
                                variant="gray-outlined"
                                size="sm"
                                type="button"
                            >
                                {{ $t('public.details') }}
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="p-4 md:py-6 md:px-8 flex flex-col gap-5 flex-1 self-stretch items-stretch rounded-2xl bg-white shadow-toast">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold">
                            {{ $t('public.recent_posts') }}
                        </div>
                        <Button
                            variant="gray-text"
                            size="sm"
                            type="button"
                            iconOnly
                            v-slot="{ iconSizeClasses }"
                        >
                            <IconChevronRight size="16" stroke-width="1.25" color="#667085" />
                        </Button>
                    </div>

                    <div class="h-[300px] overflow-y-auto">
                        <div class="flex flex-col items-center gap-4 flex-1">
                            <template v-for="index in 5" :key="index">
                                <div class="flex flex-col items-start gap-1 self-stretch">
                                    <div class="flex justify-between items-start self-stretch">
                                        <div class="flex items-start gap-3">
                                            <div class="w-7 h-7 rounded-full overflow-hidden">
                                                <img src="https://cdn.vox-cdn.com/thumbor/VlPF8UuUKoUHFtiebdDsQpW1zYs=/1400x1400/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/9632107/mario.jpg" alt="">
                                            </div>
                                            <div class="flex flex-col justify-center items-start">
                                                <div class="text-gray-950 text-xs font-bold">
                                                    Mosanes Admin {{ index }}
                                                </div>
                                                <div class="text-gray-500 text-xs">
                                                    @display name
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-gray-700 text-right text-xs">
                                            13:52
                                        </div>
                                    </div>
                                    <div class="pl-10 flex flex-col items-start self-stretch">
                                        <div class="w-14">
                                            <img src="https://media.geeksforgeeks.org/wp-content/uploads/20230418085545/1.png" alt="">
                                        </div>
                                        <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs font-semibold">
                                            Lorem ipsum, dolor sit amet consectetur
                                        </div>
                                        <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique sit, ducimus cupiditate praesentium aliquid alias perferendis nesciunt repellat cum incidunt? Ullam distinctio enim repellendus? Quia rem accusantium ratione dignissimos est.
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
