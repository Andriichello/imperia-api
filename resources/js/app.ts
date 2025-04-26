import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Welcome from '@/Pages/Welcome.vue'
import Restaurant from "@/Pages/Restaurant.vue";

// Map your pages
const pages: Record<string, any> = {
  'Welcome': Welcome,
  'Restaurant': Restaurant,
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
