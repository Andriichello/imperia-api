<script setup lang="ts">
  import {useI18n} from 'vue-i18n'
  import type {Media} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import PhotoGallery from '@/Components/Editor/PhotoGallery.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Photos of the restaurant page's slideshow: the first one guests see is the cover. Uploads
   * start right away (and go on when the panel is left), but the photos join the gallery only
   * when it's saved.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  /** The most photos (the server's limit). */
  const MAX_PHOTOS = 20

  const {draft, change, error} = usePanelDraft<Media[]>({section: 'photos', id: null})
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.photos')"
              :subtitle="t('editor.subtitles.photos')"
              @navigate="editor.close()"
              @close="editor.close()">
    <PhotoGallery v-model="draft"
                  :max="MAX_PHOTOS"
                  :uploaded="(photo) => change((photos) => [...photos, photo])"
                  :error="error('media')"
                  :help="t('editor.photos.formats')"/>

    <InfoBox>
      <i18n-t keypath="editor.photos.info" scope="global">
        <template #hide><b class="font-semibold">{{ t('editor.photos.info_hide') }}</b></template>
      </i18n-t>
    </InfoBox>
  </PanelShell>
</template>
