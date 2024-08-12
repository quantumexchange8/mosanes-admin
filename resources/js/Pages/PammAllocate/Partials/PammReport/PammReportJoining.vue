<script setup>
import { ref, h, watch, computed, onMounted } from "vue";
import Button from '@/Components/Button.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm, usePage } from '@inertiajs/vue3';
import RadioButton from 'primevue/radiobutton';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import StatusBadge from '@/Components/StatusBadge.vue';
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import Empty from '@/Components/Empty.vue';
import Loader from "@/Components/Loader.vue";
import {FilterMatchMode} from "primevue/api";
import { IconSearch, IconCircleXFilled, IconX, IconPremiumRights, IconAdjustments, IconScanEye, IconTriangleFilled, IconTriangleInvertedFilled } from '@tabler/icons-vue';
import Calendar from 'primevue/calendar';

const props = defineProps({
    master: Object
})

const dt = ref(null);
const loading = ref(false);
const transactions = ref();
const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
});

// Reactive variable for selected date range
const selectedDate = ref([]);

// Get current date
const today = new Date();

const maxDate = ref(today);


</script>

<template>
    <div class="flex flex-col items-center gap-4 flex-grow self-stretch">
        <DataTable
            :value="transactions"
            paginator
            removableSort
            :rows="10"
            :rowsPerPageOptions="[10, 20, 50, 100]"
            tableStyle="lg:min-width: 50rem"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
            ref="dt"
            @row-click="(event) => openDialog(event.data)"
            :loading="loading"
        >
            <template #header>
                <div class="flex flex-col lg:flex-row gap-3 items-center self-stretch">
                    <div class="relative w-full lg:w-60">
                        <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                            <IconSearch size="20" stroke-width="1.25" />
                        </div>
                        <InputText v-model="filters['global'].value" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full lg:w-60" />
                        <div
                            v-if="filters['global'].value !== null"
                            class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                            @click="clearFilterGlobal"
                        >
                            <IconCircleXFilled size="16" />
                        </div>
                    </div>
                    <div class="w-full grid grid-cols-1 lg:grid-cols-2 gap-3">
                        <div class="relative w-full lg:w-[272px]">
                            <Calendar
                                v-model="selectedDate"
                                selectionMode="range"
                                :manualInput="false"
                                :maxDate="maxDate"
                                dateFormat="dd/mm/yy"
                                showIcon
                                iconDisplay="input"
                                placeholder="yyyy/mm/dd - yyyy/mm/dd"
                                class="w-full"
                            />
                            <div
                                v-if="selectedDate && selectedDate.length > 0"
                                class="absolute top-2/4 -mt-2.5 right-4 text-gray-400 select-none cursor-pointer bg-white"
                                @click="clearDate"
                            >
                                <IconX size="20" />
                            </div>
                        </div>
                        <div class="w-full flex justify-end">
                            <Button
                                variant="primary-outlined"
                                @click="exportCSV($event)"
                                class="w-full lg:w-auto"
                            >
                                {{ $t('public.export') }}
                            </Button>
                        </div>
                    </div>
                    <div class="flex justify-end self-stretch lg:hidden">
                        <span class="text-gray-500 text-right text-sm font-medium">{{ $t('public.total') }}:</span>
                        <span class="text-gray-950 text-sm font-semibold ml-2">$ {{ props.master.total_gain }}</span>
                    </div>
                </div>
            </template>
            <template #empty><Empty :message="$t('public.no_record_message')"/></template>
            <template #loading>
                <div class="flex flex-col gap-2 items-center justify-center">
                    <Loader />
                    <span class="text-sm text-gray-700">{{ $t('public.loading_transactions_caption') }}</span>
                </div>
            </template>
            <template #footer>
                <div v-if="transactions" class="hidden lg:flex justify-end items-center py-2 px-3 gap-3 self-stretch border-y">
                    <span class="flex-grow text-right text-sm">{{ $t('public.total') }}:</span>
                    <span class="text-sm">{{ 'aetawraw' }}</span>
                </div>
            </template>

            <Column 
                field="created_at" 
                sortable 
                :header="$t('public.date')" 
                class="hidden lg:table-cell"
            >
                <template #body="slotProps">
                    {{ formatDateTime(slotProps.data.created_at) }}
                </template>
            </Column>
            <Column 
                field="name" 
                sortable 
                :header="$t('public.name')" 
                class="hidden lg:table-cell"
            >
                <template #body="slotProps">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
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
            <Column 
                field="to_meta_login" 
                :header="$t('public.account')" 
                class="hidden lg:table-cell">
                <template #body="slotProps"
            >
                    {{ slotProps.data.to_meta_login }}
                </template>
            </Column>
            <Column 
                field="transaction_amount" 
                sortable 
                :header="$t('public.balance') + '&nbsp;($)'" 
                class="hidden lg:table-cell"
            >
                <template #body="slotProps">
                    {{ formatAmount(slotProps.data.transaction_amount) }}
                </template>
            </Column>
            <Column 
                field="status" 
                :header="$t('public.status')" 
                class="hidden lg:table-cell"
            >
                <template #body="slotProps">
                    <StatusBadge :value="slotProps.data.status">
                        {{ $t('public.' + slotProps.data.status) }}
                    </StatusBadge>
                </template>
            </Column>
            <Column class="lg:hidden">
                <template #body="slotProps">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                <DefaultProfilePhoto />
                            </div>
                            <div class="flex flex-col items-start">
                                <div class="text-xs font-medium">
                                    {{ slotProps.data.name }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    {{ formatDateTime(slotProps.data.created_at) }}
                                </div>
                            </div>
                        </div>
                        <div class="overflow-hidden text-right text-ellipsis font-semibold">
                            {{ formatAmount(slotProps.data.transaction_amount) }}
                        </div>
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>
</template>