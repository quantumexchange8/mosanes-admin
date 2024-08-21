<script setup>
import { ref, h, watch, computed, onMounted } from "vue";
import Button from '@/Components/Button.vue';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm, usePage } from '@inertiajs/vue3';
import RadioButton from 'primevue/radiobutton';
import { wTrans } from "laravel-vue-i18n";
import PammReportJoining from "@/Pages/PammAllocate/Partials/PammReport/PammReportJoining.vue";
import PammReportRevoke from "@/Pages/PammAllocate/Partials/PammReport/PammReportRevoke.vue";

const props = defineProps({
    master: Object
})

const tabs = ref([
    {
        component: h(PammReportJoining),
        type: 'joining',
        rowCount: 0
    },
    {
        component: h(PammReportRevoke),
        type: 'revoke',
        rowCount: 0
    },
]);

const selectedType = ref('joining');
const activeIndex = ref(tabs.value.findIndex(tab => tab.type === selectedType.value));

// Watch for changes in selectedType and update the activeIndex accordingly
watch(selectedType, (newType) => {
    const index = tabs.value.findIndex(tab => tab.type === newType);
    if (index >= 0) {
        activeIndex.value = index;
    }
});

const updateRowCount = (newRowCount) => {
    tabs.value[activeIndex.value].rowCount = newRowCount;
};

function updateType(event) {
    const selectedTab = tabs.value[event.index];
    selectedType.value = selectedTab.type;
}

</script>

<template>
    <div class="flex flex-col items-center gap-4 flex-grow self-stretch">
        <div class="flex flex-col gap-4 items-center self-stretch lg:flex-row">
            <TabView
                class="flex items-center self-stretch"
                :activeIndex="activeIndex"
                @tab-change="updateType"
            >
                <TabPanel
                    v-for="(tab, index) in tabs"
                    :key="index"
                    :header="$t(`public.${tab.type}`) + ` (${tab.rowCount})`"
                />
            </TabView>
        </div>
        <component
            :is="tabs[activeIndex]?.component"
            :master="props.master"
            @update:rowCount="updateRowCount($event)"
        />
    </div>
</template>
