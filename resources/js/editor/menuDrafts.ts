import type {
  Dish,
  DishCategory,
  DishMenu,
  DishVariant,
  EditorCategory,
  EditorDish,
  EditorMenu,
  EditorSize,
  EditorStoreDishRequest,
  EditorTranslations,
  Media,
} from '@/api'
import type {PreviewPatch} from '@/editor/protocol'
import {isNewId} from '@/editor/sections'
import {newKey} from '@/editor/lists'
import {translated, Translations, translationsOf} from '@/editor/translations'

/**
 * What the menu panels edit (drafts), what they send to save it, and how the preview shows
 * it: the menus and dishes guests see, in the preview's language.
 */

type Hideable = { is_hidden: boolean, archived: boolean }

/** Guests see it: it's neither hidden nor archived. */
export const isShown = (item: Hideable) => !item.is_hidden && !item.archived

/** Not archived: it's in the lists. */
export const isListed = (item: { archived: boolean }) => !item.archived

function text(value: EditorTranslations | Translations | null | undefined, locale: string, fallback: string): string | null {
  return translated(value as EditorTranslations, locale, fallback) || null
}

/** Texts to save: empty ones aren't written. */
function texts(value: Translations): Record<string, string | null> {
  return Object.fromEntries(Object.entries(value).map(([locale, item]) => [locale, item.trim() || null]))
}

/** A number of a field, null when it's empty. */
function numberOf(value: string): number | null {
  return value.trim() === '' ? null : Number(value.replace(',', '.'))
}

// Preview

function publicDish(dish: EditorDish, menuId: number, categoryId: number, locale: string, fallback: string): Dish {
  // guests see the sizes and photos, which aren't hidden; the dish shows its cheapest size
  const sizes = dish.sizes.filter((size) => !size.is_hidden).sort((a, b) => a.price - b.price)
  const [first] = sizes

  return {
    id: dish.id,
    menu_id: menuId,
    category_id: categoryId,
    slug: dish.slug,
    title: text(dish.title, locale, fallback) ?? '',
    description: text(dish.description, locale, fallback),
    badge: text(dish.badge, locale, fallback),
    price: first?.price ?? 0,
    weight: first?.weight ?? null,
    weight_unit: first?.weight_unit ?? null,
    calories: first?.calories ?? null,
    preparation_time: first?.preparation_time ?? null,
    archived: false,
    popularity: dish.popularity,
    flags: dish.flags,
    variants: sizes.map((size) => ({
      id: size.id,
      dish_id: dish.id,
      price: size.price,
      weight: size.weight,
      weight_unit: size.weight_unit,
      calories: size.calories,
      preparation_time: size.preparation_time,
      archived: false,
    }) as DishVariant),
    media: (dish.photos ?? []).filter((photo) => !photo.is_hidden),
  } as Dish
}

/**
 * The menus guests see with their categories, and the dishes of those categories.
 */
export function menusPreview(menus: EditorMenu[], locale: string, fallback: string): PreviewPatch {
  const shown = menus.filter(isShown)

  return {
    menus: shown.map((menu) => ({
      id: menu.id,
      restaurant_id: menu.restaurant_id,
      slug: menu.slug,
      title: text(menu.title, locale, fallback) ?? '',
      description: text(menu.description, locale, fallback),
      archived: false,
      popularity: menu.popularity,
      categories: (menu.categories ?? []).filter(isShown).map((category) => ({
        id: category.id,
        menu_id: menu.id,
        slug: category.slug,
        title: text(category.title, locale, fallback) ?? '',
        description: text(category.description, locale, fallback),
        archived: false,
        popularity: category.popularity,
      }) as DishCategory),
    }) as DishMenu),
    products: shown.flatMap((menu) => (menu.categories ?? [])
      .filter(isShown)
      .flatMap((category) => (category.dishes ?? [])
        .filter(isShown)
        .map((dish) => publicDish(dish, menu.id, category.id, locale, fallback)))),
  }
}

// Menus: their order, hidden ones, and categories moved between them

export interface MenuOrder {
  id: number
  is_hidden: boolean
  categories: number[]
}

