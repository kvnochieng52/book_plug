<script setup>
import { CheckCircleIcon } from '@heroicons/vue/24/solid'
</script>

<template>
  <section class="max-w-3xl mx-auto px-4 py-20 text-center">
    <div class="mx-auto h-20 w-20 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center">
      <CheckCircleIcon class="h-12 w-12" />
    </div>
    <h1 class="section-title mt-6">Thanks for your order!</h1>
    <p class="text-ink-600 mt-2 max-w-lg mx-auto">
      A confirmation is on its way to your inbox. Digital books are already
      waiting in your library. Physical orders will ship in 1–2 business days.
    </p>
    <div class="mt-8 flex justify-center gap-3">
      <router-link to="/library" class="btn-primary">Go to my library</router-link>
      <router-link to="/books" class="btn-outline">Keep browsing</router-link>
    </div>
  </section>
</template>
