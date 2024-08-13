<script setup>
import { IconWorld } from '@tabler/icons-vue';
import {ref} from "vue";
import {usePage, Head, router} from "@inertiajs/vue3";
import {loadLanguageAsync} from "laravel-vue-i18n";
import OverlayPanel from 'primevue/overlaypanel';
import Button from '@/Components/Button.vue'
import dayjs from "dayjs";

const op = ref();
const toggle = (event) => {
    op.value.toggle(event);
}

const currentLocale = ref(usePage().props.locale);
const locales = [
    {'label': 'English', 'value': 'en'},
    {'label': '中文', 'value': 'tw'},
];

const changeLanguage = async (langVal) => {
    try {
        currentLocale.value = langVal;
        await loadLanguageAsync(langVal);
        await axios.get(`/locale/${langVal}`);
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

const handleLogOut = () => {
    router.post(route('logout'))
}
</script>

<template>
    <Head :title="$t('public.dashboard')" />
    <div
        style="background-image: url('/img/background-login.svg'); background-repeat: repeat-x;"
    >
        <div class="flex flex-col min-h-screen">
            <div class="flex py-3 px-5 md:px-10 justify-end items-center">
                <div
                    class="w-[60px] h-[60px] p-[17.5px] flex items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-100 text-gray-800"
                    @click="toggle"
                >
                    <IconWorld size="25" stroke-width="1.25" />
                </div>
            </div>
            <div class="flex flex-1 flex-col justify-between md:gap-[60px] md:justify-center items-center px-4 pb-8 md:py-12 md:px-24">
                <div class="flex flex-col justify-center items-center self-stretch">
                    <img src="/img/errors/illustration-403.svg" class="" alt="">
                    <div class="flex flex-col gap-8 items-center self-stretch">
                        <div class="flex flex-col items-center gap-3 text-center">
                            <div class="text-gray-950 text-xl font-bold">
                                {{$t('public.access_denied')}}
                            </div>
                            <div class="text-gray-500">
                                {{$t('public.access_denied_desc')}}
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="primary-flat"
                            class="w-full md:w-auto text-nowrap justify-center"
                            @click="handleLogOut"
                        >
                            {{$t('public.logout')}}
                        </Button>
                    </div>
                </div>
                <div class="text-center text-gray-500 text-xs">© {{ dayjs().year() }} mosanes. All rights reserved.</div>
            </div>
        </div>
    </div>

    <OverlayPanel ref="op">
        <div class="py-2 flex flex-col items-center w-[120px]">
            <div
                v-for="locale in locales"
                class="p-3 flex items-center gap-3 self-stretch text-sm hover:bg-gray-100 hover:cursor-pointer"
                :class="{'bg-primary-50 text-primary-500': locale.value === currentLocale}"
                @click="changeLanguage(locale.value)"
            >
                {{ locale.label }}
            </div>
        </div>
    </OverlayPanel>
</template>
