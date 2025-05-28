import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

// Global i18n instance for use in non-component files
let globalI18n = null;

/**
 * Set the global i18n instance
 *
 * @param i18n The i18n instance
 */
export function setI18n(i18n) {
  globalI18n = i18n;
}

/**
 * Get the global i18n instance
 *
 * @returns The global i18n instance
 */
export function getI18n() {
  return globalI18n;
}

/**
 * Translate a key using the global i18n instance
 *
 * @param key The translation key
 * @param params The translation parameters
 * @returns The translated string
 */
export function t(key: string, params = {}) {
  if (globalI18n) {
    return globalI18n.global.t(key, params);
  }

  // Fallback to the key if i18n is not available
  return key;
}

/**
 * Switch the application language
 *
 * @param i18n The i18n instance
 * @param locale The locale to switch to
 * @param reload Flag to reload the page
 */
export function switchLanguage(i18n, locale: string, reload: boolean = false): void {
  const { locale: currentLocale } = i18n;

  // Update the i18n locale
  currentLocale.value = locale;

  if (reload) {
    // Get the current URL
    const url = window.location.pathname;

    // Replace the locale in the URL
    const newUrl = url.replace(/^\/([^\/]+)/, `/${locale}`);

    // Redirect to the new URL
    router.visit(newUrl);
  }
}
