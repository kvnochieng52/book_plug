<script setup>
import { BookOpenIcon, GlobeAltIcon, HeartIcon } from '@heroicons/vue/24/outline'
</script>

<template>
  <section class="max-w-4xl mx-auto px-4 py-16">
    <span class="badge bg-brand-100 text-brand-800">About us</span>
    <h1 class="section-title mt-3">A bookstore built for readers.</h1>
    <p class="text-lg text-ink-600 mt-4">
      BookPlug is a small, independent platform for readers who love good
      writing — whether they read on paper or on a screen. We publish
      established authors and new voices side by side, price fairly, and pay
      creators promptly.
    </p>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
      <div class="card p-6">
        <BookOpenIcon class="h-8 w-8 text-brand-600" />
        <h3 class="font-semibold mt-3">Curated, not endless</h3>
        <p class="text-sm text-ink-600 mt-1">Every title on BookPlug is chosen by a real human. No infinite scroll of noise.</p>
      </div>
      <div class="card p-6">
        <GlobeAltIcon class="h-8 w-8 text-accent-600" />
        <h3 class="font-semibold mt-3">Global by default</h3>
        <p class="text-sm text-ink-600 mt-1">Digital anywhere in the world; physical delivery across East Africa and beyond.</p>
      </div>
      <div class="card p-6">
        <HeartIcon class="h-8 w-8 text-rose-500" />
        <h3 class="font-semibold mt-3">Author-first</h3>
        <p class="text-sm text-ink-600 mt-1">We pay authors monthly and share revenue transparently on every sale.</p>
      </div>
    </div>

    <div class="mt-14 rounded-3xl bg-hero-gradient p-10 text-white">
      <h3 class="font-display text-3xl font-bold">Our promise</h3>
      <p class="mt-3 text-white/90 max-w-2xl">
        Every book comes with a free 20-page preview, a fair refund window,
        and a lifetime download for the digital edition. If you're ever
        unhappy, tell us — we'll make it right.
      </p>
    </div>
  </section>
</template>
