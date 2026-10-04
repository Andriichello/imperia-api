<script setup lang="ts">
import { Dish } from "@/api";
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import { computed, PropType, ref, watch } from "vue";
import { DishSize, getDishSizes, priceFormatted, sizeWeightFormatted } from "@/helpers";
import { isWidePhoto, photoSources, photoUrl } from "@/photos";
import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
import { Timer, Flame, TriangleAlert } from "lucide-vue-next";
import DishTags from "@/Components/Menu/DishTags.vue";
import { getAllergenLabel, getAllergens } from "@/flags";
import { useI18n } from "vue-i18n";
import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
import { editKey } from "@/editor/editKey";

const i18n = useI18n();

const props = defineProps({
  product: {
    type: Object as PropType<Dish | null>,
    required: false,
    default: null,
  },
  currency: {
    type: String as PropType<string | null>,
    required: false,
    default: null,
  },
  establishment: {
    type: String as PropType<string | null>,
    default: 'restaurant',
  },
  open: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['close']);

// the copies of the photos, which are just big enough for the page
const photos = computed(() => (props.product?.media ?? []).map(photoSources));

// The photos are square, when the first one is at least as tall as it's wide (most are, taken by
// a phone): a square crops it a little. A wider one is in a shorter strip, which crops it less.
// Until its size is known, they're square. Either is at most 40% of the screen's height.
const isFirstPhotoWide = ref(false);

watch(() => props.product?.media?.[0] ?? null, (photo) => {
  isFirstPhotoWide.value = false;

  if (!photo) {
    return;
  }

  // the copies say it
  const isWide = isWidePhoto(photo);

  if (isWide !== null) {
    isFirstPhotoWide.value = isWide;

    return;
  }

  // a photo without them, once it's loaded (one the list has shown is known right away)
  const url = photoUrl(photo);
  const probe = new Image();
  const measure = () => {
    if (props.product?.media?.[0]?.id === photo.id && probe.naturalWidth) {
      isFirstPhotoWide.value = probe.naturalWidth > probe.naturalHeight;
    }
  };

  probe.onload = measure;
  probe.src = url;

  if (probe.complete) {
    measure();
  }
}, { immediate: true });

const photoSize = computed(() => isFirstPhotoWide.value ? 'h-65' : 'aspect-square');

const allergens = computed<string[]>(() => getAllergens(props.product?.flags));

// In the same order as in the list
const sizes = computed<DishSize[]>(() => props.product ? getDishSizes(props.product) : []);

const closePopup = () => {
  emit('close');
};
</script>

<template>
  <BaseDrawer :open="open" :padding-top="false" :restaurant-button="false" @close="closePopup">
    <div class="w-full h-full flex flex-col overflow-auto">
      <template v-if="product">
        <div class="w-full shrink-0 relative overflow-hidden border-b border-base-300"
             v-bind="editKey('dish-photos')"
             v-if="photos.length">
          <div class="absolute inset-0 overflow-hidden flex flex-col justify-center">
            <DiagonalPattern class="scale-165 text-primary-content/50"
                             :establishment="establishment ?? 'restaurant'"/>
          </div>

          <Splide id="product-media" class="w-full" :options="{
                    perPage: 1,
                    perMove: 1,
                    rewind: false,
                    rewindByDrag: false,
                    drag: photos.length > 1,
                    arrows: photos.length > 1,
                    pagination: photos.length > 1,
                  }">
            <SplideSlide v-for="(photo, index) in photos" :key="photo.src">
              <img class="w-full max-h-[40dvh] object-cover object-center"
                   :class="photoSize"
                   :src="photo.src" :srcset="photo.srcset" sizes="(min-width: 28rem) 28rem, 100vw" alt=""
                   :loading="index === 0 ? 'eager' : 'lazy'"/>
            </SplideSlide>
          </Splide>
        </div>

        <!-- Without photos: a strip under the close button -->
        <div class="w-full h-15 shrink-0 relative overflow-hidden border-b border-base-300"
             v-bind="editKey('dish-photos')"
             v-else>
          <div class="absolute inset-0 overflow-hidden flex flex-col justify-center">
            <DiagonalPattern class="scale-165 text-primary-content/50"
                             :establishment="establishment ?? 'restaurant'"/>
          </div>
        </div>

        <div class="w-full shrink-0 px-5 py-2.5 bg-primary/10 text-primary-content text-base/6 font-semibold"
             v-bind="editKey('dish-badge')"
             v-if="product.badge?.length">
          {{ product.badge }}
        </div>

        <div class="flex flex-col gap-5 pt-4 px-5 pb-20">
          <div class="flex flex-col items-start gap-2"
               v-bind="editKey('dish-text')">
            <h2 class="text-[22px]/[30px] font-semibold">
              {{ product.title }}
            </h2>

            <p class="text-base/6 text-base-content/72"
               v-if="product.description?.length">
              {{ product.description }}
            </p>
          </div>

          <div class="flex flex-col gap-2"
               v-bind="editKey('dish-sizes')">
            <h3 class="text-lg/7 font-semibold">
              {{ i18n.t('product.sizes') }}
            </h3>

            <div class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-lg border border-zinc-200"
                 v-for="size in sizes" :key="size.id ?? 'base'">
              <div class="flex flex-col gap-0.5">
                <span class="text-[17px]/6 font-semibold"
                      v-if="sizeWeightFormatted(size)">
                  {{ sizeWeightFormatted(size) }}
                </span>

                <span class="flex flex-wrap items-center gap-x-3 text-sm/5 text-base-content/65"
                      v-if="size.preparation_time || size.calories">
                  <span class="flex items-center gap-1"
                        v-if="size.preparation_time">
                    <Timer class="size-3.5 shrink-0"/>
                    {{ i18n.t('badges.time', { minutes: size.preparation_time }) }}
                  </span>

                  <span class="flex items-center gap-1"
                        v-if="size.calories">
                    <Flame class="size-3.5 shrink-0"/>
                    {{ i18n.t('badges.calories', { calories: size.calories }) }}
                  </span>
                </span>
              </div>

              <span class="text-xl/7 font-bold whitespace-nowrap">
                {{ priceFormatted(size.price, currency?.toLowerCase() ?? 'uah') }}
              </span>
            </div>
          </div>

          <DishTags class="text-sm/5" icon-class="size-4" :flags="product.flags" v-bind="editKey('dish-tags')"/>

          <div class="flex flex-col gap-1.5 p-2.5 rounded-lg bg-orange-700/6 border border-orange-700/25"
               v-bind="editKey('dish-allergens')"
               v-if="allergens.length">
            <h3 class="flex items-center gap-1 text-sm/5 font-semibold text-orange-700">
              <TriangleAlert class="size-4 shrink-0"/>
              {{ i18n.t('product.contains_allergens') }}
            </h3>

            <div class="flex flex-wrap gap-1.5">
              <span class="px-2 py-0.5 rounded bg-base-100 border border-orange-700/35 text-orange-700 text-[13px]/5 font-semibold"
                    v-for="allergen in allergens" :key="allergen">
                {{ i18n.t(getAllergenLabel(allergen)) }}
              </span>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <!-- Skeleton while the dish is loading -->
        <div class="w-full h-65 shrink-0 relative overflow-hidden border-b border-base-300">
          <div class="absolute inset-0 overflow-hidden flex flex-col justify-center">
            <DiagonalPattern class="scale-165 text-primary-content/50"
                             :establishment="establishment ?? 'restaurant'"/>
          </div>
        </div>

        <div class="flex flex-col gap-5 pt-4 px-5 pb-20">
          <div class="flex flex-col gap-2">
            <div class="h-7 w-3/4 bg-base-300/70 rounded animate-pulse"></div>
            <div class="h-4 w-full bg-base-300/50 rounded animate-pulse"></div>
            <div class="h-4 w-5/6 bg-base-300/50 rounded animate-pulse"></div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="h-6 w-24 bg-base-300/70 rounded animate-pulse"></div>

            <div class="flex items-center justify-between gap-3 px-3.5 py-3 rounded-lg border border-zinc-200"
                 v-for="n in 2" :key="n">
              <div class="flex flex-col gap-1.5">
                <div class="h-5 w-20 bg-base-300/60 rounded animate-pulse"></div>
                <div class="h-4 w-32 bg-base-300/50 rounded animate-pulse"></div>
              </div>

              <div class="h-6 w-16 bg-base-300/60 rounded animate-pulse"></div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </BaseDrawer>
</template>
