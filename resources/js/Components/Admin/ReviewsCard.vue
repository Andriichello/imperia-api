<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ChevronRight} from 'lucide-vue-next'
  import type {EditorDashboard} from '@/api'
  import StarRating from '@/Components/Reviews/StarRating.vue'
  import {averageFormatted} from '@/reviews'

  /**
   * Reviews guests left: the approved ones (their average and count), and how many wait for
   * approval, with a link to approve or reject them.
   */
  const props = defineProps({
    restaurant: {type: Object as PropType<EditorDashboard>, required: true},
    reviewsUrl: {type: String, required: true},
  })

  const {t, locale} = useI18n()
  const key = 'admin.dashboard.reviews.'

  const summary = computed(() => props.restaurant.reviews)
  const pending = computed(() => props.restaurant.pending_reviews)
</script>

<template>
  <section class="e-card col-span-full px-5 pt-[18px] pb-4">
    <div class="flex items-center gap-2 min-h-7">
      <h2 class="e-card-title flex items-center">
        {{ t(key + 'title') }}
        <span class="e-pill h-5! ml-1.5 bg-amber-100 text-amber-900" v-if="pending">
          {{ t(key + 'waiting', {count: pending}, pending) }}
        </span>
      </h2>
      <div class="flex-1"/>
      <a class="e-link" :href="reviewsUrl">
        {{ t(key + (pending ? 'check' : 'see_all')) }}
        <ChevronRight class="size-3.5"/>
      </a>
    </div>

    <p class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-zinc-600">
      <template v-if="summary.count">
        <StarRating filled-class="text-amber-500" :value="summary.average ?? 0"/>
        <span>
          <b class="font-semibold text-zinc-900">{{ averageFormatted(summary.average ?? 0, locale) }}</b>
          · {{ t(key + 'count', {count: summary.count}, summary.count) }}
        </span>
      </template>
      <span v-else>{{ t(key + 'none') }}</span>
    </p>
  </section>
</template>