export function menuOrderOf(menus: EditorMenu[]): MenuOrder[] {
  return menus.filter(isListed).map((menu) => ({
    id: menu.id,
    is_hidden: menu.is_hidden,
    categories: (menu.categories ?? []).filter(isListed).map((category) => category.id),
  }))
}

/** The menus in the order, with their categories (moved ones too). */
export function applyMenuOrder(menus: EditorMenu[], order: MenuOrder[]): EditorMenu[] {
  const byId = new Map(menus.map((menu) => [menu.id, menu]))
  const categories = new Map(menus.flatMap((menu) => menu.categories ?? []).map((category) => [category.id, category]))

  return order
    .filter((item) => byId.has(item.id))
    .map((item) => ({
      ...byId.get(item.id)!,
      is_hidden: item.is_hidden,
      categories: item.categories
        .map((id) => categories.get(id))
        .filter((category): category is EditorCategory => !!category)
        .map((category) => ({...category, menu_id: item.id})),
    }))
}

// A menu or a category: its texts

export interface TextsDraft {
  title: Translations
  description: Translations
  is_hidden: boolean
}

export function textsOf(item: EditorMenu | EditorCategory | null, locales: string[]): TextsDraft {
  return {
    title: translationsOf(item?.title, locales),
    description: translationsOf(item?.description, locales),
    is_hidden: item?.is_hidden ?? false,
  }
}

export function textsRequest(draft: TextsDraft) {
  return {
    title: texts(draft.title),
    description: texts(draft.description),
    is_hidden: draft.is_hidden,
  }
}

/** The menus with the menu's texts (a new one at the end). */
export function applyMenu(menus: EditorMenu[], id: number, draft: TextsDraft, restaurantId: number): EditorMenu[] {
  const values = {title: draft.title, description: draft.description, is_hidden: draft.is_hidden}

  if (isNewId(id)) {
    return [...menus, {
      id,
      restaurant_id: restaurantId,
      slug: null,
      archived: false,
      archived_at: null,
      popularity: null,
      categories: [],
      ...values,
    }]
  }

  return menus.map((menu) => menu.id === id ? {...menu, ...values} : menu)
}

// A category: its texts and the order of its dishes

export interface CategoryDraft extends TextsDraft {
  dishes: number[]
}

export function categoryOf(category: EditorCategory | null, locales: string[]): CategoryDraft {
  return {
    ...textsOf(category, locales),
    dishes: (category?.dishes ?? []).filter(isListed).map((dish) => dish.id),
  }
}

/** The menus with the category's texts and its dishes in their order (a new one in its menu). */
export function applyCategory(menus: EditorMenu[], id: number, menuId: number, draft: CategoryDraft): EditorMenu[] {
  const values = {title: draft.title, description: draft.description, is_hidden: draft.is_hidden}

  return menus.map((menu) => {
    if (isNewId(id)) {
      return menu.id === menuId
        ? {
          ...menu,
          categories: [...(menu.categories ?? []), {
            id,
            menu_id: menuId,
            slug: null,
            archived: false,
            archived_at: null,
            popularity: null,
            dishes: [],
            ...values,
          }],
        }
        : menu
    }

    return {
      ...menu,
      categories: (menu.categories ?? []).map((category) => {
        if (category.id !== id) {
          return category
        }

        const dishes = new Map((category.dishes ?? []).map((dish) => [dish.id, dish]))
        const ordered = draft.dishes.map((dishId) => dishes.get(dishId)).filter((dish): dish is EditorDish => !!dish)

        return {...category, ...values, dishes: [...ordered, ...(category.dishes ?? []).filter((dish) => dish.archived)]}
      }),
    }
  })
}

// A dish

export interface SizeDraft {
  // of the list (ids of new sizes are unknown)
  key: string
  // of its variant, null for new ones
  id: number | null
  // guests don't see it (a dish has a size they see)
  is_hidden: boolean
  weight: string
  weight_unit: string
  price: string
  calories: string
  preparation_time: string
}

export interface DishDraft {
  title: Translations
  description: Translations
  badge: Translations
  is_hidden: boolean
  flags: string[]
  sizes: SizeDraft[]
  // the same preparation time and calories for every size
  shared: boolean
  preparation_time: string
  calories: string
  photos: Media[]
}

