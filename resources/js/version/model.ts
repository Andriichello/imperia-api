import type {
  EditorCategory,
  EditorDish,
  EditorMenu,
  EditorNote,
  EditorRestaurant,
  EditorSize,
  EditorTranslations,
  EditorVersion,
  EditorVersionChange,
  Media,
} from '@/api'
import {translated} from '@/editor/translations'

/**
 * A scheduled version over the restaurant: its items (menus, categories, dishes and their
 * sizes, the restaurant page) as a tree, and each field's live value and the one from the
 * version's date. Values are the ones of version changes (see `VersionFields`).
 */

export type TargetType = EditorVersionChange['target_type']

export type NodeKind = 'restaurant' | 'menu' | 'category' | 'dish' | 'size'

/** A field's live value and its value from the version's date. */
export type FieldValues = Record<string, { live: unknown, new: unknown }>

/** An item of a change: a saved one by its id, a new one by its change (or where it's added). */
export interface Target {
  type: TargetType
  id: number | null
  // of a new item
  changeId?: number | null
  parentId?: number | null
}

/** A size of a dish in the version: a saved one, a new one, or one of a new dish's sizes. */
export interface SizeNode {
  key: string
  target: Target | null
  // of a new dish (its sizes are a field of it)
  index: number | null
  live: Record<string, unknown> | null
  values: Record<string, unknown>
  change: EditorVersionChange | null
}

export interface TreeNode {
  // "menu:3", "dish:12", "dish:c45" (a new one by its change), "size:7", "restaurant"
  key: string
  kind: NodeKind
  name: string
  // a size opens its dish
  opens: string
  target: Target | null
  item: EditorMenu | EditorCategory | EditorDish | EditorSize | null
  change: EditorVersionChange | null
  isNew: boolean
  // the version hides or archives it
  hides: boolean
  archives: boolean
  // archived now
  archived: boolean
  // changes inside, its own ones too
  count: number
  children: TreeNode[]
  // names of the ones it's in
  path: string[]
  size?: SizeNode
}

export const fieldsOf = (change: EditorVersionChange | null | undefined): FieldValues =>
  (change?.fields ?? {}) as FieldValues

/** The value from the version's date: the changed one, or the live one. */
export function valueOf<T>(change: EditorVersionChange | null | undefined, field: string, live: T): T {
  const fields = fieldsOf(change)

  return field in fields ? fields[field].new as T : live
}

export function changeOf(version: EditorVersion, type: TargetType, id: number | null): EditorVersionChange | null {
  return id === null ? null : version.changes.find((change) => change.target_type === type && change.target_id === id) ?? null
}

/** Changes of new items added to the parent. */
export function newChanges(version: EditorVersion, type: TargetType, parentId: number): EditorVersionChange[] {
  return version.changes.filter((change) => change.target_type === type && change.is_new && change.parent_id === parentId)
}

export function changeById(version: EditorVersion, id: number | null | undefined): EditorVersionChange | null {
  return version.changes.find((change) => change.id === id) ?? null
}

/** Texts of every language (none for the missing ones). */
export const texts = (value: EditorTranslations | null | undefined, locales: string[]): Record<string, string | null> =>
  Object.fromEntries(locales.map((locale) => [locale, (value as Record<string, string | null> | null)?.[locale] ?? null]))

const media = (photos: Media[] | undefined) => (photos ?? []).map((photo) => ({id: photo.id, is_hidden: !!photo.is_hidden}))

