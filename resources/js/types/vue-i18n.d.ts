declare module 'vue-i18n' {
  export interface I18n {
    t: (key: string, params?: Record<string, any>) => string;
    global: {
      t: (key: string, params?: Record<string, any>) => string;
      locale: string;
    };
    locale: {
      value: string;
    };
  }

  export function useI18n(): I18n;
  export function createI18n(options: any): I18n;
}
