<script setup>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import {ref} from "vue";
import {FilterMatchMode} from "primevue/api";
import Loader from "@/Components/Loader.vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import MemberTableActions from "@/Pages/Member/Listing/Partials/MemberTableActions.vue";
import Empty from "@/Components/Empty.vue";
import dayjs from "dayjs";
import AccountTableActions from "@/Pages/Member/Account/Partials/AccountTableActions.vue";

const accounts = ref([]);
const loading = ref(false);
const filteredValueCount = ref(0);

const getResults = async () => {
    loading.value = true;

    try {
        const response = await axios.get('/member/getAccountListingData?account_listing=all');
        accounts.value = response.data.accounts;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        loading.value = false;
    }
};

getResults();

const exportCSV = () => {
    dt.value.exportCSV();
};

const filters = ref({
    global: {value: null, matchMode: FilterMatchMode.CONTAINS},
    name: {value: null, matchMode: FilterMatchMode.STARTS_WITH},
    upline_id: {value: null, matchMode: FilterMatchMode.EQUALS},
    group_id: {value: null, matchMode: FilterMatchMode.EQUALS},
    role: {value: null, matchMode: FilterMatchMode.EQUALS},
    status: {value: null, matchMode: FilterMatchMode.EQUALS},
});

const openDialog = (data) => {

}

const handleFilter = (e) => {
    filteredValueCount.value = e.filteredValue.length;
};
</script>

<template>
    <DataTable
        v-model:filters="filters"
        :value="accounts"
        :paginator="accounts?.length > 0 && filteredValueCount > 0"
        removableSort
        :rows="10"
        :rowsPerPageOptions="[10, 20, 50, 100]"
        tableStyle="md:min-width: 50rem"
        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
        :currentPageReportTemplate="$t('public.paginator_caption')"
        :globalFilterFields="['name']"
        ref="dt"
        selectionMode="single"
        @row-click="(event) => openDialog(event.data)"
        :loading="loading"
        @filter="handleFilter"
    >
        <template #empty><Empty :title="$t('public.empty_member_title')" :message="$t('public.empty_member_message')" /></template>
        <template #loading>
            <div class="flex flex-col gap-2 items-center justify-center">
                <Loader />
                <span class="text-sm text-gray-700">{{ $t('public.loading_users_caption') }}</span>
            </div>
        </template>
        <template v-if="accounts?.length > 0">
            <Column
                field="last_login"
                sortable
                class="hidden md:table-cell"
            >
                <template #header>
                    <span class="hidden md:block max-w-[40px] md:max-w-[60px] lg:max-w-[100px] truncate">{{ $t('public.last_logged_in') }}</span>
                </template>
                <template #body="slotProps">
                    {{ dayjs(slotProps.data.last_login).format('YYYY/MM/DD HH:mm:ss') }}
                </template>
            </Column>
            <Column
                field="user_name"
                sortable
                :header="$t('public.name')"
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                            <template v-if="slotProps.data.user_profile_photo">
                                <img :src="slotProps.data.user_profile_photo" alt="profile_photo">
                            </template>
                            <template v-else>
                                <DefaultProfilePhoto />
                            </template>
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="font-medium max-w-[120px] lg:max-w-[160px] xl:max-w-[400px] truncate">
                                {{ slotProps.data.user_name }}
                            </div>
                            <div class="text-gray-500 text-xs max-w-[120px] lg:max-w-[160px] xl:max-w-[400px] truncate">
                                {{ slotProps.data.user_email }}
                            </div>
                        </div>
                    </div>
                </template>
            </Column>
            <Column
                field="meta_login"
                sortable
                class="hidden md:table-cell"
            >
                <template #header>
                    <span class="hidden md:block max-w-[40px] lg:max-w-[100px] truncate">{{ $t('public.account') }}</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data.meta_login }}
                </template>
            </Column>
            <Column
                field="balance"
                sortable
                class="hidden md:table-cell"
            >
                <template #header>
                    <span class="hidden md:block max-w-[40px] lg:max-w-[100px] truncate">{{ $t('public.balance') }} ($)</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data.balance }}
                </template>
            </Column>
            <Column
                field="equity"
                sortable
                class="hidden md:table-cell"
            >
                <template #header>
                    <span class="hidden md:block max-w-[40px] lg:max-w-[100px] truncate">{{ $t('public.equity') }} ($)</span>
                </template>
                <template #body="slotProps">
                    {{ slotProps.data.equity }}
                </template>
            </Column>
            <Column
                field="action"
                header=""
                class="hidden md:table-cell"
            >
                <template #body="slotProps">
                    <AccountTableActions
                        :account="slotProps.data"
                    />
                </template>
            </Column>
            <Column class="md:hidden">
                <template #body="slotProps">
                    <div class="flex flex-col items-start gap-1 self-stretch">
                        <div class="flex items-center gap-2 self-stretch w-full">
                            <div class="flex items-center gap-3 w-full">
                                <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                    <template v-if="slotProps.data.profile_photo">
                                        <img :src="slotProps.data.profile_photo" alt="profile_photo">
                                    </template>
                                    <template v-else>
                                        <DefaultProfilePhoto />
                                    </template>
                                </div>
                                <div class="flex flex-col items-start">
                                    <div class="font-medium max-w-[120px] xxs:max-w-[140px] min-[390px]:max-w-[180px] xs:max-w-[220px] truncate">
                                        {{ slotProps.data.name }}
                                    </div>
                                    <div class="text-gray-500 text-xs max-w-[120px] xxs:max-w-[140px] min-[390px]:max-w-[180px] xs:max-w-[220px] truncate">
                                        {{ slotProps.data.email }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-end">
                                <MemberTableActions
                                    :member="slotProps.data"
                                />
                            </div>
                        </div>
                        <div class="flex items-center gap-1 h-[26px]">
                            <StatusBadge :value="slotProps.data.role">{{ $t(`public.${slotProps.data.role}`) }}</StatusBadge>
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
                            </div>
                        </div>
                    </div>
                </template>
            </Column>
        </template>
    </DataTable>
</template>
