<script setup>
import Button from '@/Components/Button.vue';
import {IconChevronRight, IconCaretUpFilled} from '@tabler/icons-vue';
import { DepositIcon, WithdrawalIcon, MemberIcon, AgentIcon } from '@/Components/Icons/solid';
import Badge from '@/Components/Badge.vue';
import Vue3Autocounter from 'vue3-autocounter';
import {ref} from 'vue';

const props = defineProps({
    postCounts: Number,
    groupMonths: Array,
})

const counterDuration = ref(10);
const pendingWithdrawal = ref(0)
const totalDeposit = ref(99999.00)
const totalWithdrawal = ref(99999.00)
const totalMember = ref(999);
const totalAgent = ref(999);
const todayDeposit = ref(0);
const todayWithdrawal = ref(0);
const todayAgent = ref(0);
const todayMember = ref(0);
const pendingWithdrawalCount = ref(0);
const pendingBonus = ref(0);
const pendingBonusCount = ref(0);

const getPendingData = async () => {
    try {
        const response = await axios.get('/getPendingData');
        pendingWithdrawal.value = parseFloat(response.data.pendingWithdrawal);
        pendingWithdrawalCount.value = parseFloat(response.data.pendingWithdrawal);
        pendingBonus.value = parseFloat(response.data.pendingBonus);
        pendingBonusCount.value = parseFloat(response.data.pendingBonusCount);
    } catch (error) {
        console.error('Error pending data:', error);
    } finally {
        counterDuration.value = 1
    }
};

getPendingData();


const getDashboardData = async () => {
    try {
        const response = await axios.get('/getDashboardData');
        totalDeposit.value = Number(response.data.totalDeposit);
        totalWithdrawal.value = Number(response.data.totalWithdrawal);
        totalAgent.value = response.data.totalAgent;
        totalMember.value = response.data.totalMember;
        todayDeposit.value = response.data.todayDeposit;
        todayWithdrawal.value = response.data.todayWithdrawal;
        todayAgent.value = response.data.todayAgent;
        todayMember.value = response.data.todayMember;
    } catch (error) {
        console.error('Error pending counts:', error);
    } finally {
        counterDuration.value = 1
    }
};

getDashboardData();

</script>

