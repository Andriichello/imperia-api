<script setup lang="ts">
  import {computed, onMounted, ref, watch} from "vue";
  import {ArrowDownWideNarrow, ChevronDown, ShieldCheck, SquarePen} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import {getMyRestaurantReviews, getRestaurantReviews, type RestaurantReview} from "@/api";
  import LanguageButton from "@/Components/Base/LanguageButton.vue";
  import PageFooter from "@/Components/Base/PageFooter.vue";
  import RestaurantButton from "@/Components/Base/RestaurantButton.vue";
  import LanguageDrawer from "@/Components/Drawer/LanguageDrawer.vue";
  import PendingReview from "@/Components/Reviews/PendingReview.vue";
  import ReviewDrawer from "@/Components/Reviews/ReviewDrawer.vue";
  import ReviewItem from "@/Components/Reviews/ReviewItem.vue";
  import StarRating from "@/Components/Reviews/StarRating.vue";
  import {switchLanguage} from "@/i18n/utils";
  import {useAppStore} from "@/stores/app";
  import {averageFormatted, deviceToken, keepMyReviews, myReviewIds, restaurantUrl} from "@/reviews";

  /**
   * The restaurant's reviews (`/{locale}/web/{restaurant}/reviews`): their summary, the approved
   * ones a page at a time, and the form to leave one. The guest's own reviews, which wait for
   * approval, are shown to them on their device only. How reviews are checked is said right away.
   */
  type Sort = 'newest' | 'highest' | 'lowest';

  const SORTS: Sort[] = ['newest', 'highest', 'lowest'];

  const i18n = useI18n();
  const app = useAppStore();

  const restaurant = computed(() => app.restaurant!);
  const summary = ref(app.reviews);
  const count = computed(() => summary.value?.count ?? 0);

  // the most reviews of a rating: the bars are relative to it
  const most = computed(() => Math.max(1, ...(summary.value?.ratings ?? []).map((item) => item.count)));

  const breakdown = computed(() => (summary.value?.ratings ?? [])
    .map((item) => i18n.t('reviews.breakdown', {rating: item.rating, total: item.count}, item.rating))
    .join(', '));

  const sort = ref<Sort>('newest');
  const reviews = ref<RestaurantReview[]>([]);
  const page = ref(0);
  const lastPage = ref(1);
  const loading = ref(false);
  const failed = ref(false);

  // the guest's own reviews, which wait for approval
  const mine = ref<RestaurantReview[]>([]);

  const formOpen = ref(false);
  const languageOpen = ref(false);

  async function load(next: number = 1) {
    loading.value = true;
    failed.value = false;

    try {
      const response = (await getRestaurantReviews(String(restaurant.value.id), {sort: sort.value, page: next})).data;

      reviews.value = next === 1 ? response.data : [...reviews.value, ...response.data];
      page.value = response.meta.current_page;
      lastPage.value = response.meta.last_page;
      summary.value = response.summary;
    } catch (error) {
      failed.value = true;
    } finally {
      loading.value = false;
    }
  }

  /** The guest's reviews, which still wait: approved ones are in the list, rejected ones are gone. */
  async function loadMine() {
    const ids = myReviewIds(restaurant.value.id);

    if (!ids.length) {
      return;
    }

    try {
      const response = (await getMyRestaurantReviews(String(restaurant.value.id), {client_token: deviceToken(), ids})).data;

      mine.value = response.data.filter((review) => review.status === 'pending');
      keepMyReviews(restaurant.value.id, mine.value.map((review) => review.id as number));
    } catch (error) {
      // they're shown the next time
    }
  }

  function onPosted(review: RestaurantReview) {
    if (review.id) {
      mine.value = [...mine.value, review];
    }
  }

  function openRestaurant() {
    window.location.assign(restaurantUrl());
  }

  function onSwitchLanguage(locale: string) {
    switchLanguage(i18n, locale);
  }

  watch(sort, () => load(1));

  onMounted(() => {
    document.title = `${i18n.t('reviews.title')} · ${restaurant.value.name}`;
    load(1);
    loadMine();
  });
</script>

