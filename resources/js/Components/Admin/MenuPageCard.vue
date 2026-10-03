<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {BookOpen, Check, Clock, Copy, Globe, Pencil, QrCode} from 'lucide-vue-next'
  import type {EditorDashboard} from '@/api'
  import QrCodeDialog from '@/Components/Admin/QrCodeDialog.vue'
  import {formatDayMonth, formatTime} from '@/admin/format'
  import {languageName, translated} from '@/editor/translations'

  /**
   * The restaurant's public page: its address, the editor, the link and its QR code, and what's on it.
   */
  const props = defineProps({
    restaurant: {type: Object as PropType<EditorDashboard>, required: true},
    editorUrl: {type: String, required: true},
  })

  const {t, locale} = useI18n()

  // "localhost/en/web/" and the restaurant's own part, "smak"
  const address = computed(() => {
    const url = new URL(props.restaurant.url)
    const path = `${url.host}${url.pathname}`.replace(/\/$/, '')
    const at = path.lastIndexOf('/') + 1

    return {base: path.slice(0, at), own: path.slice(at)}
  })

  const name = computed(() => translated(props.restaurant.name, props.restaurant.default_locale))

  const languages = computed(() => props.restaurant.supported_locales.map(languageName).join(', '))

  const counts = computed(() => [
    t('admin.dashboard.page.menus', props.restaurant.menus_count),
    t('admin.dashboard.page.dishes', props.restaurant.dishes_count),
  ].join(' · '))

  const saved = computed(() => {
    if (!props.restaurant.last_saved_at) {
      return t('admin.dashboard.page.saved.never')
    }

    const at = DateTime.fromISO(props.restaurant.last_saved_at, {setZone: true})
    const today = DateTime.now().setZone(at.zone)
    const time = formatTime(at)

    let text = t('admin.dashboard.page.saved.date', {date: formatDayMonth(at, locale.value), time})

    if (at.hasSame(today, 'day')) {
      text = t('admin.dashboard.page.saved.today', {time})
    } else if (at.hasSame(today.minus({days: 1}), 'day')) {
      text = t('admin.dashboard.page.saved.yesterday', {time})
    }

    const by = props.restaurant.last_saved_by?.name

    return by ? t('admin.dashboard.page.saved.by', {saved: text, name: by}) : text
  })

  const copied = ref(false)
  const qrOpen = ref(false)

  async function copyLink() {
    await navigator.clipboard.writeText(props.restaurant.url)

    copied.value = true
    setTimeout(() => copied.value = false, 2000)
  }
</script>

<template>
  <section class="e-card col-span-2 max-lg:col-span-1 flex gap-6 overflow-hidden min-h-[248px]">
    <div class="flex-1 min-w-0 flex flex-col gap-3.5 py-[22px] pl-6 max-lg:pr-6">
      <p class="e-section">{{ t('admin.dashboard.page.title') }}</p>

      <div class="flex items-center gap-2.5 flex-wrap">
        <span class="text-[26px]/[34px] font-bold tracking-[-0.01em] break-all">
          {{ address.base }}<span class="text-green-700">{{ address.own }}</span>
        </span>

        <span class="e-pill bg-green-50 text-green-800" v-if="restaurant.menus_count > 0">
          <span class="size-1.5 rounded-full bg-green-600" aria-hidden="true"/>
          {{ t('admin.dashboard.page.live') }}
        </span>
        <span class="e-pill bg-zinc-100 text-zinc-600" v-else>
          {{ t('admin.dashboard.page.not_live') }}
        </span>
      </div>

      <p class="max-w-[460px] text-sm text-zinc-600">{{ t('admin.dashboard.page.help') }}</p>

      <div class="flex flex-wrap gap-2">
        <a class="e-btn e-btn-primary h-10! px-4!" :href="editorUrl">
          <Pencil class="size-4"/>
          {{ t('admin.dashboard.page.open_editor') }}
        </a>

        <button type="button"
                class="e-btn e-btn-secondary h-10!"
                @click="copyLink">
          <Check class="size-4" v-if="copied"/>
          <Copy class="size-4" v-else/>
          <span aria-live="polite">{{ t(copied ? 'admin.dashboard.page.copied' : 'admin.dashboard.page.copy_link') }}</span>
        </button>

        <button type="button"
                class="e-btn e-btn-secondary h-10!"
                aria-haspopup="dialog"
                @click="qrOpen = true">
          <QrCode class="size-4"/>
          {{ t('admin.dashboard.page.qr_code') }}
        </button>
      </div>

      <div class="mt-0.5 flex flex-wrap gap-x-4 gap-y-1 text-[13px] text-zinc-500">
        <span class="inline-flex items-center gap-1.5">
          <BookOpen class="size-3.5"/>
          {{ counts }}
        </span>
        <span class="inline-flex items-center gap-1.5">
          <Globe class="size-3.5"/>
          {{ languages }}
        </span>
        <span class="inline-flex items-center gap-1.5">
          <Clock class="size-3.5"/>
          {{ saved }}
        </span>
      </div>
    </div>

    <!-- the page itself, as guests see it -->
    <div class="relative w-60 shrink-0 self-stretch overflow-hidden max-lg:hidden" aria-hidden="true">
      <div class="absolute top-6 left-3 w-[390px] h-[900px] origin-top-left scale-[0.55] bg-white shadow-[0_0_0_1px_#d4d4d8,0_16px_40px_-16px_rgba(24,24,27,0.35)]">
        <iframe class="size-full pointer-events-none"
                :src="restaurant.url"
                :title="name"
                tabindex="-1"
                loading="lazy"/>
      </div>
    </div>

    <QrCodeDialog :url="restaurant.url" :name="name" v-model="qrOpen"/>
  </section>
</template>
