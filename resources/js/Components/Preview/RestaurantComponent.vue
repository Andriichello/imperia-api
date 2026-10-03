<script setup lang="ts">
  import {computed, PropType, ref} from "vue";
  import {DateTime} from "luxon";
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
  import {DishMenu, Media, Restaurant} from "@/api";
  import Schedule from "@/Components/Restaurant/Schedule.vue";
  import {getScheduleInfo, ScheduleInfo, time} from "@/helpers";
  import {editKey} from "@/editor/editKey";
  import PageFooter from "@/Components/Base/PageFooter.vue";
  import ReviewsRow from "@/Components/Reviews/ReviewsRow.vue";
  import { useI18n } from 'vue-i18n';

  const props = defineProps({
    restaurant: {
      type: Object as PropType<Restaurant>,
      required: true,
    },
    menus: {
      type: Array as PropType<DishMenu[]>,
      required: true,
    },
  });

  const emits = defineEmits(['open-menu']);

  const i18n = useI18n();

  const media = computed<Media[]>(() => {
    return props.restaurant.media.map((m: Media) => {
      const webp = m?.variants?.find((v: Media) => v.extension === 'webp');
      return webp ?? m;
    })
  });

  // follows the photos (they change in the editor's preview)
  const slideOptions = computed(() => ({
    perPage: 1,
    perMove: 1,
    rewind: false,
    rewindByDrag: false,
    drag: (media.value?.length ?? 0) > 1,
    arrows: (media.value?.length ?? 0) > 1,
    pagination: true,
  }));

  const scheduleInfo = computed<ScheduleInfo>(
    () => getScheduleInfo(props.restaurant)
  );

  /** Colors of the working hours status: dot and state word. */
  const STATUS_TONES = {
    open: {dot: 'bg-green-600', text: 'text-green-700'},
    soon: {dot: 'bg-yellow-600', text: 'text-yellow-800'},
    closed: {dot: 'bg-red-600', text: 'text-red-700'},
  };

  /** "14 Oct" in the page's language. */
  function shortDate(date: string): string {
    return DateTime.fromISO(date)
      .setLocale(i18n.locale.value)
      .toLocaleString({day: 'numeric', month: 'short'});
  }

  /**
   * Working hours status: "● Open · until 23:00", with a line of details in the last hour
   * and the name of today's special day (a holiday, a short day).
   */
  const scheduleStatus = computed(() => {
    const {state, relevant, minutesLeft, opensToday, special, closedUntil} = scheduleInfo.value;

    // closed for a while (e.g. for a renovation): till when, and why
    if (state === 'temporarily_closed') {
      return {
        tone: STATUS_TONES.closed,
        label: i18n.t('schedule.temporarily_closed'),
        detail: i18n.t('schedule.until_date', {date: shortDate(closedUntil as string)}),
        note: props.restaurant.closed_reason || null,
      };
    }

    if (!relevant) {
      return null;
    }

    const closesAt = time(relevant.end_hour, relevant.end_minute);
    const opensAt = time(relevant.beg_hour, relevant.beg_minute);
    const minutes = minutesLeft ?? 0;
    let status;

    switch (state) {
      case 'open':
        status = {
          tone: STATUS_TONES.open,
          label: i18n.t('restaurant.open'),
          detail: i18n.t('schedule.until', {time: closesAt}),
          note: null,
        };
        break;
      case 'closing_soon':
        status = {
          tone: STATUS_TONES.soon,
          label: i18n.t('schedule.closing_soon'),
          detail: closesAt,
          note: i18n.t('schedule.closes_in', {count: minutes}, minutes),
        };
        break;
      case 'opens_soon':
        status = {
          tone: STATUS_TONES.soon,
          label: i18n.t('schedule.opens_soon'),
          detail: opensAt,
          note: i18n.t('schedule.opens_in', {count: minutes}, minutes),
        };
        break;
      default:
        status = {
          tone: STATUS_TONES.closed,
          label: i18n.t('restaurant.closed'),
          detail: opensToday
            ? i18n.t('schedule.opens_at', {time: opensAt})
            : i18n.t('schedule.opens_day_at', {day: i18n.t('schedule.short.' + relevant.weekday), time: opensAt}),
          note: null,
        };
    }

    if (special?.reason) {
      status.note = status.note ? `${special.reason} · ${status.note}` : special.reason;
    }

    return status;
  });

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
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80">
    <div class="w-full max-w-md flex-1 flex flex-col justify-start items-center relative">
      <Splide class="w-full h-75" :options="slideOptions"
              v-bind="editKey('photos')"
              v-if="media?.length > 0">
        <SplideSlide v-for="(m, index) in media ?? []" :key="m.id">
          <img class="w-full h-75 object-cover object-center"
               :src="m.url" alt=""
               :loading="index === 0 ? 'eager' : 'lazy'"/>
        </SplideSlide>
      </Splide>

      <div class="w-full h-75"
           v-bind="editKey('photos')"
           v-else>
      </div>

      <div class="w-full pt-3 pb-1 px-3 text-center"
           v-bind="editKey('details')">
        <h1 class="text-2xl font-bold">
          {{ restaurant!.name }}
        </h1>

        <p class="text-base/6 text-base-content/65">
          {{ getEstablishmentTitle }}
        </p>
      </div>

      <div class="w-full pt-1 pb-3 px-3 pr-6 chat chat-start flex flex-col gap-1.5 translate-x-0.5"
           v-bind="editKey('notes')"
           v-if="restaurant!.notes!?.length > 0">
        <!-- The corner the tail comes out of stays square -->
        <p class="w-full chat-bubble bg-primary/15 text-primary-content rounded-xl rounded-es-none"
           v-for="(note, index) in restaurant!.notes" :key="index">
          {{ note }}
        </p>
      </div>

      <div class="w-full flex flex-col grow pt-2 pb-3 px-3 gap-2"
           v-bind="editKey('menus')">
        <template v-if="menus!.length > 0">
          <button type="button"
                  class="w-full flex items-center justify-center py-3 pr-3 pl-5 bg-base-100 border-2 border-base-300 rounded text-start cursor-pointer"
                  @click="emits('open-menu', menu)"
                  v-for="menu in menus" :key="menu.id">
            <span class="w-full flex flex-col">
              <span class="text-xl/7 font-bold">
                {{ menu.title }}
              </span>

              <span class="text-base/6 text-base-content/65"
                    v-if="menu.description?.length">
                {{ menu.description }}
              </span>
            </span>

            <ChevronRight class="size-7 shrink-0 text-primary-content"/>
          </button>
        </template>

        <div class="w-full flex items-center justify-center px-3 py-3" v-else>
          <h3 class="text-xl font-bold text-center text-base-content/65">
            {{ i18n.t('restaurant.no_menus') }}
          </h3>
        </div>
      </div>

      <div class="w-full flex flex-col grow mt-3 pb-3 gap-3 bg-base-200">
        <div class="w-full flex flex-col gap-1"
             v-bind="editKey('hours')"
             v-if="scheduleStatus">
          <div class="w-full h-px bg-base-300"/>

          <div class="w-full flex flex-col justify-start items-start py-2 px-3">
            <button type="button"
                    class="w-full flex justify-start items-start gap-3 text-start cursor-pointer"
                    :aria-expanded="scheduleExpanded"
                    @click="scheduleExpanded = !scheduleExpanded">
              <span class="size-12 min-w-12 flex justify-center items-center bg-primary/15 border border-primary/40 text-primary-content rounded">
                <CalendarClock class="size-6"/>
              </span>

              <span class="grow min-h-12 flex flex-col justify-center items-start">
                <span class="text-sm/5 font-semibold text-base-content/65">
                  {{ i18n.t('restaurant.working_hours') }}
                </span>

                <span class="flex flex-wrap items-center gap-x-1.5 text-base/6 font-semibold">
                  <span class="size-2 shrink-0 rounded-full" :class="scheduleStatus.tone.dot" aria-hidden="true"/>
                  <span :class="scheduleStatus.tone.text">{{ scheduleStatus.label }}</span>
                  <span class="text-base-content/72">· {{ scheduleStatus.detail }}</span>
                </span>

                <span class="text-sm/5 text-base-content/72"
                      v-if="scheduleStatus.note">
                  {{ scheduleStatus.note }}
                </span>
              </span>

              <span class="size-12 min-w-12 mr-1.5 flex justify-center items-center text-base-content/65">
                <ChevronUp class="size-6" v-if="scheduleExpanded"/>
                <ChevronDown class="size-6" v-else/>
              </span>
            </button>

            <Schedule class="mt-1"
                      v-if="scheduleExpanded"
                      :info="scheduleInfo"/>
          </div>

          <div class="w-full h-px bg-base-300"/>
        </div>

        <div class="w-full flex justify-start items-start gap-3 px-3"
             v-bind="editKey('contact')"
             v-if="restaurant!.phone?.length">
          <div class="size-12 min-w-12 flex justify-center items-center bg-primary/15 border border-primary/60 text-primary-content rounded">
            <Phone class="size-6"/>
          </div>

          <div class="w-full flex flex-col justify-center items-start">
            <h3 class="text-sm/5 font-semibold text-base-content/65 translate-y-0.5">
              {{ i18n.t('restaurant.phone') }}
            </h3>
            <p class="text-base/6 font-semibold">
              {{ restaurant!.phone }}
            </p>
          </div>

          <button type="button"
                  class="w-13 min-w-13 h-12 flex justify-center items-center rounded text-base-content/65 cursor-pointer pr-1"
                  :aria-label="i18n.t('restaurant.copy_phone')"
                  @click="copyToClipboard(restaurant!.phone)">
            <Copy class="size-6"/>
          </button>
        </div>

        <div class="w-full flex justify-start items-start gap-3 px-3"
             v-bind="editKey('contact')"
             v-if="restaurant!.full_address?.length">
          <div class="size-12 min-w-12 flex justify-center items-center bg-primary/15 border border-primary/60 text-primary-content rounded">
            <MapPin class="size-6"/>
          </div>

          <div class="w-full flex flex-col justify-start items-start">
            <h3 class="text-sm/5 font-semibold text-base-content/65">
              {{ i18n.t('restaurant.location') }}
            </h3>
            <p class="text-base/6 font-semibold">
              {{ restaurant!.full_address }}
            </p>
          </div>

          <button type="button"
                  class="w-13 min-w-13 h-12 flex justify-center items-center rounded text-base-content/65 cursor-pointer pr-1"
                  :aria-label="i18n.t('restaurant.copy_address')"
                  @click="copyToClipboard(restaurant!.full_address)">
            <Copy class="size-6"/>
          </button>
        </div>

        <ReviewsRow/>
      </div>

      <PageFooter/>
    </div>
  </div>
</template>
