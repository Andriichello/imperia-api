<script setup lang="ts">
  import {computed, nextTick, ref} from "vue";
  import {CircleCheck, Star} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import {storeRestaurantReview, type RestaurantReview} from "@/api";
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import PendingReview from "@/Components/Reviews/PendingReview.vue";
  import {useAppStore} from "@/stores/app";
  import {deviceToken, keepMyReviews, myReviewIds} from "@/reviews";

  /**
   * Leave a review of the restaurant: a rating (required), a name and a text, if the guest likes.
   * It waits for the restaurant's approval; until then, only the guest sees it on their device.
   */
  defineProps({
    open: {
      type: Boolean,
      required: true,
    },
  });

  const emits = defineEmits<{
    close: []
    // the review was posted: it waits for approval
    posted: [review: RestaurantReview]
    'open-restaurant': []
  }>();

  const i18n = useI18n();
  const app = useAppStore();

  const MAX_NAME = 40;
  const MAX_TEXT = 1000;

  const restaurantName = computed(() => app.restaurant?.name ?? '');

  const rating = ref(0);
  const name = ref('');
  const text = ref('');
  // a field guests don't see: bots fill it in
  const website = ref('');
  const posting = ref(false);
  const error = ref<string | null>(null);
  // the review, once it's posted
  const sent = ref<RestaurantReview | null>(null);

  const stars = ref<HTMLButtonElement[]>([]);

  const ratingLabel = computed(() => rating.value
    ? i18n.t(`reviews.form.ratings.${rating.value}`)
    : i18n.t('reviews.form.tap'));

  function reset() {
    rating.value = 0;
    name.value = '';
    text.value = '';
    website.value = '';
    error.value = null;
    sent.value = null;
  }

  function close() {
    emits('close');
  }

  /** Arrows pick the next or the previous rating, like radio buttons. */
  function onRatingKeydown(event: KeyboardEvent) {
    const step = {ArrowRight: 1, ArrowUp: 1, ArrowLeft: -1, ArrowDown: -1}[event.key];

    if (!step) {
      return;
    }

    event.preventDefault();
    rating.value = Math.min(5, Math.max(1, (rating.value || (step > 0 ? 0 : 6)) + step));
    nextTick(() => stars.value[rating.value - 1]?.focus());
  }

  async function post() {
    if (!rating.value || posting.value || !app.restaurant) {
      return;
    }

    posting.value = true;
    error.value = null;

    try {
      const review = (await storeRestaurantReview(String(app.restaurant.id), {
        rating: rating.value,
        name: name.value.trim() || null,
        text: text.value.trim() || null,
        locale: i18n.locale.value,
        client_token: deviceToken(),
        website: website.value || null,
      })).data.data;

      if (review.id) {
        keepMyReviews(app.restaurant.id, [...myReviewIds(app.restaurant.id), review.id]);
      }

      sent.value = review;
      emits('posted', review);
    } catch (exception) {
      const status = (exception as { response?: { status?: number } }).response?.status;

      error.value = i18n.t(status === 429 ? 'reviews.form.too_many' : 'reviews.form.failed');
    } finally {
      posting.value = false;
    }
  }

  /** Back to the reviews page: the next review starts empty. */
  function backToReviews() {
    close();
    reset();
  }
</script>

