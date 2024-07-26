<script setup>
import Button from '@/Components/Button.vue';
import { IconAdjustmentsHorizontal } from '@tabler/icons-vue';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import { onMounted, ref } from 'vue';
import InputText from 'primevue/inputtext';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    account_type:Object,
})

const visible = ref(false);
const categories = ref(['Individual', 'Manage']);
const trade_delay_duration = ref([
    {name: '0 sec', value: '0'},
    {name: '1 sec', value: '1'},
    {name: '2 sec', value: '2'},
    {name: '3 sec', value: '3'},
    {name: '4 sec', value: '4'},
    {name: '5 sec', value: '5'},
    {name: '6 sec', value: '6'},
    {name: '7 sec', value: '7'},
    {name: '8 sec', value: '8'},
    {name: '9 sec', value: '9'},
    {name: '10 sec', value: '10'},
    {name: '1 min', value: '60'},
    {name: '2 min', value: '120'},
    {name: '3 min', value: '180'},
    {name: '4 min', value: '240'},
    {name: '5 min', value: '300'},
])
const leverages = ref();
const getLeverages = async () => {
    try {
        const response = await axios.get('/account_type/getLevearges');
        leverages.value = response.data.leverages;
    } catch (error) {
        console.error('Error getting leverages:', error);
    }
}

const form = useForm({
    account_type_name: props.account_type.name,
    category: props.account_type.category,
    description: props.account_type.descriptions,
    leverage: props.account_type.leverage,
    trade_delay_duration: props.account_type.trade_open_duration,
    max_account: props.account_type.maximum_account_number,
})

const submitForm = () => {
    form.post(route('accountType.update', props.account_type.id), {
        preserveScroll: true,
        onSuccess: () => {
            visible.value = false;
        },
        onError: (e) => {
            console.log('Error submit form:', e);
        }
    })
}

onMounted(() => {
    getLeverages()
})
</script>

<template>
    <Button
        variant="gray-text"
        size="sm"
        iconOnly
        @click="visible = true"
    >
        <IconAdjustmentsHorizontal size="16" stroke-width="1.25" color="#667085" />
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.account_type_setting')"
        class="dialog-xs md:dialog-md"
    >
        <form @submit.prevent="submitForm()">
            <div class="flex flex-col items-center gap-8 self-stretch">
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <div class="self-stretch text-gray-950 text-sm font-bold">
                        {{ $t('public.account_information') }}
                    </div>
                    <div class="grid justify-center items-start content-start gap-5 self-stretch flex-wrap grid-cols-2">
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="account_type_name" :invalid="!!form.errors.account_type_name">
                                {{ $t('public.account_type_name') }}
                            </InputLabel>
                            <InputText
                                v-model="form.account_type_name"
                                id="account_type_name"
                                type="text"
                                class="w-full"
                                disabled
                            />
                            <InputError :message="form.errors.account_type_name" />
                        </div>
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="category" :value="$t('public.category')" :invalid="!!form.errors.category" />
                            <Dropdown
                                v-model="form.category"
                                id="category"
                                :options="categories"
                                class="w-full"
                            />
                            <InputError :message="form.errors.category" />
                        </div>
                        <div class="flex flex-col items-start gap-1 flex-1 col-span-2">
                            <InputLabel for="description" :invalid="!!form.errors.description">
                                {{ $t('public.description_en') }}
                            </InputLabel>
                            <InputText
                                v-model="form.description"
                                id="description"
                                type="text"
                                class="w-full"
                                placeholder="Tell more about this..."
                            />
                            <div class="self-stretch text-gray-500 text-xs">
                                {{ $t('public.description_caption') }}
                            </div>
                            <InputError :message="form.errors.description" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <div class="self-stretch text-gray-950 text-sm font-bold">
                        {{ $t('public.trading_conditions') }}
                    </div>
                    <div class="flex justify-center items-start content-start gap-5 self-stretch flex-wrap">
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="leverage" :value="$t('public.leverage')" :invalid="!!form.errors.leverage" />
                            <Dropdown
                                v-model="form.leverage"
                                id="category"
                                :options="leverages"
                                optionLabel="name"
                                optionValue="value"
                                class="w-full"
                            />
                            <InputError :message="form.errors.leverage" />
                        </div>
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="trade_delay_duration" :value="$t('public.trade_delay_duration')" :invalid="!!form.errors.trade_delay_duration" />
                            <Dropdown
                                v-model="form.trade_delay_duration"
                                id="trade_delay_duration"
                                :options="trade_delay_duration"
                                optionLabel="name"
                                optionValue="value"
                                class="w-full"
                            />
                            <InputError :message="form.errors.trade_delay_duration" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <div class="self-stretch text-gray-950 text-sm font-bold">
                        {{ $t('public.other_settings') }}
                    </div>
                    <div class="grid justify-center items-start content-start gap-5 self-stretch flex-wrap grid-cols-2">
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="max_account" :value="$t('public.maximum_account_creation')" :invalid="!!form.errors.max_account" />
                            <InputText
                                v-model="form.max_account"
                                id="max_account"
                                type="text"
                                class="w-full"
                                placeholder="0"
                            />
                            <InputError :message="form.errors.max_account" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-7 flex flex-col items-end self-stretch">
                <Button
                    variant="primary-flat"
                    :disabled="form.processing"
                >
                    {{ $t('public.save') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>