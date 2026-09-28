<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { BooksAPI } from '../../api'
import BookCard from '../../components/BookCard.vue'
import { useCartStore } from '../../stores/cart'
import {
  StarIcon,
  BookOpenIcon,
  TruckIcon,
  ArrowDownTrayIcon,
  ShieldCheckIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/solid'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()

const book = ref(null)
const related = ref([])
const loading = ref(true)
const notFound = ref(false)

const format = ref('digital')
const bundle = ref(false)
const tab = ref('description')
const added = ref(false)

const price = computed(() => {
  if (!book.value) return 0
  if (format.value === 'physical' && bundle.value) {
    return book.value.physicalPrice + book.value.digitalPrice * 0.5
  }
  return format.value === 'physical' ? book.value.physicalPrice : book.value.digitalPrice
})

async function load(slug) {
  loading.value = true
  notFound.value = false
  try {
    const res = await BooksAPI.show(slug)
    book.value = res.book
    related.value = res.related
    format.value = res.book.hasDigital ? 'digital' : 'physical'
  } catch (err) {
    if (err?.response?.status === 404) notFound.value = true
    else throw err
  } finally {
    loading.value = false
  }
}

onMounted(() => load(route.params.slug))
watch(() => route.params.slug, (s) => s && load(s))

function addToCart() {
  cart.add(book.value, format.value)
  if (format.value === 'physical' && bundle.value) cart.add(book.value, 'digital')
  added.value = true
  setTimeout(() => (added.value = false), 1500)
}
function buyNow() {
  addToCart()
  router.push('/checkout')
}
</script>

<template>
  <div v-if="loading" class="max-w-7xl mx-auto px-4 py-16">
    <div class="grid gap-10 md:grid-cols-[320px_1fr] animate-pulse">
      <div class="aspect-[2/3] bg-ink-100 rounded-xl"></div>
      <div class="space-y-4">
        <div class="h-4 bg-ink-100 w-24 rounded"></div>
        <div class="h-10 bg-ink-100 w-3/4 rounded"></div>
        <div class="h-6 bg-ink-100 w-1/3 rounded"></div>
        <div class="h-24 bg-ink-100 rounded"></div>
        <div class="h-20 bg-ink-100 rounded"></div>
      </div>
    </div>
  </div>

  <div v-else-if="notFound || !book" class="max-w-3xl mx-auto px-4 py-24 text-center">
    <h1 class="section-title">Book not found</h1>
    <p class="mt-2 text-ink-500">The title you're looking for doesn't exist.</p>
    <router-link to="/books" class="btn-primary mt-6">Back to catalog</router-link>
  </div>

  <template v-else>
    <!-- Breadcrumb -->
    <div class="max-w-7xl mx-auto px-4 pt-6 text-sm text-ink-500">
      <router-link to="/" class="hover:text-brand-700">Home</router-link>
      <span class="mx-1">/</span>
      <router-link to="/books" class="hover:text-brand-700">Books</router-link>
      <span class="mx-1">/</span>
      <span class="text-ink-700">{{ book.title }}</span>
    </div>

    <section class="max-w-7xl mx-auto px-4 pt-6">
      <div class="grid gap-10 md:grid-cols-[320px_1fr]">
        <!-- Cover -->
        <div>
          <div class="book-cover aspect-[2/3] shadow-book">
            <img :src="book.cover" :alt="book.title" class="h-full w-full object-cover" />
          </div>
          <div class="mt-4 grid grid-cols-2 gap-2">
            <router-link :to="{ name: 'reader', params: { slug: book.slug } }" class="btn-outline">
              <BookOpenIcon class="h-4 w-4" /> Preview
            </router-link>
            <button class="btn-outline">
              <ArrowDownTrayIcon class="h-4 w-4" /> Sample PDF
            </button>
          </div>
        </div>

        <!-- Info -->
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <span v-if="book.tags?.includes('bestseller')" class="badge bg-accent-500 text-white">Bestseller</span>
            <span v-if="book.tags?.includes('new')" class="badge bg-brand-600 text-white">New</span>
            <span v-if="book.tags?.includes('staff pick')" class="badge bg-brand-100 text-brand-800">Staff pick</span>
          </div>
          <h1 class="font-display text-3xl md:text-5xl font-extrabold text-ink-900 mt-2">{{ book.title }}</h1>
          <p class="text-lg text-ink-600 mt-1">by <span class="text-brand-700 font-semibold">{{ book.author }}</span></p>

          <div class="mt-3 flex items-center gap-3 text-sm">
            <div class="flex items-center gap-1">
              <StarIcon v-for="i in 5" :key="i" :class="i <= Math.round(book.rating) ? 'text-accent-500' : 'text-ink-200'" class="h-4 w-4" />
            </div>
            <span class="font-semibold text-ink-800">{{ book.rating.toFixed(1) }}</span>
            <span class="text-ink-500">({{ book.reviews }} reviews)</span>
          </div>

          <p class="mt-5 text-ink-700 leading-relaxed">{{ book.description }}</p>

          <!-- Format picker -->
          <div class="mt-8 grid gap-3 sm:grid-cols-2">
            <label :class="['card p-4 cursor-pointer transition', format === 'digital' ? 'border-brand-500 ring-2 ring-brand-200' : 'hover:border-brand-300']">
              <input v-model="format" type="radio" value="digital" class="sr-only" />
              <div class="flex items-start gap-3">
                <div class="h-10 w-10 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center">
                  <ArrowDownTrayIcon class="h-5 w-5" />
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div class="font-semibold">Digital (PDF)</div>
                    <div class="font-bold text-brand-700">${{ book.digitalPrice.toFixed(2) }}</div>
                  </div>
                  <p class="text-xs text-ink-500 mt-1">Read online or download. DRM-free.</p>
                </div>
              </div>
            </label>
            <label
              :class="['card p-4 transition',
                book.hasPhysical
                  ? (format === 'physical' ? 'border-accent-500 ring-2 ring-accent-200 cursor-pointer' : 'hover:border-accent-300 cursor-pointer')
                  : 'opacity-50 cursor-not-allowed']"
            >
              <input :disabled="!book.hasPhysical" v-model="format" type="radio" value="physical" class="sr-only" />
              <div class="flex items-start gap-3">
                <div class="h-10 w-10 rounded-lg bg-accent-100 text-accent-700 flex items-center justify-center">
                  <TruckIcon class="h-5 w-5" />
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div class="font-semibold">Physical (paperback)</div>
                    <div class="font-bold text-accent-700">${{ book.physicalPrice.toFixed(2) }}</div>
                  </div>
                  <p class="text-xs text-ink-500 mt-1">Ships in 3–5 days · Tracked delivery</p>
                </div>
              </div>
            </label>
          </div>

          <!-- Bundle upsell -->
          <label v-if="format === 'physical' && book.hasPhysical && book.hasDigital" class="mt-3 flex items-start gap-3 card p-3 bg-brand-50 border-brand-200 cursor-pointer">
            <input v-model="bundle" type="checkbox" class="mt-1 rounded text-brand-600 focus:ring-brand-400" />
            <div>
              <div class="text-sm font-semibold text-brand-900">Add digital copy for 50% off</div>
              <div class="text-xs text-brand-800/80">Read now while your paperback ships. +${{ (book.digitalPrice * 0.5).toFixed(2) }}</div>
            </div>
          </label>

          <div class="mt-6 flex flex-wrap items-center gap-3">
            <div class="text-3xl font-extrabold text-ink-900">${{ price.toFixed(2) }}</div>
            <button class="btn-primary px-6 py-3" @click="addToCart">
              <CheckCircleIcon v-if="added" class="h-5 w-5" /> {{ added ? 'Added to cart' : 'Add to cart' }}
            </button>
            <button class="btn-accent px-6 py-3" @click="buyNow">Buy now</button>
          </div>

          <div class="mt-6 grid gap-3 sm:grid-cols-3 text-sm">
            <div class="flex items-center gap-2 text-ink-600"><ShieldCheckIcon class="h-5 w-5 text-brand-600" /> Secure checkout</div>
            <div class="flex items-center gap-2 text-ink-600"><ArrowDownTrayIcon class="h-5 w-5 text-brand-600" /> Instant PDF access</div>
            <div class="flex items-center gap-2 text-ink-600"><TruckIcon class="h-5 w-5 text-brand-600" /> Nationwide delivery</div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mt-14 border-b border-ink-100 flex gap-4">
        <button
          v-for="t in ['description','details','reviews']"
          :key="t"
          @click="tab = t"
          :class="['px-4 py-3 text-sm font-medium capitalize border-b-2 transition',
            tab === t ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:text-ink-800']"
        >{{ t }}</button>
      </div>

      <div class="py-8 max-w-3xl">
        <div v-if="tab === 'description'" class="prose prose-ink max-w-none">
          <p>{{ book.description }}</p>
          <p class="text-ink-600 mt-3">
            This edition includes an exclusive foreword and updated reading
            group questions. Recommended for book clubs and long weekends
            alike.
          </p>
        </div>
        <dl v-else-if="tab === 'details'" class="grid grid-cols-2 gap-y-3 text-sm">
          <dt class="text-ink-500">Author</dt><dd>{{ book.author }}</dd>
          <dt class="text-ink-500">Category</dt><dd class="capitalize">{{ book.category }}</dd>
          <dt class="text-ink-500">Pages</dt><dd>{{ book.pages }}</dd>
          <dt class="text-ink-500">Language</dt><dd>{{ book.language }}</dd>
          <dt class="text-ink-500">Published</dt><dd>{{ new Date(book.published).toDateString() }}</dd>
          <dt class="text-ink-500">Format</dt>
          <dd>{{ book.hasDigital ? 'Digital' : '' }}{{ book.hasDigital && book.hasPhysical ? ' · ' : '' }}{{ book.hasPhysical ? 'Physical' : '' }}</dd>
        </dl>
        <div v-else class="space-y-6">
          <div v-for="i in 3" :key="i" class="card p-5">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center">
                {{ ['AR','KJ','ML'][i-1] }}
              </div>
              <div>
                <div class="font-semibold">{{ ['Ava R.','Kim J.','Malik L.'][i-1] }}</div>
                <div class="flex text-accent-500">
                  <StarIcon v-for="s in 5" :key="s" class="h-4 w-4" :class="s <= (i === 2 ? 4 : 5) ? '' : 'text-ink-200'" />
                </div>
              </div>
            </div>
            <p class="mt-3 text-ink-700 text-sm">
              {{ ['Couldn\'t put it down. Absolutely gorgeous prose.',
                  'Solid but a bit slow in the middle chapters.',
                  'One of my favourite reads this year — highly recommended.'][i-1] }}
            </p>
          </div>
        </div>
      </div>

      <!-- Related -->
      <div v-if="related.length" class="mt-10">
        <h2 class="section-title mb-6">You may also like</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <BookCard v-for="b in related" :key="b.id" :book="b" />
        </div>
      </div>
    </section>
  </template>
</template>
