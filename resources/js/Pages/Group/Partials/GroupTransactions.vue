<script setup>
import { ref } from 'vue';
import { IconCloudDownload } from '@tabler/icons-vue';
import Button from '@/Components/Button.vue';
import Calendar from 'primevue/calendar';

const date = ref('');
</script>

<template>
    <div class="flex flex-col items-center gap-8 flex-1 self-stretch">
        <div class="flex flex-col items-center gap-4 self-stretch md:flex-row md:justify-between">
            <Calendar
                v-model="date"
                selectionMode="range"
                :manualInput="false"
                dateFormat="dd/mm/yy"
                class="w-full md:w-auto"
            />

            <Button
                variant="primary-outlined"
                type="button"
                v-slot="{ iconSizeClasses }"
                class="w-full md:w-auto"
            >
                Export
                <IconCloudDownload size="20" stroke-width="1.25" color="#2970FF"/>
            </Button>
        </div>

        <!-- this is a table -->
    </div>
</template>