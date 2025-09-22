import {createApp} from 'vue';
import {createPinia} from 'pinia';
import VueSplide from '@splidejs/vue-splide';
import setupI18n from '@/i18n';
import {setI18n, setRouter as setI18nRouter} from '@/i18n/utils';
import {createWebRouter} from '@/router';
import {useAppStore} from '@/stores/app';
import App from "@/App.vue";

const element = document.getElementById('app');
const props = element ? JSON.parse(element.dataset.props || '{}') : {};

// Create app
const app = createApp(App);
app.use(VueSplide);
// Create Pinia
const pinia = createPinia();
app.use(pinia);

// Hydrate the store with Laravel props
const appStore = useAppStore(pinia);
appStore.hydrate(props);

// Setup i18n
const i18n = setupI18n(props.locale || 'en');
setI18n(i18n);
// @ts-ignore
app.use(i18n);

// Create router
const router = createWebRouter();
app.use(router);
setI18nRouter(router);

app.mount('#app');
