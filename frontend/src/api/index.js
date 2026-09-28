import { api } from './client'

// Normalize the API's snake_case book to the camelCase shape the UI uses.
export function mapBook(b) {
  if (!b) return b
  return {
    id: b.id,
    slug: b.slug,
    title: b.title,
    author: b.author,
    description: b.description,
    pages: b.pages,
    language: b.language,
    published: b.published_at,
    isbn: b.isbn,
    hasDigital: b.has_digital,
    hasPhysical: b.has_physical,
    digitalPrice: Number(b.digital_price),
    physicalPrice: Number(b.physical_price),
    stock: b.stock,
    cover: b.cover,
    hasPdf: b.has_pdf,
    featured: b.is_featured,
    published_flag: b.is_published,
    tags: b.tags || [],
    rating: Number(b.rating),
    reviews: b.reviews,
    category: b.category_slug || b.category?.slug || null,
    categoryName: b.category?.name || null,
  }
}

// ---------- Public ----------
export const CategoriesAPI = {
  list: () => api.get('/categories').then((r) => r.data.data),
}

export const BooksAPI = {
  list: (params = {}) =>
    api.get('/books', { params }).then((r) => ({
      data: (r.data.data || []).map(mapBook),
      meta: r.data.meta,
    })),
  show: (slug) =>
    api.get(`/books/${slug}`).then((r) => ({
      book: mapBook(r.data.data),
      related: (r.data.related || []).map(mapBook),
    })),
  pdfUrl: (slug) => `${api.defaults.baseURL}/books/${slug}/pdf`,
}

// ---------- Auth ----------
export const AuthAPI = {
  register: (payload) => api.post('/auth/register', payload).then((r) => r.data),
  login: (payload) => api.post('/auth/login', payload).then((r) => r.data),
  me: () => api.get('/auth/me').then((r) => r.data.user),
  logout: () => api.post('/auth/logout').then((r) => r.data),
}

// ---------- Orders ----------
export const OrdersAPI = {
  list: () => api.get('/orders').then((r) => r.data),
  show: (ref) => api.get(`/orders/${ref}`).then((r) => r.data.data),
  create: (payload) => api.post('/orders', payload).then((r) => r.data.data),
}

// ---------- M-Pesa ----------
export const MpesaAPI = {
  initiate: (ref, phone) => api.post(`/orders/${ref}/mpesa/stk`, { phone }).then((r) => r.data),
  status: (txId) => api.get(`/mpesa/transactions/${txId}`).then((r) => r.data),
}

// ---------- Library ----------
export const LibraryAPI = {
  list: () => api.get('/library').then((r) => r.data.data),
  updateProgress: (slug, progress) =>
    api.patch(`/library/${slug}/progress`, { progress }).then((r) => r.data.data),
}

// ---------- Admin ----------
export const AdminAPI = {
  // Books
  books: {
    list: (params = {}) => api.get('/admin/books', { params }).then((r) => r.data),
    show: (slug) => api.get(`/admin/books/${slug}`).then((r) => r.data.data),
    create: (formData) =>
      api.post('/admin/books', formData, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data.data),
    update: (slug, formData) =>
      api.post(`/admin/books/${slug}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        params: { _method: 'PUT' },
      }).then((r) => r.data.data),
    remove: (slug) => api.delete(`/admin/books/${slug}`).then((r) => r.data),
  },
  categories: {
    list: () => api.get('/admin/categories').then((r) => r.data.data),
    create: (payload) => api.post('/admin/categories', payload).then((r) => r.data.data),
    update: (slug, payload) => api.put(`/admin/categories/${slug}`, payload).then((r) => r.data.data),
    remove: (slug) => api.delete(`/admin/categories/${slug}`).then((r) => r.data),
  },
  orders: {
    list: (params = {}) => api.get('/admin/orders', { params }).then((r) => r.data),
    show: (ref) => api.get(`/admin/orders/${ref}`).then((r) => r.data.data),
    updateStatus: (ref, status) => api.patch(`/admin/orders/${ref}/status`, { status }).then((r) => r.data.data),
  },
  deliveries: {
    list: () => api.get('/admin/deliveries').then((r) => r.data),
    upsert: (ref, payload) => api.put(`/admin/deliveries/${ref}`, payload).then((r) => r.data.data),
  },
  users: {
    list: (params = {}) => api.get('/admin/users', { params }).then((r) => r.data),
    updateRole: (id, role) => api.patch(`/admin/users/${id}/role`, { role }).then((r) => r.data.data),
  },
}
