<script setup>
import {ref, watch, watchEffect} from "vue";
import InputText from 'primevue/inputtext';
import Button from '@/Components/Button.vue';
import {useForm, usePage} from '@inertiajs/vue3';
import OverlayPanel from 'primevue/overlaypanel';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import {FilterMatchMode} from "primevue/api";
import Loader from "@/Components/Loader.vue";
import Dropdown from "primevue/dropdown";
import {
    IconSearch,
    IconCircleXFilled,
    IconAdjustmentsHorizontal,
} from '@tabler/icons-vue';
import { wTrans } from "laravel-vue-i18n";
import AgentDropdown from '@/Pages/RebateAllocate/Partials/AgentDropdown.vue';
import InputNumber from "primevue/inputnumber";

const dropdownOptions = [
    {
        name: wTrans('public.standard_account'),
        value: 1
    },
    {
        name: wTrans('public.premium_account'),
        value: 2
    },
]

const accountType = ref(dropdownOptions[0].value);
const loading = ref(false);
const dt = ref();
const agents = ref();

const getResults = async (type_id = 1) => {
    loading.value = true;

    try {
        const response = await axios.get(`/rebate_allocate/getAgents?type_id=${type_id}`);
        agents.value = response.data;
    } catch (error) {
        console.error('Error get agents:', error);
    } finally {
        loading.value = false;
    }
};

getResults();

watch(accountType, (newValue) => {
    getResults(newValue);
})

const changeAgent = async (newAgent) => {
    loading.value = true;

    try {
        const response = await axios.get(`/rebate_allocate/changeAgents?id=${newAgent.id}&type_id=${accountType.value}`);
        agents.value = response.data;
    } catch (error) {
        console.error('Error get change:', error);
    } finally {
        loading.value = false;
    }
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    upline_id: { value: null, matchMode: FilterMatchMode.EQUALS },
    level: { value: null, matchMode: FilterMatchMode.EQUALS },
    role: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const clearFilter = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
        upline_id: { value: null, matchMode: FilterMatchMode.EQUALS },
        level: { value: null, matchMode: FilterMatchMode.EQUALS },
        role: { value: null, matchMode: FilterMatchMode.EQUALS },
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
    };

    upline_id.value = null;
    level.value = null;
};

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}

const editingRows = ref([]);

const form = useForm({
    rebates: null
});
const onRowEditSave = (event) => {
    let { newData, index } = event;

    agents.value[index] = newData;

    form.rebates = agents.value[index][1];
    form.post(route('rebate_allocate.updateRebateAmount'));
};
</script>

