<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import AdminNavbar from '@/Components/Admin/AdminNavbar.vue'
  import VersionHeader from '@/Components/Version/VersionHeader.vue'
  import VersionTree from '@/Components/Version/VersionTree.vue'
  import ItemView from '@/Components/Version/ItemView.vue'
  import VersionPreview from '@/Components/Version/VersionPreview.vue'
  import {useVersionStore} from '@/stores/version'

  /**
   * A scheduled version: its items as a tree on the left, the open item's fields (live, and from
   * the version's date, edited in place) on the right. Every change is saved into the version
   * right away.
   */
  const store = useVersionStore()
  const {t} = useI18n()

  const siteUrl = computed(() => store.restaurant?.url ?? '')
</script>

<template>
  <div class="h-full min-h-[600px] flex flex-col overflow-hidden">
    <AdminNavbar :user="store.user!"
                 :restaurants="store.restaurants"
                 :restaurant-id="store.restaurant!.id"
                 :site-url="siteUrl"
                 :urls="store.urls!"/>

    <VersionHeader/>

    <div class="flex-1 min-h-0 flex">
      <VersionTree/>

      <main class="flex-1 min-w-0 overflow-y-auto bg-white">
        <ItemView v-if="store.current"/>
      </main>
    </div>

    <VersionPreview v-if="store.previewOpen"/>

    <div class="fixed bottom-5 left-1/2 -translate-x-1/2 z-40 flex items-center gap-3 pl-4 pr-2 py-2 rounded-lg bg-zinc-900 text-white text-[13px] shadow-lg"
         role="status"
         v-if="store.toast">
      {{ store.toast }}
      <button type="button" class="px-2 py-1 text-zinc-300 hover:text-white rounded e-focus" @click="store.toast = null">
        {{ t('editor.toast.dismiss') }}
      </button>
    </div>
  </div>
</template>
