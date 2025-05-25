<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import {PropType, ref, watch} from "vue";
  import { switchLanguage } from "@/i18n/utils";

  const props = defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    supported_locales: {
      type: Array as PropType<string[]>,
      required: true,
    }
  });

  const emits = defineEmits(['close', 'switch-language']);

  const selected = ref<string>(props.locale);

  function close() {
    emits('close')
  }

  watch(() => selected.value, (newVal, oldVal) => {
    if (newVal !== oldVal) {
      emits('switch-language', newVal)
    }
  });
</script>

<template>
  <BaseDrawer :open="open"
              @close="close">
    <div class="w-full h-full flex flex-col gap-2 px-6">
      <h2 class="w-full font-bold text-xl pb-1 cursor-pointer">
        {{ $t('languages.title') }}:
      </h2>

      <template v-for="l in supported_locales" :key="l">
        <label class="text-lg px-3 py-3 border-1 border-base-300 rounded flex justify-between"
               :class="{'font-bold': selected === l}">
          {{ $t('languages.names.' + l) }}
          <input class="radio"
                 type="radio"
                 :value="l"
                 v-model="selected"/>
        </label>
      </template>
    </div>
  </BaseDrawer>
</template>
