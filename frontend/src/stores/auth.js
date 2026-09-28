import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { AuthAPI } from '../api'
import { getToken, setToken } from '../api/client'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const booted = ref(false)

  const isLoggedIn = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  async function boot() {
    if (booted.value) return
    booted.value = true
    if (!getToken()) return
    try {
      user.value = await AuthAPI.me()
    } catch {
      setToken(null)
      user.value = null
    }
  }

  async function login(email, password) {
    loading.value = true
    try {
      const { user: u, token } = await AuthAPI.login({ email, password })
      setToken(token)
      user.value = u
      return u
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    try {
      const { user: u, token } = await AuthAPI.register(payload)
      setToken(token)
      user.value = u
      return u
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try { await AuthAPI.logout() } catch {}
    setToken(null)
    user.value = null
  }

  return { user, loading, isLoggedIn, isAdmin, boot, login, register, logout }
})
