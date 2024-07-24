<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/Button.vue';
import { IconRefresh, IconAdjustmentsHorizontal } from '@tabler/icons-vue';
import DataTable from 'primevue/datatable';
import { ref } from 'vue';
import Column from 'primevue/column';
import Empty from '@/Components/Empty.vue';

const accountTypes = ref();
accountTypes.value = [
{
    name: "MarketMaster",
    max_acc: 10,
    trade_delay: "0 sec",
    total_acc: 250
},
{
    name: "InvestPro",
    max_acc: 8,
    trade_delay: "0 sec",
    total_acc: 150
},
{
    name: "FinanceGuru",
    max_acc: 3,
    trade_delay: "0 sec",
    total_acc: 75
},
{
    name: "TraderJoe",
    max_acc: 5,
    trade_delay: "0 sec",
    total_acc: 100
},
{
    name: "WealthWizard",
    max_acc: 12,
    trade_delay: "0 sec",
    total_acc: 300
},
];
</script>

<template>
    <AuthenticatedLayout title="Account Type">
        <div class="flex flex-col items-center gap-8">
            <div class="flex justify-end items-center self-stretch">
                <Button
                    variant="primary-flat"
                >
                    <IconRefresh size="20" stroke-width="1.25" color="#FFF" />
                    Synchronise
                </Button>
            </div>

            <div class="p-6 flex flex-col justify-center items-center gap-6 self-stretch rounded-2xl border border-solid border-gray-200 bg-white shadow-table">
                <DataTable
                    :value="accountTypes"
                    removableSort
                >
                    <template #empty>
                        <Empty title="No Account Type Yet" message="Looks like you haven't created any account types yet. Let's get started by adding your first one!" />
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
                            {{ slotProps.data.max_acc }}
                        </template>
                    </Column>
                    <Column field="trade_delay" style="width: 20%" >
                        <template #header>
                            <span>trade delay</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.trade_delay }}
                        </template>
                    </Column>
                    <Column field="total_acc" sortable style="width: 20%" >
                        <template #header>
                            <span>total account</span>
                        </template>
                        <template #body="slotProps">
                            {{ slotProps.data.total_acc }}
                        </template>
                    </Column>
                    <Column field="action" style="width: 15%" >
                        <template #body="slotProps">
                            <div class="py-2 px-3 flex justify-center items-center gap-2 flex-1">
                                <div>toggle</div>
                                <Button
                                    variant="gray-text"
                                    size="sm"
                                    iconOnly
                                >
                                    <IconAdjustmentsHorizontal size="16" stroke-width="1.25" color="#667085" />
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>