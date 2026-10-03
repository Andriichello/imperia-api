<script setup lang="ts">
  import {nextTick, ref, watch} from 'vue'

  /**
   * A question before something, which can't be undone or takes effect right away.
   */
  const props = defineProps({
    title: {type: String, required: true},
    message: {type: String, required: true},
    confirm: {type: String, required: true},
    cancel: {type: String, required: true},
    danger: {type: Boolean, default: false},
    // whether it's open
    modelValue: {type: Boolean, default: false},
  })

  const emit = defineEmits<{ confirmed: [], 'update:modelValue': [open: boolean] }>()

  const close = () => emit('update:modelValue', false)

  const dialog = ref<HTMLDialogElement | null>(null)
  const confirmButton = ref<HTMLButtonElement | null>(null)

  watch(() => props.modelValue, async (value) => {
    if (value) {
      dialog.value?.showModal()
      await nextTick()
      confirmButton.value?.focus()
    } else {
      dialog.value?.close()
    }
  })

  function confirmed() {
    close()
    emit('confirmed')
  }
</script>

<template>
  <dialog class="m-auto w-[400px] p-0 rounded-xl bg-white text-zinc-900 shadow-[0_24px_48px_-12px_rgba(24,24,27,0.4)] backdrop:bg-zinc-900/40"
          ref="dialog"
          @close="close">
    <div class="p-5 flex flex-col gap-2">
      <h2 class="text-base font-semibold">{{ title }}</h2>
      <p class="text-[13px]/5 text-zinc-600">{{ message }}</p>

      <div class="mt-3 flex justify-end gap-2">
        <button type="button" class="e-btn e-btn-secondary" @click="close">{{ cancel }}</button>
        <button type="button"
                class="e-btn"
                :class="danger ? 'bg-red-600 text-white' : 'e-btn-primary'"
                ref="confirmButton"
                @click="confirmed">
          {{ confirm }}
        </button>
      </div>
    </div>
  </dialog>
</template>
