<script setup lang="ts">
  import {computed, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {
    BookOpen,
    CalendarClock,
    Check,
    ChevronDown,
    ChevronRight,
    EyeOff,
    Image,
    Info,
    MessageSquare,
    Plus,
  } from 'lucide-vue-next'
  import type {EditorCategory, EditorMenu} from '@/api'
  import {useEditorStore} from '@/stores/editor'
  import {isSameSelection, Section, Selection, selectionOf} from '@/editor/sections'
  import {translated} from '@/editor/translations'
  import {openState} from '@/editor/hours'
  import {presetOf} from '@/editor/brand'

  /**
   * The parts of the public page: a click opens the panel of the part, and the preview shows it.
   * The part hovered in the preview is highlighted here, and the other way round.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const restaurant = computed(() => editor.restaurant!)

  const title = (value: EditorMenu | EditorCategory) => translated(value.title, editor.defaultLocale)

  const notArchived = <T extends { archived: boolean }>(items: T[] | undefined): T[] =>
    (items ?? []).filter((item) => !item.archived)

  const status = computed(() => openState(restaurant.value))

  const STATUS_DOTS = {
    open: 'bg-[#00a63e]',
    closed: 'bg-red-600',
    temporarily_closed: 'bg-amber-500',
  }

  const sections = computed<{ section: Section, icon: typeof Image, meta: string }[]>(() => [
    {
      section: 'photos',
      icon: Image,
      meta: t('editor.structure.photos_count', restaurant.value.photos?.length ?? 0),
    },
    {
      section: 'details',
      icon: Info,
      meta: t('editor.structure.details_meta'),
    },
    {
      section: 'notes',
      icon: MessageSquare,
      meta: t('editor.structure.notes_count', restaurant.value.notes?.length ?? 0),
    },
    {
      section: 'menus',
      icon: BookOpen,
      meta: t('editor.structure.menus_count', editor.menus.length),
    },
    {
      section: 'hours',
      icon: CalendarClock,
      meta: t('editor.status.' + status.value),
    },
  ])

  const brandName = computed(() => {
    const preset = presetOf(editor.brand)

    return preset ? t('editor.brand.presets.' + preset.key) : t('editor.structure.custom_colors')
  })

  // the first menu is open at the start
  const expanded = ref<Set<number>>(new Set(editor.menus.slice(0, 1).map((m) => m.id)))

  function toggle(menu: EditorMenu) {
    const ids = new Set(expanded.value)

    if (!ids.delete(menu.id)) {
      ids.add(menu.id)
    }

    expanded.value = ids
  }

  /** The row of the part hovered in the preview. */
  function isHighlighted(selection: Selection): boolean {
    return !!editor.previewHover && isSameSelection(selectionOf(editor.previewHover), selection)
  }

  function open(selection: Selection) {
    // a menu's page opens in the preview, when guests can see it
    if (selection.section === 'menu' && editor.isShown(selection)) {
      editor.openPage({page: 'menu', menuId: selection.id})
    }

    editor.select(selection, editor.isShown(selection))
  }

  function hover(selection: Selection | null) {
    editor.treeHover = selection
  }
</script>

<template>
  <div class="flex-1 min-h-0 flex flex-col">
    <div class="shrink-0 px-5 pt-4 pb-3 border-b border-[#f0f0f1]">
      <p class="text-xs text-zinc-500">{{ translated(restaurant.name, editor.defaultLocale) }}</p>
      <h2 class="mt-0.5 text-lg/[26px] font-semibold">{{ t('editor.structure.title') }}</h2>
      <p class="mt-1 text-[13px]/[18px] text-zinc-500">{{ t('editor.structure.help') }}</p>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto px-3 pt-3 pb-6 flex flex-col gap-4">
      <section class="flex flex-col gap-0.5">
        <h3 class="e-section px-2 pb-1.5">{{ t('editor.structure.restaurant_page') }}</h3>

        <button type="button"
                class="h-10 w-full flex items-center gap-2.5 px-2.5 rounded-md text-start e-focus"
                :class="isHighlighted({section: item.section, id: null}) ? 'bg-blue-50 text-blue-700' : 'hover:bg-zinc-50'"
                v-for="item in sections" :key="item.section"
                @click="open({section: item.section, id: null})"
                @mouseenter="hover({section: item.section, id: null})"
                @mouseleave="hover(null)"
                @focus="hover({section: item.section, id: null})"
                @blur="hover(null)">
          <component :is="item.icon"
                     class="size-4"
                     :class="{'text-zinc-500': !isHighlighted({section: item.section, id: null})}"/>
          <span class="font-medium">{{ t('editor.sections.' + item.section) }}</span>

          <span class="ml-auto inline-flex items-center gap-1.5 text-[13px]"
                :class="{'text-zinc-500': !isHighlighted({section: item.section, id: null})}">
            <span class="size-[7px] rounded-full"
                  :class="STATUS_DOTS[status]"
                  aria-hidden="true"
                  v-if="item.section === 'hours'"/>
            {{ item.meta }}
          </span>

          <ChevronRight class="size-4"
                        :class="{'text-zinc-400': !isHighlighted({section: item.section, id: null})}"/>
        </button>
      </section>

      <section class="flex flex-col gap-0.5">
        <h3 class="e-section px-2 pb-1.5">{{ t('editor.structure.menus') }}</h3>

        <div class="flex flex-col items-start gap-2 px-2.5 py-1"
             v-if="!editor.menus.length">
          <p class="text-[13px] text-zinc-500">{{ t('editor.structure.no_menus') }}</p>

          <button type="button"
                  class="e-btn e-btn-secondary h-8 px-2.5"
                  @click="editor.select({section: 'menu', id: null})">
            <Plus class="size-[15px]"/>
            {{ t('editor.menus.first') }}
          </button>
        </div>

        <template v-for="menu in editor.menus" :key="menu.id">
          <div class="h-10 flex items-center gap-1 pl-0.5 pr-2.5 rounded-md"
               :class="isHighlighted({section: 'menu', id: menu.id}) ? 'bg-blue-50 text-blue-700' : 'hover:bg-zinc-50'">
            <button type="button"
                    class="size-7 shrink-0 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 e-focus"
                    :aria-expanded="expanded.has(menu.id)"
                    :aria-label="t(expanded.has(menu.id) ? 'editor.structure.hide_categories' : 'editor.structure.show_categories', {menu: title(menu)})"
                    @click="toggle(menu)">
              <ChevronDown class="size-4" v-if="expanded.has(menu.id)"/>
              <ChevronRight class="size-4" v-else/>
            </button>

            <button type="button"
                    class="flex-1 min-w-0 h-full flex items-center gap-2 text-start rounded-md e-focus"
                    @click="open({section: 'menu', id: menu.id})"
                    @mouseenter="hover({section: 'menu', id: menu.id})"
                    @mouseleave="hover(null)">
              <span class="font-semibold truncate"
                    :class="{'text-zinc-400': menu.is_hidden}">
                {{ title(menu) }}
              </span>

              <EyeOff class="size-3.5 shrink-0 text-zinc-400"
                      :aria-label="t('editor.structure.hidden')"
                      v-if="menu.is_hidden"/>

              <span class="ml-auto shrink-0 text-[13px] text-zinc-500">
                {{ t('editor.structure.categories_count', notArchived(menu.categories).length) }}
              </span>
            </button>
          </div>

          <div class="flex flex-col gap-0.5 pl-[26px]"
               v-if="expanded.has(menu.id)">
            <button type="button"
                    class="h-9 w-full flex items-center gap-2.5 px-2.5 rounded-md text-start e-focus"
                    :class="isHighlighted({section: 'category', id: category.id}) ? 'bg-blue-50 text-blue-700' : 'hover:bg-zinc-50'"
                    v-for="category in notArchived(menu.categories)" :key="category.id"
                    @click="open({section: 'category', id: category.id})"
                    @mouseenter="hover({section: 'category', id: category.id})"
                    @mouseleave="hover(null)"
                    @focus="hover({section: 'category', id: category.id})"
                    @blur="hover(null)">
              <span class="truncate"
                    :class="{'text-zinc-400': category.is_hidden || menu.is_hidden}">
                {{ title(category) }}
              </span>

              <EyeOff class="size-3.5 shrink-0 text-zinc-400"
                      :aria-label="t('editor.structure.hidden')"
                      v-if="category.is_hidden"/>

              <span class="ml-auto shrink-0 text-[13px] text-zinc-500">
                {{ t('editor.structure.dishes_count', notArchived(category.dishes).length) }}
              </span>
            </button>
          </div>
        </template>
      </section>

      <section class="flex flex-col gap-0.5">
        <h3 class="e-section px-2 pb-1.5">{{ t('editor.structure.appearance') }}</h3>

        <button type="button"
                class="h-10 w-full flex items-center gap-2.5 px-2.5 rounded-md text-start hover:bg-zinc-50 e-focus"
                @click="open({section: 'brand', id: null})">
          <span class="size-4 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
                :style="{background: editor.brand.primary}"
                aria-hidden="true"/>
          <span class="font-medium">{{ t('editor.sections.brand') }}</span>
          <span class="ml-auto text-[13px] text-zinc-500">{{ brandName }}</span>
          <ChevronRight class="size-4 text-zinc-400"/>
        </button>
      </section>
    </div>

    <div class="shrink-0 flex items-center gap-2 px-5 py-3 border-t border-zinc-200 text-[13px]">
      <template v-if="editor.dirty">
        <span class="size-[7px] rounded-full bg-amber-500" aria-hidden="true"/>
        <span class="text-[#a16207]">{{ t('editor.panel.unsaved') }}</span>
      </template>

      <template v-else>
        <Check class="size-4 text-[#00a63e]"/>
        <span class="text-zinc-500">{{ t('editor.panel.saved') }}</span>
      </template>
    </div>
  </div>
</template>
