<script setup lang="ts">
import {computed, defineProps, PropType} from 'vue'

/**
 * Lightweight replacement for Inertia's <Deferred>.
 *
 * Usage:
 *   <Deferred :data="products">
 *     <template #default>
 *       <!-- content when data is available -->
 *     </template>
 *     <template #fallback>
 *       <!-- skeletons while loading -->
 *     </template>
 *   </Deferred>
 *
 * Pass any "falsy while loading" value (null/undefined) into `data`.
 * When `data` becomes truthy (Array length > 0 also counts), the default slot renders.
 */
const props = defineProps({
  data: { type: [Array, Object, String, Number, Boolean] as PropType<any>, default: null },
})

const isReady = computed<boolean>(() => {
  const d: any = props.data
  if (Array.isArray(d)) {
    // Consider ready when array is loaded (even if empty array)
    return d !== null && d !== undefined
  }
  return !!d
})
</script>

<template>
  <template v-if="isReady">
    <slot />
  </template>
  <template v-else>
    <slot name="fallback" />
  </template>
</template>