const field = (value: string | number | null | undefined) => value === null || value === undefined ? '' : String(value)

/**
 * A size to edit: a saved one, or a new one.
 *
 * @param size
 * @param key Of a new one (the first size of a new dish has the same one every time, so that
 *            the new dish's draft equals its empty values till something is typed)
 */
export function sizeOf(size: Partial<EditorSize> = {}, key: string | null = null): SizeDraft {
  return {
    key: size.id ? `size-${size.id}` : (key ?? newKey()),
    id: size.id ?? null,
    is_hidden: size.is_hidden ?? false,
    weight: field(size.weight),
    weight_unit: size.weight_unit ?? 'g',
    price: field(size.price),
    calories: field(size.calories),
    preparation_time: field(size.preparation_time),
  }
}

export function dishOf(dish: EditorDish | null, locales: string[]): DishDraft {
  const sizes = dish?.sizes?.length ? dish.sizes : [{} as EditorSize]
  const first = sizes[0]
  const shared = sizes.every((size) => size.calories === first.calories
    && size.preparation_time === first.preparation_time)

  return {
    title: translationsOf(dish?.title, locales),
    description: translationsOf(dish?.description, locales),
    badge: translationsOf(dish?.badge, locales),
    is_hidden: dish?.is_hidden ?? false,
    flags: [...(dish?.flags ?? [])],
    sizes: sizes.map((size) => sizeOf(size, 'new-first')),
    shared,
    preparation_time: field(first.preparation_time),
    calories: field(first.calories),
    photos: dish?.photos ?? [],
  }
}

/**
 * Sizes as they're saved: the time and calories of all of them, when they're the same.
 * New ones have negative ids, so that the preview tells them apart.
 */
function sizesOf(draft: DishDraft): EditorSize[] {
  return draft.sizes.map((size, index) => {
    const weight = numberOf(size.weight)

    return {
      id: size.id ?? -(index + 1),
      is_hidden: size.is_hidden,
      archived_at: null,
      price: numberOf(size.price) ?? 0,
      weight: weight === null ? null : String(weight),
      weight_unit: (weight === null ? null : size.weight_unit) as EditorSize['weight_unit'],
      calories: numberOf(draft.shared ? draft.calories : size.calories),
      preparation_time: numberOf(draft.shared ? draft.preparation_time : size.preparation_time),
    }
  })
}

export function dishRequest(draft: DishDraft, isNew: boolean): EditorStoreDishRequest {
  return {
    title: texts(draft.title),
    description: texts(draft.description),
    badge: texts(draft.badge),
    is_hidden: draft.is_hidden,
    flags: draft.flags,
    sizes: sizesOf(draft).map(({id, weight, archived_at, ...size}) => ({
      // new dishes have no sizes yet
      ...(isNew ? {} : {id: id > 0 ? id : null}),
      ...size,
      weight: weight === null ? null : Number(weight),
    })),
    media: draft.photos.map((photo) => ({id: photo.id, is_hidden: photo.is_hidden ?? false})),
  }
}

/** The menus with the dish's values (a new one in its category). */
export function applyDish(menus: EditorMenu[], id: number, categoryId: number, draft: DishDraft): EditorMenu[] {
  const values = {
    title: draft.title,
    description: draft.description,
    badge: draft.badge,
    is_hidden: draft.is_hidden,
    flags: draft.flags,
    sizes: sizesOf(draft),
    photos: draft.photos,
  }

  return menus.map((menu) => ({
    ...menu,
    categories: (menu.categories ?? []).map((category) => {
      if (isNewId(id)) {
        return category.id === categoryId
          ? {
            ...category,
            dishes: [...(category.dishes ?? []), {
              id,
              menu_id: menu.id,
              category_id: categoryId,
              slug: null,
              archived: false,
              archived_at: null,
              popularity: null,
              archived_sizes: [],
              ...values,
            }],
          }
          : category
      }

      return {
        ...category,
        dishes: (category.dishes ?? []).map((dish) => dish.id === id ? {...dish, ...values} : dish),
      }
    }),
  }))
}