<template>
  <BaseDrawer :open="open"
              @close="sent ? backToReviews() : close()"
              @restaurant="emits('open-restaurant')">
    <form class="h-full flex flex-col"
          novalidate
          v-if="!sent"
          @submit.prevent="post">
      <div class="flex-1 min-h-0 overflow-auto flex flex-col gap-5 pt-1 px-5 pb-5">
        <div>
          <h2 class="text-[22px]/[30px] font-bold">{{ i18n.t('reviews.form.title') }}</h2>
          <p class="text-[15px]/[22px] text-base-content/65">{{ i18n.t('reviews.form.subtitle', {name: restaurantName}) }}</p>
        </div>

        <fieldset class="flex flex-col gap-1">
          <legend class="text-sm/5 font-semibold text-base-content/72">{{ i18n.t('reviews.form.rating') }}</legend>

          <div class="flex items-center -ml-1.5"
               role="radiogroup"
               :aria-label="i18n.t('reviews.form.rating')"
               @keydown="onRatingKeydown">
            <button type="button"
                    class="size-12 flex items-center justify-center rounded cursor-pointer"
                    role="radio"
                    :aria-checked="rating === n"
                    :aria-label="i18n.t('reviews.form.star', {rating: n}, n)"
                    :tabindex="rating === n || (!rating && n === 1) ? 0 : -1"
                    ref="stars"
                    v-for="n in 5" :key="n"
                    @click="rating = n">
              <Star class="size-9 stroke-[1.5]"
                    :class="n <= rating ? 'fill-primary-content text-primary-content' : 'fill-none text-zinc-400'"/>
            </button>
          </div>

          <p class="text-base/6 font-semibold"
             :class="rating ? 'text-base-content' : 'text-base-content/65'"
             aria-live="polite">
            {{ ratingLabel }}
          </p>
        </fieldset>

        <label class="flex flex-col gap-1.5">
          <span class="text-sm/5 font-semibold text-base-content/72">
            {{ i18n.t('reviews.form.name') }} <span class="font-normal">{{ i18n.t('reviews.form.optional') }}</span>
          </span>
          <input class="w-full py-[11px] px-3 rounded-lg border border-zinc-300 bg-base-100 text-base/6 outline-none placeholder:text-base-content/50 focus:border-primary-content focus:ring-3 focus:ring-primary/20"
                 type="text"
                 autocomplete="given-name"
                 :maxlength="MAX_NAME"
                 :placeholder="i18n.t('reviews.guest')"
                 v-model="name"/>
          <span class="text-[13px]/[18px] text-base-content/65">{{ i18n.t('reviews.form.name_help') }}</span>
        </label>

        <label class="flex flex-col gap-1.5">
          <span class="flex justify-between gap-2 text-sm/5 font-semibold text-base-content/72">
            <span>{{ i18n.t('reviews.form.text') }} <span class="font-normal">{{ i18n.t('reviews.form.optional') }}</span></span>
            <span class="font-normal tabular-nums">{{ text.length }} / {{ MAX_TEXT }}</span>
          </span>
          <textarea class="w-full py-[11px] px-3 rounded-lg border border-zinc-300 bg-base-100 text-base/6 outline-none resize-none placeholder:text-base-content/50 focus:border-primary-content focus:ring-3 focus:ring-primary/20"
                    rows="5"
                    :maxlength="MAX_TEXT"
                    :placeholder="i18n.t('reviews.form.text_placeholder')"
                    v-model="text"/>
        </label>

        <!-- guests don't see it -->
        <input class="absolute size-px -left-[9999px] opacity-0"
               type="text"
               name="website"
               tabindex="-1"
               autocomplete="off"
               aria-hidden="true"
               v-model="website"/>
      </div>

      <div class="shrink-0 flex flex-col gap-2 pt-3 px-4 pb-5 border-t border-primary/40">
        <p class="text-sm/5 text-center text-red-700"
           role="alert"
           v-if="error">
          {{ error }}
        </p>

        <button type="submit"
                class="w-full h-13 flex items-center justify-center rounded-lg text-base font-semibold"
                :class="rating ? 'bg-primary-content text-base-100 cursor-pointer' : 'bg-zinc-200 text-zinc-600'"
                :aria-disabled="!rating || posting">
          {{ rating ? i18n.t(posting ? 'reviews.form.posting' : 'reviews.form.post') : i18n.t('reviews.form.pick_rating') }}
        </button>

        <p class="text-[13px]/[18px] text-center text-base-content/65">
          {{ i18n.t('reviews.form.note', {name: restaurantName}) }}
        </p>
      </div>
    </form>

    <!-- posted: it waits for approval -->
    <div class="h-full overflow-auto flex flex-col items-center gap-4 pt-12 px-6 pb-8 text-center"
         role="status"
         v-else>
      <span class="size-18 flex items-center justify-center rounded-full bg-primary/15 text-primary-content">
        <CircleCheck class="size-9"/>
      </span>

      <h2 class="text-[26px]/[34px] font-bold">{{ i18n.t('reviews.form.sent_title') }}</h2>
      <p class="text-base/6 text-base-content/65">{{ i18n.t('reviews.form.sent_text', {name: restaurantName}) }}</p>

      <PendingReview class="w-full mt-2" :review="sent" posted/>

      <button type="button"
              class="w-full h-13 mt-2 flex items-center justify-center rounded-lg bg-primary-content text-base-100 text-base font-semibold cursor-pointer"
              @click="backToReviews">
        {{ i18n.t('reviews.form.back') }}
      </button>
    </div>
  </BaseDrawer>
</template>
