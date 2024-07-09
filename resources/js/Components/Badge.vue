<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'info',
    },
    pill: {
        type: Boolean,
        default: false,
    }
});

const { variant, pill } = props;

const baseClasses = [
    'min-w-5 flex flex-col justify-center items-center text-center'
]

const variantClasses = (variant) => ({
    'bg-info-400': variant === 'info',
})

const classes = computed(() => [
    ...baseClasses,
    variantClasses(variant),
    {
        'rounded p-0.5': !pill,
        'rounded-full py-px px-1': pill,
    }
]);
</script>

<template>
    <div
        :class="classes"
    >
        <slot />
    </div>
</template>
