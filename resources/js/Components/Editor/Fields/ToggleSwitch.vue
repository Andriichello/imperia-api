<script setup lang="ts">
  /**
   * An on / off switch.
   */
  defineProps({
    modelValue: {
      type: Boolean,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
  })

  const emits = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()
</script>

<template>
  <button type="button"
          class="e-switch"
          role="switch"
          :aria-checked="modelValue"
          :aria-label="label"
          @click="emits('update:modelValue', !modelValue)"/>
</template>
