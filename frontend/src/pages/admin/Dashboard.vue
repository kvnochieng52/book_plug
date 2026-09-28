<script setup>
import { ref, onMounted } from 'vue'
import { AdminAPI } from '../../api'
import {
  BanknotesIcon,
  BookOpenIcon,
  ShoppingBagIcon,
  UsersIcon,
  ArrowUpRightIcon,
  TruckIcon,
} from '@heroicons/vue/24/outline'

const stats = ref({
  revenueMonth: 0,
  orderCount: 0,
  userCount: 0,
  bookCount: 0,
})
const topBooks = ref([])
const recentOrders = ref([])
const pendingDeliveries = ref([])
const loading = ref(true)

const statusColor = {
  paid: 'bg-emerald-100 text-emerald-700',
  pending: 'bg-amber-100 text-amber-700',
  shipped: 'bg-brand-100 text-brand-700',
  delivered: 'bg-ink-100 text-ink-700',
  refunded: 'bg-rose-100 text-rose-700',
  cancelled: 'bg-ink-100 text-ink-600',
}

onMounted(async () => {
  try {
    const [orders, users, books, deliveries] = await Promise.all([
      AdminAPI.orders.list({ per_page: 8 }),
      AdminAPI.users.list({ per_page: 1 }),
      AdminAPI.books.list({ per_page: 5, sort: 'rating' }),
      AdminAPI.deliveries.list(),
    ])
    stats.value = {
      revenueMonth: orders.meta?.revenue_month || 0,
      orderCount: orders.meta?.counts?.all || orders.meta?.total || 0,
      userCount: users.data?.total || users.data?.length || 0,
      bookCount: books.meta?.total || 0,
    }
    recentOrders.value = orders.data
    topBooks.value = books.data
    pendingDeliveries.value = (deliveries.data || []).filter((d) => d.status !== 'delivered').slice(0, 4)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-end justify-between">
      <div>
        <h1 class="font-display text-3xl font-bold">Dashboard</h1>
        <p class="text-ink-500 mt-1">Live data from your store.</p>
      </div>
      <div class="hidden md:flex gap-2">
        <router-link to="/admin/books/new" class="btn-primary">+ Add a book</router-link>
      </div>
    </div>

    <!-- KPIs -->
    <div class="grid gap-4 md:grid-cols-4">
      <div class="card p-5">
        <div class="flex items-center justify-between">
          <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-brand-100 text-brand-700">
            <BanknotesIcon class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-4 text-2xl font-bold text-ink-900">
          <span v-if="loading" class="inline-block w-24 h-6 bg-ink-100 animate-pulse rounded"></span>
          <span v-else>KES {{ stats.revenueMonth.toLocaleString() }}</span>
        </div>
        <div class="text-sm text-ink-500">Revenue this month</div>
      </div>
      <div class="card p-5">
        <div class="flex items-center justify-between">
          <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-accent-100 text-accent-700">
            <ShoppingBagIcon class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-4 text-2xl font-bold text-ink-900">{{ loading ? '—' : stats.orderCount }}</div>
        <div class="text-sm text-ink-500">Total orders</div>
      </div>
      <div class="card p-5">
        <div class="flex items-center justify-between">
          <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-emerald-100 text-emerald-700">
            <UsersIcon class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-4 text-2xl font-bold text-ink-900">{{ loading ? '—' : stats.userCount }}</div>
        <div class="text-sm text-ink-500">Registered readers</div>
      </div>
      <div class="card p-5">
        <div class="flex items-center justify-between">
          <div class="h-10 w-10 rounded-xl flex items-center justify-center bg-rose-100 text-rose-700">
            <BookOpenIcon class="h-5 w-5" />
          </div>
        </div>
        <div class="mt-4 text-2xl font-bold text-ink-900">{{ loading ? '—' : stats.bookCount }}</div>
        <div class="text-sm text-ink-500">Books in catalog</div>
      </div>
    </div>

    <!-- Recent orders + Top books -->
    <div class="grid gap-6 lg:grid-cols-3">
      <div class="card overflow-hidden lg:col-span-2">
        <div class="p-5 flex items-center justify-between border-b border-ink-100">
          <h3 class="font-semibold">Recent orders</h3>
          <router-link to="/admin/orders" class="text-sm text-brand-700 hover:underline flex items-center gap-1">
            View all <ArrowUpRightIcon class="h-4 w-4" />
          </router-link>
        </div>
        <table class="min-w-full text-sm">
          <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
            <tr>
              <th class="px-5 py-3 text-left">Order</th>
              <th class="px-5 py-3 text-left">Customer</th>
              <th class="px-5 py-3 text-left">Total</th>
              <th class="px-5 py-3 text-left">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-ink-100">
            <tr v-if="loading">
              <td colspan="4" class="px-5 py-8 text-center text-ink-400">Loading…</td>
            </tr>
            <tr v-else-if="!recentOrders.length">
              <td colspan="4" class="px-5 py-8 text-center text-ink-400">No orders yet.</td>
            </tr>
            <tr v-else v-for="o in recentOrders" :key="o.id" class="hover:bg-ink-50/50">
              <td class="px-5 py-3 font-semibold">{{ o.reference }}</td>
              <td class="px-5 py-3">{{ o.customer_name }}</td>
              <td class="px-5 py-3">KES {{ Number(o.total).toFixed(2) }}</td>
              <td class="px-5 py-3">
                <span :class="['badge', statusColor[o.status] || 'bg-ink-100 text-ink-700']">{{ o.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card p-6">
        <h3 class="font-semibold">Top-rated books</h3>
        <div class="mt-4 space-y-3">
          <div v-if="loading" class="text-sm text-ink-400">Loading…</div>
          <div v-for="(b, i) in topBooks" :key="b.id" class="flex items-center gap-3">
            <div class="w-6 text-center font-bold text-ink-400">{{ i + 1 }}</div>
            <div class="w-10 h-14 rounded-md overflow-hidden bg-ink-100 shrink-0">
              <img v-if="b.cover" :src="b.cover" class="h-full w-full object-cover" />
            </div>
            <div class="min-w-0 flex-1">
              <div class="text-sm font-semibold text-ink-900 truncate">{{ b.title }}</div>
              <div class="text-xs text-ink-500">{{ b.author }} · {{ b.rating }}★</div>
            </div>
            <div class="text-sm font-semibold">KES {{ b.digitalPrice.toFixed(2) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending deliveries -->
    <div class="card p-6">
      <div class="flex items-center gap-2 mb-2">
        <TruckIcon class="h-5 w-5 text-accent-600" />
        <h3 class="font-semibold">Pending deliveries</h3>
      </div>
      <div v-if="loading" class="text-sm text-ink-400 mt-3">Loading…</div>
      <div v-else-if="!pendingDeliveries.length" class="text-sm text-ink-400 mt-3">No physical orders in transit right now.</div>
      <div v-else class="mt-3 grid gap-3 md:grid-cols-2">
        <div v-for="d in pendingDeliveries" :key="d.id" class="flex items-center gap-3 p-3 rounded-xl border border-ink-100">
          <div class="h-9 w-9 rounded-lg bg-accent-100 text-accent-700 flex items-center justify-center text-xs font-bold">
            {{ d.order?.reference?.replace('BP-', '') || '—' }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold truncate">{{ d.order?.customer_name || 'Customer' }}</div>
            <div class="text-xs text-ink-500 truncate">{{ d.order?.shippingAddress?.city || d.order?.shippingAddress?.address || '' }}</div>
          </div>
          <span class="badge bg-amber-100 text-amber-800 capitalize">{{ d.status.replace('_', ' ') }}</span>
        </div>
      </div>
      <router-link to="/admin/deliveries" class="btn-outline w-full mt-5">Manage deliveries</router-link>
    </div>
  </div>
</template>
