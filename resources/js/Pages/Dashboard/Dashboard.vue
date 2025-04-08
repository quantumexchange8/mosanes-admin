<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IllustrationGreetings from '@/Pages/Dashboard/Partials/IllustrationGreetings.vue';
import Button from '@/Components/Button.vue';
import {IconChevronRight} from '@tabler/icons-vue';
import DataOverview from './Partials/DataOverview.vue';
import AccountData from './Partials/AccountData.vue';
import TradeLotVolume from './Partials/TradeLotVolume.vue';
import GroupOverview from './Partials/GroupOverview.vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    postCounts: Number,
    groupMonths: Array,
})

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

            <DataOverview/>
            
            <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
                <div class="p-4 md:py-6 md:px-8 flex flex-col gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white cursor-pointer"
                    @click="router.visit(route('member.account_listing'))"
                >
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
                <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white cursor-pointer"
                    @click="router.visit(route('member.forum'))"
                >
                    <div class="flex items-center gap-6 w-full">
                        <div class="flex flex-col">
                            <div class="text-gray-950 font-semibold text-sm md:text-base">
                                {{ $t('public.edit_forum') }}
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

            <AccountData/>

            <TradeLotVolume/>

            <GroupOverview/>
        </div>
    </AuthenticatedLayout>
</template>
