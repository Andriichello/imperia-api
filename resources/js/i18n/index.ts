import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import uk from './locales/uk.json';

export default function setupI18n(locale: string) {
  return createI18n({
    legacy: false, // Use Composition API
    globalInjection: true, // Make $t, $d, etc. available in templates
    locale: locale,
    fallbackLocale: 'en',
    messages: {
      en,
      uk
    }
  });
}