<template>
  <div class="w-full min-h-screen flex flex-col items-center bg-base-200/80">
    <div class="w-full max-w-md min-h-screen flex flex-col bg-base-100">
      <div class="w-full p-2 flex items-center justify-between gap-2">
        <div class="min-w-0 flex">
          <RestaurantButton @navigate="openRestaurant"/>
        </div>

        <LanguageButton class="shrink-0" @click="languageOpen = true"/>
      </div>

      <main class="flex flex-col gap-4 pt-2 px-4 pb-6">
        <h1 class="text-[26px]/[34px] font-bold">{{ i18n.t('reviews.title') }}</h1>

        <section class="flex items-center gap-5 p-4 border border-[#eeeeee] rounded-lg bg-zinc-50"
                 :aria-label="i18n.t('reviews.summary')"
                 v-if="count">
          <div class="flex flex-col items-start gap-1">
            <span class="text-5xl/[52px] font-bold tracking-[-0.02em]">{{ averageFormatted(summary!.average ?? 0, i18n.locale.value) }}</span>
            <StarRating :value="summary!.average ?? 0"/>
            <span class="text-[13px]/[18px] text-base-content/65">{{ i18n.t('reviews.count', {count}, count) }}</span>
          </div>

          <div class="flex-1 min-w-0 flex flex-col gap-1.5"
               role="img"
               :aria-label="breakdown">
            <div class="flex items-center gap-2 text-[13px]/[18px] tabular-nums"
                 v-for="item in summary!.ratings" :key="item.rating">
              <span class="w-2.5 text-end font-semibold">{{ item.rating }}</span>
              <span class="flex-1 h-2 rounded bg-[#eeeeee] overflow-hidden" aria-hidden="true">
                <span class="block h-full rounded bg-primary-content"
                      :style="{width: `${item.count / most * 100}%`}"/>
              </span>
              <span class="w-6 text-end text-base-content/65">{{ item.count }}</span>
            </div>
          </div>
        </section>

        <section class="flex flex-col gap-0.5 p-4 border border-[#eeeeee] rounded-lg bg-zinc-50"
                 v-else>
          <p class="text-lg/[26px] font-bold">{{ i18n.t('reviews.none') }}</p>
          <p class="text-[15px]/[22px] text-base-content/65">{{ i18n.t('reviews.empty') }}</p>
        </section>

        <button type="button"
                class="w-full h-13 flex items-center justify-center gap-2 rounded-lg bg-primary-content text-base-100 text-base font-semibold cursor-pointer"
                @click="formOpen = true">
          <SquarePen class="size-[18px]"/>
          {{ i18n.t('reviews.leave') }}
        </button>

        <p class="-mt-1.5 flex items-start gap-2 text-[13px]/[18px] text-base-content/65">
          <ShieldCheck class="size-4 shrink-0 mt-px"/>
          <span>{{ i18n.t('reviews.moderation', {name: restaurant.name}) }}</span>
        </p>

        <PendingReview :review="review"
                       v-for="review in mine" :key="review.id ?? 0"/>

        <div v-if="count || reviews.length">
          <div class="flex items-center justify-between gap-2 pb-1">
            <h2 class="text-lg/[26px] font-bold">{{ i18n.t('reviews.all') }}</h2>

            <label class="relative shrink-0 inline-flex">
              <span class="sr-only">{{ i18n.t('reviews.sort') }}</span>
              <ArrowDownWideNarrow class="absolute left-3 top-3 size-4 pointer-events-none"/>
              <select class="h-10 appearance-none pl-8 pr-8 rounded-lg border border-zinc-300 bg-base-100 text-sm font-semibold cursor-pointer"
                      v-model="sort">
                <option :value="item" v-for="item in SORTS" :key="item">{{ i18n.t('reviews.sorts.' + item) }}</option>
              </select>
              <ChevronDown class="absolute right-2.5 top-3 size-4 pointer-events-none"/>
            </label>
          </div>

          <ReviewItem :review="review"
                      v-for="review in reviews" :key="review.id ?? 0"/>

          <button type="button"
                  class="w-full h-11 mt-2 flex items-center justify-center rounded-lg border border-zinc-300 bg-base-100 font-semibold cursor-pointer disabled:cursor-default disabled:opacity-60"
                  :disabled="loading"
                  v-if="page < lastPage && !failed"
                  @click="load(page + 1)">
            {{ i18n.t('reviews.show_more') }}
          </button>
        </div>

        <div class="w-full flex justify-center py-4"
             v-if="loading && !reviews.length">
          <div class="loading loading-dots loading-lg text-primary/40"/>
        </div>

        <div class="flex flex-col items-center gap-2 py-4 text-center"
             v-if="failed">
          <p class="text-base-content/72">{{ i18n.t('reviews.load_failed') }}</p>
          <button type="button"
                  class="h-11 px-4 rounded border border-zinc-200 bg-base-100 font-semibold text-base-content/72 cursor-pointer"
                  @click="load(page + 1)">
            {{ i18n.t('reviews.try_again') }}
          </button>
        </div>
      </main>

      <PageFooter/>
    </div>

    <ReviewDrawer :open="formOpen"
                  @close="formOpen = false"
                  @posted="onPosted"
                  @open-restaurant="openRestaurant"/>

    <LanguageDrawer :open="languageOpen"
                    :locale="app.locale"
                    :supported_locales="app.supported_locales"
                    @close="languageOpen = false"
                    @switch-language="onSwitchLanguage"
                    @open-restaurant="openRestaurant"/>
  </div>
</template>
