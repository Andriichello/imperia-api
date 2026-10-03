<script setup lang="ts">
  import {computed, PropType, ref, watchEffect} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {DateTime} from 'luxon'
  import {getEditorDashboard} from '@/api'
  import AdminNavbar from '@/Components/Admin/AdminNavbar.vue'
  import MenuPageCard from '@/Components/Admin/MenuPageCard.vue'
  import RightNowCard from '@/Components/Admin/RightNowCard.vue'
  import ReviewsCard from '@/Components/Admin/ReviewsCard.vue'
  import ScheduledChangesCard from '@/Components/Admin/ScheduledChangesCard.vue'
  import SpecialDaysCard from '@/Components/Admin/SpecialDaysCard.vue'
  import {formatLongDate} from '@/admin/format'
  import type {DashboardProps} from '@/admin/types'
  import {translated} from '@/editor/translations'

  /**
   * The admin's home (see `DashboardController`): the restaurant's page, its schedule (whether it's
   * open now), upcoming changes in its working schedule, planned changes of its menu and its reviews.
   */
  const props = defineProps({
    props: {type: Object as PropType<DashboardProps>, required: true},
  })

  const {t, locale} = useI18n()

  const restaurant = ref(props.props.restaurant)
  const name = computed(() => translated(restaurant.value.name, restaurant.value.default_locale))

  // the editor opened on its working hours
  const hoursUrl = computed(() => `${props.props.urls.editor}?select=hours`)

  const greeting = computed(() => {
    const hour = DateTime.now().hour
    const part = hour < 12 ? 'morning' : (hour < 18 ? 'afternoon' : 'evening')
    const firstName = props.props.user.name.split(/\s+/)[0]

    return t('admin.dashboard.greeting.' + part, {name: firstName})
  })

  const today = computed(() => formatLongDate(DateTime.now(), locale.value))

  const message = ref<string | null>(null)
  let messageTimer: number | undefined

  function notify(text: string) {
    message.value = text
    window.clearTimeout(messageTimer)
    messageTimer = window.setTimeout(() => message.value = null, 5000)
  }

  async function reload() {
    try {
      restaurant.value = (await getEditorDashboard(restaurant.value.id)).data.data
    } catch (error) {
      notify(t('admin.dashboard.versions.error'))
    }
  }

  watchEffect(() => {
    document.title = `${name.value} · ${t('admin.nav.dashboard')}`
  })
</script>

<template>
  <div class="h-full flex flex-col">
    <AdminNavbar :user="props.props.user"
                 :restaurants="props.props.restaurants"
                 :restaurant-id="restaurant.id"
                 :site-url="restaurant.url"
                 :urls="props.props.urls"/>

    <main class="flex-1 min-h-0 overflow-y-auto">
      <div class="max-w-[1240px] mx-auto px-8 pt-[22px] pb-7 flex flex-col gap-4 max-md:px-4">
        <div>
          <h1 class="text-[22px]/[30px] font-bold">{{ greeting }}</h1>
          <p class="text-[13px]/[18px] text-zinc-500 first-letter:uppercase">{{ today }}</p>
        </div>

        <!-- the menu page, then the schedule and its changes side by side, then planned menu changes -->
        <div class="grid grid-cols-2 gap-4 items-stretch max-lg:grid-cols-1">
          <MenuPageCard :restaurant="restaurant" :editor-url="props.props.urls.editor"/>
          <RightNowCard :restaurant="restaurant" :hours-url="hoursUrl"/>
          <SpecialDaysCard :restaurant="restaurant" :hours-url="hoursUrl"/>
          <ScheduledChangesCard :restaurant="restaurant"
                                :versions-url="props.props.urls.versions"
                                :version-url="props.props.urls.version"
                                @changed="reload"
                                @notify="notify"/>
          <ReviewsCard :restaurant="restaurant" :reviews-url="props.props.urls.reviews"/>
        </div>
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
