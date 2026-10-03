<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import PropRow from '@/Components/Version/PropRow.vue'
  import TextRow from '@/Components/Version/TextRow.vue'
  import PhotoStrip from '@/Components/Version/PhotoStrip.vue'
  import SizeRows from '@/Components/Version/SizeRows.vue'
  import FlagRows from '@/Components/Version/FlagRows.vue'
  import ToggleSwitch from '@/Components/Editor/Fields/ToggleSwitch.vue'
  import {fieldsFor} from '@/version/fields'
  import {liveOf, TreeNode} from '@/version/model'
  import {useVersionStore} from '@/stores/version'

  /**
   * A dish on the version's page: whether guests see it, its photos, texts, sizes, diet and
   * allergens, live and from the version's date.
   */
  const props = defineProps({
    node: {
      type: Object as PropType<TreeNode>,
      required: true,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  /** The most photos of a dish. */
  const MAX_PHOTOS = 3

  const live = computed(() => props.node.item ? liveOf('dishes', props.node.item, store.restaurant!) : null)

  const field = computed(() => fieldsFor(props.node.target!, live.value, props.node.change))

  const hidden = computed(() => field.value<boolean>('is_hidden'))
  const flags = computed(() => field.value<string[] | null>('flags'))
  const media = computed(() => field.value<{ id: number, is_hidden: boolean }[] | null>('media'))
</script>

<template>
  <PropRow :label="t('admin.version.on_the_menu')"
           :changed="hidden.changed"
           :conflict="hidden.conflict"
           :revertable="!node.isNew"
           @revert="hidden.revert()">
    <template #live>
      {{ node.isNew ? t('admin.version.not_yet') : t(hidden.live ? 'admin.version.hidden' : 'admin.version.shown') }}
    </template>

    <span class="inline-flex items-center gap-2">
      <ToggleSwitch :model-value="!hidden.value"
                    :label="t('admin.version.on_the_menu')"
                    :disabled="store.readOnly"
                    @update:model-value="hidden.set(!$event, 0)"/>
      <span class="text-[13px] text-zinc-700">{{ t(hidden.value ? 'admin.version.hidden' : 'admin.version.shown') }}</span>
    </span>
  </PropRow>

  <PhotoStrip :field="media" :max="MAX_PHOTOS" :is-new="node.isNew"/>

  <TextRow :field="field('title')" :label="t('editor.dish.name')" :is-new="node.isNew"/>
  <TextRow :field="field('description')" :label="t('editor.dish.description')" multiline :maxlength="300" :is-new="node.isNew"/>
  <TextRow :field="field('badge')" :label="t('editor.dish.badge')" :maxlength="25" :is-new="node.isNew"/>

  <SizeRows :dish="node"/>

  <FlagRows :field="flags" :is-new="node.isNew"/>
</template>
