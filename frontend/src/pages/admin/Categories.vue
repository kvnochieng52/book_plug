<script setup>
import { ref, onMounted } from 'vue'
import { AdminAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

const list = ref([])
const loading = ref(true)
const draft = ref('')
const err = ref('')

async function refresh() {
  list.value = await AdminAPI.categories.list()
}

onMounted(async () => {
  try { await refresh() } finally { loading.value = false }
})

async function add() {
  err.value = ''
  const name = draft.value.trim()
  if (!name) return
  try {
    await AdminAPI.categories.create({ name })
    draft.value = ''
    await refresh()
  } catch (e) {
    err.value = apiErrorMessage(e)
  }
}

async function remove(slug) {
  if (!confirm('Delete this category?')) return
  try {
    await AdminAPI.categories.remove(slug)
    await refresh()
  } catch (e) {
    alert(apiErrorMessage(e))
  }
}
</script>

<template>
  <div class="space-y-6 max-w-3xl">
    <div>
      <h1 class="font-display text-3xl font-bold">Categories</h1>
      <p class="text-ink-500 mt-1">Organize your catalog. Categories with books cannot be deleted.</p>
    </div>

    <form @submit.prevent="add" class="card p-4 flex gap-3">
      <input v-model="draft" class="input flex-1" placeholder="Add a new category (e.g. Poetry)" />
      <button class="btn-primary"><PlusIcon class="h-5 w-5" /> Add</button>
    </form>
    <div v-if="err" class="rounded-lg bg-red-50 text-red-700 px-4 py-2 text-sm">{{ err }}</div>

    <div class="card overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Name</th>
            <th class="px-5 py-3 text-left">Slug</th>
            <th class="px-5 py-3 text-left">Books</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-if="loading"><td colspan="4" class="px-5 py-8 text-center text-ink-400">Loading…</td></tr>
          <tr v-else v-for="c in list" :key="c.slug" class="hover:bg-ink-50/50">
            <td class="px-5 py-3 font-semibold">{{ c.name }}</td>
            <td class="px-5 py-3"><code class="text-xs">{{ c.slug }}</code></td>
            <td class="px-5 py-3">{{ c.books_count }}</td>
            <td class="px-5 py-3 text-right">
              <button @click="remove(c.slug)" class="p-2 rounded-lg hover:bg-red-50 text-red-600" :disabled="c.books_count > 0" :class="c.books_count > 0 ? 'opacity-30 cursor-not-allowed' : ''">
                <TrashIcon class="h-4 w-4" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
