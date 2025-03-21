<script setup>
import Button from '@/Components/Button.vue';
import { IconRefresh, IconUserFilled} from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import { ref, watch } from 'vue';
import Dropdown from "primevue/dropdown";
import dayjs from "dayjs";

const props = defineProps({
    postCounts: Number,
    groupMonths: Array,
})

const { formatAmount } = transactionFormat();

const currentYear = dayjs().year();
const groupMonths = ref([]);
const selectedGroupMonth = ref('');

const groups = ref();
const counterGroup = ref(null);
const groupLoading = ref(false);
const groupDuration = ref(10);

for (let month = 1; month <= 12; month++) {
    groupMonths.value.push({
        value: dayjs().month(month - 1).year(currentYear).format('MM/YYYY')
    });
}

selectedGroupMonth.value = dayjs().format('MM/YYYY');

const getGroupsData = async () => {
    groupLoading.value = true;

    try {
        const response = await axios.get(`/getGroupsData?selectedMonth=${selectedGroupMonth.value}`);        

        // Process response data here
        groups.value = response.data.groups;

        groupDuration.value = 1;
    } catch (error) {
        console.error('Error fetching data:', error);
        groupLoading.value = false;
    } finally {
        groupDuration.value = 1
        groupLoading.value = false;
    }
};

getGroupsData();

// Watch for changes in selectedMonth and trigger getGroupsData
watch(selectedGroupMonth,(newGroupMonth, oldGroupMonth) => {
        if (newGroupMonth !== oldGroupMonth) {
            getGroupsData();
        }
    }
);

const updateGroupsData = () => {
    // Reset the selected month to the current month
    selectedGroupMonth.value = dayjs().format('MM/YYYY');

    // Reset the team counters if it have a reset() method
    if (counterGroup.value && counterTeam.value.reset) {
        counterGroup.value.reset();
    }
    getGroupsData();
};
</script>

<template>
    <div class="p-4 md:py-6 md:px-8 flex flex-col items-center gap-8 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
        <div class="flex items-center self-stretch">
            <div class="flex-1 text-gray-950 font-semibold">
                <Dropdown
                    v-model="selectedGroupMonth"
                    :options="groupMonths"
                    optionLabel="value"
                    optionValue="value"
                    :placeholder="$t('public.month_placeholder')"
                    scroll-height="236px"
                    :pt="{
                        root: 'inline-flex items-center justify-center relative rounded-lg bg-gray-100 px-3 py-2 gap-3 cursor-pointer overflow-hidden overflow-ellipsis whitespace-nowrap appearance-none',
                        input: 'text-sm font-medium block flex-auto relative focus:outline-none',
                        trigger: 'w-4 h-4 flex items-center justify-center shrink-0',
                    }"
                >
                </Dropdown>
            </div>
            <Button
                variant="gray-text"
                size="sm"
                type="button"
                iconOnly
                v-slot="{ iconSizeClasses }"
                @click="updateGroupsData()"
            >
                <IconRefresh size="16" stroke-width="1.25" color="#667085" />
            </Button>
        </div>

        <div class="w-full max-h-[770px] grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-1 gap-3 md:gap-6">
            <div
                v-if="groupLoading"
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
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.group_deposit') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                            </span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.group_withdrawal') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                            </span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.net_balance') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                            </span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_group_account_equity') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                            </span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_group_adjustment_in') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                            </span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_group_adjustment_out') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base animate-pulse">
                                <div class="h-3 bg-gray-200 rounded-full w-30"></div>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                v-for="group in groups"
                :key="group.id"
                class="w-full flex flex-col items-center rounded bg-white shadow overflow-hidden"
            >
                <div 
                    class="w-full flex py-1 px-3 items-center gap-3 md:py-2 md:px-4"
                    :style="{'backgroundColor': `#${group.color}`}"
                >
                    <span class="w-full text-white font-medium truncate">{{ group.name }}</span>
                    <div class="flex items-center gap-2 text-white">
                        <IconUserFilled size="20" stroke-width="1.25" />
                        <span class="text-right font-medium">{{ formatAmount(group.member_count, 0) }}</span>
                    </div>
                </div>
                <div class="w-full flex flex-col items-center p-3 gap-2 md:p-4 md:gap-3">
                    <div class="w-full grid grid-cols-3 gap-2 ">
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.group_deposit') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(group?.deposit || 0) }}</span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.group_withdrawal') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(group?.withdrawal || 0) }}</span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.net_balance') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(group?.net_balance || 0) }}</span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_group_account_equity') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(group?.account_equity || 0) }}</span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_group_adjustment_in') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(group?.adjustment_in || 0) }}</span>
                        </div>
                        <div class="w-full flex flex-col items-start gap-1">
                            <span class="w-full truncate text-gray-500 text-xxs md:text-xs">{{ $t('public.dashboard_group_adjustment_out') }}</span>
                            <span class="w-full truncate text-gray-950 font-semibold text-sm md:text-base">$&nbsp;{{ formatAmount(group?.adjustment_out || 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>