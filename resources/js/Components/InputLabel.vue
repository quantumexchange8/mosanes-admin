<script setup>
defineProps({
    value: {
        type: String,
    },
    invalid: Boolean
});
</script>

<template>
    <label
        class="block font-medium text-xs"
        :class="{
            'text-gray-700': !invalid,
            'text-error-500': invalid
        }"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
