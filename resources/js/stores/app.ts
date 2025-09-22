import { defineStore } from 'pinia'
import {DishMenu, Restaurant} from "@/api";

interface AppAuthState {
  user: Record<string, any> | null
}

interface FlashAuthState {
  message: string | null
}

interface AppState {
  auth: AppAuthState | null;
  flash: FlashAuthState | null;
  locale: string;
  supported_locales: string[];
  restaurant: Restaurant | null;
  menus: DishMenu[] | null;
}

export const useAppStore = defineStore('app', {
  state: (): AppState => ({
    auth: null,
    flash: null,
    locale: 'en',
    supported_locales: ['en'],
    restaurant: null,
    menus: null,
  }),
  actions: {
    hydrate(props: any) {
      Object.assign(this, props)
    }
  }
})
