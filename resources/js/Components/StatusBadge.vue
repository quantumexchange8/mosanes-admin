<script setup>
import { computed } from "vue";

const props = defineProps({
  variant: {
    type: String,
    default: "success",
    validator(value) {
      return ["primary", "success", "error", "warning", "info", "gray"].includes(value);
    },
  },
  borderRadius: {
    default: "rounded",
  },
  value: String,
});

const baseClasses = [
  "flex px-2 py-1 justify-center items-center text-xs font-semibold hover:-translate-y-1 transition-all duration-300 ease-in-out",
];

const variantClasses = computed(() => {
    if (props.value.toLowerCase() === 'primary' || props.value.toLowerCase() === 'member') {
        return 'bg-primary-50 text-primary-500'
    } else if (props.value.toLowerCase() === 'success' || props.value.toLowerCase() === 'active' || props.value.toLowerCase() === 'live') {
        return 'bg-success-50 text-success-500'
    } else if (props.value.toLowerCase() === 'error') {
        return 'bg-error-50 text-error-500'
    } else if (props.value.toLowerCase() === 'warning') {
        return 'bg-warning-50 text-warning-500'
    } else if (props.value.toLowerCase() === 'info' || props.value.toLowerCase() === 'demo') {
        return 'bg-info-50 text-info-500'
    } else if (props.value.toLowerCase() === 'gray') {
        return 'bg-gray-50 text-gray-500'
    }
    return 'bg-primary-600 text-gray-950'
})

const borderClass = computed(() => {
  return {
    rounded: "rounded",
    20: "rounded-[20px]",
  }[props.borderRadius.toString()];
});

const classes = computed(() => [
    ...baseClasses,
    variantClasses.value
])

</script>

<template>
  <div :class="[borderClass, classes]">
    {{ value }}
  </div>
</template>
