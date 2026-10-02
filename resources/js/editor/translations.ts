import type {EditorTranslations} from '@/api'

/** Names of languages, in themselves (tabs of the content languages, the preview's language). */
const LANGUAGE_NAMES: Record<string, string> = {
  en: 'English',
  uk: 'Українська',
}

export function languageName(locale: string): string {
  return LANGUAGE_NAMES[locale] ?? locale.toUpperCase()
}

/**
 * Text in the language, or in the fallback one, or in any language it's written in.
 */
export function translated(value: EditorTranslations | null | undefined, locale: string, fallback?: string): string {
  const texts = (value ?? {}) as Record<string, string | null | undefined>

  return texts[locale]
    || (fallback ? texts[fallback] : null)
    || Object.values(texts).find((text) => !!text)
    || ''
}
