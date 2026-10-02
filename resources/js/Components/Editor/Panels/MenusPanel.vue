<script setup lang="ts">
  import {computed, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {
    Archive,
    ChevronDown,
    ChevronRight,
    Copy,
    Ellipsis,
    Eye,
    EyeOff,
    GripVertical,
    Pencil,
    Plus,
    RotateCcw,
    Trash2,
  } from 'lucide-vue-next'
  import {VueDraggable} from 'vue-draggable-plus'
  import type {EditorMenu} from '@/api'
  import {orderEditorMenus} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useItemActions} from '@/composables/useItemActions'
  import {applyMenuOrder, isListed, MenuOrder, menuOrderOf, menusPreview} from '@/editor/menuDrafts'
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

  const {draft, dirty, saving, failed, discard, save} = usePanelDraft<MenuOrder[]>({
    saved: () => menuOrderOf(restaurant.value.menus),
    save: async (order) => (await orderEditorMenus(restaurant.value.id, {menus: order})).data.data,
    preview: (order, locale) => menusPreview(applyMenuOrder(restaurant.value.menus, order), locale, editor.defaultLocale),
  })

  const name = (item: { title: EditorMenu['title'] } | null) => item ? translated(item.title, editor.defaultLocale) : ''

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

  /**
   * The draft after something was done right away (archived, restored, copied): the menus and
   * categories, which are gone, are left out, new ones are added. The rest stays as it is.
   */
  function syncDraft() {
    const saved = menuOrderOf(restaurant.value.menus)
    const savedIds = new Set(saved.map((item) => item.id))
    const categories = new Set(saved.flatMap((item) => item.categories))

    const synced = draft.value
      .filter((item) => savedIds.has(item.id))
      .map((item) => ({...item, categories: item.categories.filter((id) => categories.has(id))}))

    const kept = new Set(synced.map((item) => item.id))
    synced.push(...saved.filter((item) => !kept.has(item.id)))

    const placed = new Set(synced.flatMap((item) => item.categories))

    for (const item of saved) {
      const target = synced.find((other) => other.id === item.id)!

      target.categories.push(...item.categories.filter((id) => !placed.has(id)))
    }

    draft.value = synced
  }

  // menus and categories changed right away (also from a toast's Undo)
  watch(() => JSON.stringify(menuOrderOf(restaurant.value.menus).map((item) => [item.id, item.categories])), syncDraft)

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
      ? DateTime.fromISO(menu.archived_at).setLocale(editor.locale).toLocaleString(DateTime.DATE_MED)
      : ''
  }
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.menus')"
              :subtitle="t('editor.subtitles.menus')"
              :dirty="dirty"
              :saving="saving"
              :failed="failed"
              @navigate="editor.close()"
              @close="editor.close()"
              @discard="discard"
              @save="save">
    <template #actions>
      <button type="button"
              class="e-btn e-btn-primary h-8 px-2.5"
              @click="editor.select({section: 'menu', id: null})">
        <Plus class="size-[15px]"/>
        {{ t('editor.menus.new') }}
      </button>
    </template>

    <section class="flex flex-col">
      <div class="flex flex-col items-start gap-3 py-2"
           v-if="!draft.length">
        <p class="text-zinc-500">{{ t('editor.structure.no_menus') }}</p>

        <button type="button"
                class="e-btn e-btn-secondary"
                @click="editor.select({section: 'menu', id: null})">
          <Plus class="size-[15px]"/>
          {{ t('editor.menus.first') }}
        </button>
      </div>

      <VueDraggable class="flex flex-col"
                    v-model="draft"
                    handle=".e-menu-grip"
                    ghost-class="e-drag-ghost"
                    :animation="150">
        <div v-for="item in draft" :key="item.id">
          <div class="flex items-center gap-1 min-h-[52px] py-1 pr-1 border-b border-[#f0f0f1]">
            <span class="e-grip e-menu-grip" :aria-label="t('editor.reorder')">
              <GripVertical class="size-4"/>
            </span>

            <button type="button"
                    class="e-icon-btn w-6"
                    :aria-expanded="expanded.has(item.id)"
                    :aria-label="t(expanded.has(item.id) ? 'editor.structure.hide_categories' : 'editor.structure.show_categories', {menu: name(editor.findMenu(item.id))})"
                    @click="toggle(item.id)">
              <ChevronDown class="size-4" v-if="expanded.has(item.id)"/>
              <ChevronRight class="size-4" v-else/>
            </button>

            <button type="button"
                    class="flex-1 min-w-0 flex flex-col text-start rounded e-focus"
                    @click="toggle(item.id)">
              <span class="font-semibold truncate" :class="{'text-zinc-500': item.is_hidden}">
                {{ name(editor.findMenu(item.id)) }}
              </span>
              <span class="text-xs text-zinc-500">{{ meta(item) }}</span>
            </button>

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
              <div class="h-10 flex items-center gap-1 pl-7 pr-1 rounded-md hover:bg-zinc-50"
                   v-for="id in item.categories" :key="id">
                <span class="e-grip e-category-grip h-7" :aria-label="t('editor.reorder')">
                  <GripVertical class="size-4"/>
                </span>

                <button type="button"
                        class="flex-1 min-w-0 h-full flex items-center gap-2 text-start rounded e-focus"
                        @click="editor.select({section: 'category', id}, true)">
                  <span class="truncate"
                        :class="{'text-zinc-400': editor.findCategory(id)?.is_hidden}">
                    {{ name(editor.findCategory(id)) }}
                  </span>

                  <EyeOff class="size-3.5 shrink-0 text-zinc-400"
                          :aria-label="t('editor.structure.hidden')"
                          v-if="editor.findCategory(id)?.is_hidden"/>

                  <span class="ml-auto shrink-0 text-[13px] text-zinc-500">{{ dishCount(id) }}</span>
                  <ChevronRight class="size-4 shrink-0 text-zinc-400"/>
                </button>
              </div>
            </VueDraggable>

            <button type="button"
                    class="h-9 flex items-center gap-2 pl-14 pr-2.5 text-blue-600 font-semibold rounded e-focus"
                    @click="editor.select({section: 'category', id: null, parent: item.id})">
              <Plus class="size-4"/>
              {{ t('editor.menus.add_category') }}
            </button>
          </div>
        </div>
      </VueDraggable>
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
        <div class="flex items-center gap-2.5 py-2.5 pl-3 pr-2 rounded-lg bg-zinc-50 border border-[#f0f0f1]"
             v-for="menu in archived" :key="menu.id">
          <span class="flex-1 min-w-0 flex flex-col">
            <span class="font-semibold text-zinc-600 truncate">{{ name(menu) }}</span>
            <span class="e-help">
              {{ [t('editor.menus.archived_on', {date: archivedAt(menu)}), actions.menuContents(menu)].filter(Boolean).join(' · ') }}
            </span>
          </span>

          <button type="button"
                  class="e-btn e-btn-secondary h-8 px-2.5"
                  @click="restore(menu)">
            <RotateCcw class="size-[15px]"/>
            {{ t('editor.actions.restore') }}
          </button>

          <button type="button"
                  class="e-icon-btn text-red-700 hover:text-red-800"
                  :aria-label="t('editor.actions.delete_for_good', {name: name(menu)})"
                  :title="t('editor.actions.delete_for_good', {name: name(menu)})"
                  @click="destroy(menu)">
            <Trash2 class="size-4"/>
          </button>
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
