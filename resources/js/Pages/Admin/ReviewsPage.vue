<script setup lang="ts">
  import {computed, onMounted, PropType, ref, watchEffect} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {Check, ChevronLeft, ExternalLink, X} from 'lucide-vue-next'
  import {
    approveEditorReview,
    getEditorReviews,
    rejectEditorReview,
    type EditorReview,
    type EditorReviewsResponseCounts,
  } from '@/api'
  import AdminNavbar from '@/Components/Admin/AdminNavbar.vue'
  import StarRating from '@/Components/Reviews/StarRating.vue'
  import {formatDateTime} from '@/admin/format'
  import type {DashboardProps} from '@/admin/types'
  import {translated} from '@/editor/translations'

  /**
   * Reviews guests left of the restaurant (see `ReviewPageController`): the ones, which wait for
   * approval, first. Approving one makes it public; rejecting one keeps it off the page. Either
   * can be decided again later.
   */
  type Status = 'pending' | 'approved' | 'rejected'

  const STATUSES: Status[] = ['pending', 'approved', 'rejected']

  const props = defineProps({
    props: {type: Object as PropType<DashboardProps>, required: true},
  })

  const {t, locale} = useI18n()
  const key = 'admin.reviews.'

  const restaurant = computed(() => props.props.restaurant)
  const urls = computed(() => props.props.urls)
  const name = computed(() => translated(restaurant.value.name, restaurant.value.default_locale))
  // the reviews of another restaurant
  const switchUrl = (id: number) => `${urls.value.reviews}?restaurant=${id}`

  const status = ref<Status>('pending')
  const reviews = ref<EditorReview[]>([])
  const counts = ref<EditorReviewsResponseCounts>({pending: restaurant.value.pending_reviews, approved: 0, rejected: 0})
  const page = ref(0)
  const lastPage = ref(1)
  const loading = ref(false)
  // reviews, which are being approved or rejected
  const busy = ref<number[]>([])

  const message = ref<string | null>(null)
  let messageTimer: number | undefined

  function notify(text: string) {
    message.value = text
    window.clearTimeout(messageTimer)
    messageTimer = window.setTimeout(() => message.value = null, 5000)
  }

  async function load(next: number = 1) {
    loading.value = true

    try {
      const response = (await getEditorReviews(restaurant.value.id, {status: status.value, page: next})).data

      reviews.value = next === 1 ? response.data : [...reviews.value, ...response.data]
      page.value = response.meta.current_page
      lastPage.value = response.meta.last_page
      counts.value = response.counts
    } catch (error) {
      notify(t(key + 'error'))
    } finally {
      loading.value = false
    }
  }

  function show(next: Status) {
    status.value = next
    reviews.value = []
    load(1)
  }

  /** Approve or reject the review: it leaves the list it's in. */
  async function moderate(review: EditorReview, decision: 'approved' | 'rejected') {
    busy.value = [...busy.value, review.id]

    try {
      await (decision === 'approved' ? approveEditorReview : rejectEditorReview)(review.id)

      reviews.value = reviews.value.filter((other) => other.id !== review.id)
      counts.value = {...counts.value, [review.status]: counts.value[review.status] - 1, [decision]: counts.value[decision] + 1}
      notify(t(key + (decision === 'approved' ? 'approved_message' : 'rejected_message')))
    } catch (error) {
      notify(t(key + 'error'))
    } finally {
      busy.value = busy.value.filter((id) => id !== review.id)
    }
  }

  const date = (iso: string | null) => iso ? formatDateTime(DateTime.fromISO(iso, {setZone: true}), locale.value) : ''

  /** "Approved by Anna on Sat 3 Oct, 14:32" */
  function decided(review: EditorReview): string {
    if (!review.moderated_at) {
      return ''
    }

    return t(key + 'decided.' + review.status, {name: review.moderated_by?.name ?? '—', date: date(review.moderated_at)})
  }

  onMounted(() => load(1))

  watchEffect(() => {
    document.title = `${t(key + 'title')} · ${name.value}`
  })
</script>

