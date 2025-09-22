<script setup lang="ts">
import {onMounted} from 'vue'
import {useAppStore} from "@/stores/app";

console.log('WebPreviewPage');

const app = useAppStore();

onMounted(() => {
  console.log('app store', app.$state);
})
</script>

<template>
  <div>
    Something here to see...
    <p>
      Prop: {{ app.locale }}
    </p>
    <p>
      Prop: {{ app.supported_locales }}
    </p>
    <p>
      Prop: {{ app.auth?.user }}
    </p>
    <p>
      Prop: {{ app.flash?.message ?? 'null' }}
    </p>
    <p>
      Prop: {{ app.restaurant }}
    </p>
  </div>
</template>
