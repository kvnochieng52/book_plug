<script setup>
import { computed, ref, onMounted } from 'vue'
import { LibraryAPI, OrdersAPI } from '../../api'
import { useAuthStore } from '../../stores/auth'
import EmptyState from '../../components/EmptyState.vue'
import {
  BookOpenIcon,
  ArrowDownTrayIcon,
  TruckIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()

const library = ref([])
const orders = ref([])
const loading = ref(true)

const tab = ref('reading')

const owned = computed(() => library.value.map((i) => i.book))
const inProgress = computed(() =>
  library.value.filter((i) => i.progress > 0 && i.progress < 100),
)

onMounted(async () => {
  loading.value = true
  try {
    const [lib, ord] = await Promise.all([LibraryAPI.list(), OrdersAPI.list()])
    library.value = lib
    orders.value = ord.data || []
  } finally {
    loading.value = false
  }
})

const statusColor = {
  paid: 'bg-emerald-100 text-emerald-800',
  pending: 'bg-amber-100 text-amber-800',
  shipped: 'bg-brand-100 text-brand-800',
  delivered: 'bg-ink-100 text-ink-700',
  refunded: 'bg-rose-100 text-rose-800',
  cancelled: 'bg-ink-100 text-ink-600',
}
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 py-10">
    <!-- Greeting -->
    <div class="grid gap-6 md:grid-cols-3">
      <div class="md:col-span-2 relative overflow-hidden rounded-3xl bg-hero-gradient p-8 text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.15),transparent_40%)]"></div>
        <div class="relative">
          <span class="badge bg-white/15 text-white border border-white/20">Welcome back</span>
          <h1 class="font-display text-3xl md:text-4xl font-bold mt-3 text-white">
            Hi, {{ auth.user?.name || 'reader' }} 👋
          </h1>
          <p v-if="inProgress.length" class="text-white/85 mt-2 max-w-lg">
            You're {{ inProgress[0].progress }}% through <em>{{ inProgress[0].book.title }}</em>. Ready to keep reading?
          </p>
          <p v-else-if="owned.length" class="text-white/85 mt-2 max-w-lg">
            {{ owned.length }} book{{ owned.length === 1 ? '' : 's' }} in your library. Pick one and dive in.
          </p>
          <p v-else class="text-white/85 mt-2 max-w-lg">
            Your library is empty. Buy your first book to get started.
          </p>
          <router-link
            v-if="inProgress.length"
            :to="{ name: 'reader', params: { slug: inProgress[0].book.slug } }"
            class="btn-accent mt-5"
          >
            <BookOpenIcon class="h-5 w-5" /> Resume reading
          </router-link>
          <router-link v-else to="/books" class="btn-accent mt-5">
            <BookOpenIcon class="h-5 w-5" /> Browse books
          </router-link>
        </div>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-1 gap-4">
        <div class="card p-5">
          <div class="text-xs uppercase text-ink-500 tracking-widest">Books owned</div>
          <div class="text-3xl font-bold mt-1">{{ owned.length }}</div>
        </div>
        <div class="card p-5">
          <div class="text-xs uppercase text-ink-500 tracking-widest">Orders placed</div>
          <div class="text-3xl font-bold mt-1">{{ orders.length }}</div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mt-10 flex gap-1 border-b border-ink-100">
      <button
        v-for="t in ['reading','library','orders']"
        :key="t"
        @click="tab = t"
        :class="['px-4 py-3 text-sm font-medium capitalize border-b-2 transition',
          tab === t ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:text-ink-800']"
      >{{ t === 'reading' ? 'Currently reading' : t === 'library' ? 'My library' : 'Orders' }}</button>
    </div>

    <div v-if="loading" class="mt-6 grid gap-5 md:grid-cols-3">
      <div v-for="i in 3" :key="i" class="card p-4 animate-pulse h-32"></div>
    </div>

    <template v-else>
      <!-- Currently reading -->
      <div v-if="tab === 'reading'" class="mt-6">
        <div v-if="inProgress.length" class="grid gap-5 md:grid-cols-3">
          <div v-for="i in inProgress" :key="i.book.id" class="card p-4 flex gap-4">
            <div class="w-20 h-28 rounded-lg overflow-hidden bg-ink-100 shrink-0">
              <img :src="i.book.cover" class="h-full w-full object-cover" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-ink-900 line-clamp-1">{{ i.book.title }}</div>
              <div class="text-sm text-ink-500">{{ i.book.author }}</div>
              <div class="mt-3 h-1.5 rounded-full bg-ink-100 overflow-hidden">
                <div class="h-full bg-brand-600" :style="{ width: i.progress + '%' }"></div>
              </div>
              <div class="mt-1 text-xs text-ink-500">{{ i.progress }}% complete</div>
              <router-link :to="{ name: 'reader', params: { slug: i.book.slug } }" class="btn-primary mt-3 text-xs px-3 py-1.5">
                Continue
              </router-link>
            </div>
          </div>
        </div>
        <EmptyState v-else icon="📖" title="Nothing in progress" message="Open a book from your library to start reading.">
          <router-link to="/books" class="btn-primary">Browse books</router-link>
        </EmptyState>
      </div>

      <!-- Library -->
      <div v-else-if="tab === 'library'" class="mt-6">
        <div v-if="owned.length" class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div v-for="b in owned" :key="b.id" class="animate-fade-up">
            <div class="book-cover aspect-[2/3]">
              <img :src="b.cover" class="h-full w-full object-cover" />
            </div>
            <div class="mt-3">
              <div class="font-semibold text-ink-900 line-clamp-1">{{ b.title }}</div>
              <div class="text-sm text-ink-500">{{ b.author }}</div>
              <div class="mt-2 flex gap-2">
                <router-link :to="{ name: 'reader', params: { slug: b.slug } }" class="btn-outline text-xs px-3 py-1.5">
                  <BookOpenIcon class="h-4 w-4" /> Read
                </router-link>
              </div>
            </div>
          </div>
        </div>
        <EmptyState v-else icon="📚" title="Your library is empty" message="Books you buy will appear here forever.">
          <router-link to="/books" class="btn-primary">Find your first read</router-link>
        </EmptyState>
      </div>

      <!-- Orders -->
      <div v-else class="mt-6">
        <div v-if="orders.length" class="card overflow-hidden">
          <table class="min-w-full text-sm">
            <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
              <tr>
                <th class="px-4 py-3 text-left">Order</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Items</th>
                <th class="px-4 py-3 text-left">Total</th>
                <th class="px-4 py-3 text-left">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
              <tr v-for="o in orders" :key="o.id">
                <td class="px-4 py-3 font-semibold">{{ o.reference }}</td>
                <td class="px-4 py-3">{{ new Date(o.created_at).toLocaleDateString() }}</td>
                <td class="px-4 py-3">{{ o.items?.length || 0 }}</td>
                <td class="px-4 py-3">KES {{ Number(o.total).toFixed(2) }}</td>
                <td class="px-4 py-3">
                  <span :class="['badge', statusColor[o.status] || 'bg-ink-100 text-ink-700']">
                    <component :is="o.status === 'delivered' || o.status === 'shipped' ? TruckIcon : o.status === 'paid' ? ArrowDownTrayIcon : ClockIcon" class="h-3.5 w-3.5" />
                    {{ o.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <EmptyState v-else icon="🧾" title="No orders yet" message="Anything you order will show up here.">
          <router-link to="/books" class="btn-primary">Browse the catalog</router-link>
        </EmptyState>
      </div>
    </template>
  </section>
</template>
