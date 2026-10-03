<script setup lang="ts">
  import {useI18n} from 'vue-i18n'
  import {ChevronLeft, ChevronRight, Image, Languages, Search} from 'lucide-vue-next'

  /**
   * A restaurant's public page as the editor shows it, with its notes selected: 390px wide,
   * made up (no real restaurant is shown before signing in).
   */
  const {t} = useI18n()
</script>

<template>
  <div class="bg-[#f7f7f7] text-zinc-900 select-none">
    <div class="relative h-[240px] bg-[#f2f2f2] border-b border-zinc-200">
      <div class="absolute top-2 right-2 flex gap-2">
        <span class="h-11 px-3 flex items-center gap-1.5 rounded-md border border-zinc-200 bg-white text-sm font-semibold">
          <Languages class="size-4"/> EN
        </span>
        <span class="size-11 flex items-center justify-center rounded-md border border-zinc-200 bg-white">
          <Search class="size-5"/>
        </span>
      </div>

      <span class="absolute left-4 top-1/2 -translate-y-1/2 size-11 rounded-full bg-zinc-200/80 flex items-center justify-center text-zinc-400">
        <ChevronLeft class="size-6"/>
      </span>
      <span class="absolute right-4 top-1/2 -translate-y-1/2 size-11 rounded-full bg-zinc-300 flex items-center justify-center text-zinc-700">
        <ChevronRight class="size-6"/>
      </span>

      <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-8 px-3 flex items-center gap-2 rounded border border-zinc-200 bg-white text-sm font-medium text-zinc-600">
        <Image class="size-4"/> {{ t('admin.illustration.photo') }}
      </span>

      <span class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5">
        <span class="size-3 rounded-full border border-zinc-400"/>
        <span class="size-2 rounded-full bg-zinc-300"/>
        <span class="size-2 rounded-full bg-zinc-300"/>
      </span>
    </div>

    <div class="pt-5 pb-1 text-center">
      <p class="text-[26px]/8 font-bold">{{ t('admin.illustration.name') }}</p>
      <p class="text-base text-zinc-500">{{ t('admin.illustration.type') }}</p>
    </div>

    <div class="relative mx-0.5 mt-1 px-3 py-1 flex flex-col gap-2 outline-2 outline-blue-600">
      <span class="absolute -top-2.5 left-2 h-[22px] px-2 flex items-center rounded bg-blue-600 text-white text-xs font-semibold">
        {{ t('admin.illustration.notes') }}
      </span>

      <p class="relative max-w-[320px] px-4 py-2.5 rounded-2xl rounded-bl-none bg-[#dcedd6] text-[17px]/6"
         v-for="note in ['note_1', 'note_2', 'note_3']" :key="note">
        {{ t('admin.illustration.' + note) }}
      </p>
    </div>

    <div class="mx-3 mt-4 mb-6 px-6 py-4 flex items-center gap-3 bg-white border border-zinc-200 rounded">
      <div class="flex-1 min-w-0">
        <p class="text-xl font-bold">{{ t('admin.illustration.menu') }}</p>
        <p class="text-base text-zinc-500">{{ t('admin.illustration.menu_description') }}</p>
      </div>
      <ChevronRight class="size-6 text-zinc-700"/>
    </div>
  </div>
</template>
