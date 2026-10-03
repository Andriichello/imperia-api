<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import axios from 'axios'
  import {Eye, EyeOff, Plus, X} from 'lucide-vue-next'
  import type {Media} from '@/api'
  import {uploadEditorRestaurantPhoto} from '@/api'
  import PropRow from '@/Components/Version/PropRow.vue'
  import type {VersionField} from '@/version/fields'
  import {useVersionStore} from '@/stores/version'

  /**
   * Photos of an item: the live ones, and the ones from the version's date with what happens to
   * them (Added, Replaced, Hidden, Shown, Removed). Photos can be hidden, removed and uploaded.
   */
  type Photo = { id: number, is_hidden: boolean }

  const props = defineProps({
    field: {
      type: Object as PropType<VersionField<Photo[] | null>>,
      required: true,
    },
    // the most photos (hidden ones too)
    max: {
      type: Number,
      required: true,
    },
    isNew: {
      type: Boolean,
      default: false,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  const live = computed(() => props.field.live ?? [])
  const photos = computed(() => props.field.value ?? [])
  const uploading = ref(0)
  const rejected = ref<string | null>(null)
  const input = ref<HTMLInputElement | null>(null)

  const media = (id: number): Media | null => store.photos[id] ?? null
  const thumbnail = (id: number) => {
    const item = media(id)

    return item ? (item.variants?.find((variant) => variant.extension === 'webp')?.url ?? item.url) : null
  }
  const nameOf = (id: number) => media(id)?.title ?? ''

  /** What happens to the photo at the version's date. */
  function chipOf(photo: Photo, index: number): 'added' | 'replaced' | 'hidden' | 'shown' | null {
    const before = live.value.find((item) => item.id === photo.id)

    if (!before) {
      const replaced = live.value[index]

      return replaced && !photos.value.some((item) => item.id === replaced.id) ? 'replaced' : 'added'
    }

    if (photo.is_hidden !== before.is_hidden) {
      return photo.is_hidden ? 'hidden' : 'shown'
    }

    return null
  }

  // live photos, which aren't there from the version's date (but replaced ones)
  const removed = computed(() => live.value.filter((item, index) => !photos.value.some((other) => other.id === item.id)
    && !(photos.value[index] && !live.value.some((other) => other.id === photos.value[index].id))))

  const CHIPS = {
    added: 'bg-green-100 text-green-800',
    replaced: 'bg-blue-100 text-blue-800',
    hidden: 'bg-zinc-100 text-zinc-600',
    shown: 'bg-green-100 text-green-800',
  }

  function toggle(photo: Photo) {
    props.field.set(photos.value.map((item) => item.id === photo.id ? {...item, is_hidden: !item.is_hidden} : item), 0)
  }

  function remove(photo: Photo) {
    props.field.set(photos.value.filter((item) => item.id !== photo.id), 0)
  }

  async function upload(files: FileList | null) {
    const file = files?.[0]
    rejected.value = null

    if (input.value) {
      input.value.value = ''
    }

    if (!file) {
      return
    }

    uploading.value++

    try {
      const photo = (await uploadEditorRestaurantPhoto(store.restaurant!.id, {file})).data.data

      store.photos[photo.id] = photo
      props.field.set([...photos.value, {id: photo.id, is_hidden: false}], 0)
    } catch (e) {
      const reason = axios.isAxiosError(e) ? e.response?.data?.errors?.file?.[0] : null

      rejected.value = reason ?? t('editor.photos.upload_failed', {name: file.name})
    } finally {
      uploading.value--
    }
  }
</script>

<template>
  <PropRow :label="t('editor.photos.label')"
           :changed="field.changed"
           :conflict="field.conflict"
           :error="field.error ?? rejected"
           :revertable="!isNew"
           top
           @revert="field.revert()">
    <template #live>
      <span v-if="isNew || !live.length">{{ isNew ? '—' : t('admin.version.no_photos') }}</span>

      <span class="flex flex-wrap gap-2" v-else>
        <span class="w-14 flex flex-col gap-1" v-for="photo in live" :key="photo.id">
          <img class="size-14 rounded-md object-cover shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)]"
               :class="{'opacity-50 grayscale': photo.is_hidden}"
               :src="thumbnail(photo.id)!"
               alt=""
               v-if="thumbnail(photo.id)"/>
          <span class="text-[11px]/[14px] text-zinc-500 truncate">{{ nameOf(photo.id) }}</span>
        </span>
      </span>
    </template>

    <div class="flex flex-wrap gap-2">
      <span class="group relative w-14 flex flex-col gap-1" v-for="(photo, index) in photos" :key="photo.id">
        <img class="size-14 rounded-md object-cover shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)]"
             :class="{'opacity-50 grayscale': photo.is_hidden}"
             :src="thumbnail(photo.id)!"
             alt=""
             v-if="thumbnail(photo.id)"/>
        <span class="size-14 rounded-md bg-zinc-100" v-else/>

        <span class="absolute top-1 right-1 hidden group-hover:flex group-focus-within:flex gap-0.5"
              v-if="!store.readOnly">
          <button type="button"
                  class="size-6 flex items-center justify-center rounded bg-white/95 text-zinc-700 shadow-sm e-focus"
                  :aria-label="t(photo.is_hidden ? 'editor.photos.show' : 'editor.photos.hide', {name: nameOf(photo.id)})"
                  :title="t(photo.is_hidden ? 'editor.photos.show' : 'editor.photos.hide', {name: nameOf(photo.id)})"
                  @click="toggle(photo)">
            <Eye class="size-3.5" v-if="photo.is_hidden"/>
            <EyeOff class="size-3.5" v-else/>
          </button>
          <button type="button"
                  class="size-6 flex items-center justify-center rounded bg-white/95 text-zinc-700 shadow-sm e-focus"
                  :aria-label="t('editor.photos.remove', {name: nameOf(photo.id)})"
                  :title="t('editor.photos.remove', {name: nameOf(photo.id)})"
                  @click="remove(photo)">
            <X class="size-3.5"/>
          </button>
        </span>

        <span class="text-[11px]/[14px] text-zinc-500 truncate">{{ nameOf(photo.id) }}</span>
        <span class="e-pill e-pill-sm self-start" :class="CHIPS[chipOf(photo, index)!]" v-if="chipOf(photo, index)">
          {{ t('admin.version.photos.' + chipOf(photo, index)) }}
        </span>
      </span>

      <span class="w-14 flex flex-col gap-1 opacity-60" v-for="photo in removed" :key="`removed-${photo.id}`">
        <img class="size-14 rounded-md object-cover grayscale" :src="thumbnail(photo.id)!" alt="" v-if="thumbnail(photo.id)"/>
        <span class="text-[11px]/[14px] text-zinc-500 truncate line-through">{{ nameOf(photo.id) }}</span>
        <span class="e-pill e-pill-sm self-start bg-red-100 text-red-800">{{ t('admin.version.photos.removed') }}</span>
      </span>

      <button type="button"
              class="size-14 flex items-center justify-center rounded-md border border-dashed border-zinc-400 text-zinc-600 hover:bg-zinc-50 e-focus"
              :aria-label="t('editor.photos.add')"
              :title="t('editor.photos.add')"
              :disabled="uploading > 0"
              v-if="!store.readOnly && photos.length < max"
              @click="input?.click()">
        <span class="text-xs" v-if="uploading">…</span>
        <Plus class="size-4" v-else/>
      </button>

      <input class="hidden"
             type="file"
             accept="image/jpeg,image/png,image/webp"
             ref="input"
             @change="upload(($event.target as HTMLInputElement).files)"/>
    </div>
  </PropRow>
</template>
