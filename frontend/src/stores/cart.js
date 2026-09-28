import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'

const STORAGE_KEY = 'bookplug.cart.v1'

export const useCartStore = defineStore('cart', () => {
  const items = ref(load())

  function load() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      return raw ? JSON.parse(raw) : []
    } catch {
      return []
    }
  }

  watch(items, (v) => localStorage.setItem(STORAGE_KEY, JSON.stringify(v)), { deep: true })

  const count = computed(() => items.value.reduce((n, i) => n + i.qty, 0))
  const subtotal = computed(() =>
    items.value.reduce((s, i) => s + i.qty * i.price, 0),
  )
  const shipping = computed(() =>
    items.value.some((i) => i.type === 'physical') ? 4.99 : 0,
  )
  const total = computed(() => subtotal.value + shipping.value)

  function add(book, type) {
    const price = type === 'physical' ? book.physicalPrice : book.digitalPrice
    const key = `${book.id}:${type}`
    const existing = items.value.find((i) => i.key === key)
    if (existing) existing.qty += 1
    else
      items.value.push({
        key,
        bookId: book.id,
        slug: book.slug,
        title: book.title,
        author: book.author,
        cover: book.cover,
        type,
        price,
        qty: 1,
      })
  }

  function remove(key) {
    items.value = items.value.filter((i) => i.key !== key)
  }
  function setQty(key, qty) {
    const it = items.value.find((i) => i.key === key)
    if (it) it.qty = Math.max(1, qty)
  }
  function clear() { items.value = [] }

  return { items, count, subtotal, shipping, total, add, remove, setQty, clear }
})
