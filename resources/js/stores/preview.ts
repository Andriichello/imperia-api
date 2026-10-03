import { defineStore } from 'pinia'
import type { Dish } from '@/api'
import { useAppStore } from '@/stores/app'

/**
 * The dishes of the restaurant's pages: all of them at once, from their snapshot (a gzipped JSON
 * file, see `MenuSnapshotRepository`), which the page says where to load from (`dishes_url`, it
 * preloads it). When that fails (e.g. the bucket doesn't let the page read it), the API gives them.
 */
interface PreviewState {
  products: Dish[] | null
  loading: boolean
  error: boolean
}

/** Dishes of the JSON of the address (`{"data": [...]}`). */
async function fetchDishes(url: string): Promise<Dish[]> {
  // like the preload (`crossorigin="anonymous"`), so the preloaded answer is used
  const response = await fetch(url, { credentials: 'same-origin' })

  if (!response.ok) {
    throw new Error(`Dishes weren't loaded: ${response.status}`)
  }

  return (await response.json()).data
}

export const usePreviewStore = defineStore('preview', {
  state: (): PreviewState => ({
    products: null,
    loading: false,
    error: false,
  }),
  actions: {
    /** Load the restaurant's dishes, unless they're loaded already (calling it again after an error retries). */
    async loadDishes(): Promise<void> {
      const app = useAppStore()

      if (!app.restaurant || this.products) {
        return
      }

      // the API, which gives the current snapshot (or builds it)
      const fallback = `/api/restaurants/${app.restaurant.id}/dishes?locale=${encodeURIComponent(app.locale || 'en')}`
      const url = app.dishes_url || fallback

      this.loading = true
      this.error = false

      try {
        this.products = await fetchDishes(url)
          .catch((error) => url === fallback ? Promise.reject(error) : fetchDishes(fallback))
      } catch (e) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
  },
})
