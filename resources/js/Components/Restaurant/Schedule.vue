<script setup lang="ts">
  import {computed, PropType} from "vue";
  import {Schedule as ScheduleModel, ScheduleWeekday} from "@/api";
  import {ScheduleInfo, time} from "@/helpers";
  import {useI18n} from "vue-i18n";

  const props = defineProps({
    info: {
      type: Object as PropType<ScheduleInfo>,
      required: true,
    },
  });

  const i18n = useI18n();

  /** Every day of the week with its hours, or null if it is closed (switched off or without hours). */
  const days = computed(() => Object.values(ScheduleWeekday).map((weekday) => ({
    weekday,
    schedule: props.info.schedules.find((s: ScheduleModel) => s.weekday === weekday && !s.archived) ?? null,
  })));
</script>

<template>
  <div class="w-full overflow-x-auto">
    <table class="w-full">
      <tbody>
        <tr v-for="({weekday, schedule}, index) in days" :key="weekday"
            :class="{
              'border-b border-base-content/6': index < days.length - 1,
              'bg-primary/10 text-primary-content font-bold': weekday === info.today,
              'text-base-content/65': weekday !== info.today && !schedule,
            }">
          <td class="p-2 text-base/6">
            {{ i18n.t('schedule.' + weekday) }}
          </td>

          <template v-if="schedule">
            <td class="p-2 w-[60px] text-end text-base/6">
              {{ time(schedule.beg_hour, schedule.beg_minute) }}
            </td>
            <td class="p-2 w-[60px] text-base/6">
              {{ time(schedule.end_hour, schedule.end_minute) }}
            </td>
          </template>

          <td class="p-2 text-end text-base/6"
              :class="{'text-red-700': weekday === info.today}"
              colspan="2"
              v-else>
            {{ i18n.t('restaurant.closed') }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
