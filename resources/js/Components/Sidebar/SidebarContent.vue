<script setup>
import PerfectScrollbar from '@/Components/PerfectScrollbar.vue'
import SidebarLink from '@/Components/Sidebar/SidebarLink.vue'
import SidebarCollapsible from '@/Components/Sidebar/SidebarCollapsible.vue'
import SidebarCollapsibleItem from '@/Components/Sidebar/SidebarCollapsibleItem.vue'
import {ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import { IconLayoutDashboard, IconComponents, IconUserCircle, IconUsersGroup, IconReceiptDollar, IconId } from '@tabler/icons-vue';

</script>

<template>
    <PerfectScrollbar
        tagname="nav"
        aria-label="main"
        class="relative flex flex-col flex-1 max-h-full gap-1 px-5 py-3 items-center"
    >
        <!-- Dashboard -->
        <SidebarLink
            title="Dashboard"
            :href="route('dashboard')"
            :active="route().current('dashboard')"
        >
            <template #icon>
                <IconLayoutDashboard :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Member -->
        <SidebarCollapsible
            title="Member"
            :active="route().current('member.*')"
        >
            <template #icon>
                <IconComponents :size="20" stroke-width="1.25" />
            </template>

            <SidebarCollapsibleItem
                title="Member Listing"
                :href="route('member.listing')"
                :active="route().current('member.listing')"
            />

            <SidebarCollapsibleItem
                title="Member Network"
                :href="route('member.network')"
                :active="route().current('member.network')"
            />

        </SidebarCollapsible>

        <!-- Group -->
        <SidebarLink
            title="Group"
            :href="route('group')"
            :active="route().current('group')"
        >
            <template #icon>
                <IconUsersGroup :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Transaction -->
        <SidebarLink
            title="Transaction"
            :href="route('transaction')"
            :active="route().current('transaction')"
        >
            <template #icon>
                <IconReceiptDollar :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Account Type -->
        <SidebarLink
            title="Account Type"
            :href="route('accountType')"
            :active="route().current('accountType')"
        >
            <template #icon>
                <IconId :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

        <!-- Components -->
        <SidebarCollapsible
            title="Components"
            :active="route().current('components.*')"
        >
            <template #icon>
                <IconComponents :size="20" stroke-width="1.25" />
            </template>

            <SidebarCollapsibleItem
                title="Buttons"
                :href="route('components.buttons')"
                :active="route().current('components.buttons')"
            />

            <SidebarCollapsibleItem
                title="Member Network"
                :href="route('member.network')"
                :active="route().current('member.network')"
            />
        </SidebarCollapsible>


        <!-- Profile -->
        <SidebarLink
            title="My Profile"
            :href="route('profile.edit')"
            :active="route().current('profile.edit')"
        >
            <template #icon>
                <IconUserCircle :size="20" stroke-width="1.25" />
            </template>
        </SidebarLink>

    </PerfectScrollbar>
</template>
