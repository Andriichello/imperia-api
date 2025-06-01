<script setup lang="ts">
  import {Product, ProductVariant} from "@/api";
  import {Splide, SplideSlide} from "@splidejs/vue-splide";
  import {ref, computed, PropType} from "vue";
  import {priceFormatted, weightUnitFormatted} from "@/helpers";
  import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";

  const props = defineProps({
    product: {
      type: Object as PropType<Product>,
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

  const variants = computed<Partial<ProductVariant>[]>(() => {
    if (!props.product.variants || !props.product.variants.length) {
      return null;
    }

    const variants = [...props.product.variants];
    const base = {
      id: null,
      type: 'variants',
      product_id: props.product.id,
      price: props.product.price,
      weight: props.product.weight,
      weight_unit: props.product.weight_unit,
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

  const variantWeight = (variant: Partial<ProductVariant>) => {
    return variant.weight + ' '
      + (weightUnitFormatted((variant?.id ? variant.weight_unit : props.product!.weight_unit) ?? ''));
  };

  const variantPrice = (variant: Partial<ProductVariant>) => {
    return priceFormatted(variant.price, props.currency?.toLowerCase() ?? 'uah');
  };

  const selectVariant = (variant: Partial<ProductVariant> | null) => {
    selectedVariant.value = variant;
  };
</script>

<template>
  <div class="w-full flex flex-col rounded shadow-lg bg-base-100"
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
                    drag: !preview && Number(product.media!.length) > 1,
                    arrows: !preview && Number(product.media!.length) > 1,
                    pagination: !preview,
                  }">
          <SplideSlide v-for="(media, index) in (preview ? [product.media[0]] : product.media)" :key="media.id">
            <img class="w-full h-45 object-cover object-center rounded-t border-none"
                 :src="media.url" alt=""
                 :loading="index === 0 ? 'eager' : 'lazy'"/>
          </SplideSlide>
        </Splide>
      </div>
    </template>

    <div class="card-body min-h-[100px] rounded text-start relative">
      <div class="absolute top-0 right-0">
        <div class="badge-md badge badge-warning text-warning-content bg-warning/60 select-none rounded-tl-none rounded-br-none translate-y-[-1px] py-3 px-5 font-semibold"
             :class="{'rounded-tr-md': !product.media?.length, 'rounded-tr-none': product.media?.length}"
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

      <div class="card-actions justify-between items-end pt-2">
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
