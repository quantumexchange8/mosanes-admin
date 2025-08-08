<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="border-none focus:border focus:border-primary-500 focus:ring-primary-500 focus:rounded-lg focus:shadow-sm"
        v-model="model"
        ref="input"
    />
</template>
