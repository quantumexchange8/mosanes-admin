<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabPanel from "primevue/tabpanel";
import TabView from "primevue/tabview";
import {h, ref} from "vue";
import BillboardProfile from "@/Pages/Billboard/BillboardProfile/BillboardProfile.vue";
import WithdrawalTransactionTable from "@/Pages/Transaction/Partials/WithdrawalTransactionTable.vue";

const props = defineProps({
    profileCount: Number
})

const tabs = ref([
    {
        title: 'profile',
        component: h(BillboardProfile, {
            profileCount: props.profileCount
        }),
    },
    {
        title: 'bonus_withdrawal',
    },
]);

const activeIndex = ref(tabs.value.findIndex(tab => tab.title === 'profile'));

const updateType = (event) => {
    activeIndex.value = event.index;
}
</script>

<template>
    <AuthenticatedLayout :title="$t('public.billboard')">
        <div class="flex flex-col gap-5 md:gap-8">
            <TabView class="flex flex-col" :activeIndex="activeIndex" @tab-change="updateType">
                <TabPanel v-for="(tab, index) in tabs" :key="index" :header="$t(`public.${tab.title}`)" />
            </TabView>
            <component
                :is="tabs[activeIndex]?.component"
            />
        </div>
    </AuthenticatedLayout>
</template>
