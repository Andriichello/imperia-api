import type {EditorTranslations} from '@/api'

/** A text in each language: what's edited (`EditorTranslations` with empty strings for missing ones). */
export type Translations = Record<string, string>

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

/**
 * The text in each of the languages, empty when it isn't written (for fields to edit).
 */
export function translationsOf(value: EditorTranslations | null | undefined, locales: string[]): Translations {
  const texts = (value ?? {}) as Record<string, string | null | undefined>

  return Object.fromEntries(locales.map((locale) => [locale, texts[locale] ?? '']))
}
