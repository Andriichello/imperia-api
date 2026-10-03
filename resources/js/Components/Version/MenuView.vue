<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ChevronRight} from 'lucide-vue-next'
  import PropRow from '@/Components/Version/PropRow.vue'
  import TextRow from '@/Components/Version/TextRow.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import {fieldsFor} from '@/version/fields'
  import {liveOf, TreeNode} from '@/version/model'
  import {translated} from '@/editor/translations'
  import {useVersionStore} from '@/stores/version'

  /**
   * A menu on the version's page: whether guests see it (or it's archived), its texts, and its
   * categories with their changes. Archiving it tells what goes off the site with it.
   */
  const props = defineProps({
    node: {
      type: Object as PropType<TreeNode>,
      required: true,
    },
    // "Sun 1 Nov, 00:00"
    date: {
      type: String,
      required: true,
    },
    // the version has a date
    dated: {
      type: Boolean,
      default: true,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  type Status = 'shown' | 'hidden' | 'archived'

  const live = computed(() => liveOf('dish-menus', props.node.item, store.restaurant!))
  const field = computed(() => fieldsFor(props.node.target!, live.value, props.node.change))

  const statusOf = (hidden: unknown, archived: unknown): Status => archived ? 'archived' : (hidden ? 'hidden' : 'shown')

  const hidden = computed(() => field.value<boolean>('is_hidden'))
  const archived = computed(() => field.value<boolean>('archived'))

  const liveStatus = computed(() => statusOf(hidden.value.live, archived.value.live))
  const status = computed(() => statusOf(hidden.value.value, archived.value.value))

  function setStatus(value: Status) {
    store.editFields(props.node.target!, {
      is_hidden: value === 'hidden' ? true : (value === 'shown' ? false : hidden.value.value),
      archived: value === 'archived',
    })
  }

  // what goes off the site with an archived menu
  const contents = computed(() => {
    const categories = props.node.children
    const dishes = categories.reduce((count, category) => count + category.children.length, 0)
    const names = categories.slice(0, 2).map((category) => category.name).join(', ')

    return {
      categories: t('admin.version.categories', {names}, categories.length),
      dishes: t('editor.structure.dishes_count', dishes),
    }
  })

  const description = (node: TreeNode) => translated((node.item as { description?: never } | null)?.description, store.defaultLocale)
</script>

<template>
  <PropRow :label="t('admin.version.status')"
           :changed="status !== liveStatus"
           @revert="store.revert(node.target!, ['is_hidden', 'archived'])">
    <template #live>{{ t('admin.version.statuses.' + liveStatus) }}</template>

    <select class="e-input h-9"
            :class="{'e-changed': status !== liveStatus}"
            :value="status"
            :disabled="store.readOnly"
            @change="setStatus(($event.target as HTMLSelectElement).value as Status)">
      <option :value="item" v-for="item in ['shown', 'hidden', 'archived']" :key="item">
        {{ t('admin.version.statuses.' + item) }}
      </option>
    </select>
  </PropRow>

  <TextRow :field="field('title')" :label="t('editor.menu.name')"/>
  <TextRow :field="field('description')" :label="t('editor.menu.description')" multiline :maxlength="1000"/>

  <div class="px-3 pt-4" v-if="status === 'archived' && liveStatus !== 'archived'">
    <InfoBox>
      {{ t(dated ? 'admin.version.archive_info' : 'admin.version.archive_info_undated', {date, categories: contents.categories, dishes: contents.dishes}) }}
    </InfoBox>
  </div>

  <p class="px-3 pt-3.5 pb-1.5 e-section">{{ t('admin.version.categories_title') }}</p>

  <p class="px-3 py-3 text-[13px] text-zinc-500" v-if="!node.children.length">{{ t('editor.menu.no_categories') }}</p>

  <button type="button"
          class="w-full grid grid-cols-[minmax(0,1fr)_auto_20px] items-center gap-3 px-3 py-2 border-b border-[#f0f0f1] text-start hover:bg-zinc-50 e-focus"
          v-for="category in node.children" :key="category.key"
          @click="store.select(category.key)">
    <span class="flex flex-col min-w-0">
      <span class="font-semibold truncate">{{ category.name }}</span>
      <span class="e-help truncate" v-if="description(category)">{{ description(category) }}</span>
    </span>
    <span class="text-xs font-semibold text-[#a16207]">{{ category.count ? t('admin.version.changes', category.count) : '' }}</span>
    <ChevronRight class="size-4 text-zinc-400"/>
  </button>
</template>
