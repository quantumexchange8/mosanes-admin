<script setup>
import ConfirmDialog from 'primevue/confirmdialog';
import Button from "@/Components/Button.vue";
import { SecurityIcon, BulbIcon, DeleteIcon } from "@/Components/Icons/brand.jsx";

const props = defineProps({
    variant: {
        type: String,
        default: 'error'
    }
})

const bgColor = {
    primary: 'bg-primary-700',
    gray: 'bg-gray-900',
    error: 'bg-error-600',
}[props.variant];

const iconComponent = {
    primary: BulbIcon,
    gray: SecurityIcon,
    error: DeleteIcon,
}[props.variant];

const buttonVariant = {
    primary: 'primary-flat',
    gray: 'gray-flat',
    error: 'error-flat',
}[props.variant];

</script>

<template>
    <ConfirmDialog group="headless">
        <template #container="{ message, acceptCallback, rejectCallback }">
            <div class="flex flex-col items-center bg-white rounded-3xl w-[320px] h-[250px] md:w-[500px] md:h-[300px]">
                <div class="relative w-full h-[93px] md:h-[132px] flex">
                    <div
                        class="rounded-tl-3xl rounded-tr-3xl flex w-full p-1 justify-center [clip-path:polygon(0%_0%,_100%_0%,_100%_78%,_50%_100%,_0_78%)]"
                        :class="[bgColor]"
                    >
                        <div class="p-5 flex items-center justify-center">
                            <component :is="iconComponent" class="w-16 h-16 md:w-full md:h-auto" />
                        </div>
                    </div>
                </div>
                <div class="pt-2 md:pt-3 pb-6 px-4 md:px-6 w-full flex flex-col items-center gap-5 self-stretch">
                    <div class="flex flex-col gap-1 items-center self-stretch text-center">
                        <span class="text-gray-950 text-sm md:text-base font-semibold">{{ message.header }}</span>
                        <span class="text-gray-700 text-xs md:text-sm">{{ message.message }}</span>
                    </div>
                    <div class="flex items-center gap-4 md:gap-5 self-stretch">
                        <Button
                            variant="gray-tonal"
                            @click="rejectCallback"
                            class="w-full"
                            size="base"
                        >
                            {{ $t('public.cancel') }}
                        </Button>
                        <Button
                            :variant="buttonVariant"
                            @click="acceptCallback"
                            class="w-full text-nowrap"
                            size="base"
                        >
                            {{ message.acceptButton }}
                        </Button>
                    </div>
                </div>
            </div>
        </template>
    </ConfirmDialog>
</template>
