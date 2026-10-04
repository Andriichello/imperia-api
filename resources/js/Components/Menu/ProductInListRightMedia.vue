<script setup lang="ts">
import {Dish} from "@/api";
import {Splide, SplideSlide} from "@splidejs/vue-splide";
import {ref, computed, PropType} from "vue";
import {DishSize, getDishSizes, priceFormatted, sizeWeightFormatted} from "@/helpers";
import {photoSources} from "@/photos";
import {Timer, Flame, TriangleAlert} from "lucide-vue-next";
import {type DishTag, getAllergenLabel, getAllergens, getDishTags} from "@/flags";
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

// the copies of the photos, which are just big enough for the list
const photos = computed(() => (props.product.media ?? []).map(photoSources));

const sizes = computed<DishSize[]>(() => getDishSizes(props.product));

// the size is picked by its id: the sizes change in the editor's preview
const selectedSizeId = ref<number | null>(sizes.value[0]?.id ?? null);

const selectedSize = computed<DishSize>(
  () => sizes.value.find((size) => size.id === selectedSizeId.value) ?? sizes.value[0]
);

const price = computed(
  () => priceFormatted(selectedSize.value.price, props.currency?.toLowerCase() ?? 'uah')
);

const tags = computed<DishTag[]>(() => getDishTags(props.product.flags));

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
      <!-- the text stretches to the photo's height, so time and calories line up with its bottom edge -->
      <div class="flex items-stretch gap-3">
        <div class="flex-1 min-w-0 flex flex-col gap-1.5">
          <h3 class="text-lg/[26px] font-semibold line-clamp-3">
            {{ product.title }}
          </h3>

          <p class="text-[15px]/[22px] text-base-content/72 line-clamp-3"
             v-if="product.description?.length">
            {{ product.description }}
          </p>

          <div class="mt-auto pt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[13px]/5 text-base-content/65"
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
        </div>

        <div class="size-28 shrink-0 self-start relative rounded-lg overflow-hidden border border-base-300 bg-base-200/20"
             v-if="photos.length">
          <!-- a list shows the first photo only: no slider for it (a list has many of them) -->
          <img class="w-full h-28 object-cover object-center"
               :src="photos[0].src" :srcset="photos[0].srcset" sizes="112px" alt=""
               loading="lazy" decoding="async"
               v-if="preview"/>

          <Splide class="size-full" v-else :options="{
                  perPage: 1,
                  perMove: 1,
                  rewind: false,
                  rewindByDrag: false,
                  drag: false,
                  arrows: false,
                  pagination: true,
                }">
            <SplideSlide v-for="(photo, index) in photos" :key="photo.src">
              <img class="w-full h-28 object-cover object-center"
                   :src="photo.src" :srcset="photo.srcset" sizes="112px" alt=""
                   :loading="index === 0 ? 'eager' : 'lazy'"/>
            </SplideSlide>
          </Splide>
        </div>
      </div>

      <!-- tags, then allergens, on one line, which wraps when they don't fit -->
      <div class="flex flex-wrap items-start gap-x-3 gap-y-0.5 text-[13px]/5"
           v-if="tags.length || allergenNames.length">
        <span class="flex items-center gap-1 text-primary-content"
              v-for="tag in tags" :key="tag.key">
          <component :is="tag.icon" class="size-3.5 shrink-0"/>
          {{ i18n.t(tag.label) }}
        </span>

        <span class="flex items-start gap-1 text-orange-700"
              v-if="allergenNames.length">
          <TriangleAlert class="size-3.5 shrink-0 mt-[3px]"/>
          <span>{{ allergenNames }}</span>
        </span>
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
                  @click.stop="selectedSizeId = size.id">
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
