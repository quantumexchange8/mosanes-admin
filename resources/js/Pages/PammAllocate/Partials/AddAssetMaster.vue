<script setup>
import Button from '@/Components/Button.vue';
import Dialog from 'primevue/dialog';
import { ref, watch, watchEffect, onMounted } from "vue";
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
import RadioButton from 'primevue/radiobutton';
import InputNumber from 'primevue/inputnumber';

const props = defineProps({
    groupsOptions: Array,
})
const visible = ref(false);
const selectedDate = ref();
const selectedGroups = ref([]);
const selectedMode = ref('auto');
const checked = ref(false);
const groupsOptions = ref(props.groupsOptions);

// Watch for changes in the props.groupsOptions
watch(() => props.groupsOptions, (newValue) => {
  groupsOptions.value = newValue;
});

// Define initial form state
const initialFormState = {
    step: 1,
    pamm_name: '',
    trader_name: '',
    started_at: '',
    groups: '',
    total_investors: '',
    total_fund: 0,
    asset_master: '',
    min_investment: 0,
    min_investment_period: '',
    performance_fee: '',
    total_gain: '',
    monthly_gain: '',
    latest: '',
    generation_mode: '',
    expected_gain: '',
};

// Initialize form with the initial state
const form = useForm({ ...initialFormState });

// Reset form fields to their initial values
const resetForm = () => {
    Object.keys(initialFormState).forEach(key => {
        form[key] = initialFormState[key];
    });
};

const clearDate = () => {
    selectedDate.value = '';
};

watch(selectedMode, (newValue, oldValue) => {
    form.expected_gain = '';
});


const nextStep = () => {
    form.groups = selectedGroups.value.map(item => item.value);
    form.generation_mode = selectedMode.value
    form.post(route('pamm_allocate.validateStep'), {
        onSuccess: () => {
            form.step += 1;
        }
    });
};

const createAssetMaster = () => {
    form.post(route('pamm_allocate.create_asset_master'), {
        onSuccess: () => {
            resetForm();
            selectedDate.value = '';
            selectedGroups.value = [];
            visible.value = false;
        }
    });
};


const previousStep = () => {
  if (form.step > 1) {
    form.step -= 1;
  }
};

const investmentPeriodOptions = ref([
    {name: 'Lock-free', value: 0 },
    {name: '3 months',  value: 3 },
    {name: '6 months',  value: 6 },
    {name: '9 months',  value: 9 },
    {name: '12 months', value: 12 },
    {name: '18 months', value: 18 },
    {name: '24 months', value: 24 },
    {name: '36 months', value: 36 },
])

// Define constants and reactive data
const PUBLIC_OPTION = { value: 'public', name: 'Public', color: 'ffffff' };

// Check if 'Public' is selected
function isPublicChecked() {
  return selectedGroups.value.some(item => item.value === PUBLIC_OPTION.value);
}

// Handle checkbox change and div click
function togglePublicSelection() {
  const isCurrentlyChecked = isPublicChecked();
  
  if (isCurrentlyChecked) {
    // Remove 'Public' from selection if it's currently selected
    selectedGroups.value = selectedGroups.value.filter(item => item.value !== PUBLIC_OPTION.value);
  } else {
    // Add 'Public' to selection and remove all other selections
    selectedGroups.value = [PUBLIC_OPTION];
  }
  
  // Update `checked` state based on the new state
  checked.value = !isCurrentlyChecked;
}

// Watch for changes in `selectedGroups`
watch(selectedGroups, (newValue) => {
  // If another option is selected, remove 'Public'
  if (newValue.length > 1 && isPublicChecked()) {
    selectedGroups.value = newValue.filter(item => item.value !== PUBLIC_OPTION.value);
  }

  // Update `checked` state based on 'Public' selection
  checked.value = isPublicChecked();
}, { deep: true });

</script>

