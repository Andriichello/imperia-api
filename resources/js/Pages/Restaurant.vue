<script setup lang="ts">
import {computed, onMounted, PropType, ref, watch} from "vue";
  import {Splide, SplideSlide} from '@splidejs/vue-splide';
  import {
    CalendarClock,
    ChevronUp,
    ChevronDown,
    ChevronRight,
    MapPin,
    Phone,
    Search,
    Languages,
  } from 'lucide-vue-next';
  import {Menu, Restaurant} from "@/api";
  import Schedule from "@/Components/Restaurant/Schedule.vue";
  import {getScheduleInfo, ScheduleInfo, time} from "@/helpers";
  import {router} from "@inertiajs/vue3";
  import LanguagesDrawer from "@/Components/Menu/LanguagesDrawer.vue";
  import SearchDrawer from "@/Components/Menu/SearchDrawer.vue";
  import { useI18n } from 'vue-i18n';
  import {switchLanguage} from "@/i18n/utils";
  import axios from "axios";

  const props = defineProps({
    restaurant: {
      type: Object as PropType<Restaurant>,
      required: true,
    },
    menus: {
      type: Array as PropType<Menu[]>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    supported_locales: {
      type: Array as PropType<string[]>,
      required: true,
    }
  });

  const i18n = useI18n();

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

  const isSearchDrawerOpen = ref(false);
  const isLanguagesDrawerOpen = ref(false);

  const menusWithProducts = ref<Menu[] | null>(null);
  const isLoadingMenusWithProducts = ref(false);
  const wasLoadingMenusWithProducts = ref(false);

  const loadMenusWithProducts = async () => {
    isLoadingMenusWithProducts.value = true;
    wasLoadingMenusWithProducts.value = true;

    axios(window.location.pathname + '/menus')
      .catch(err => {
        console.log(err);
      })
      .then(res => {
        menusWithProducts.value = res.data.data as Menu[];
      })
      .finally(() => {
        isLoadingMenusWithProducts.value = false;
      })
  }

  const openMenu = (menu: Menu) => {
    router.visit(window.location.pathname + `/menu/${menu.id}`, {replace: false});
  }

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

  const onSwitchLanguage = (locale: string) => {
    switchLanguage(i18n, locale);
  }

  watch(() => isSearchDrawerOpen.value, (newValue) => {
    if (newValue === true && wasLoadingMenusWithProducts.value === false) {
      setTimeout(() => loadMenusWithProducts(), 1000);
    }
  }, {immediate: true});
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-20">
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <div class="w-full flex justify-end gap-2 p-2 absolute top-0 z-2">
        <div class="flex gap-2">
          <button class="btn btn-sm bg-base-100"
                  @click="isLanguagesDrawerOpen = true">
            <Languages class="w-5 h-5 text-base-content/80"/>
          </button>

          <button class="btn btn-sm bg-base-100"
                  @click="isSearchDrawerOpen = true">
            <Search class="w-5 h-5 text-base-content/80"/>
          </button>
        </div>
      </div>

      <Splide class="w-full h-75" :options="slideOptions"
              v-if="restaurant!.media?.length > 0">
        <SplideSlide v-for="(media, index) in restaurant!.media ?? []" :key="media.id">
          <img class="w-full h-75 object-cover object-center"
               :src="media.url" :alt="`Slide ${index}`"
               :loading="index === 0 ? 'eager' : 'lazy'"/>
        </SplideSlide>
      </Splide>

      <div class="w-full h-12 bg-base-200 border-b-1 border-base-300"
           v-else/>

      <div class="w-full pt-3 pb-1 px-3 text-center">
        <h3 class="text-2xl font-bold">
          {{ restaurant!.name }}
        </h3>

        <p class="text-md -translate-y-0.5 opacity-70">
          {{ $t('restaurant.title') }}
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
          <template v-if="menus!.length > 0">
            <div class="w-full flex items-center justify-center pl-5 pr-3 py-3 bg-base-200/40 border-2 border-base-300 rounded cursor-pointer"
                 @click="openMenu(menu)"
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
              {{ $t('restaurant.no_menus') }}
            </h3>
          </div>
        </div>
      </div>

      <div class="w-full flex flex-col grow mt-3 pb-3 gap-1 bg-base-200/80">
        <div class="w-full flex flex-col gap-3">
          <div class="w-full flex flex-col gap-1">
            <div class="w-full h-[1px] bg-base-300"/>

            <div class="w-full flex flex-col justify-start items-start py-2 px-3">
              <div class="w-full flex justify-start items-start gap-3 cursor-pointer"
                   @click="scheduleExpanded = !scheduleExpanded">
                <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
                  <CalendarClock class="w-6 h-6"/>
                </div>

                <div class="flex grow flex-col justify-center items-start">
                  <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                    {{ $t('restaurant.working_hours') }}
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
                <span class="font-semibold">{{ scheduleInfo.status === 'Open' ? $t('restaurant.open') : $t('restaurant.closed') }}:</span> {{ scheduleInfo.timeBeforeOrUntil }} {{ scheduleInfo.status === 'Open' ? $t('restaurant.until_closing') : $t('restaurant.until_opening') }}
              </p>

              <Schedule class="w-full"
                        v-if="scheduleExpanded"
                        :info="scheduleInfo"/>
            </div>

            <div class="w-full h-[1px] bg-base-300"/>
          </div>

          <div class="w-full flex justify-start items-start gap-3 px-3 cursor-pointer"
               v-if="restaurant!.phone?.length"
               @click="callPhone(restaurant!.phone)">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <Phone class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-center items-start">
              <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                {{ $t('restaurant.phone') }}
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant!.phone }}
              </p>
            </div>
          </div>

          <div class="w-full flex justify-start items-start gap-3 px-3 cursor-pointer"
               v-if="restaurant!.full_address?.length"
               @click="openMap(restaurant!.full_address)">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <MapPin class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-start items-start">
              <h3 class="text-sm font-semibold text-base-content/50">
                {{ $t('restaurant.location') }}
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant!.full_address }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <SearchDrawer :open="isSearchDrawerOpen"
                  :restaurant="restaurant"
                  :menus="menusWithProducts"
                  :loading="isLoadingMenusWithProducts"
                  @close="isSearchDrawerOpen = false"/>

    <LanguagesDrawer :open="isLanguagesDrawerOpen"
                     :locale="locale"
                     :supported_locales="supported_locales"
                     @close="isLanguagesDrawerOpen = false"
                     @switch-language="onSwitchLanguage"/>
  </div>
</template>
