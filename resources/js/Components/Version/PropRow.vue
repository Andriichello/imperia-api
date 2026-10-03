<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ArrowRight, Undo2} from 'lucide-vue-next'
  import {useVersionStore} from '@/stores/version'

  /**
   * A field of an item on the version's page: its live value, and its value from the version's
   * date (a control, which changes it). A changed one is amber and can be reverted to the live one.
   */
  defineProps({
    label: {
      type: String,
      required: true,
    },
    // the language of a translated field
    locale: {
      type: String as PropType<string | null>,
      default: null,
    },
    changed: {
      type: Boolean,
      default: false,
    },
    // the live value isn't the one, which the change was planned for
    conflict: {
      type: String as PropType<string | null>,
      default: null,
    },
    error: {
      type: String as PropType<string | null>,
      default: null,
    },
    revertable: {
      type: Boolean,
      default: true,
    },
    // a row of several lines (a text, photos) starts at the top
    top: {
      type: Boolean,
      default: false,
    },
  })

  const emits = defineEmits<{
    (e: 'revert'): void
  }>()

  const store = useVersionStore()
  const {t} = useI18n()
</script>

<template>
  <div class="e-vrow"
       :class="[top ? 'items-start' : 'items-center', {'e-vrow-changed': changed}]">
    <span class="flex items-center gap-1.5 min-w-0" :class="{'pt-2': top}">
      <span class="e-label">{{ label }}</span>
      <span class="e-locale-chip" :title="t('editor.fields.translated')" v-if="locale">{{ locale }}</span>
      <slot name="label"/>
    </span>

    <div class="min-w-0 text-[13px]/[18px] text-zinc-600" :class="{'pt-2': top}">
      <slot name="live"/>
      <p class="mt-0.5 text-xs text-[#a16207]" v-if="conflict">{{ conflict }}</p>
    </div>

    <span class="flex justify-center" :class="{'pt-2': top}">
      <ArrowRight class="size-4" :class="changed ? 'text-amber-500' : 'text-zinc-300'"/>
    </span>

    <div class="min-w-0">
      <slot/>
      <p class="mt-1 e-error" v-if="error">{{ error }}</p>
    </div>

    <div class="flex justify-end gap-0.5" :class="{'pt-1': top}">
      <slot name="actions"/>

      <button type="button"
              class="e-icon-btn size-7"
              :aria-label="t('admin.version.revert_field', {field: label})"
              :title="t('admin.version.revert')"
              v-if="changed && revertable && !store.readOnly"
              @click="emits('revert')">
        <Undo2 class="size-4"/>
      </button>
    </div>
  </div>
</template>