<template>
    <Button type="button" variant="primary-flat" size="base" class='w-full md:w-auto' @click="visible = true">
        <IconPlus size="20" color="#ffffff" stroke-width="1.25" />
        {{ $t('public.new_asset_master') }}
    </Button>

    <Dialog v-model:visible="visible" modal :header="$t('public.new_asset_master')" class="dialog-xs md:dialog-md">
        <div class="flex flex-col items-center gap-8 self-stretch">
            <!-- Stepper -->
            <div class="flex justify-center items-center gap-2 self-stretch">
                <div
                    v-for="step in [1, 2, 3]"
                    :key="step"
                    class="h-1 flex-grow rounded-full relative overflow-hidden"
                    :class="{
                        'bg-primary-500': form.step >= step,
                        'bg-gray-200': form.step < step
                    }"
                >
                    <!-- Highlight the current step -->
                    <template v-if="form.step === step">
                        <div class="absolute inset-0 w-1/2 bg-primary-500"></div>
                        <div class="absolute inset-0 left-1/2 w-1/2 bg-gray-200"></div>
                    </template>
                </div>
            </div>
            <div v-if="form.step === 1" class="flex flex-col items-center gap-8 self-stretch">
                <!-- Basic Information -->
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <span class="self-stretch text-gray-950 text-sm font-bold">{{ $t('public.basic_information') }}</span>
                    <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
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
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
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
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                            <InputLabel for="started_at" :value="$t('public.created_date')" />
                            <div class="relative w-full">
                                <Calendar
                                    v-model="selectedDate"
                                    selectionMode="single"
                                    :manualInput="false"
                                    dateFormat="dd/mm/yy"
                                    showIcon
                                    iconDisplay="input"
                                    placeholder="yyyy/mm/dd - yyyy/mm/dd"
                                    class="w-full"
                                    :invalid="form.errors.started_at"
                                />
                                <div
                                    v-if="selectedDate"
                                    class="absolute top-2/4 -mt-2.5 right-4 text-gray-400 select-none cursor-pointer bg-white"
                                    @click="clearDate"
                                >
                                    <IconX size="20" stroke-width="1.25" />
                                </div>
                            </div>
                            <InputError :message="form.errors.started_at" />
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
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
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
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
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
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
            </div>
            <div v-if="form.step === 2" class="flex flex-col items-center gap-8 self-stretch">
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
                            <InputLabel for="performance_fee">{{ $t('public.performance_fee') }}</InputLabel>
                            <InputText
                                id="performance_fee"
                                type="number"
                                class="block w-full"
                                v-model="form.performance_fee"
                                :invalid="!!form.errors.performance_fee"
                                placeholder="0.00%"
                            />
                            <InputError :message="form.errors.performance_fee" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <div class="flex flex-col justify-center items-start gap-1 self-stretch">
                        <span class="self-stretch text-gray-950 text-sm font-bold">{{ $t('public.profit_information') }}</span>
                        <span class="self-stretch text-gray-500 text-xs">{{ $t('public.profit_information_message') }}</span>
                    </div>
                    <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                            <InputLabel for="total_gain" :value="$t('public.total_gain')" />
                            <InputText
                                id="total_gain"
                                type="number"
                                class="block w-full"
                                v-model="form.total_gain"
                                :invalid="!!form.errors.total_gain"
                                placeholder="0.00%"
                            />
                            <InputError :message="form.errors.total_gain" />
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                            <InputLabel for="monthly_gain" :value="$t('public.monthly_gain')" />
                            <InputText
                                id="monthly_gain"
                                type="number"
                                class="block w-full"
                                v-model="form.monthly_gain"
                                :invalid="!!form.errors.monthly_gain"
                                placeholder="0.00%"
                            />
                            <InputError :message="form.errors.monthly_gain" />
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-grow">
                            <InputLabel for="latest" :value="$t('public.latest')" />
                            <InputText
                                id="latest"
                                type="number"
                                class="block w-full"
                                v-model="form.latest"
                                :invalid="!!form.errors.latest"
                                placeholder="0.00%"
                            />
                            <InputError :message="form.errors.latest" />
                        </div>

                    </div>
                </div>
            </div>
            <div v-if="form.step === 3" class="flex flex-col items-center gap-8 self-stretch">
                <div class="flex flex-col items-center gap-3 self-stretch md:pb-5 md:gap-5">
                    <div class="flex flex-col justify-center items-start gap-1 self-stretch">
                        <span class="text-gray-950 text-sm font-bold">{{ $t('public.profit_display_setting') }}</span>
                        <span class="text-gray-500 text-xs">{{ $t('public.profit_display_setting_message') }}</span>
                    </div>
                    <div class="flex flex-col items-center gap-3 self-stretch md:gap-2">
                        <div class="w-full grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="flex flex-col items-start gap-1 self-stretch">
                                <InputLabel for="generate_mode" :value="$t('public.generate_mode')" />
                                <div class="w-full grid grid-cols-2 gap-5">
                                    <div class="flex items-center gap-2 text-sm text-gray-950">
                                        <RadioButton v-model="selectedMode" inputId="auto" value="auto" class="w-5 h-5" />
                                        <label for="auto">{{ $t('public.automatic') }}</label>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-950">
                                        <RadioButton v-model="selectedMode" inputId="manual" value="manual" class="w-5 h-5" />
                                        <label for="manual">{{ $t('public.custom') }}</label>
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
                                <div class="flex items-center px-2 flex-grow">
                                    <span class="flex-grow text-gray-950 text-xs font-semibold uppercase">{{ $t('public.date') }}</span>
                                </div>
                                <div class="flex items-center px-2 flex-grow">
                                    <span class="flex-grow text-gray-950 text-xs font-semibold uppercase">{{ $t('public.daily_profit') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
        </div>
        <div class="flex pt-5 self-stretch md:pt-7" 
            :class="{ 
                'flex-col items-end': form.step === 1,
                'justify-between items-center': form.step !== 1,
            }"
         >
            <Button v-if="form.step !== 1" type="button" variant="gray-tonal" size="base" :disabled="form.processing" @click="previousStep">
                {{ $t('public.back') }}
            </Button>

            <Button type="button" variant="primary-flat" size="base" :disabled="form.processing" @click="form.step === 3 ? createAssetMaster() : nextStep()">
                {{ form.step === 3 ? $t('public.create') : $t('public.next') }}
            </Button>
        </div>
    </Dialog>

</template>