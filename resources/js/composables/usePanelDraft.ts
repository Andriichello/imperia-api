import {computed, onBeforeUnmount, Ref, ref, watch} from 'vue'
import axios from 'axios'
import type {EditorRestaurant} from '@/api'
import type {PreviewPatch} from '@/editor/protocol'
import {PanelGuard, useEditorStore} from '@/stores/editor'

/** Validation errors of a request, by field ("notes.0.text.en"). */
export type ValidationErrors = Record<string, string[]>

interface DraftOptions<T> {
  // the saved values, from the restaurant in the store (in the same shape as the draft)
  saved: () => T
  // save the values: one request, which responds with everything of the restaurant
  save: (values: T) => Promise<EditorRestaurant>
  // the values in the preview: the public page's data, in the preview's language
  preview?: (values: T, locale: string) => PreviewPatch
  // the values are valid, they can be saved
  canSave?: () => boolean
  // something isn't done yet, which is lost when the panel is left (e.g. an upload)
  pending?: () => boolean
  // keys of items of lists in the values, by the list's field ("notes"): errors of an item
  // ("notes.2.text.en") are kept by its key ("notes.note-7.text.en"), it may be dragged meanwhile
  lists?: Record<string, (values: T) => string[]>
}

/**
 * A panel's draft: its changes stay in the panel (and the preview shows them) till they're
 * saved. Validation errors of saving are kept by field.
 */
export function usePanelDraft<T>(options: DraftOptions<T>) {
  const editor = useEditorStore()

  const copy = (value: T): T => JSON.parse(JSON.stringify(value))

  const draft = ref(copy(options.saved())) as Ref<T>
  const errors = ref<ValidationErrors>({})
  const saving = ref(false)
  // saving failed for another reason (no connection, a server error)
  const failed = ref(false)

  const dirty = computed(() => JSON.stringify(draft.value) !== JSON.stringify(options.saved())
    || !!options.pending?.())

  watch(dirty, (value) => {
    editor.dirty = value
  }, {immediate: true})

  if (options.preview) {
    watch([draft, () => editor.previewLocale], () => {
      editor.previewPatch = options.preview!(draft.value, editor.previewLocale)
    }, {deep: true, immediate: true})
  }

  function discard() {
    draft.value = copy(options.saved())
    errors.value = {}
    failed.value = false
  }

  /** Errors of items of lists by their keys (in the order the items were sent). */
  function keyed(received: ValidationErrors, values: T): ValidationErrors {
    const lists = Object.entries(options.lists ?? {}).map(([field, keysOf]) => ({field, keys: keysOf(values)}))

    return Object.fromEntries(Object.entries(received).map(([field, messages]) => {
      for (const list of lists) {
        const match = field.match(new RegExp(`^${list.field}\\.(\\d+)(.*)$`))
        const key = match ? list.keys[parseInt(match[1])] : undefined

        if (match && key !== undefined) {
          return [`${list.field}.${key}${match[2]}`, messages]
        }
      }

      return [field, messages]
    }))
  }

  async function save(): Promise<boolean> {
    saving.value = true
    errors.value = {}
    failed.value = false

    const values = copy(draft.value)

    try {
      editor.restaurant = await options.save(values)
      draft.value = copy(options.saved())

      return true
    } catch (e) {
      if (axios.isAxiosError(e) && e.response?.status === 422) {
        errors.value = keyed(e.response.data?.errors ?? {}, values)
      } else {
        failed.value = true
      }

      return false
    } finally {
      saving.value = false
    }
  }

  /** The first error of the field. */
  function error(field: string): string | null {
    return errors.value[field]?.[0] ?? null
  }

  // leaving the panel with changes asks whether to save them or discard them
  const guard: PanelGuard = {
    save,
    discard,
    canSave: () => !options.pending?.() && (options.canSave?.() ?? true),
  }

  editor.guard(guard)

  // the preview shows the saved values again, when the panel is left
  onBeforeUnmount(() => {
    editor.unguard(guard)
    editor.dirty = false

    if (options.preview) {
      editor.previewPatch = options.preview(options.saved(), editor.previewLocale)
    }
  })

  return {draft, dirty, errors, saving, failed, discard, save, error}
}
