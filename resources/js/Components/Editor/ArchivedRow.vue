<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Archive, Ellipsis, Image, RotateCcw, Trash2} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'

  /**
   * An archived category or dish in its list: muted, with what it was ("Archived 2 Sep · 300 g ·
   * 150 ₴"), Restore, and Delete permanently in its ⋯ menu.
   */
  defineProps({
    name: {
      type: String,
      required: true,
    },
    badge: {
      type: String as PropType<string | null>,
      default: null,
    },
    meta: {
      type: String,
      default: '',
    },
    // a dish's photo (none: a placeholder), none for a category
    thumbnail: {
      type: String as PropType<string | null | undefined>,
      default: undefined,
    },
  })

  const emits = defineEmits<{
    (e: 'restore'): void
    (e: 'delete'): void
  }>()

  const {t} = useI18n()
</script>

<template>
  <div class="flex items-center gap-2.5 min-h-[60px] py-2 pl-1 pr-1 border-b border-[#f0f0f1] bg-zinc-50">
    <Archive class="size-4 shrink-0 mx-1 text-zinc-400"/>

    <template v-if="thumbnail !== undefined">
      <img class="size-11 shrink-0 rounded-md object-cover opacity-70 grayscale-50"
           :src="thumbnail"
           alt=""
           v-if="thumbnail"/>

      <span class="size-11 shrink-0 flex items-center justify-center rounded-md border border-dashed border-zinc-300 text-zinc-400"
            aria-hidden="true"
            v-else>
        <Image class="size-4"/>
      </span>
    </template>

    <span class="flex-1 min-w-0 flex flex-col gap-0.5">
      <span class="flex items-center gap-1.5 font-semibold text-zinc-500">
        <!-- the badge gives way to the name -->
        <span class="shrink-0 max-w-full truncate">{{ name }}</span>
        <span class="e-pill e-pill-sm min-w-0 bg-zinc-100 text-zinc-500" v-if="badge">
          <span class="truncate">{{ badge }}</span>
        </span>
      </span>
      <span class="text-xs/4 text-zinc-500 line-clamp-2">{{ meta }}</span>
    </span>

    <button type="button"
            class="e-btn e-btn-secondary h-8 px-2.5"
            @click="emits('restore')">
      <RotateCcw class="size-[15px]"/>
      {{ t('editor.actions.restore') }}
    </button>

    <DropdownMenu align="end">
      <template #trigger="{open, toggle}">
        <button type="button"
                class="e-icon-btn size-7"
                aria-haspopup="menu"
                :aria-expanded="open"
                :aria-label="t('editor.panel.more_actions', {name})"
                @click="toggle">
          <Ellipsis class="size-[18px]"/>
        </button>
      </template>

      <button type="button" class="e-dropdown-item text-red-700" role="menuitem" @click="emits('delete')">
        <Trash2 class="size-4"/>
        {{ t('editor.actions.delete_permanently') }}
      </button>
    </DropdownMenu>
  </div>
</template>
