import {createApp} from 'vue';
import {createPinia} from 'pinia';
import VueSplide from '@splidejs/vue-splide';
import setupI18n from '@/i18n';
import {setI18n} from '@/i18n/utils';
import {createWebRouter} from '@/router';
import {useAppStore} from '@/stores/app';
import {isEditorPreview} from '@/editor/editKey';
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
setI18n(i18n.global);
app.use(i18n);

// Create router
app.use(createWebRouter());

app.mount('#app');

// No pinch zoom in Safari on iOS, which ignores `user-scalable=no` of the viewport
for (const type of ['gesturestart', 'gesturechange']) {
  document.addEventListener(type, (event) => event.preventDefault());
}

// In the editor's preview, connect to the editor (a chunk of its own, guests never load it)
if (isEditorPreview) {
  import(/* webpackChunkName: "editor-preview" */ '@/editor/previewBridge')
    .then(({installPreviewBridge}) => installPreviewBridge(pinia));
}
