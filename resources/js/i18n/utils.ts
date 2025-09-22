import { Router } from 'vue-router';

// Global i18n instance for use in non-component files
let globalI18n = null;

// Global router instance for use in non-component files
let globalRouter: Router | null = null;

/**
 * Set the global i18n instance
 *
 * @param i18n The i18n instance
 */
export function setI18n(i18n) {
  globalI18n = i18n;
}

/**
 * Set the global router instance
 *
 * @param router The router instance
 */
export function setRouter(router: Router) {
  globalRouter = router;
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
 * Get the global router instance
 *
 * @returns The global router instance
 */
export function getRouter() {
  return globalRouter;
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
 * @param reload Flag to reload the page or navigate with Vue Router
 */
export function switchLanguage(i18n, locale: string, reload: boolean = false): void {
  const { locale: currentLocale } = i18n;

  // Update the i18n locale
  currentLocale.value = locale;

  if (reload && globalRouter) {
    // Get the current route
    const currentRoute = globalRouter.currentRoute.value;

    // Replace the locale in the path
    const currentPath = currentRoute.fullPath;
    const newPath = currentPath.replace(/^\/([^\/]+)/, `/${locale}`);

    // Navigate to the new URL using Vue Router
    globalRouter.replace({
      path: newPath,
      query: currentRoute.query,
      hash: currentRoute.hash
    });
  }
}
