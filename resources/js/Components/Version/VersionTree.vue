<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {BookOpen, ChevronDown, ChevronRight, List, Search, Soup, Store} from 'lucide-vue-next'
  import {priceFormatted} from '@/helpers'
  import type {NodeKind, TreeNode} from '@/version/model'
  import {weightLabel} from '@/version/sizes'
  import {useVersionStore} from '@/stores/version'

  /**
   * The version's items: the restaurant page, then menus › categories › dishes › sizes. Changed
   * ones are amber, with what happens to them; every row opens its item (a size opens its dish).
   */
  const store = useVersionStore()
  const {t} = useI18n()

  const ICONS: Partial<Record<NodeKind, unknown>> = {
    restaurant: Store,
    menu: BookOpen,
    category: List,
    dish: Soup,
  }

  interface Row {
    node: TreeNode
    depth: number
    open: boolean
  }

  const filtering = computed(() => !!store.search.trim() || store.changedOnly)

  /** Whether the node shows with the filters: it matches, or something inside it does. */
  function matches(node: TreeNode): boolean {
    const words = store.search.trim().toLowerCase()
    const own = (!words || node.name.toLowerCase().includes(words) || node.kind === 'size')
      && (!store.changedOnly || node.count > 0 || !!node.change)

    return (own && (node.kind !== 'size' || !words)) || node.children.some(matches)
  }

  const rows = computed<Row[]>(() => {
    const rows: Row[] = []
    const add = (nodes: TreeNode[], depth: number) => {
      for (const node of nodes) {
        if (filtering.value && !matches(node)) {
          continue
        }

        // the filters open everything they show
        const open = filtering.value || store.expanded.includes(node.key)
        rows.push({node, depth, open})

        if (open) {
          add(node.children, depth + 1)
        }
      }
    }

    add(store.tree, 0)

    return rows
  })

  const currency = computed(() => (store.restaurant?.currency ?? 'uah').toLowerCase())
  const price = (value: unknown) => value === null || value === undefined ? '' : (priceFormatted(Number(value), currency.value) ?? '')
  const number = (value: unknown) => value === null || value === undefined ? '' : String(Number(value))

  /** "300 g · 160 ₴" of a dish's first size. */
  function dishMeta(node: TreeNode): string {
    const size = node.children[0]?.size

    return size ? [weightLabel(size.values), price(size.values.price)].filter(Boolean).join(' · ') : ''
  }

  const changedTotal = computed(() => store.version?.changes_count ?? 0)

  function collapseAll() {
    store.expanded = []
  }
</script>

