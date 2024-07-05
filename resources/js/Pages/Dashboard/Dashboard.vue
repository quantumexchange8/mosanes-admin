<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IllustrationGreetings from '@/Pages/Dashboard/Partials/IllustrationGreetings.vue';
import Button from '@/Components/Button.vue';
import { IconRefresh, IconChevronRight, IconChevronDown } from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import { DepositIcon, WithdrawalIcon, RebateIcon } from '@/Components/Icons/solid';

const { formatAmount } = transactionFormat();

</script>

<template>
    <AuthenticatedLayout title="Dashboard">
        <div class="w-full flex flex-col items-center gap-5">
            <div 
                class="w-full h-36 py-6 pl-4 md:p-8 flex justify-between self-stretch rounded-2xl bg-primary-100 shadow-[0_4px_20px_0_rgba(12,17,29,0.08)] relative"
                style="background-image: url('/img/background-greetings.png'); background-repeat: repeat; background-size: contain;"
            >
                <div class="w-3/4 md:w-full flex flex-col items-start gap-1">
                    <div class="self-stretch text-primary-900 text-base md:text-xxl font-bold">
                        Welcome Back, Mosanes Admin!
                    </div>
                    <div class="self-stretch text-primary-900 text-xs md:text-base">
                        Ready to grow your trading community? Let's get started!
                    </div>
                </div>

                <div class="absolute right-0 bottom-0">
                    <IllustrationGreetings />
                </div>
            </div>

            <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-center gap-8 flex-1 self-stretch rounded-2xl shadow-[0_4px_20px_0_rgba(12,17,29,0.08)] bg-white">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold">
                            Account Balance and Equity
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
                    </div>

                    <div class="flex items-center gap-3 self-stretch">
                        <div class="flex flex-col items-start gap-2 flex-1">
                            <div class="self-stretch text-gray-500 text-xs md:text-sm">
                                Balance ($)
                            </div>
                            <div class="text-gray-950 text-lg md:text-xl font-semibold">
                                {{ formatAmount(100000) }}
                            </div>
                        </div>
                        
                        <div class="w-px h-14 rounded-xl bg-gray-300"></div>

                        <div class="flex flex-col items-end gap-2 flex-1">
                            <div class="self-stretch text-gray-500 text-right text-xs md:text-sm">
                                Equity ($)
                            </div>
                            <div class="text-gray-950 text-lg md:text-xl font-semibold">
                                {{ formatAmount(30000) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-[0_4px_20px_0_rgba(12,17,29,0.08)] bg-white">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold text-sm md:text-base">
                            Pending Withdrawal ($)
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
                        {{ formatAmount(16976.96) }}
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- badge -->
                        <div class="text-xs md:text-sm bg-info-400 p-0.5 text-white">
                            6
                        </div>
                        <div class="text-gray-500 text-xs md:text-sm">
                            accounts request to withdraw
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full h-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-5 flex-1 self-stretch rounded-2xl bg-white shadow-[0_4px_20px_0_rgba(12,17,29,0.08)]">
                    <div class="flex items-start gap-5 self-stretch">
                        <div class="flex flex-col items-start flex-1">
                            <div class="py-2 text-gray-500 text-sm">
                                Net Asset ($)
                            </div>
                            <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                                {{ formatAmount(768709.42) }}
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
                                    Total Deposit ($)
                                </div>
                                <div class="self-stretch text-gray-950 text-base md:text-lg font-semibold">
                                    {{ formatAmount(1802283.90) }}
                                </div>
                            </div>
                            <Button
                                variant="gray-outlined"
                                size="sm"
                                type="button"
                            >
                                Details
                            </Button>
                        </div>

                        <div class="py-3 flex items-center gap-4 self-stretch rounded-xl">
                            <WithdrawalIcon />

                            <div class="flex flex-col items-start gap-1 flex-1">
                                <div class="self-stretch text-gray-500 text-xs">
                                    Total Withdrawal ($)
                                </div>
                                <div class="self-stretch text-gray-950 text-base md:text-lg font-semibold">
                                    {{ formatAmount(937270.20) }}
                                </div>
                            </div>
                            <Button
                                variant="gray-outlined"
                                size="sm"
                                type="button"
                            >
                                Details
                            </Button>
                        </div>

                        <div class="py-3 flex items-center gap-4 self-stretch rounded-xl">
                            <RebateIcon />

                            <div class="flex flex-col items-start gap-1 flex-1">
                                <div class="self-stretch text-gray-500 text-xs">
                                    Total Rebate Payout ($)
                                </div>
                                <div class="self-stretch text-gray-950 text-base md:text-lg font-semibold">
                                    {{ formatAmount(96304.28) }}
                                </div>
                            </div>
                            <Button
                                variant="gray-outlined"
                                size="sm"
                                type="button"
                            >
                                Details
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="p-4 md:py-6 md:px-8 flex flex-col gap-5 flex-1 self-stretch items-stretch rounded-2xl bg-white shadow-[0_4px_20px_0_rgba(12,17,29,0.08)]">
                    <div class="flex items-center self-stretch">
                        <div class="flex-1 text-gray-950 font-semibold">
                            Recent Posts from Forum
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
                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <div class="flex justify-between items-start self-stretch">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-full overflow-hidden">
                                            <img src="https://cdn.vox-cdn.com/thumbor/VlPF8UuUKoUHFtiebdDsQpW1zYs=/1400x1400/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/9632107/mario.jpg" alt="">
                                        </div>
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="text-gray-950 text-xs font-bold">
                                                Mosanes Admin
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
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs font-semibold">
                                        Lorem ipsum, dolor sit amet consectetur
                                    </div>
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique sit, ducimus cupiditate praesentium aliquid alias perferendis nesciunt repellat cum incidunt? Ullam distinctio enim repellendus? Quia rem accusantium ratione dignissimos est.
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <div class="flex justify-between items-start self-stretch">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-full overflow-hidden">
                                            <img src="https://cdn.vox-cdn.com/thumbor/VlPF8UuUKoUHFtiebdDsQpW1zYs=/1400x1400/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/9632107/mario.jpg" alt="">
                                        </div>
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="text-gray-950 text-xs font-bold">
                                                Mosanes Admin
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
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs font-semibold">
                                        Lorem ipsum, dolor sit amet consectetur
                                    </div>
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique sit, ducimus cupiditate praesentium aliquid alias perferendis nesciunt repellat cum incidunt? Ullam distinctio enim repellendus? Quia rem accusantium ratione dignissimos est.
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <div class="flex justify-between items-start self-stretch">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-full overflow-hidden">
                                            <img src="https://cdn.vox-cdn.com/thumbor/VlPF8UuUKoUHFtiebdDsQpW1zYs=/1400x1400/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/9632107/mario.jpg" alt="">
                                        </div>
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="text-gray-950 text-xs font-bold">
                                                Mosanes Admin
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
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs font-semibold">
                                        Lorem ipsum, dolor sit amet consectetur
                                    </div>
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique sit, ducimus cupiditate praesentium aliquid alias perferendis nesciunt repellat cum incidunt? Ullam distinctio enim repellendus? Quia rem accusantium ratione dignissimos est.
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <div class="flex justify-between items-start self-stretch">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-full overflow-hidden">
                                            <img src="https://cdn.vox-cdn.com/thumbor/VlPF8UuUKoUHFtiebdDsQpW1zYs=/1400x1400/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/9632107/mario.jpg" alt="">
                                        </div>
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="text-gray-950 text-xs font-bold">
                                                Mosanes Admin
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
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs font-semibold">
                                        Lorem ipsum, dolor sit amet consectetur
                                    </div>
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique sit, ducimus cupiditate praesentium aliquid alias perferendis nesciunt repellat cum incidunt? Ullam distinctio enim repellendus? Quia rem accusantium ratione dignissimos est.
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <div class="flex justify-between items-start self-stretch">
                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-full overflow-hidden">
                                            <img src="https://cdn.vox-cdn.com/thumbor/VlPF8UuUKoUHFtiebdDsQpW1zYs=/1400x1400/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/9632107/mario.jpg" alt="">
                                        </div>
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="text-gray-950 text-xs font-bold">
                                                Mosanes Admin
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
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs font-semibold">
                                        Lorem ipsum, dolor sit amet consectetur
                                    </div>
                                    <div class="max-w-64 xl:max-w-sm self-stretch overflow-hidden text-gray-950 text-ellipsis whitespace-nowrap text-xs">
                                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique sit, ducimus cupiditate praesentium aliquid alias perferendis nesciunt repellat cum incidunt? Ullam distinctio enim repellendus? Quia rem accusantium ratione dignissimos est.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
