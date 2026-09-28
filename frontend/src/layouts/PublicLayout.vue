<script setup>
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRouter } from 'vue-router'
import {
  Bars3Icon,
  XMarkIcon,
  MagnifyingGlassIcon,
  ShoppingBagIcon,
  UserCircleIcon,
} from '@heroicons/vue/24/outline'
import Logo from '../components/Logo.vue'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const cart = useCartStore()
const auth = useAuthStore()

const menuOpen = ref(false)
const q = ref('')

const nav = [
  { to: '/', label: 'Home' },
  { to: '/books', label: 'Books' },
  { to: '/about', label: 'About' },
]

function submitSearch() {
  router.push({ name: 'books', query: q.value ? { q: q.value } : {} })
  menuOpen.value = false
}

async function handleLogout() {
  menuOpen.value = false
  await auth.logout()
  router.push('/')
}

const initials = computed(() =>
  auth.user?.name
    ?.split(' ')
    .map((p) => p[0])
    .slice(0, 2)
    .join('')
    .toUpperCase() || '',
)
</script>

<template>
  <div class="min-h-screen flex flex-col bg-ink-50">
    <!-- Top ribbon -->
    <div class="bg-hero-gradient text-white text-xs">
      <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
        <span>Free digital preview on every book • Physical books delivered nationwide</span>
        <router-link to="/admin" class="hidden sm:inline hover:underline">Admin portal →</router-link>
      </div>
    </div>

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white/85 backdrop-blur border-b border-ink-100">
      <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">
        <Logo />
        <nav class="hidden md:flex items-center gap-1 ml-4">
          <RouterLink
            v-for="link in nav"
            :key="link.to"
            :to="link.to"
            class="px-3 py-2 rounded-lg text-sm font-medium text-ink-700 hover:text-brand-700 hover:bg-brand-50 transition"
            active-class="text-brand-700 bg-brand-50"
          >{{ link.label }}</RouterLink>
        </nav>

        <form @submit.prevent="submitSearch" class="hidden md:flex flex-1 max-w-md ml-auto">
          <div class="relative w-full">
            <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-ink-400" />
            <input
              v-model="q"
              type="search"
              placeholder="Search by title, author…"
              class="input pl-10"
            />
          </div>
        </form>

        <div class="ml-auto md:ml-0 flex items-center gap-1">
          <router-link
            to="/cart"
            class="relative p-2 rounded-lg text-ink-700 hover:bg-ink-100"
            aria-label="Cart"
          >
            <ShoppingBagIcon class="h-6 w-6" />
            <span
              v-if="cart.count"
              class="absolute -top-1 -right-1 h-5 min-w-[20px] px-1 rounded-full bg-accent-500 text-white text-[11px] font-bold flex items-center justify-center"
            >{{ cart.count }}</span>
          </router-link>
          <template v-if="auth.isLoggedIn">
            <div class="hidden sm:flex items-center gap-2">
              <router-link
                to="/library"
                class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full bg-brand-50 text-brand-800 hover:bg-brand-100"
              >
                <span class="h-7 w-7 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center">
                  {{ initials }}
                </span>
                <span class="text-sm font-medium">My library</span>
              </router-link>
              <router-link v-if="auth.isAdmin" to="/admin" class="btn-outline text-sm py-1.5 px-3">Admin</router-link>
              <button @click="handleLogout" class="btn-ghost text-sm py-1.5">Sign out</button>
            </div>
          </template>
          <template v-else>
            <router-link to="/login" class="hidden sm:inline-flex btn-ghost text-sm">Sign in</router-link>
            <router-link to="/register" class="hidden sm:inline-flex btn-primary">Get started</router-link>
          </template>
          <button
            class="md:hidden p-2 rounded-lg hover:bg-ink-100"
            @click="menuOpen = !menuOpen"
          >
            <Bars3Icon v-if="!menuOpen" class="h-6 w-6" />
            <XMarkIcon v-else class="h-6 w-6" />
          </button>
        </div>
      </div>
      <!-- Mobile menu -->
      <div v-if="menuOpen" class="md:hidden border-t border-ink-100 bg-white">
        <div class="px-4 py-3 space-y-2">
          <form @submit.prevent="submitSearch">
            <div class="relative">
              <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-ink-400" />
              <input v-model="q" type="search" placeholder="Search…" class="input pl-10" />
            </div>
          </form>
          <RouterLink
            v-for="link in nav"
            :key="link.to"
            :to="link.to"
            class="block px-3 py-2 rounded-lg text-ink-700 hover:bg-brand-50"
            @click="menuOpen = false"
          >{{ link.label }}</RouterLink>
          <template v-if="auth.isLoggedIn">
            <router-link to="/library" class="btn-primary w-full" @click="menuOpen = false">My library</router-link>
            <router-link v-if="auth.isAdmin" to="/admin" class="btn-outline w-full" @click="menuOpen = false">Admin</router-link>
            <button class="btn-ghost w-full" @click="handleLogout">Sign out</button>
          </template>
          <template v-else>
            <router-link to="/login" class="btn-outline w-full" @click="menuOpen = false">Sign in</router-link>
            <router-link to="/register" class="btn-primary w-full" @click="menuOpen = false">Create account</router-link>
          </template>
        </div>
      </div>
    </header>

    <main class="flex-1">
      <RouterView />
    </main>

    <!-- Footer -->
    <footer class="mt-16 bg-ink-900 text-ink-200">
      <div class="max-w-7xl mx-auto px-4 py-14 grid gap-10 md:grid-cols-4">
        <div class="space-y-4">
          <Logo />
          <p class="text-sm text-ink-400 max-w-xs">
            Read anywhere, own the ones you love. Digital and physical books
            curated for readers who care.
          </p>
        </div>
        <div>
          <h4 class="font-semibold text-white mb-3">Explore</h4>
          <ul class="space-y-2 text-sm">
            <li><router-link to="/books" class="hover:text-white">All books</router-link></li>
            <li><router-link to="/books?filter=bestseller" class="hover:text-white">Bestsellers</router-link></li>
            <li><router-link to="/books?filter=new" class="hover:text-white">New releases</router-link></li>
            <li><router-link to="/about" class="hover:text-white">About us</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold text-white mb-3">Support</h4>
          <ul class="space-y-2 text-sm">
            <li><a class="hover:text-white" href="#">Help center</a></li>
            <li><a class="hover:text-white" href="#">Shipping</a></li>
            <li><a class="hover:text-white" href="#">Refunds</a></li>
            <li><a class="hover:text-white" href="#">Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold text-white mb-3">Stay in the loop</h4>
          <p class="text-sm text-ink-400 mb-3">New releases and staff picks in your inbox.</p>
          <form class="flex gap-2">
            <input type="email" placeholder="you@email.com" class="input flex-1 bg-ink-800 border-ink-700 text-white placeholder:text-ink-500" />
            <button class="btn-accent">Join</button>
          </form>
        </div>
      </div>
      <div class="border-t border-ink-800 py-5 text-center text-xs text-ink-500">
        © 2026 BookPlug. All rights reserved.
      </div>
    </footer>
  </div>
</template>
