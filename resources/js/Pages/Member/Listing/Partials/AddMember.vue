<script setup>
import Button from "@/Components/Button.vue";
import Dialog from 'primevue/dialog';
import {ref} from "vue";
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm } from '@inertiajs/vue3';

const visible = ref(false)

const form = useForm({
    name: '',
    email: '',
    phone: '',
    upline: '',
    password: '',
    confirm_password: '',
});

const create = () => {
    form.post(route('member.addNewMember'), {
        onSuccess: () => {
            visible.value = false;
        },
    })
    // visible.value = false;
};

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
        class="dialog-xs sm:dialog-md"
    >
        <div class="flex flex-col items-center gap-8 self-stretch">
            <div class="flex flex-col items-center gap-3 self-stretch">
                <span class="text-gray-950 text-sm font-bold self-stretch">Basic Information</span>
                <div class="flex flex-col md:flex-row items-center md:items-start md:content-start gap-3 md:gap-5 self-stretch md:flex-wrap">
                    <div class="h-[66px] md:h-auto md:min-w-[200px] flex flex-col items-start gap-1 self-stretch md:self-auto md:flex-grow">
                        <InputLabel for="name" value="Name" />
                        <InputText 
                            id="name"
                            type="text"
                            class="block w-full"
                            v-model="form.name"
                            placeholder="Name as per NRIC or passport"
                            :invalid="form.errors.name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="h-[66px] md:h-auto md:min-w-[200px] flex flex-col items-start gap-1 self-stretch md:self-auto md:flex-grow">
                        <InputLabel for="email" value="Email" />
                        <InputText 
                            id="email"
                            type="text"
                            class="block w-full"
                            v-model="form.email"
                            placeholder="Enter Email"
                            :invalid="form.errors.email"
                        />
                        <InputError :message="form.errors.email" />

                    </div>
                    <div class="h-[66px] md:h-auto md:min-w-[200px] flex flex-col items-start gap-1 self-stretch md:self-auto md:flex-grow">
                        <InputLabel for="phone" value="Phone Number" />
                        <div class="flex items-center gap-2 self-stretch">

                            <InputText 
                            id="phone"
                            type="phone"
                            class="block w-full"
                            v-model="form.phone"
                            placeholder="Enter your phone"
                            :invalid="!!form.errors.phone"
                            autocomplete="phone"
                            />
                        </div>
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div class="h-[66px] md:h-auto md:min-w-[200px] flex flex-col items-start gap-1 self-stretch md:self-auto md:flex-grow">
                        <InputLabel for="upline" value="Upline" />
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-center gap-3 self-stretch">
                <span class="text-gray-950 text-sm font-bold self-stretch">KYC Verification</span>
            </div>
            <div class="flex flex-col items-center gap-3 self-stretch">
                <span class="text-gray-950 text-sm font-bold self-stretch">Create Password</span>
                <div class="flex flex-col items-start md:items-center gap-3 md:gap-1 self-stretch">
                    <!-- below md -->
                     <div class="md:hidden w-full">
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputLabel for="password" value="Password" />
                            <InputText
                                type="password"
                                class="block w-full"
                                v-model="form.password"
                                :invalid="!!form.errors.password"
                                placeholder="••••••••"
                            />
                            <InputError :message="form.errors.password" />
                            <span class="self-stretch text-gray-500 text-xs">Must be at least 8 characters containing uppercase letters, lowercase letters, numbers, and symbols.</span>
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <InputLabel for="confirm_password" value="Confirm Password" />
                            <InputText
                                type="password"
                                class="block w-full"
                                v-model="form.confirm_password"
                                :invalid="!!form.errors.confirm_password"
                                placeholder="••••••••"
                            />
                            <InputError :message="form.errors.confirm_password" />
                        </div>
                     </div>
                    <!-- above md -->
                    <div class="hidden md:flex items-center gap-5 self-stretch">
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="password" value="Password" />
                            <InputText
                                type="password"
                                class="block w-full"
                                v-model="form.password"
                                :invalid="!!form.errors.password"
                                placeholder="••••••••"
                            />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="flex flex-col items-start gap-1 flex-1">
                            <InputLabel for="confirm_password" value="Confirm Password" />
                            <InputText
                                type="password"
                                class="block w-full"
                                v-model="form.confirm_password"
                                :invalid="!!form.errors.confirm_password"
                                placeholder="••••••••"
                            />
                            <InputError :message="form.errors.confirm_password" />
                        </div>
                    </div>
                    <span class="hidden md:flex self-stretch text-gray-500 text-xs">Must be at least 8 characters containing uppercase letters, lowercase letters, numbers, and symbols.</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-end pt-5 self-stretch">
            <Button type="button" variant="primary-flat" size="base" @click="create">
                Create
            </Button>
        </div>
    </Dialog>
</template>
