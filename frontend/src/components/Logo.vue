<script setup>
defineProps({ compact: { type: Boolean, default: false } })
</script>

<template>
  <router-link to="/" class="flex items-center gap-2 group">
    <span class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl bg-hero-gradient shadow-book">
      <svg viewBox="0 0 24 24" class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 4h9a4 4 0 0 1 4 4v12H8a4 4 0 0 1-4-4V4Z" />
        <path d="M4 4h9a4 4 0 0 1 4 4" opacity=".5" />
        <path d="M20 6v14" />
      </svg>
    </span>
    <span v-if="!compact" class="font-display font-extrabold text-xl tracking-tight">
      Book<span class="text-brand-600">Plug</span>
    </span>
  </router-link>
</template>
