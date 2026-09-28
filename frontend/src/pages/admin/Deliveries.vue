<script setup>
import { ref, onMounted } from 'vue'
import { AdminAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import { MapPinIcon } from '@heroicons/vue/24/outline'

const deliveries = ref([])
const counts = ref({ ready: 0, in_transit: 0, delivered: 0 })
const loading = ref(true)

async function fetchDeliveries() {
  loading.value = true
  try {
    const res = await AdminAPI.deliveries.list()
    deliveries.value = res.data || []
    counts.value = res.counts || counts.value
  } finally {
    loading.value = false
  }
}

onMounted(fetchDeliveries)

async function updateRow(row, patch) {
  try {
    await AdminAPI.deliveries.upsert(row.order.reference, {
      courier: row.courier || null,
      tracking_number: row.tracking_number || null,
      status: row.status,
      eta: row.eta || null,
      notes: row.notes || null,
      ...patch,
    })
    await fetchDeliveries()
  } catch (e) {
    alert(apiErrorMessage(e))
  }
}

const statusColor = {
  ready: 'bg-amber-100 text-amber-800',
  in_transit: 'bg-brand-100 text-brand-800',
  delivered: 'bg-emerald-100 text-emerald-800',
  failed: 'bg-rose-100 text-rose-800',
  returned: 'bg-ink-100 text-ink-700',
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="font-display text-3xl font-bold">Deliveries</h1>
      <p class="text-ink-500 mt-1">Physical orders on their way to readers.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <div class="card p-5">
        <div class="text-xs uppercase tracking-widest text-ink-500">Ready</div>
        <div class="text-3xl font-bold mt-1">{{ counts.ready }}</div>
      </div>
      <div class="card p-5">
        <div class="text-xs uppercase tracking-widest text-ink-500">In transit</div>
        <div class="text-3xl font-bold mt-1">{{ counts.in_transit }}</div>
      </div>
      <div class="card p-5">
        <div class="text-xs uppercase tracking-widest text-ink-500">Delivered</div>
        <div class="text-3xl font-bold mt-1">{{ counts.delivered }}</div>
      </div>
    </div>

    <div class="card overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Order</th>
            <th class="px-5 py-3 text-left">Customer</th>
            <th class="px-5 py-3 text-left">Address</th>
            <th class="px-5 py-3 text-left">Courier</th>
            <th class="px-5 py-3 text-left">Tracking</th>
            <th class="px-5 py-3 text-left">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-if="loading"><td colspan="6" class="px-5 py-10 text-center text-ink-400">Loading deliveries…</td></tr>
          <tr v-else-if="!deliveries.length">
            <td colspan="6" class="px-5 py-10 text-center text-ink-400">
              No physical deliveries yet. Deliveries appear here as soon as a physical order is paid.
            </td>
          </tr>
          <tr v-else v-for="d in deliveries" :key="d.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3 font-semibold">{{ d.order?.reference }}</td>
            <td class="px-5 py-3">{{ d.order?.customer_name }}</td>
            <td class="px-5 py-3">
              <div class="flex items-center gap-1 text-ink-600">
                <MapPinIcon class="h-4 w-4 text-brand-600" />
                {{ d.order?.shipping_address?.address || d.order?.shippingAddress?.address || '—' }},
                {{ d.order?.shipping_address?.city || d.order?.shippingAddress?.city || '' }}
              </div>
            </td>
            <td class="px-5 py-3">
              <input :value="d.courier" @change="updateRow(d, { courier: $event.target.value })" class="input py-1 text-xs" placeholder="Sendy…" />
            </td>
            <td class="px-5 py-3">
              <input :value="d.tracking_number" @change="updateRow(d, { tracking_number: $event.target.value })" class="input py-1 text-xs" placeholder="SN-…" />
            </td>
            <td class="px-5 py-3">
              <select :value="d.status" @change="updateRow(d, { status: $event.target.value })" :class="['badge cursor-pointer border-0', statusColor[d.status]]">
                <option value="ready">ready</option>
                <option value="in_transit">in transit</option>
                <option value="delivered">delivered</option>
                <option value="failed">failed</option>
                <option value="returned">returned</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
