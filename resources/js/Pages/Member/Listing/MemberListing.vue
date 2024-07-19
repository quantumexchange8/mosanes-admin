<script setup>
import {onMounted, ref, watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddMember from "@/Pages/Member/Listing/Partials/AddMember.vue";
import { SearchIcon, Sliders02Icon, DownloadCloud01Icon } from '@/Components/Icons/outline';
import { MemberIcon, AgentIcon, UserIcon, } from '@/Components/Icons/solid';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import RadioButton from 'primevue/radiobutton';
import Button from '@/Components/Button.vue';
import Badge from "@/Components/Badge.vue";
import { useForm } from '@inertiajs/vue3';
// import MemberListingTable from "./Partials/MemberListingTable.vue";
import OverlayPanel from 'primevue/overlaypanel';
import Dialog from 'primevue/dialog';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {FilterMatchMode} from "primevue/api";

const form = useForm({
    search: '',
});

const visible = ref(false);
const op = ref();

// Method to toggle the dialog visibility
const toggleDialog = () => {
    visible.value = !visible.value;
    if (visible.value && op.value) {
        op.value.hide(); // Close overlay panel if open
    }
};

// Method to toggle the overlay panel visibility
const toggleOverlay = () => {
    if (visible.value) {
        visible.value = false; // Close dialog if open
    }
    op.value.show();
};

const selectedRole = ref(null);
const selectedGroup = ref(null);
const selectedUpline = ref(null);
const selectedStatus = ref(null);

// Temporary variables to hold selected filter values
const tempRole = ref(null);
const tempGroup = ref(null);
const tempUpline = ref(null);
const tempStatus = ref(null);

const filterMappings = [
    { selected: selectedRole, temp: tempRole },
    { selected: selectedGroup, temp: tempGroup },
    { selected: selectedUpline, temp: tempUpline },
    { selected: selectedStatus, temp: tempStatus }
];

const applyFilters = () => {
    filterMappings.forEach(({ selected, temp }) => {
    selected.value = temp.value;
    });
    op.value.hide();
    visible.value = false;
};

const clearFilters = () => {
    filterMappings.forEach(({ selected, temp }) => {
    selected.value = null;
    temp.value = null;
    });
};

// Calculate the count of applied filters
const filterCount = ref(0);

// Watch for changes in filter variables and update the count
watchEffect(() => {
    filterCount.value = filterMappings.reduce((count, { selected }) => {
        return selected.value !== null ? count + 1 : count;
    }, 0);
});

onMounted(() => {
    getResults();
})
const getResults = async (langVal) => {
    try {
        const response = await axios.get('http://mosanes-admin.test/test/getData');
        customers.value = response.data.users;
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

const dt = ref();
const exportCSV = () => {
    dt.value.exportCSV();
};

const customers = ref();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    representative: { value: null, matchMode: FilterMatchMode.IN },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});
</script>

<template>
    <AuthenticatedLayout title="Member Listing">
        <div class="flex flex-col gap-5">
            <!-- below md data overview -->
             <div class="flex md:hidden flex-col justify-center items-center gap-3 self-stretch max-w-[1440ox]">
                <div class="flex justify-center items-center px-6 py-4 gap-5 self-stretch rounded-2xl bg-white shadow-toast">
                    <MemberIcon class="w-12 h-12" />
                    <div class="flex flex-col items-start gap-1 flex-grow">
                        <span class="text-gray-950 text-lg font-semibold">1,844</span>
                        <span class="text-gray-500 text-xs">Total Members</span>
                    </div>
                </div>
                <div class="flex justify-center items-center px-6 py-4 gap-5 self-stretch rounded-2xl bg-white shadow-toast">
                    <AgentIcon class="w-12 h-12" />
                    <div class="flex flex-col items-start gap-1 flex-grow">
                        <span class="text-gray-950 text-lg font-semibold">128</span>
                        <span class="text-gray-500 text-xs">Total Agents</span>
                    </div>
                </div>
                <div class="flex justify-center items-center px-6 py-4 gap-5 self-stretch rounded-2xl bg-white shadow-toast">
                    <UserIcon class="w-12 h-12" />
                    <div class="flex flex-col items-start gap-1 flex-grow">
                        <span class="text-gray-950 text-lg font-semibold">1,972</span>
                        <span class="text-gray-500 text-xs">Total Users</span>
                    </div>
                </div>
             </div>
            <!-- below md add member button -->
            <div class="md:hidden">
                <AddMember />
            </div>

            <!-- above md add member button -->
            <div class="hidden md:flex flex-col items-end gap-2.5 max-w-[1440px] self-stretch">
                <AddMember />
            </div>
            <!-- above md data overview -->
            <div class="hidden md:flex justify-center items-center gap-5 self-stretch max-w-[1440px] rounded-xl">
                <div class="flex justify-center items-center p-6 gap-5 flex-grow rounded-2xl bg-white shadow-toast">
                    <MemberIcon class="w-12 h-12" />
                    <div class="flex flex-col items-start gap-1 flex-grow">
                        <span class="text-gray-950 text-xl font-semibold">1,844</span>
                        <span class="text-gray-500 text-sm">Total Members</span>
                    </div>
                </div>
                <div class="flex justify-center items-center p-6 gap-5 flex-grow rounded-2xl bg-white shadow-toast">
                    <AgentIcon class="w-12 h-12" />
                    <div class="flex flex-col items-start gap-1 flex-grow">
                        <span class="text-gray-950 text-xl font-semibold">128</span>
                        <span class="text-gray-500 text-sm">Total Agents</span>
                    </div>
                </div>
                <div class="flex justify-center items-center p-6 gap-5 flex-grow rounded-2xl bg-white shadow-toast">
                    <UserIcon class="w-12 h-12" />
                    <div class="flex flex-col items-start gap-1 flex-grow">
                        <span class="text-gray-950 text-xl font-semibold">1,972</span>
                        <span class="text-gray-500 text-sm">Total Users</span>
                    </div>
                </div>
            </div>
            <div class="p-6 flex flex-col items-center justify-center self-stretch gap-6 border border-gray-200 bg-white shadow-table rounded-2xl">
                <DataTable
                    v-model:filters="filters"
                    :value="customers"
                    paginator
                    removableSort
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    tableStyle="min-width: 50rem"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                    :globalFilterFields="['first_name']"
                    ref="dt"
                >
                    <template #header>
                        <div class="flex justify-between items-center self-stretch">
                            <div>
                                <InputText v-model="filters['global'].value" placeholder="Keyword Search" class="font-normal" />
                            </div>
                            <div >
                                <Button variant="primary-outlined" @click="exportCSV($event)">
                                    Export
                                </Button>
                            </div>
                        </div>
                    </template>
                    <template #empty> No customers found. </template>
                    <template #loading> Loading customers data. Please wait. </template>
                    <Column field="id" sortable header="Id" style="width: 25%">
                        <template #body="slotProps">
                            MID00000{{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="first_name" sortable header="Name" style="width: 25%">
                        <template #body="slotProps">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full overflow-hidden">
                                    <DefaultProfilePhoto />
                                </div>
                                <div class="flex flex-col items-start">
                                    <div class="font-medium">
                                        {{ slotProps.data.first_name }}
                                    </div>
                                    <div class="text-gray-500 text-xs">
                                        {{ slotProps.data.email }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="company" header="Company" style="width: 25%"></Column>
                    <Column field="representative.name" header="Representative" style="width: 25%"></Column>
                </DataTable>

            </div>
<!--            <div class="flex flex-col justify-center md:justify-normal items-center px-4 md:px-6 py-6 gap-5 md:gap-6 self-stretch max-w-[1440px] rounded-2xl border border-gray-200 bg-white shadow-table">-->
<!--                &lt;!&ndash; below md table &ndash;&gt;-->
<!--                <div class="flex md:hidden flex-col items-center gap-3 self-stretch">-->
<!--                    <IconField iconPosition="left" class="w-full">-->
<!--                        <SearchIcon class="w-5 h-5 text-gray-400" />-->
<!--                        <InputText-->
<!--                            id="searchSM"-->
<!--                            type="text"-->
<!--                            class="block w-full"-->
<!--                            v-model="form.search"-->
<!--                            placeholder="Search"-->
<!--                            :invalid="form.errors.search"-->
<!--                        />-->
<!--                    </IconField>-->
<!--                    <div class="flex justify-center items-center gap-3 self-stretch">-->
<!--                        <Button variant="gray-text" class="flex flex-1 ring-1 ring-gray-300 hover:bg-gray-50 focus:bg-gray-50 focus:ring-gray-300 focus:ring-1" @click="toggleDialog">-->
<!--                            <Sliders02Icon />-->
<!--                            Filter-->
<!--                            <Badge variant="numberbadge" class="text-xs text-white">{{filterCount}}</Badge>-->
<!--                        </Button>-->
<!--                        <Button variant="primary-outlined" class="flex flex-1 focus:ring-0">-->
<!--                            Export-->
<!--                            <DownloadCloud01Icon />-->
<!--                        </Button>-->
<!--                    </div>-->
<!--                </div>-->
<!--                &lt;!&ndash; above md table &ndash;&gt;-->
<!--                <div class="hidden md:flex justify-between items-center self-stretch">-->
<!--                    <div class="flex items-center gap-3">-->
<!--                        <IconField iconPosition="left" class="w-[240px]">-->
<!--                            <SearchIcon class="w-5 h-5 text-gray-400" />-->
<!--                            <InputText-->
<!--                                id="searchMD"-->
<!--                                type="text"-->
<!--                                class="block w-full"-->
<!--                                v-model="form.search"-->
<!--                                placeholder="Search"-->
<!--                                :invalid="form.errors.search"-->
<!--                            />-->
<!--                        </IconField>-->
<!--                        <Button variant="gray-text" class="flex flex-1 ring-1 ring-gray-300 hover:bg-gray-50 focus:bg-gray-50 focus:ring-gray-300 focus:ring-1" @click="toggleOverlay">-->
<!--                            <Sliders02Icon />-->
<!--                            Filter-->
<!--                            <Badge variant="numberbadge" class="text-xs text-white">{{filterCount}}</Badge>-->
<!--                        </Button>-->
<!--                    </div>-->
<!--                    <Button variant="primary-outlined" class="flex items-center w-[124px] focus:ring-0">-->
<!--                        Export-->
<!--                        <DownloadCloud01Icon />-->
<!--                    </Button>-->
<!--                </div>-->
<!--&lt;!&ndash;                <MemberListingTable />&ndash;&gt;-->
<!--            </div>-->
        </div>
    </AuthenticatedLayout>

    <Dialog v-model:visible="visible" modal header="Filters" class="dialog-xs">
        <form @submit.prevent="applyFilters">
            <div class="flex flex-col items-center gap-8 pb-5 self-stretch">
                <!-- role -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by role</span>
                    <div class="flex flex-col items-center gap-1 self-stretch">
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempRole" inputId="member" name="Member" value="member" />
                            </div>
                            <div class="text-gray-950 text-sm">Member</div>
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempRole" inputId="agent" name="Agent" value="agent" />
                            </div>
                            <div class="text-gray-950 text-sm">Agent</div>
                        </div>
                    </div>
                </div>
                <!-- group -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by group</span>

                </div>
                <!-- upline -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by upline</span>

                </div>
                <!-- status -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by role</span>
                    <div class="flex flex-col items-center gap-1 self-stretch">
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempStatus" inputId="active" name="Active" value="active" />
                            </div>
                            <div class="text-gray-950 text-sm">Active</div>
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempStatus" inputId="inactive" name="Inactive" value="inactive" />
                            </div>
                            <div class="text-gray-950 text-sm">Inactive</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex pt-5 justify-center items-center gap-4 self-stretch border-t border-gray-200">
                <Button variant="gray-tonal" class="flex flex-1" @click.prevent="clearFilters">Clear All</Button>
                <Button variant="primary-flat" class="flex flex-1" @click.prevent="applyFilters">Apply</Button>
            </div>
        </form>
    </Dialog>

    <OverlayPanel ref="op">
        <div class="w-60">
            <div class="flex flex-col items-center gap-8 pb-5 self-stretch">
                <!-- role -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by role</span>
                    <div class="flex flex-col items-center gap-1 self-stretch">
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempRole" inputId="member" name="Member" value="member" />
                            </div>
                            <div class="text-gray-950 text-sm">Member</div>
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempRole" inputId="agent" name="Agent" value="agent" />
                            </div>
                            <div class="text-gray-950 text-sm">Agent</div>
                        </div>
                    </div>
                </div>
                <!-- group -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by group</span>

                </div>
                <!-- upline -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by upline</span>

                </div>
                <!-- status -->
                <div class="flex flex-col items-center gap-2 self-stretch">
                    <span class="self-stretch text-gray-950 text-xs font-bold">Filter by role</span>
                    <div class="flex flex-col items-center gap-1 self-stretch">
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempStatus" inputId="active" name="Active" value="active" />
                            </div>
                            <div class="text-gray-950 text-sm">Active</div>
                        </div>
                        <div class="flex items-center gap-2 self-stretch">
                            <div class="flex w-8 h-8 p-2 justify-center items-center">
                                <RadioButton v-model="tempStatus" inputId="inactive" name="Inactive" value="inactive" />
                            </div>
                            <div class="text-gray-950 text-sm">Inactive</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex pt-5 justify-center items-center gap-4 self-stretch border-t border-gray-200">
                <Button variant="gray-tonal" class="flex flex-1" @click.prevent="clearFilters">Clear All</Button>
                <Button variant="primary-flat" class="flex flex-1" @click.prevent="applyFilters">Apply</Button>
            </div>
        </div>
    </OverlayPanel>

</template>
