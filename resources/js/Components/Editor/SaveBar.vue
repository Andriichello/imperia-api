<script setup lang="ts">
  import {onBeforeUnmount, onMounted, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Check, ChevronDown, ChevronUp} from 'lucide-vue-next'
  import UnsavedList from '@/Components/Editor/UnsavedList.vue'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The editor's one save bar, under the open panel: how many items have unsaved changes (they
   * open the list of them), Discard all and Save all. Nothing reaches guests before Save.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const bar = ref<HTMLElement | null>(null)

  function toggle() {
    editor.reviewOpen = !editor.reviewOpen
  }

  // a click outside of the bar and its list closes the list
  function onPointerDown(event: PointerEvent) {
    if (editor.reviewOpen && bar.value && !bar.value.contains(event.target as Node)
      && !(event.target as Element).closest?.('[data-keeps-review]')) {
      editor.reviewOpen = false
    }
  }

  onMounted(() => document.addEventListener('pointerdown', onPointerDown))
  onBeforeUnmount(() => document.removeEventListener('pointerdown', onPointerDown))
</script>

<template>
  <div class="relative shrink-0 flex items-center gap-2 pl-5 pr-4 py-3 border-t"
       :class="editor.unsaved.length ? 'border-[#fde68a] bg-[#fffbeb]' : 'border-zinc-200 bg-white'"
       ref="bar">
    <template v-if="editor.unsaved.length">
      <button type="button"
              class="flex-1 min-w-0 inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#92400e] text-start rounded e-focus"
              aria-haspopup="dialog"
              :aria-expanded="editor.reviewOpen"
              @click="toggle">
        <span class="size-[7px] shrink-0 rounded-full bg-amber-500" aria-hidden="true"/>
        <span class="truncate" v-if="editor.saving">{{ t('editor.save.saving') }}</span>
        <span class="truncate" v-else-if="editor.uploads">{{ t('editor.photos.uploading', editor.uploads) }}</span>
        <span class="truncate" v-else>{{ t('editor.save.count', editor.unsaved.length) }}</span>
        <ChevronDown class="size-4 shrink-0" v-if="editor.reviewOpen"/>
        <ChevronUp class="size-4 shrink-0" v-else/>
      </button>

      <button type="button"
              class="e-btn e-btn-secondary"
              :disabled="editor.saving"
              @click="editor.discardAll()">
        {{ t('editor.save.discard_all') }}
      </button>

      <button type="button"
              class="e-btn e-btn-primary"
              :disabled="editor.saving || editor.uploads > 0"
              :title="editor.uploads ? t('editor.save.wait_uploads') : undefined"
              @click="editor.saveAll()">
        {{ t('editor.save.save_all') }}
      </button>

      <UnsavedList v-if="editor.reviewOpen"/>
    </template>

    <p class="flex-1 inline-flex items-center gap-2 text-[13px] text-zinc-500"
       role="status"
       v-else>
      <Check class="size-4 text-[#00a63e]"/>
      {{ t('editor.save.saved') }}
    </p>
  </div>
</template>
