<script setup>
import Button from "@/Components/Button.vue";
import {
    IconEdit,
    IconSearch,
    IconCircleXFilled
} from "@tabler/icons-vue";
import InputText from 'primevue/inputtext';
import {ref} from "vue";

const search = ref('');

const clearSearch = () => {
    search.value = '';
}
</script>

<template>
    <div class="flex flex-col self-stretch py-5 px-6 bg-white rounded-2xl shadow-toast w-full">

    </div>
</template>