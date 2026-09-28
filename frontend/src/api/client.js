import axios from 'axios'

const TOKEN_KEY = 'bookplug.token'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080/api',
  headers: { Accept: 'application/json' },
})

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}
export function setToken(token) {
  if (token) localStorage.setItem(TOKEN_KEY, token)
  else localStorage.removeItem(TOKEN_KEY)
}

api.interceptors.request.use((config) => {
  const token = getToken()
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

let onUnauthorized = null
export function setUnauthorizedHandler(fn) {
  onUnauthorized = fn
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status
    if (status === 401) {
      setToken(null)
      if (onUnauthorized) onUnauthorized()
    }
    return Promise.reject(error)
  },
)

// Helper: extract a human-readable error message
export function apiErrorMessage(err, fallback = 'Something went wrong.') {
  const r = err?.response?.data
  if (!r) return err?.message || fallback
  if (r.message) return r.message
  if (r.errors) {
    const first = Object.values(r.errors)[0]
    return Array.isArray(first) ? first[0] : String(first)
  }
  return fallback
}
