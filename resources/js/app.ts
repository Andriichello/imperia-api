import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Welcome from './Pages/Welcome.vue'

// Map your pages
const pages: Record<string, any> = {
  'Welcome': Welcome,
  // Add other pages here as needed
}

createInertiaApp({
  resolve: (name: string) => {
    return pages[name];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
