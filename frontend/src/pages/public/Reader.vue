<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { BooksAPI, LibraryAPI } from '../../api'
import { getToken } from '../../api/client'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  ArrowDownTrayIcon,
  BookmarkIcon,
  AdjustmentsHorizontalIcon,
  MagnifyingGlassMinusIcon,
  MagnifyingGlassPlusIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const book = ref(null)
const owned = ref(false)

onMounted(async () => {
  try {
    const res = await BooksAPI.show(route.params.slug)
    book.value = res.book
  } catch {}

  // Detect entitlement so the download button hits the gated endpoint properly.
  try {
    const library = await LibraryAPI.list()
    owned.value = library.some((i) => i.book?.slug === route.params.slug)
  } catch {}
})

async function downloadPdf() {
  if (!book.value) return
  // Fetch as authorized blob and trigger a download so the Bearer token is sent.
  const res = await fetch(BooksAPI.pdfUrl(book.value.slug), {
    headers: { Authorization: `Bearer ${getToken()}` },
  })
  if (!res.ok) {
    alert(res.status === 403 ? 'Purchase the digital edition to download this PDF.' : 'This book has no PDF available yet.')
    return
  }
  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${book.value.slug}.pdf`
  document.body.appendChild(a); a.click(); a.remove()
  URL.revokeObjectURL(url)
}

const pages = Array.from({ length: 8 }, (_, i) => i + 1)
const currentPage = ref(1)
const zoom = ref(100)
const sepia = ref(false)

const chapters = [
  { n: 1, title: 'Prologue', page: 1 },
  { n: 2, title: 'The Stranger at Dawn', page: 2 },
  { n: 3, title: 'A House by the Water', page: 3 },
  { n: 4, title: 'What the Woods Remembered', page: 4 },
  { n: 5, title: 'Interlude', page: 5 },
  { n: 6, title: 'The Weight of Small Things', page: 6 },
  { n: 7, title: 'Coming Home', page: 7 },
  { n: 8, title: 'Epilogue', page: 8 },
]

const sample =
  `Chapter opening — this is a preview built from mock data. In the real
build this pane will render the actual PDF via an embedded reader
(PDF.js), streaming pages from the backend based on the user's
entitlement.

The trees never asked her to come back. They simply waited, the way
old things do — patient as stone, patient as light. She followed the
path from the harbour up through the pines, and by the time she
reached the ridge, the salt in her hair had turned to something
sweeter: the smell of resin, of a summer that refused to end.

She had promised herself she would not cry. That was the first
promise she broke.`

function prev() { if (currentPage.value > 1) currentPage.value-- }
function next() { if (currentPage.value < pages.length) currentPage.value++ }
</script>

<template>
  <div v-if="!book" class="max-w-3xl mx-auto px-4 py-24 text-center">
    <h1 class="section-title">Book not found</h1>
    <router-link to="/books" class="btn-primary mt-6">Back to catalog</router-link>
  </div>

  <template v-else>
    <div class="bg-ink-900 text-ink-100 min-h-[calc(100vh-8rem)]">
      <!-- Reader top bar -->
      <div class="border-b border-ink-800 bg-ink-950/50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3">
          <router-link :to="{ name: 'book-detail', params: { slug: book.slug } }" class="btn bg-ink-800 text-white hover:bg-ink-700">
            ← Back
          </router-link>
          <div class="min-w-0">
            <div class="text-xs text-ink-400">Reading preview</div>
            <div class="font-semibold truncate">{{ book.title }}</div>
          </div>
          <div class="ml-auto flex items-center gap-2">
            <button class="p-2 rounded-lg hover:bg-ink-800" @click="zoom = Math.max(70, zoom - 10)">
              <MagnifyingGlassMinusIcon class="h-5 w-5" />
            </button>
            <span class="text-sm w-12 text-center">{{ zoom }}%</span>
            <button class="p-2 rounded-lg hover:bg-ink-800" @click="zoom = Math.min(150, zoom + 10)">
              <MagnifyingGlassPlusIcon class="h-5 w-5" />
            </button>
            <button
              @click="sepia = !sepia"
              :class="['btn text-sm', sepia ? 'bg-accent-500 text-white' : 'bg-ink-800 text-white hover:bg-ink-700']"
            ><AdjustmentsHorizontalIcon class="h-4 w-4" /> {{ sepia ? 'Sepia' : 'Day' }}</button>
            <button class="btn bg-ink-800 text-white hover:bg-ink-700"><BookmarkIcon class="h-4 w-4" /> Save</button>
            <button class="btn-accent" @click="downloadPdf"><ArrowDownTrayIcon class="h-4 w-4" /> Download PDF</button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 py-8 grid gap-6 lg:grid-cols-[240px_1fr]">
        <!-- Chapters -->
        <aside class="hidden lg:block">
          <div class="text-xs uppercase tracking-widest text-ink-400 mb-3">Chapters</div>
          <ol class="space-y-1">
            <li v-for="c in chapters" :key="c.n">
              <button
                @click="currentPage = c.page"
                :class="['w-full text-left px-3 py-2 rounded-lg text-sm transition',
                  currentPage === c.page ? 'bg-brand-600 text-white' : 'hover:bg-ink-800 text-ink-300']"
              >
                <span class="text-xs opacity-70 mr-2">{{ String(c.n).padStart(2,'0') }}</span>{{ c.title }}
              </button>
            </li>
          </ol>
        </aside>

        <!-- Page -->
        <div class="flex flex-col items-center">
          <div
            :class="['rounded-lg shadow-2xl mx-auto transition',
              sepia ? 'bg-[#f6ecd8] text-[#4a3a24]' : 'bg-white text-ink-900']"
            :style="{ width: `min(720px, ${zoom}%)`, minHeight: '80vh' }"
          >
            <div class="p-10 md:p-16 leading-relaxed" :style="{ fontSize: `${zoom / 100}rem` }">
              <div class="text-xs uppercase tracking-widest opacity-60">
                {{ book.title }} · Page {{ currentPage }}
              </div>
              <h2 class="font-display text-3xl md:text-4xl font-bold mt-2 mb-6">
                {{ chapters.find(c => c.page === currentPage)?.title || 'Chapter' }}
              </h2>
              <p class="whitespace-pre-line">{{ sample }}</p>
              <p class="mt-6 opacity-70 text-sm">— continued on next page —</p>
            </div>
          </div>

          <!-- Pager -->
          <div class="mt-6 flex items-center gap-4 bg-ink-950/50 rounded-full px-4 py-2 border border-ink-800">
            <button class="p-2 rounded-full hover:bg-ink-800 disabled:opacity-40" :disabled="currentPage === 1" @click="prev">
              <ChevronLeftIcon class="h-5 w-5" />
            </button>
            <div class="text-sm">Page {{ currentPage }} / {{ pages.length }}</div>
            <button class="p-2 rounded-full hover:bg-ink-800 disabled:opacity-40" :disabled="currentPage === pages.length" @click="next">
              <ChevronRightIcon class="h-5 w-5" />
            </button>
          </div>

          <div class="mt-6 text-xs text-ink-400 text-center max-w-md">
            You're reading the free 8-page preview. Buy the digital edition to
            unlock the full book.
          </div>
        </div>
      </div>
    </div>
  </template>
</template>
