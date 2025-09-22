import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import WebPreviewPage from '@/Pages/WebPreviewPage.vue'

function getBaseFromCurrentPathname(): string {
  // Expect: /{locale}/web/... -> base is /{locale}/web
  const parts = window.location.pathname.split('/')
  if (parts.length >= 3 && parts[2] === 'web') {
    return `/${parts[1]}/web`
  }
  // Fallback: root
  return '/'
}

const routes: Array<RouteRecordRaw> = [
  {
    path: '/:restaurant_id',
    name: 'restaurant.preview',
    component: WebPreviewPage,
    props: true
  },
  {
    path: '/:restaurant_id/menu/:menu_id?',
    name: 'menu.preview',
    component: WebPreviewPage,
    props: true
  },
]

export function createWebRouter() {
  const base = getBaseFromCurrentPathname()
  return createRouter({
    history: createWebHistory(base),
    routes,
  })
}
