<script setup lang="ts">
  import {computed, PropType, ref, watchEffect} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ChevronLeft, Plus} from 'lucide-vue-next'
  import {getEditorVersions, storeEditorVersion} from '@/api'
  import AdminNavbar from '@/Components/Admin/AdminNavbar.vue'
  import VersionList from '@/Components/Admin/VersionList.vue'
  import type {VersionsProps} from '@/admin/types'
  import {translated} from '@/editor/translations'

  /**
   * Planned menu changes of the restaurant (see `VersionPageController::index()`): the versions,
   * which haven't gone live, from the earliest, then the ones, which went live, from the latest.
   */
  const props = defineProps({
    props: {type: Object as PropType<VersionsProps>, required: true},
  })

  const {t} = useI18n()
  const key = 'admin.versions.'

  const restaurant = computed(() => props.props.restaurant)
  const name = computed(() => translated(restaurant.value.name, restaurant.value.default_locale))
  const urls = computed(() => props.props.urls)
  // the planned menu changes of another restaurant
  const switchUrl = (id: number) => `${urls.value.versions}?restaurant=${id}`

  const versions = ref(props.props.restaurant.versions)
  const planned = computed(() => versions.value.filter((version) => version.status !== 'applied'))
  const applied = computed(() => versions.value.filter((version) => version.status === 'applied'))

  const timezone = computed(() => restaurant.value.timezone.split('/').pop()?.replace(/_/g, ' ') ?? '')

  const message = ref<string | null>(null)
  let messageTimer: number | undefined

  function notify(text: string) {
    message.value = text
    window.clearTimeout(messageTimer)
    messageTimer = window.setTimeout(() => message.value = null, 5000)
  }

  async function reload() {
    try {
      versions.value = (await getEditorVersions(restaurant.value.id)).data.data
    } catch (error) {
      notify(t('admin.dashboard.versions.error'))
    }
  }

  const adding = ref(false)

  /** A new version, a draft: its page. */
  async function add() {
    adding.value = true

    try {
      const version = (await storeEditorVersion(restaurant.value.id, {})).data.data

      window.location.assign(`${urls.value.version}/${version.id}`)
    } catch (error) {
      notify(t('admin.dashboard.versions.error'))
      adding.value = false
    }
  }

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
      <div class="max-w-[1240px] mx-auto px-8 pt-[22px] pb-7 flex flex-col gap-4 max-md:px-4">
        <div class="flex items-end gap-3">
          <div class="flex-1 min-w-0">
            <a class="e-link -ml-0.5 mb-1" :href="urls.dashboard">
              <ChevronLeft class="size-3.5"/>
              {{ t('admin.nav.dashboard') }}
            </a>
            <h1 class="text-[22px]/[30px] font-bold">{{ t(key + 'title') }}</h1>
            <p class="text-[13px]/[18px] text-zinc-500">{{ t(key + 'help', {timezone}) }}</p>
          </div>

          <button type="button" class="e-btn e-btn-primary" :disabled="adding" @click="add">
            <Plus class="size-4"/>
            {{ t(key + 'add') }}
          </button>
        </div>

        <section class="e-card px-5 pt-[18px] pb-3">
          <h2 class="e-card-title flex items-center min-h-7">
            {{ t(key + 'planned') }}
            <span class="e-pill h-5! ml-1.5 bg-zinc-100 text-zinc-700" v-if="planned.length">{{ planned.length }}</span>
          </h2>

          <VersionList class="mt-2"
                       :versions="planned"
                       :currency="restaurant.currency"
                       :version-url="urls.version"
                       @changed="reload"
                       @notify="notify"
                       v-if="planned.length"/>

          <p class="mt-2 py-3 border-t border-[#f0f0f1] text-[13px]/[18px] text-zinc-500" v-else>
            {{ t('admin.dashboard.versions.empty') }}
          </p>
        </section>

        <section class="e-card px-5 pt-[18px] pb-3" v-if="applied.length">
          <h2 class="e-card-title flex items-center min-h-7">
            {{ t(key + 'applied') }}
            <span class="e-pill h-5! ml-1.5 bg-zinc-100 text-zinc-700">{{ applied.length }}</span>
          </h2>

          <VersionList class="mt-2"
                       :versions="applied"
                       :currency="restaurant.currency"
                       :version-url="urls.version"
                       @changed="reload"
                       @notify="notify"/>
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
