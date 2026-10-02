import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import uk from './locales/uk.json';

/**
 * Ukrainian plurals with three forms: "1 страва | 2 страви | 5 страв".
 * Messages with two forms are split as in English.
 */
function ukrainianPlural(choice: number, choicesLength: number): number {
  const n = Math.abs(choice);

  if (choicesLength < 3) {
    return n === 1 ? 0 : 1;
  }

  if (n % 10 === 1 && n % 100 !== 11) {
    return 0;
  }

  if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 12 || n % 100 > 14)) {
    return 1;
  }

  return 2;
}

/**
 * @param locale
 * @param extra Messages of another app on top of the public site's ones (e.g. the editor's), by locale
 */
export default function setupI18n(locale: string, extra: Record<string, object> = {}) {
  return createI18n({
    legacy: false, // Use Composition API
    globalInjection: true, // Make $t, $d, etc. available in templates
    locale: locale,
    fallbackLocale: 'en',
    pluralRules: {
      uk: ukrainianPlural,
    },
    messages: {
      en: {...en, ...extra.en},
      uk: {...uk, ...extra.uk},
    }
  });
}