<template>
  <aside class="w-[380px] shrink-0 flex flex-col bg-white border-r border-zinc-200 max-lg:w-[300px]"
         :aria-label="t('admin.version.items')">
    <div class="shrink-0 flex flex-col gap-2.5 px-3.5 pt-3.5 pb-2.5 border-b border-[#f0f0f1]">
      <div class="relative">
        <Search class="absolute left-3 top-2.5 size-4 text-zinc-400" aria-hidden="true"/>
        <input class="e-input h-9 pl-[34px]"
               type="search"
               :placeholder="t('admin.version.find')"
               :aria-label="t('admin.version.find_label')"
               v-model="store.search"/>
      </div>

      <div class="flex items-center gap-2">
        <div class="e-seg" role="group" :aria-label="t('admin.version.show')">
          <button type="button" class="e-focus" :aria-pressed="!store.changedOnly" @click="store.changedOnly = false">
            {{ t('admin.version.all_items') }}
          </button>
          <button type="button" class="e-focus" :aria-pressed="store.changedOnly" @click="store.changedOnly = true">
            {{ t('admin.version.changed_only') }} <span class="text-[#a16207]">{{ changedTotal }}</span>
          </button>
        </div>

        <button type="button"
                class="ml-auto text-xs font-semibold text-zinc-600 hover:text-zinc-900 rounded e-focus"
                @click="collapseAll">
          {{ t('admin.version.collapse_all') }}
        </button>
      </div>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto px-2 pt-2 pb-4 flex flex-col gap-px">
      <p class="px-2 py-3 text-[13px] text-zinc-500" v-if="!rows.length">{{ t('admin.version.nothing_found') }}</p>

      <div class="h-8 flex items-center gap-1.5 pr-2.5 rounded-md cursor-pointer"
           :class="row.node.key === store.current?.key
             ? 'bg-blue-100 text-blue-700'
             : (row.node.change || (row.node.kind === 'restaurant' && row.node.count) ? 'bg-[#fffbeb] hover:bg-amber-100/60' : 'hover:bg-zinc-50')"
           :style="{paddingLeft: `${8 + row.depth * 16}px`}"
           v-for="row in rows" :key="row.node.key"
           @click="store.select(row.node.key)">
        <button type="button"
                class="size-3.5 shrink-0 flex items-center justify-center text-zinc-500 rounded e-focus"
                :aria-expanded="row.open"
                :aria-label="t(row.open ? 'admin.version.collapse' : 'admin.version.expand', {name: row.node.name})"
                v-if="row.node.children.length"
                @click.stop="store.toggle(row.node.key)">
          <ChevronDown class="size-3.5" v-if="row.open"/>
          <ChevronRight class="size-3.5" v-else/>
        </button>
        <span class="w-3.5 shrink-0" v-else/>

        <component :is="ICONS[row.node.kind]" class="size-[15px] shrink-0" v-if="ICONS[row.node.kind]"/>
        <span class="w-[15px] shrink-0 flex justify-center" aria-hidden="true" v-else>
          <span class="size-[5px] rounded-full bg-zinc-400"/>
        </span>

        <!-- the row's button: its name -->
        <button type="button"
                class="min-w-0 truncate text-start rounded e-focus"
                :class="[
                  row.node.kind === 'dish' || row.node.kind === 'size' ? 'font-medium' : 'font-semibold',
                  {'text-zinc-400': row.node.archived && !row.node.change},
                ]"
                @click.stop="store.select(row.node.key)">
          <template v-if="row.node.kind === 'size'">
            <template v-if="row.node.size?.live && weightLabel(row.node.size.live) !== weightLabel(row.node.size.values)">
              <s class="text-zinc-400">{{ weightLabel(row.node.size.live) }}</s> → <b>{{ weightLabel(row.node.size.values) }}</b>
            </template>
            <template v-else>{{ weightLabel(row.node.size?.values ?? null) || t('editor.dish.size', {number: 1}) }}</template>
          </template>
          <template v-else>{{ row.node.name || t('editor.dish.new') }}</template>
        </button>

        <span class="size-[7px] shrink-0 rounded-full bg-amber-500"
              :aria-label="t('admin.version.changed')"
              v-if="row.node.change || (row.node.kind === 'restaurant' && row.node.count)"/>

        <span class="e-pill e-pill-sm shrink-0 bg-green-100 text-green-800" v-if="row.node.isNew">{{ t('admin.version.new') }}</span>
        <span class="e-pill e-pill-sm shrink-0 bg-zinc-100 text-zinc-600" v-if="row.node.hides">{{ t('admin.version.hide') }}</span>
        <span class="e-pill e-pill-sm shrink-0 bg-red-100 text-red-800" v-if="row.node.archives">{{ t('admin.version.archive') }}</span>

        <span class="ml-auto shrink-0 text-xs tabular-nums whitespace-nowrap"
              :class="row.node.kind === 'menu' || row.node.kind === 'category' || row.node.kind === 'restaurant' ? 'font-semibold text-[#a16207]' : 'text-zinc-500'">
          <template v-if="row.node.kind === 'size'">
            <template v-if="row.node.size?.live && row.node.size.live.price !== row.node.size.values.price">
              <s class="text-zinc-400">{{ number(row.node.size.live.price) }}</s> → <b class="text-zinc-900">{{ price(row.node.size.values.price) }}</b>
            </template>
            <template v-else>{{ price(row.node.size?.values.price) }}</template>
          </template>
          <template v-else-if="row.node.kind === 'dish'">
            {{ row.node.count && !row.node.isNew ? t('admin.version.changes', row.node.count) : dishMeta(row.node) }}
          </template>
          <template v-else-if="row.node.kind === 'category'">{{ row.node.count || '' }}</template>
          <template v-else>{{ row.node.count ? t('admin.version.changes', row.node.count) : '' }}</template>
        </span>
      </div>
    </div>

    <div class="shrink-0 flex items-center gap-2.5 px-4 py-2.5 border-t border-zinc-200 text-xs text-zinc-600">
      <span class="inline-flex items-center gap-1.5"><span class="size-[7px] rounded-full bg-amber-500"/>{{ t('admin.version.changed') }}</span>
      <span class="e-pill e-pill-sm bg-green-100 text-green-800">{{ t('admin.version.new') }}</span>
      <span class="e-pill e-pill-sm bg-zinc-100 text-zinc-600">{{ t('admin.version.hide') }}</span>
      <span class="e-pill e-pill-sm bg-red-100 text-red-800">{{ t('admin.version.archive') }}</span>
    </div>
  </aside>
</template>
