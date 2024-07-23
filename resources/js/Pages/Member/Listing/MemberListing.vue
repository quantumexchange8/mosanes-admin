<script setup>
import {computed, onMounted, ref, watchEffect} from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddMember from "@/Pages/Member/Listing/Partials/AddMember.vue";
import { MemberIcon, AgentIcon, UserIcon, } from '@/Components/Icons/solid';
import InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import Button from '@/Components/Button.vue';
import {useForm, usePage} from '@inertiajs/vue3';
import OverlayPanel from 'primevue/overlaypanel';
import Dialog from 'primevue/dialog';
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {FilterMatchMode} from "primevue/api";
import Loader from "@/Components/Loader.vue";

const total_members = ref();
const total_agents = ref();
const total_users = ref();
const loading = ref(false);
const dt = ref();
const users = ref();

onMounted(() => {
    loading.value = true;
    getResults();
})

// data overview
const dataOverviews = computed(() => [
    {
        icon: MemberIcon,
        total: total_members.value,
        label: 'Total Members',
    },
    {
        icon: AgentIcon,
        total: total_agents.value,
        label: 'Total Agents',
    },
    {
        icon: UserIcon,
        total: total_users.value,
        label: 'Total Users',
    },
]);

const getResults = async () => {
    try {
        const response = await axios.get('/member/getMemberListingData');
        users.value = response.data.users;
        total_members.value = response.data.total_members;
        total_agents.value = response.data.total_agents;
        total_users.value = response.data.total_users;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        loading.value = false;
    }
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    role: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
    verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

// overlay panel
const op = ref();

const toggle = (event) => {
    op.value.toggle(event);
}

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
        'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
        role: { value: null, matchMode: FilterMatchMode.EQUALS },
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
        verified: { value: null, matchMode: FilterMatchMode.EQUALS }
    };
};

const clearFilter = () => {
    initFilters();
};

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults();
    }
});
</script>

<template>
    <AuthenticatedLayout title="Member Listing">
        <div class="flex flex-col gap-5 items-center">
            <div class="flex justify-end w-full">
                <AddMember />
            </div>

            <!-- data overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 w-full gap-3 md:gap-5">
                <div
                    v-for="(item, index) in dataOverviews"
                    :key="index"
                    class="flex justify-center items-center px-6 py-4 md:p-6 gap-5 self-stretch rounded-2xl bg-white shadow-toast"
                >
                    <component :is="item.icon" class="w-12 h-12 grow-0 shrink-0" />
                    <div class="flex flex-col items-start gap-1 w-full">
                        <span class="text-gray-950 text-lg md:text-2xl font-semibold">{{ item.total }}</span>
                        <span class="text-gray-500 text-xs md:text-sm">{{ item.label }}</span>
                    </div>
                </div>
            </div>

            <!-- data table -->
            <div class="p-6 flex flex-col items-center justify-center self-stretch gap-6 border border-gray-200 bg-white shadow-table rounded-2xl">
                <DataTable
                    v-model:filters="filters"
                    :value="users"
                    paginator
                    removableSort
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50, 100]"
                    tableStyle="min-width: 50rem"
                    paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                    :globalFilterFields="['first_name']"
                    ref="dt"
                    :loading="loading"
                >
                    <template #header>
                        <div class="flex justify-between items-center self-stretch">
                            <div class="flex gap-3 items-center">
                                <InputText v-model="filters['global'].value" placeholder="Keyword Search" class="font-normal w-60" />
                                <Button variant="gray-outlined" @click="toggle">
                                    Filter
                                </Button>
                            </div>
                            <div >
                                <Button variant="primary-outlined" @click="exportCSV($event)">
                                    Export
                                </Button>
                            </div>
                        </div>
                    </template>
                    <template #empty> No users found. </template>
                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <Loader />
                            <span class="text-sm text-gray-700">Loading users data. Please wait.</span>
                        </div>
                    </template>
                    <Column field="id_number" sortable style="width: 25%">
                        <template #header>
                            <span class="hidden md:block">id</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.id_number }}
                        </template>
                    </Column>
                    <Column field="name" sortable header="Name" style="width: 25%">
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

    <OverlayPanel ref="op">
        <div class="flex flex-col gap-8 w-60">
            <!-- Filter Role-->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 font-semibold">
                    Filter by role
                </div>
                <div class="flex flex-col gap-1 self-stretch">
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['role'].value" inputId="role_member" value="member" />
                        <label for="role_member">Member</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['role'].value" inputId="role_agent" value="agent" />
                        <label for="role_agent">Agent</label>
                    </div>
                </div>
            </div>

<!--            &lt;!&ndash; Filter Group&ndash;&gt;-->
<!--            <div class="flex flex-col gap-2 items-center self-stretch">-->
<!--                <div class="flex self-stretch text-xs text-gray-950 font-semibold">-->
<!--                    Filter by group-->
<!--                </div>-->
<!--                <div class="flex flex-col gap-1 self-stretch">-->
<!--                    <div class="flex items-center gap-2 text-sm text-gray-950">-->
<!--                        <RadioButton v-model="filterByRole" inputId="role_member" value="member" />-->
<!--                        <label for="role_member">Member</label>-->
<!--                    </div>-->
<!--                    <div class="flex items-center gap-2 text-sm text-gray-950">-->
<!--                        <RadioButton v-model="filterByRole" inputId="role_agent" value="agent" />-->
<!--                        <label for="role_agent">Agent</label>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

<!--            &lt;!&ndash; Filter Upline&ndash;&gt;-->
<!--            <div class="flex flex-col gap-2 items-center self-stretch">-->
<!--                <div class="flex self-stretch text-xs text-gray-950 font-semibold">-->
<!--                    Filter by upline-->
<!--                </div>-->
<!--                <div class="flex flex-col gap-1 self-stretch">-->
<!--                    <div class="flex items-center gap-2 text-sm text-gray-950">-->
<!--                        <RadioButton v-model="filterByRole" inputId="role_member" value="member" />-->
<!--                        <label for="role_member">Member</label>-->
<!--                    </div>-->
<!--                    <div class="flex items-center gap-2 text-sm text-gray-950">-->
<!--                        <RadioButton v-model="filterByRole" inputId="role_agent" value="agent" />-->
<!--                        <label for="role_agent">Agent</label>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

            <!-- Filter Statys-->
            <div class="flex flex-col gap-2 items-center self-stretch">
                <div class="flex self-stretch text-xs text-gray-950 font-semibold">
                    Filter by status
                </div>
                <div class="flex flex-col gap-1 self-stretch">
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['status'].value" inputId="status_active" value="active" />
                        <label for="status_active">Active</label>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-950">
                        <RadioButton v-model="filters['status'].value" inputId="status_inactive" value="inactive" />
                        <label for="status_inactive">Inactive</label>
                    </div>
                </div>
            </div>

            <div class="flex w-full">
                <Button
                    type="button"
                    variant="primary-outlined"
                    class="flex justify-center w-full"
                    @click="clearFilter()"
                >
                    Clear All
                </Button>
            </div>
        </div>
    </OverlayPanel>

</template>
