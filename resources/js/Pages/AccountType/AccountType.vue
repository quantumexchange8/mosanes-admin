<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/Button.vue';
import { IconRefresh } from '@tabler/icons-vue';
import DataTable from 'primevue/datatable';
import { onMounted, ref, watchEffect } from 'vue';
import Column from 'primevue/column';
import Empty from '@/Components/Empty.vue';
import Loader from "@/Components/Loader.vue";
import { usePage } from '@inertiajs/vue3';
import AccountTypeSetting from '@/Pages/AccountType/Partials/AccountTypeSetting.vue';

const accountTypes = ref();
const loading = ref(false);

const getAccountTypes = async () => {
    loading.value = true;

    try {
        const response = await axios.get('/account_type/getAccountTypes');
        accountTypes.value = response.data.accountTypes;
    } catch (error) {
        console.error('Error getting account types:', error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    getAccountTypes();
})

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getAccountTypes();
    }
});
</script>

<template>
    <AuthenticatedLayout title="Account Type">
        <div class="flex flex-col items-center gap-8">
            <div class="flex justify-end items-center self-stretch">
                <Button
                    variant="primary-flat"
                    type="button"
                    :href="route('accountType.syncAccountTypes')"
                >
                    <IconRefresh size="20" stroke-width="1.25" color="#FFF" />
                    Synchronise
                </Button>
            </div>

            <div class="p-6 flex flex-col justify-center items-center gap-6 self-stretch rounded-2xl border border-solid border-gray-200 bg-white shadow-table">
                <DataTable
                    :value="accountTypes"
                    removableSort
                    :loading="loading"
                >
                    <template #empty>
                        <Empty title="No Account Type Yet" message="Looks like you haven't created any account types yet. Let's get started by adding your first one!" />
                    </template>

                    <template #loading>
                        <div class="flex flex-col gap-2 items-center justify-center">
                            <Loader />
                            <span class="text-sm text-gray-700">Loading account types data. Please wait.</span>
                        </div>
                    </template>

                    <Column field="name" sortable style="width: 25%" >
                        <template #header>
                            <span>name</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.name }}
                        </template>
                    </Column>
                    <Column field="max_acc" style="width: 20%" >
                        <template #header>
                            <span>max.account</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.maximum_account_number }}
                        </template>
                    </Column>
                    <Column field="trade_delay" style="width: 20%" >
                        <template #header>
                            <span>trade delay</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.trade_open_duration }}
                        </template>
                    </Column>
                    <Column field="total_acc" sortable style="width: 20%" >
                        <template #header>
                            <span>total account</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.total_account }}
                        </template>
                    </Column>
                    <Column field="action" style="width: 15%" >
                        <template #body="slotProps">
                            <div class="py-2 px-3 flex justify-center items-center gap-2 flex-1">
                                <div>toggle</div>
                                
                                <AccountTypeSetting :account_type="slotProps.data" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>