/** Live values of an item, as a version has them. */
export function liveOf(type: TargetType, item: unknown, restaurant: EditorRestaurant): Record<string, unknown> {
  const locales = restaurant.supported_locales

  switch (type) {
    case 'dish-menus':
    case 'dish-categories': {
      const value = item as EditorMenu | EditorCategory

      return {
        title: texts(value.title, locales),
        description: texts(value.description, locales),
        is_hidden: value.is_hidden,
        archived: value.archived,
      }
    }
    case 'dishes': {
      const value = item as EditorDish

      return {
        title: texts(value.title, locales),
        description: texts(value.description, locales),
        badge: texts(value.badge, locales),
        is_hidden: value.is_hidden,
        archived: value.archived,
        flags: [...value.flags].sort(),
        media: media(value.photos),
      }
    }
    case 'dish-variants': {
      const value = item as EditorSize

      return {
        price: value.price,
        weight: value.weight === null ? null : String(Number(value.weight)),
        weight_unit: value.weight === null ? null : value.weight_unit,
        calories: value.calories,
        preparation_time: value.preparation_time,
        is_hidden: value.is_hidden,
        archived: !!value.archived_at,
      }
    }
    case 'restaurant-notes': {
      const value = item as EditorNote

      return {text: texts(value.text, locales), is_hidden: value.is_hidden}
    }
    default:
      return {
        name: texts(restaurant.name, locales),
        address: texts(restaurant.address, locales),
        establishment: restaurant.establishment,
        phone: restaurant.phone,
        brand_primary: restaurant.brand_primary,
        brand_primary_content: restaurant.brand_primary_content,
        brand_accent: restaurant.brand_accent,
        media: media(restaurant.photos),
      }
  }
}

/** Values from the version's date: live ones with the changed ones. */
export function valuesOf(live: Record<string, unknown> | null, change: EditorVersionChange | null): Record<string, unknown> {
  const values = {...(live ?? {})}

  for (const [field, value] of Object.entries(fieldsOf(change))) {
    values[field] = value.new
  }

  return values
}

/** Sizes of the dish in the version, from the cheapest one. */
export function sizesOf(dish: EditorDish | null, change: EditorVersionChange | null, version: EditorVersion,
  restaurant: EditorRestaurant, dishId: number | null): SizeNode[] {
  const sizes: SizeNode[] = []

  if (change?.is_new) {
    // a new dish has them as a field
    ((fieldsOf(change).sizes?.new ?? []) as Record<string, unknown>[]).forEach((values, index) => sizes.push({
      key: `size:c${change.id}.${index}`,
      target: null,
      index,
      live: null,
      values,
      change: null,
    }))
  } else if (dish && dishId !== null) {
    // archived ones only, when the version restores them
    const saved = [...dish.sizes, ...dish.archived_sizes.filter((size) => changeOf(version, 'dish-variants', size.id))]

    for (const size of saved) {
      const sizeChange = changeOf(version, 'dish-variants', size.id)
      const live = liveOf('dish-variants', size, restaurant)

      sizes.push({
        key: `size:${size.id}`,
        target: {type: 'dish-variants', id: size.id},
        index: null,
        live,
        values: valuesOf(live, sizeChange),
        change: sizeChange,
      })
    }

    for (const sizeChange of newChanges(version, 'dish-variants', dishId)) {
      sizes.push({
        key: `size:c${sizeChange.id}`,
        target: {type: 'dish-variants', id: null, changeId: sizeChange.id},
        index: null,
        live: null,
        values: valuesOf({}, sizeChange),
        change: sizeChange,
      })
    }
  }

  const price = (size: SizeNode) => size.values.price === null || size.values.price === undefined
    ? Infinity
    : Number(size.values.price)

  return sizes.sort((a, b) => price(a) - price(b))
}

interface Context {
  restaurant: EditorRestaurant
  version: EditorVersion
  // names in the restaurant's default language, and of new items
  name: (title: EditorTranslations | null | undefined) => string
  restaurantName: string
}

const countOf = (change: EditorVersionChange | null) => change?.changes_count ?? 0

function node(partial: Partial<TreeNode> & Pick<TreeNode, 'key' | 'kind' | 'name'>): TreeNode {
  const fields = fieldsOf(partial.change)

  return {
    opens: partial.key,
    target: null,
    item: null,
    change: null,
    isNew: !!partial.change?.is_new,
    hides: fields.is_hidden?.new === true,
    archives: fields.archived?.new === true,
    archived: false,
    count: 0,
    children: [],
    path: [],
    ...partial,
  }
}

function sizeNodes(dishNode: TreeNode, sizes: SizeNode[], path: string[]): TreeNode[] {
  return sizes.map((size) => node({
    key: size.key,
    kind: 'size',
    name: '',
    opens: dishNode.key,
    target: size.target,
    change: size.change,
    count: countOf(size.change),
    path,
    size,
  }))
}

