<script setup lang="ts">
  import {nextTick, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Download, X} from 'lucide-vue-next'

  /**
   * The QR code of the restaurant's page, to print on tables (the library is loaded, when it opens).
   */
  const props = defineProps({
    url: {type: String, required: true},
    name: {type: String, required: true},
    // whether it's open
    modelValue: {type: Boolean, default: false},
  })

  const emit = defineEmits<{ 'update:modelValue': [open: boolean] }>()
  const {t} = useI18n()

  const close = () => emit('update:modelValue', false)

  const dialog = ref<HTMLDialogElement | null>(null)
  const svg = ref('')

  watch(() => props.modelValue, async (value) => {
    if (!value) {
      dialog.value?.close()
      return
    }

    if (!svg.value) {
      const {default: QRCode} = await import(/* webpackChunkName: "admin-qr-code" */ 'qrcode')
      svg.value = await QRCode.toString(props.url, {type: 'svg', margin: 2, errorCorrectionLevel: 'M'})
    }

    await nextTick()
    dialog.value?.showModal()
  })

  function download() {
    const link = document.createElement('a')
    link.href = URL.createObjectURL(new Blob([svg.value], {type: 'image/svg+xml'}))
    link.download = `${props.name.replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-|-$/g, '') || 'menu'}-qr.svg`
    link.click()
    URL.revokeObjectURL(link.href)
  }
</script>

<template>
  <dialog class="m-auto w-[360px] p-0 rounded-xl bg-white text-zinc-900 shadow-[0_24px_48px_-12px_rgba(24,24,27,0.4)] backdrop:bg-zinc-900/40"
          ref="dialog"
          aria-labelledby="qr-title"
          @close="close">
    <div class="p-5 flex flex-col gap-3">
      <div class="flex items-start gap-2">
        <h2 class="flex-1 text-base font-semibold" id="qr-title">{{ t('admin.dashboard.qr.title') }}</h2>
        <button type="button"
                class="e-icon-btn -mt-1 -mr-1"
                :aria-label="t('admin.dashboard.qr.close')"
                @click="close">
          <X class="size-4"/>
        </button>
      </div>

      <div class="mx-auto size-60 [&>svg]:size-full" v-html="svg"/>

      <p class="text-[13px]/[18px] text-zinc-500 text-center break-all">{{ url }}</p>
      <p class="text-[13px]/[18px] text-zinc-600">{{ t('admin.dashboard.qr.help') }}</p>

      <button type="button"
              class="e-btn e-btn-primary"
              @click="download">
        <Download class="size-4"/>
        {{ t('admin.dashboard.qr.download') }}
      </button>
    </div>
  </dialog>
</template>
