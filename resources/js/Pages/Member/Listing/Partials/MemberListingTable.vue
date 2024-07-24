<script setup>
import { ref } from "vue";
import Dialog from 'primevue/dialog';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import RadioButton from 'primevue/radiobutton';
import Chip from 'primevue/chip';
import Textarea from 'primevue/textarea';
import { useForm } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue'
import StatusBadge from '@/Components/StatusBadge.vue';
import Empty from '@/Components/Empty.vue';
import { useConfirm } from "primevue/useconfirm";
import ConfirmationDialog from "@/Components/ConfirmationDialog.vue";

const hasData = ref(false);
</script>

<template>
    <Empty title="No Members Found" message="There are no members in the listing yet. Once members join, you'll see their details here." />

    
</template>
