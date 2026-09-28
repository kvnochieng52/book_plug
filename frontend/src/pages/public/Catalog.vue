<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { BooksAPI, CategoriesAPI } from '../../api'
import BookCard from '../../components/BookCard.vue'
import BookCardSkeleton from '../../components/BookCardSkeleton.vue'
import { FunnelIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()

const q = ref(route.query.q?.toString() || '')
const selectedCategory = ref(route.query.category?.toString() || '')
const format = ref(route.query.format?.toString() || '')
const sort = ref(route.query.sort?.toString() || 'popular')
const minRating = ref(Number(route.query.rating || 0))
const filterTag = ref(route.query.filter?.toString() || '')

const books = ref([])
const categories = ref([])
const loading = ref(true)
const total = ref(0)
let debounceTimer = null

async function fetchBooks() {
  loading.value = true
  try {
    const params = {}
    if (q.value) params.q = q.value
    if (selectedCategory.value) params.category = selectedCategory.value
    if (format.value) params.format = format.value
    if (sort.value !== 'popular') params.sort = sort.value
    if (minRating.value) params.rating = minRating.value
    if (filterTag.value) params.filter = filterTag.value
    params.per_page = 24
    const res = await BooksAPI.list(params)
    books.value = res.data
    total.value = res.meta?.total || res.data.length
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  categories.value = await CategoriesAPI.list()
  await fetchBooks()
})

watch([q, selectedCategory, format, sort, minRating, filterTag], () => {
  router.replace({
    query: {
      ...(q.value ? { q: q.value } : {}),
      ...(selectedCategory.value ? { category: selectedCategory.value } : {}),
      ...(format.value ? { format: format.value } : {}),
      ...(sort.value !== 'popular' ? { sort: sort.value } : {}),
      ...(minRating.value ? { rating: minRating.value } : {}),
      ...(filterTag.value ? { filter: filterTag.value } : {}),
    },
  })
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchBooks, 250)
})

function reset() {
  q.value = ''
  selectedCategory.value = ''
  format.value = ''
  sort.value = 'popular'
  minRating.value = 0
  filterTag.value = ''
}
</script>

<template>
  <section class="bg-gradient-to-b from-brand-50 to-transparent">
    <div class="max-w-7xl mx-auto px-4 py-10">
      <h1 class="section-title">Explore our catalog</h1>
      <p class="text-ink-600 mt-2">Thousands of digital and physical books, curated for readers who care.</p>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 pb-16">
    <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
      <!-- Filters -->
      <aside class="card p-5 h-max sticky top-20 hidden lg:block">
        <div class="flex items-center gap-2 mb-4">
          <FunnelIcon class="h-5 w-5 text-brand-600" />
          <h3 class="font-semibold">Filters</h3>
          <button class="ml-auto text-xs text-brand-700 hover:underline" @click="reset">Reset</button>
        </div>
        <div class="space-y-5">
          <div>
            <label class="label">Search</label>
            <input v-model="q" type="search" placeholder="Title or author" class="input" />
          </div>
          <div>
            <label class="label">Category</label>
            <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
              <label class="flex items-center gap-2 text-sm">
                <input type="radio" v-model="selectedCategory" value="" class="text-brand-600 focus:ring-brand-400" />
                <span>All categories</span>
              </label>
              <label v-for="c in categories" :key="c.slug" class="flex items-center gap-2 text-sm">
                <input type="radio" v-model="selectedCategory" :value="c.slug" class="text-brand-600 focus:ring-brand-400" />
                <span>{{ c.name }}</span>
              </label>
            </div>
          </div>
          <div>
            <label class="label">Format</label>
            <div class="space-y-1.5 text-sm">
              <label class="flex items-center gap-2"><input type="radio" v-model="format" value="" /> Any</label>
              <label class="flex items-center gap-2"><input type="radio" v-model="format" value="digital" /> Digital (PDF)</label>
              <label class="flex items-center gap-2"><input type="radio" v-model="format" value="physical" /> Physical</label>
            </div>
          </div>
          <div>
            <label class="label">Minimum rating</label>
            <input type="range" min="0" max="5" step="0.5" v-model.number="minRating" class="w-full accent-brand-600" />
            <div class="text-xs text-ink-500 mt-1">{{ minRating || 'Any' }}{{ minRating ? '★ & up' : '' }}</div>
          </div>
        </div>
      </aside>

      <!-- Results -->
      <div>
        <div class="flex flex-wrap items-center gap-3 mb-4">
          <div class="lg:hidden flex-1">
            <input v-model="q" type="search" placeholder="Search books…" class="input" />
          </div>
          <select v-model="selectedCategory" class="input lg:hidden max-w-[180px]">
            <option value="">All categories</option>
            <option v-for="c in categories" :key="c.slug" :value="c.slug">{{ c.name }}</option>
          </select>
          <div class="ml-auto flex items-center gap-2 text-sm">
            <span class="text-ink-500">{{ total }} results</span>
            <select v-model="sort" class="input max-w-[180px] text-sm">
              <option value="popular">Most popular</option>
              <option value="newest">Newest</option>
              <option value="rating">Highest rated</option>
              <option value="price-asc">Price ↑</option>
              <option value="price-desc">Price ↓</option>
            </select>
          </div>
        </div>

        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
          <BookCardSkeleton v-for="i in 8" :key="i" />
        </div>
        <div v-else-if="books.length" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
          <BookCard v-for="b in books" :key="b.id" :book="b" />
        </div>
        <div v-else class="card p-10 text-center text-ink-500">
          No books match those filters. <button class="text-brand-700 font-semibold" @click="reset">Clear filters</button>
        </div>
      </div>
    </div>
  </section>
</template>
