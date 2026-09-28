<script setup>
defineProps({
  icon: { type: String, default: '📚' },
  title: { type: String, required: true },
  message: { type: String, default: '' },
})
</script>

<template>
  <div class="card p-10 text-center">
    <div class="mx-auto h-16 w-16 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-3xl">
      {{ icon }}
    </div>
    <h3 class="mt-4 font-semibold text-lg">{{ title }}</h3>
    <p v-if="message" class="text-ink-500 mt-1">{{ message }}</p>
    <div class="mt-4"><slot /></div>
  </div>
</template>
