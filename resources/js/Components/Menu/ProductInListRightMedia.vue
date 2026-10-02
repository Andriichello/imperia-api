<script setup lang="ts">
import {Dish, Media} from "@/api";
import {Splide, SplideSlide} from "@splidejs/vue-splide";
import {ref, computed, PropType} from "vue";
import {DishSize, getDishSizes, priceFormatted, sizeWeightFormatted} from "@/helpers";
import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
import {Timer, Flame, TriangleAlert} from "lucide-vue-next";
import DishTags from "@/Components/Menu/DishTags.vue";
import {getAllergenLabel, getAllergens, getDishTags} from "@/flags";
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
  },
  // Without side padding, for lists that pad their rows themselves
  flush: {
    type: Boolean as PropType<boolean>,
    default: false,
  },
});

const emit = defineEmits(['productClick']);

const handleProductClick = () => {
  emit('productClick', props.product);
};

const media = computed<Media[]>(() => {
  return (props.product.media ?? []).map((m: Media) => {
    const webp = m?.variants?.find((v: Media) => v.extension === 'webp');
    return webp ?? m;
  })
});

const sizes = computed<DishSize[]>(() => getDishSizes(props.product));

const selectedSize = ref<DishSize>(sizes.value[0]);

const price = computed(
  () => priceFormatted(selectedSize.value.price, props.currency?.toLowerCase() ?? 'uah')
);

const hasTags = computed<boolean>(() => getDishTags(props.product.flags).length > 0);

const allergenNames = computed<string>(
  () => getAllergens(props.product.flags).map((flag) => i18n.t(getAllergenLabel(flag))).join(', ')
);
</script>

<template>
  <div class="w-full flex flex-col text-start"
       :class="{'mt-2': product.badge?.length}"
       :id="'product-' + product.id"
       @click="handleProductClick">

    <div class="w-full p-2 bg-primary/10 text-primary-content text-base/6 font-semibold select-none"
         v-if="product.badge?.length">
      {{ product.badge }}
    </div>

    <div class="flex flex-col gap-2.5"
         :class="flush ? 'pt-3 pb-3.5' : 'px-2 py-3.5'">
      <div class="flex items-start gap-3">
        <div class="flex-1 min-w-0 flex flex-col gap-1.5">
          <h3 class="text-lg/[26px] font-semibold line-clamp-3">
            {{ product.title }}
          </h3>

          <p class="text-[15px]/[22px] text-base-content/72 line-clamp-3"
             v-if="product.description?.length">
            {{ product.description }}
          </p>
        </div>

        <div class="size-28 shrink-0 relative rounded-lg overflow-hidden border border-base-300 bg-base-200/20"
             v-if="media.length">
          <div class="absolute inset-0 overflow-hidden flex flex-col justify-center">
            <DiagonalPattern class="scale-165 text-primary-content/50"
                             :establishment="establishment ?? 'restaurant'"/>
          </div>

          <Splide class="size-full" :options="{
                  perPage: 1,
                  perMove: 1,
                  rewind: false,
                  rewindByDrag: false,
                  drag: false,
                  arrows: false,
                  pagination: !preview,
                }">
            <SplideSlide v-for="(m, index) in (preview ? [media[0]] : media)" :key="m.id">
              <img class="w-full h-28 object-cover object-center"
                   :src="m.url" alt=""
                   :loading="index === 0 ? 'eager' : 'lazy'"/>
            </SplideSlide>
          </Splide>
        </div>
      </div>

      <div class="flex flex-col gap-1"
           v-if="selectedSize.preparation_time || selectedSize.calories || hasTags || allergenNames.length">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[13px]/5 text-base-content/65"
             v-if="selectedSize.preparation_time || selectedSize.calories">
          <span class="flex items-center gap-1"
                v-if="selectedSize.preparation_time">
            <Timer class="size-3.5 shrink-0"/>
            {{ i18n.t('badges.time', { minutes: selectedSize.preparation_time }) }}
          </span>

          <span class="flex items-center gap-1"
                v-if="selectedSize.calories">
            <Flame class="size-3.5 shrink-0"/>
            {{ i18n.t('badges.calories', { calories: selectedSize.calories }) }}
          </span>
        </div>

        <DishTags class="text-[13px]/5" :flags="product.flags"/>

        <div class="flex items-start gap-1 text-[13px]/5 font-semibold text-orange-700"
             v-if="allergenNames.length">
          <TriangleAlert class="size-3.5 shrink-0 mt-[3px]"/>
          <span>{{ allergenNames }}</span>
        </div>
      </div>

      <div class="flex justify-between gap-2"
           :class="sizes.length > 1 ? 'items-end' : 'items-center'">
        <div class="flex flex-wrap gap-1.5 min-w-0"
             v-if="sizes.length > 1">
          <button type="button"
                  class="h-10 px-3 rounded border text-sm font-semibold whitespace-nowrap cursor-pointer"
                  :class="selectedSize.id === size.id
                    ? 'bg-primary/20 border-primary/40 text-primary-content'
                    : 'border-dashed border-base-content/40 text-base-content/75'"
                  :aria-pressed="selectedSize.id === size.id"
                  v-for="size in sizes" :key="size.id ?? 'base'"
                  @click.stop="selectedSize = size">
            {{ sizeWeightFormatted(size) }}
          </button>
        </div>

        <span class="text-base/6 font-bold"
              v-else-if="sizeWeightFormatted(selectedSize)">
          {{ sizeWeightFormatted(selectedSize) }}
        </span>

        <span class="ml-auto text-xl/7 font-bold whitespace-nowrap"
              :class="{'pb-1.5': sizes.length > 1}">
          {{ price }}
        </span>
      </div>
    </div>
  </div>
</template>
