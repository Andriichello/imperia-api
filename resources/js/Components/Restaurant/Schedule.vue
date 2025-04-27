<script setup lang="ts">
  import {capitalize, computed, PropType} from "vue";
  import {ScheduleInfo} from "@/helpers";

  const props = defineProps({
    info: Object as PropType<ScheduleInfo>,
  });

  const time = (hour: number, minute: number) => {
    let time = '';

    time += hour < 10 ? '0' + hour : hour;

    time += ':'

    time += minute < 10 ? '0' + minute : minute;

    return time;
  }
</script>

<template>
  <div>
    <div class="w-full flex flex-col justify-start items-start mt-1">
      <div class="w-full overflow-x-auto">
        <table class="table table-sm w-full rounded-xl">
          <tbody class="w-full">
            <template v-for="(schedule) in info.schedules" :key="schedule.id">
              {{ void(active = schedule.id === info.active?.id || schedule.id === info.relevant?.id) }}
              <tr :class="{'bg-base-300/80': active}">
                <td class="p-2 grow" :class="{'font-light': !active, 'font-bold': active}">
                  <h5 class="text-[16px]">{{ capitalize(schedule.weekday) }}</h5>
                </td>
                <td class="p-2 w-[60px] text-end" :class="{'font-light': !active, 'font-bold': active}">
                  <p class="text-[16px]">{{ time(schedule.beg_hour, schedule.beg_minute) }}</p>
                </td>
                <td class="p-2 w-[60px]" :class="{'font-light': !active, 'font-bold': active}">
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
