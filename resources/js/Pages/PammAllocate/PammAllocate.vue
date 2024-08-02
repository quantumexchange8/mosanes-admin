<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, watchEffect } from "vue";
import { transactionFormat } from "@/Composables/index.js";
import { usePage } from "@inertiajs/vue3";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import StatusBadge from "@/Components/StatusBadge.vue";

const items = ref([
    { id: 1, name: 'Alice Johnson', value: '$120' },
    { id: 2, name: 'Bob Smith', value: '$150' },
    { id: 3, name: 'Charlie Brown', value: '$200' },
])

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
            <div class="grid grid-cols-1 gap-5 self-stretch md:grid-cols-2 md:content-center">
                <div class="flex flex-col items-center p-6 gap-4 rounded-2xl bg-white shadow-toast">
                    <div class="flex items-start gap-4 self-stretch">
                        <div class="w-[42px] h-[42px] rounded-full overflow-hidden grow-0 shrink-0">
                            <DefaultProfilePhoto />
                        </div>
                        <div class="flex flex-col items-start">
                            <span class="self-stretch overflow-hidden text-gray-950 text-ellipsis font-bold">{{ items[0].name }}</span>
                            <span class="self-stretch overflow-hidden text-gray-500 text-ellipsis text-sm">{{ items[0].name }}</span>
                        </div>
                        <!-- action -->
                    </div>
                    <div class="flex items-center gap-2 self-stretch">
                        <StatusBadge value="info">
                            {{ 'test' }}
                        </StatusBadge>
                        <StatusBadge value="gray">
                            {{ 'test' }}
                        </StatusBadge>
                        <StatusBadge value="gray">
                            {{ 'test' }}
                        </StatusBadge>
                    </div>
                    <div class="flex justify-center items-center py-2 gap-2 self-stretch border-y border-gray-200">
                        <div class="flex flex-col items-center"></div>
                        <div class="flex flex-col items-center"></div>
                        <div class="flex flex-col items-center"></div>
                    </div>
                    <div class="flex items-end gap-5 self-stretch">
                        <div class="flex flex-col items-center gap-1"></div>
                        <div class="flex items-center gap-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
