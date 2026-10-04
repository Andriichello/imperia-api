import type {EditorDish, EditorRestaurant, EditorSize, EditorTranslations, EditorVersion, Media} from '@/api'
import {detailsOf, detailsPreview, notesOf, notesPreview, photosPreview} from '@/editor/drafts'
import {menusPreview} from '@/editor/menuDrafts'
import type {PreviewBrand, PreviewPatch} from '@/editor/protocol'
import {brandOf} from '@/editor/brand'
import {copy} from '@/editor/items'
import {fieldsOf} from '@/version/model'

/**
 * The restaurant as it will be at the version's date, for its preview: the public page's data
 * with the version's changes.
 */

type Values = Record<string, unknown>

const newValues = (fields: Record<string, { new: unknown }>): Values =>
  Object.fromEntries(Object.entries(fields).map(([field, value]) => [field, value.new]))

/** Photos of the ids, as the version has them. */
function photosOf(items: unknown, photos: Record<number, Media>): Media[] {
  return ((items ?? []) as { id: number, is_hidden: boolean }[])
    .filter((item) => photos[item.id])
    .map((item) => ({...photos[item.id], is_hidden: item.is_hidden}))
}

function applySize(size: EditorSize, values: Values): EditorSize {
  return {
    ...size,
    ...('price' in values ? {price: Number(values.price)} : {}),
    ...('weight' in values ? {weight: values.weight === null ? null : String(values.weight)} : {}),
    ...('weight_unit' in values ? {weight_unit: values.weight_unit as EditorSize['weight_unit']} : {}),
    ...('calories' in values ? {calories: values.calories as number | null} : {}),
    ...('preparation_time' in values ? {preparation_time: values.preparation_time as number | null} : {}),
    ...('is_hidden' in values ? {is_hidden: !!values.is_hidden} : {}),
  }
}

function newSize(id: number, values: Values): EditorSize {
  return applySize({
    id, price: 0, weight: null, weight_unit: null, calories: null, preparation_time: null, is_hidden: false, archived_at: null,
  } as EditorSize, values)
}

export function versionRestaurant(restaurant: EditorRestaurant, version: EditorVersion, photos: Record<number, Media>): EditorRestaurant {
  const after = copy(restaurant)

  for (const change of version.changes) {
    const values = newValues(fieldsOf(change))
    const texts = (field: string, current: EditorTranslations) =>
      field in values ? values[field] as EditorTranslations : current

    switch (change.target_type) {
      case 'restaurants':
        Object.assign(after, {
          name: texts('name', after.name),
          address: texts('address', after.address),
          ...('establishment' in values ? {establishment: values.establishment} : {}),
          ...('phone' in values ? {phone: values.phone} : {}),
          ...('brand_primary' in values ? {brand_primary: values.brand_primary} : {}),
          ...('brand_primary_content' in values ? {brand_primary_content: values.brand_primary_content} : {}),
          ...('brand_accent' in values ? {brand_accent: values.brand_accent} : {}),
          ...('media' in values ? {photos: photosOf(values.media, photos)} : {}),
        })
        break
      case 'restaurant-notes':
        if (change.is_new) {
          after.notes.push({id: -change.id, text: values.text as EditorTranslations, is_hidden: !!values.is_hidden, order: 999})
        } else {
          after.notes = after.notes.map((note) => note.id === change.target_id
            ? {...note, text: texts('text', note.text), ...('is_hidden' in values ? {is_hidden: !!values.is_hidden} : {})}
            : note)
        }
        break
      case 'dish-menus':
      case 'dish-categories': {
        const items = change.target_type === 'dish-menus'
          ? after.menus
          : after.menus.flatMap((menu) => menu.categories ?? [])
        const item = items.find((other) => other.id === change.target_id)

        if (item) {
          Object.assign(item, {
            title: texts('title', item.title),
            description: texts('description', item.description),
            ...('is_hidden' in values ? {is_hidden: !!values.is_hidden} : {}),
            ...('archived' in values ? {archived: !!values.archived} : {}),
          })
        }
        break
      }
      default:
        break
    }
  }

  const dishes = after.menus.flatMap((menu) => (menu.categories ?? []).flatMap((category) => category.dishes ?? []))

  for (const change of version.changes.filter((item) => item.target_type === 'dishes')) {
    const values = newValues(fieldsOf(change))

    if (change.is_new) {
      const category = after.menus.flatMap((menu) => menu.categories ?? []).find((item) => item.id === change.parent_id)

      category?.dishes?.push({
        id: -change.id,
        menu_id: category.menu_id,
        category_id: category.id,
        slug: null,
        title: values.title as EditorTranslations,
        description: values.description as EditorTranslations,
        badge: values.badge as EditorTranslations,
        is_hidden: !!values.is_hidden,
        archived: false,
        archived_at: null,
        popularity: null,
        flags: (values.flags ?? []) as string[],
        sizes: ((values.sizes ?? []) as Values[]).map((size, index) => newSize(-(change.id * 100 + index + 1), size)),
        archived_sizes: [],
        photos: photosOf(values.media, photos),
      } as EditorDish)
      continue
    }

    const dish = dishes.find((item) => item.id === change.target_id)

    if (dish) {
      Object.assign(dish, {
        ...Object.fromEntries(['title', 'description', 'badge'].filter((field) => field in values).map((field) => [field, values[field]])),
        ...('is_hidden' in values ? {is_hidden: !!values.is_hidden} : {}),
        ...('archived' in values ? {archived: !!values.archived} : {}),
        ...('flags' in values ? {flags: values.flags} : {}),
        ...('media' in values ? {photos: photosOf(values.media, photos)} : {}),
      })
    }
  }

  for (const change of version.changes.filter((item) => item.target_type === 'dish-variants')) {
    const values = newValues(fieldsOf(change))

    if (change.is_new) {
      dishes.find((dish) => dish.id === change.parent_id)?.sizes.push(newSize(-change.id, values))
      continue
    }

    for (const dish of dishes) {
      const size = [...dish.sizes, ...dish.archived_sizes].find((item) => item.id === change.target_id)

      if (!size) {
        continue
      }

      const updated = applySize(size, values)
      const archived = 'archived' in values ? !!values.archived : !!size.archived_at

      dish.sizes = dish.sizes.filter((item) => item.id !== size.id)
      dish.archived_sizes = dish.archived_sizes.filter((item) => item.id !== size.id)

      if (archived) {
        dish.archived_sizes.push(updated)
      } else {
        dish.sizes.push(updated)
      }
    }
  }

  return after
}

/** The public page's data at the version's date (in the language), and its brand colors. */
export function versionPreview(restaurant: EditorRestaurant, version: EditorVersion, photos: Record<number, Media>,
  locale: string): { patch: PreviewPatch, brand: PreviewBrand } {
  const after = versionRestaurant(restaurant, version, photos)
  const fallback = after.default_locale

  return {
    patch: {
      restaurant: {
        ...detailsPreview(detailsOf(after), locale, fallback).restaurant,
        ...notesPreview(notesOf(after), locale, fallback).restaurant,
        ...photosPreview(after.photos ?? []).restaurant,
      },
      ...menusPreview(after.menus, locale, fallback),
    },
    brand: brandOf(after),
  }
}
