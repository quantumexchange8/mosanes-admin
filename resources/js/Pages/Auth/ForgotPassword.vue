<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { Head, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import { ref, onMounted, onBeforeUnmount } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submitted = ref(false);
const countdown = ref(60);
let interval;
const submittedEmail = ref('');

const startCountdown = () => {
    countdown.value = 60;
    interval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value -= 1;
        } else {
            clearInterval(interval);
            submitted.value = false;
        }
    }, 1000);
};

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            submittedEmail.value = form.email;
            submitted.value = true;
            startCountdown();
        },
    });
};

const goToLoginPage = () => {
    window.location.href = '/login'; // Redirect to the login page URL
};

onBeforeUnmount(() => {
    clearInterval(interval);
});

</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />
        
        <div v-if="!submitted" class="w-full flex flex-col items-center justify-center gap-8">
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="self-stretch text-center text-gray-950 text-xl font-semibold">Forgot password?</div>
                <div class="self-stretch text-center text-gray-500">No worries, we'll send you reset instructions.</div>
            </div>
            <form @submit.prevent="submit" class="flex flex-col items-center gap-6 self-stretch">
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="email" value="Email" />

                    <InputText
                        id="email"
                        type="email"
                        class="block w-full"
                        v-model="form.email"
                        autofocus
                        placeholder="Enter your email"
                        :invalid="!!form.errors.email"
                        autocomplete="username"
                    />

                    <InputError :message="form.errors.email" />
                </div>
                <div class="flex flex-col items-center gap-4 self-stretch">
                    <Button size="base" variant="primary-flat" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="submit">Send Reset Password Link</Button>
                    <Button size="base" variant="gray-text" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="goToLoginPage">Back to Log In</Button>
                </div>
            </form>
        </div>

        <div v-else class="w-full flex flex-col items-center justify-center gap-8">
            <div class="flex flex-col items-start gap-3 self-stretch">
                <div class="self-stretch text-center text-gray-950 text-xl font-semibold">Check your email</div>
                <div class="self-stretch text-center text-gray-500">We've sent a reset password link to <br/><span class="text-gray-900 font-medium">{{ submittedEmail }}</span> </div>
            </div>
            <div class="flex flex-col items-center justify-center gap-6 self-stretch">
                <Button size="base" variant="primary-flat" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="goToLoginPage">Back to Log In</Button>
                <div class="flex justify-between items-center self-stretch">
                    <div class="text-gray-700 text-sm font-medium">Didn't receive the email?</div>
                    <div class="text-gray-300 text-right text-sm font-semibold">Resend in {{ countdown }}s</div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
