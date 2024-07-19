<script setup>
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import InputText from 'primevue/inputtext';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm } from "@inertiajs/vue3";
import ColorPicker from 'primevue/colorpicker';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    group: Object,
})

const emit = defineEmits(['closeDialog']);

const name = ref(props.group.groupName);
const chargesPercent = ref(props.group.groupChargesPercent);
const colour = ref(props.group.groupColour);
const agentId = ref('LDN');
const memberCount = ref(props.group.groupMemberCount);

const form = useForm({
    group_name: '',
    fee_charges: '',
    colour: '',
    agent_id: '',
    group_members: null,
})

const submitForm = () => {
    form.group_name = name.value;
    form.fee_charges = chargesPercent.value;
    form.colour = colour.value;
    form.agent_id = agentId.value;
    form.group_members = memberCount.value;

    form.put(route('group.edit'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('closeDialog', false);
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
                            v-model="name"
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
                            v-model="chargesPercent"
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
                            v-model="agentId"
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
                            v-model="memberCount"
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
                Save
            </Button>
        </div>
    </form>
</template>