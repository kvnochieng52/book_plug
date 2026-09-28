import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import { setUnauthorizedHandler } from './api/client'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
app.use(createPinia())
app.use(router)

const auth = useAuthStore()

// If the API ever returns 401, drop the user and bounce to /login (unless already there).
setUnauthorizedHandler(() => {
  auth.$patch({ user: null })
  const current = router.currentRoute.value
  if (!['login', 'register', 'home', 'books', 'book-detail', 'about'].includes(current.name)) {
    router.push({ name: 'login', query: { redirect: current.fullPath } })
  }
})

// Hydrate session before mounting so route guards see the correct auth state.
auth.boot().finally(() => app.mount('#app'))
