<script setup lang="ts">
  import {computed, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {intlLocale} from '@/admin/format'
  import {
    Archive,
    ChevronDown,
    ChevronRight,
    Copy,
    Ellipsis,
    Eye,
    EyeOff,
    Pencil,
    Plus,
    RotateCcw,
    Trash2,
  } from 'lucide-vue-next'
  import {VueDraggable} from 'vue-draggable-plus'
  import type {EditorMenu} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import GripHandle from '@/Components/Editor/Fields/GripHandle.vue'
  import {moveItem} from '@/editor/lists'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import UnsavedPill from '@/Components/Editor/UnsavedPill.vue'
  import NewDraftRow from '@/Components/Editor/NewDraftRow.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useItemActions} from '@/composables/useItemActions'
  import {isListed, MenuOrder} from '@/editor/menuDrafts'
  import {translated} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The menus and their categories: their order (categories can move to other menus), which
   * ones guests see, and the archived ones, which can be restored or deleted.
   */
  const editor = useEditorStore()
  const {t} = useI18n()
  const actions = useItemActions()

  const restaurant = computed(() => editor.restaurant!)

  const {draft} = usePanelDraft<MenuOrder[]>({section: 'menus', id: null})

  const name = (item: { title: EditorMenu['title'] } | null) => item ? translated(item.title, editor.defaultLocale) : ''

  const description = (item: { description: EditorMenu['description'] } | null) => item
    ? translated(item.description, editor.defaultLocale)
    : ''

  const archived = computed(() => restaurant.value.menus.filter((menu) => menu.archived))

  // the first menu is open at the start
  const expanded = ref<Set<number>>(new Set(draft.value.slice(0, 1).map((item) => item.id)))
  const showArchived = ref(true)

  function toggle(id: number) {
    const ids = new Set(expanded.value)

    if (!ids.delete(id)) {
      ids.add(id)
    }

    expanded.value = ids
  }

  /** "5 categories · 18 dishes" of the menu's categories in the draft. */
  function meta(item: MenuOrder): string {
    const dishes = item.categories.reduce((count, id) => {
      return count + (editor.findCategory(id)?.dishes ?? []).filter(isListed).length
    }, 0)

    return `${t('editor.structure.categories_count', item.categories.length)} · ${t('editor.structure.dishes_count', dishes)}`
  }

  function dishCount(id: number): string {
    return t('editor.structure.dishes_count', (editor.findCategory(id)?.dishes ?? []).filter(isListed).length)
  }

  /** Guests see only this menu: hiding or archiving it leaves the menu page empty. */
  async function confirmLastVisible(item: MenuOrder): Promise<boolean> {
    const others = draft.value.filter((other) => other.id !== item.id && !other.is_hidden)

    if (item.is_hidden || others.length) {
      return true
    }

    return editor.confirm({
      title: t('editor.menus.last_visible_title'),
      message: t('editor.menus.last_visible_message'),
      confirm: t('editor.menus.continue'),
    })
  }

  async function toggleHidden(item: MenuOrder) {
    if (await confirmLastVisible(item)) {
      item.is_hidden = !item.is_hidden
    }
  }

  async function archive(item: MenuOrder) {
    const menu = editor.findMenu(item.id)

    if (menu && await confirmLastVisible(item)) {
      await actions.archive('menu', item.id, name(menu))
    }
  }

  async function duplicate(item: MenuOrder) {
    const menu = editor.findMenu(item.id)

    if (menu) {
      await actions.duplicate('menu', item.id, name(menu))
    }
  }

  async function restore(menu: EditorMenu) {
    await actions.restore('menu', menu.id, name(menu))
  }

  async function destroy(menu: EditorMenu) {
    await actions.destroy('menu', menu.id, name(menu), actions.menuContents(menu))
  }

  function archivedAt(menu: EditorMenu): string {
    return menu.archived_at
      ? DateTime.fromISO(menu.archived_at).setLocale(intlLocale(editor.locale)).toLocaleString(DateTime.DATE_MED)
      : ''
  }
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.menus')"
              :subtitle="t('editor.subtitles.menus')"
              @navigate="editor.close()"
              @close="editor.close()">
    <template #actions>
      <button type="button"
              class="e-btn e-btn-primary h-8 px-2.5"
              @click="editor.add('menu')">
        <Plus class="size-[15px]"/>
        {{ t('editor.menus.new') }}
      </button>
    </template>

    <section class="flex flex-col">
      <div class="flex flex-col items-start gap-3 py-2"
           v-if="!draft.length && !editor.newItems('menu').length">
        <p class="text-zinc-500">{{ t('editor.structure.no_menus') }}</p>

        <button type="button"
                class="e-btn e-btn-secondary"
                @click="editor.add('menu')">
          <Plus class="size-[15px]"/>
          {{ t('editor.menus.first') }}
        </button>
      </div>

      <VueDraggable class="flex flex-col"
                    v-model="draft"
                    handle=".e-menu-grip"
                    ghost-class="e-drag-ghost"
                    :animation="150">
        <div v-for="(item, index) in draft" :key="item.id">
          <div class="flex items-start gap-1 min-h-[52px] py-2.5 pr-1 border-b border-[#f0f0f1]">
            <GripHandle class="e-menu-grip"
                        :name="name(editor.findMenu(item.id))"
                        :index="index"
                        :count="draft.length"
                        @move="(from, to) => draft = moveItem(draft, from, to)"/>

            <button type="button"
                    class="e-icon-btn w-6"
                    :aria-expanded="expanded.has(item.id)"
                    :aria-label="t(expanded.has(item.id) ? 'editor.structure.hide_categories' : 'editor.structure.show_categories', {menu: name(editor.findMenu(item.id))})"
                    @click="toggle(item.id)">
              <ChevronDown class="size-4" v-if="expanded.has(item.id)"/>
              <ChevronRight class="size-4" v-else/>
            </button>

            <button type="button"
                    class="flex-1 min-w-0 flex flex-col pt-1 text-start rounded e-focus"
                    @click="toggle(item.id)">
              <span class="font-semibold truncate" :class="{'text-zinc-500': item.is_hidden}">
                {{ name(editor.findMenu(item.id)) }}
              </span>
              <span class="text-[13px]/[18px] text-zinc-600 line-clamp-2"
                    v-if="description(editor.findMenu(item.id))">
                {{ description(editor.findMenu(item.id)) }}
              </span>
              <span class="text-xs/4 text-zinc-500">{{ meta(item) }}</span>
            </button>

            <UnsavedPill v-if="editor.isUnsaved({section: 'menu', id: item.id})"/>

            <span class="e-pill bg-zinc-100 text-zinc-600"
                  v-if="item.is_hidden">
              <EyeOff class="size-3"/>
              {{ t('editor.menus.hidden') }}
            </span>

            <span class="e-pill bg-green-50 text-green-800"
                  v-else>
              <span class="size-1.5 rounded-full bg-green-600" aria-hidden="true"/>
              {{ t('editor.menus.visible') }}
            </span>

            <DropdownMenu align="end">
              <template #trigger="{open, toggle: toggleMenu}">
                <button type="button"
                        class="e-icon-btn"
                        :class="{'bg-zinc-100 text-zinc-900': open}"
                        aria-haspopup="menu"
                        :aria-expanded="open"
                        :aria-label="t('editor.panel.more_actions', {name: name(editor.findMenu(item.id))})"
                        @click="toggleMenu">
                  <Ellipsis class="size-[18px]"/>
                </button>
              </template>

              <button type="button" class="e-dropdown-item" role="menuitem"
                      @click="editor.select({section: 'menu', id: item.id})">
                <Pencil class="size-4 text-zinc-500"/>
                {{ t('editor.menus.rename') }}
              </button>

              <button type="button" class="e-dropdown-item" role="menuitem" @click="duplicate(item)">
                <Copy class="size-4 text-zinc-500"/>
                {{ t('editor.actions.duplicate') }}
              </button>

              <button type="button" class="e-dropdown-item" role="menuitem" @click="toggleHidden(item)">
                <template v-if="item.is_hidden">
                  <Eye class="size-4 text-zinc-500"/>
                  {{ t('editor.actions.show') }}
                </template>
                <template v-else>
                  <EyeOff class="size-4 text-zinc-500"/>
                  {{ t('editor.actions.hide') }}
                </template>
              </button>

              <div class="h-px my-1 bg-[#f0f0f1]"/>

              <button type="button" class="e-dropdown-item" role="menuitem" @click="archive(item)">
                <Archive class="size-4 text-zinc-500"/>
                {{ t('editor.actions.archive') }}
              </button>
            </DropdownMenu>
          </div>

          <div class="flex flex-col pt-1 pb-2 border-b border-[#f0f0f1]"
               v-if="expanded.has(item.id)">
            <!-- categories can be dragged to other menus too -->
            <VueDraggable class="flex flex-col min-h-2"
                          v-model="item.categories"
                          group="categories"
                          handle=".e-category-grip"
                          ghost-class="e-drag-ghost"
                          :animation="150">
              <div class="min-h-12 flex items-center gap-1 py-1 pl-7 pr-1 rounded-md hover:bg-zinc-50"
                   v-for="(id, position) in item.categories" :key="id">
                <GripHandle class="e-category-grip h-7"
                            :name="name(editor.findCategory(id))"
                            :index="position"
                            :count="item.categories.length"
                            @move="(from, to) => item.categories = moveItem(item.categories, from, to)"/>

                <button type="button"
                        class="flex-1 min-w-0 h-full flex items-center gap-2 text-start rounded e-focus"
                        @click="editor.select({section: 'category', id}, true)">
                  <span class="min-w-0 flex flex-col">
                    <span class="truncate"
                          :class="{'text-zinc-400': editor.findCategory(id)?.is_hidden}">
                      {{ name(editor.findCategory(id)) }}
                    </span>
                    <span class="text-xs/4 text-zinc-500 truncate"
                          v-if="description(editor.findCategory(id))">
                      {{ description(editor.findCategory(id)) }}
                    </span>
                    <span class="text-xs/4 text-zinc-400 italic" v-else>{{ t('editor.menus.no_description') }}</span>
                  </span>

                  <EyeOff class="size-3.5 shrink-0 text-zinc-400"
                          :aria-label="t('editor.structure.hidden')"
                          v-if="editor.findCategory(id)?.is_hidden"/>

                  <UnsavedPill class="ml-auto" v-if="editor.isUnsaved({section: 'category', id})"/>
                  <span class="shrink-0 text-[13px] text-zinc-500"
                        :class="{'ml-auto': !editor.isUnsaved({section: 'category', id})}">{{ dishCount(id) }}</span>
                  <ChevronRight class="size-4 shrink-0 text-zinc-400"/>
                </button>
              </div>
            </VueDraggable>

            <div class="pl-12"
                 v-for="entry in editor.newItems('category', item.id)" :key="entry.key">
              <NewDraftRow :entry="entry"/>
            </div>

            <button type="button"
                    class="h-9 flex items-center gap-2 pl-14 pr-2.5 text-blue-600 font-semibold rounded e-focus"
                    @click="editor.add('category', item.id)">
              <Plus class="size-4"/>
              {{ t('editor.menus.add_category') }}
            </button>
          </div>
        </div>
      </VueDraggable>

      <div class="py-1 border-b border-[#f0f0f1]"
           v-for="entry in editor.newItems('menu')" :key="entry.key">
        <NewDraftRow :entry="entry"/>
      </div>
    </section>

    <section class="flex flex-col gap-2"
             v-if="archived.length">
      <button type="button"
              class="e-section flex items-center gap-1.5 rounded e-focus"
              :aria-expanded="showArchived"
              @click="showArchived = !showArchived">
        <ChevronDown class="size-3.5" v-if="showArchived"/>
        <ChevronRight class="size-3.5" v-else/>
        {{ t('editor.menus.archived', {count: archived.length}) }}
      </button>

      <template v-if="showArchived">
        <div class="flex items-start gap-2.5 py-2.5 pl-3 pr-1 rounded-lg bg-zinc-50 border border-[#f0f0f1]"
             v-for="menu in archived" :key="menu.id">
          <Archive class="size-4 shrink-0 mt-0.5 text-zinc-400"/>

          <div class="flex-1 min-w-0 flex flex-col items-start gap-0.5">
            <span class="max-w-full font-semibold text-zinc-500 truncate">{{ name(menu) }}</span>
            <span class="text-[13px]/[18px] text-zinc-500 line-clamp-2" v-if="description(menu)">{{ description(menu) }}</span>
            <span class="e-help">
              {{ [t('editor.menus.archived_on', {date: archivedAt(menu)}), actions.menuContents(menu)].filter(Boolean).join(' · ') }}
            </span>

            <button type="button"
                    class="e-btn e-btn-secondary h-8 px-2.5 mt-1.5"
                    @click="restore(menu)">
              <RotateCcw class="size-[15px]"/>
              {{ t('editor.actions.restore') }}
            </button>
          </div>

          <span class="e-pill bg-zinc-100 text-zinc-600 shrink-0">
            <Archive class="size-3"/>
            {{ t('editor.menus.archived_pill') }}
          </span>

          <DropdownMenu align="end">
            <template #trigger="{open, toggle: toggleMenu}">
              <button type="button"
                      class="e-icon-btn -mt-1"
                      aria-haspopup="menu"
                      :aria-expanded="open"
                      :aria-label="t('editor.panel.more_actions', {name: name(menu)})"
                      @click="toggleMenu">
                <Ellipsis class="size-[18px]"/>
              </button>
            </template>

            <button type="button" class="e-dropdown-item text-red-700" role="menuitem" @click="destroy(menu)">
              <Trash2 class="size-4"/>
              {{ t('editor.actions.delete_permanently') }}
            </button>
          </DropdownMenu>
        </div>
      </template>
    </section>

    <InfoBox>
      <i18n-t keypath="editor.menus.info" scope="global">
        <template #hide><b class="font-semibold">{{ t('editor.menus.info_hide') }}</b></template>
        <template #archive><b class="font-semibold">{{ t('editor.menus.info_archive') }}</b></template>
        <template #delete><b class="font-semibold">{{ t('editor.menus.info_delete') }}</b></template>
      </i18n-t>
    </InfoBox>
  </PanelShell>
</template>
