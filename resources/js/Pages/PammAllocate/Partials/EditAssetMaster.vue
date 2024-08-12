<script setup>
import { ref, h, watch, computed, onMounted } from "vue";
import Button from '@/Components/Button.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm, usePage } from '@inertiajs/vue3';
import Dropdown from "primevue/dropdown";
import { IconPlus, IconX, IconRefresh } from '@tabler/icons-vue';
import Calendar from 'primevue/calendar';
import MultiSelect from 'primevue/multiselect';
import IconField from 'primevue/iconfield';
import FileUpload from 'primevue/fileupload';
import { transactionFormat } from '@/Composables/index.js';
import InputNumber from 'primevue/inputnumber';

const { formatDate, formatDateTime, formatAmount } = transactionFormat();

// Define props
const props = defineProps({
    master: Object,
    groupsOptions: Array,
});

// Define emits
const emit = defineEmits(['update:visible']);

// Define constants and reactive data
const PUBLIC_OPTION = { value: 'public', name: 'Public', color: 'ffffff' };

// Initialize groupsOptions and selectedGroups
const groupsOptions = ref(props.groupsOptions);
const selectedGroups = ref([]);
const checked = ref(false);

// Function to initialize selectedGroups based on visible_to
const initializeSelectedGroups = () => {
    const visibleTo = props.master.visible_to || [];
    if (visibleTo.includes('public')) {
        selectedGroups.value = [PUBLIC_OPTION];
    } else {
        selectedGroups.value = props.groupsOptions.filter(group => visibleTo.includes(group.value));
    }
    // Update `checked` state based on `selectedGroups`
    checked.value = selectedGroups.value.some(item => item.value === PUBLIC_OPTION.value);
};

// Initialize selectedGroups
initializeSelectedGroups();

// Watch for changes in props.groupsOptions and props.master.visible_to
watch([() => props.groupsOptions, () => props.master.visible_to], () => {
    groupsOptions.value = props.groupsOptions;
    initializeSelectedGroups();
}, { immediate: true });

// Define form using useForm
const form = useForm({
    id: props.master.id,
    pamm_name: props.master.asset_name,
    trader_name: props.master.trader_name,
    created_date: formatDate(props.master.created_at),
    groups: selectedGroups.value.map(group => group.value),
    total_investors: props.master.total_investors,
    total_fund: Number(props.master.total_fund),
    asset_master: '',
    min_investment: Number(props.master.minimum_investment),
    min_investment_period: props.master.minimum_investment_period,
    profit_sharing: '',
});

// Clear date function
const clearDate = () => {
    form.created_date = '';
};

// Handle checkbox change and div click
function togglePublicSelection() {
    const isCurrentlyChecked = selectedGroups.value.some(item => item.value === PUBLIC_OPTION.value);
    
    if (isCurrentlyChecked) {
        // Remove 'Public' from selection
        selectedGroups.value = selectedGroups.value.filter(item => item.value !== PUBLIC_OPTION.value);
    } else {
        // Add 'Public' to selection and remove all other selections
        selectedGroups.value = [PUBLIC_OPTION];
    }

    // Update `checked` state based on the new state
    checked.value = !isCurrentlyChecked;
}

// Watch for changes in selectedGroups
watch(selectedGroups, (newValue) => {
    // If another option is selected, remove 'Public'
    if (newValue.length > 1 && selectedGroups.value.some(item => item.value === PUBLIC_OPTION.value)) {
        selectedGroups.value = newValue.filter(item => item.value !== PUBLIC_OPTION.value);
    }

    // Update `checked` state based on 'Public' selection
    checked.value = selectedGroups.value.some(item => item.value === PUBLIC_OPTION.value);

    // Update form's groups field
    form.groups = selectedGroups.value.map(group => group.value);
}, { deep: true });

