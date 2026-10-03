<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {ChevronRight, Plus} from 'lucide-vue-next'
  import type {EditorDashboard, EditorInterval, EditorScheduleException, EditorWeekdays} from '@/api'
  import {formatHour, intlLocale} from '@/admin/format'
  import {translated} from '@/editor/translations'

  /**
   * The next special days: holidays (closed, in red) and short days (in amber).
   */
  const props = defineProps({
    restaurant: {type: Object as PropType<EditorDashboard>, required: true},
    hoursUrl: {type: String, required: true},
  })

  const {t, locale} = useI18n()

  const SHOWN = 4

  function describe(day: EditorScheduleException) {
    const date = DateTime.fromISO(day.starts_on).setLocale(intlLocale(locale.value))
    const short = date.toLocaleString({weekday: 'short'})
    const weekday = short.charAt(0).toUpperCase() + short.slice(1)
    const key = 'admin.dashboard.special_days.'

    let detail = t(key + 'closed', {weekday})

    if (!day.is_closed) {
      const usual = (props.restaurant.weekdays[date.setLocale('en').weekdayLong!.toLowerCase() as keyof EditorWeekdays]
        ?? []) as EditorInterval[]
      const opens = formatHour(day.beg_hour ?? 0, day.beg_minute ?? 0)
      const closes = formatHour(day.end_hour ?? 0, day.end_minute ?? 0)

      // it only closes earlier than usually
      detail = usual.length && formatHour(usual[0].beg_hour, usual[0].beg_minute) === opens
        ? t(key + 'closes_at', {weekday, time: closes})
        : t(key + 'hours', {weekday, hours: `${opens} – ${closes}`})
    }

    return {
      id: day.id,
      month: date.toLocaleString({month: 'short'}).replace('.', ''),
      day: date.day,
      title: translated(day.reason, locale.value, props.restaurant.default_locale) || t(key + 'untitled'),
      detail,
      closed: day.is_closed,
    }
  }

  const days = computed(() => props.restaurant.exceptions.slice(0, SHOWN).map(describe))
</script>

<template>
  <section class="e-card px-5 pt-[18px] pb-3 flex flex-col">
    <div class="flex items-center gap-2 min-h-7">
      <h2 class="e-card-title">{{ t('admin.dashboard.special_days.title') }}</h2>
      <div class="flex-1"/>
      <a class="e-btn e-btn-secondary h-[30px]! px-2.5! text-[13px]!" :href="hoursUrl">
        <Plus class="size-3.5"/>
        {{ t('admin.dashboard.special_days.add') }}
      </a>
    </div>

    <ul class="mt-2" v-if="days.length">
      <li class="flex items-center gap-3 py-[9px] border-t border-[#f0f0f1]"
          v-for="day in days" :key="day.id">
        <span class="w-[42px] shrink-0 flex flex-col items-center py-[3px] rounded-md bg-zinc-100">
          <span class="text-[10px]/[14px] font-bold text-zinc-500 uppercase">{{ day.month }}</span>
          <span class="text-base/5 font-bold">{{ day.day }}</span>
        </span>

        <span class="flex-1 min-w-0 flex flex-col">
          <span class="font-semibold truncate">{{ day.title }}</span>
          <span class="text-[13px]/[18px]" :class="day.closed ? 'text-red-700' : 'text-[#894b00]'">{{ day.detail }}</span>
        </span>
      </li>
    </ul>

    <p class="mt-2 py-3 border-t border-[#f0f0f1] text-[13px]/[18px] text-zinc-500" v-else>
      {{ t('admin.dashboard.special_days.empty') }}
    </p>

    <a class="mt-auto pt-2.5 pb-0.5 e-link self-start" :href="hoursUrl" v-if="days.length">
      {{ t('admin.dashboard.special_days.all') }}
      <ChevronRight class="size-3.5"/>
    </a>
  </section>
</template>
