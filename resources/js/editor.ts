import {createApp} from 'vue';
import {createPinia} from 'pinia';
import setupI18n from '@/i18n';
import {setI18n} from '@/i18n/utils';
import editorEn from '@/i18n/editor/en.json';
import editorUk from '@/i18n/editor/uk.json';
import {useEditorStore} from '@/stores/editor';
import EditorPage from '@/Pages/EditorPage.vue';

// The page editor of the admin panel (see `resources/views/editor/app.blade.php`)
const element = document.getElementById('editor');
const props = element ? JSON.parse(element.dataset.props || '{}') : {};

const app = createApp(EditorPage);

const pinia = createPinia();
app.use(pinia);

// Hydrate the store with Laravel props
useEditorStore(pinia).hydrate(props);

// The editor's own texts, on top of the public site's ones (e.g. names of restaurant types)
const i18n = setupI18n(props.locale || 'en', {en: editorEn, uk: editorUk});
setI18n(i18n.global);
app.use(i18n);

app.mount('#editor');
