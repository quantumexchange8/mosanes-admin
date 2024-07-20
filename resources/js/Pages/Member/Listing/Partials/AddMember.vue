<script setup>
import Button from "@/Components/Button.vue";
import Dialog from 'primevue/dialog';
import {ref} from "vue";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm } from '@inertiajs/vue3';
import Dropdown from "primevue/dropdown";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";

const visible = ref(false)

const form = useForm({
    name: '',
    email: '',
    dial_code: '',
    phone: '',
    upline: '',
    password: '',
    password_confirmation: '',
});

const submitForm = () => {
    form.dial_code = selectedCountry.value
    form.post(route('member.addNewMember'), {
        onSuccess: () => {
            visible.value = false
            form.reset();
        },
    })
};

const countries = ref()
const uplines = ref()
const selectedCountry = ref();
const getResults = async () => {
    try {
        const countriesResponse = await axios.get('/member/loadCountries');
        countries.value = countriesResponse.data;

        const uplineResponse = await axios.get('/member/loadUplines');
        uplines.value = uplineResponse.data;
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

getResults()
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        size="base"
        class='w-full md:w-auto'
        @click="visible = true"
    >
        Add member
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        header="New Member"
        class="dialog-xs md:dialog-md"
    >
        <form @submit.prevent="submitForm()">
            <div class="flex flex-col items-center gap-8 self-stretch">

                <!-- Basic Information -->
                <div class="flex flex-col gap-3 items-center self-stretch">
                    <div class="text-gray-950 font-semibold text-sm self-stretch">
                        Basic Information
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                        <div class="space-y-1 h-[66px]">
                            <InputLabel for="name" value="Full Name" />
                            <InputText
                                id="name"
                                type="name"
                                class="block w-full"
                                v-model="form.name"
                                placeholder="Name as per NRIC or passport"
                                :invalid="!!form.errors.name"
                                autofocus
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-1 h-[66px]">
                            <InputLabel for="email" value="Email" />
                            <InputText
                                id="email"
                                type="email"
                                class="block w-full"
                                v-model="form.email"
                                placeholder="Enter Email"
                                :invalid="!!form.errors.email"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="space-y-1 h-[66px]">
                            <InputLabel for="phone" value="Phone Number" />
                            <div class="flex gap-2 items-center self-stretch relative">
                                <Dropdown
                                    v-model="selectedCountry"
                                    :options="countries"
                                    filter
                                    :filterFields="['name', 'phone_code']"
                                    optionLabel="name"
                                    placeholder="Code"
                                    class="w-[100px]"
                                    scroll-height="236px"
                                >
                                    <template #value="slotProps">
                                        <div v-if="slotProps.value" class="flex items-center">
                                            <div>{{ slotProps.value.phone_code }}</div>
                                        </div>
                                        <span v-else>
                                            {{ slotProps.placeholder }}
                                        </span>
                                    </template>
                                    <template #option="slotProps">
                                        <div class="flex items-center w-[262px] md:max-w-[236px]">
                                            <div>{{ slotProps.option.name }} <span class="text-gray-500">{{ slotProps.option.phone_code }}</span></div>
                                        </div>
                                    </template>
                                </Dropdown>

                                <InputText
                                    id="phone"
                                    type="text"
                                    class="block w-full"
                                    v-model="form.phone"
                                    placeholder="Phone Number"
                                    :invalid="!!form.errors.phone"
                                />
                            </div>
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div class="space-y-1 h-[66px]">
                            <InputLabel for="email" value="Upline" />
                            <Dropdown
                                v-model="form.upline"
                                :options="uplines"
                                filter
                                :filterFields="['name', 'phone_code']"
                                optionLabel="name"
                                placeholder="Select Upline"
                                class="w-full"
                                scroll-height="236px"
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center gap-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full overflow-hidden">
                                                <template v-if="slotProps.value.profile_photo">
                                                    <img :src="slotProps.value.profile_photo" alt="profile_picture" />
                                                </template>
                                                <template v-else>
                                                    <DefaultProfilePhoto />
                                                </template>
                                            </div>
                                            <div>{{ slotProps.value.name }}</div>
                                        </div>
                                    </div>
                                    <span v-else class="text-gray-400">
                                            {{ slotProps.placeholder }}
                                    </span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-full overflow-hidden">
                                            <template v-if="slotProps.option.profile_photo">
                                                <img :src="slotProps.option.profile_photo" alt="profile_picture" />
                                            </template>
                                            <template v-else>
                                                <DefaultProfilePhoto />
                                            </template>
                                        </div>
                                        <div>{{ slotProps.option.name }}</div>
                                    </div>
                                </template>
                            </Dropdown>
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>
                </div>

                <!-- Kyc Verification -->
                <div class="flex flex-col gap-3 items-center self-stretch">
                    <div class="text-gray-950 font-semibold text-sm self-stretch">
                        KYC Verification
                    </div>
                    <div class="flex flex-col gap-3 items-start self-stretch">
                        <span class="text-xs text-gray-500">Maximum file size is 10 MB. Supported file types are .jpg and .png.</span>
                        <Button
                            type="button"
                            variant="primary-tonal"
                            size="base"
                        >
                            Browse
                        </Button>
                    </div>
                </div>

                <!-- Create Password -->
                <div class="flex flex-col gap-3 items-center self-stretch">
                    <div class="text-gray-950 font-semibold text-sm self-stretch">
                        Create Password
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                        <div class="space-y-1 h-[66px]">
                            <InputLabel for="password" value="Password" />
                            <InputText
                                id="password"
                                type="password"
                                class="block w-full"
                                v-model="form.password"
                                :invalid="!!form.errors.password"
                                placeholder="••••••••"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-1 h-[66px]">
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <InputText
                                id="password_confirmation"
                                type="password"
                                class="block w-full"
                                v-model="form.password_confirmation"
                                placeholder="••••••••"
                                :invalid="!!form.errors.password"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end pt-5 self-stretch">
                <Button
                    variant="primary-flat"
                    size="base"
                    @click="submitForm"
                >
                    Create
                </Button>
            </div>
        </form>
    </Dialog>
</template>
