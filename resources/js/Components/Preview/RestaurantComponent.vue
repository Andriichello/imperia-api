<script setup lang="ts">
  import {computed, onMounted, PropType, ref, watch} from "vue";
  import {Splide, SplideSlide} from '@splidejs/vue-splide';
  import {
    Copy,
    CalendarClock,
    ChevronUp,
    ChevronDown,
    ChevronRight,
    MapPin,
    Phone,
  } from 'lucide-vue-next';
  import {Media, Menu, Restaurant} from "@/api";
  import Schedule from "@/Components/Restaurant/Schedule.vue";
  import {getScheduleInfo, ScheduleInfo, time} from "@/helpers";
  import { useI18n } from 'vue-i18n';

  const props = defineProps({
    restaurant: {
      type: Object as PropType<Restaurant>,
      required: true,
    },
    menus: {
      type: Array as PropType<Menu[]>,
      required: true,
    },
  });

  const emits = defineEmits(['open-menu', 'open-phone', 'open-address']);

  const i18n = useI18n();

  const media = computed<Media[]>(() => {
    return props.restaurant.media.map((m: Media) => {
      const webp = m?.variants.find((v: Media) => v.extension === 'webp');
      return webp ?? m;
    })
  });

  const slideOptions = ref({
    perPage: 1,
    perMove: 1,
    rewind: false,
    rewindByDrag: false,
    drag: (media.value?.length ?? 0) > 1,
    arrows: (media.value?.length ?? 0) > 1,
    pagination: true,
  });

  const scheduleInfo = computed<ScheduleInfo>(
    () => getScheduleInfo(props.restaurant)
  );

  const getEstablishmentTitle = computed(() => {
    const establishment = props.restaurant?.establishment?.toLowerCase();

    if (!establishment) {
      return i18n.t('restaurant.title');
    }

    if (establishment.includes('café') || establishment.includes('cafe')) {
      return i18n.t('restaurant.cafe_title');
    }

    if (establishment.includes('bakery')) {
      return i18n.t('restaurant.bakery_title');
    }

    if (establishment.includes('bistro')) {
      return i18n.t('restaurant.bistro_title');
    }

    if (establishment.includes('pizzeria')) {
      return i18n.t('restaurant.pizzeria_title');
    }

    if (establishment.includes('bar')) {
      return i18n.t('restaurant.bar_title');
    }

    return i18n.t('restaurant.title');
  });

  const scheduleExpanded = ref(false);

  function copyToClipboard(text: string) {
    navigator.clipboard.writeText(text)
      .then(() => alert(i18n.t('clipboard.copied')))
      .catch(() => alert(i18n.t('clipboard.failed')));
  }
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-20">
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <Splide class="w-full h-75" :options="slideOptions"
              v-if="media?.length > 0">
        <SplideSlide v-for="(m, index) in media ?? []" :key="m.id">
          <img class="w-full h-75 object-cover object-center"
               :src="m.url" alt=""
               :loading="index === 0 ? 'eager' : 'lazy'"/>
        </SplideSlide>
      </Splide>

      <div class="w-full h-75"
           v-else>
      </div>

      <div class="w-full pt-3 pb-1 px-3 text-center">
        <h3 class="text-2xl font-bold">
          {{ restaurant!.name }}
        </h3>

        <p class="text-md -translate-y-0.5 opacity-70">
          {{ getEstablishmentTitle }}
        </p>
      </div>
      <div class="w-full pt-1 pb-3 px-3 pr-6 chat chat-start flex flex-col gap-1.5 translate-x-0.5"
           v-if="restaurant!.notes!?.length > 0">
        <template v-for="(note, index) in restaurant!.notes" :key="index">
          <p class="w-full chat-bubble bg-warning/15 text-warning-content rounded-xl">
            {{ note }}
          </p>
        </template>
      </div>

      <div class="w-full flex flex-col grow pt-2 pb-3 px-3 gap-2">
        <div class="w-full flex flex-col grow gap-2">
          <template v-if="menus!.length > 0">
            <div class="w-full flex items-center justify-center pl-5 pr-3 py-3 bg-base-200/40 border-2 border-base-300 rounded cursor-pointer"
                 @click="emits('open-menu', menu)"
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
              {{ i18n.t('restaurant.no_menus') }}
            </h3>
          </div>
        </div>
      </div>

      <div class="w-full flex flex-col grow mt-3 pb-3 gap-1 bg-base-200">
        <div class="w-full flex flex-col gap-3">
          <div class="w-full flex flex-col gap-1">
            <div class="w-full h-[1px] bg-base-300"/>

            <div class="w-full flex flex-col justify-start items-start py-2 px-3">
              <div class="w-full flex justify-start items-start gap-3 cursor-pointer"
                   @click="scheduleExpanded = !scheduleExpanded">
                <div class="w-12 min-w-12 h-12 flex justify-center items-center  bg-warning/15 border-1 border-warning/40 text-warning-content rounded">
                  <CalendarClock class="w-6 h-6"/>
                </div>

                <div class="flex grow flex-col justify-center items-start">
                  <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                    {{ i18n.t('restaurant.working_hours') }}
                  </h3>
                  <p class="text-md text-base-content/90 font-semibold">
                    {{ time(scheduleInfo.relevant.beg_hour, scheduleInfo.relevant.beg_minute) }} -
                    {{ time(scheduleInfo.relevant.end_hour, scheduleInfo.relevant.end_minute) }}
                  </p>
                </div>

                <div class="w-12 min-w-12 h-12 flex justify-center items-center mr-1.5 text-base-content/60">
                  <ChevronUp class="w-6 h-6" v-if="scheduleExpanded"/>
                  <ChevronDown class="w-6 h-6" v-else/>
                </div>
              </div>

              <p class="w-full font-mono text-md cursor-pointer pl-15 pt-2"
                 :class="{'text-green-600': scheduleInfo.status === 'Open', 'text-red-600': scheduleInfo.status === 'Closed'}"
                 @click="scheduleExpanded = !scheduleExpanded">
                <span class="font-semibold">{{ scheduleInfo.status === 'Open' ? i18n.t('restaurant.open') : i18n.t('restaurant.closed') }}:</span> {{ scheduleInfo.timeBeforeOrUntil }} {{ scheduleInfo.status === 'Open' ? i18n.t('restaurant.until_closing') : i18n.t('restaurant.until_opening') }}
              </p>

              <Schedule class="w-full"
                        v-if="scheduleExpanded"
                        :info="scheduleInfo"/>
            </div>

            <div class="w-full h-[1px] bg-base-300"/>
          </div>

          <div class="w-full flex justify-start items-start gap-3 px-3"
               v-if="restaurant!.phone?.length">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-warning/15 border-1 border-warning/60 text-warning-content rounded">
              <Phone class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-center items-start">
              <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                {{ i18n.t('restaurant.phone') }}
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant!.phone }}
              </p>
            </div>

            <button class="w-13 min-w-13 h-12 flex justify-center items-center rounded text-base-content/60 cursor-pointer pr-1"
                    @click="copyToClipboard(restaurant!.phone)">
              <Copy class="w-6 h-6"/>
            </button>
          </div>

          <div class="w-full flex justify-start items-start gap-3 px-3"
               v-if="restaurant!.full_address?.length">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-warning/15 border-1 border-warning/60 text-warning-content rounded">
              <MapPin class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-start items-start">
              <h3 class="text-sm font-semibold text-base-content/50">
                {{ i18n.t('restaurant.location') }}
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant!.full_address }}
              </p>
            </div>

            <button class="w-13 min-w-13 h-12 flex justify-center items-center rounded text-base-content/60 cursor-pointer pr-1"
                    @click="copyToClipboard(restaurant!.full_address)">
              <Copy class="w-6 h-6"/>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
