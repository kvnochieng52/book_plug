<script setup>
import { computed } from 'vue'
import { StarIcon } from '@heroicons/vue/24/solid'
defineProps({
  book: { type: Object, required: true },
})
</script>

<template>
  <router-link
    :to="{ name: 'book-detail', params: { slug: book.slug } }"
    class="group block animate-fade-up"
  >
    <div class="book-cover aspect-[2/3] bg-ink-100">
      <img
        :src="book.cover"
        :alt="book.title"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
        loading="lazy"
      />
      <div class="absolute top-3 left-3 flex flex-wrap gap-1">
        <span
          v-if="book.tags?.includes('bestseller')"
          class="badge bg-accent-500 text-white"
        >Bestseller</span>
        <span
          v-if="book.tags?.includes('new')"
          class="badge bg-brand-600 text-white"
        >New</span>
        <span
          v-if="!book.hasPhysical"
          class="badge bg-ink-900/80 text-white"
        >Digital only</span>
      </div>
      <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-900/70 via-ink-900/20 to-transparent p-3">
        <div class="flex items-center gap-1 text-white text-xs">
          <StarIcon class="h-4 w-4 text-accent-400" />
          <span class="font-semibold">{{ book.rating.toFixed(1) }}</span>
          <span class="opacity-70">({{ book.reviews }})</span>
        </div>
      </div>
    </div>
    <div class="mt-3">
      <h3 class="font-semibold text-ink-900 leading-tight line-clamp-1 group-hover:text-brand-700 transition">
        {{ book.title }}
      </h3>
      <p class="text-sm text-ink-500">{{ book.author }}</p>
      <div class="mt-1 flex items-baseline gap-2">
        <span class="text-brand-700 font-bold">KES {{ book.digitalPrice.toFixed(2) }}</span>
        <span v-if="book.hasPhysical" class="text-xs text-ink-500 line-through">KES {{ book.physicalPrice.toFixed(2) }}</span>
      </div>
    </div>
  </router-link>
</template>
