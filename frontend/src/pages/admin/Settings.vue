<script setup>
import { ref } from 'vue'

const tab = ref('store')
const form = ref({
  storeName: 'BookPlug',
  tagline: 'Read, download, own great books.',
  supportEmail: 'hello@bookplug.io',
  currency: 'KES',
  taxRate: 16,
  shippingFlat: 4.99,
  freeShipOver: 40,
  enablePhysical: true,
  enableDigital: true,
  enableMpesa: true,
  enableStripe: true,
  enablePaypal: false,
})
</script>

<template>
  <div class="space-y-6 max-w-5xl">
    <div>
      <h1 class="font-display text-3xl font-bold">Settings</h1>
      <p class="text-ink-500 mt-1">Fine-tune how your store looks and operates.</p>
    </div>

    <div class="flex flex-wrap gap-1 border-b border-ink-100">
      <button
        v-for="t in ['store','payments','shipping','notifications']"
        :key="t"
        @click="tab = t"
        :class="['px-4 py-3 text-sm font-medium capitalize border-b-2 transition',
          tab === t ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:text-ink-800']"
      >{{ t }}</button>
    </div>

    <div v-if="tab === 'store'" class="card p-6 grid gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="label">Store name</label>
        <input v-model="form.storeName" class="input" />
      </div>
      <div class="sm:col-span-2">
        <label class="label">Tagline</label>
        <input v-model="form.tagline" class="input" />
      </div>
      <div>
        <label class="label">Support email</label>
        <input v-model="form.supportEmail" type="email" class="input" />
      </div>
      <div>
        <label class="label">Currency</label>
        <select v-model="form.currency" class="input">
          <option>KES</option><option>USD</option><option>EUR</option><option>GBP</option>
        </select>
      </div>
    </div>

    <div v-else-if="tab === 'payments'" class="card p-6 space-y-3">
      <label class="flex items-center justify-between p-3 rounded-xl border border-ink-100">
        <div>
          <div class="font-semibold">Stripe (credit / debit cards)</div>
          <div class="text-sm text-ink-500">Accept Visa, Mastercard, and Amex.</div>
        </div>
        <input type="checkbox" v-model="form.enableStripe" class="h-5 w-5 rounded text-brand-600" />
      </label>
      <label class="flex items-center justify-between p-3 rounded-xl border border-ink-100">
        <div>
          <div class="font-semibold">M-Pesa</div>
          <div class="text-sm text-ink-500">Accept mobile payments in Kenya.</div>
        </div>
        <input type="checkbox" v-model="form.enableMpesa" class="h-5 w-5 rounded text-brand-600" />
      </label>
      <label class="flex items-center justify-between p-3 rounded-xl border border-ink-100">
        <div>
          <div class="font-semibold">PayPal</div>
          <div class="text-sm text-ink-500">Accept international payments.</div>
        </div>
        <input type="checkbox" v-model="form.enablePaypal" class="h-5 w-5 rounded text-brand-600" />
      </label>
      <div class="grid sm:grid-cols-2 gap-4 pt-3 border-t border-ink-100 mt-3">
        <div>
          <label class="label">Tax rate (%)</label>
          <input type="number" v-model.number="form.taxRate" class="input" />
        </div>
      </div>
    </div>

    <div v-else-if="tab === 'shipping'" class="card p-6 grid gap-4 sm:grid-cols-2">
      <div>
        <label class="label">Flat shipping ($)</label>
        <input type="number" step="0.01" v-model.number="form.shippingFlat" class="input" />
      </div>
      <div>
        <label class="label">Free shipping over ($)</label>
        <input type="number" step="0.01" v-model.number="form.freeShipOver" class="input" />
      </div>
      <label class="sm:col-span-2 flex items-center justify-between p-3 rounded-xl border border-ink-100">
        <div>
          <div class="font-semibold">Enable physical books</div>
          <div class="text-sm text-ink-500">Turn off to only sell digital PDFs.</div>
        </div>
        <input type="checkbox" v-model="form.enablePhysical" class="h-5 w-5 rounded text-brand-600" />
      </label>
    </div>

    <div v-else class="card p-6 space-y-3">
      <div class="text-sm text-ink-500">Choose which events email you.</div>
      <label class="flex items-center gap-3 p-3 rounded-xl border border-ink-100"><input type="checkbox" checked class="rounded text-brand-600" /> New order placed</label>
      <label class="flex items-center gap-3 p-3 rounded-xl border border-ink-100"><input type="checkbox" checked class="rounded text-brand-600" /> Delivery status update</label>
      <label class="flex items-center gap-3 p-3 rounded-xl border border-ink-100"><input type="checkbox" class="rounded text-brand-600" /> Low stock alert</label>
      <label class="flex items-center gap-3 p-3 rounded-xl border border-ink-100"><input type="checkbox" class="rounded text-brand-600" /> Weekly sales digest</label>
    </div>

    <div class="flex justify-end gap-2">
      <button class="btn-outline">Cancel</button>
      <button class="btn-primary">Save changes</button>
    </div>
  </div>
</template>
