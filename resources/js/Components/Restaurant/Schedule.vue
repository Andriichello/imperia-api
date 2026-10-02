<script setup lang="ts">
  import {PropType} from "vue";
  import {DateTime} from "luxon";
  import {ScheduleException} from "@/api";
  import {ScheduleInfo, time} from "@/helpers";
  import {useI18n} from "vue-i18n";

  const props = defineProps({
    info: {
      type: Object as PropType<ScheduleInfo>,
      required: true,
    },
  });

  const i18n = useI18n();

  /** "24 Dec", or "24 Dec – 2 Jan" for several days. */
  function dates(special: ScheduleException): string {
    const format = (date: string) => DateTime.fromISO(date)
      .setLocale(i18n.locale.value)
      .toLocaleString({day: 'numeric', month: 'short'});

    return special.starts_on === special.ends_on
      ? format(special.starts_on)
      : `${format(special.starts_on)} – ${format(special.ends_on)}`;
  }

  function isToday(special: ScheduleException): boolean {
    return props.info.special?.id === special.id;
  }
</script>

<template>
  <div class="w-full overflow-x-auto">
    <table class="w-full">
      <tbody>
        <tr v-for="({weekday, intervals}, index) in info.week" :key="weekday"
            :class="{
              'border-b border-base-content/6': index < info.week.length - 1,
              'bg-primary/10 text-primary-content font-bold': weekday === info.today,
              'text-base-content/65': weekday !== info.today && !intervals.length,
            }">
          <td class="p-2 text-base/6 align-top">
            {{ i18n.t('schedule.' + weekday) }}
          </td>

          <!-- several intervals a day (a lunch break) are one under another -->
          <template v-if="intervals.length">
            <td class="p-2 w-[60px] text-end text-base/6 align-top">
              <div v-for="(interval, i) in intervals" :key="i">
                {{ time(interval.beg_hour, interval.beg_minute) }}
              </div>
            </td>
            <td class="p-2 w-[60px] text-base/6 align-top">
              <div v-for="(interval, i) in intervals" :key="i">
                {{ time(interval.end_hour, interval.end_minute) }}
              </div>
            </td>
          </template>

          <td class="p-2 text-end text-base/6"
              colspan="2"
              v-else>
            {{ i18n.t('restaurant.closed') }}
          </td>
        </tr>
      </tbody>
    </table>

    <template v-if="info.specials.length">
      <h4 class="mt-3 px-2 text-sm/5 font-semibold text-base-content/65">
        {{ i18n.t('schedule.special_days') }}
      </h4>

      <table class="w-full">
        <tbody>
          <tr v-for="(special, index) in info.specials" :key="special.id"
              :class="{
                'border-b border-base-content/6': index < info.specials.length - 1,
                'bg-primary/10 text-primary-content font-bold': isToday(special),
                'text-base-content/65': !isToday(special) && special.is_closed,
              }">
            <td class="p-2 text-base/6 align-top">
              {{ dates(special) }}
              <span class="block text-sm/5 font-normal text-base-content/65"
                    v-if="special.reason">
                {{ special.reason }}
              </span>
            </td>

            <td class="p-2 w-[60px] text-end text-base/6 align-top"
                v-if="!special.is_closed">
              {{ time(special.beg_hour ?? 0, special.beg_minute ?? 0) }}
            </td>
            <td class="p-2 w-[60px] text-base/6 align-top"
                v-if="!special.is_closed">
              {{ time(special.end_hour ?? 0, special.end_minute ?? 0) }}
            </td>

            <td class="p-2 text-end text-base/6 align-top"
                colspan="2"
                v-else>
              {{ i18n.t('restaurant.closed') }}
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </div>
</template>
