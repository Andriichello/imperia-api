<script setup lang="ts">
  import {computed, nextTick, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {Copy, Pencil, Plus, Trash2, X} from 'lucide-vue-next'
  import type {EditorInterval} from '@/api'
  import {ScheduleWeekday, updateEditorRestaurantHours} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import ToggleSwitch from '@/Components/Editor/Fields/ToggleSwitch.vue'
  import TimeInput from '@/Components/Editor/Fields/TimeInput.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {
    hoursOf,
    hoursPreview,
    hoursRequest,
    intervalOf,
    SpecialDayDraft,
    WEEKDAYS,
  } from '@/editor/drafts'
  import {languageName, translated, translationsOf} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The time zone, the weekly hours (with lunch breaks), special days, which override them,
   * and a temporary closure.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  /** The most intervals a day. */
  const MAX_INTERVALS = 3

  const restaurant = computed(() => editor.restaurant!)

  const {draft, dirty, errors, saving, failed, discard, save, error} = usePanelDraft({
    saved: () => hoursOf(restaurant.value),
    save: async (hours) => (await updateEditorRestaurantHours(restaurant.value.id, hoursRequest(hours))).data.data,
    preview: (hours, locale) => hoursPreview(hours, locale, editor.defaultLocale),
    canSave: () => canSave.value,
    lists: {exceptions: (hours) => hours.exceptions.map((day) => day.key)},
  })

  // the default language first
  const locales = computed(() => [...editor.locales]
    .sort((a, b) => Number(b === editor.defaultLocale) - Number(a === editor.defaultLocale)))

  // Time zone

  /** "Kyiv (UTC+03:00)" */
  function zoneLabel(zone: string, offset: number): string {
    const city = (zone.split('/').pop() ?? zone).replace(/_/g, ' ')
    const sign = offset < 0 ? '-' : '+'
    const hours = String(Math.floor(Math.abs(offset) / 60)).padStart(2, '0')
    const minutes = String(Math.abs(offset) % 60).padStart(2, '0')

    return `${city} (UTC${sign}${hours}:${minutes})`
  }

  const zones = computed(() => {
    const now = DateTime.now()
    const names: string[] = typeof Intl.supportedValuesOf === 'function' ? Intl.supportedValuesOf('timeZone') : []

    if (!names.includes(draft.value.timezone)) {
      names.push(draft.value.timezone)
    }

    return names
      .map((zone) => {
        const offset = now.setZone(zone).offset

        return {zone, offset, label: zoneLabel(zone, offset)}
      })
      .sort((a, b) => a.offset - b.offset || a.label.localeCompare(b.label))
  })

  // the restaurant's weekday now
  const today = computed(() => WEEKDAYS[DateTime.now().setZone(draft.value.timezone).weekday - 1])

  // Weekly hours

  const minutes = (hour: number, minute: number) => hour * 60 + minute

  /**
   * What's wrong with the hours of a day: an interval closes when it opens, or two overlap
   * (the same check as the server's).
   */
  function problemOf(intervals: EditorInterval[]): string | null {
    const ranges: number[][] = []

    for (const interval of intervals) {
      const beg = minutes(interval.beg_hour, interval.beg_minute)
      const end = minutes(interval.end_hour, interval.end_minute)

      if (beg === end) {
        return t('editor.hours.same_time')
      }

      const range = [beg, end < beg ? end + 24 * 60 : end]

      if (ranges.some((other) => range[0] < other[1] && other[0] < range[1])) {
        return t('editor.hours.overlap')
      }

      ranges.push(range)
    }

    return null
  }

  /** The first error of the day from saving, or what's wrong with it now. */
  function dayError(weekday: ScheduleWeekday): string | null {
    const saved = Object.entries(errors.value).find(([field]) => field.startsWith(`weekdays.${weekday}`))

    return problemOf(draft.value.weekdays[weekday]) ?? saved?.[1]?.[0] ?? null
  }

  // hours of days switched off, back when they're on again
  const switchedOff: Partial<Record<ScheduleWeekday, EditorInterval[]>> = {}

  function setOpen(weekday: ScheduleWeekday, open: boolean) {
    if (open) {
      draft.value.weekdays[weekday] = switchedOff[weekday]
        ?? [{beg_hour: 10, beg_minute: 0, end_hour: 22, end_minute: 0}]
    } else {
      switchedOff[weekday] = draft.value.weekdays[weekday]
      draft.value.weekdays[weekday] = []
    }
  }

  /**
   * A break: the last interval is split in the middle (an hour off), or a short one comes
   * an hour after it.
   */
  function addBreak(weekday: ScheduleWeekday) {
    const intervals = draft.value.weekdays[weekday]
    const last = intervals[intervals.length - 1]
    const beg = minutes(last.beg_hour, last.beg_minute)
    let end = minutes(last.end_hour, last.end_minute)

    if (end <= beg) {
      end += 24 * 60
    }

    const at = (value: number) => ({hour: Math.floor(value / 60) % 24, minute: value % 60})
    let next: { hour: number, minute: number }[]

    if (end - beg >= 180) {
      const middle = Math.round((beg + end) / 2 / 60) * 60
      const close = at(middle - 30)

      next = [at(middle + 30), at(end)]
      last.end_hour = close.hour
      last.end_minute = close.minute
    } else {
      next = [at(end + 60), at(end + 180)]
    }

    intervals.push({beg_hour: next[0].hour, beg_minute: next[0].minute, end_hour: next[1].hour, end_minute: next[1].minute})
  }

  function removeInterval(weekday: ScheduleWeekday, index: number) {
    draft.value.weekdays[weekday].splice(index, 1)
  }

  function setTime(interval: EditorInterval, side: 'beg' | 'end', hour: number, minute: number) {
    if (side === 'beg') {
      interval.beg_hour = hour
      interval.beg_minute = minute
    } else {
      interval.end_hour = hour
      interval.end_minute = minute
    }
  }

  function copyMonday() {
    for (const weekday of WEEKDAYS.slice(1, 5)) {
      draft.value.weekdays[weekday] = draft.value.weekdays[ScheduleWeekday.monday].map(intervalOf)
    }
  }

  // Special days

  const editingDay = ref<string | null>(null)
  let newDays = 0

  /** What's wrong with the special day: no date, or the hours. */
  function specialProblem(day: SpecialDayDraft): string | null {
    if (!day.starts_on) {
      return t('editor.hours.pick_date')
    }

    if (day.ends_on && day.ends_on < day.starts_on) {
      return t('editor.hours.end_before_start')
    }

    return day.is_closed ? null : problemOf([day])
  }

  function specialError(day: SpecialDayDraft): string | null {
    const saved = Object.entries(errors.value).find(([field]) => field.startsWith(`exceptions.${day.key}.`))

    return specialProblem(day) ?? saved?.[1]?.[0] ?? null
  }

  function addDay() {
    const tomorrow = DateTime.now().setZone(draft.value.timezone).plus({days: 1}).toISODate() as string
    const day: SpecialDayDraft = {
      key: `new-${++newDays}`,
      id: null,
      starts_on: tomorrow,
      ends_on: tomorrow,
      is_closed: true,
      beg_hour: 10,
      beg_minute: 0,
      end_hour: 18,
      end_minute: 0,
      reason: translationsOf(null, editor.locales),
    }

    draft.value.exceptions.push(day)
    editingDay.value = day.key

    nextTick(() => document.getElementById(`day-${day.key}-starts`)?.focus())
  }

  /** The day is done: the list stays in the order of the dates. */
  function finishDay() {
    editingDay.value = null
    draft.value.exceptions.sort((a, b) => a.starts_on.localeCompare(b.starts_on))
  }

  function removeDay(day: SpecialDayDraft) {
    draft.value.exceptions = draft.value.exceptions.filter((item) => item.key !== day.key)

    if (editingDay.value === day.key) {
      editingDay.value = null
    }
  }

  function setStart(day: SpecialDayDraft, value: string) {
    // one day, unless the end is set after it
    if (!day.ends_on || day.ends_on === day.starts_on || day.ends_on < value) {
      day.ends_on = value
    }

    day.starts_on = value
  }

  const date = (value: string) => DateTime.fromISO(value).setLocale(editor.locale)

  function dayTitle(day: SpecialDayDraft): string {
    return translated(day.reason, editor.defaultLocale)
      || (day.starts_on ? date(day.starts_on).toLocaleString({weekday: 'long', day: 'numeric', month: 'long'}) : '')
  }

  function dayHours(day: SpecialDayDraft): string {
    const time = (hour: number, minute: number) => `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`

    return `${time(day.beg_hour, day.beg_minute)} – ${time(day.end_hour, day.end_minute)}`
  }

  function dayRange(day: SpecialDayDraft): string | null {
    if (!day.ends_on || day.ends_on === day.starts_on) {
      return null
    }

    const format = (value: string) => date(value).toLocaleString({day: 'numeric', month: 'short'})

    return `${format(day.starts_on)} – ${format(day.ends_on)}`
  }

  // Closure

  function setClosed(closed: boolean) {
    draft.value.closed = closed

    if (closed && !draft.value.closed_until) {
      draft.value.closed_until = DateTime.now().setZone(draft.value.timezone).plus({days: 7}).toISODate() as string
    }
  }

  const closureError = computed(() => {
    if (draft.value.closed && !draft.value.closed_until) {
      return t('editor.hours.pick_date')
    }

    return error('closed_until')
  })

  const canSave = computed(() => !closureError.value
    && WEEKDAYS.every((weekday) => !problemOf(draft.value.weekdays[weekday]))
    && draft.value.exceptions.every((day) => !specialProblem(day)))

  function onDiscard() {
    editingDay.value = null
    discard()
  }
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.hours')"
              :subtitle="t('editor.subtitles.hours')"
              :dirty="dirty"
              :saving="saving"
              :failed="failed"
              :can-save="canSave"
              @navigate="editor.close()"
              @close="editor.close()"
              @discard="onDiscard"
              @save="finishDay(); save()">
    <div class="flex flex-col gap-1.5">
      <FieldLabel target="hours-zone" :label="t('editor.hours.time_zone')"/>

      <select id="hours-zone"
              class="e-input"
              :aria-invalid="!!error('timezone')"
              v-model="draft.timezone">
        <option :value="item.zone"
                v-for="item in zones" :key="item.zone">
          {{ item.label }}
        </option>
      </select>

      <p class="e-error" v-if="error('timezone')">{{ error('timezone') }}</p>
      <p class="e-help" v-else>{{ t('editor.hours.time_zone_help') }}</p>
    </div>

    <section class="flex flex-col gap-0.5">
      <div class="flex items-center justify-between mb-1.5">
        <h3 class="e-section">{{ t('editor.hours.weekly') }}</h3>

        <button type="button"
                class="inline-flex items-center gap-1 text-[13px] font-semibold text-blue-600 rounded e-focus"
                @click="copyMonday">
          <Copy class="size-3.5"/>
          {{ t('editor.hours.copy_monday') }}
        </button>
      </div>

      <div class="flex flex-col"
           v-for="weekday in WEEKDAYS" :key="weekday">
        <div class="flex items-start gap-2">
          <span class="w-[92px] shrink-0 min-h-11 flex flex-col justify-center"
                :class="weekday === today ? 'font-semibold' : 'font-medium'">
            {{ t('schedule.' + weekday) }}
            <span class="e-help -mt-0.5 font-normal" v-if="weekday === today">{{ t('editor.hours.today') }}</span>
          </span>

          <div class="h-11 flex items-center">
            <ToggleSwitch :model-value="draft.weekdays[weekday].length > 0"
                          :label="t('editor.hours.open_on', {day: t('schedule.' + weekday)})"
                          @update:model-value="setOpen(weekday, $event)"/>
          </div>

          <span class="w-1"/>

          <div class="flex flex-col gap-1 py-1"
               v-if="draft.weekdays[weekday].length">
            <div class="flex items-center gap-2"
                 v-for="(interval, index) in draft.weekdays[weekday]" :key="index">
              <TimeInput :hour="interval.beg_hour"
                         :minute="interval.beg_minute"
                         :label="t('editor.hours.opens', {day: t('schedule.' + weekday)})"
                         :invalid="!!dayError(weekday)"
                         @update="(hour, minute) => setTime(interval, 'beg', hour, minute)"/>

              <span class="text-zinc-400">–</span>

              <TimeInput :hour="interval.end_hour"
                         :minute="interval.end_minute"
                         :label="t('editor.hours.closes', {day: t('schedule.' + weekday)})"
                         :invalid="!!dayError(weekday)"
                         @update="(hour, minute) => setTime(interval, 'end', hour, minute)"/>

              <button type="button"
                      class="e-icon-btn"
                      :aria-label="t('editor.hours.add_break', {day: t('schedule.' + weekday)})"
                      :title="t('editor.hours.add_break', {day: t('schedule.' + weekday)})"
                      v-if="index === 0 && draft.weekdays[weekday].length < MAX_INTERVALS"
                      @click="addBreak(weekday)">
                <Plus class="size-4"/>
              </button>

              <button type="button"
                      class="e-icon-btn"
                      :aria-label="t('editor.hours.remove_interval')"
                      :title="t('editor.hours.remove_interval')"
                      v-else-if="index > 0"
                      @click="removeInterval(weekday, index)">
                <X class="size-4"/>
              </button>
            </div>
          </div>

          <span class="flex-1 h-11 flex items-center text-zinc-500"
                v-else>
            {{ t('editor.hours.closed_all_day') }}
          </span>
        </div>

        <p class="e-error pl-[150px] pb-1" v-if="dayError(weekday)">{{ dayError(weekday) }}</p>
      </div>

      <p class="e-help mt-1">{{ t('editor.hours.after_midnight') }}</p>
    </section>

    <div class="h-px shrink-0 bg-[#f0f0f1]"/>

    <section class="flex flex-col gap-2">
      <div class="flex items-center justify-between">
        <h3 class="e-section">{{ t('editor.hours.special_days') }}</h3>

        <button type="button"
                class="e-btn e-btn-secondary h-8 px-2.5"
                @click="addDay">
          <Plus class="size-[15px]"/>
          {{ t('editor.hours.add_day') }}
        </button>
      </div>

      <p class="e-help">{{ t('editor.hours.special_days_help') }}</p>

      <template v-for="day in draft.exceptions" :key="day.key">
        <!-- edited -->
        <div class="flex flex-col gap-3 p-3 border border-blue-600 rounded-lg shadow-[0_0_0_3px_rgba(37,99,235,0.15)]"
             v-if="editingDay === day.key"
             @keydown.esc.prevent="finishDay">
          <div class="grid grid-cols-2 gap-2">
            <div class="flex flex-col gap-1.5">
              <FieldLabel :target="`day-${day.key}-starts`" :label="t('editor.hours.from')"/>

              <input class="e-input"
                     type="date"
                     :id="`day-${day.key}-starts`"
                     :value="day.starts_on"
                     @input="setStart(day, ($event.target as HTMLInputElement).value)"/>
            </div>

            <div class="flex flex-col gap-1.5">
              <FieldLabel :target="`day-${day.key}-ends`" :label="t('editor.hours.to')"/>

              <input class="e-input"
                     type="date"
                     :id="`day-${day.key}-ends`"
                     :min="day.starts_on"
                     v-model="day.ends_on"/>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <ToggleSwitch :model-value="!day.is_closed"
                          :label="t('editor.hours.open_that_day')"
                          @update:model-value="day.is_closed = !$event"/>

            <span class="font-medium" v-if="day.is_closed">{{ t('editor.hours.closed_all_day') }}</span>

            <template v-else>
              <TimeInput :hour="day.beg_hour"
                         :minute="day.beg_minute"
                         :label="t('editor.hours.opens_that_day')"
                         :invalid="!!specialProblem(day)"
                         @update="(hour, minute) => setTime(day, 'beg', hour, minute)"/>

              <span class="text-zinc-400">–</span>

              <TimeInput :hour="day.end_hour"
                         :minute="day.end_minute"
                         :label="t('editor.hours.closes_that_day')"
                         :invalid="!!specialProblem(day)"
                         @update="(hour, minute) => setTime(day, 'end', hour, minute)"/>
            </template>
          </div>

          <div class="flex flex-col gap-1.5"
               v-for="code in locales" :key="code">
            <FieldLabel :target="`day-${day.key}-reason-${code}`"
                        :label="t('editor.hours.reason')"
                        :locale="code"/>

            <input class="e-input"
                   type="text"
                   maxlength="60"
                   :id="`day-${day.key}-reason-${code}`"
                   :lang="code"
                   :placeholder="code === editor.defaultLocale ? t('editor.hours.reason_example') : day.reason[editor.defaultLocale]"
                   v-model="day.reason[code]"/>
          </div>

          <p class="e-error" v-if="specialError(day)">{{ specialError(day) }}</p>

          <div class="flex items-center justify-between">
            <button type="button"
                    class="e-btn text-red-700 hover:bg-red-50 px-2.5"
                    @click="removeDay(day)">
              <Trash2 class="size-[15px]"/>
              {{ t('editor.hours.remove_day') }}
            </button>

            <button type="button"
                    class="e-btn e-btn-secondary"
                    :disabled="!!specialProblem(day)"
                    @click="finishDay">
              {{ t('editor.hours.done') }}
            </button>
          </div>
        </div>

        <!-- shown -->
        <div class="flex flex-col gap-1"
             v-else>
          <div class="flex items-center gap-3 py-2 pr-1 pl-2 border border-zinc-200 rounded-lg">
            <span class="w-11 shrink-0 flex flex-col items-center py-1 rounded-md bg-zinc-100">
              <span class="text-[11px]/[14px] font-bold text-zinc-500 uppercase">
                {{ date(day.starts_on).toFormat('MMM') }}
              </span>
              <span class="text-[17px]/[22px] font-bold">{{ date(day.starts_on).day }}</span>
            </span>

            <span class="flex-1 min-w-0 flex flex-col">
              <span class="font-semibold truncate">{{ dayTitle(day) }}</span>
              <span class="text-[13px] text-zinc-700">
                <span class="text-red-700" v-if="day.is_closed">{{ t('restaurant.closed') }}</span>
                <template v-else>{{ dayHours(day) }}</template>
                <template v-if="dayRange(day)"> · {{ dayRange(day) }}</template>
              </span>
            </span>

            <button type="button"
                    class="e-icon-btn"
                    :aria-label="t('editor.hours.edit_day', {day: dayTitle(day)})"
                    :title="t('editor.hours.edit_day', {day: dayTitle(day)})"
                    @click="editingDay = day.key">
              <Pencil class="size-[15px]"/>
            </button>

            <button type="button"
                    class="e-icon-btn"
                    :aria-label="t('editor.hours.delete_day', {day: dayTitle(day)})"
                    :title="t('editor.hours.delete_day', {day: dayTitle(day)})"
                    @click="removeDay(day)">
              <Trash2 class="size-[15px]"/>
            </button>
          </div>

          <p class="e-error px-1" v-if="specialError(day)">{{ specialError(day) }}</p>
        </div>
      </template>
    </section>

    <div class="h-px shrink-0 bg-[#f0f0f1]"/>

    <section class="flex flex-col gap-3">
      <div class="flex items-start gap-3">
        <div class="flex-1 flex flex-col gap-0.5">
          <p class="font-semibold">{{ t('editor.hours.close_temporarily') }}</p>
          <p class="e-help">{{ t('editor.hours.close_temporarily_help') }}</p>
        </div>

        <ToggleSwitch class="mt-0.5"
                      :model-value="draft.closed"
                      :label="t('editor.hours.close_temporarily')"
                      @update:model-value="setClosed"/>
      </div>

      <template v-if="draft.closed">
        <div class="flex flex-col gap-1.5">
          <FieldLabel target="hours-closed-until" :label="t('editor.hours.closed_until')"/>

          <input id="hours-closed-until"
                 class="e-input"
                 type="date"
                 :aria-invalid="!!closureError"
                 v-model="draft.closed_until"/>

          <p class="e-error" v-if="closureError">{{ closureError }}</p>
          <p class="e-help" v-else>{{ t('editor.hours.closed_until_help') }}</p>
        </div>

        <div class="flex flex-col gap-1.5"
             v-for="code in locales" :key="code">
          <FieldLabel :target="`hours-closed-reason-${code}`"
                      :label="t('editor.hours.closed_reason')"
                      :locale="code"/>

          <input class="e-input"
                 type="text"
                 maxlength="120"
                 :id="`hours-closed-reason-${code}`"
                 :lang="code"
                 :placeholder="code === editor.defaultLocale ? t('editor.hours.closed_reason_example') : draft.closed_reason[editor.defaultLocale]"
                 :aria-invalid="!!error(`closed_reason.${code}`)"
                 v-model="draft.closed_reason[code]"/>

          <p class="e-error" v-if="error(`closed_reason.${code}`)">
            {{ languageName(code) }}: {{ error(`closed_reason.${code}`) }}
          </p>
        </div>
      </template>
    </section>
  </PanelShell>
</template>
