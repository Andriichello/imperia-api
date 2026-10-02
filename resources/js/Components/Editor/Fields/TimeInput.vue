<script setup lang="ts">
  import {ref, watch} from 'vue'

  /**
   * A time of the day in 24 hours ("10:00"). It takes "10", "1000" or "10.00" too, and the
   * last valid time stays, when the text isn't one.
   */
  const props = defineProps({
    hour: {
      type: Number,
      required: true,
    },
    minute: {
      type: Number,
      required: true,
    },
    label: {
      type: String,
      required: true,
    },
    invalid: {
      type: Boolean,
      default: false,
    },
  })

  const emits = defineEmits<{
    (e: 'update', hour: number, minute: number): void
  }>()

  const format = (hour: number, minute: number) =>
    `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`

  const text = ref(format(props.hour, props.minute))
  const focused = ref(false)

  // changed outside of it (e.g. hours copied from another day)
  watch(() => [props.hour, props.minute], () => {
    if (!focused.value) {
      text.value = format(props.hour, props.minute)
    }
  })

  function parse(value: string): { hour: number, minute: number } | null {
    const match = value.trim().match(/^(\d{1,2})[:.]?(\d{2})?$/)

    if (!match) {
      return null
    }

    const hour = parseInt(match[1])
    const minute = match[2] ? parseInt(match[2]) : 0

    return hour <= 23 && minute <= 59 ? {hour, minute} : null
  }

  function onInput() {
    const time = parse(text.value)

    if (time) {
      emits('update', time.hour, time.minute)
    }
  }

  function onBlur() {
    focused.value = false
    text.value = format(props.hour, props.minute)
  }
</script>

<template>
  <input class="e-input w-[76px] h-9 px-2.5 text-center tabular-nums"
         type="text"
         inputmode="numeric"
         maxlength="5"
         :aria-label="label"
         :aria-invalid="invalid"
         v-model="text"
         @focus="focused = true"
         @input="onInput"
         @blur="onBlur"/>
</template>
