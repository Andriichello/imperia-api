<script setup lang="ts">
import { Dish } from "@/api";
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import { computed, PropType, ref, watch } from "vue";
import { DishSize, getDishSizes, nbsp, priceFormatted, sizeWeightFormatted } from "@/helpers";
import { isWidePhoto, photoSources, photoUrl } from "@/photos";
import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
import { Timer, Flame, TriangleAlert } from "lucide-vue-next";
import DishTagIcon from "@/Components/Menu/DishTagIcon.vue";
import { type DishTag, getAllergenLabel, getAllergens, getDishTags } from "@/flags";
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

const tags = computed<DishTag[]>(() => getDishTags(props.product?.flags));

const price = (size: DishSize) => priceFormatted(size.price, props.currency?.toLowerCase() ?? 'uah');

const time = (size: DishSize) => nbsp(i18n.t('badges.time', {minutes: size.preparation_time}));

const kcal = (size: DishSize) => nbsp(i18n.t('badges.calories', {calories: size.calories}));

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

        <!-- Under the photos: everything in full -->
        <div class="grow flex flex-col gap-6 pt-4 px-5 pb-8 bg-[#F9F9F9] text-[#1C1B1F]">
          <header class="flex flex-col items-start gap-1.5"
                  v-bind="editKey('dish-text')">
            <span class="mb-0.5 px-2 py-0.5 rounded-md bg-primary/16 text-primary-content text-xs/[18px] font-bold"
                  v-bind="editKey('dish-badge')"
                  v-if="product.badge?.length">
              {{ product.badge }}
            </span>

            <h1 class="text-2xl/[30px] font-bold text-pretty">
              {{ product.title }}
            </h1>

            <!-- One size: its price, weight, time and calories (more sizes are in their table) -->
            <div class="flex flex-col items-start gap-1.5"
                 v-bind="editKey('dish-sizes')"
                 v-if="sizes.length === 1">
              <div class="flex flex-wrap items-baseline gap-x-2">
                <span class="text-[22px]/[30px] font-bold whitespace-nowrap text-(--dish-price)">{{ price(sizes[0]) }}</span>

                <template v-if="sizeWeightFormatted(sizes[0])">
                  <span class="text-[17px]/6 text-[#A3A2A7]" aria-hidden="true">·</span>
                  <span class="text-[17px]/6 font-semibold whitespace-nowrap text-[#3C3B3F]">{{ sizeWeightFormatted(sizes[0]) }}</span>
                </template>
              </div>

              <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm/5 text-[#6E6D71]"
                   v-if="sizes[0].preparation_time || sizes[0].calories">
                <span class="inline-flex items-center gap-[5px] whitespace-nowrap"
                      v-if="sizes[0].preparation_time">
                  <Timer class="size-[15px] shrink-0" aria-hidden="true"/>
                  {{ time(sizes[0]) }}
                </span>

                <span class="inline-flex items-center gap-[5px] whitespace-nowrap"
                      v-if="sizes[0].calories">
                  <Flame class="size-[15px] shrink-0" aria-hidden="true"/>
                  {{ kcal(sizes[0]) }}
                </span>
              </div>
            </div>

            <p class="mt-1.5 text-base/6 text-[#5F5E62]"
               v-if="product.description?.length">
              {{ product.description }}
            </p>
          </header>

          <section class="flex flex-col gap-2"
                   v-bind="editKey('dish-sizes')"
                   v-if="sizes.length > 1">
            <h2 class="text-[13px]/[18px] font-bold uppercase tracking-[.04em] text-[#6E6D71]">
              {{ i18n.t('product.sizes') }}
            </h2>

            <!-- the 1px gaps between the rows are their dividers -->
            <div class="flex flex-col gap-px rounded-xl overflow-hidden bg-[#EDEDED] shadow-[0_0_0_1px_#E5E5E5]">
              <div class="min-h-14 flex items-center gap-3 px-3.5 py-2.5 bg-white"
                   v-for="(size, index) in sizes" :key="size.id ?? index">
                <div class="flex-1 min-w-0 flex flex-col gap-0.5">
                  <span class="text-base/[22px] font-semibold text-[#3C3B3F]"
                        v-if="sizeWeightFormatted(size)">
                    {{ sizeWeightFormatted(size) }}
                  </span>

                  <span class="flex flex-wrap gap-x-3 text-[13px]/5 text-[#6E6D71]"
                        v-if="size.preparation_time || size.calories">
                    <span class="inline-flex items-center gap-1 whitespace-nowrap"
                          v-if="size.preparation_time">
                      <Timer class="size-[13px] shrink-0" aria-hidden="true"/>
                      {{ time(size) }}
                    </span>

                    <span class="inline-flex items-center gap-1 whitespace-nowrap"
                          v-if="size.calories">
                      <Flame class="size-[13px] shrink-0" aria-hidden="true"/>
                      {{ kcal(size) }}
                    </span>
                  </span>
                </div>

                <span class="text-[22px]/[30px] font-bold whitespace-nowrap text-(--dish-price)">{{ price(size) }}</span>
              </div>
            </div>
          </section>

          <section class="flex flex-col gap-2.5"
                   v-bind="editKey('dish-tags')"
                   v-if="tags.length">
            <h2 class="text-[13px]/[18px] font-bold uppercase tracking-[.04em] text-[#6E6D71]">
              {{ i18n.t('product.features') }}
            </h2>

            <div class="flex flex-wrap gap-x-[18px] gap-y-2.5">
              <span class="inline-flex items-center gap-2 text-[15px]/[22px] whitespace-nowrap text-[#3C3B3F]"
                    v-for="tag in tags" :key="tag.key">
                <DishTagIcon :icon="tag.icon" :tone="tag.tone" large/>
                {{ i18n.t(tag.label) }}
              </span>
            </div>
          </section>

          <section class="flex flex-col gap-2 px-3.5 py-3 rounded-xl bg-[#FDF4EE] border border-[#F2D3BF]"
                   v-bind="editKey('dish-allergens')"
                   v-if="allergens.length">
            <h2 class="flex items-center gap-1.5 text-sm/5 font-bold text-[#B4410C]">
              <TriangleAlert class="size-4 shrink-0" aria-hidden="true"/>
              {{ i18n.t('product.contains_allergens') }}
            </h2>

            <div class="flex flex-wrap gap-1.5">
              <span class="px-[9px] py-[3px] rounded-md bg-white border border-[#EDC3A7] text-[#A33B0B] text-[13px]/[18px] font-semibold"
                    v-for="allergen in allergens" :key="allergen">
                {{ i18n.t(getAllergenLabel(allergen)) }}
              </span>
            </div>
          </section>
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
