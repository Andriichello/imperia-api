import {computed, ref, watch} from 'vue'
import type {LanguageTab} from '@/editor/sections'
import {languageName, Translations} from '@/editor/translations'
import {useEditorStore} from '@/stores/editor'

/**
 * The language of the texts a panel edits (its tabs), which the preview follows.
 *
 * @param texts The panel's translatable texts: the tabs count the ones written in the default
 *              language, and the ones of them written in theirs too
 */
export function useContentLocale(texts: () => Translations[] = () => []) {
  const editor = useEditorStore()

  const locale = ref(editor.defaultLocale)

  watch(locale, (value) => {
    editor.previewLocale = value
  })

  const isDefault = computed(() => locale.value === editor.defaultLocale)

  const languages = computed<LanguageTab[]>(() => {
    const fallback = editor.defaultLocale

    // the default language first
    return [...editor.locales]
      .sort((a, b) => Number(b === fallback) - Number(a === fallback))
      .map((code) => {
        const written = texts().filter((text) => !!text[fallback]?.trim())

        return {
          locale: code,
          label: languageName(code),
          isDefault: code === fallback,
          total: written.length,
          filled: written.filter((text) => !!text[code]?.trim()).length,
        }
      })
  })

  /** On other tabs, an empty text shows the default language's one. */
  function placeholder(text: Translations | null | undefined): string {
    return isDefault.value ? '' : (text?.[editor.defaultLocale] ?? '')
  }

  /**
   * The first error of a translated field: in the tab's language, or in another one
   * ("English: The name is required.").
   */
  function textError(error: (field: string) => string | null, field: string): string | null {
    const own = error(`${field}.${locale.value}`)

    if (own) {
      return own
    }

    for (const code of editor.locales) {
      const message = error(`${field}.${code}`)

      if (message) {
        return `${languageName(code)}: ${message}`
      }
    }

    return error(field)
  }

  return {locale, isDefault, languages, placeholder, textError}
}
