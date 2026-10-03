<script setup lang="ts">
  import {computed, onBeforeUnmount, onMounted, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {X} from 'lucide-vue-next'
  import {usePreviewBridge} from '@/composables/usePreviewBridge'
  import {formatDateTime} from '@/admin/format'
  import type {PreviewMessage} from '@/editor/protocol'
  import {versionPreview} from '@/version/preview'
  import {useVersionStore} from '@/stores/version'

  /**
   * The public page as it will be at the version's date: the real page in a phone, with the
   * version's changes (it works like for guests).
   */
  const store = useVersionStore()
  const {t, locale} = useI18n()

  const frame = ref<HTMLIFrameElement | null>(null)
  const previewLocale = ref(store.defaultLocale)

  const src = computed(() => `/${previewLocale.value}/web/${store.restaurant!.id}?editor=1`)

  const date = computed(() => store.version?.goes_live_at
    ? formatDateTime(DateTime.fromISO(store.version.goes_live_at, {setZone: true}), locale.value)
    : null)

  const {post} = usePreviewBridge(frame, onMessage)

  function onMessage(message: PreviewMessage) {
    if (message.type === 'editor:ready') {
      const {patch, brand} = versionPreview(store.restaurant!, store.version!, store.photos, previewLocale.value)

      post({type: 'editor:mode', mode: 'browse'})
      post({type: 'editor:draft', patch})
      post({type: 'editor:brand', brand})
    }
  }

  function close() {
    store.previewOpen = false
  }

  function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
      close()
    }
  }

  onMounted(() => window.addEventListener('keydown', onKeydown))
  onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-zinc-900/40"
       role="dialog"
       aria-modal="true"
       :aria-label="t('admin.version.preview')"
       @click.self="close">
    <div class="h-full max-h-[900px] flex flex-col rounded-xl bg-[#eef0f3] overflow-hidden shadow-[0_24px_48px_-12px_rgba(24,24,27,0.4)]"
         style="background-image: radial-gradient(#d4d4d8 1px, transparent 1px); background-size: 18px 18px">
      <div class="shrink-0 flex items-center gap-3 px-4 h-12 bg-white border-b border-zinc-200">
        <p class="flex-1 font-semibold truncate">
          {{ date ? t('admin.version.preview_at', {date}) : t('admin.version.preview') }}
        </p>

        <div class="e-seg" role="group" :aria-label="t('editor.toolbar.preview_language')" v-if="store.locales.length > 1">
          <button type="button"
                  class="px-2! uppercase e-focus"
                  :aria-pressed="previewLocale === item"
                  v-for="item in store.locales" :key="item"
                  @click="previewLocale = item">
            {{ item }}
          </button>
        </div>

        <button type="button" class="e-icon-btn" :aria-label="t('editor.panel.close')" @click="close">
          <X class="size-[18px]"/>
        </button>
      </div>

      <div class="flex-1 min-h-0 flex justify-center px-10 py-4">
        <div class="w-[390px] h-full bg-white overflow-hidden shadow-[0_0_0_1px_#d4d4d8,0_16px_40px_-20px_rgba(24,24,27,0.4)]">
          <iframe class="block w-full h-full border-0"
                  ref="frame"
                  :src="src"
                  :key="src"
                  :title="t('admin.version.preview')"/>
        </div>
      </div>
    </div>
  </div>
</template>
