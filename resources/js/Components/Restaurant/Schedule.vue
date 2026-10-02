<script setup lang="ts">
  import {computed, PropType} from "vue";
  import {Schedule as ScheduleModel, ScheduleWeekday} from "@/api";
  import {ScheduleInfo} from "@/helpers";
  import {useI18n} from "vue-i18n";

  const props = defineProps({
    info: {
      type: Object as PropType<ScheduleInfo>,
      required: true,
    },
  });

  const i18n = useI18n();

  const time = (hour: number, minute: number) => {
    let time = '';

    time += hour < 10 ? '0' + hour : hour;

    time += ':'

    time += minute < 10 ? '0' + minute : minute;

    return time;
  }

  /** Returns a function that checks if a schedule is active. */
  const isActive = (scheduleId: number) =>
    props.info.active?.id === scheduleId ||
    props.info.relevant?.id === scheduleId;

  /** Every day of the week with its hours, or null if it is closed (switched off or without hours). */
  const days = computed(() => Object.values(ScheduleWeekday).map((weekday) => ({
    weekday,
    schedule: props.info.schedules.find((s: ScheduleModel) => s.weekday === weekday && !s.archived) ?? null,
  })));
</script>

<template>
  <div>
    <div class="w-full flex flex-col justify-start items-start mt-1">
      <div class="w-full overflow-x-auto">
        <table class="table table-sm w-full rounded-xl">
          <tbody class="w-full">
            <template v-for="{weekday, schedule} in days" :key="weekday">
              <tr v-if="schedule" :class="{'bg-warning/10 text-warning-content/80': isActive(schedule.id)}">
                <td class="p-2 grow" :class="{'font-light': !isActive(schedule.id), 'font-bold': isActive(schedule.id)}">
                  <h5 class="text-[16px]">{{ i18n.t('schedule.' + weekday) }}</h5>
                </td>
                <td class="p-2 w-[60px] text-end" :class="{'font-light': !isActive(schedule.id), 'font-bold': isActive(schedule.id)}">
                  <p class="text-[16px]">{{ time(schedule.beg_hour, schedule.beg_minute) }}</p>
                </td>
                <td class="p-2 w-[60px]" :class="{'font-light': !isActive(schedule.id), 'font-bold': isActive(schedule.id)}">
                  <p class="text-[16px]">{{ time(schedule.end_hour, schedule.end_minute) }}</p>
                </td>
              </tr>

              <tr v-else class="text-base-content/50">
                <td class="p-2 grow font-light">
                  <h5 class="text-[16px]">{{ i18n.t('schedule.' + weekday) }}</h5>
                </td>
                <td class="p-2 text-end font-light" colspan="2">
                  <p class="text-[16px]">{{ i18n.t('restaurant.closed') }}</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
