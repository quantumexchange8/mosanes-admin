<script setup>
import Button from "@/Components/Button.vue";
import Dialog from 'primevue/dialog';
import {ref} from "vue";
import { IconPlus } from '@tabler/icons-vue';
import InputText from 'primevue/inputtext';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from "@inertiajs/vue3";
import ColorPicker from 'primevue/colorpicker';
import Dropdown from 'primevue/dropdown';

const visible = ref(false);
const colour = ref('ff0000');

const form = useForm({
    group_name: '',
    fee_charges: '',
    colour: '',
    agent_id: '',
    group_members: null,
})

const submitForm = () => {
    form.colour = colour.value;

    form.post(route('group.create'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            visible.value = false;
        },
        onError: () => {
            console.log('err');
        }
    })
}

const cities = ref([
    { name: 'New York', code: 'NY' },
    { name: 'Rome', code: 'RM' },
    { name: 'London', code: 'LDN' },
    { name: 'Istanbul', code: 'IST' },
    { name: 'Paris', code: 'PRS' },
]);

</script>

<template>
    <Button
        variant="primary-flat"
        type="button"
        size="base"
        class="w-full md:w-auto"
        v-slot="{ iconSizeClasses }"
        @click="visible = true"
    >
        <div class="flex justify-center items-center gap-3 self-stretch">
            <IconPlus size="20" stroke-width="1.25" color="white" />
            <div class="text-white text-center text-sm font-medium">
                New Group
            </div>
        </div>
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        header="New Group"
        class="dialog-xs sm:dialog-md"
    >
        <form @submit.prevent="submitForm()">
            <div class="flex flex-col items-center gap-8 self-stretch">
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <div class="self-stretch text-gray-950 text-sm font-bold">
                        Group Information
                    </div>
                    <div class="grid items-center gap-3 self-stretch grid-cols-1 md:grid-cols-2 md:gap-5">
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputLabel for="groupName" value="Group Name" :invalid="!!form.errors.group_name" />
                            <InputText
                                id="groupName"
                                type="text"
                                class="block w-full"
                                v-model="form.group_name"
                                placeholder="eg. Amazing Team"
                                :invalid="!!form.errors.group_name"
                            />
                            <InputError :message="form.errors.group_name" />
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputLabel for="feeCharges" value="Fee Charges" :invalid="!!form.errors.fee_charges" />
                            <InputText
                                id="feeCharges"
                                type="number"
                                class="block w-full"
                                v-model="form.fee_charges"
                                placeholder="0.00%"
                                :invalid="!!form.errors.fee_charges"
                            />
                            <InputError :message="form.errors.fee_charges" />
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch md:col-span-2">
                            <InputLabel for="colour" value="Colour" :invalid="!!form.errors.colour" />

                            <ColorPicker v-model="colour" id="colour"/>

                            <InputError :message="form.errors.colour" />
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-3 self-stretch">
                    <div class="self-stretch text-gray-950 text-sm font-bold">
                        Agent
                    </div>
                    <div class="flex flex-col items-start gap-3 self-stretch md:flex-row md:justify-center md:content-start md:gap-5 md:flex-wrap">
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-1">
                            <InputLabel for="agent_id" value="Agent" :invalid="!!form.errors.agent_id" />
                            <Dropdown
                                id="agent_id"
                                v-model="form.agent_id"
                                :options="cities"
                                optionLabel="name"
                                optionValue="code"
                                placeholder="Select agent"
                                class="w-full"
                            />
                            <InputError :message="form.errors.agent_id" />
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch md:flex-1">
                            <InputLabel for="groupMembers" value="Total Group Members" :invalid="!!form.errors.group_members" />
                            <InputText
                                id="groupMembers"
                                type="number"
                                class="block w-full"
                                v-model="form.group_members"
                                placeholder="0"
                                :invalid="!!form.errors.group_members"
                                disabled
                            />
                            <InputError :message="form.errors.group_members" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="pt-5 flex flex-col items-end self-stretch">
                <Button
                    variant="primary-flat"
                    size="base"
                    :disabled="form.processing"
                >
                    Create
                </Button>
            </div>
        </form>
    </Dialog>
</template>