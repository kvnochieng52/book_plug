<script setup>
import { ref, watch, onMounted } from 'vue'
import { AdminAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import { EyeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const orders = ref([])
const counts = ref({ all: 0, pending: 0, paid: 0, shipped: 0, delivered: 0, refunded: 0 })
const revenueMonth = ref(0)
const loading = ref(true)

const q = ref('')
const status = ref('all')

let debounce

async function fetchOrders() {
  loading.value = true
  try {
    const params = { per_page: 30 }
    if (status.value !== 'all') params.status = status.value
    if (q.value) params.q = q.value
    const res = await AdminAPI.orders.list(params)
    orders.value = res.data
    counts.value = res.meta?.counts || counts.value
    revenueMonth.value = res.meta?.revenue_month || 0
  } finally {
    loading.value = false
  }
}

onMounted(fetchOrders)

watch([q, status], () => {
  clearTimeout(debounce)
  debounce = setTimeout(fetchOrders, 250)
})

async function updateStatus(ref, next) {
  try {
    await AdminAPI.orders.updateStatus(ref, next)
    await fetchOrders()
  } catch (e) {
    alert(apiErrorMessage(e))
  }
}

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
  <div class="space-y-6">
    <div class="flex items-end justify-between flex-wrap gap-3">
      <div>
        <h1 class="font-display text-3xl font-bold">Orders</h1>
        <p class="text-ink-500 mt-1">{{ counts.all }} orders · KES {{ revenueMonth.toLocaleString() }} revenue this month</p>
      </div>
    </div>

    <div class="grid gap-3 md:grid-cols-6">
      <button
        v-for="s in ['all','pending','paid','shipped','delivered','refunded']"
        :key="s"
        @click="status = s"
        :class="['card px-4 py-3 text-sm capitalize font-medium transition',
          status === s ? 'border-brand-500 ring-2 ring-brand-200 text-brand-800' : 'hover:border-brand-300']"
      >
        {{ s }} <span class="ml-1 text-ink-400">· {{ counts[s] ?? 0 }}</span>
      </button>
    </div>

    <div class="card p-4">
      <div class="relative">
        <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-ink-400" />
        <input v-model="q" class="input pl-10" placeholder="Search by order id, customer, or email…" />
      </div>
    </div>

    <div class="card overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Order</th>
            <th class="px-5 py-3 text-left">Customer</th>
            <th class="px-5 py-3 text-left">Items</th>
            <th class="px-5 py-3 text-left">Total</th>
            <th class="px-5 py-3 text-left">Status</th>
            <th class="px-5 py-3 text-left">Date</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-if="loading"><td colspan="7" class="px-5 py-10 text-center text-ink-400">Loading orders…</td></tr>
          <tr v-else-if="!orders.length"><td colspan="7" class="px-5 py-10 text-center text-ink-400">No orders yet.</td></tr>
          <tr v-else v-for="o in orders" :key="o.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3 font-semibold">{{ o.reference }}</td>
            <td class="px-5 py-3">
              <div class="font-medium">{{ o.customer_name }}</div>
              <div class="text-xs text-ink-500">{{ o.customer_email }}</div>
            </td>
            <td class="px-5 py-3">{{ o.items?.length || 0 }}</td>
            <td class="px-5 py-3 font-semibold">KES {{ Number(o.total).toFixed(2) }}</td>
            <td class="px-5 py-3">
              <select :value="o.status" @change="updateStatus(o.reference, $event.target.value)" :class="['badge cursor-pointer border-0', statusColor[o.status]]">
                <option>pending</option><option>paid</option><option>shipped</option>
                <option>delivered</option><option>refunded</option><option>cancelled</option>
              </select>
            </td>
            <td class="px-5 py-3 text-ink-500">{{ new Date(o.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-3 text-right">
              <button class="p-2 rounded-lg hover:bg-brand-50 text-brand-700"><EyeIcon class="h-4 w-4" /></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