<template>
  <div class="h-full flex flex-col">
    <AdminNavbar :user="props.props.user"
                 :restaurants="props.props.restaurants"
                 :restaurant-id="restaurant.id"
                 :site-url="restaurant.url"
                 :urls="urls"
                 :switch-url="switchUrl"/>

    <main class="flex-1 min-h-0 overflow-y-auto">
      <div class="max-w-[960px] mx-auto px-8 pt-[22px] pb-7 flex flex-col gap-4 max-md:px-4">
        <div class="flex items-end gap-3">
          <div class="flex-1 min-w-0">
            <a class="e-link -ml-0.5 mb-1" :href="urls.dashboard">
              <ChevronLeft class="size-3.5"/>
              {{ t('admin.nav.dashboard') }}
            </a>
            <h1 class="text-[22px]/[30px] font-bold">{{ t(key + 'title') }}</h1>
            <p class="max-w-[620px] text-[13px]/[18px] text-zinc-500">{{ t(key + 'help') }}</p>
          </div>

          <a class="e-btn e-btn-secondary shrink-0"
             target="_blank"
             rel="noopener"
             :href="`${restaurant.url}/reviews`">
            <ExternalLink class="size-4"/>
            {{ t(key + 'public_page') }}
          </a>
        </div>

        <div class="e-seg self-start"
             role="group"
             :aria-label="t(key + 'statuses')">
          <button type="button"
                  class="e-focus"
                  :aria-pressed="status === item"
                  v-for="item in STATUSES" :key="item"
                  @click="show(item)">
            {{ t(key + 'tabs.' + item) }}
            <span class="tabular-nums text-zinc-500">{{ counts[item] }}</span>
          </button>
        </div>

        <section class="e-card px-5 py-1" :aria-busy="loading">
          <article class="flex gap-4 py-4 border-t border-[#f0f0f1] first:border-t-0 max-sm:flex-col"
                   v-for="review in reviews" :key="review.id">
            <div class="flex-1 min-w-0 flex flex-col gap-1.5">
              <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <StarRating filled-class="text-amber-500" :value="review.rating"/>
                <b class="font-semibold">{{ review.name || t('reviews.guest') }}</b>
                <span class="text-[13px] text-zinc-500">{{ date(review.created_at) }}</span>
                <span class="e-pill h-5! bg-zinc-100 text-zinc-600 uppercase" v-if="review.locale">{{ review.locale }}</span>
              </div>

              <p class="text-sm/5 text-zinc-800 whitespace-pre-line break-words" v-if="review.text">{{ review.text }}</p>
              <p class="text-sm/5 text-zinc-500 italic" v-else>{{ t(key + 'no_text') }}</p>

              <p class="text-xs text-zinc-500" v-if="review.moderated_at">{{ decided(review) }}</p>
            </div>

            <div class="shrink-0 flex items-start gap-2">
              <button type="button"
                      class="e-btn e-btn-primary"
                      :disabled="busy.includes(review.id)"
                      v-if="review.status !== 'approved'"
                      @click="moderate(review, 'approved')">
                <Check class="size-4"/>
                {{ t(key + 'approve') }}
              </button>

              <button type="button"
                      class="e-btn e-btn-secondary"
                      :disabled="busy.includes(review.id)"
                      v-if="review.status !== 'rejected'"
                      @click="moderate(review, 'rejected')">
                <X class="size-4"/>
                {{ t(key + 'reject') }}
              </button>
            </div>
          </article>

          <p class="py-6 text-center text-sm text-zinc-500" v-if="!loading && !reviews.length">
            {{ t(key + 'empty.' + status) }}
          </p>

          <div class="py-3 border-t border-[#f0f0f1]" v-if="page < lastPage">
            <button type="button"
                    class="w-full e-btn e-btn-secondary"
                    :disabled="loading"
                    @click="load(page + 1)">
              {{ t(key + 'show_more') }}
            </button>
          </div>
        </section>
      </div>
    </main>

    <div class="fixed bottom-5 left-1/2 -translate-x-1/2 max-w-[480px] px-4 py-2.5 rounded-lg bg-zinc-900 text-white text-sm shadow-lg"
         role="status"
         aria-live="polite"
         v-if="message">
      {{ message }}
    </div>
  </div>
</template>
