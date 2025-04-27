<script setup lang="ts">
  import {computed, ref} from "vue";
  import {Splide, SplideSlide} from '@splidejs/vue-splide';
  import {ChevronRight, ChevronDown, CalendarClock, DoorOpen, Lock, MapPin, Phone, Circle} from 'lucide-vue-next';

  const props = defineProps({
    restaurant: Object,
    menus: Object,
  });

  console.log(props.restaurant);
  console.log(props.menus);

  const slideOptions = ref({
    perPage: 1,
    perMove: 1,
    rewind: false,
    rewindByDrag: false,
    drag: Number(props.restaurant?.media?.length) > 1,
    arrows: Number(props.restaurant?.media?.length) > 1,
    pagination: true,
  });

  const isOpen = computed(() => {
    return true;
  });
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center ">
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <Splide class="w-full h-75" :options="slideOptions"
              v-if="restaurant?.media?.length > 0">
        <SplideSlide v-for="(media, index) in restaurant?.media ?? []" :key="media.id">
          <img class="w-full h-75 object-cover object-center"
               :src="media.url" :alt="`Slide ${index}`"
               :loading="index === 0 ? 'eager' : 'lazy'"/>
        </SplideSlide>
      </Splide>

      <div class="w-full pt-3 pb-1 px-3 text-center bg-base-100">
        <h3 class="text-3xl font-bold">
          {{ restaurant.name }}
        </h3>

        <p class="text-md -translate-y-0.5 opacity-70">
          {{ 'cafe' }}
        </p>
      </div>

      <div class="w-full pt-1 pb-3 px-3 bg-base-100"
           v-if="restaurant.notes?.length > 0">
        <ul>
          <template v-for="(note, index) in restaurant.notes" :key="index">
            <li class="w-full flex justify-center items-start px-2 gap-3">

              <Circle class="w-2 h-6"/>

              <p class="w-full">
                {{ note }}
              </p>
            </li>
          </template>
        </ul>
      </div>

      <div class="w-full flex flex-col grow pt-2 pb-3 px-3 gap-2">
        <div class="w-full flex flex-col grow gap-2">
          <template v-if="menus?.length > 0">
            <div class="w-full flex items-center justify-center px-3 py-3 bg-base-100/60 border-2 border-base-300 rounded"
                 v-for="menu in menus" :key="menu.id">
              <div class="w-full flex flex-col">
                <h3 class="text-2xl font-bold">
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

      <div class="w-full flex flex-col grow py-3 px-3 gap-1">
        <div class="w-full flex flex-col gap-3">
          <div class="w-full flex justify-start items-center gap-3" v-if="isOpen">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <DoorOpen class="w-6.5 h-6.5"/>
            </div>

            <div class="w-full flex flex-col justify-center items-start translate-y-0.5">
              <h3 class="text-lg font-bold translate-y-0.5">
                Open
              </h3>
              <p class="text-md -translate-y-1">
                7h 49m until closing
              </p>
            </div>
          </div>

          <div class="w-full flex justify-start items-center gap-3" v-else>
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <Lock class="w-7.5 h-7.5"/>
            </div>

            <div class="w-full flex flex-col justify-center items-start">
              <h3 class="text-lg font-bold translate-y-0.5">
                Closed
              </h3>
              <p class="text-md -translate-y-0.5">
                12h 15m until opening
              </p>
            </div>
          </div>

          <div class="w-full flex justify-start items-start gap-3">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <CalendarClock class="w-6 h-6"/>
            </div>

            <div class="flex grow flex-col justify-center items-start">
              <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                Working hours:
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                10:00 - 22:00
              </p>
            </div>

            <div class="w-12 min-w-12 h-12 flex justify-center items-center" v-show="false">
              <ChevronDown class="w-6 h-6"/>
            </div>
          </div>

          <div class="w-full flex justify-start items-start gap-3"
               v-if="restaurant?.phone?.length">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <Phone class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-center items-start">
              <h3 class="text-sm font-semibold text-base-content/50 translate-y-0.5">
                Phone:
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant.phone }}
              </p>
            </div>
          </div>

          <div class="w-full flex justify-start items-start gap-3"
               v-if="restaurant?.full_address?.length">
            <div class="w-12 min-w-12 h-12 flex justify-center items-center bg-base-300/80 rounded">
              <MapPin class="w-6 h-6"/>
            </div>

            <div class="w-full flex flex-col justify-start items-start">
              <h3 class="text-sm font-semibold text-base-content/50">
                Location:
              </h3>
              <p class="text-md text-base-content/90 font-semibold">
                {{ restaurant.full_address }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
