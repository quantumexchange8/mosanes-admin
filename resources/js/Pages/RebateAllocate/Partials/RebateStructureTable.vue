<script setup>
import Button from "@/Components/Button.vue";
import {IconAdjustments, IconCircleXFilled, IconSearch} from "@tabler/icons-vue";
import MemberTableActions from "@/Pages/Member/Listing/Partials/MemberTableActions.vue";
import InputText from "primevue/inputtext";
import Badge from "@/Components/Badge.vue";
import Loader from "@/Components/Loader.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {ref, watch} from "vue";
import {FilterMatchMode} from "primevue/api";

const props = defineProps({
    companyProfile: Object
})

const loading = ref(false);
const accountType = ref(1);
const user = ref()
const rebateStructures = ref();

watch(() => props.companyProfile, (newUpline) => {
    user.value = newUpline;
    getResults(accountType.value, user.value.user_id);
})

const getResults = async (filterAccountType = accountType.value, filterUpline = '') => {
    loading.value = true;

    try {
        let url = `/rebate_allocate/getRebateStructureData?account_type_id=${filterAccountType}`;

        if (filterUpline) {
            url += `&user_id=${filterUpline}`;
        }

        const response = await axios.get(url);
        rebateStructures.value = response.data.rebateStructures;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        loading.value = false;
    }
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    upline_id: { value: null, matchMode: FilterMatchMode.EQUALS },
    group_id: { value: null, matchMode: FilterMatchMode.EQUALS },
    role: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
});

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
}
</script>

<template>
    <div class="p-6 flex flex-col items-center justify-center self-stretch gap-6 border border-gray-200 bg-white shadow-table rounded-2xl">
        <DataTable
            v-model:filters="filters"
            :value="rebateStructures"
            removableSort
            tableStyle="min-width: 50rem"
            :globalFilterFields="['name']"
            ref="dt"
            :loading="loading"
        >
            <template #header>
                <div class="flex flex-col md:flex-row gap-3 items-center self-stretch">
                    <div class="relative w-full md:w-60">
                        <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                            <IconSearch size="20" stroke-width="1.25" />
                        </div>
                        <InputText v-model="filters['global'].value" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full md:w-60" />
                        <div
                            v-if="filters['global'].value !== null"
                            class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                            @click="clearFilterGlobal"
                        >
                            <IconCircleXFilled size="16" />
                        </div>
                    </div>
                </div>
            </template>
            <template #empty> {{ $t('public.no_user_header') }} </template>
            <template #loading>
                <div class="flex flex-col gap-2 items-center justify-center">
                    <Loader />
                    <span class="text-sm text-gray-700">{{ $t('public.loading_users_caption') }}</span>
                </div>
            </template>
            <Column field="id_number" style="width: 25%" headerClass="hidden md:table-cell">
                <template #header>
                    <span class="hidden md:block">id</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data.id_number }}
                </template>
            </Column>
            <Column field="name" :header="$t('public.name')" style="width: 35%" headerClass="hidden md:table-cell">
                <template #body="slotProps">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden">
                            <DefaultProfilePhoto />
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="font-medium">
                                {{ slotProps.data.name }}
                            </div>
                            <div class="text-gray-500 text-xs">
                                {{ slotProps.data.email }}
                            </div>
                        </div>
                    </div>
                </template>
            </Column>
            <Column field="group" style="width: 15%" headerClass="hidden md:table-cell">
                <template #header>
                    <span class="hidden md:block items-center justify-center w-full text-center">{{ $t('public.group') }}</span>
                </template>
                <template #body="slotProps">
                    <div class="flex items-center justify-center">
                        <div
                            v-if="slotProps.data.group_id"
                            class="flex items-center gap-2 rounded justify-center py-1 px-2"
                            :style="{ backgroundColor: formatRgbaColor(slotProps.data.group_color, 0.1) }"
                        >
                            <div
                                class="w-1.5 h-1.5 grow-0 shrink-0 rounded-full"
                                :style="{ backgroundColor: `#${slotProps.data.group_color}` }"
                            ></div>
                            <div
                                class="text-xs font-semibold"
                                :style="{ color: `#${slotProps.data.group_color}` }"
                            >
                                {{ slotProps.data.group_name }}
                            </div>
                        </div>
                        <div v-else>
                            -
                        </div>
                    </div>
                </template>
            </Column>
            <Column field="representative.name" header="" style="width: 10%" headerClass="hidden md:table-cell">
                <template #body="slotProps">
                    button
                </template>
            </Column>
        </DataTable>
    </div>
</template>
