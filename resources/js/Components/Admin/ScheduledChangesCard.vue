<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Plus} from 'lucide-vue-next'
  import {storeEditorVersion, type EditorDashboard} from '@/api'
  import VersionList from '@/Components/Admin/VersionList.vue'

  /**
   * Versions, which haven't gone live yet, from the earliest (see `VersionList`), and a link to
   * all of them, those which went live included.
   */
  const props = defineProps({
    restaurant: {type: Object as PropType<EditorDashboard>, required: true},
    // planned menu changes: all versions of the restaurant
    versionsUrl: {type: String, required: true},
    // of a version: this one and `/{id}`
    versionUrl: {type: String, required: true},
  })

  const emit = defineEmits<{
    // the versions changed: the dashboard is loaded again
    changed: []
    notify: [message: string]
  }>()

  const {t} = useI18n()
  const key = 'admin.dashboard.versions.'

  const timezone = computed(() => props.restaurant.timezone.split('/').pop()?.replace(/_/g, ' ') ?? '')

  const adding = ref(false)

  /** A new version, a draft: its page. */
  async function add() {
    adding.value = true

    try {
      const version = (await storeEditorVersion(props.restaurant.id, {})).data.data

      window.location.assign(`${props.versionUrl}/${version.id}`)
    } catch (error) {
      emit('notify', t(key + 'error'))
      adding.value = false
    }
  }
</script>

<template>
  <section class="e-card col-span-full px-5 pt-[18px] pb-3">
    <div class="flex items-center gap-2 min-h-7">
      <h2 class="e-card-title flex items-center">
        {{ t(key + 'title') }}
        <span class="e-pill h-5! ml-1.5 bg-zinc-100 text-zinc-700" v-if="restaurant.versions.length">{{ restaurant.versions.length }}</span>
      </h2>
      <div class="flex-1"/>
      <a class="mr-3 e-link" :href="versionsUrl">{{ t(key + 'see_all') }}</a>
      <button type="button" class="e-btn e-btn-secondary h-8 px-2.5" :disabled="adding" @click="add">
        <Plus class="size-4"/>
        {{ t(key + 'add') }}
      </button>
    </div>

    <VersionList class="mt-2"
                 :versions="restaurant.versions"
                 :currency="restaurant.currency"
                 :version-url="versionUrl"
                 @changed="emit('changed')"
                 @notify="(message) => emit('notify', message)"
                 v-if="restaurant.versions.length"/>

    <p class="mt-2 py-3 border-t border-[#f0f0f1] text-[13px]/[18px] text-zinc-500" v-else>
      {{ t(key + 'empty') }}
    </p>

    <p class="pt-2.5 pb-0.5 border-t border-[#f0f0f1] text-xs text-zinc-500" v-if="restaurant.versions.length">
      {{ t(key + 'footer', {timezone}) }}
    </p>
  </section>
</template>
