import {computed, onBeforeUnmount, watch, WritableComputedRef} from 'vue'
import {isNewId, Selection} from '@/editor/sections'
import type {ValidationErrors} from '@/editor/items'
import {useEditorStore} from '@/stores/editor'

/**
 * A panel's draft: the part's changes, kept in the editor's store till they're saved or
 * discarded (the save bar saves them, the preview shows them). Leaving the panel keeps them.
 *
 * @param selection The part the panel edits
 */
export function usePanelDraft<T>(selection: Selection) {
  const editor = useEditorStore()

  const entry = editor.draftOf<T>(selection)

  const draft: WritableComputedRef<T> = computed({
    get: () => entry.values,
    set: (values: T) => {
      entry.values = values
    },
  })

  const dirty = computed(() => editor.isUnsaved(selection))

  // a new item is in the preview, once something of it is typed: the preview shows it
  if (isNewId(selection.id)) {
    const stop = watch(dirty, (value) => {
      if (value) {
        stop()
        editor.select(editor.selection, true, 'none')
      }
    })
  }

  // of the last save
  const errors = computed<ValidationErrors>(() => entry.errors)

  /** The first error of the field. */
  function error(field: string): string | null {
    return entry.errors[field]?.[0] ?? null
  }

  /**
   * Change the values after something was done, which may end after the panel is left (an
   * upload): the change goes to the part's draft, whether it's open or not.
   */
  function change(update: (values: T) => T) {
    const current = editor.draftOf<T>(selection)

    current.values = update(current.values)
  }

  function discard() {
    editor.discard(entry.key)
  }

  // a draft, which changes nothing, isn't kept
  onBeforeUnmount(() => editor.prune())

  return {draft, dirty, errors, error, change, discard}
}
