<script setup lang="ts">
  import {computed, PropType, reactive, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import axios from 'axios'
  import {Eye, EyeOff, Trash2, Upload, X} from 'lucide-vue-next'
  import {VueDraggable} from 'vue-draggable-plus'
  import type {Media} from '@/api'
  import {uploadEditorRestaurantPhoto} from '@/api'
  import GripHandle from '@/Components/Editor/Fields/GripHandle.vue'
  import {moveItem} from '@/editor/lists'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Photos of the restaurant or of a dish: in their order (the first one guests see is the
   * cover), hidden ones are kept but guests don't see them, deleted ones are left out. Uploads
   * start right away and go on, when the panel is left: an uploaded photo joins the draft.
   * Compact: three in a row, with their buttons at the bottom (a dish's photos).
   */
  const props = defineProps({
    modelValue: {
      type: Array as PropType<Media[]>,
      required: true,
    },
    // the most photos, hidden ones included
    max: {
      type: Number,
      required: true,
    },
    compact: {
      type: Boolean,
      default: false,
    },
    // an uploaded photo: it's added to the part's draft, whether its panel is open or not
    uploaded: {
      type: Function as PropType<(photo: Media) => void>,
      required: true,
    },
    // of saving
    error: {
      type: String as PropType<string | null>,
      default: null,
    },
    help: {
      type: String,
      default: '',
    },
  })

  const emits = defineEmits<{
    (e: 'update:modelValue', photos: Media[]): void
  }>()

  const editor = useEditorStore()
  const {t} = useI18n()

  const TYPES = ['image/jpeg', 'image/png', 'image/webp']
  const MAX_SIZE = 10 * 1024 * 1024

  interface PhotoUpload {
    key: number
    name: string
    // percent
    progress: number
    // the file, shown while it's uploaded
    url: string
    controller: AbortController
  }

  const uploads = ref<PhotoUpload[]>([])
  // files, which weren't uploaded, and why
  const rejected = ref<string[]>([])
  const dragging = ref(false)
  const fileInput = ref<HTMLInputElement | null>(null)
  let uploaded = 0

  const photos = computed<Media[]>({
    get: () => props.modelValue,
    set: (value) => emits('update:modelValue', value),
  })

  const columns = computed(() => props.compact ? 3 : 2)
  const total = computed(() => photos.value.length + uploads.value.length)
  const hidden = computed(() => photos.value.filter((photo) => photo.is_hidden).length)

  /** "4 · 1 hidden", or "2 of 3" of a compact one. */
  const count = computed(() => props.compact
    ? t('editor.photos.of', {count: total.value, max: props.max})
    : [String(total.value), hidden.value ? t('editor.structure.hidden_count', hidden.value) : null].filter(Boolean).join(' · '))

  // the first one guests see
  const cover = computed(() => photos.value.find((photo) => !photo.is_hidden) ?? null)

  /** Its title, or "Photo 2" for ones without one (older uploads have a hash as their title). */
  function photoName(photo: Media, index: number): string {
    const title = photo.title ?? ''

    return title && !/^[0-9a-f]{32}$/.test(title) ? title : t('editor.photos.untitled', {number: index + 1})
  }

  /** The WebP version, when there's one. */
  function thumbnail(photo: Media): string {
    return photo.variants?.find((variant) => variant.extension === 'webp')?.url ?? photo.url
  }

  /** "1 · Cover", "2", …: the cover is the first photo guests see. */
  function badge(photo: Media, index: number): string {
    return photo === cover.value ? t('editor.photos.cover', {number: index + 1}) : String(index + 1)
  }

  function addFiles(files: FileList | null) {
    rejected.value = []

    for (const file of Array.from(files ?? [])) {
      if (!TYPES.includes(file.type)) {
        rejected.value.push(t('editor.photos.wrong_type', {name: file.name}))
      } else if (file.size > MAX_SIZE) {
        rejected.value.push(t('editor.photos.too_big', {name: file.name}))
      } else if (total.value >= props.max) {
        rejected.value.push(t('editor.photos.too_many', {count: props.max}))
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
    editor.uploads++

    try {
      const response = await uploadEditorRestaurantPhoto(editor.restaurant!.id, {file}, {
        signal: item.controller.signal,
        onUploadProgress: (event) => {
          item.progress = event.total ? Math.round(event.loaded / event.total * 100) : 0
        },
      })

      props.uploaded(response.data.data)
    } catch (e) {
      if (!axios.isCancel(e)) {
        const reason = axios.isAxiosError(e) ? e.response?.data?.errors?.file?.[0] : null

        rejected.value.push(reason ? `${file.name}: ${reason}` : t('editor.photos.upload_failed', {name: file.name}))
      }
    } finally {
      uploads.value = uploads.value.filter((other) => other.key !== item.key)
      editor.uploads--
      URL.revokeObjectURL(item.url)
    }
  }

  function onDrop(event: DragEvent) {
    dragging.value = false

    // photos of the list are dragged too: only files are added
    if (event.dataTransfer?.files?.length) {
      addFiles(event.dataTransfer.files)
    }
  }

  function onDragOver(event: DragEvent) {
    if (event.dataTransfer?.types?.includes('Files')) {
      event.preventDefault()
      dragging.value = true
    }
  }

  function toggleHidden(photo: Media) {
    photos.value = photos.value.map((item) => item.id === photo.id ? {...item, is_hidden: !item.is_hidden} : item)
  }

  function remove(photo: Media) {
    photos.value = photos.value.filter((item) => item.id !== photo.id)
  }

  // buttons on the photos
  const button = computed(() => props.compact ? 'size-[26px]' : 'size-[30px]')
  const icon = computed(() => props.compact ? 'size-3.5' : 'size-[15px]')
</script>

<template>
  <div class="flex flex-col gap-2"
       @dragover="onDragOver"
       @dragleave="dragging = false"
       @drop.prevent="onDrop">
    <div class="flex items-baseline gap-1.5">
      <p class="e-label">{{ t('editor.photos.label') }}</p>
      <span class="e-help">{{ count }}</span>
      <slot name="label"/>
      <p class="e-help ml-auto" v-if="photos.length > 1">{{ t('editor.photos.order') }}</p>
    </div>

    <VueDraggable class="grid gap-y-3"
                  :class="compact ? 'grid-cols-3 gap-x-2.5' : 'grid-cols-2 gap-x-3'"
                  v-model="photos"
                  draggable=".e-photo"
                  handle=".e-grip"
                  ghost-class="e-drag-ghost"
                  :animation="150">
      <div class="e-photo flex flex-col gap-1.5 min-w-0"
           v-for="(photo, index) in photos" :key="photo.id">
        <div class="relative aspect-[4/3] rounded-lg overflow-hidden bg-zinc-100"
             :class="{'grayscale-60': photo.is_hidden}">
          <img class="size-full object-cover"
               :src="thumbnail(photo)"
               alt=""
               draggable="false"/>

          <span class="absolute inset-0 rounded-lg shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)]" aria-hidden="true"/>
          <span class="absolute inset-0 bg-white/55" aria-hidden="true" v-if="photo.is_hidden"/>

          <span class="absolute top-1.5 left-1.5 h-5 inline-flex items-center gap-1 px-1.5 rounded bg-white text-zinc-600 text-[11px]/4 font-bold shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                v-if="photo.is_hidden">
            <EyeOff class="size-3"/>
            {{ t('editor.photos.hidden') }}
          </span>

          <span class="absolute top-1.5 left-1.5 h-5 inline-flex items-center px-1.5 rounded bg-zinc-900 text-white text-[11px]/4 font-bold"
                v-else>
            {{ badge(photo, index) }}
          </span>

          <div class="absolute flex gap-1"
               :class="compact ? 'bottom-[5px] left-[5px]' : 'top-[5px] right-[5px]'">
            <button type="button"
                    class="e-icon-btn bg-white/95 shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                    :class="[button, {'text-blue-700!': photo.is_hidden}]"
                    :aria-label="t(photo.is_hidden ? 'editor.photos.show' : 'editor.photos.hide', {name: photoName(photo, index)})"
                    :title="t(photo.is_hidden ? 'editor.photos.show' : 'editor.photos.hide', {name: photoName(photo, index)})"
                    @click="toggleHidden(photo)">
              <Eye :class="icon" v-if="photo.is_hidden"/>
              <EyeOff :class="icon" v-else/>
            </button>

            <button type="button"
                    class="e-icon-btn bg-white/95 shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                    :class="button"
                    :aria-label="t('editor.photos.remove', {name: photoName(photo, index)})"
                    :title="t('editor.photos.remove', {name: photoName(photo, index)})"
                    @click="remove(photo)">
              <Trash2 :class="icon"/>
            </button>
          </div>

          <GripHandle class="absolute bottom-[5px] right-[5px] rounded-md bg-white/95 text-zinc-600 shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                      :class="button"
                      :icon-class="icon"
                      :name="photoName(photo, index)"
                      :index="index"
                      :count="photos.length"
                      :columns="columns"
                      @move="(from, to) => photos = moveItem(photos, from, to)"/>
        </div>

        <p class="text-xs truncate"
           :class="photo.is_hidden ? 'text-zinc-400' : 'text-zinc-600'">
          {{ photoName(photo, index) }}
        </p>
      </div>

      <div class="flex flex-col gap-1.5 min-w-0"
           v-for="item in uploads" :key="`upload-${item.key}`">
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
                  class="e-icon-btn absolute top-[5px] right-[5px] bg-white/95 shadow-[0_1px_2px_rgba(0,0,0,0.15)]"
                  :class="button"
                  :aria-label="t('editor.photos.cancel_upload')"
                  :title="t('editor.photos.cancel_upload')"
                  @click="item.controller.abort()">
            <X :class="icon"/>
          </button>
        </div>

        <p class="text-xs text-zinc-600 truncate">{{ item.name }}</p>
      </div>

      <button type="button"
              class="flex flex-col min-w-0 text-center rounded-lg e-focus"
              v-if="total < max"
              @click="fileInput?.click()">
        <span class="aspect-[4/3] flex flex-col items-center justify-center gap-1 rounded-lg border-[1.5px] border-dashed text-zinc-700 transition-colors"
              :class="dragging ? 'border-blue-600 bg-blue-50' : 'border-zinc-400 bg-zinc-50 hover:bg-zinc-100'">
          <Upload class="size-[18px]"/>
          <span class="font-semibold" :class="compact ? 'text-xs' : 'text-[13px]/4'">{{ t('editor.photos.add') }}</span>
          <span class="text-[11px]/[14px] text-zinc-500">{{ t('editor.photos.or_drop') }}</span>
        </span>
      </button>
    </VueDraggable>

    <input class="hidden"
           type="file"
           accept="image/jpeg,image/png,image/webp"
           multiple
           ref="fileInput"
           @change="addFiles(($event.target as HTMLInputElement).files)"/>

    <ul class="flex flex-col gap-1"
        v-if="rejected.length">
      <li class="e-error" v-for="(message, index) in rejected" :key="index">{{ message }}</li>
    </ul>

    <p class="e-error" v-if="error">{{ error }}</p>
    <p class="e-help" v-if="help">{{ help }}</p>
  </div>
</template>
