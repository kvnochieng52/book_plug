<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { apiErrorMessage } from '../../api/client'
import Logo from '../../components/Logo.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const email = ref('')
const password = ref('')
const remember = ref(true)
const err = ref('')

async function submit() {
  err.value = ''
  if (!email.value || !password.value) {
    err.value = 'Please enter your email and password.'
    return
  }
  try {
    await auth.login(email.value, password.value)
    const redirect = route.query.redirect?.toString()
    router.push(redirect || (auth.isAdmin ? '/admin' : '/library'))
  } catch (e) {
    err.value = apiErrorMessage(e, 'Sign in failed. Check your email and password.')
  }
}
</script>

<template>
  <section class="min-h-[70vh] grid md:grid-cols-2">
    <div class="hidden md:block relative bg-hero-gradient overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.15),transparent_40%)]"></div>
      <div class="relative h-full flex flex-col justify-between p-12 text-white">
        <Logo compact />
        <div>
          <h2 class="font-display text-4xl font-bold leading-tight">
            Pick up where <br /> you left off.
          </h2>
          <p class="mt-3 text-white/85 max-w-sm">
            Your library, your reading progress, and your orders — all in one
            place.
          </p>
        </div>
        <blockquote class="text-white/85 text-sm max-w-sm">
          "BookPlug feels like a proper indie bookstore, but online."<br />
          <span class="opacity-70">— Ava R., verified reader</span>
        </blockquote>
      </div>
    </div>
    <div class="flex items-center justify-center p-6 md:p-12">
      <form @submit.prevent="submit" class="w-full max-w-md space-y-5">
        <div>
          <h1 class="font-display text-3xl font-bold">Welcome back</h1>
          <p class="text-ink-500 mt-1">Sign in to your BookPlug account.</p>
        </div>
        <div v-if="err" class="rounded-lg bg-red-50 text-red-700 px-4 py-2 text-sm">{{ err }}</div>
        <div>
          <label class="label">Email</label>
          <input v-model="email" type="email" class="input" placeholder="you@email.com" required />
        </div>
        <div>
          <label class="label">Password</label>
          <input v-model="password" type="password" class="input" placeholder="••••••••" required />
        </div>
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="remember" class="rounded text-brand-600 focus:ring-brand-400" /> Remember me
          </label>
          <a href="#" class="text-brand-700 font-semibold hover:underline">Forgot password?</a>
        </div>
        <button :disabled="auth.loading" class="btn-primary w-full py-3 disabled:opacity-60">
          {{ auth.loading ? 'Signing in…' : 'Sign in' }}
        </button>
        <div class="relative text-center text-xs text-ink-400">
          <span class="bg-ink-50 px-2 relative z-10">or continue with</span>
          <div class="absolute left-0 right-0 top-1/2 h-px bg-ink-200"></div>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" class="btn-outline">Google</button>
          <button type="button" class="btn-outline">Apple</button>
        </div>
        <p class="text-sm text-center text-ink-500">
          No account yet?
          <router-link to="/register" class="text-brand-700 font-semibold hover:underline">Create one</router-link>
        </p>
        <p class="text-xs text-center text-ink-400">
          Tip: use an email containing <code class="text-brand-700">admin</code> to preview the admin portal.
        </p>
      </form>
    </div>
  </section>
</template>