// Investment period options
const investmentPeriodOptions = ref([
    { name: 'Lock-free', value: 0 },
    { name: '3 months',  value: 3 },
    { name: '6 months',  value: 6 },
    { name: '9 months',  value: 9 },
    { name: '12 months', value: 12 },
    { name: '18 months', value: 18 },
    { name: '24 months', value: 24 },
    { name: '36 months', value: 36 },
]);

const editAssetMaster  = () => {
    form.post(route('pamm_allocate.edit_asset_master'), {
        onSuccess: () => {
            form.reset();
            emit('update:visible')
        }
    });
}
</script>

<template>
    <div class="flex flex-col items-center gap-8 self-stretch">
        <div class="flex flex-col items-center gap-3 self-stretch">
            <span class="self-stretch text-gray-950 text-sm font-bold">{{ $t('public.basic_information') }}</span>
            <div class="grid grid-cols-1 items-center gap-3 self-stretch md:grid-cols-2">
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel for="pamm_name" :value="$t('public.pamm_name')" />
                    <InputText
                        id="pamm_name"
                        type="text"
                        class="block w-full"
                        v-model="form.pamm_name"
                        :placeholder="$t('public.pamm_name_placeholder')"
                        :invalid="!!form.errors.pamm_name"
                        autofocus
                    />
                    <InputError :message="form.errors.pamm_name" />
                </div>
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel for="trader_name" :value="$t('public.trader_name')" />
                    <InputText
                        id="trader_name"
                        type="text"
                        class="block w-full"
                        v-model="form.trader_name"
                        :placeholder="$t('public.trader_name_placeholder')"
                        :invalid="!!form.errors.trader_name"
                        autofocus
                    />
                    <InputError :message="form.errors.trader_name" />
                </div>
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel for="created_date" :value="$t('public.created_date')" />
                    <div class="relative w-full">
                        <Calendar
                            v-model="form.created_date"
                            selectionMode="single"
                            :manualInput="false"
                            dateFormat="yy/mm/dd"
                            showIcon
                            iconDisplay="input"
                            placeholder="yyyy/mm/dd - yyyy/mm/dd"
                            class="w-full"
                            :invalid="form.errors.created_date"
                        />
                        <div
                            v-if="form.created_date"
                            class="absolute top-2/4 -mt-2.5 right-4 text-gray-400 select-none cursor-pointer bg-white"
                            @click="clearDate"
                        >
                            <IconX size="20" stroke-width="1.25" />
                        </div>
                    </div>
                    <InputError :message="form.errors.created_date" />
                </div>
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel for="visible_to" :value="$t('public.visible_to')" />
                    <MultiSelect 
                        v-model="selectedGroups"
                        :showToggleAll="false"
                        :options="groupsOptions"
                        class="w-full h-full"
                        :invalid="form.errors.groups"
                    >
                        <template #value="slotProps">
                            <!-- Check if slotProps.value is an array and display names, otherwise show placeholder -->
                            <span v-if="slotProps.value.length > 0">
                                {{ slotProps.value.map(item => item.name).join(', ') }}
                            </span>
                            <span v-else>
                                {{ $t('public.select_group_placeholder') }}
                            </span>
                        </template>
                        <template #option="slotProps">
                            <span v-for="slotProp in slotProps" class="px-2 py-1 rounded text-gray-950">
                            {{ slotProp.name }} 
                            </span>
                        </template>
                        <template #header>
                            <div class="flex items-center p-1 border-b rounded-tl-md rounded-tr-md  text-surface-700 bg-surface-0 border-gray-200">
                                <div 
                                    class="w-full flex items-center py-2 px-3 gap-2 rounded-l-md rounded-r-md  cursor-pointer"
                                    :class="{
                                        'hover:bg-surface-200': !checked,
                                        'hover:bg-primary-highlight-hover': checked
                                    }"
                                    @click="togglePublicSelection"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="checked"
                                        class="w-5 h-5 rounded-full border-gray-300 ring-transparent focus:ring-0 focus:ring-offset-0"
                                    />
                                    <span class="text-gray-950">{{ $t('public.public') }}</span>
                                </div>
                            </div>
                        </template>
                    </MultiSelect>
                    <InputError :message="form.errors.groups" />
                </div>
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel for="total_investors">{{ $t('public.total_investors') }}</InputLabel>
                    <InputText
                        id="total_investors"
                        type="text"
                        class="block w-full"
                        v-model="form.total_investors"
                        placeholder="0"
                        :invalid="!!form.errors.total_investors"
                        autofocus
                    />
                    <InputError :message="form.errors.total_investors" />
                </div>
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <InputLabel for="total_fund">{{ $t('public.total_fund') }}</InputLabel>
                    <InputNumber
                        v-model="form.total_fund"
                        inputId="currency-us"
                        prefix="$ "
                        class="w-full"
                        inputClass="py-3 px-4"
                        :min="0"
                        :step="100"
                        :minFractionDigits="2"
                        fluid
                        autofocus
                        :invalid="!!form.errors.total_fund"
                    />
                    <InputError :message="form.errors.total_fund" />
                </div>
            </div>
        </div>
        <div class="flex flex-col items-center gap-3 self-stretch">
            <span class="self-stretch text-gray-950 text-sm font-bold">{{ $t('public.upload_image') }}</span>
            <div class="flex flex-col items-start gap-3 self-stretch">
                <span class="text-xs text-gray-500">{{ $t('public.upload_image_caption') }}</span>
                <FileUpload
                        class="w-full"
                        name="asset_master"
                        url="/pamm_allocate/upload_image"
                        accept="image/*"
                        :maxFileSize="10485760"
                        auto
                    >
                    <template #header="{ chooseCallback }">
                        <div class="flex flex-wrap justify-between items-center flex-1 gap-2">
                            <div class="flex gap-2">
                                <Button
                                    type="button"
                                    variant="primary-tonal"
                                    @click="chooseCallback()"
                                >
                                    {{ $t('public.browse') }}
                                </Button>
                            </div>
                        </div>
                    </template>
                </FileUpload>
            </div>
        </div>
        <div class="flex flex-col items-center gap-3 self-stretch">
            <span class="self-stretch text-gray-950 text-sm font-bold">{{ $t('public.joining_conditions') }}</span>
            <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                    <InputLabel for="min_investment">{{ $t('public.min_investment') }}</InputLabel>
                    <InputNumber
                        v-model="form.min_investment"
                        inputId="currency-us"
                        prefix="$ "
                        class="w-full"
                        inputClass="py-3 px-4"
                        :min="0"
                        :step="100"
                        :minFractionDigits="2"
                        fluid
                        autofocus
                        :invalid="!!form.errors.min_investment"
                    />
                    <InputError :message="form.errors.min_investment" />
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                    <InputLabel for="min_investment_period" :value="$t('public.min_investment_period')" />
                    <Dropdown 
                        v-model="form.min_investment_period" 
                        :options="investmentPeriodOptions" 
                        optionLabel="name" 
                        optionValue="value"
                        :placeholder="$t('public.min_investment_period_placeholder')" 
                        class="w-full"
                        scroll-height="236px"
                        :invalid="form.errors.min_investment_period"
                    />
                    <InputError :message="form.errors.min_investment_period" />
                </div>
                <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                    <InputLabel for="profit_sharing">{{ $t('public.profit_sharing_label') }}</InputLabel>
                    <InputText
                        id="profit_sharing"
                        type="number"
                        class="block w-full"
                        v-model="form.profit_sharing"
                        :invalid="!!form.errors.profit_sharing"
                        placeholder="0.00%"
                    />
                    <InputError :message="form.errors.profit_sharing" />
                </div>
            </div>
        </div>
    </div>
    <div class="flex justify-end items-end pt-5 self-stretch md:pt-7">
        <Button type="button" variant="primary-flat" size="base" @click="editAssetMaster">{{ $t('public.save') }}</Button>
    </div>
</template>