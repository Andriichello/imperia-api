<script setup lang="ts">
  import {computed, onBeforeUnmount, onMounted, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {ChevronRight} from 'lucide-vue-next'
  import type {EditorDashboard, EditorTranslations} from '@/api'
  import type {Interval} from '@/helpers'
  import {formatDayMonth, formatHour, formatTime, intlLocale} from '@/admin/format'
  import {NowState, rightNow} from '@/admin/status'
  import {translated} from '@/editor/translations'

  /**
   * Whether the restaurant is open right now (in its time zone), today's hours on a 24-hour bar
   * and the week's hours. It's worked out again every minute.
   */
  const props = defineProps({
    restaurant: {type: Object as PropType<EditorDashboard>, required: true},
    hoursUrl: {type: String, required: true},
  })

  const {t, locale} = useI18n()

  // a minute passed: the state is worked out again
  const tick = ref(0)
  let timer: number | undefined

  onMounted(() => timer = window.setInterval(() => tick.value++, 60 * 1000))
  onBeforeUnmount(() => window.clearInterval(timer))

  const now = computed(() => {
    void tick.value

    return rightNow(props.restaurant)
  })

  const TONES: Record<NowState, { dot: string, text: string }> = {
    open: {dot: '#16a34a', text: '#15803d'},
    closing_soon: {dot: '#d08700', text: '#894b00'},
    opens_soon: {dot: '#d08700', text: '#894b00'},
    closed: {dot: '#dc2626', text: '#b91c1c'},
    holiday: {dot: '#dc2626', text: '#b91c1c'},
    temporarily_closed: {dot: '#dc2626', text: '#b91c1c'},
  }

  const tone = computed(() => TONES[now.value.state])

  const text = (value: Parameters<typeof translated>[0]) => translated(value, locale.value, props.restaurant.default_locale)

  /** "6 h 20 min", "25 min" */
  function left(minutes: number): string {
    const hours = Math.floor(minutes / 60)

    return hours > 0
      ? t('admin.dashboard.now.left.hours', {hours, minutes: minutes % 60})
      : t('admin.dashboard.now.left.minutes', {minutes})
  }

  /** "opens tomorrow at 10:00" */
  function nextOpening(): string {
    const {info, now: today} = now.value
    const key = 'admin.dashboard.now.details.'

    if (!info.relevant) {
      return t(key + 'no_hours')
    }

    const date = info.relevant.closestBegDate
    const time = formatTime(date)
    const days = Math.round(date.startOf('day').diff(today.startOf('day'), 'days').days)

    if (days <= 0) {
      return t(key + 'opens_today', {time})
    }

    return days === 1
      ? t(key + 'opens_tomorrow', {time})
      : t(key + 'opens_on', {weekday: date.setLocale(intlLocale(locale.value)).toLocaleString({weekday: 'long'}), time})
  }

  const detail = computed(() => {
    const {state, info} = now.value
    const key = 'admin.dashboard.now.details.'
    const minutes = info.minutesLeft ?? 0

    switch (state) {
      case 'open':
        return t(key + 'until', {time: formatTime(info.relevant!.closestEndDate), left: left(minutes)})
      case 'closing_soon':
        return t(key + 'closes_at', {time: formatTime(info.relevant!.closestEndDate), left: left(minutes)})
      case 'opens_soon':
        return t(key + 'opens_at', {time: formatTime(info.relevant!.closestBegDate), left: left(minutes)})
      case 'holiday':
        return `${text(info.special?.reason as unknown as EditorTranslations) || t(key + 'special_day')} · ${nextOpening()}`
      case 'temporarily_closed': {
        // closed till that day, inclusive
        const reopens = DateTime.fromISO(info.closedUntil as string).plus({days: 1})
        const reason = text(props.restaurant.closed_reason)
        const date = t(key + 'reopens_on', {date: formatDayMonth(reopens, locale.value)})

        return reason ? `${reason} · ${date}` : date.charAt(0).toUpperCase() + date.slice(1)
      }
      default:
        return nextOpening()
    }
  })

  const hours = (intervals: Interval[]) => intervals
    .map((i) => `${formatHour(i.beg_hour, i.beg_minute)} – ${formatHour(i.end_hour, i.end_minute)}`)
    .join(', ')

  const todayHours = computed(() => hours(now.value.hours) || t('admin.dashboard.now.closed'))

  const weekday = computed(() => now.value.now.setLocale(intlLocale(locale.value)).toLocaleString({weekday: 'long'}))

  /** "10–22", "10:30–22" (the first opening to the last closing) */
  function short(intervals: Interval[]): string | null {
    if (!intervals.length) {
      return null
    }

    const hour = (h: number, m: number) => m ? formatHour(h, m) : String(h)
    const first = intervals[0]
    const last = intervals[intervals.length - 1]

    return `${hour(first.beg_hour, first.beg_minute)}–${hour(last.end_hour, last.end_minute)}`
  }

  const week = computed(() => now.value.week.map((day, index) => ({
    key: day.weekday,
    // Monday of this week, plus the day
    name: now.value.now.startOf('week').plus({days: index}).setLocale(intlLocale(locale.value))
      .toLocaleString({weekday: 'short'}),
    hours: short(day.intervals),
    today: index === now.value.now.weekday - 1,
  })))

  const percent = (minutes: number) => `${(minutes / (24 * 60) * 100).toFixed(3)}%`

  const nowMinutes = computed(() => now.value.now.hour * 60 + now.value.now.minute)
</script>

<template>
  <section class="e-card px-5 py-[18px]" :aria-label="t('admin.dashboard.now.title')">
    <div class="flex items-center gap-2 min-h-7">
      <h2 class="e-card-title">{{ t('admin.dashboard.now.title') }}</h2>
      <div class="flex-1"/>
      <a class="e-link" :href="hoursUrl">
        {{ t('admin.dashboard.now.hours') }}
        <ChevronRight class="size-3.5"/>
      </a>
    </div>

    <div class="mt-3 flex items-center gap-2.5">
      <span class="size-3 rounded-full"
            :style="{background: tone.dot, boxShadow: `0 0 0 4px color-mix(in oklab, ${tone.dot} 18%, transparent)`}"
            aria-hidden="true"/>
      <span class="text-[22px]/[30px] font-bold" :style="{color: tone.text}">
        {{ t('admin.dashboard.now.states.' + now.state) }}
      </span>
    </div>
    <p class="mt-0.5 text-sm text-zinc-700">{{ detail }}</p>

    <div class="mt-[18px] flex items-baseline justify-between gap-3 text-[13px]">
      <span class="text-zinc-500">{{ t('admin.dashboard.now.today', {weekday}) }}</span>
      <span class="font-semibold text-right">{{ todayHours }}</span>
    </div>

    <div class="relative mt-2 h-2.5 rounded-[5px] bg-zinc-100">
      <span class="absolute inset-0 rounded-[5px] bg-[repeating-linear-gradient(-45deg,#fee2e2_0_6px,#fef2f2_6px_12px)]"
            v-if="!now.today.length"/>
      <span class="absolute inset-y-0 rounded-[5px] bg-green-200"
            :style="{left: percent(span.beg), width: percent(span.end - span.beg)}"
            v-for="span in now.today" :key="span.beg"/>
      <span class="absolute -top-1 -bottom-1 w-0.5 -ml-px rounded-[1px] bg-zinc-900"
            :style="{left: percent(nowMinutes)}"
            :aria-label="t('admin.dashboard.now.now', {time: formatTime(now.now)})"/>
    </div>

    <div class="relative h-4 mt-1 text-[11px] text-zinc-400" aria-hidden="true">
      <span class="absolute left-0">00</span>
      <span class="absolute left-1/4 -translate-x-1/2">06</span>
      <span class="absolute left-1/2 -translate-x-1/2">12</span>
      <span class="absolute left-3/4 -translate-x-1/2">18</span>
      <span class="absolute right-0">24</span>
    </div>

    <p class="mt-3.5 text-[13px] text-zinc-500">{{ t('admin.dashboard.now.this_week') }}</p>

    <div class="mt-1.5 grid grid-cols-7 gap-0.5">
      <div class="flex flex-col items-center gap-px py-1.5 rounded-md"
           :class="{'bg-zinc-100 shadow-[inset_0_0_0_1px_#e4e4e7]': day.today}"
           :aria-current="day.today ? 'date' : undefined"
           v-for="day in week" :key="day.key">
        <span class="text-[11px]/[14px] font-semibold capitalize" :class="day.today ? 'text-zinc-900' : 'text-zinc-500'">
          {{ day.name }}
        </span>
        <span class="text-xs tabular-nums"
              :class="[day.hours ? '' : 'text-red-700', day.today ? 'font-semibold' : '']">
          {{ day.hours ?? t('admin.dashboard.now.closed') }}
        </span>
      </div>
    </div>
  </section>
</template>
