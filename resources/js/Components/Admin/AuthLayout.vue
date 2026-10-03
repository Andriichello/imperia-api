<script setup lang="ts">
  import {useI18n} from 'vue-i18n'
  import {Utensils} from 'lucide-vue-next'
  import LanguageMenu from '@/Components/Admin/LanguageMenu.vue'
  import PageIllustration from '@/Components/Admin/PageIllustration.vue'

  /**
   * Pages before signing in: the form on the left, a public page (with a part selected,
   * like in the editor) on the right.
   */
  const {t} = useI18n()
</script>

<template>
  <div class="h-full min-h-[600px] flex bg-white">
    <div class="flex-1 min-w-0 flex flex-col">
      <div class="h-16 shrink-0 flex items-center gap-2.5 px-7">
        <span class="size-[30px] rounded-[7px] bg-zinc-900 text-white flex items-center justify-center" aria-hidden="true">
          <Utensils class="size-4"/>
        </span>
        <span class="text-[15px] font-bold">{{ t('admin.brand') }}</span>
        <div class="flex-1"/>
        <LanguageMenu/>
      </div>

      <main class="flex-1 min-h-0 flex items-center justify-center px-7 pb-10 overflow-y-auto">
        <div class="w-full max-w-[360px] flex flex-col gap-[18px]">
          <slot/>
        </div>
      </main>
    </div>

    <div class="w-[46%] shrink-0 flex flex-col items-center justify-center gap-[22px] border-l border-zinc-200 e-dots max-lg:hidden"
         aria-hidden="true">
      <div class="w-[312px] h-[520px] overflow-hidden bg-white shadow-[0_0_0_1px_#d4d4d8,0_24px_48px_-20px_rgba(24,24,27,0.4)]">
        <div class="w-[390px] origin-top-left scale-[0.8]">
          <PageIllustration/>
        </div>
      </div>
      <p class="max-w-[340px] text-center text-sm text-zinc-600">{{ t('admin.sign_in.illustration') }}</p>
    </div>
  </div>
</template>
