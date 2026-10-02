<script setup lang="ts">
  import {computed, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Check, Copy, MapPin, Phone} from 'lucide-vue-next'
  import {EditorRestaurantEstablishment, updateEditorRestaurant} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {detailsOf, detailsPreview, detailsRequest} from '@/editor/drafts'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Name, type and contacts of the restaurant, and the address of its page.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const restaurant = computed(() => editor.restaurant!)

  const {draft, dirty, saving, failed, discard, save, error} = usePanelDraft({
    saved: () => detailsOf(restaurant.value),
    save: async (details) => (await updateEditorRestaurant(restaurant.value.id, detailsRequest(details))).data.data,
    preview: (details, locale) => detailsPreview(details, locale, editor.defaultLocale),
  })

  const {locale, languages, placeholder, textError} = useContentLocale(
    () => [draft.value.name, draft.value.address]
  )

  // the name is required in the default language
  const canSave = computed(() => !!draft.value.name[editor.defaultLocale]?.trim())

  const TYPES = Object.values(EditorRestaurantEstablishment)

  /** Names of the types are the app's ones, in the language of the guest. */
  function typeLabel(type: string): string {
    return t(type === 'restaurant' ? 'restaurant.title' : `restaurant.${type}_title`)
  }

  // the public page's address without its protocol: "example.com/en/web/" and "smak"
  const webAddress = computed(() => {
    const url = new URL(restaurant.value.url)
    const path = url.host + url.pathname
    const slug = path.split('/').pop() ?? ''

    return {base: path.slice(0, path.length - slug.length), slug}
  })

  const copied = ref(false)

  async function copyAddress() {
    await navigator.clipboard.writeText(restaurant.value.url)

    copied.value = true
    setTimeout(() => copied.value = false, 2000)
  }
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.details')"
              :subtitle="t('editor.subtitles.details')"
              :languages="languages"
              v-model:locale="locale"
              :dirty="dirty"
              :saving="saving"
              :failed="failed"
              :can-save="canSave"
              @navigate="editor.close()"
              @close="editor.close()"
              @discard="discard"
              @save="save">
    <section class="flex flex-col gap-3.5">
      <h3 class="e-section">{{ t('editor.details.name_and_type') }}</h3>

      <div class="flex flex-col gap-1.5">
        <FieldLabel target="details-name"
                    :label="t('editor.details.name')"
                    :locale="locale"/>

        <input id="details-name"
               class="e-input"
               type="text"
               maxlength="255"
               :placeholder="placeholder(draft.name)"
               :aria-invalid="!!textError(error, 'name')"
               v-model="draft.name[locale]"/>

        <p class="e-error" v-if="textError(error, 'name')">{{ textError(error, 'name') }}</p>
      </div>

      <div class="flex flex-col gap-1.5">
        <FieldLabel target="details-type"
                    :label="t('editor.details.type')"/>

        <select id="details-type"
                class="e-input"
                :aria-invalid="!!error('establishment')"
                v-model="draft.establishment">
          <option :value="type"
                  v-for="type in TYPES" :key="type">
            {{ typeLabel(type) }}
          </option>
        </select>

        <p class="e-error" v-if="error('establishment')">{{ error('establishment') }}</p>
        <p class="e-help" v-else>{{ t('editor.details.type_help') }}</p>
      </div>
    </section>

    <div class="h-px shrink-0 bg-[#f0f0f1]"/>

    <section class="flex flex-col gap-3.5">
      <h3 class="e-section">{{ t('editor.details.contacts') }}</h3>

      <div class="flex flex-col gap-1.5">
        <FieldLabel target="details-phone"
                    :label="t('editor.details.phone')"/>

        <div class="relative">
          <Phone class="size-4 absolute left-3 top-3 text-zinc-400" aria-hidden="true"/>

          <input id="details-phone"
                 class="e-input pl-9"
                 type="tel"
                 maxlength="30"
                 :aria-invalid="!!error('phone')"
                 v-model="draft.phone"/>
        </div>

        <p class="e-error" v-if="error('phone')">{{ error('phone') }}</p>
        <p class="e-help" v-else>{{ t('editor.details.phone_help') }}</p>
      </div>

      <div class="flex flex-col gap-1.5">
        <FieldLabel target="details-address"
                    :label="t('editor.details.address')"
                    :locale="locale"/>

        <div class="relative">
          <MapPin class="size-4 absolute left-3 top-3 text-zinc-400" aria-hidden="true"/>

          <input id="details-address"
                 class="e-input pl-9"
                 type="text"
                 maxlength="255"
                 :placeholder="placeholder(draft.address)"
                 :aria-invalid="!!textError(error, 'address')"
                 v-model="draft.address[locale]"/>
        </div>

        <p class="e-error" v-if="textError(error, 'address')">{{ textError(error, 'address') }}</p>
        <p class="e-help" v-else>{{ t('editor.details.address_help') }}</p>
      </div>
    </section>

    <div class="h-px shrink-0 bg-[#f0f0f1]"/>

    <section class="flex flex-col gap-3.5">
      <h3 class="e-section">{{ t('editor.details.your_page') }}</h3>

      <div class="flex flex-col gap-1.5">
        <FieldLabel :label="t('editor.details.web_address')"/>

        <div class="flex gap-2">
          <div class="flex-1 min-w-0 h-10 flex items-center px-3 rounded-md bg-zinc-100 text-zinc-600 whitespace-nowrap overflow-hidden">
            {{ webAddress.base }}<b class="font-semibold text-zinc-900">{{ webAddress.slug }}</b>
          </div>

          <button type="button"
                  class="e-btn e-btn-secondary h-10"
                  @click="copyAddress">
            <Check class="size-[15px]" v-if="copied"/>
            <Copy class="size-[15px]" v-else/>
            {{ copied ? t('editor.details.copied') : t('editor.details.copy') }}
          </button>
        </div>
      </div>
    </section>
  </PanelShell>
</template>
