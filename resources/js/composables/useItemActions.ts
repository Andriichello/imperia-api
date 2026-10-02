import {useI18n} from 'vue-i18n'
import type {AxiosResponse} from 'axios'
import type {EditorCategory, EditorMenu} from '@/api'
import {
  archiveEditorCategory,
  archiveEditorDish,
  archiveEditorMenu,
  destroyEditorCategory,
  destroyEditorDish,
  destroyEditorMenu,
  duplicateEditorCategory,
  duplicateEditorDish,
  duplicateEditorMenu,
  moveEditorCategory,
  moveEditorDish,
  unarchiveEditorCategory,
  unarchiveEditorDish,
  unarchiveEditorMenu,
} from '@/api'
import {useEditorStore} from '@/stores/editor'

export type ItemKind = 'menu' | 'category' | 'dish'

type Action = (id: number) => Promise<AxiosResponse<{ data: { id: number } }>>

const API: Record<ItemKind, Record<'archive' | 'unarchive' | 'duplicate' | 'destroy', Action>> = {
  menu: {
    archive: archiveEditorMenu,
    unarchive: unarchiveEditorMenu,
    duplicate: duplicateEditorMenu,
    destroy: destroyEditorMenu as unknown as Action,
  },
  category: {
    archive: archiveEditorCategory,
    unarchive: unarchiveEditorCategory,
    duplicate: duplicateEditorCategory,
    destroy: destroyEditorCategory as unknown as Action,
  },
  dish: {
    archive: archiveEditorDish,
    unarchive: unarchiveEditorDish,
    duplicate: duplicateEditorDish,
    destroy: destroyEditorDish as unknown as Action,
  },
}

/**
 * Actions on menus, categories and dishes, which aren't part of a draft: they're done right
 * away. Archiving and restoring can be undone, deleting is confirmed first.
 */
export function useItemActions() {
  const editor = useEditorStore()
  const {t} = useI18n()

  /** Do it, and load everything again (counts, orders and states change). */
  async function run<T>(action: () => Promise<T>): Promise<T | null> {
    try {
      const result = await action()
      await editor.reload()

      return result
    } catch (e) {
      editor.notify(t('editor.actions.failed'))

      return null
    }
  }

  async function archive(kind: ItemKind, id: number, name: string): Promise<boolean> {
    if (await run(() => API[kind].archive(id)) === null) {
      return false
    }

    editor.notify(t('editor.actions.archived', {name}), {
      label: t('editor.actions.undo'),
      run: () => restore(kind, id, name, false),
    })

    return true
  }

  async function restore(kind: ItemKind, id: number, name: string, withUndo: boolean = true): Promise<boolean> {
    if (await run(() => API[kind].unarchive(id)) === null) {
      return false
    }

    editor.notify(t('editor.actions.restored', {name}), withUndo
      ? {label: t('editor.actions.undo'), run: () => archive(kind, id, name)}
      : undefined)

    return true
  }

  /** The copy is hidden, till it's shown. Its id. */
  async function duplicate(kind: ItemKind, id: number, name: string): Promise<number | null> {
    const response = await run(() => API[kind].duplicate(id))

    if (response) {
      editor.notify(t('editor.actions.duplicated', {name}))
    }

    return response?.data.data.id ?? null
  }

  /**
   * Delete it for good, once it's confirmed.
   *
   * @param contents What's deleted with it ("2 categories, 7 dishes")
   */
  async function destroy(kind: ItemKind, id: number, name: string, contents: string | null = null): Promise<boolean> {
    const confirmed = await editor.confirm({
      title: t('editor.actions.delete_title', {name}),
      message: contents
        ? t('editor.actions.delete_with', {contents})
        : t('editor.actions.delete_message'),
      confirm: t('editor.actions.delete'),
      danger: true,
    })

    if (!confirmed || await run(() => API[kind].destroy(id)) === null) {
      return false
    }

    editor.notify(t('editor.actions.deleted', {name}))

    return true
  }

  async function moveCategory(id: number, menuId: number, name: string, menu: string): Promise<boolean> {
    const moved = await run(() => moveEditorCategory(id, {menu_id: menuId})) !== null

    if (moved) {
      editor.notify(t('editor.actions.moved', {name, to: menu}))
    }

    return moved
  }

  async function moveDish(id: number, categoryId: number, name: string, category: string): Promise<boolean> {
    const moved = await run(() => moveEditorDish(id, {category_id: categoryId})) !== null

    if (moved) {
      editor.notify(t('editor.actions.moved', {name, to: category}))
    }

    return moved
  }

  /** "2 categories, 7 dishes" of a menu, which go with it (archived ones too), null when it's empty. */
  function menuContents(menu: EditorMenu): string | null {
    const categories = menu.categories ?? []
    const dishes = categories.reduce((count, category) => count + (category.dishes?.length ?? 0), 0)
    const parts = [
      categories.length ? t('editor.structure.categories_count', categories.length) : null,
      dishes ? t('editor.structure.dishes_count', dishes) : null,
    ].filter(Boolean)

    return parts.length ? parts.join(', ') : null
  }

  function categoryContents(category: EditorCategory): string | null {
    const dishes = category.dishes?.length ?? 0

    return dishes ? t('editor.structure.dishes_count', dishes) : null
  }

  return {archive, restore, duplicate, destroy, moveCategory, moveDish, menuContents, categoryContents}
}
