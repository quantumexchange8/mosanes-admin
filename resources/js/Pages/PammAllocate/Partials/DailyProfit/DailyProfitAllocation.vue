<script setup>
import { ref, h, watch, computed, onMounted } from "vue";
import Button from '@/Components/Button.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm, usePage } from '@inertiajs/vue3';
import RadioButton from 'primevue/radiobutton';
import { IconRefresh } from '@tabler/icons-vue';
import { transactionFormat } from '@/Composables/index.js';
import InputNumber from "primevue/inputnumber";

const { formatDate, formatDateTime, formatAmount } = transactionFormat();

const props = defineProps({
    master: Object
})

const selectedMode = ref('automatic');
const emit = defineEmits(['update:visible'])

const form = useForm({
    id: props.master.id,
    expected_gain: '',
    amount: 0,
});

const closeDialog = () => {
    emit('update:visible', false);
}

watch(selectedMode, (newValue, oldValue) => {
    form.expected_gain = '';
});

// Get today's date
const today = ref(formatDate(new Date()));

// Create a ref to hold the end-of-month date
const endOfMonth = ref('');

// Function to calculate the end of the month date
const calculateEndOfMonth = () => {
    const date = new Date();
    date.setMonth(date.getMonth() + 1);
    date.setDate(0);
    endOfMonth.value = formatDate(date);
}

// Calculate and assign the end-of-month date
calculateEndOfMonth();

const submit = () => {
    form.post(route('pamm_allocate.validateStep'))
}
</script>

<template>
    <div class="flex flex-col items-center gap-3 self-stretch md:pb-5 md:gap-5">
        <div class="flex flex-col items-center gap-3 self-stretch md:gap-2">
            <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="generate_mode" :value="$t('public.generate_mode')" />
                    <div class="w-full grid grid-cols-2 gap-5">
                        <div class="flex items-center gap-2 text-sm text-gray-950">
                            <RadioButton v-model="selectedMode" inputId="automatic" value="automatic" class="w-5 h-5" />
                            <label for="automatic">{{ $t('public.automatic') }}</label>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-950">
                            <RadioButton v-model="selectedMode" inputId="custom" value="custom" class="w-5 h-5" />
                            <label for="custom">{{ $t('public.custom') }}</label>
                        </div>
                    </div>
                </div>
                <div class="min-w-[200px] flex flex-col items-start gap-1 self-stretch md:min-w-60 md:flex-grow">
                    <InputLabel for="expected_gain" :value="$t('public.expected_gain')" />
                    <InputText
                        id="expected_gain"
                        type="number"
                        class="block w-full"
                        v-model="form.expected_gain"
                        :invalid="!!form.errors.expected_gain"
                        placeholder="0.00%"
                        :disabled="selectedMode == 'custom'"
                    />
                    <InputError :message="form.errors.expected_gain" />
                </div>
            </div>
            <div v-if="selectedMode == 'automatic'" class="w-full flex justify-center items-end md:justify-end">
                <Button type="button" variant="primary-text" size="sm" :disabled="!form.expected_gain || form.processing">
                    <IconRefresh size="20" stroke-width="1.25" />
                    {{ $t('public.generate_again') }}
                </Button>
            </div>
            <div class="flex flex-col items-center gap-3 self-stretch md:min-w-[500px]">
                <div class="flex justify-between items-center py-2 self-stretch border-b border-gray-200 bg-gray-100">
                    <div class="flex items-center px-2 w-full">
                        <span class="w-full text-gray-950 text-xs font-semibold uppercase">{{ $t('public.date') }}</span>
                    </div>
                    <div class="flex items-center px-2 w-full">
                        <span class="w-full text-gray-950 text-xs font-semibold uppercase">{{ $t('public.daily_profit') + ' (%)' }}</span>
                    </div>
                </div>
                <div class="flex flex-col items-center self-stretch">
                    <div class="flex py-1 items-center self-stretch">
                        <div class="flex px-2 items-center w-full">
                            <span class="w-full text-gray-950 text-sm">test</span>
                        </div>
                        <div class="flex flex-col px-2 items-start w-full">
                            <InputNumber
                                v-model="form.amount"
                                :min="0"
                                :minFractionDigits="2"
                                fluid
                                :invalid="!!form.errors.amount"
                                inputClass="py-1.5 px-3 w-full border-none shadow-none"
                            />
                            <InputError :message="form.errors.amount" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex justify-end items-center pt-5 self-stretch md:pt-7">
        <Button type="button" variant="primary-flat" size="base" @click.prevent="submit">{{ $t('public.save') }}</Button>
    </div>
</template>