<script setup lang="ts">
  import {useI18n} from 'vue-i18n'
  import {X} from 'lucide-vue-next'
  import {Toast, useEditorStore} from '@/stores/editor'

  /**
   * Messages of what was done (archived, restored, moved), with an Undo where it can be.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  async function run(toast: Toast) {
    editor.dismiss(toast.id)
    await toast.action?.run()
  }
</script>

<template>
  <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 flex flex-col items-center gap-2"
       aria-live="polite">
    <div class="min-h-11 max-w-[520px] flex items-center gap-3 pl-4 pr-1.5 py-1.5 rounded-lg bg-zinc-900 text-white text-[13px]/[18px] shadow-[0_12px_32px_-8px_rgba(24,24,27,0.45)]"
         role="status"
         v-for="toast in editor.toasts" :key="toast.id">
      <span class="flex-1">{{ toast.message }}</span>

      <button type="button"
              class="h-8 px-2 rounded-md font-semibold text-blue-300 hover:bg-white/10 e-focus"
              v-if="toast.action"
              @click="run(toast)">
        {{ toast.action.label }}
      </button>

      <button type="button"
              class="size-8 flex items-center justify-center rounded-md text-zinc-400 hover:bg-white/10 hover:text-white e-focus"
              :aria-label="t('editor.toast.dismiss')"
              @click="editor.dismiss(toast.id)">
        <X class="size-4"/>
      </button>
    </div>
  </div>
</template>
