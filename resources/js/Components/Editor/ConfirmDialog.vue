<script setup lang="ts">
  import {nextTick, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The editor's questions (see `editor.confirm()`), e.g. before something is deleted.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const dialog = ref<HTMLDialogElement | null>(null)
  const confirmButton = ref<HTMLButtonElement | null>(null)

  watch(() => editor.confirmation, async (question) => {
    if (question) {
      dialog.value?.showModal()
      await nextTick()
      confirmButton.value?.focus()
    } else {
      dialog.value?.close()
    }
  })
</script>

<template>
  <dialog class="m-auto w-[400px] p-0 rounded-xl bg-white text-zinc-900 shadow-[0_24px_48px_-12px_rgba(24,24,27,0.4)] backdrop:bg-zinc-900/40"
          ref="dialog"
          @keydown.esc.prevent="editor.answer(false)">
    <div class="p-5 flex flex-col gap-2"
         v-if="editor.confirmation">
      <h2 class="text-base font-semibold">{{ editor.confirmation.title }}</h2>
      <p class="text-[13px]/5 text-zinc-600">{{ editor.confirmation.message }}</p>

      <div class="mt-3 flex justify-end gap-2">
        <button type="button"
                class="e-btn e-btn-secondary"
                @click="editor.answer(false)">
          {{ t('editor.confirm.cancel') }}
        </button>

        <button type="button"
                class="e-btn"
                :class="editor.confirmation.danger ? 'bg-red-700 text-white' : 'e-btn-primary'"
                ref="confirmButton"
                @click="editor.answer(true)">
          {{ editor.confirmation.confirm }}
        </button>
      </div>
    </div>
  </dialog>
</template>
