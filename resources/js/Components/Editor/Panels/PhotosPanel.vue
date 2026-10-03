<script setup lang="ts">
  import {computed, onBeforeUnmount, reactive, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import axios from 'axios'
  import {Trash2, Upload, X} from 'lucide-vue-next'
  import {VueDraggable} from 'vue-draggable-plus'
  import type {Media} from '@/api'
  import {updateEditorRestaurantPhotos, uploadEditorRestaurantPhoto} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import GripHandle from '@/Components/Editor/Fields/GripHandle.vue'
  import {moveItem} from '@/editor/lists'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {photosPreview} from '@/editor/drafts'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Photos of the restaurant page's slideshow: the first one is the cover. Uploads start
   * right away, but the photos join the gallery only when it's saved.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const TYPES = ['image/jpeg', 'image/png', 'image/webp']
  const MAX_SIZE = 10 * 1024 * 1024
  const MAX_PHOTOS = 20

  const restaurant = computed(() => editor.restaurant!)

  interface PhotoUpload {
    key: number
    name: string
    // percent
    progress: number
    // the file, shown while it's uploaded
    url: string
    controller: AbortController
  }

  // photos being uploaded, they join the draft once they are (before the draft: it waits for them)
  const uploads = ref<PhotoUpload[]>([])

  const {draft, dirty, saving, failed, discard, save, error} = usePanelDraft<Media[]>({
    saved: () => restaurant.value.photos ?? [],
    save: async (photos) => (await updateEditorRestaurantPhotos(restaurant.value.id, {
      media: photos.map((photo) => ({id: photo.id, is_hidden: photo.is_hidden ?? false})),
    })).data.data,
    preview: (photos) => photosPreview(photos),
    // photos being uploaded are lost, when the panel is left
    pending: () => uploads.value.length > 0,
  })

  // files, which weren't uploaded, and why
  const rejected = ref<string[]>([])
  const dragging = ref(false)
  const fileInput = ref<HTMLInputElement | null>(null)
  let uploaded = 0

  /** Its title, or "Photo 2" for ones without one (older uploads have a hash as their title). */
  function photoName(photo: Media, index: number): string {
    const title = photo.title ?? ''

    return title && !/^[0-9a-f]{32}$/.test(title) ? title : t('editor.photos.untitled', {number: index + 1})
  }

  /** The WebP version, when there's one. */
  function thumbnail(photo: Media): string {
    return photo.variants?.find((variant) => variant.extension === 'webp')?.url ?? photo.url
  }

  function addFiles(files: FileList | null) {
    rejected.value = []

    for (const file of Array.from(files ?? [])) {
      if (!TYPES.includes(file.type)) {
        rejected.value.push(t('editor.photos.wrong_type', {name: file.name}))
      } else if (file.size > MAX_SIZE) {
        rejected.value.push(t('editor.photos.too_big', {name: file.name}))
      } else if (draft.value.length + uploads.value.length >= MAX_PHOTOS) {
        rejected.value.push(t('editor.photos.too_many', {count: MAX_PHOTOS}))
        break
      } else {
        upload(file)
      }
    }

    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }

  async function upload(file: File) {
    const item = reactive<PhotoUpload>({
      key: ++uploaded,
      name: file.name,
      progress: 0,
      url: URL.createObjectURL(file),
      controller: new AbortController(),
    })

    uploads.value.push(item)

    try {
      const response = await uploadEditorRestaurantPhoto(restaurant.value.id, {file}, {
        signal: item.controller.signal,
        onUploadProgress: (event) => {
          item.progress = event.total ? Math.round(event.loaded / event.total * 100) : 0
        },
      })

      draft.value = [...draft.value, response.data.data]
    } catch (e) {
      if (!axios.isCancel(e)) {
        const reason = axios.isAxiosError(e) ? e.response?.data?.errors?.file?.[0] : null

        rejected.value.push(reason ? `${file.name}: ${reason}` : t('editor.photos.upload_failed', {name: file.name}))
      }
    } finally {
      uploads.value = uploads.value.filter((other) => other.key !== item.key)
      URL.revokeObjectURL(item.url)
    }
  }

  function cancelUploads() {
    uploads.value.forEach((item) => item.controller.abort())
  }

  function onDrop(event: DragEvent) {
    dragging.value = false
    addFiles(event.dataTransfer?.files ?? null)
  }

  function remove(photo: Media) {
    draft.value = draft.value.filter((item) => item.id !== photo.id)
  }

  function onDiscard() {
    cancelUploads()
    rejected.value = []
    discard()
  }

  onBeforeUnmount(cancelUploads)
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.photos')"
              :subtitle="t('editor.subtitles.photos')"
              :dirty="dirty"
              :saving="saving"
              :failed="failed"
              :can-save="uploads.length === 0"
              @navigate="editor.close()"
              @close="editor.close()"
              @discard="onDiscard"
              @save="save">
    <template #status v-if="uploads.length && !saving">
      <span class="size-[7px] rounded-full bg-amber-500" aria-hidden="true"/>
      <span class="text-[#a16207]">{{ t('editor.photos.uploading', uploads.length) }}</span>
    </template>

    <div class="flex flex-col items-center gap-1.5 px-4 py-5 border-[1.5px] border-dashed rounded-lg text-center transition-colors"
         :class="dragging ? 'border-blue-600 bg-blue-50' : 'border-zinc-400 bg-zinc-50'"
         v-if="draft.length + uploads.length < MAX_PHOTOS"
         @dragover.prevent="dragging = true"
         @dragleave="dragging = false"
         @drop.prevent="onDrop">
      <span class="size-10 flex items-center justify-center rounded-full bg-white border border-zinc-200 text-zinc-700">
        <Upload class="size-[18px]"/>
      </span>

      <p class="mt-1 font-semibold">
        {{ t('editor.photos.drop') }}
        <button type="button"
                class="text-blue-600 underline font-semibold e-focus"
                @click="fileInput?.click()">
          {{ t('editor.photos.browse') }}
        </button>
      </p>

      <p class="e-help">{{ t('editor.photos.formats') }}</p>

      <input class="hidden"
             type="file"
             accept="image/jpeg,image/png,image/webp"
             multiple
             ref="fileInput"
             @change="addFiles(($event.target as HTMLInputElement).files)"/>
    </div>

    <ul class="flex flex-col gap-1"
        v-if="rejected.length">
      <li class="e-error" v-for="(message, index) in rejected" :key="index">{{ message }}</li>
    </ul>

    <p class="e-error" v-if="error('media')">{{ error('media') }}</p>

    <section class="flex flex-col gap-2.5"
             v-if="draft.length || uploads.length">
      <div class="flex items-baseline justify-between">
        <p class="e-label">{{ t('editor.structure.photos_count', draft.length) }}</p>
        <p class="e-help" v-if="draft.length > 1">{{ t('editor.photos.order') }}</p>
      </div>

      <VueDraggable class="grid grid-cols-2 gap-x-3 gap-y-3.5"
                    v-model="draft"
                    handle=".e-grip"
                    ghost-class="e-drag-ghost"
                    :animation="150">
        <div class="flex flex-col gap-1.5 min-w-0"
             v-for="(photo, index) in draft" :key="photo.id">
          <div class="relative aspect-[4/3] rounded-lg overflow-hidden bg-zinc-100">
            <img class="size-full object-cover"
                 :src="thumbnail(photo)"
                 alt=""
                 draggable="false"/>

            <span class="absolute inset-0 rounded-lg shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)]" aria-hidden="true"/>

            <span class="absolute top-2 left-2 h-[22px] inline-flex items-center px-[7px] rounded bg-zinc-900 text-white text-[11px]/4 font-bold">
              {{ index === 0 ? t('editor.photos.cover') : index + 1 }}
            </span>

            <button type="button"
                    class="e-icon-btn absolute top-1.5 right-1.5 size-[30px] bg-white/90 shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                    :aria-label="t('editor.photos.remove', {name: photoName(photo, index)})"
                    :title="t('editor.photos.remove', {name: photoName(photo, index)})"
                    @click="remove(photo)">
              <Trash2 class="size-[15px]"/>
            </button>

            <GripHandle class="absolute bottom-1.5 right-1.5 size-[30px] rounded-md bg-white/90 text-zinc-600 shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                        icon-class="size-[15px]"
                        :name="photoName(photo, index)"
                        :index="index"
                        :count="draft.length"
                        :columns="2"
                        @move="(from, to) => draft = moveItem(draft, from, to)"/>
          </div>

          <p class="text-xs text-zinc-600 truncate">{{ photoName(photo, index) }}</p>
        </div>
      </VueDraggable>

      <div class="grid grid-cols-2 gap-x-3 gap-y-3.5"
           v-if="uploads.length">
        <div class="flex flex-col gap-1.5 min-w-0"
             v-for="item in uploads" :key="item.key">
          <div class="relative aspect-[4/3] rounded-lg overflow-hidden bg-zinc-100">
            <img class="size-full object-cover" :src="item.url" alt=""/>

            <div class="absolute inset-0 bg-white/72 flex flex-col items-center justify-center gap-2">
              <span class="text-xs font-semibold text-zinc-700">
                {{ t('editor.photos.uploading_percent', {percent: item.progress}) }}
              </span>

              <span class="w-[70%] h-1 rounded-sm bg-zinc-200 overflow-hidden">
                <span class="block h-full bg-blue-600 transition-[width]" :style="{width: `${item.progress}%`}"/>
              </span>
            </div>

            <button type="button"
                    class="e-icon-btn absolute top-1.5 right-1.5 size-[30px] bg-white/90"
                    :aria-label="t('editor.photos.cancel_upload')"
                    :title="t('editor.photos.cancel_upload')"
                    @click="item.controller.abort()">
              <X class="size-[15px]"/>
            </button>
          </div>

          <p class="text-xs text-zinc-600 truncate">{{ item.name }}</p>
        </div>
      </div>
    </section>

    <!-- without photos, there's only the dropzone -->
    <InfoBox v-if="draft.length || uploads.length">{{ t('editor.photos.info') }}</InfoBox>
  </PanelShell>
</template>
