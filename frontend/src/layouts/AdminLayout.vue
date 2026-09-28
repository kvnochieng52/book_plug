<script setup>
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRouter } from 'vue-router'
import Logo from '../components/Logo.vue'
import {
  HomeIcon,
  BookOpenIcon,
  ShoppingCartIcon,
  TruckIcon,
  UsersIcon,
  TagIcon,
  Cog6ToothIcon,
  ArrowLeftOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  MagnifyingGlassIcon,
  BellIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const open = ref(false)

const nav = [
  { to: '/admin', icon: HomeIcon, label: 'Dashboard', exact: true },
  { to: '/admin/books', icon: BookOpenIcon, label: 'Books' },
  { to: '/admin/orders', icon: ShoppingCartIcon, label: 'Orders' },
  { to: '/admin/deliveries', icon: TruckIcon, label: 'Deliveries' },
  { to: '/admin/users', icon: UsersIcon, label: 'Users' },
  { to: '/admin/categories', icon: TagIcon, label: 'Categories' },
  { to: '/admin/settings', icon: Cog6ToothIcon, label: 'Settings' },
]

function logout() {
  auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-ink-50 flex">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-40 w-72 bg-ink-900 text-ink-100 flex flex-col transform transition-transform lg:translate-x-0 lg:static',
        open ? 'translate-x-0' : '-translate-x-full',
      ]"
    >
      <div class="h-16 px-5 flex items-center justify-between border-b border-ink-800">
        <div class="flex items-center gap-2">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-hero-gradient shadow-book">
            <svg viewBox="0 0 24 24" class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h9a4 4 0 0 1 4 4v12H8a4 4 0 0 1-4-4V4Z" />
              <path d="M20 6v14" />
            </svg>
          </span>
          <div>
            <div class="font-display font-bold text-white leading-none">BookPlug</div>
            <div class="text-[11px] tracking-widest text-brand-300 uppercase">Admin</div>
          </div>
        </div>
        <button class="lg:hidden p-1 rounded-md hover:bg-ink-800" @click="open = false">
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <nav class="flex-1 overflow-y-auto p-4 space-y-1">
        <RouterLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          :end="item.exact"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-ink-300 hover:bg-ink-800 hover:text-white transition"
          active-class="bg-brand-600 text-white hover:bg-brand-600"
          @click="open = false"
        >
          <component :is="item.icon" class="h-5 w-5" />
          <span class="font-medium">{{ item.label }}</span>
        </RouterLink>
      </nav>

      <div class="p-4 border-t border-ink-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="h-9 w-9 rounded-full bg-accent-500 text-white flex items-center justify-center font-bold">
            {{ (auth.user?.name || 'Admin')[0].toUpperCase() }}
          </div>
          <div class="min-w-0">
            <div class="text-sm font-semibold truncate">{{ auth.user?.name || 'Admin User' }}</div>
            <div class="text-xs text-ink-400 truncate">{{ auth.user?.email || 'admin@bookplug.io' }}</div>
          </div>
        </div>
        <button
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-ink-300 hover:bg-ink-800 hover:text-white"
          @click="logout"
        >
          <ArrowLeftOnRectangleIcon class="h-5 w-5" /> Sign out
        </button>
      </div>
    </aside>

    <!-- Backdrop -->
    <div
      v-if="open"
      class="fixed inset-0 z-30 bg-ink-900/60 lg:hidden"
      @click="open = false"
    />

    <div class="flex-1 flex flex-col min-w-0">
      <!-- Topbar -->
      <header class="h-16 bg-white border-b border-ink-100 flex items-center gap-3 px-4 sticky top-0 z-20">
        <button class="lg:hidden p-2 rounded-md hover:bg-ink-100" @click="open = true">
          <Bars3Icon class="h-6 w-6" />
        </button>
        <div class="relative w-full max-w-md">
          <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-ink-400" />
          <input class="input pl-10" placeholder="Search books, orders, users…" />
        </div>
        <div class="ml-auto flex items-center gap-2">
          <router-link to="/" class="btn-ghost text-sm">View store ↗</router-link>
          <button class="relative p-2 rounded-lg hover:bg-ink-100">
            <BellIcon class="h-6 w-6 text-ink-600" />
            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-accent-500 ring-2 ring-white"></span>
          </button>
        </div>
      </header>

      <main class="flex-1 p-4 md:p-8">
        <RouterView />
      </main>
    </div>
  </div>
</template>
