<script setup>
import { ref, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../../stores/cart'
import { useAuthStore } from '../../stores/auth'
import { OrdersAPI, MpesaAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import {
  LockClosedIcon,
  TruckIcon,
  ArrowDownTrayIcon,
  DevicePhoneMobileIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

const cart = useCartStore()
const auth = useAuthStore()
const router = useRouter()

const needsShipping = computed(() => cart.items.some((i) => i.type === 'physical'))

const form = ref({
  email: auth.user?.email || '',
  fullName: auth.user?.name || '',
  phone: auth.user?.phone || '',
  address: '',
  city: '',
  region: '',
  postal: '',
  country: 'Kenya',
})

const step = ref('form')      // form | processing | success | failed
const message = ref('')
const err = ref('')
const submitting = ref(false)
const order = ref(null)
const stkTxId = ref(null)

let pollTimer = null

function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null } }
onUnmounted(stopPolling)

async function placeOrder() {
  err.value = ''
  if (needsShipping.value && (!form.value.address || !form.value.city)) {
    err.value = 'Enter a shipping address for physical items.'
    return
  }
  if (!form.value.phone) {
    err.value = 'Enter the M-Pesa phone number to charge.'
    return
  }

  submitting.value = true
  try {
    const payload = {
      items: cart.items.map((i) => ({ book_id: i.bookId, type: i.type, quantity: i.qty })),
      customer_name: form.value.fullName,
      customer_email: form.value.email,
      customer_phone: form.value.phone,
    }
    if (needsShipping.value) {
      payload.shipping = {
        recipient_name: form.value.fullName,
        phone: form.value.phone,
        address: form.value.address,
        city: form.value.city,
        region: form.value.region,
        postal_code: form.value.postal,
        country: form.value.country,
      }
    }
    order.value = await OrdersAPI.create(payload)

    step.value = 'processing'
    message.value = 'Sending an STK Push to your phone. Approve it to complete the payment.'

    const stk = await MpesaAPI.initiate(order.value.reference, form.value.phone)
    stkTxId.value = stk.transaction_id

    pollTimer = setInterval(pollStatus, 3000)
  } catch (e) {
    err.value = apiErrorMessage(e, 'Could not place your order.')
  } finally {
    submitting.value = false
  }
}

async function pollStatus() {
  if (!stkTxId.value) return
  try {
    const { status, order_status, result_desc } = await MpesaAPI.status(stkTxId.value)
    if (status === 'success' || order_status === 'paid') {
      stopPolling()
      step.value = 'success'
      cart.clear()
      setTimeout(() => router.push({ name: 'checkout-success', query: { ref: order.value.reference } }), 800)
    } else if (status === 'failed' || status === 'cancelled' || status === 'timeout') {
      stopPolling()
      step.value = 'failed'
      message.value = result_desc || 'Payment was not completed. You can try again.'
    }
  } catch {
    // Keep polling — transient errors are fine.
  }
}

function retry() {
  step.value = 'form'
  err.value = ''
  message.value = ''
}
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="section-title">Checkout</h1>

    <!-- Success / Failure overlays -->
    <div v-if="step === 'processing'" class="card p-10 mt-8 text-center max-w-lg mx-auto">
      <div class="mx-auto h-16 w-16 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center">
        <DevicePhoneMobileIcon class="h-8 w-8 animate-pulse" />
      </div>
      <h3 class="font-semibold text-lg mt-4">Check your phone</h3>
      <p class="text-ink-500 mt-2">{{ message }}</p>
      <p class="text-xs text-ink-400 mt-4">Order reference: <code>{{ order?.reference }}</code></p>
    </div>

    <div v-else-if="step === 'success'" class="card p-10 mt-8 text-center max-w-lg mx-auto">
      <CheckCircleIcon class="mx-auto h-14 w-14 text-emerald-500" />
      <h3 class="font-semibold text-lg mt-3">Payment received!</h3>
      <p class="text-ink-500 mt-1">Redirecting to your library…</p>
    </div>

    <div v-else-if="step === 'failed'" class="card p-10 mt-8 text-center max-w-lg mx-auto">
      <ExclamationCircleIcon class="mx-auto h-14 w-14 text-rose-500" />
      <h3 class="font-semibold text-lg mt-3">Payment not completed</h3>
      <p class="text-ink-500 mt-1">{{ message }}</p>
      <button class="btn-primary mt-5" @click="retry">Try again</button>
    </div>

    <div v-else-if="cart.items.length" class="grid gap-8 lg:grid-cols-[1fr_400px] mt-8">
      <form @submit.prevent="placeOrder" class="space-y-6">
        <div v-if="err" class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ err }}</div>

        <div class="card p-6">
          <h3 class="font-semibold text-lg mb-4">Contact</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Email</label>
              <input v-model="form.email" required type="email" class="input" placeholder="you@email.com" />
            </div>
            <div>
              <label class="label">Full name</label>
              <input v-model="form.fullName" required class="input" placeholder="Jane Doe" />
            </div>
          </div>
        </div>

        <div v-if="needsShipping" class="card p-6">
          <div class="flex items-center gap-2 mb-4">
            <TruckIcon class="h-5 w-5 text-accent-600" />
            <h3 class="font-semibold text-lg">Shipping address</h3>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="label">Street address</label>
              <input v-model="form.address" required class="input" placeholder="123 Kimathi Street" />
            </div>
            <div><label class="label">City</label><input v-model="form.city" required class="input" /></div>
            <div><label class="label">Region</label><input v-model="form.region" class="input" /></div>
            <div><label class="label">Postal code</label><input v-model="form.postal" class="input" /></div>
            <div>
              <label class="label">Country</label>
              <select v-model="form.country" class="input">
                <option>Kenya</option><option>Uganda</option><option>Tanzania</option>
                <option>Rwanda</option><option>Ethiopia</option>
              </select>
            </div>
          </div>
        </div>

        <div v-else class="card p-6">
          <div class="flex items-center gap-2">
            <ArrowDownTrayIcon class="h-5 w-5 text-brand-600" />
            <div>
              <h3 class="font-semibold text-lg">Digital delivery</h3>
              <p class="text-sm text-ink-500">Your books will appear in your library right after payment.</p>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center gap-2 mb-4">
            <DevicePhoneMobileIcon class="h-5 w-5 text-brand-600" />
            <h3 class="font-semibold text-lg">Pay with M-Pesa</h3>
          </div>
          <div>
            <label class="label">M-Pesa phone number</label>
            <input v-model="form.phone" required type="tel" class="input" placeholder="+254 700 000 000" />
            <p class="text-xs text-ink-500 mt-2">We'll prompt this phone to authorize the payment.</p>
          </div>
        </div>

        <button :disabled="submitting" class="btn-primary w-full py-3.5 text-base disabled:opacity-60">
          {{ submitting ? 'Placing order…' : `Place order · KES ${cart.total.toFixed(2)}` }}
        </button>
        <p class="text-xs text-ink-500 text-center flex items-center justify-center gap-1">
          <LockClosedIcon class="h-3.5 w-3.5" /> Payments are processed by Safaricom M-Pesa.
        </p>
      </form>

      <aside class="card p-6 h-max sticky top-24 space-y-4">
        <h3 class="font-semibold text-lg">Your order</h3>
        <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
          <div v-for="i in cart.items" :key="i.key" class="flex gap-3">
            <div class="w-12 h-16 rounded-md overflow-hidden bg-ink-100 shrink-0">
              <img :src="i.cover" class="h-full w-full object-cover" />
            </div>
            <div class="flex-1 min-w-0 text-sm">
              <div class="font-semibold text-ink-900 truncate">{{ i.title }}</div>
              <div class="text-ink-500 capitalize">{{ i.type }} · Qty {{ i.qty }}</div>
            </div>
            <div class="text-sm font-semibold">KES {{ (i.price * i.qty).toFixed(2) }}</div>
          </div>
        </div>
        <div class="border-t border-ink-100 pt-4 space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-ink-500">Subtotal</span><span>KES {{ cart.subtotal.toFixed(2) }}</span></div>
          <div class="flex justify-between"><span class="text-ink-500">Shipping</span><span>KES {{ cart.shipping.toFixed(2) }}</span></div>
          <div class="flex justify-between font-bold text-base pt-2 border-t border-ink-100 mt-2">
            <span>Total</span><span>KES {{ cart.total.toFixed(2) }}</span>
          </div>
        </div>
      </aside>
    </div>

    <div v-else class="card p-14 mt-8 text-center">
      <h3 class="font-semibold text-lg">Nothing to check out yet</h3>
      <p class="text-ink-500 mt-1">Add a book to your cart first.</p>
      <router-link to="/books" class="btn-primary mt-6">Browse the catalog</router-link>
    </div>
  </section>
</template>
