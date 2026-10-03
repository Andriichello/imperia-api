<script setup lang="ts">
  import {computed, nextTick, onMounted, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {CalendarClock} from 'lucide-vue-next'
  import type {EditorVersion} from '@/api'
  import {formatDateTime} from '@/admin/format'
  import {describeVersion} from '@/admin/versions'
  import {describeDraft} from '@/editor/changes'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Schedule the drafts for later instead of saving them: in a version of their own at a date
   * and time (the restaurant's), or in a version, which is planned already. Guests keep seeing
   * the current page till then.
   */
  const editor = useEditorStore()
  const {t, locale} = useI18n()

  /** How many drafts the summary names. */
  const SHOWN = 3

  const restaurant = computed(() => editor.restaurant!)
  const zone = computed(() => restaurant.value.timezone || 'Europe/Kyiv')
  const now = () => DateTime.now().setZone(zone.value)

  // what's scheduled, and what stays a draft (a version can't change it)
  const scheduled = computed(() => editor.schedulable.map(({entry}) => describeDraft(entry, {
    t,
    restaurant: restaurant.value,
    locale: locale.value,
  })))

  const staying = computed(() => editor.unsaved
    .filter((entry) => !editor.schedulable.some((item) => item.entry.key === entry.key && item.scheduling.rest === null))
    .map((entry) => describeDraft(entry, {t, restaurant: restaurant.value, locale: locale.value}).name))

  // versions, which haven't gone live
  const versions = computed<EditorVersion[]>(() => editor.versions.filter((version) => version.status !== 'applied'
    && version.status !== 'failed'))

  const target = ref<number | 'own'>('own')
  // tomorrow at 08:00 in the restaurant's time zone
  const date = ref(now().plus({days: 1}).toISODate() as string)
  const time = ref('08:00')
  const error = ref<string | null>(null)
  const dateInput = ref<HTMLInputElement | null>(null)

  const goesLiveAt = computed(() => DateTime.fromISO(`${date.value}T${time.value}`, {zone: zone.value}))

  const inFuture = computed(() => goesLiveAt.value.isValid && goesLiveAt.value > now())

  /** "Kyiv time (UTC+03:00)" */
  const zoneNote = computed(() => {
    const city = (zone.value.split('/').pop() ?? zone.value).replace(/_/g, ' ')

    return t('editor.schedule.zone', {city, offset: goesLiveAt.value.isValid ? goesLiveAt.value.toFormat('ZZ') : now().toFormat('ZZ')})
  })

  function versionName(version: EditorVersion): string {
    return describeVersion(version, {t, locale: locale.value, currency: restaurant.value.currency}).name
  }

  /** "Version · Sun 1 Nov, 00:00 · 14 changes" */
  function versionMeta(version: EditorVersion): string {
    const when = version.goes_live_at
      ? formatDateTime(DateTime.fromISO(version.goes_live_at, {setZone: true}), locale.value)
      : t('editor.schedule.no_date')

    return [t('editor.schedule.status.' + version.status), when, t('admin.dashboard.versions.changes', version.changes_count)].join(' · ')
  }

  const canSchedule = computed(() => scheduled.value.length > 0 && !editor.saving
    && (target.value !== 'own' || inFuture.value))

  async function schedule() {
    error.value = null
    error.value = await editor.scheduleDrafts(target.value === 'own'
      ? {goesLiveAt: goesLiveAt.value.toFormat('yyyy-MM-dd HH:mm')}
      : {versionId: target.value})
  }

  function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
      event.preventDefault()
      event.stopPropagation()
      editor.scheduleOpen = false
    }
  }

  onMounted(() => nextTick(() => dateInput.value?.focus()))
</script>

