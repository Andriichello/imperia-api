<script setup lang="ts">
import {Dish, DishVariant, Media} from "@/api";
  import {Splide, SplideSlide} from "@splidejs/vue-splide";
  import {ref, computed, PropType} from "vue";
  import {priceFormatted, weightUnitFormatted} from "@/helpers";
  import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
  import {Timer, Flame, Vegan, Leaf, Nut, EggFried, Salad, Milk, Droplet, DropletOff, Dumbbell, MilkOff, TriangleAlert } from "lucide-vue-next";
  import { useI18n } from "vue-i18n";

  const i18n = useI18n();

  const props = defineProps({
    product: {
      type: Object as PropType<Dish>,
      required: true,
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
    preview: {
      type: Boolean as PropType<boolean>,
      default: false,
    }
  });

  const media = computed<Media[]>(() => {
    return props.product.media.map((m: Media) => {
      const webp = m?.variants.find((v: Media) => v.extension === 'webp');
      return webp ?? m;
    })
  });

  const variants = computed<Partial<DishVariant>[]>(() => {
    if (!props.product.variants || !props.product.variants.length) {
      return null;
    }

    const variants = [...props.product.variants];
    const base = {
      id: null,
      type: 'variants',
      dish_id: props.product.id,
      price: props.product.price,
      weight: props.product.weight,
      weight_unit: props.product.weight_unit,
      calories: props.product.calories,
      preparation_time: props.product.preparation_time,
    };

    variants.push(base);

    return variants.sort((v1, v2) => {
      if (v1.price < v2.price) {
        return -1;
      }
      if (v1.price > v2.price) {
        return 1;
      }
      return 0;
    });
  });

  const selectedVariant = ref<Partial<DishVariant> | null>(
    variants.value?.find((_) => true)
  );

  const weight = computed(() => {
    let weight: number | string = props.product!.weight;
    let unit: string = props.product!.weight_unit;

    if (selectedVariant.value) {
      weight = selectedVariant.value?.weight;
      unit = selectedVariant.value?.weight_unit;
    }

    unit = unit ? weightUnitFormatted(unit) : '';
    weight = weight ?? '';

    return weight + ' ' + unit;
  });

  const price = computed(() => {
    let price = props.product!.price;

    if (selectedVariant.value) {
      price = selectedVariant.value?.price;
    }

    return priceFormatted(price, props.currency?.toLowerCase() ?? 'uah');
  });

  const calories = computed(() => {
    if (selectedVariant.value?.id) {
      return selectedVariant.value?.calories;
    }

    return props.product!.calories;
  });

  const preparationTime = computed(() => {
    if (selectedVariant.value?.id) {
      return selectedVariant.value?.preparation_time;
    }

    return props.product!.preparation_time;
  });

  const allergens = computed(() => {
    if (!props.product.flags) return [];
    return props.product.flags.filter(flag => flag.startsWith('alg-'));
  });

  const getAllergenTranslation = (allergen: string) => {
    // Remove 'alg-' prefix to get the allergen name
    const allergenName = allergen.replace('alg-', '');
    return i18n.t(`badges.${allergenName}`);
  };

  const variantWeight = (variant: Partial<DishVariant>) => {
    return variant.weight + ' '
      + (weightUnitFormatted((variant?.id ? variant.weight_unit : props.product!.weight_unit) ?? ''));
  };

  const selectVariant = (variant: Partial<DishVariant> | null) => {
    selectedVariant.value = variant;
  };
</script>

<template>
  <div class="w-full flex flex-col rounded shadow-md bg-base-100"
       :id="'product-' + product.id">

    <template v-if="product.media?.length">
      <div class="w-full h-45 rounded-t relative">
        <div class="absolute w-full top-0 h-45 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center rounded-t">
          <DiagonalPattern class="scale-165 opacity-60 text-warning-content/80"
                           :establishment="establishment ?? 'restaurant'"/>
        </div>

        <Splide class="w-full h-45 rounded-t" :options="{
                    perPage: 1,
                    perMove: 1,
                    rewind: false,
                    rewindByDrag: false,
                    drag: !preview && Number(media!.length) > 1,
                    arrows: !preview && !(Number(media!.length)<=1),
                    pagination: !preview,
                  }">
          <SplideSlide v-for="(m, index) in (preview ? [media[0]] : media)" :key="m.id">
            <img class="w-full h-45 object-cover object-center rounded-t border-none"
                 :src="m.url" alt=""
                 :loading="index === 0 ? 'eager' : 'lazy'"/>
          </SplideSlide>
        </Splide>
      </div>
    </template>

    <div class="card-body min-h-[100px] rounded text-start relative">
      <div class="absolute top-0 right-0">
        <div class="badge-md badge badge-warning text-warning-content bg-warning/60 select-none rounded-tl-none rounded-br-none translate-y-[-1px] translate-x-[1px] py-3 px-5 font-semibold"
             :class="{'rounded-tr-sm': !product.media?.length, 'rounded-tr-none': product.media?.length}"
             v-if="product!.badge?.length">
          {{ product.badge }}
        </div>
      </div>

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

      <div class="card-actions justify-between items-end gap-0">
        <div class="w-full flex flex-wrap gap-x-3 gap-y-0.5 normal-case text-[12px] text-base-content/60">
          <!-- Allergens - Combined Badge -->
          <div v-if="allergens.length > 0" class="relative tooltip">
            <div @click="toggleAllergensList" class="flex flex-row justify-center items-center gap-1 cursor-pointer text-orange-600/75 border border-1 border-dashed border-orange-600/75 pl-1 pr-2 rounded-sm opacity-80">
              <TriangleAlert class="w-4 h-4"/>
              <p class="font-semibold pt-0.5">
                {{ i18n.t('badges.allergens') }}
              </p>
            </div>

            <!-- Allergens Popover -->
            <div class="tooltip-content absolute z-4 rounded-sm bg-base-100 text-orange-600 border-orange-600 border-1 border-dashed">
              <div class="flex flex-col gap-x-2 gap-y-0.5">
                <div v-for="allergen in allergens" :key="allergen" class="flex flex-row items-start opacity-80">
                  <p class="text-start font-semibold text-[12px] pb-0.5">
                    - {{ getAllergenTranslation(allergen) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="preparationTime" class="flex flex-row justify-center items-center gap-1 opacity-70">
            <Timer class="w-4 h-4"/>
            <p class="font-semibold pt-0.5">
              {{ i18n.t('badges.time', { minutes: preparationTime }) }}
            </p>
          </div>

          <div v-if="calories" class="flex flex-row justify-center items-center gap-1 opacity-70">
            <Flame class="w-4 h-4"/>
            <p class="font-semibold pt-0.5">
              {{ i18n.t('badges.calories', { calories: calories }) }}
            </p>
          </div>
        </div>

        <div class="flex flex-wrap gap-x-3 gap-y-0.5 normal-case text-[12px] text-base-content/60 opacity-70 mt-1">
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

      <div class="card-actions justify-between items-end">
        <div class="flex gap-1">
          <template v-if="variants?.length">
            <template v-for="v in variants" :key="v.id">
              <button class="btn btn-sm normal-case text-[14px] px-2 py-2"
                      :class="{'btn-warning bg-warning/20 border-warning/40': selectedVariant?.id === v.id, 'btn-outline border-dashed text-base-content/75 border-base-content/40': selectedVariant?.id !== v.id}"
                      @click="selectVariant(v)">
                {{ variantWeight(v) }}
              </button>
            </template>
          </template>

          <template v-else>
            <div class="p-0 justify-end">
              <span class="font-bold text-[16px]">
                {{ weight }}
              </span>
            </div>
          </template>
        </div>

        <div class="p-0 justify-end">
          <h2 class="card-title text-xl grow">
            {{ price }}
          </h2>
        </div>
      </div>
    </div>
  </div>
</template>
