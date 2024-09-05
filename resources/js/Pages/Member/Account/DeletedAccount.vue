<script setup>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import {ref} from "vue";
import {FilterMatchMode} from "primevue/api";

const accounts = ref([]);
const loading = ref(false);
const filteredValueCount = ref(0);

const exportCSV = () => {
    dt.value.exportCSV();
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    upline_id: { value: null, matchMode: FilterMatchMode.EQUALS },
    group_id: { value: null, matchMode: FilterMatchMode.EQUALS },
    role: { value: null, matchMode: FilterMatchMode.EQUALS },
    status: { value: null, matchMode: FilterMatchMode.EQUALS },
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
        currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
        :globalFilterFields="['name']"
        ref="dt"
        selectionMode="single"
        @row-click="(event) => openDialog(event.data)"
        :loading="loading"
        @filter="handleFilter"
    >
    </DataTable>
</template>