<template>
  <div class="absolute left-full bottom-2 z-30 ml-2 w-[340px] max-h-[calc(100vh-80px)] overflow-y-auto flex flex-col gap-3 p-4 rounded-xl bg-white border border-zinc-200 shadow-[0_20px_48px_-12px_rgba(24,24,27,0.35)] max-[800px]:left-3 max-[800px]:right-3 max-[800px]:bottom-16 max-[800px]:ml-0 max-[800px]:w-auto"
       role="dialog"
       :aria-label="t('editor.schedule.title')"
       data-keeps-review
       @keydown="onKeydown">
    <div class="flex flex-col gap-0.5">
      <p class="text-[15px]/5 font-semibold">{{ t('editor.schedule.title') }}</p>
      <p class="e-help">{{ t('editor.schedule.help') }}</p>
    </div>

    <ul class="flex flex-col gap-1 px-3 py-2.5 rounded-md bg-zinc-100 text-[13px]/[18px] text-zinc-700"
        v-if="scheduled.length">
      <li class="truncate" v-for="item in scheduled.slice(0, SHOWN)" :key="item.entry.key">
        <b class="font-semibold text-zinc-900">{{ item.name }}</b> · {{ item.summary }}
      </li>
      <li class="text-zinc-500" v-if="scheduled.length > SHOWN">
        {{ t('editor.schedule.more', scheduled.length - SHOWN) }}
      </li>
    </ul>

    <p class="px-3 py-2.5 rounded-md bg-zinc-100 text-[13px]/[18px] text-zinc-600" v-else>
      {{ t('editor.schedule.nothing') }}
    </p>

    <p class="text-xs/4 text-[#a16207]" v-if="staying.length">
      {{ t('editor.schedule.staying', {names: staying.join(', ')}, staying.length) }}
    </p>

    <div class="flex flex-col gap-2"
         role="radiogroup"
         :aria-label="t('editor.schedule.where')">
      <label class="flex items-start gap-2.5 px-3 py-2.5 rounded-lg border cursor-pointer"
             :class="target === 'own' ? 'border-blue-600 bg-blue-50 shadow-[0_0_0_1px_#2563eb]' : 'border-zinc-200 hover:bg-zinc-50'">
        <input class="mt-1 accent-blue-600" type="radio" value="own" v-model="target"/>
        <span class="flex flex-col">
          <span class="font-semibold">{{ t('editor.schedule.own') }}</span>
          <span class="e-help">{{ t('editor.schedule.own_help') }}</span>
        </span>
      </label>

      <label class="flex items-start gap-2.5 px-3 py-2.5 rounded-lg border cursor-pointer"
             :class="target === version.id ? 'border-blue-600 bg-blue-50 shadow-[0_0_0_1px_#2563eb]' : 'border-zinc-200 hover:bg-zinc-50'"
             v-for="version in versions" :key="version.id">
        <input class="mt-1 accent-blue-600" type="radio" :value="version.id" v-model="target"/>
        <span class="min-w-0 flex flex-col">
          <span class="font-semibold truncate">{{ t('editor.schedule.add_to', {name: versionName(version)}) }}</span>
          <span class="e-help">{{ versionMeta(version) }}</span>
        </span>
      </label>
    </div>

    <template v-if="target === 'own'">
      <div class="grid grid-cols-[1fr_128px] gap-2.5">
        <label class="flex flex-col gap-1.5">
          <span class="e-label">{{ t('editor.schedule.date') }}</span>
          <input class="e-input" type="date" ref="dateInput" :min="now().toISODate()!" v-model="date"/>
        </label>

        <label class="flex flex-col gap-1.5">
          <span class="e-label">{{ t('editor.schedule.time') }}</span>
          <input class="e-input" type="time" v-model="time"/>
        </label>
      </div>

      <p class="e-help">
        {{ zoneNote }}
        <span class="text-red-700" v-if="!inFuture">{{ t('editor.schedule.in_past') }}</span>
      </p>
    </template>

    <p class="e-error" role="alert" v-if="error">{{ error }}</p>

    <div class="flex justify-end gap-2">
      <button type="button"
              class="e-btn e-btn-secondary"
              @click="editor.scheduleOpen = false">
        {{ t('editor.confirm.cancel') }}
      </button>

      <button type="button"
              class="e-btn e-btn-primary"
              :disabled="!canSchedule"
              @click="schedule">
        <CalendarClock class="size-4"/>
        {{ editor.saving ? t('editor.schedule.scheduling') : t('editor.schedule.schedule') }}
      </button>
    </div>
  </div>
</template>
