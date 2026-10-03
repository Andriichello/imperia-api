import type { Composer } from 'vue-i18n';

// Global i18n instance for use in non-component files
let globalI18n: Composer | null = null;

/**
 * Set the global i18n instance
 *
 * @param i18n The global composer of the i18n instance
 */
export function setI18n(i18n: Composer) {
  globalI18n = i18n;
}

/**
 * Translate a key using the global i18n instance
 *
 * @param key The translation key
 * @param params The translation parameters
 * @param plural The number, which picks the plural form ("1 item | {count} items")
 * @returns The translated string
 */
export function t(key: string, params: Record<string, unknown> = {}, plural?: number): string {
  if (globalI18n) {
    return plural === undefined ? globalI18n.t(key, params) : globalI18n.t(key, params, plural);
  }

  // Fallback to the key if i18n is not available
  return key;
}

/**
 * Switch the application language: reloads the page under the new
 * locale prefix ("/en/web/..." -> "/uk/web/...").
 *
 * @param i18n The i18n instance
 * @param locale The locale to switch to
 */
export function switchLanguage(i18n: Composer, locale: string): void {
  // Update the i18n locale immediately
  i18n.locale.value = locale;

  // Replace the very first path segment (the locale)
  const { pathname, search, hash } = window.location;
  window.location.replace(`${pathname.replace(/^\/([^\/]+)/, `/${locale}`)}${search}${hash}`);
}
