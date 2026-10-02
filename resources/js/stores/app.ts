import { defineStore } from 'pinia'
import {DishMenu, Restaurant} from "@/api";

interface AppState {
  locale: string;
  supported_locales: string[];
  restaurant: Restaurant | null;
  menus: DishMenu[] | null;
}

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    locale: 'en',
    supported_locales: ['en'],
    restaurant: null,
    menus: null,
  }),
  actions: {
    hydrate(props: Partial<AppState>) {
      Object.assign(this, props)
    }
  }
})
