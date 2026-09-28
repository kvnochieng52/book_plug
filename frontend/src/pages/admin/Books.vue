<script setup>
import { ref, watch, onMounted } from 'vue'
import { AdminAPI, CategoriesAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import {
  PencilSquareIcon,
  TrashIcon,
  EyeIcon,
  MagnifyingGlassIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

const books = ref([])
const categories = ref([])
const loading = ref(true)
const total = ref(0)

const q = ref('')
const cat = ref('')
const fmt = ref('')

let debounce

async function fetchBooks() {
  loading.value = true
  try {
    const params = { per_page: 30 }
    if (q.value) params.q = q.value
    if (cat.value) params.category = cat.value
    if (fmt.value) params.format = fmt.value
    const res = await AdminAPI.books.list(params)
    books.value = (res.data || []).map((b) => ({
      ...b,
      digitalPrice: Number(b.digital_price),
      physicalPrice: Number(b.physical_price),
      hasPhysical: b.has_physical,
      hasDigital: b.has_digital,
      cover: b.cover,
    }))
    total.value = res.meta?.total || res.data.length
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  categories.value = await CategoriesAPI.list()
  await fetchBooks()
})

watch([q, cat, fmt], () => {
  clearTimeout(debounce)
  debounce = setTimeout(fetchBooks, 250)
})

async function remove(slug) {
  if (!confirm('Delete this book? This will also remove its cover and PDF.')) return
  try {
    await AdminAPI.books.remove(slug)
    await fetchBooks()
  } catch (e) {
    alert(apiErrorMessage(e))
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="font-display text-3xl font-bold">Books</h1>
        <p class="text-ink-500 mt-1">{{ total }} titles in the catalog</p>
      </div>
      <router-link to="/admin/books/new" class="btn-primary">
        <PlusIcon class="h-5 w-5" /> Add a book
      </router-link>
    </div>

    <div class="card p-4 grid gap-3 md:grid-cols-[1fr_200px_200px]">
      <div class="relative">
        <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-ink-400" />
        <input v-model="q" class="input pl-10" placeholder="Search title or author" />
      </div>
      <select v-model="cat" class="input">
        <option value="">All categories</option>
        <option v-for="c in categories" :key="c.slug" :value="c.slug">{{ c.name }}</option>
      </select>
      <select v-model="fmt" class="input">
        <option value="">Any format</option>
        <option value="digital">Digital only</option>
        <option value="physical">Physical only</option>
      </select>
    </div>

    <div class="card overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Book</th>
            <th class="px-5 py-3 text-left">Category</th>
            <th class="px-5 py-3 text-left">Formats</th>
            <th class="px-5 py-3 text-left">Price</th>
            <th class="px-5 py-3 text-left">Rating</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-if="loading">
            <td colspan="6" class="px-5 py-10 text-center text-ink-400">Loading books…</td>
          </tr>
          <tr v-else-if="!books.length">
            <td colspan="6" class="px-5 py-10 text-center text-ink-400">No books match those filters.</td>
          </tr>
          <tr v-else v-for="b in books" :key="b.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-14 rounded-md overflow-hidden bg-ink-100 shrink-0">
                  <img v-if="b.cover" :src="b.cover" class="h-full w-full object-cover" />
                </div>
                <div class="min-w-0">
                  <div class="font-semibold text-ink-900 truncate">{{ b.title }}</div>
                  <div class="text-xs text-ink-500 truncate">{{ b.author }}</div>
                </div>
              </div>
            </td>
            <td class="px-5 py-3 capitalize">{{ b.category?.name || b.category }}</td>
            <td class="px-5 py-3">
              <div class="flex gap-1">
                <span v-if="b.hasDigital" class="badge bg-brand-100 text-brand-800">Digital</span>
                <span v-if="b.hasPhysical" class="badge bg-accent-100 text-accent-800">Physical</span>
              </div>
            </td>
            <td class="px-5 py-3">
              <div class="font-semibold">KES {{ b.digitalPrice.toFixed(2) }}</div>
              <div v-if="b.hasPhysical" class="text-xs text-ink-500">KES {{ b.physicalPrice.toFixed(2) }} paper</div>
            </td>
            <td class="px-5 py-3">
              <span class="font-semibold">{{ Number(b.rating).toFixed(1) }}★</span>
              <span class="text-xs text-ink-500 ml-1">({{ b.reviews }})</span>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <router-link :to="{ name: 'book-detail', params: { slug: b.slug } }" class="p-2 rounded-lg hover:bg-ink-100 text-ink-500" title="View">
                  <EyeIcon class="h-4 w-4" />
                </router-link>
                <router-link :to="{ name: 'admin-book-edit', params: { slug: b.slug } }" class="p-2 rounded-lg hover:bg-brand-50 text-brand-700" title="Edit">
                  <PencilSquareIcon class="h-4 w-4" />
                </router-link>
                <button @click="remove(b.slug)" class="p-2 rounded-lg hover:bg-red-50 text-red-600" title="Delete">
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
