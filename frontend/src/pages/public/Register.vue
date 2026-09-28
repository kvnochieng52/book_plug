<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { apiErrorMessage } from '../../api/client'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const err = ref('')

async function submit() {
  err.value = ''
  if (!name.value || !email.value || password.value.length < 6) {
    err.value = 'Please enter your name, email, and a password (6+ chars).'
    return
  }
  try {
    await auth.register({ name: name.value, email: email.value, phone: phone.value, password: password.value })
    router.push('/library')
  } catch (e) {
    err.value = apiErrorMessage(e, 'Could not create your account.')
  }
}
</script>

<template>
  <section class="min-h-[70vh] grid md:grid-cols-2">
    <div class="flex items-center justify-center p-6 md:p-12 order-2 md:order-1">
      <form @submit.prevent="submit" class="w-full max-w-md space-y-5">
        <div>
          <h1 class="font-display text-3xl font-bold">Create your account</h1>
          <p class="text-ink-500 mt-1">Get personalized recommendations, save favorites, and buy in one click.</p>
        </div>
        <div v-if="err" class="rounded-lg bg-red-50 text-red-700 px-4 py-2 text-sm">{{ err }}</div>
        <div>
          <label class="label">Full name</label>
          <input v-model="name" class="input" placeholder="Jane Doe" required />
        </div>
        <div>
          <label class="label">Email</label>
          <input v-model="email" type="email" class="input" placeholder="you@email.com" required />
        </div>
        <div>
          <label class="label">Phone <span class="text-ink-400 font-normal">(for M-Pesa)</span></label>
          <input v-model="phone" type="tel" class="input" placeholder="+254 700 000 000" />
        </div>
        <div>
          <label class="label">Password</label>
          <input v-model="password" type="password" class="input" placeholder="Minimum 6 characters" required />
        </div>
        <label class="flex items-start gap-2 text-sm text-ink-600">
          <input type="checkbox" required class="mt-1 rounded text-brand-600 focus:ring-brand-400" />
          <span>I agree to BookPlug's <a href="#" class="text-brand-700 hover:underline">terms</a> and <a href="#" class="text-brand-700 hover:underline">privacy policy</a>.</span>
        </label>
        <button :disabled="auth.loading" class="btn-primary w-full py-3 disabled:opacity-60">
          {{ auth.loading ? 'Creating account…' : 'Create account' }}
        </button>
        <p class="text-sm text-center text-ink-500">
          Already have an account?
          <router-link to="/login" class="text-brand-700 font-semibold hover:underline">Sign in</router-link>
        </p>
      </form>
    </div>
    <div class="hidden md:block relative bg-hero-gradient overflow-hidden order-1 md:order-2">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(255,255,255,0.15),transparent_40%)]"></div>
      <div class="relative h-full flex items-center p-12 text-white">
        <div>
          <span class="badge bg-white/15 text-white border border-white/20">Free forever</span>
          <h2 class="mt-4 font-display text-4xl font-bold leading-tight">
            Start your <br />reading life.
          </h2>
          <ul class="mt-6 space-y-3 text-white/90">
            <li>📚 Free 20-page previews on every book</li>
            <li>💾 Keep your PDFs forever, DRM-free</li>
            <li>🚚 Order the paperback too — we deliver</li>
            <li>⭐ Personal recommendations based on your taste</li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>
