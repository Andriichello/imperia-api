<script setup lang="ts">
  import {capitalize, computed, PropType} from "vue";
  import {ScheduleInfo} from "@/helpers";

  const props = defineProps({
    info: {
      type: Object as PropType<ScheduleInfo>,
      required: true,
    },
  });

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

</script>

<template>
  <div>
    <div class="w-full flex flex-col justify-start items-start mt-1">
      <div class="w-full overflow-x-auto">
        <table class="table table-sm w-full rounded-xl">
          <tbody class="w-full">
            <template v-for="(schedule) in info.schedules" :key="schedule.id">
              <tr :class="{'bg-base-300/80': isActive(schedule.id)}">
                <td class="p-2 grow" :class="{'font-light': !isActive(schedule.id), 'font-bold': isActive(schedule.id)}">
                  <h5 class="text-[16px]">{{ capitalize(schedule.weekday) }}</h5>
                </td>
                <td class="p-2 w-[60px] text-end" :class="{'font-light': !isActive(schedule.id), 'font-bold': isActive(schedule.id)}">
                  <p class="text-[16px]">{{ time(schedule.beg_hour, schedule.beg_minute) }}</p>
                </td>
                <td class="p-2 w-[60px]" :class="{'font-light': !isActive(schedule.id), 'font-bold': isActive(schedule.id)}">
                  <p class="text-[16px]">{{ time(schedule.end_hour, schedule.end_minute) }}</p>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
