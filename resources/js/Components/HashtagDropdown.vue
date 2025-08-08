<script setup>
    defineProps(['items', 'command', 'selectedIndex'])
</script>

<template>
  <div class="bg-white border rounded text-sm">
    <ul>
      <li
        v-for="(item, index) in items"
        :key="index"
        :class="[
          'cursor-pointer px-3 py-2',
          selectedIndex === index ? 'bg-primary-100 text-primary-500' : ''
        ]"
        @mousedown.prevent="command(item)"
      >
        #{{ item.label }}
      </li>
    </ul>
  </div>
</template>