function dishNode(dish: EditorDish | null, change: EditorVersionChange | null, ctx: Context, path: string[]): TreeNode {
  const key = dish ? `dish:${dish.id}` : `dish:c${change!.id}`
  const title = valueOf(change, 'title', dish?.title ?? null) as EditorTranslations
  const result = node({
    key,
    kind: 'dish',
    name: ctx.name(title),
    target: dish ? {type: 'dishes', id: dish.id} : {type: 'dishes', id: null, changeId: change!.id, parentId: change!.parent_id},
    item: dish,
    change,
    archived: !!dish?.archived,
    path,
  })

  result.children = sizeNodes(result, sizesOf(dish, change, ctx.version, ctx.restaurant, dish?.id ?? null), [...path, result.name])
  result.count = countOf(change) + (change?.is_new ? 0 : result.children.reduce((sum, child) => sum + child.count, 0))

  return result
}

function categoryNode(category: EditorCategory, ctx: Context, path: string[]): TreeNode {
  const change = changeOf(ctx.version, 'dish-categories', category.id)
  const result = node({
    key: `category:${category.id}`,
    kind: 'category',
    name: ctx.name(valueOf(change, 'title', category.title)),
    target: {type: 'dish-categories', id: category.id},
    item: category,
    change,
    archived: category.archived,
    path,
  })
  const inside = [...path, result.name]

  // archived dishes, when the version changes them
  const dishes = (category.dishes ?? [])
    .filter((dish) => !dish.archived || changeOf(ctx.version, 'dishes', dish.id))
    .map((dish) => dishNode(dish, changeOf(ctx.version, 'dishes', dish.id), ctx, inside))

  const added = newChanges(ctx.version, 'dishes', category.id).map((item) => dishNode(null, item, ctx, inside))

  result.children = [...dishes, ...added]
  result.count = countOf(change) + result.children.reduce((sum, child) => sum + child.count, 0)

  return result
}

function menuNode(menu: EditorMenu, ctx: Context): TreeNode {
  const change = changeOf(ctx.version, 'dish-menus', menu.id)
  const result = node({
    key: `menu:${menu.id}`,
    kind: 'menu',
    name: ctx.name(valueOf(change, 'title', menu.title)),
    target: {type: 'dish-menus', id: menu.id},
    item: menu,
    change,
    archived: menu.archived,
  })

  result.children = (menu.categories ?? [])
    .filter((category) => !category.archived || changeOf(ctx.version, 'dish-categories', category.id))
    .map((category) => categoryNode(category, ctx, [result.name]))
  result.count = countOf(change) + result.children.reduce((sum, child) => sum + child.count, 0)

  return result
}

/**
 * The tree of the version: the restaurant page, then the menus with their categories, dishes
 * and sizes (archived ones, when the version changes them; new ones added by it).
 */
export function treeOf(restaurant: EditorRestaurant, version: EditorVersion, restaurantName: string): TreeNode[] {
  const fallback = restaurant.default_locale
  const ctx: Context = {
    restaurant,
    version,
    name: (title) => translated(title, fallback),
    restaurantName,
  }

  const page = node({
    key: 'restaurant',
    kind: 'restaurant',
    name: restaurantName,
    target: {type: 'restaurants', id: restaurant.id},
    change: changeOf(version, 'restaurants', restaurant.id),
  })

  page.count = version.changes
    .filter((change) => change.target_type === 'restaurants' || change.target_type === 'restaurant-notes')
    .reduce((sum, change) => sum + change.changes_count, 0)

  const menus = restaurant.menus
    .filter((menu) => !menu.archived || changeOf(version, 'dish-menus', menu.id))
    .map((menu) => menuNode(menu, ctx))

  return [page, ...menus]
}

/** Every node of the tree by its key. */
export function nodesOf(tree: TreeNode[]): Map<string, TreeNode> {
  const nodes = new Map<string, TreeNode>()
  const add = (items: TreeNode[]) => items.forEach((item) => {
    nodes.set(item.key, item)
    add(item.children)
  })

  add(tree)

  return nodes
}

/** The key of a target's node: "dish:12", "dish:c45". */
export function targetKey(target: Target): string {
  const kind = {
    'dish-menus': 'menu',
    'dish-categories': 'category',
    dishes: 'dish',
    'dish-variants': 'size',
    restaurants: 'restaurant',
    'restaurant-notes': 'note',
  }[target.type]

  return target.id !== null ? `${kind}:${target.id}` : `${kind}:c${target.changeId ?? 'new'}`
}
