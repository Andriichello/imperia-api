import { defineStore } from 'pinia'
import type { Dish } from '@/api'
import { indexDishes } from '@/api/services/dish-dishes'

interface PreviewState {
  products: Dish[] | null
  loading: boolean
  lastRestaurantId: number | null
  lastMenuId: number | null
  lastMenuIds: number[] | null
}

function equalNumberArrays(a: number[] | null | undefined, b: number[] | null | undefined): boolean {
  if (!a && !b) {
    return true
  }
  if (!a || !b) {
    return false
  }
  if (a.length !== b.length) {
    return false
  }
  const sa = [...a].sort((x, y) => x - y)
  const sb = [...b].sort((x, y) => x - y)
  return sa.every((v, i) => v === sb[i])
}

export const usePreviewStore = defineStore('preview', {
  state: (): PreviewState => ({
    products: null,
    loading: false,
    lastRestaurantId: null,
    lastMenuId: null,
    lastMenuIds: null,
  }),
  actions: {
    setContext(restaurantId: number | null, menuId: number | null = null, menuIds: number[] | null = null): void {
      this.lastRestaurantId = restaurantId ?? null
      this.lastMenuId = menuId ?? null
      this.lastMenuIds = menuIds ? [...menuIds] : null
    },

    clearProducts(): void {
      this.products = null
    },

    async loadProducts(args?: { restaurantId?: number | null; menuId?: number | null; menuIds?: number[] | null; include?: string; pageSize?: number; sort?: string }): Promise<void> {
      const restaurantId = args?.restaurantId ?? this.lastRestaurantId
      const menuIds = args?.menuIds ?? this.lastMenuIds
      const menuId = args?.menuId ?? this.lastMenuId
      const include = args?.include ?? 'category,variants,media'
      const pageSize = args?.pageSize ?? 500
      const sort = args?.sort ?? '-popularity'

      const hasMenuIds = Array.isArray(menuIds) && menuIds.length > 0

      // If no context provided, do nothing
      if (!restaurantId && !menuId && !hasMenuIds) {
        this.products = null
        return
      }

      // Avoid reloading if we already have products for the same context
      if (
        this.products &&
        (
          (hasMenuIds && equalNumberArrays(menuIds!, this.lastMenuIds)) ||
          (!hasMenuIds && menuId === this.lastMenuId) ||
          (!hasMenuIds && !menuId && restaurantId === this.lastRestaurantId)
        )
      ) {
        return
      }

      this.loading = true
      try {
        const params: Record<string, string | number> = {
          include,
          sort,
          'page[size]': pageSize,
        }

        if (hasMenuIds) {
          params['filter[menu_ids]'] = (menuIds as number[]).join(',')
        } else if (menuId) {
          params['filter[menu_id]'] = String(menuId)
        } else if (restaurantId) {
          params['filter[restaurant_id]'] = String(restaurantId)
        }

        const resp = await indexDishes(params)
        // AxiosResponse<IndexDishResponse>
        this.products = resp.data.data

        // Persist the context we actually used
        this.lastRestaurantId = restaurantId ?? null
        this.lastMenuId = menuId ?? null
        this.lastMenuIds = hasMenuIds ? [...(menuIds as number[])] : null
      } finally {
        this.loading = false
      }
    },
  },
})
