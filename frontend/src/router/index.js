import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const PublicLayout = () => import('../layouts/PublicLayout.vue')
const AdminLayout = () => import('../layouts/AdminLayout.vue')

const routes = [
  {
    path: '/',
    component: PublicLayout,
    children: [
      { path: '', name: 'home', component: () => import('../pages/public/Home.vue') },
      { path: 'books', name: 'books', component: () => import('../pages/public/Catalog.vue') },
      { path: 'books/:slug', name: 'book-detail', component: () => import('../pages/public/BookDetail.vue') },
      { path: 'read/:slug', name: 'reader', component: () => import('../pages/public/Reader.vue'), meta: { requiresAuth: true } },
      { path: 'cart', name: 'cart', component: () => import('../pages/public/Cart.vue') },
      { path: 'checkout', name: 'checkout', component: () => import('../pages/public/Checkout.vue'), meta: { requiresAuth: true } },
      { path: 'checkout/success', name: 'checkout-success', component: () => import('../pages/public/CheckoutSuccess.vue'), meta: { requiresAuth: true } },
      { path: 'login', name: 'login', component: () => import('../pages/public/Login.vue'), meta: { guestOnly: true } },
      { path: 'register', name: 'register', component: () => import('../pages/public/Register.vue'), meta: { guestOnly: true } },
      { path: 'library', name: 'library', component: () => import('../pages/public/Library.vue'), meta: { requiresAuth: true } },
      { path: 'about', name: 'about', component: () => import('../pages/public/About.vue') },
    ],
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAdmin: true },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('../pages/admin/Dashboard.vue') },
      { path: 'books', name: 'admin-books', component: () => import('../pages/admin/Books.vue') },
      { path: 'books/new', name: 'admin-book-new', component: () => import('../pages/admin/BookForm.vue') },
      { path: 'books/:slug/edit', name: 'admin-book-edit', component: () => import('../pages/admin/BookForm.vue') },
      { path: 'orders', name: 'admin-orders', component: () => import('../pages/admin/Orders.vue') },
      { path: 'deliveries', name: 'admin-deliveries', component: () => import('../pages/admin/Deliveries.vue') },
      { path: 'users', name: 'admin-users', component: () => import('../pages/admin/Users.vue') },
      { path: 'categories', name: 'admin-categories', component: () => import('../pages/admin/Categories.vue') },
      { path: 'settings', name: 'admin-settings', component: () => import('../pages/admin/Settings.vue') },
    ],
  },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../pages/NotFound.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() { return { top: 0 } },
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  await auth.boot()

  if (to.meta.requiresAdmin) {
    if (!auth.isLoggedIn) return { name: 'login', query: { redirect: to.fullPath } }
    if (!auth.isAdmin) return { name: 'home' }
  }
  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if (to.meta.guestOnly && auth.isLoggedIn) {
    return { name: auth.isAdmin ? 'admin-dashboard' : 'library' }
  }
})

export default router
