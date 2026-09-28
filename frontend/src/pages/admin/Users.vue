<script setup>
import { ref, watch, onMounted } from 'vue'
import { AdminAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const users = ref([])
const total = ref(0)
const adminsCount = ref(0)
const loading = ref(true)

const q = ref('')
const role = ref('all')

let debounce

async function fetchUsers() {
  loading.value = true
  try {
    const params = { per_page: 30 }
    if (role.value !== 'all') params.role = role.value
    if (q.value) params.q = q.value
    const res = await AdminAPI.users.list(params)
    users.value = res.data?.data || res.data || []
    total.value = res.data?.total || users.value.length
    if (role.value === 'all') {
      adminsCount.value = users.value.filter((u) => u.role === 'admin').length
    }
  } finally {
    loading.value = false
  }
}

onMounted(fetchUsers)

watch([q, role], () => {
  clearTimeout(debounce)
  debounce = setTimeout(fetchUsers, 250)
})

async function changeRole(user, newRole) {
  try {
    await AdminAPI.users.updateRole(user.id, newRole)
    await fetchUsers()
  } catch (e) {
    alert(apiErrorMessage(e))
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="font-display text-3xl font-bold">Users</h1>
      <p class="text-ink-500 mt-1">{{ total }} accounts</p>
    </div>

    <div class="card p-4 grid gap-3 md:grid-cols-[1fr_200px]">
      <div class="relative">
        <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-2.5 text-ink-400" />
        <input v-model="q" class="input pl-10" placeholder="Search by name or email…" />
      </div>
      <select v-model="role" class="input">
        <option value="all">All roles</option>
        <option value="user">Readers</option>
        <option value="admin">Admins</option>
      </select>
    </div>

    <div class="card overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">User</th>
            <th class="px-5 py-3 text-left">Role</th>
            <th class="px-5 py-3 text-left">Orders</th>
            <th class="px-5 py-3 text-left">Lifetime spend</th>
            <th class="px-5 py-3 text-left">Joined</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-if="loading"><td colspan="5" class="px-5 py-10 text-center text-ink-400">Loading users…</td></tr>
          <tr v-else v-for="u in users" :key="u.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3">
              <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-sm">
                  {{ u.name.split(' ').map(p => p[0]).slice(0,2).join('') }}
                </div>
                <div>
                  <div class="font-semibold">{{ u.name }}</div>
                  <div class="text-xs text-ink-500">{{ u.email }}</div>
                </div>
              </div>
            </td>
            <td class="px-5 py-3">
              <select :value="u.role" @change="changeRole(u, $event.target.value)"
                :class="['badge cursor-pointer border-0', u.role === 'admin' ? 'bg-brand-100 text-brand-800' : 'bg-ink-100 text-ink-700']">
                <option value="user">user</option>
                <option value="admin">admin</option>
              </select>
            </td>
            <td class="px-5 py-3">{{ u.orders_count || 0 }}</td>
            <td class="px-5 py-3 font-semibold">KES {{ Number(u.spent || 0).toFixed(2) }}</td>
            <td class="px-5 py-3 text-ink-500">{{ new Date(u.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
