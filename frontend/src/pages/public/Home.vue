<script setup>
import { ref, onMounted } from 'vue'
import { BooksAPI, CategoriesAPI } from '../../api'
import BookCard from '../../components/BookCard.vue'
import BookCardSkeleton from '../../components/BookCardSkeleton.vue'
import {
  BookOpenIcon,
  TruckIcon,
  ArrowDownTrayIcon,
  CheckBadgeIcon,
} from '@heroicons/vue/24/outline'

const featured = ref([])
const newReleases = ref([])
const bestsellers = ref([])
const categories = ref([])
const loading = ref(true)

const perks = [
  { icon: ArrowDownTrayIcon, title: 'Read online or download', text: 'Every digital book comes as a DRM-free PDF you can keep forever.' },
  { icon: TruckIcon, title: 'Physical delivery', text: 'Order the paperback — we ship nationwide with tracking, 3–5 days.' },
  { icon: BookOpenIcon, title: 'Curated library', text: 'Handpicked titles across fiction, business, tech, romance, and more.' },
  { icon: CheckBadgeIcon, title: 'Author-first', text: 'We pay authors fairly and highlight new voices every month.' },
]

onMounted(async () => {
  try {
    const [cat, feat, fresh, best] = await Promise.all([
      CategoriesAPI.list(),
      BooksAPI.list({ featured: 1, per_page: 6 }),
      BooksAPI.list({ sort: 'newest', per_page: 6 }),
      BooksAPI.list({ filter: 'bestseller', per_page: 6 }),
    ])
    categories.value = cat
    featured.value = feat.data
    newReleases.value = fresh.data
    bestsellers.value = best.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <!-- Hero -->
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-hero-gradient opacity-95"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(255,255,255,0.15),transparent_40%),radial-gradient(circle_at_80%_90%,rgba(255,255,255,0.1),transparent_40%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-10 md:py-14 grid gap-8 lg:grid-cols-2 items-center">
      <div class="text-white animate-fade-up">
        <span class="badge bg-white/15 text-white backdrop-blur border border-white/20 mb-3">
          ✨ New this week — 12 fresh releases
        </span>
        <h1 class="font-display text-3xl md:text-5xl font-extrabold leading-[1.1] text-white">
          The books you love,
          <span class="text-accent-300">delivered your way.</span>
        </h1>
        <p class="mt-3 text-base text-white/85 max-w-lg">
          Read online, download the PDF, or have the paperback shipped to your door.
        </p>
        <div class="mt-5 flex flex-wrap gap-3">
          <router-link to="/books" class="btn-accent px-5 py-2.5">Browse the catalog</router-link>
          <router-link to="/register" class="btn bg-white/10 text-white border border-white/30 hover:bg-white/20 px-5 py-2.5">Create free account</router-link>
        </div>
        <div class="mt-5 flex items-center gap-5 text-white/80 text-xs">
          <div><span class="text-xl font-bold text-white">12k+</span> <span class="opacity-80">readers</span></div>
          <div class="h-6 w-px bg-white/20"></div>
          <div><span class="text-xl font-bold text-white">2,300+</span> <span class="opacity-80">titles</span></div>
          <div class="h-6 w-px bg-white/20"></div>
          <div><span class="text-xl font-bold text-white">4.8★</span> <span class="opacity-80">rating</span></div>
        </div>
      </div>

      <!-- Floating book stack -->
      <div class="relative hidden lg:block">
        <div class="grid grid-cols-4 gap-3 max-w-md ml-auto">
          <template v-if="featured.length">
            <div v-for="(b, i) in featured.slice(0,4)" :key="b.id"
              :class="['book-cover aspect-[2/3] transform animate-float', i % 2 ? '' : 'translate-y-4']"
              :style="{ animationDelay: (i * 0.3) + 's' }"
            >
              <img :src="b.cover" :alt="b.title" class="h-full w-full object-cover" />
            </div>
          </template>
          <template v-else>
            <div v-for="i in 4" :key="i" class="aspect-[2/3] rounded-xl bg-white/10 animate-pulse"></div>
          </template>
        </div>
        <div class="absolute -bottom-4 -right-2 bg-white rounded-xl p-3 shadow-soft w-48 border border-ink-100">
          <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-lg bg-accent-100 text-accent-700 flex items-center justify-center">
              <TruckIcon class="h-4 w-4" />
            </div>
            <div>
              <div class="text-[10px] text-ink-500">Delivering to</div>
              <div class="text-xs font-semibold">Nairobi, KE</div>
            </div>
          </div>
          <div class="mt-2 h-1 rounded-full bg-ink-100 overflow-hidden">
            <div class="h-full w-2/3 bg-brand-600"></div>
          </div>
          <div class="mt-1 text-[10px] text-ink-500">Arriving in 2 days</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured -->
  <section class="max-w-7xl mx-auto px-4 mt-8">
    <div class="flex items-end justify-between mb-6">
      <div>
        <span class="badge bg-accent-100 text-accent-800">Editor's picks</span>
        <h2 class="section-title mt-2">Featured this month</h2>
      </div>
      <router-link to="/books" class="text-brand-700 font-semibold text-sm hover:underline hidden sm:inline">Explore more →</router-link>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
      <template v-if="loading">
        <BookCardSkeleton v-for="i in 6" :key="`fs-${i}`" />
      </template>
      <template v-else>
        <BookCard v-for="b in featured" :key="b.id" :book="b" />
      </template>
    </div>
  </section>

  <!-- Perks -->
  <section class="max-w-7xl mx-auto px-4 mt-10">
    <div class="grid gap-3 md:grid-cols-4">
      <div v-for="p in perks" :key="p.title" class="card p-4 flex items-start gap-3">
        <component :is="p.icon" class="h-6 w-6 text-brand-600 shrink-0" />
        <div>
          <h3 class="font-semibold text-ink-900 text-sm">{{ p.title }}</h3>
          <p class="text-xs text-ink-500 mt-0.5">{{ p.text }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories -->
  <section class="max-w-7xl mx-auto px-4 mt-14">
    <div class="flex items-end justify-between mb-6">
      <h2 class="section-title">Browse by category</h2>
      <router-link to="/books" class="text-brand-700 font-semibold text-sm hover:underline">See all →</router-link>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <router-link
        v-for="c in categories"
        :key="c.slug"
        :to="{ name: 'books', query: { category: c.slug } }"
        class="group relative overflow-hidden rounded-2xl p-5 h-24 bg-card-gradient border border-brand-100 hover:border-brand-300 transition"
      >
        <div class="absolute -bottom-4 -right-4 h-20 w-20 rounded-full bg-brand-600/10 group-hover:bg-brand-600/20 transition"></div>
        <div class="relative">
          <div class="text-xs text-brand-700 font-semibold uppercase tracking-widest">Explore</div>
          <div class="text-lg font-semibold text-ink-900">{{ c.name }}</div>
        </div>
      </router-link>
    </div>
  </section>

  <!-- Physical / digital pitch -->
  <section class="max-w-7xl mx-auto px-4 mt-20">
    <div class="grid gap-8 md:grid-cols-2">
      <div class="card p-8 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-brand-100"></div>
        <span class="badge bg-brand-100 text-brand-800">Digital</span>
        <h3 class="section-title mt-3">Read instantly. Anywhere.</h3>
        <p class="mt-3 text-ink-600 max-w-md">
          Preview 20 pages of any book for free. Buy once and access the PDF
          from your library — on your phone, tablet, or laptop.
        </p>
        <router-link to="/books" class="btn-primary mt-6">Start reading →</router-link>
      </div>
      <div class="card p-8 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-accent-100"></div>
        <span class="badge bg-accent-100 text-accent-800">Physical</span>
        <h3 class="section-title mt-3">Prefer paper? We deliver.</h3>
        <p class="mt-3 text-ink-600 max-w-md">
          Order the paperback and we'll ship it to you with tracking. Bundle
          it with a digital copy for a small extra fee.
        </p>
        <router-link to="/books" class="btn-accent mt-6">Shop physical →</router-link>
      </div>
    </div>
  </section>

  <!-- Bestsellers strip -->
  <section class="max-w-7xl mx-auto px-4 mt-20">
    <div class="flex items-end justify-between mb-6">
      <h2 class="section-title">Bestsellers</h2>
      <router-link to="/books?filter=bestseller" class="text-brand-700 font-semibold text-sm hover:underline">All bestsellers →</router-link>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
      <template v-if="loading">
        <BookCardSkeleton v-for="i in 6" :key="`bs-${i}`" />
      </template>
      <template v-else>
        <BookCard v-for="b in bestsellers.slice(0,6)" :key="b.id" :book="b" />
      </template>
    </div>
  </section>

  <!-- New releases -->
  <section class="max-w-7xl mx-auto px-4 mt-20">
    <div class="flex items-end justify-between mb-6">
      <h2 class="section-title">New this month</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
      <template v-if="loading">
        <BookCardSkeleton v-for="i in 6" :key="`nr-${i}`" />
      </template>
      <template v-else>
        <BookCard v-for="b in newReleases" :key="b.id" :book="b" />
      </template>
    </div>
  </section>

  <!-- CTA -->
  <section class="max-w-7xl mx-auto px-4 mt-20">
    <div class="relative overflow-hidden rounded-3xl bg-hero-gradient p-10 md:p-14 text-white">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_10%_20%,rgba(255,255,255,0.15),transparent_40%)]"></div>
      <div class="relative grid gap-6 md:grid-cols-[1fr_auto] items-center">
        <div>
          <h3 class="font-display text-3xl md:text-4xl font-bold text-white">Start your library today.</h3>
          <p class="mt-2 text-white/85 max-w-2xl">
            Create a free account to save favorites, buy in one click, and
            keep your digital books forever.
          </p>
        </div>
        <div class="flex gap-3">
          <router-link to="/register" class="btn-accent px-6 py-3">Create account</router-link>
          <router-link to="/books" class="btn bg-white/10 text-white border border-white/30 hover:bg-white/20 px-6 py-3">Browse first</router-link>
        </div>
      </div>
    </div>
  </section>
</template>
