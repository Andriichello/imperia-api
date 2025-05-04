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

  const selectedVariant = ref<Partial<ProductVariant> | null>(
    variants.value?.find((_) => true)
  );

  const weight = computed(() => {
    let weight = props.product!.weight;
    let unit = props.product!.weight_unit;

    if (selectedVariant.value) {
      weight = selectedVariant.value?.weight;
      unit = selectedVariant.value?.weight_unit;
    }

    return weight + (unit ?? '');
  });

  const price = computed(() => {
    let price = props.product!.price;

    if (selectedVariant.value) {
      price = selectedVariant.value?.price;
    }

    return price + ' ₴';
  });

  const variantWeight = (variant: Partial<ProductVariant>) => {
    return variant.weight + ((variant?.id ? variant.weight_unit : props.product!.weight_unit) ?? '');
  };

  const selectVariant = (variant: Partial<ProductVariant> | null) => {
    selectedVariant.value = variant;
  };
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
        <div class="grow flex flex-col justify-center items-start card-title gap-0">
          <h2 class="grow line-clamp-2 text-ellipsis flex justify-start items-center text-xl">
            {{ product.title }}
          </h2>

          <div class="self-end badge-sm badge badge-warning bg-warning/80 select-none"
               v-if="product!.badge?.length">
            {{ product.badge }}
          </div>
        </div>
      </div>

      <div class="flex-grow">
        <p class="opacity-80 text-[16px]">
          {{ product.description }}
        </p>
      </div>

      <div class="card-actions justify-between items-end pt-2">
        <div class="flex gap-1">
          <template v-if="variants?.length">
            <template v-for="v in variants" :key="v.id">
              {{ void(isSelected = selectedVariant?.id === v.id) }}

              <button class="btn btn-sm rounded-none normal-case text-[14px] px-2 py-2"
                      :class="{'btn-neutral': isSelected, 'btn-outline': !isSelected}"
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
