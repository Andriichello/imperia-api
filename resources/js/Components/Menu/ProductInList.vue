<script setup lang="ts">
  import {Product, ProductVariant} from "@/api";
  import {Splide, SplideSlide} from "@splidejs/vue-splide";
  import {ref, computed} from "vue";

  const props = defineProps({
    product: Object as PropType<Product>,
  });

  const variants = computed<Partial<ProductVariant>[]>(() => {
    if (!props.product.variants || !props.product.variants.length) {
      return null;
    }

    const variants = [...props.product.variants];
    const base = {
      id: null,
      productId: props.product.id,
      price: props.product.price,
      weight: props.product.weight,
      weightUnit: props.product.weightUnit,
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

  const variant = ref<Partial<ProductVariant>>(variants.value?.find((_) => true));
</script>

<template>
  <div class="w-full flex flex-col rounded shadow-lg bg-base-100">

    <template v-if="product.media?.length">
      <Splide class="w-full h-45 rounded-t" :options="{
                    perPage: 1,
                    perMove: 1,
                    rewind: false,
                    rewindByDrag: false,
                    drag: Number(product.media!.length) > 1,
                    arrows: Number(product.media!.length) > 1,
                    pagination: true,
                  }">
        <SplideSlide v-for="(media, index) in product.media" :key="media.id">
          <img class="w-full h-45 object-cover object-center rounded-t"
               :src="media.url" :alt="`Dish preview #${index}`"
               :loading="index === 0 ? 'eager' : 'lazy'"/>
        </SplideSlide>
      </Splide>
    </template>

    <div class="card-body min-h-[100px] rounded text-start">
      <div class="flex justify-between items-center">
        <div class="grow flex justify-center items-start card-title">
          <h2 class="grow line-clamp-2 text-ellipsis flex justify-start items-center text-xl">
            {{ product.title }}
          </h2>
          <div class="badge badge-warning mt-1 select-none" v-if="product!.badge?.length">
            {{ product.badge }}
          </div>
        </div>
      </div>

      <div class="flex-grow">
        <p class="opacity-80 text-md">
          {{ product.description }}
        </p>
      </div>

      <div class="card-actions justify-between items-end pt-2">
        <div class="flex gap-1">
          <template v-if="variants?.length">
            <!--            <button class="btn btn-sm normal-case rounded-none text-md px-2 py-2" v-if="weight"-->
            <!--                    :class="{'btn-neutral': theme!== 'dark' && (!variant || !variants), 'btn-outline': variant && variants, 'btn-selected': theme === 'dark' && (!variant || !variants)}"-->
            <!--                    @click="onVariantSelect(null)">-->
            <!--              {{ weight ?? '' }}-->
            <!--            </button>-->

            <template v-for="v in variants" :key="v.id">
                <button class="btn btn-sm rounded-none normal-case text-md px-2 py-2">
<!--                        @click="onVariantSelect(v)">-->
                  {{ v.weight }}{{ (v?.id ? v.weight_unit : product.weight_unit) ?? '' }}
                </button>
            </template>
          </template>

          <template v-else>
            <div class="breadcrumbs p-0 justify-end">
              <ul>
                <li>
                   <span class="font-bold text-md">
                    {{ product.weight + (product.weight_unit ?? '') }}
                  </span>
                </li>
              </ul>
            </div>
          </template>
        </div>

        <div class="breadcrumbs p-0 justify-end">
          <ul>
            <li>
              <h2 class="card-title grow">
                {{ product.price + ' ₴' }}
              </h2>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
