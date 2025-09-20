<script setup lang="ts">
import { Dish, Media } from "@/api";
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import { computed, PropType } from "vue";
import { priceFormatted, weightUnitFormatted } from "@/helpers";
import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
import { Timer, Flame, Vegan, Leaf, Nut, EggFried, Salad, Milk, Droplet, DropletOff, Dumbbell, MilkOff, TriangleAlert } from "lucide-vue-next";
import { useI18n } from "vue-i18n";
import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";

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

const media = computed<Media[]>(() => {
  return props.product?.media?.map((m: Media) => {
    const webp = m?.variants?.find((v: Media) => v.extension === 'webp');
    return webp ?? m;
  }) ?? [];
});

const allergens = computed<string[]>(() => {
  const flags = props.product?.flags as string[] | undefined;
  if (!flags) {
    return [];
  }
  return flags.filter((flag: string) => flag.startsWith('alg-'));
});

const getAllergenTranslation = (allergen: string) => {
  const allergenName = allergen.replace('alg-', '');
  return i18n.t(`badges.${allergenName}`);
};

const closePopup = () => {
  emit('close');
};
</script>

<template>
  <BaseDrawer :open="open" :padding-top="false" @close="closePopup">
    <div class="w-full h-full flex flex-col overflow-auto">
      <template v-if="product">
      <template v-if="product.media?.length">
        <div class="w-full max-h-65 h-65 relative z-1">
          <div class="absolute w-full top-0 h-65 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center">
            <DiagonalPattern class="scale-165 opacity-60 text-warning-content/80"
                             :establishment="establishment ?? 'restaurant'"/>
          </div>

          <Splide id="product-media" class="w-full max-h-65 h-65" :options="{
                    perPage: 1,
                    perMove: 1,
                    rewind: false,
                    rewindByDrag: false,
                    drag: Number(media!.length) > 1,
                    arrows: !(Number(media!.length)<=1),
                    pagination: Number(media!.length) > 1,
                  }">
            <SplideSlide v-for="(m, index) in media" :key="m.id">
              <img class="w-full h-65 object-cover object-center border-none"
                   :src="m.url" alt=""
                   :loading="index === 0 ? 'eager' : 'lazy'"/>
            </SplideSlide>
          </Splide>
        </div>
      </template>

      <div class="w-full h-12 relative"
           v-else-if="!product!.badge?.length">
        <div class="absolute w-full top-0 h-12 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center">
        <DiagonalPattern class="scale-165 opacity-60 text-warning-content/80"
                         :establishment="establishment ?? 'restaurant'"/>
      </div>
    </div>

      <div class="w-full text-warning-content/80 bg-warning/10 opacity-80 border-none select-none rounded-none py-3 px-2 pr-16 font-semibold text-md text-start"
           v-if="product!.badge?.length">
        {{ product.badge }}
      </div>

      <!-- Scrollable content area -->
      <div class="flex-1 pb-20">
        <div class="card-body px-4 pb-3 pt-4 rounded text-start relative">
          <div class="flex justify-between items-start gap-1">
            <div class="flex flex-col justify-start items-start gap-2">
              <div class="flex justify-between items-center">
                <div class="grow flex flex-col justify-center items-start card-title gap-0">
                  <h2 class="grow line-clamp-2 text-ellipsis flex justify-start items-center text-xl">
                    {{ product.title }}
                  </h2>
                </div>
              </div>

              <div class="flex-grow">
                <p class="opacity-80 text-[16px]">
                  {{ product.description }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-1 px-6 py-1">
          <!-- Tags and Flags -->
          <div class="flex justify-between items-end gap-0">
            <div class="flex flex-wrap gap-x-3 gap-y-0.5 normal-case text-[14px] text-base-content/60 opacity-70">
              <div v-if="product.flags?.includes('vegan')" class="flex flex-row justify-center items-center gap-1">
                <Vegan class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.vegan') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('low_calorie')" class="flex flex-row justify-center items-center gap-1">
                <Salad class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.low_calorie') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('vegetarian')" class="flex flex-row justify-center items-center gap-1">
                <Leaf class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.vegetarian') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('nuts')" class="flex flex-row justify-center items-center gap-1">
                <Nut class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.nuts') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('eggs')" class="flex flex-row justify-center items-center gap-1">
                <EggFried class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.eggs') }}
                </p>
              </div>

              <div v-if="product.flags?.find((flag: string) => flag?.endsWith('hotness'))" class="flex flex-row justify-center items-center gap-1">
                <Flame class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  <template v-if="product.flags?.includes('extreme-hotness')">
                    {{ i18n.t('badges.extreme_hot') }}
                  </template>
                  <template v-else-if="product.flags?.includes('high-hotness')">
                    {{ i18n.t('badges.high_hot') }}
                  </template>
                  <template v-else-if="product.flags?.includes('medium-hotness')">
                    {{ i18n.t('badges.medium_hot') }}
                  </template>
                  <template v-else-if="product.flags?.includes('low-hotness')">
                    {{ i18n.t('badges.low_hot') }}
                  </template>
                  <template v-else>
                    {{ i18n.t('badges.hot') }}
                  </template>
                </p>
              </div>

              <!-- Lactose related -->
              <div v-if="product.flags?.includes('lactose-free')" class="flex flex-row justify-center items-center gap-1">
                <Milk class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.lactose_free') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('dairy-free')" class="flex flex-row justify-center items-center gap-1">
                <MilkOff class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.dairy_free') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('plant-milk')" class="flex flex-row justify-center items-center gap-1">
                <Milk class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.plant_milk') }}
                </p>
              </div>

              <!-- Calorie related -->
              <div v-if="product.flags?.includes('high-calorie')" class="flex flex-row justify-center items-center gap-1">
                <Flame class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.high_calorie') }}
                </p>
              </div>

              <!-- Protein related -->
              <div v-if="product.flags?.includes('high-protein')" class="flex flex-row justify-center items-center gap-1">
                <Dumbbell class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.high_protein') }}
                </p>
              </div>

              <!-- Fat related -->
              <div v-if="product.flags?.includes('low-fat')" class="flex flex-row justify-center items-center gap-1">
                <DropletOff class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.low_fat') }}
                </p>
              </div>

              <div v-if="product.flags?.includes('high-fat')" class="flex flex-row justify-center items-center gap-1">
                <Droplet class="w-4 h-4"/>
                <p class="font-semibold pt-0.5">
                  {{ i18n.t('badges.high_fat') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Price and Variants -->
          <div class="mb-4 mt-2">
            <h4 class="font-semibold text-lg mb-3">{{ i18n.t('product.variants') }}</h4>

            <!-- Base price -->
            <div class="w-full flex flex-col justify-start items-center py-2 px-3 rounded mb-2 btn-outline border border-dashed text-base-content/75 border-base-content/40">
              <div class="w-full flex justify-between items-center">
                <div class="flex items-center gap-2">
                  <span class="font-semibold text-lg">{{ product.weight }} {{ weightUnitFormatted(product.weight_unit ?? '') }}</span>
                </div>
                <span class="font-bold text-xl">{{ priceFormatted(product.price, currency?.toLowerCase() ?? 'uah') }}</span>
              </div>

              <!-- Nutrition and timing info -->
              <div class="w-full flex justify-start gap-4 opacity-80 text-[16px]" v-if="product.preparation_time || product.calories">
                <div v-if="product.preparation_time" class="flex items-center gap-2">
                  <Timer class="w-4 h-4 text-gray-600" />
                  <span class="text-md">{{ i18n.t('badges.time', { minutes: product.preparation_time }) }}</span>
                </div>

                <div v-if="product.calories" class="flex items-center gap-2">
                  <Flame class="w-4 h-4 text-gray-600" />
                  <span class="text-md">{{ i18n.t('badges.calories', { calories: product.calories }) }}</span>
                </div>
              </div>
            </div>

            <!-- Variants -->
            <div v-if="product.variants?.length" class="space-y-2">
              <div v-for="variant in product.variants" :key="variant.id" class="w-full flex flex-col justify-start items-center py-2 px-3 rounded mb-2 btn-outline border border-dashed text-base-content/75 border-base-content/40">
                <div class="w-full flex justify-between items-center">
                  <div class="flex items-center gap-2">
                    <span class="font-semibold text-lg">{{ variant.weight }} {{ weightUnitFormatted(variant.weight_unit ?? '') }}</span>
                  </div>
                  <span class="font-bold text-xl">{{ priceFormatted(variant.price, currency?.toLowerCase() ?? 'uah') }}</span>
                </div>

                <!-- Nutrition and timing info -->
                <div class="w-full flex justify-start gap-4 opacity-80 text-[16px]" v-if="variant.preparation_time || variant.calories">
                  <div v-if="variant.preparation_time" class="flex items-center gap-2">
                    <Timer class="w-4 h-4 text-gray-600" />
                    <span class="text-md">{{ i18n.t('badges.time', { minutes: variant.preparation_time }) }}</span>
                  </div>

                  <div v-if="variant.calories" class="flex items-center gap-2">
                    <Flame class="w-4 h-4 text-gray-600" />
                    <span class="text-md">{{ i18n.t('badges.calories', { calories: variant.calories }) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6">
          <!-- Tags and Flags -->
          <div class="mb-6">
            <!-- Allergens -->
            <div v-if="allergens.length > 0" class="mb-4">
              <h4 class="font-semibold text-lg mb-2 text-orange-600/75">{{ i18n.t('badges.allergens') }}</h4>
              <div class="flex flex-wrap gap-2">
                <div v-for="allergen in allergens" :key="allergen" class="flex items-center gap-1 text-orange-600/75 border border-dashed border-orange-600/75 px-2 py-1 rounded-sm">
                  <TriangleAlert class="w-4 h-4" />
                  <span class="text-sm font-semibold">{{ getAllergenTranslation(allergen) }}</span>
                </div>
              </div>
            </div>


          </div>


        </div>
      </div>
      </template>
      <template v-else>
        <!-- Skeleton preloader while product is null -->
        <div class="w-full max-h-65 h-65 relative z-1">
          <div class="absolute w-full top-0 h-65 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center">
            <DiagonalPattern class="scale-165 opacity-60 text-warning-content/80"
                             :establishment="establishment ?? 'restaurant'"/>
          </div>
        </div>

        <div class="w-full text-warning-content/80 bg-warning/10 opacity-80 border-none select-none rounded-none py-3 px-2 pr-16 font-semibold text-md text-start">
          <div class="h-5 w-40 bg-base-300/70 rounded animate-pulse"></div>
        </div>

        <div class="flex-1 pb-20">
          <div class="card-body px-4 pb-3 pt-4 rounded text-start relative">
            <div class="flex justify-between items-start gap-1">
              <div class="flex flex-col justify-start items-start gap-2 w-full">
                <div class="h-7 w-3/4 bg-base-300/70 rounded animate-pulse"></div>
                <div class="space-y-2 w-full">
                  <div class="h-4 w-full bg-base-300/50 rounded animate-pulse"></div>
                  <div class="h-4 w-5/6 bg-base-300/50 rounded animate-pulse"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-1 px-6 py-1">
            <div class="flex flex-wrap gap-x-3 gap-y-2 opacity-70">
              <div class="h-5 w-24 bg-base-300/60 rounded animate-pulse"></div>
              <div class="h-5 w-16 bg-base-300/60 rounded animate-pulse"></div>
              <div class="h-5 w-20 bg-base-300/60 rounded animate-pulse"></div>
            </div>

            <div class="mb-4 mt-4">
              <div class="h-6 w-40 bg-base-300/70 rounded mb-3 animate-pulse"></div>
              <div class="w-full flex flex-col gap-2">
                <div class="w-full py-3 px-3 rounded mb-2 btn-outline border border-dashed text-base-content/75 border-base-content/40">
                  <div class="w-full flex justify-between items-center">
                    <div class="h-5 w-32 bg-base-300/60 rounded animate-pulse"></div>
                    <div class="h-6 w-20 bg-base-300/60 rounded animate-pulse"></div>
                  </div>
                  <div class="w-full flex justify-start gap-4 mt-2">
                    <div class="h-4 w-24 bg-base-300/50 rounded animate-pulse"></div>
                    <div class="h-4 w-28 bg-base-300/50 rounded animate-pulse"></div>
                  </div>
                </div>
                <div class="w-full py-3 px-3 rounded btn-outline border border-dashed text-base-content/75 border-base-content/40">
                  <div class="w-full flex justify-between items-center">
                    <div class="h-5 w-28 bg-base-300/60 rounded animate-pulse"></div>
                    <div class="h-6 w-16 bg-base-300/60 rounded animate-pulse"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="px-6">
            <div class="mb-6">
              <div class="h-6 w-36 bg-base-300/70 rounded mb-2 animate-pulse"></div>
              <div class="flex flex-wrap gap-2">
                <div class="h-6 w-24 bg-orange-300/40 rounded animate-pulse"></div>
                <div class="h-6 w-20 bg-orange-300/40 rounded animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </BaseDrawer>
</template>
