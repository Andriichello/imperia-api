<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Check, ChevronDown, ExternalLink, LogOut, Utensils} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import LanguageMenu from '@/Components/Admin/LanguageMenu.vue'
  import type {AdminRestaurant, AdminUser} from '@/admin/types'

  /**
   * The admin's navbar on every signed-in page: home (the logo and the restaurant's name), other
   * restaurants (their dashboards), the admin's language, the public site and the account.
   */
  const props = defineProps({
    user: {type: Object as PropType<AdminUser>, required: true},
    restaurants: {type: Array as PropType<AdminRestaurant[]>, required: true},
    restaurantId: {type: Number, required: true},
    siteUrl: {type: String, required: true},
    urls: {
      type: Object as PropType<{ dashboard: string, logout: string }>,
      required: true,
    },
    // the page of another restaurant (its dashboard, or e.g. its editor)
    switchUrl: {
      type: Function as PropType<(id: number) => string>,
      default: null,
    },
  })

  const {t} = useI18n()

  const current = computed(() => props.restaurants.find((item) => item.id === props.restaurantId) ?? null)

  const initials = computed(() => props.user.name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((word) => word[0].toUpperCase())
    .join('') || '?')

  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''

  const urlOf = (id: number) => props.switchUrl ? props.switchUrl(id) : `${props.urls.dashboard}?restaurant=${id}`
</script>

<template>
  <header class="h-[52px] shrink-0 flex items-center gap-2 pl-3.5 pr-3 bg-white border-b border-zinc-200">
    <a class="size-[30px] shrink-0 rounded-[7px] bg-zinc-900 text-white flex items-center justify-center e-focus"
       :href="urls.dashboard"
       :aria-label="t('admin.nav.dashboard')"
       :title="t('admin.nav.dashboard')">
      <Utensils class="size-4"/>
    </a>

    <span class="w-px h-5 bg-zinc-200" aria-hidden="true"/>

    <a class="e-nav-btn min-w-0 text-[15px]! e-focus"
       :href="urls.dashboard"
       :title="t('admin.nav.dashboard')">
      <span class="truncate">{{ current?.name }}</span>
    </a>

    <!-- another restaurant: its dashboard (or e.g. its editor) -->
    <DropdownMenu class="-ml-1.5" v-if="restaurants.length > 1">
      <template #trigger="{open, toggle}">
        <button type="button"
                class="e-nav-btn px-1.5! e-focus"
                aria-haspopup="menu"
                :aria-expanded="open"
                :aria-label="t('admin.nav.switch_restaurant', {name: current?.name ?? ''})"
                :title="t('admin.nav.switch')"
                @click="toggle">
          <ChevronDown class="size-4 text-zinc-500"/>
        </button>
      </template>

      <a class="e-dropdown-item"
         role="menuitem"
         :href="urlOf(item.id)"
         :aria-current="item.id === restaurantId"
         v-for="item in restaurants" :key="item.id">
        <span class="flex-1 truncate">{{ item.name }}</span>
        <Check class="size-4 text-zinc-500" v-if="item.id === restaurantId"/>
      </a>
    </DropdownMenu>

    <slot/>

    <div class="flex-1"/>

    <LanguageMenu/>

    <a class="e-nav-btn border border-zinc-300 px-3! mx-1"
       target="_blank"
       rel="noopener"
       :href="siteUrl">
      <ExternalLink class="size-4"/>
      <span class="max-md:sr-only">{{ t('admin.nav.visit_site') }}</span>
    </a>

    <DropdownMenu align="end">
      <template #trigger="{open, toggle}">
        <button type="button"
                class="size-8 shrink-0 rounded-full bg-zinc-200 text-zinc-700 flex items-center justify-center text-xs font-bold e-focus"
                aria-haspopup="menu"
                :aria-expanded="open"
                :aria-label="t('admin.nav.account', {name: user.name})"
                @click="toggle">
          {{ initials }}
        </button>
      </template>

      <div class="px-2.5 pt-1.5 pb-2 mb-1 border-b border-zinc-100">
        <p class="font-semibold truncate">{{ user.name }}</p>
        <p class="text-xs text-zinc-500 truncate">{{ user.email }}</p>
      </div>

      <form method="post" :action="urls.logout">
        <input type="hidden" name="_token" :value="csrfToken"/>

        <button type="submit"
                class="e-dropdown-item"
                role="menuitem">
          <LogOut class="size-4 text-zinc-500"/>
          {{ t('admin.nav.sign_out') }}
        </button>
      </form>
    </DropdownMenu>
  </header>
</template>
