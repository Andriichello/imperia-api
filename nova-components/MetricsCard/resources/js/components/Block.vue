<template>
  <div class="flex flex-col p-5">
    <h1 class="text-lg text-gray-500 font-normal">
      {{ title ?? '' }}
    </h1>

    <div class="w-full flex justify-start items-start">
      <slot/>
    </div>

    <div class="w-full bg-gray-300 mr-2" style="height: 1px"></div>

    <p class="text-xs text-gray-400 font-normal pr-4" v-if="description?.length > 0">
      {{ description }}
    </p>
  </div>
</template>

<script>
export default {
  name: 'Block',
  props: {
    title: {
      type: String,
    },
    description: {
      type: String,
      default: 'This is a value block\'s description.',
    }
  },
  data() {
    return {
      metrics: null,
    }
  },
}
</script>