<template>
    <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
        <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
            <div class="flex items-center self-stretch">
                <div class="flex-1 text-gray-950 font-semibold text-sm md:text-base">
                    {{ $t('public.dashboard_withdrawal_request') }}
                </div>
                <Button
                    external
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    :href="route('pending.withdrawal')"
                >
                    <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                </Button>
            </div>

            <div class="self-stretch text-gray-950 text-xl md:text-xxl font-semibold">
                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="pendingWithdrawal" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
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

        <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
            <div class="flex items-center self-stretch">
                <div class="flex-1 text-gray-950 font-semibold text-sm md:text-base">
                    {{ $t('public.dashboard_bonus_request') }}
                </div>
                <Button
                    external
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    :href="route('pending.bonus')"
                >
                    <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                </Button>
            </div>

            <div class="self-stretch text-gray-950 text-xl md:text-xxl font-semibold">
                <vue3-autocounter ref="counter" :startAmount="0" :endAmount="pendingBonus" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
            </div>

            <div class="flex items-center gap-2">
                <Badge class="text-white text-sm">
                    {{ pendingBonusCount }}
                </Badge>
                <div class="text-gray-500 text-xs md:text-sm">
                    {{ $t('public.pending_withdrawal_caption') }}
                </div>
            </div>
        </div>
    </div>

    <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
        <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
            <div class="flex items-center gap-6 w-full">
                <AgentIcon class="w-12 h-12" />
                <div class="flex flex-col">
                    <div class="text-gray-950 font-semibold text-sm md:text-base">
                        {{ $t('public.total_agent') }}
                    </div>
                    <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalAgent" :duration="1" separator="," decimalSeparator="." :decimals="0" :autoinit="true" />
                    </div>
                    <div class="flex items-center gap-2">
                        <IconCaretUpFilled 
                            class="w-3 h-3 md:w-4 md:h-4"
                            :class="{'text-success-500': todayAgent > 0, 'text-gray-950': todayAgent <= 0}"
                        />
                            {{ todayAgent }}

                        <div class="text-gray-500 text-xs md:text-sm">
                            {{ $t('public.today') }}
                        </div>
                    </div>
                </div>
                <Button
                    external
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    :href="route('member.listing', { user_role: 'agent' })"
                    class="ml-auto"
                >
                    <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                </Button>
            </div>
        </div>

        <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
            <div class="flex items-center gap-6 w-full">
                <MemberIcon class="w-12 h-12"/>
                <div class="flex flex-col">
                    <div class="text-gray-950 font-semibold text-sm md:text-base">
                        {{ $t('public.total_member') }}
                    </div>
                    <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalMember" :duration="1" separator="," decimalSeparator="." :decimals="0" :autoinit="true" />
                    </div>
                    <div class="flex items-center gap-2">
                        <IconCaretUpFilled 
                            class="w-3 h-3 md:w-4 md:h-4"
                            :class="{'text-success-500': todayMember > 0, 'text-gray-950': todayMember <= 0}"
                        />
                            {{ todayMember }}

                        <div class="text-gray-500 text-xs md:text-sm">
                            {{ $t('public.today') }}
                        </div>
                    </div>
                </div>
                <Button
                    external
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    :href="route('member.listing', { user_role: 'member' })"
                    class="ml-auto"
                >
                    <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                </Button>
            </div>
        </div>
    </div>

    <div class="w-full flex flex-col md:flex-row justify-center items-center gap-5 self-stretch">
        <div class="p-4 md:py-6 md:px-8 flex flex-col gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
            <div class="flex items-center gap-6 w-full">
                <DepositIcon class="w-12 h-12"/>
                <div class="flex flex-col">
                    <div class="text-gray-950 font-semibold text-sm md:text-base">
                        {{ $t('public.total_deposit') }} ($)
                    </div>
                    <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalDeposit" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                    </div>
                    <div class="flex items-center gap-2">
                        <IconCaretUpFilled 
                            class="w-3 h-3 md:w-4 md:h-4"
                            :class="{'text-success-500': todayDeposit > 0, 'text-gray-950': todayDeposit <= 0}"
                        />
                            {{ todayDeposit }}

                        <div class="text-gray-500 text-xs md:text-sm">
                            {{ $t('public.today') }}
                        </div>
                    </div>
                </div>
                <Button
                    external
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    :href="route('transaction.deposit')"
                    class="ml-auto"
                >
                    <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                </Button>
            </div>
        </div>
        <div class="p-4 md:py-6 md:px-8 flex flex-col items-start gap-4 flex-1 self-stretch rounded-2xl shadow-toast bg-white">
            <div class="flex items-center gap-6 w-full">
                <WithdrawalIcon class="w-12 h-12"/>
                <div class="flex flex-col">
                    <div class="text-gray-950 font-semibold text-sm md:text-base">
                        {{ $t('public.total_withdrawal') }} ($)
                    </div>
                    <div class="text-gray-950 text-xl md:text-xxl font-semibold">
                        <vue3-autocounter ref="counter" :startAmount="0" :endAmount="totalWithdrawal" :duration="1" separator="," decimalSeparator="." :decimals="2" :autoinit="true" />
                    </div>
                    <div class="flex items-center gap-2">
                        <IconCaretUpFilled 
                            class="w-3 h-3 md:w-4 md:h-4"
                            :class="{'text-success-500': todayWithdrawal > 0, 'text-gray-950': todayWithdrawal <= 0}"
                        />
                            {{ todayWithdrawal }}

                        <div class="text-gray-500 text-xs md:text-sm">
                            {{ $t('public.today') }}
                        </div>
                    </div>
                </div>
                <Button
                    external
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    :href="route('transaction.withdrawal')"
                    class="ml-auto"
                >
                    <IconChevronRight size="16" stroke-width="1.25" color="#667085"/>
                </Button>
            </div>
        </div>
    </div>
</template>