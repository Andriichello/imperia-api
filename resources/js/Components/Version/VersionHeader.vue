<script setup lang="ts">
  import {computed, nextTick, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {CalendarClock, ChevronLeft, Copy, Eye, MoreHorizontal, Pencil, Play, Trash2} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import ConfirmDialog from '@/Components/Admin/ConfirmDialog.vue'
  import {formatDateTime, formatRelative} from '@/admin/format'
  import {useVersionStore, VersionAction} from '@/stores/version'

  /**
   * The version's header: its name (renamed in place) and status, when it goes live (in the
   * restaurant's time), how many changes it has, the preview, its status action, and Apply now,
   * Duplicate and Delete.
   */
  const store = useVersionStore()
  const {t, locale} = useI18n()

  const version = computed(() => store.version!)
  const zone = computed(() => version.value.timezone)

  const goesLiveAt = computed(() => version.value.goes_live_at
    ? DateTime.fromISO(version.value.goes_live_at, {setZone: true})
    : null)

  const STATUS_PILLS: Record<string, { pill: string, dot: string }> = {
    draft: {pill: 'bg-zinc-100 text-zinc-700', dot: 'bg-zinc-400'},
    scheduled: {pill: 'bg-blue-50 text-blue-700', dot: 'bg-blue-600'},
    inactive: {pill: 'bg-amber-50 text-[#a16207]', dot: 'bg-amber-500'},
    applied: {pill: 'bg-green-50 text-green-800', dot: 'bg-green-600'},
    failed: {pill: 'bg-red-50 text-red-800', dot: 'bg-red-600'},
  }

  // Name

  const renaming = ref(false)
  const name = ref('')
  const nameInput = ref<HTMLInputElement | null>(null)

  function startRenaming() {
    name.value = version.value.name ?? ''
    renaming.value = true
    nextTick(() => nameInput.value?.select())
  }

  async function rename() {
    if (renaming.value) {
      renaming.value = false

      if ((name.value.trim() || null) !== version.value.name) {
        await store.rename(name.value)
      }
    }
  }

  // When it goes live

  const date = computed(() => goesLiveAt.value?.toISODate() ?? '')
  const time = computed(() => goesLiveAt.value?.toFormat('HH:mm') ?? '')
  const dateInput = ref<HTMLInputElement | null>(null)

  /** "Sun, 1 Nov 2026" */
  const dateLabel = computed(() => goesLiveAt.value
    ? goesLiveAt.value.setLocale(locale.value === 'en' ? 'en-GB' : locale.value).toLocaleString({
      weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
    })
    : t('admin.version.pick_date'))

  /** "Kyiv time · in 29 days" */
  const liveNote = computed(() => {
    const city = (zone.value.split('/').pop() ?? zone.value).replace(/_/g, ' ')
    const when = version.value.status === 'inactive'
      ? t('admin.version.wont_go_live')
      : (version.value.status === 'applied' ? t('admin.version.went_live')
        : (goesLiveAt.value ? formatRelative(goesLiveAt.value, locale.value) : ''))

    return [t('admin.version.zone_time', {city}), when].filter(Boolean).join(' · ')
  })

  function reschedule(nextDate: string, nextTime: string) {
    if (/^\d{4}-\d{2}-\d{2}$/.test(nextDate) && /^\d{1,2}:\d{2}$/.test(nextTime)) {
      store.reschedule(`${nextDate} ${nextTime.padStart(5, '0')}`)
    }
  }

  function openDatePicker() {
    try {
      dateInput.value?.showPicker()
    } catch (e) {
      dateInput.value?.focus()
    }
  }

  // Actions

  const asking = ref<VersionAction | null>(null)
  const askingOpen = ref(false)

  function ask(action: VersionAction) {
    asking.value = action
    askingOpen.value = true
  }

  const statusAction = computed<{ action: VersionAction, primary: boolean } | null>(() => ({
    draft: {action: 'schedule' as const, primary: true},
    scheduled: {action: 'deactivate' as const, primary: false},
    inactive: {action: 'activate' as const, primary: true},
    failed: {action: 'schedule' as const, primary: true},
  } as Record<string, { action: VersionAction, primary: boolean }>)[version.value.status] ?? null)

  const savedState = computed(() => store.saving ? t('admin.version.saving') : t('admin.version.saved'))
</script>

<template>
  <div class="shrink-0 flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-2.5 bg-white border-b border-zinc-200">
    <a class="e-icon-btn border border-zinc-200"
       :href="store.urls!.dashboard"
       :aria-label="t('admin.version.back')"
       :title="t('admin.nav.dashboard')">
      <ChevronLeft class="size-[18px]"/>
    </a>

    <div class="min-w-0">
      <p class="e-help">{{ t('admin.version.title') }}</p>

      <div class="flex items-center gap-2">
        <input class="e-input h-[30px] w-60 text-[15px] font-semibold"
               type="text"
               maxlength="120"
               ref="nameInput"
               :aria-label="t('admin.version.name')"
               :placeholder="t('admin.version.unnamed')"
               v-model="name"
               v-if="renaming"
               @keydown.enter="rename"
               @keydown.esc="renaming = false"
               @blur="rename"/>

        <template v-else>
          <h1 class="text-lg/[26px] font-bold whitespace-nowrap truncate max-w-80">
            {{ version.name || t('admin.version.unnamed') }}
          </h1>

          <button type="button"
                  class="e-icon-btn size-[26px]"
                  :aria-label="t('admin.version.rename')"
                  :title="t('admin.version.rename')"
                  v-if="!store.readOnly"
                  @click="startRenaming">
            <Pencil class="size-3.5"/>
          </button>
        </template>

        <span class="e-pill" :class="STATUS_PILLS[version.status].pill">
          <span class="size-1.5 rounded-full" :class="STATUS_PILLS[version.status].dot" aria-hidden="true"/>
          {{ t('admin.version.statuses_version.' + version.status) }}
        </span>
      </div>
    </div>

    <div class="flex items-center gap-2 pl-3 pr-2.5 py-1.5 rounded-[10px] border border-zinc-200 bg-zinc-50"
         role="group"
         :aria-label="t('admin.version.goes_live')">
      <span class="e-label whitespace-nowrap">{{ t('admin.version.goes_live') }}</span>

      <span class="relative">
        <button type="button"
                class="e-input h-[34px] w-auto inline-flex items-center gap-2 whitespace-nowrap"
                :disabled="store.readOnly"
                @click="openDatePicker">
          <CalendarClock class="size-4 text-zinc-500"/>
          {{ dateLabel }}
        </button>

        <input class="absolute inset-0 opacity-0 pointer-events-none"
               type="date"
               tabindex="-1"
               ref="dateInput"
               :aria-label="t('admin.version.date')"
               :value="date"
               @change="reschedule(($event.target as HTMLInputElement).value, time || '00:00')"/>
      </span>

      <input class="e-input h-[34px] w-[70px] text-center tabular-nums"
             type="text"
             maxlength="5"
             placeholder="00:00"
             :aria-label="t('admin.version.time_of_day')"
             :value="time"
             :disabled="store.readOnly || !date"
             @change="reschedule(date, ($event.target as HTMLInputElement).value)"/>

      <span class="e-help whitespace-nowrap">{{ liveNote }}</span>
    </div>

    <div class="flex-1"/>

    <span class="e-help text-[13px] whitespace-nowrap">
      <template v-if="version.changes_count">
        <b class="font-semibold text-zinc-900">{{ t('admin.dashboard.versions.changes', version.changes_count) }}</b>
        {{ t('admin.version.in_items', version.items_count) }}
      </template>
      <template v-else>{{ t('admin.version.no_changes') }}</template>
      · {{ savedState }}
    </span>

    <button type="button"
            class="e-btn e-btn-secondary h-[34px]"
            @click="store.previewOpen = true">
      <Eye class="size-4"/>
      {{ t('admin.version.preview') }}
    </button>

    <button type="button"
            class="e-btn h-[34px]"
            :class="statusAction.primary ? 'e-btn-primary' : 'e-btn-secondary'"
            v-if="statusAction"
            @click="store.act(statusAction.action)">
      <CalendarClock class="size-4" v-if="statusAction.action === 'schedule'"/>
      {{ t('admin.version.actions.' + statusAction.action) }}
    </button>

    <DropdownMenu align="end" v-if="version.status !== 'applied'">
      <template #trigger="{open, toggle}">
        <button type="button"
                class="e-icon-btn size-[34px] border border-zinc-200"
                aria-haspopup="menu"
                :aria-expanded="open"
                :aria-label="t('admin.version.more')"
                @click="toggle">
          <MoreHorizontal class="size-4"/>
        </button>
      </template>

      <button type="button" class="e-dropdown-item" role="menuitem" @click="ask('apply')">
        <Play class="size-4 text-zinc-500"/>
        {{ t('admin.dashboard.versions.apply') }}
      </button>
      <button type="button" class="e-dropdown-item" role="menuitem" @click="store.act('duplicate')">
        <Copy class="size-4 text-zinc-500"/>
        {{ t('admin.dashboard.versions.duplicate') }}
      </button>
      <div class="h-px my-1 bg-[#f0f0f1]"/>
      <button type="button" class="e-dropdown-item text-red-700" role="menuitem" @click="ask('delete')">
        <Trash2 class="size-4"/>
        {{ t('admin.dashboard.versions.delete') }}
      </button>
    </DropdownMenu>

    <ConfirmDialog :title="t('admin.dashboard.versions.' + (asking === 'delete' ? 'confirm_delete' : 'confirm_apply'), {name: version.name || t('admin.version.unnamed')})"
                   :message="t('admin.dashboard.versions.' + (asking === 'delete' ? 'confirm_delete_message' : 'confirm_apply_message'))"
                   :confirm="t('admin.dashboard.versions.' + (asking === 'delete' ? 'delete' : 'apply'))"
                   :cancel="t('admin.dashboard.versions.cancel')"
                   :danger="asking === 'delete'"
                   v-model="askingOpen"
                   @confirmed="store.act(asking!)"/>
  </div>

  <div class="shrink-0 flex items-center gap-2 px-4 py-2 border-b text-[13px]"
       :class="{
         'bg-[#fffbeb] border-[#fde68a] text-[#92400e]': version.status === 'inactive',
         'bg-zinc-100 border-zinc-200 text-zinc-700': version.status === 'draft',
         'bg-red-50 border-red-200 text-red-800': version.status === 'failed',
         'bg-green-50 border-green-200 text-green-800': version.status === 'applied',
       }"
       role="status"
       v-if="version.status !== 'scheduled'">
    <template v-if="version.status === 'inactive'">
      {{ t('admin.version.banners.inactive', {count: version.changes_count, date: goesLiveAt ? formatDateTime(goesLiveAt, locale) : ''}) }}
    </template>
    <template v-else-if="version.status === 'draft'">{{ t('admin.version.banners.draft') }}</template>
    <template v-else-if="version.status === 'failed'">
      {{ t('admin.version.banners.failed', {reason: version.failure_reason ?? ''}) }}
    </template>
    <template v-else>
      {{ t('admin.version.banners.applied', {date: version.applied_at ? formatDateTime(DateTime.fromISO(version.applied_at, {setZone: true}), locale) : ''}) }}
    </template>
  </div>
</template>
