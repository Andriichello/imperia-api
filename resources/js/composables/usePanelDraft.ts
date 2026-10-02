import {computed, onBeforeUnmount, Ref, ref, watch} from 'vue'
import axios from 'axios'
import type {EditorRestaurant} from '@/api'
import type {PreviewPatch} from '@/editor/protocol'
import {useEditorStore} from '@/stores/editor'

/** Validation errors of a request, by field ("notes.0.text.en"). */
export type ValidationErrors = Record<string, string[]>

interface DraftOptions<T> {
  // the saved values, from the restaurant in the store (in the same shape as the draft)
  saved: () => T
  // save the values: one request, which responds with everything of the restaurant
  save: (values: T) => Promise<EditorRestaurant>
  // the values in the preview: the public page's data, in the preview's language
  preview?: (values: T, locale: string) => PreviewPatch
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

  const dirty = computed(() => JSON.stringify(draft.value) !== JSON.stringify(options.saved()))

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

  async function save(): Promise<boolean> {
    saving.value = true
    errors.value = {}
    failed.value = false

    try {
      editor.restaurant = await options.save(draft.value)
      draft.value = copy(options.saved())

      return true
    } catch (e) {
      if (axios.isAxiosError(e) && e.response?.status === 422) {
        errors.value = e.response.data?.errors ?? {}
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

  // the preview shows the saved values again, when the panel is left
  onBeforeUnmount(() => {
    editor.dirty = false

    if (options.preview) {
      editor.previewPatch = options.preview(options.saved(), editor.previewLocale)
    }
  })

  return {draft, dirty, errors, saving, failed, discard, save, error}
}
