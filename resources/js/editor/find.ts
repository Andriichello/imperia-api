import type {EditorCategory, EditorDish, EditorMenu, EditorRestaurant} from '@/api'

/**
 * Menus, categories and dishes of the restaurant by their ids (archived ones too).
 */

export function findMenu(restaurant: EditorRestaurant | null, id: number | null | undefined): EditorMenu | null {
  return (restaurant?.menus ?? []).find((menu) => menu.id === id) ?? null
}

export function findCategory(restaurant: EditorRestaurant | null, id: number | null | undefined): EditorCategory | null {
  for (const menu of restaurant?.menus ?? []) {
    const category = (menu.categories ?? []).find((item) => item.id === id)

    if (category) {
      return category
    }
  }

  return null
}

export function findDish(restaurant: EditorRestaurant | null, id: number | null | undefined): EditorDish | null {
  for (const menu of restaurant?.menus ?? []) {
    for (const category of menu.categories ?? []) {
      const dish = (category.dishes ?? []).find((item) => item.id === id)

      if (dish) {
        return dish
      }
    }
  }

  return null
}
