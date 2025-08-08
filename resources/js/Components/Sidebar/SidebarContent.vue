<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import {onMounted, ref, watchEffect} from "vue";
import {usePage} from "@inertiajs/vue3";
import {
    IconLayoutDashboard,
    IconComponents,
    IconUserCircle,
    IconUsersGroup,
    IconReceiptDollar,
    IconId,
    IconCoinMonero,
    IconBusinessplan,
    IconClockDollar,
    IconAward,
    IconShieldCheckered,
    IconChartArrowsVertical,
} from '@tabler/icons-vue';
import { usePermission } from '@/Composables';

const { hasRole, hasPermission } = usePermission();

const pendingWithdrawals = ref(0);
const pendingPammAllocate = ref(0);
const pendingBonusWithdrawal = ref(0);
const pendingPammRequest = ref(0);
const pendingKyc = ref(0);

const getPendingCounts = async () => {
    try {
        const response = await axios.get('/getPendingCounts');
        pendingWithdrawals.value = response.data.pendingWithdrawals
        pendingPammAllocate.value = response.data.pendingPammAllocate
        pendingPammRequest.value = response.data.pendingPammRequest
        pendingBonusWithdrawal.value = response.data.pendingBonusWithdrawal
        pendingKyc.value = response.data.pendingKyc
    } catch (error) {
        console.error('Error pending counts:', error);
    }
};

onMounted(() => {
    getPendingCounts();
})

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getPendingCounts();
    }
});
</script>

