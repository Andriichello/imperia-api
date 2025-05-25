import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import VueSplide from '@splidejs/vue-splide';
import WelcomePage from '@/Pages/Welcome.vue'
import RestaurantPage from "@/Pages/Restaurant.vue";
import MenuPage from "@/Pages/Menu.vue";
import setupI18n from '@/i18n';
import { setI18n } from '@/i18n/utils';

// Map your pages
const pages: Record<string, any> = {
  'Welcome': WelcomePage,
  'Restaurant': RestaurantPage,
  'Menu': MenuPage,
  // Add other pages here as needed
}

createInertiaApp({
  resolve: (name: string) => {
    return pages[name];
  },
  setup({ el, App, props, plugin }) {
    // Get the locale from Inertia props
    const locale = props.initialPage.props.locale || 'en';

    // Create the i18n instance
    const i18n = setupI18n(locale as string);

    // Set the global i18n instance for use in non-component files
    setI18n(i18n);

    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(VueSplide)
      // @ts-ignore
      .use(i18n)
      .mount(el)
  },
})
