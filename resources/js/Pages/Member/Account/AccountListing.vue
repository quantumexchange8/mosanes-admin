<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import {h, ref, watch} from "vue";
import AllAccount from "@/Pages/Member/Account/AllAccount.vue";
import DeletedAccount from "@/Pages/Member/Account/DeletedAccount.vue";

const tabs = ref([
    {
        title: 'all_accounts',
        component: h(AllAccount),
        type: 'all_accounts'
    },
    {
        title: 'deleted_accounts',
        component: h(DeletedAccount),
        type: 'deleted_accounts'
    },
]);

const selectedType = ref('all_accounts');
const activeIndex = ref(tabs.value.findIndex(tab => tab.type === selectedType.value));

// Watch for changes in selectedType and update the activeIndex accordingly
watch(selectedType, (newType) => {
    const index = tabs.value.findIndex(tab => tab.type === newType);
    if (index >= 0) {
        activeIndex.value = index;
    }
});

const updateType = (event) => {
    const selectedTab = tabs.value[event.index];
    selectedType.value = selectedTab.type;
}
</script>

<template>
    <AuthenticatedLayout :title="$t('public.account_listing')">
        <div class="py-6 px-4 md:p-6 flex flex-col items-center self-stretch border border-gray-200 bg-white shadow-table rounded-2xl">
            <TabView
                class="flex flex-col w-full gap-6"
                :activeIndex="activeIndex"
                @tab-change="updateType"
            >
                <TabPanel
                    v-for="(tab, index) in tabs"
                    :key="index"
                    :header="$t(`public.${tab.title}`)"
                >
                    <component :is="tabs[activeIndex]?.component" />
                </TabPanel>
            </TabView>
        </div>
    </AuthenticatedLayout>
</template>
