<script setup>
import { useCartStore } from '../../stores/cart'
import { TrashIcon, TruckIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
const cart = useCartStore()
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="section-title">Your cart</h1>
    <p class="text-ink-500 mt-1" v-if="cart.items.length">{{ cart.count }} item(s)</p>

    <div v-if="cart.items.length" class="grid gap-8 lg:grid-cols-[1fr_360px] mt-8">
      <div class="space-y-4">
        <div v-for="item in cart.items" :key="item.key" class="card p-4 flex gap-4">
          <div class="w-20 h-28 rounded-lg overflow-hidden bg-ink-100 shrink-0">
            <img :src="item.cover" :alt="item.title" class="h-full w-full object-cover" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <router-link :to="{ name: 'book-detail', params: { slug: item.slug } }" class="font-semibold text-ink-900 hover:text-brand-700 line-clamp-1">
                  {{ item.title }}
                </router-link>
                <p class="text-sm text-ink-500">{{ item.author }}</p>
                <span
                  :class="['badge mt-2', item.type === 'physical' ? 'bg-accent-100 text-accent-800' : 'bg-brand-100 text-brand-800']"
                >
                  <component :is="item.type === 'physical' ? TruckIcon : ArrowDownTrayIcon" class="h-3.5 w-3.5" />
                  {{ item.type === 'physical' ? 'Physical' : 'Digital' }}
                </span>
              </div>
              <button class="p-2 rounded-lg hover:bg-red-50 text-ink-400 hover:text-red-600" @click="cart.remove(item.key)">
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
            <div class="mt-3 flex items-center gap-3">
              <div class="inline-flex items-center rounded-lg border border-ink-200">
                <button class="px-3 py-1 hover:bg-ink-50" @click="cart.setQty(item.key, item.qty - 1)">−</button>
                <span class="w-8 text-center text-sm">{{ item.qty }}</span>
                <button class="px-3 py-1 hover:bg-ink-50" @click="cart.setQty(item.key, item.qty + 1)">+</button>
              </div>
              <div class="ml-auto font-bold">KES {{ (item.price * item.qty).toFixed(2) }}</div>
            </div>
          </div>
        </div>
      </div>

      <aside class="card p-6 h-max sticky top-24">
        <h3 class="font-semibold text-lg">Order summary</h3>
        <div class="mt-4 space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-ink-500">Subtotal</span><span>KES {{ cart.subtotal.toFixed(2) }}</span></div>
          <div class="flex justify-between"><span class="text-ink-500">Shipping</span><span>KES {{ cart.shipping.toFixed(2) }}</span></div>
          <div class="flex justify-between text-base pt-3 border-t border-ink-100 mt-3 font-bold">
            <span>Total</span><span>KES {{ cart.total.toFixed(2) }}</span>
          </div>
        </div>
        <router-link to="/checkout" class="btn-primary w-full mt-6 py-3">Checkout</router-link>
        <router-link to="/books" class="btn-outline w-full mt-2">Continue shopping</router-link>
      </aside>
    </div>

    <div v-else class="card p-14 mt-8 text-center">
      <div class="mx-auto h-16 w-16 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-3xl">🛒</div>
      <h3 class="mt-4 font-semibold text-lg">Your cart is empty</h3>
      <p class="text-ink-500 mt-1">Add a few great books to get started.</p>
      <router-link to="/books" class="btn-primary mt-6">Browse the catalog</router-link>
    </div>
  </section>
</template>
