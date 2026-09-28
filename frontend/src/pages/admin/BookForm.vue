<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { AdminAPI, CategoriesAPI } from '../../api'
import { apiErrorMessage } from '../../api/client'
import { CloudArrowUpIcon, PhotoIcon, TrashIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const editing = computed(() => !!route.params.slug)

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const err = ref('')

const form = ref({
  title: '',
  author: '',
  category_id: '',
  description: '',
  pages: 0,
  language: 'English',
  published_at: '',
  isbn: '',
  digital_price: 0,
  physical_price: 0,
  has_digital: true,
  has_physical: true,
  stock: 25,
  weight_grams: 350,
  is_featured: false,
  is_published: true,
  tags: [],
})

const coverFile = ref(null)
const pdfFile = ref(null)
const coverPreview = ref('')
const pdfName = ref('')

onMounted(async () => {
  categories.value = await AdminAPI.categories.list()

  if (editing.value) {
    loading.value = true
    try {
      const b = await AdminAPI.books.show(route.params.slug)
      form.value = {
        title: b.title,
        author: b.author,
        category_id: categories.value.find((c) => c.slug === b.category?.slug || c.slug === b.category_slug)?.id || '',
        description: b.description,
        pages: b.pages,
        language: b.language,
        published_at: b.published_at || '',
        isbn: b.isbn || '',
        digital_price: Number(b.digital_price),
        physical_price: Number(b.physical_price),
        has_digital: b.has_digital,
        has_physical: b.has_physical,
        stock: b.stock,
        weight_grams: b.weight_grams,
        is_featured: b.is_featured,
        is_published: b.is_published,
        tags: b.tags || [],
      }
      coverPreview.value = b.cover
    } finally {
      loading.value = false
    }
  } else if (categories.value.length) {
    form.value.category_id = categories.value[0].id
  }
})

function pickCover(e) {
  const f = e.target.files?.[0]
  if (!f) return
  coverFile.value = f
  coverPreview.value = URL.createObjectURL(f)
}
function pickPdf(e) {
  const f = e.target.files?.[0]
  if (!f) return
  pdfFile.value = f
  pdfName.value = f.name
}

function buildFormData() {
  const fd = new FormData()
  const obj = form.value
  for (const [k, v] of Object.entries(obj)) {
    if (v === null || v === undefined) continue
    if (k === 'tags') {
      v.forEach((t) => fd.append('tags[]', t))
    } else if (typeof v === 'boolean') {
      fd.append(k, v ? '1' : '0')
    } else {
      fd.append(k, v)
    }
  }
  if (coverFile.value) fd.append('cover', coverFile.value)
  if (pdfFile.value) fd.append('pdf', pdfFile.value)
  return fd
}

async function save() {
  err.value = ''
  saving.value = true
  try {
    if (editing.value) {
      await AdminAPI.books.update(route.params.slug, buildFormData())
    } else {
      await AdminAPI.books.create(buildFormData())
    }
    router.push('/admin/books')
  } catch (e) {
    err.value = apiErrorMessage(e, 'Save failed. Check the form fields.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="space-y-6 max-w-5xl">
    <div>
      <router-link to="/admin/books" class="text-sm text-brand-700 hover:underline">← Back to books</router-link>
      <h1 class="font-display text-3xl font-bold mt-2">{{ editing ? 'Edit book' : 'Add a new book' }}</h1>
      <p class="text-ink-500 mt-1">Fill in the details, upload the cover and the PDF, then publish.</p>
    </div>

    <div v-if="loading" class="card p-10 text-center text-ink-500">Loading book…</div>

    <form v-else @submit.prevent="save" class="grid gap-6 lg:grid-cols-[1fr_320px]">
      <div class="space-y-6">
        <div v-if="err" class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ err }}</div>

        <div class="card p-6 space-y-4">
          <h3 class="font-semibold">Book details</h3>
          <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="label">Title *</label>
              <input v-model="form.title" required class="input" />
            </div>
            <div>
              <label class="label">Author *</label>
              <input v-model="form.author" required class="input" />
            </div>
            <div>
              <label class="label">Category *</label>
              <select v-model="form.category_id" required class="input">
                <option value="" disabled>Choose a category…</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="label">Description *</label>
              <textarea v-model="form.description" rows="4" required class="input"></textarea>
            </div>
            <div><label class="label">Pages</label><input type="number" v-model.number="form.pages" class="input" /></div>
            <div><label class="label">Language</label><input v-model="form.language" class="input" /></div>
            <div><label class="label">Published</label><input type="date" v-model="form.published_at" class="input" /></div>
            <div><label class="label">ISBN</label><input v-model="form.isbn" placeholder="978-1-…" class="input" /></div>
          </div>
        </div>

        <div class="card p-6 space-y-4">
          <h3 class="font-semibold">Formats & pricing</h3>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="card p-4 flex items-start gap-3 cursor-pointer" :class="form.has_digital ? 'border-brand-500 ring-2 ring-brand-200' : ''">
              <input type="checkbox" v-model="form.has_digital" class="mt-1 rounded text-brand-600 focus:ring-brand-400" />
              <div>
                <div class="font-semibold">Digital (PDF)</div>
                <p class="text-xs text-ink-500 mt-0.5">Users read online or download.</p>
              </div>
            </label>
            <label class="card p-4 flex items-start gap-3 cursor-pointer" :class="form.has_physical ? 'border-accent-500 ring-2 ring-accent-200' : ''">
              <input type="checkbox" v-model="form.has_physical" class="mt-1 rounded text-accent-600 focus:ring-accent-400" />
              <div>
                <div class="font-semibold">Physical (paperback)</div>
                <p class="text-xs text-ink-500 mt-0.5">Requires shipping details.</p>
              </div>
            </label>
          </div>
          <div class="grid gap-4 sm:grid-cols-3">
            <div>
              <label class="label">Digital price (KES)</label>
              <input type="number" step="0.01" v-model.number="form.digital_price" :disabled="!form.has_digital" class="input disabled:opacity-50" />
            </div>
            <div>
              <label class="label">Physical price (KES)</label>
              <input type="number" step="0.01" v-model.number="form.physical_price" :disabled="!form.has_physical" class="input disabled:opacity-50" />
            </div>
            <div>
              <label class="label">Stock</label>
              <input type="number" v-model.number="form.stock" :disabled="!form.has_physical" class="input disabled:opacity-50" />
            </div>
          </div>
          <div v-if="form.has_physical">
            <label class="label">Weight (grams) — for shipping</label>
            <input type="number" v-model.number="form.weight_grams" class="input max-w-xs" />
          </div>
        </div>

        <div class="card p-6 space-y-4">
          <h3 class="font-semibold">Files</h3>
          <div>
            <label class="label">Book PDF (digital)</label>
            <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-dashed border-ink-200 hover:border-brand-400 cursor-pointer">
              <CloudArrowUpIcon class="h-6 w-6 text-brand-600" />
              <div class="text-sm">
                <div class="font-semibold" v-if="pdfName">{{ pdfName }}</div>
                <div class="font-semibold" v-else>Upload PDF</div>
                <div class="text-xs text-ink-500">PDF file, max 40 MB</div>
              </div>
              <input type="file" accept=".pdf" class="hidden" @change="pickPdf" />
            </label>
          </div>
        </div>
      </div>

      <aside class="space-y-6">
        <div class="card p-6">
          <h3 class="font-semibold mb-3">Cover image</h3>
          <div class="aspect-[2/3] rounded-xl overflow-hidden bg-ink-100 flex items-center justify-center">
            <img v-if="coverPreview" :src="coverPreview" class="h-full w-full object-cover" />
            <PhotoIcon v-else class="h-14 w-14 text-ink-300" />
          </div>
          <label class="btn-outline w-full mt-3 cursor-pointer">
            <CloudArrowUpIcon class="h-4 w-4" /> Upload cover
            <input type="file" accept="image/*" class="hidden" @change="pickCover" />
          </label>
          <button v-if="coverPreview" type="button" class="text-xs text-red-600 hover:underline mt-2 flex items-center gap-1"
            @click="coverPreview = ''; coverFile = null">
            <TrashIcon class="h-3.5 w-3.5" /> Remove cover
          </button>
        </div>
        <div class="card p-6 space-y-3">
          <h3 class="font-semibold">Visibility</h3>
          <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" v-model="form.is_featured" class="mt-1 rounded text-brand-600 focus:ring-brand-400" />
            <span>Feature on the homepage</span>
          </label>
          <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" v-model="form.is_published" class="mt-1 rounded text-brand-600 focus:ring-brand-400" />
            <span>Published (visible to shoppers)</span>
          </label>
        </div>
        <div class="flex flex-col gap-2">
          <button :disabled="saving" class="btn-primary py-3 disabled:opacity-60">
            {{ saving ? 'Saving…' : (editing ? 'Save changes' : 'Publish book') }}
          </button>
          <router-link to="/admin/books" class="btn-outline w-full">Cancel</router-link>
        </div>
      </aside>
    </form>
  </div>
</template>