<template>
    <div class="p-6 flex flex-col items-center justify-center self-stretch gap-6 border border-gray-200 bg-white shadow-table rounded-2xl">
        <DataTable
            v-model:editingRows="editingRows"
            :value="agents"
            tableStyle="min-width: 50rem"
            :globalFilterFields="['name']"
            ref="dt"
            :loading="loading"
            table-style="min-width:fit-content"
            editMode="row"
            :dataKey="agents ? agents[0].id : 'id'"
            @row-edit-save="onRowEditSave"
        >
            <template #header>
                <div class="flex flex-col md:flex-row gap-3 items-center self-stretch md:justify-between">
                    <div class="relative w-full md:w-60">
                        <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                            <IconSearch size="20" stroke-width="1.25" />
                        </div>
                        <InputText v-model="filters['global'].value" :placeholder="$t('public.search_agent')" class="font-normal pl-12 w-full md:w-60" />
                        <div
                            v-if="filters['global'].value !== null"
                            class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                            @click="clearFilterGlobal"
                        >
                            <IconCircleXFilled size="16" />
                        </div>
                    </div>
                    <Dropdown
                        v-model="accountType"
                        :options="dropdownOptions"
                        optionLabel="name"
                        optionValue="value"
                        class="w-full md:w-52 font-normal"
                    />
                </div>
            </template>
            <template #empty> {{ $t('public.no_user_header') }} </template>
            <template #loading>
                <div class="flex flex-col gap-2 items-center justify-center">
                    <Loader />
                    <span class="text-sm text-gray-700">{{ $t('public.loading_users_caption') }}</span>
                </div>
            </template>
            <Column field="level" style="width:10%;">
                <template #header>
                    <span>{{ $t('public.level') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data[0][0].level }}
                </template>
            </Column>
            <Column field="agent" class="w-auto">
                <template #header>
                    <span>{{ $t('public.agent') }}</span>
                </template>
                <template #body="slotProps">
                    <AgentDropdown :agents="slotProps.data[0]" @update:modelValue="changeAgent($event)" class="w-full" />
                </template>
                <template #editor="{ data }">
                    <div class="flex items-center gap-3">
                        <img :src="data[0][0].profile_photo" class="w-5 h-5 rounded-full grow-0 shrink-0" alt="">
                        <span>{{ data[0][0].name }}</span>
                    </div>
                </template>
            </Column>
            <Column field="1" class="hidden md:table-cell" style="width:10%;">
                <template #header>
                    <span>{{ $t('public.forex') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data[1]['1'] }}
                </template>
                <template #editor="{ data, field }">
                    <InputNumber
                        v-model="data[1][field]"
                        :min="data[1].downline_forex ? data[1].downline_forex : 0"
                        :max="data[1].upline_forex"
                        :minFractionDigits="2"
                        fluid
                        size="sm"
                        inputClass="py-2 px-4 w-20"
                    />
                </template>
            </Column>
            <Column field="2" class="hidden md:table-cell" style="width:10%;">
                <template #header>
                    <span>{{ $t('public.stocks') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data[1]['2'] }}
                </template>
                <template #editor="{ data, field }">
                    <InputNumber
                        v-model="data[1][field]"
                        :min="data[1].downline_forex ? data[1].downline_forex : 0"
                        :max="data[1].upline_forex"
                        :minFractionDigits="2"
                        fluid
                        size="sm"
                        inputClass="py-2 px-4 w-20"
                    />
                </template>
            </Column>
            <Column field="3" class="hidden md:table-cell" style="width:10%;">
                <template #header>
                    <span>{{ $t('public.indices') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data[1]['3'] }}
                </template>
                <template #editor="{ data, field }">
                    <InputNumber
                        v-model="data[1][field]"
                        :min="data[1].downline_forex ? data[1].downline_forex : 0"
                        :max="data[1].upline_forex"
                        :minFractionDigits="2"
                        fluid
                        size="sm"
                        inputClass="py-2 px-4 w-20"
                    />
                </template>
            </Column>
            <Column field="4" class="hidden md:table-cell" style="width:10%;">
                <template #header>
                    <span class="w-12 truncate lg:w-auto">{{ $t('public.commodities') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data[1]['4'] }}
                </template>
                <template #editor="{ data, field }">
                    <InputNumber
                        v-model="data[1][field]"
                        :min="data[1].downline_forex ? data[1].downline_forex : 0"
                        :max="data[1].upline_forex"
                        :minFractionDigits="2"
                        fluid
                        size="sm"
                        inputClass="py-2 px-4 w-20"
                    />
                </template>
            </Column>
            <Column field="5" class="hidden md:table-cell" style="width:10%;">
                <template #header>
                    <span class="w-12 truncate lg:w-auto">{{ $t('public.cryptocurrency') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data[1]['5'] }}
                </template>
                <template #editor="{ data, field }">
                    <InputNumber
                        v-model="data[1][field]"
                        :min="data[1].downline_forex ? data[1].downline_forex : 0"
                        :max="data[1].upline_forex"
                        :minFractionDigits="2"
                        fluid
                        size="sm"
                        inputClass="py-2 px-4 w-20"
                    />
                </template>
            </Column>
            <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center">
                <template #roweditoriniticon>
                    <Button
                        variant="gray-text"
                        type="button"
                        size="sm"
                        iconOnly
                        pill
                    >
                        <IconAdjustmentsHorizontal size="16" stroke-width="1.25" />
                    </Button>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
