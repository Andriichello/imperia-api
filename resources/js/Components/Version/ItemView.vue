<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {AlertTriangle, CalendarClock, Undo2} from 'lucide-vue-next'
  import DishView from '@/Components/Version/DishView.vue'
  import CategoryView from '@/Components/Version/CategoryView.vue'
  import MenuView from '@/Components/Version/MenuView.vue'
  import RestaurantView from '@/Components/Version/RestaurantView.vue'
  import {formatDateTime} from '@/admin/format'
  import {languageName} from '@/editor/translations'
  import {changeOf, TreeNode} from '@/version/model'
  import {useVersionStore} from '@/stores/version'

  /**
   * The item open on the version's page: its header (where it is, its name, what the version
   * does to it) and its fields, live and from the version's date.
   */
  const store = useVersionStore()
  const {t, locale} = useI18n()

  const node = computed<TreeNode>(() => store.current!)

  const goesLiveAt = computed(() => store.version?.goes_live_at
    ? DateTime.fromISO(store.version.goes_live_at, {setZone: true})
    : null)

  // "Sun 1 Nov, 00:00"
  const date = computed(() => goesLiveAt.value ? formatDateTime(goesLiveAt.value, locale.value) : t('admin.version.no_date'))

  const crumb = computed(() => {
    switch (node.value.kind) {
      case 'restaurant':
        return t('admin.version.restaurant_page')
      case 'menu':
        return t('editor.sections.menus')
      default:
        return node.value.path.join(' › ')
    }
  })

  // the version's changes of its own fields (not of the ones inside it)
  const own = computed(() => node.value.kind === 'restaurant'
    ? store.version!.changes.filter((change) => change.target_type === 'restaurants' || change.target_type === 'restaurant-notes')
    : [node.value.change, ...(node.value.kind === 'dish' ? node.value.children.map((child) => child.change) : [])]
      .filter((change) => !!change))

  const pill = computed(() => {
    if (node.value.isNew) {
      return {text: t('admin.version.new_item'), tone: 'bg-green-100 text-green-800'}
    }

    if (node.value.archives) {
      return {text: t('admin.version.archive'), tone: 'bg-red-100 text-red-800'}
    }

    if (node.value.count) {
      const inside = (node.value.kind === 'category' || node.value.kind === 'menu') && !node.value.change

      return {
        text: t(inside ? 'admin.version.changes_inside' : 'admin.version.changes', node.value.count),
        tone: 'bg-[#fffbeb] text-[#92400e]',
      }
    }

    return {text: t('admin.version.no_changes'), tone: 'bg-zinc-100 text-zinc-600'}
  })

  const subtitle = computed(() => {
    const category = node.value.path[node.value.path.length - 1] ?? ''

    if (node.value.isNew) {
      return t('admin.version.added_to', {category, date: date.value})
    }

    switch (node.value.kind) {
      case 'dish':
        return node.value.count ? null : t('admin.version.dish_help')
      case 'category':
        return node.value.change ? null : t('admin.version.category_help')
      case 'menu':
        return node.value.change ? null : t('admin.version.menu_help')
      default:
        return null
    }
  })

  const missing = computed(() => own.value.some((change) => change!.conflicts?.missing))

  /** All of the version's changes of the item (and of a dish's sizes) are left out. */
  async function revertAll() {
    for (const change of own.value) {
      await store.remove(change!.id)
    }
  }

  // the restaurant's change, for the restaurant page
  const restaurantChange = computed(() => changeOf(store.version!, 'restaurants', store.restaurant!.id))
</script>

<template>
  <div class="max-w-[1060px] px-3 pt-4 pb-8">
    <div class="flex items-end gap-2.5 px-3 pb-3">
      <div class="flex-1 min-w-0">
        <p class="text-[13px] text-zinc-500 truncate">{{ crumb }}</p>

        <div class="flex items-center gap-2 mt-0.5">
          <h2 class="text-xl/7 font-semibold truncate">{{ node.name || t('editor.dish.new') }}</h2>
          <span class="e-pill shrink-0" :class="pill.tone">{{ pill.text }}</span>
        </div>

        <p class="mt-0.5 e-help" v-if="subtitle">{{ subtitle }}</p>
      </div>

      <div class="e-seg"
           role="group"
           :aria-label="t('editor.panel.content_language')"
           v-if="store.locales.length > 1">
        <button type="button"
                class="uppercase e-focus"
                :aria-pressed="store.contentLocale === item"
                :title="languageName(item)"
                v-for="item in store.locales" :key="item"
                @click="store.contentLocale = item">
          {{ item }}
        </button>
      </div>

      <template v-if="!store.readOnly">
        <button type="button"
                class="e-btn e-btn-secondary h-[34px]"
                v-if="node.isNew"
                @click="revertAll">
          <Undo2 class="size-4"/>
          {{ t('admin.version.remove_from_version') }}
        </button>

        <button type="button"
                class="e-btn e-btn-secondary h-[34px]"
                v-else-if="node.archives"
                @click="store.revert(node.target!, ['archived'])">
          <Undo2 class="size-4"/>
          {{ t('admin.version.keep_live') }}
        </button>

        <button type="button"
                class="e-btn e-btn-secondary h-[34px]"
                v-else-if="own.length"
                @click="revertAll">
          <Undo2 class="size-4"/>
          {{ t('admin.version.revert_all') }}
        </button>
      </template>
    </div>

    <div class="flex items-center gap-2.5 mx-3 mb-3 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-[13px]/[18px] text-red-800"
         role="alert"
         v-if="missing">
      <AlertTriangle class="size-4 shrink-0"/>
      <p class="flex-1">{{ t('admin.version.missing') }}</p>
    </div>

    <div class="grid grid-cols-[136px_minmax(0,0.8fr)_20px_minmax(0,1.3fr)_64px] items-center gap-x-3 px-3 py-2 border-b border-zinc-200 text-xs/4 font-semibold text-zinc-500">
      <span/>
      <span>{{ t('admin.version.now_live') }}</span>
      <span/>
      <span class="inline-flex items-center gap-1.5 text-blue-700">
        <CalendarClock class="size-3.5"/>
        {{ goesLiveAt ? t('admin.version.from', {date}) : t('admin.version.from_undated') }}
      </span>
      <span/>
    </div>

    <DishView :node="node" v-if="node.kind === 'dish'" :key="node.key"/>
    <CategoryView :node="node" :date-label="goesLiveAt ? t('admin.version.from_short', {date: formatDateTime(goesLiveAt, locale).split(',')[0]}) : t('admin.version.from_short_undated')" v-else-if="node.kind === 'category'" :key="node.key"/>
    <MenuView :node="node" :date="date" :dated="!!goesLiveAt" v-else-if="node.kind === 'menu'" :key="node.key"/>
    <RestaurantView v-else-if="node.kind === 'restaurant'" :key="`restaurant-${restaurantChange?.id ?? 0}`"/>
  </div>
</template>
