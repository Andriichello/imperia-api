<script setup lang="ts">
import {Dish} from "@/api";
import {type Component, computed, nextTick, PropType, ref, watch} from "vue";
import {useI18n} from "vue-i18n";
import {Flame, Layers, Timer, TriangleAlert} from "lucide-vue-next";
import {DishSize, getDishSizes, nbsp, priceFormatted, sizeWeightFormatted} from "@/helpers";
import {photoSources} from "@/photos";
import {type DishTag, getAllergenLabel, getAllergens, getDishTags, type TagTone} from "@/flags";
import {useLineCount} from "@/composables/useLineCount";
import DishTagIcon from "@/Components/Menu/DishTagIcon.vue";

/**
 * A dish in the menu list. Its photo floats on the right: the text wraps beside it, then runs on
 * at full width under it. Tags and allergens are icons; the dish page (a tap on the card) has
 * everything in full: all its sizes, the whole description, the names of its tags and allergens.
 */

// a description of up to 5 lines is shown in full, a longer one in 4 (see `.is-clamped`)
const MAX_DESC_LINES = 5;

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
  // Without side padding, for lists that pad their rows themselves
  flush: {
    type: Boolean as PropType<boolean>,
    default: false,
  },
});

const i18n = useI18n();

// the copies of the first photo, which are just big enough for the card
const photo = computed(() => props.product.media?.length ? photoSources(props.product.media[0]) : null);

// the first size is the one on the card
const sizes = computed<DishSize[]>(() => getDishSizes(props.product));

const size = computed<DishSize>(() => sizes.value[0]);

const price = computed(() => priceFormatted(size.value.price, props.currency?.toLowerCase() ?? 'uah'));

const weight = computed(() => sizeWeightFormatted(size.value));

const tags = computed<DishTag[]>(() => getDishTags(props.product.flags));

// Tags with the same icon (lactose-free and dairy-free) are one circle, named after all of them
const tagIcons = computed(() => {
  const icons = new Map<Component, { key: string, icon: Component, tone: TagTone, labels: string[] }>();

  for (const tag of tags.value) {
    const same = icons.get(tag.icon);

    if (same) {
      same.labels.push(i18n.t(tag.label));
    } else {
      icons.set(tag.icon, {key: tag.key, icon: tag.icon, tone: tag.tone, labels: [i18n.t(tag.label)]});
    }
  }

  return [...icons.values()];
});

const allergens = computed<string[]>(
  () => getAllergens(props.product.flags).map((flag) => i18n.t(getAllergenLabel(flag)))
);

const hasDetailsRow = computed<boolean>(() => !!(size.value.preparation_time || size.value.calories
  || tags.value.length || allergens.value.length));

// The description's lines are counted: they depend on the photo and the title
const description = ref<HTMLElement | null>(null);

const {lines, measure} = useLineCount(description);

// (an edited one may have fewer lines in a box of the same clamped height)
watch(() => props.product.description, () => nextTick(measure));

const isClamped = computed<boolean>(() => lines.value > MAX_DESC_LINES);
</script>

<template>
  <article class="w-full flex flex-col text-start text-[#1C1B1F]"
           :id="'product-' + product.id">

    <div class="p-2 bg-primary/10 text-primary-content text-base/6 font-semibold"
         v-if="product.badge?.length">
      {{ product.badge }}
    </div>

    <div class="flow-root py-3.5"
         :class="{'px-2': !flush}">
      <!-- first, so the lines after it wrap beside it -->
      <img class="float-right ml-3 mb-2 size-28 rounded-lg border border-[#E5E5E5] object-cover bg-[#E8E2D6]"
           :src="photo.src" :srcset="photo.srcset" sizes="112px" :alt="product.title"
           loading="lazy" decoding="async"
           v-if="photo"/>

      <h3 class="text-lg/6 font-semibold text-pretty">
        {{ product.title }}
      </h3>

      <!-- the price of the first size; the dish page has the others -->
      <div class="mt-0.5 flex flex-col items-start gap-1">
        <span class="inline-flex items-baseline gap-2 whitespace-nowrap">
          <span class="text-xl/7 font-bold text-accent">{{ price }}</span>

          <template v-if="weight">
            <span class="text-base/6 text-[#A3A2A7]" aria-hidden="true">·</span>
            <span class="text-base/6 font-semibold text-[#3C3B3F]">{{ weight }}</span>
          </template>
        </span>

        <span class="inline-flex items-center gap-1 h-[22px] px-2 rounded-full border border-primary/45 text-primary-content text-xs/4 font-semibold whitespace-nowrap"
              v-if="sizes.length > 1">
          <Layers class="size-3 shrink-0" aria-hidden="true"/>
          {{ i18n.t('menu.more_sizes') }}
        </span>
      </div>

      <p class="mt-1.5 text-[15px]/[22px] text-[#5F5E62]"
         :class="{'is-clamped': isClamped}"
         ref="description"
         v-if="product.description?.length">
        {{ product.description }}
      </p>

      <!-- a block of inline items, not a flex box: only its lines beside the photo are shorter -->
      <div class="mt-1.5 text-[13px]/[30px] text-[#6E6D71]"
           v-if="hasDetailsRow">
        <span class="inline-flex items-center gap-1 mr-3 leading-5 whitespace-nowrap align-middle"
              v-if="size.preparation_time">
          <Timer class="size-3.5 shrink-0" aria-hidden="true"/>
          {{ nbsp(i18n.t('badges.time', {minutes: size.preparation_time})) }}
        </span>

        <span class="inline-flex items-center gap-1 mr-3 leading-5 whitespace-nowrap align-middle"
              v-if="size.calories">
          <Flame class="size-3.5 shrink-0" aria-hidden="true"/>
          {{ nbsp(i18n.t('badges.calories', {calories: size.calories})) }}
        </span>

        <!-- always the first of the icons: one for all of them, the dish page names them -->
        <span class="relative inline-flex mr-3 align-middle"
              v-if="allergens.length">
          <DishTagIcon :icon="TriangleAlert" tone="allergen"/>
          <span class="sr-only">{{ i18n.t('menu.allergens') }} {{ allergens.join(', ') }}</span>
        </span>

        <span v-if="tags.length">
          <span class="relative inline-flex mr-1 last:mr-0 align-middle"
                v-for="tag in tagIcons" :key="tag.key">
            <DishTagIcon :icon="tag.icon" :tone="tag.tone"/>
            <span class="sr-only">{{ tag.labels.join(', ') }}</span>
          </span>
        </span>
      </div>
    </div>
  </article>
</template>

<style scoped>
  /* 4 lines of a long description (4 × its 22px line-height), the 4th fading out. Clipped, not
     hidden: that would start a formatting context, and the text would stop wrapping around the
     photo (as line-clamp's `display: -webkit-box` would) */
  .is-clamped {
    max-height: calc(4 * 22px);
    overflow: clip;
    -webkit-mask:
      linear-gradient(#000 0 0) 0 0 / 100% 66px no-repeat,
      linear-gradient(to right, #000 30%, transparent 85%) 0 66px / 100% 22px no-repeat;
    mask:
      linear-gradient(#000 0 0) 0 0 / 100% 66px no-repeat,
      linear-gradient(to right, #000 30%, transparent 85%) 0 66px / 100% 22px no-repeat;
  }
</style>
