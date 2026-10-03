<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import PropRow from '@/Components/Version/PropRow.vue'
  import type {VersionField} from '@/version/fields'
  import {useVersionStore} from '@/stores/version'

  /**
   * A translated text of an item (name, description, badge) in the language of the page.
   */
  type Texts = Record<string, string | null> | null

  const props = defineProps({
    field: {
      type: Object as PropType<VersionField<Texts>>,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    multiline: {
      type: Boolean,
      default: false,
    },
    maxlength: {
      type: Number,
      default: 255,
    },
    // a new item has no live value
    isNew: {
      type: Boolean,
      default: false,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  const locale = computed(() => store.contentLocale)

  const live = computed(() => props.isNew ? '—' : (props.field.live?.[locale.value] || t('admin.version.none')))

  const text = computed(() => props.field.value?.[locale.value] ?? '')

  // other languages show the default one's text, while theirs isn't written
  const placeholder = computed(() => locale.value === store.defaultLocale
    ? t('admin.version.none')
    : (props.field.value?.[store.defaultLocale] ?? ''))

  function input(value: string) {
    props.field.set({...(props.field.value ?? {}), [locale.value]: value.trim() === '' ? null : value})
  }
</script>

<template>
  <PropRow :label="label"
           :locale="locale"
           :changed="field.changed"
           :conflict="field.conflict"
           :error="field.error"
           :revertable="!isNew"
           :top="multiline"
           @revert="field.revert()">
    <template #live>
      <span :class="{'line-through decoration-amber-500': field.changed && !isNew && field.live?.[locale]}">{{ live }}</span>
    </template>

    <textarea class="e-input text-sm"
              rows="2"
              :class="{'e-changed': field.changed}"
              :maxlength="maxlength"
              :placeholder="placeholder"
              :value="text"
              :disabled="store.readOnly"
              v-if="multiline"
              @input="input(($event.target as HTMLTextAreaElement).value)"/>

    <input class="e-input h-9"
           type="text"
           :class="{'e-changed': field.changed}"
           :maxlength="maxlength"
           :placeholder="placeholder"
           :value="text"
           :disabled="store.readOnly"
           v-else
           @input="input(($event.target as HTMLInputElement).value)"/>
  </PropRow>
</template>
