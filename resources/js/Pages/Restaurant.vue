<script setup lang="ts">
import {computed, PropType, ref, shallowReactive} from "vue";
  import {Splide, SplideSlide} from '@splidejs/vue-splide';
  import {CalendarClock, ChevronUp, ChevronDown, ChevronRight, Circle, DoorOpen, Lock, MapPin, Phone} from 'lucide-vue-next';
  import {Menu, Restaurant} from "@/api";
  import Schedule from "@/Components/Restaurant/Schedule.vue";
  import {getScheduleInfo, ScheduleInfo, time} from "@/helpers";

  const props = defineProps({
    restaurant: Object as PropType<Restaurant>,
    menus: Array as PropType<Menu[]>,
  });

  const slideOptions = ref({
    perPage: 1,
    perMove: 1,
    rewind: false,
    rewindByDrag: false,
    drag: Number(props.restaurant!.media?.length) > 1,
    arrows: Number(props.restaurant!.media?.length) > 1,
    pagination: true,
  });

  const scheduleInfo = computed<ScheduleInfo>(
    () => getScheduleInfo(props.restaurant!)
  );

  const scheduleExpanded = ref(false);

  const callPhone = (phone) => {
    if (window && phone?.length > 0) {
      window.open(`tel:${phone}`, '_blank');
    }
  };

  const openMap = (address) => {
    if (window && address?.length > 0) {
      window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank')
    }
  };
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-20">
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <Splide class="w-full h-75" :options="slideOptions"
              v-if="restaurant!.media?.length > 0">
        <SplideSlide v-for="(media, index) in restaurant!.media ?? []" :key="media.id">
          <img class="w-full h-75 object-cover object-center"
               :src="media.url" :alt="`Slide ${index}`"
               :loading="index === 0 ? 'eager' : 'lazy'"/>
        </SplideSlide>
      </Splide>

      <div class="w-full pt-3 pb-1 px-3 text-center">
        <h3 class="text-2xl font-bold">
          {{ restaurant!.name }}
        </h3>

        <p class="text-md -translate-y-0.5 opacity-70">
          {{ 'restaurant' }}
        </p>
      </div>
      <div class="w-full pt-1 pb-3 px-3 pr-6 chat chat-start flex flex-col gap-1.5 translate-x-0.5"
           v-if="restaurant!.notes!?.length > 0">
        <template v-for="(note, index) in restaurant!.notes" :key="index">
          <p class="w-full chat-bubble bg-warning/20 rounded-xl">
            {{ note }}
          </p>
        </template>
      </div>

      <div class="w-full flex flex-col grow pt-2 pb-3 px-3 gap-2">
        <div class="w-full flex flex-col grow gap-2">
          <template v-if="menus!?.length > 0">
            <div class="w-full flex items-center justify-center pl-5 pr-3 py-3 bg-base-200/40 border-2 border-base-300 rounded"
                 v-for="menu in menus" :key="menu.id">
              <div class="w-full flex flex-col">
                <h3 class="text-xl font-bold">
                  {{ menu.title }}
                </h3>

                <p class="text-md -translate-y-0.5 opacity-70"
                   v-if="menu.description?.length">
                  {{ menu.description }}
                </p>
              </div>

              <ChevronRight class="w-8 h-8"/>
            </div>
          </template>

          <div class="w-full flex items-center justify-center px-3 py-3" v-else>
            <h3 class="text-xl font-bold text-center opacity-70">
              Unfortunately, there are no menus available now
            </h3>
          </div>
        </div>
      </div>

      <div class="w-full flex flex-col grow mt-3 pb-3 gap-1 bg-base-200/80">
        <div class="w-full flex flex-col gap-3">
          <div class="w-full flex flex-col gap-1">
            <div class="w-full h-[2px] bg-base-300"/>

            <div class="w-full flex flex-col justify-start items-start py-2 px-3">
              <div class="w-full flex justify-start items-start gap-3 cursor-pointer"
                   @click="scheduleExpanded = !scheduleExpanded">
                <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
                  <CalendarClock class="w-6 h-6"/>
                </div>

                <div class="flex grow flex-col justify-center items-start">
                  <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                    Working hours:
                  </h3>
                  <p class="text-md text-base-content/90 font-semibold">
                    {{ time(scheduleInfo.relevant.beg_hour, scheduleInfo.relevant.beg_minute) }} -
                    {{ time(scheduleInfo.relevant.end_hour, scheduleInfo.relevant.end_minute) }}
                  </p>
                </div>

                <div class="w-12 min-w-12 h-12 flex justify-center items-center mr-1.5">
                  <ChevronUp class="w-6 h-6" v-if="scheduleExpanded"/>
                  <ChevronDown class="w-6 h-6" v-else/>
                </div>
              </div>

              <p class="w-full font-mono text-md cursor-pointer pl-15"
                 :class="{'text-green-600': scheduleInfo.status === 'Open', 'text-red-600': scheduleInfo.status === 'Closed'}"
                 @click="scheduleExpanded = !scheduleExpanded">
                <span class="font-semibold">{{ scheduleInfo.status === 'Open' ? 'Open' : 'Closed' }}:</span> {{ scheduleInfo.timeBeforeOrUntil }} {{ scheduleInfo.status === 'Open' ? 'until closing' : 'until opening' }}
              </p>

              <Schedule class="w-full"
                        v-if="scheduleExpanded"
                        :info="scheduleInfo"/>
            </div>

            <div class="w-full h-[2px] bg-base-300"/>
          </div>

          <div class="w-full flex justify-start items-start gap-3 px-3"
               v-if="restaurant!.phone?.length"
               @click="callPhone(restaurant!.phone)">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <Phone class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-center items-start">
              <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                Phone:
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant!.phone }}
              </p>
            </div>
          </div>

          <div class="w-full flex justify-start items-start gap-3 px-3"
               v-if="restaurant!.full_address?.length"
               @click="openMap(restaurant!.full_address)">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <MapPin class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-start items-start">
              <h3 class="text-sm font-semibold text-base-content/50">
                Location:
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant!.full_address }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