<template>
    <PerfectScrollbar
        tagname="nav"
        aria-label="main"
        class="relative flex flex-col flex-1 max-h-full gap-1 px-5 py-3 items-center"
    >
        <!-- Dashboard -->
        <SidebarLink
            :title="$t('public.dashboard')"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
            v-if="hasRole('super-admin') || hasPermission('access_dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Pending -->
        <SidebarCollapsible
            :title="$t('public.pending')"
            :active="route().current('pending.*')"
            :pendingCounts="[
                pendingPammRequest,
                pendingBonusWithdrawal,
                pendingWithdrawals,
                pendingKyc
            ]"
            v-if="hasRole('super-admin') || hasPermission([
                'access_withdrawal_request',
                'access_bonus_request',
            ])"
        >
            <template #icon>
                <IconClockDollar :size="20" stroke-width="1.25" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.withdrawal')"
                :href="route('pending.withdrawal')"
                :active="route().current('pending.withdrawal')"
                :pendingCounts="pendingWithdrawals"
                v-if="hasRole('super-admin') || hasPermission('access_withdrawal_request')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.revoke_pamm')"
                :href="route('pending.revoke_pamm')"
                :active="route().current('pending.revoke_pamm')"
                :pendingCounts="pendingPammRequest"
                v-if="hasRole('super-admin') || hasPermission('access_pamm_request')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.bonus')"
                :href="route('pending.bonus')"
                :active="route().current('pending.bonus')"
                :pendingCounts="pendingBonusWithdrawal"
                v-if="hasRole('super-admin') || hasPermission('access_bonus_request')"
            />

            
            <SidebarCollapsibleItem
                :title="$t('public.kyc')"
                :href="route('pending.kyc')"
                :active="route().current('pending.kyc')"
                :pendingCounts="pendingKyc"
                v-if="hasRole('super-admin') || hasPermission('access_kyc')"
            />
        </SidebarCollapsible>

        <!-- Member -->
        <SidebarCollapsible
            :title="$t('public.member')"
            :active="route().current('member.*')"
            v-if="hasRole('super-admin') || hasPermission([
                'access_member_listing',
                'access_member_network',
                'access_member_forum',
                'access_account_listing',
            ])"
        >
            <template #icon>
                <IconComponents :size="20" stroke-width="1.25" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.member_listing')"
                :href="route('member.listing')"
                :active="route().current('member.listing') || route().current('member.detail')"
                v-if="hasRole('super-admin') || hasPermission('access_member_listing')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.member_network')"
                :href="route('member.network')"
                :active="route().current('member.network')"
                v-if="hasRole('super-admin') || hasPermission('access_member_network')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.member_forum')"
                :href="route('member.forum')"
                :active="route().current('member.forum')"
                v-if="hasRole('super-admin') || hasPermission('access_member_forum')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.community')"
                :href="route('member.community')"
                :active="route().current('member.community') || route().current('member.communityProfile')"
                v-if="hasRole('super-admin') || hasPermission('access_member_forum')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.account_listing')"
                :href="route('member.account_listing')"
                :active="route().current('member.account_listing')"
                v-if="hasRole('super-admin') || hasPermission('access_account_listing')"
            />

        </SidebarCollapsible>

        <!-- Group -->
        <SidebarLink
            :title="$t('public.group')"
            :href="route('group')"
            :active="route().current('group')"
            v-if="hasRole('super-admin') || hasPermission('access_sales_team')"
        >
            <template #icon>
                <IconUsersGroup :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Pamm Allocate -->
        <SidebarLink
            :title="$t('public.pamm_allocate')"
            :href="route('pamm_allocate')"
            :active="route().current('pamm_allocate')"
            :pendingCounts="pendingPammAllocate"
            v-if="hasRole('super-admin') || hasPermission('access_pamm')"
        >
            <template #icon>
                <IconCoinMonero :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Rebate Allocate -->
        <SidebarLink
            :title="$t('public.rebate_allocate')"
            :href="route('rebate_allocate')"
            :active="route().current('rebate_allocate')"
            v-if="hasRole('super-admin') || hasPermission('access_rebate_setting')"
        >
            <template #icon>
                <IconBusinessplan :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Billboard -->
        <SidebarLink
            :title="$t('public.billboard')"
            :href="route('billboard')"
            :active="route().current('billboard')"
            v-if="hasRole('super-admin') || hasPermission('access_billboard')"
        >
            <template #icon>
                <IconAward :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Transaction -->
        <SidebarCollapsible
            :title="$t('public.transaction')"
            :active="route().current('transaction.*')"
            v-if="hasRole('super-admin') || hasPermission([
                'access_deposit',
                'access_withdrawal',
                'access_transfer',
                'access_rebate_payout',
            ])"
        >
            <template #icon>
                <IconReceiptDollar :size="20" stroke-width="1.25" />
            </template>

            <SidebarCollapsibleItem
                :title="$t('public.deposit')"
                :href="route('transaction.deposit')"
                :active="route().current('transaction.deposit')"
                v-if="hasRole('super-admin') || hasPermission('access_deposit')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.withdrawal')"
                :href="route('transaction.withdrawal')"
                :active="route().current('transaction.withdrawal')"
                v-if="hasRole('super-admin') || hasPermission('access_withdrawal')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.transfer')"
                :href="route('transaction.transfer')"
                :active="route().current('transaction.transfer')"
                v-if="hasRole('super-admin') || hasPermission('access_transfer')"
            />

            <SidebarCollapsibleItem
                :title="$t('public.payout')"
                :href="route('transaction.payout')"
                :active="route().current('transaction.payout')"
                v-if="hasRole('super-admin') || hasPermission('access_rebate_payout')"
            />

        </SidebarCollapsible>

        <!-- Broker P&L -->
        <SidebarLink
            :title="$t('public.broker_pnl')"
            v-if="hasRole('super-admin') || hasPermission('access_broker_pnl')"

        >
            <template #icon>
                <IconChartArrowsVertical :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Account Type -->
        <SidebarLink
            :title="$t('public.account_type')"
            :href="route('accountType')"
            :active="route().current('accountType')"
            v-if="hasRole('super-admin') || hasPermission('access_account_type')"
        >
            <template #icon>
                <IconId :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Components -->
<!--        <SidebarCollapsible-->
<!--            title="Components"-->
<!--            :active="route().current('components.*')"-->
<!--        >-->
<!--            <template #icon>-->
<!--                <IconComponents :size="20" stroke-width="1.25" />-->
<!--            </template>-->

<!--            <SidebarCollapsibleItem-->
<!--                title="Buttons"-->
<!--                :href="route('components.buttons')"-->
<!--                :active="route().current('components.buttons')"-->
<!--            />-->

<!--            <SidebarCollapsibleItem-->
<!--                title="Member Network"-->
<!--                :href="route('member.network')"-->
<!--                :active="route().current('member.network')"-->
<!--            />-->
<!--        </SidebarCollapsible>-->


        <!-- Admin Role -->
        <SidebarLink
            :title="$t('public.admin_role')"
            :href="route('adminRole')"
            :active="route().current('adminRole')"
            v-if="hasRole('super-admin') || hasPermission('access_admin_role')"
        >
            <template #icon>
                <IconShieldCheckered :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Profile -->
        <SidebarLink
            :title="$t('public.my_profile')"
            :href="route('profile')"
            :active="route().current('profile')"
        >
            <template #icon>
                <IconUserCircle :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

    </PerfectScrollbar>
</template>
