import {Component, createApp} from 'vue';
import {createPinia, Pinia} from 'pinia';
import setupI18n from '@/i18n';
import {setI18n} from '@/i18n/utils';
import adminEn from '@/i18n/admin/en.json';
import adminUk from '@/i18n/admin/uk.json';
import editorEn from '@/i18n/editor/en.json';
import editorUk from '@/i18n/editor/uk.json';
import {interfaceLocale} from '@/editor/translations';

/**
 * The restaurant admin (see `resources/views/admin/app.blade.php`): each page is a chunk of its
 * own, which gets the page's props.
 */
const PAGES: Record<string, () => Promise<{ default: Component }>> = {
  'sign-in': () => import(/* webpackChunkName: "admin-sign-in" */ '@/Pages/Admin/SignInPage.vue'),
  'forgot-password': () => import(/* webpackChunkName: "admin-forgot-password" */ '@/Pages/Admin/ForgotPasswordPage.vue'),
  'reset-password': () => import(/* webpackChunkName: "admin-reset-password" */ '@/Pages/Admin/ResetPasswordPage.vue'),
  dashboard: () => import(/* webpackChunkName: "admin-dashboard" */ '@/Pages/Admin/DashboardPage.vue'),
  editor: () => import(/* webpackChunkName: "admin-editor" */ '@/Pages/EditorPage.vue'),
  version: () => import(/* webpackChunkName: "admin-version" */ '@/Pages/Admin/VersionPage.vue'),
};

/** Pages, which keep their props in their stores. */
const STORES: Record<string, () => Promise<(pinia: Pinia, props: Record<string, unknown>) => void>> = {
  editor: async () => {
    const {useEditorStore} = await import(/* webpackChunkName: "admin-editor" */ '@/stores/editor');

    return (pinia, props) => useEditorStore(pinia).hydrate(props);
  },
  version: async () => {
    const {useVersionStore} = await import(/* webpackChunkName: "admin-version" */ '@/stores/version');

    return (pinia, props) => useVersionStore(pinia).hydrate(props);
  },
};

async function mount(element: HTMLElement) {
  const page = element.dataset.page ?? '';
  const props = JSON.parse(element.dataset.props || '{}');

  // The admin's language: the one picked on this browser, or the browser's one
  const locale = interfaceLocale(props.locale || 'en');
  document.documentElement.lang = locale;

  const {default: component} = await PAGES[page]();
  const pinia = createPinia();

  // the editor and the version page keep their props in their stores
  const app = STORES[page] ? createApp(component) : createApp(component, {props: {...props, locale}});
  app.use(pinia);

  // The admin's own texts, on top of the public site's ones (e.g. names of restaurant types)
  const i18n = setupI18n(locale, {en: {...editorEn, ...adminEn}, uk: {...editorUk, ...adminUk}});
  setI18n(i18n.global);
  app.use(i18n);

  if (STORES[page]) {
    (await STORES[page]())(pinia, {...props, locale});
  }

  app.mount(element);
}

const element = document.getElementById('admin');

if (element) {
  mount(element);
}
