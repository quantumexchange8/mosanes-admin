<script setup>
import { ref, h, watch, computed, onMounted } from "vue";
import Button from '@/Components/Button.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Dropdown from "primevue/dropdown";

const props = defineProps({
    master: Object
})

const form = useForm({
    id: props.master.id,
    history_period: '',
});

// Function to get the current month and year as a string
const getCurrentMonthYear = () => {
  const date = new Date();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${month}/${year}`;
};

// Reactive variables
const selectedMonths = ref(getCurrentMonthYear());

const historyPerioddOptions = ref([
    {value: '06/2024'},
    {value: '07/2024'},
    {value: '08/2024'},
])

// watch(() => selectedMonths.value, (newMonth) => {
//     selectedMonths.value = newMonth;
//     console.log(selectedMonths);
// });

</script>

<template>
    <div class="flex flex-col items-center gap-5 self-stretch flex-grow md:flex-grow-0">
        <!-- Header -->
        <div class="flex flex-col items-start self-stretch md:hidden">
            <span class="h-10 flex flex-col justify-center self-stretch text-gray-950 text-sm font-bold">{{ $t('public.generated_monthly_gain') }}</span>
            <div class="flex items-center gap-5 self-stretch">
                <span class="flex-grow text-gray-950 text-xl font-semibold">{{  }}</span>
                <Dropdown 
                    v-model="form.history_period" 
                    :options="historyPerioddOptions" 
                    optionLabel="name" 
                    optionValue="value"
                    class="border-none shadow-none"
                    scroll-height="236px"
                    :invalid="form.errors.history_period"
                />
            </div>
        </div>
        <div class="hidden md:flex flex-col items-start self-stretch">
            <div class="flex justify-between items-center self-stretch">
                <span class="text-gray-950 text-sm font-bold">{{ $t('public.generated_monthly_gain') }}</span>
                <Dropdown 
                    v-model="selectedMonths" 
                    :options="historyPerioddOptions" 
                    optionLabel="value" 
                    optionValue="value"
                    class="border-none shadow-none"
                    scroll-height="236px"
                    :invalid="form.errors.history_period"
                />
            </div>
            <span class="flex-grow text-gray-950 text-xl font-semibold">{{  }}</span>
        </div>
        <!-- Graph -->
        <div class="flex-grow self-stretch"></div>
    </div>
</template>