import { defineStore } from 'pinia'
import {DishMenu, Restaurant} from "@/api";

interface AppState {
  locale: string;
  supported_locales: string[];
  // the developer's contact at the end of the pages
  developer_email: string | null;
  restaurant: Restaurant | null;
  menus: DishMenu[] | null;
}

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    locale: 'en',
    supported_locales: ['en'],
    developer_email: null,
    restaurant: null,
    menus: null,
  }),
  actions: {
    hydrate(props: Partial<AppState>) {
      Object.assign(this, props)
    }
  }
})
