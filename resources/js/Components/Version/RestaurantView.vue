<script setup lang="ts">
  import {computed, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Eye, EyeOff, Plus, Trash2} from 'lucide-vue-next'
  import {EditorRestaurantEstablishment} from '@/api'
  import PropRow from '@/Components/Version/PropRow.vue'
  import TextRow from '@/Components/Version/TextRow.vue'
  import PhotoStrip from '@/Components/Version/PhotoStrip.vue'
  import {isHex} from '@/editor/brand'
  import {fieldsFor} from '@/version/fields'
  import {changeOf, liveOf, newChanges, Target} from '@/version/model'
  import {useVersionStore} from '@/stores/version'

  /**
   * The restaurant page on the version's page: the restaurant's details, brand colors, photos
   * and notes, live and from the version's date. Notes can be added too.
   */
  const store = useVersionStore()
  const {t} = useI18n()

  /** The most photos of the restaurant. */
  const MAX_PHOTOS = 20

  const TYPES = Object.values(EditorRestaurantEstablishment).filter(Boolean) as string[]

  const restaurant = computed(() => store.restaurant!)
  const target = computed<Target>(() => ({type: 'restaurants', id: restaurant.value.id}))
  const live = computed(() => liveOf('restaurants', null, restaurant.value))
  const field = computed(() => fieldsFor(target.value, live.value, changeOf(store.version!, 'restaurants', restaurant.value.id)))

  const establishment = computed(() => field.value<string | null>('establishment'))
  const phone = computed(() => field.value<string | null>('phone'))
  const primary = computed(() => field.value<string | null>('brand_primary'))
  const content = computed(() => field.value<string | null>('brand_primary_content'))
  const media = computed(() => field.value<{ id: number, is_hidden: boolean }[] | null>('media'))

  const typeLabel = (type: string | null) => type ? t(type === 'restaurant' ? 'restaurant.title' : `restaurant.${type}_title`) : t('admin.version.none')

  /** Both colors together (the version takes them as a pair). */
  function setColor(key: 'brand_primary' | 'brand_primary_content', value: string) {
    const hex = value.trim().toLowerCase()
    const colors = {
      brand_primary: primary.value.value,
      brand_primary_content: content.value.value,
      [key]: hex.startsWith('#') ? hex : `#${hex}`,
    }

    if (isHex(colors.brand_primary ?? '') && isHex(colors.brand_primary_content ?? '')) {
      store.editFields(target.value, colors)
    }
  }

  // notes: the saved ones, and the ones the version adds
  const notes = computed(() => [
    ...restaurant.value.notes.map((note) => {
      const noteTarget: Target = {type: 'restaurant-notes', id: note.id}

      return {key: `note:${note.id}`, target: noteTarget, isNew: false,
        field: fieldsFor(noteTarget, liveOf('restaurant-notes', note, restaurant.value), changeOf(store.version!, 'restaurant-notes', note.id))}
    }),
    ...newChanges(store.version!, 'restaurant-notes', restaurant.value.id).map((change) => {
      const noteTarget: Target = {type: 'restaurant-notes', id: null, changeId: change.id}

      return {key: `note:c${change.id}`, target: noteTarget, isNew: true, change, field: fieldsFor(noteTarget, null, change)}
    }),
  ])

  // a new note, added once its text is typed
  const adding = ref(false)
  const newText = ref('')

  function addNote() {
    if (newText.value.trim()) {
      store.add({type: 'restaurant-notes', id: null, parentId: restaurant.value.id}, {
        text: {[store.defaultLocale]: newText.value.trim()},
        is_hidden: false,
      })
    }

    adding.value = false
    newText.value = ''
  }
</script>

<template>
  <TextRow :field="field('name')" :label="t('editor.details.name')"/>

  <PropRow :label="t('editor.details.type')"
           :changed="establishment.changed"
           @revert="establishment.revert()">
    <template #live>{{ typeLabel(establishment.live) }}</template>

    <select class="e-input h-9"
            :class="{'e-changed': establishment.changed}"
            :value="establishment.value"
            :disabled="store.readOnly"
            @change="establishment.set(($event.target as HTMLSelectElement).value, 0)">
      <option :value="type" v-for="type in TYPES" :key="type">{{ typeLabel(type) }}</option>
    </select>
  </PropRow>

  <PropRow :label="t('editor.details.phone')"
           :changed="phone.changed"
           :error="phone.error"
           @revert="phone.revert()">
    <template #live>{{ phone.live || t('admin.version.none') }}</template>

    <input class="e-input h-9"
           type="tel"
           :class="{'e-changed': phone.changed}"
           :value="phone.value ?? ''"
           :disabled="store.readOnly"
           @input="phone.set(($event.target as HTMLInputElement).value.trim() || null)"/>
  </PropRow>

  <TextRow :field="field('address')" :label="t('editor.details.address')"/>

  <PropRow :label="t('editor.sections.brand')"
           :changed="primary.changed || content.changed"
           :error="content.error"
           @revert="store.revert(target, ['brand_primary', 'brand_primary_content'])">
    <template #live>
      <span class="inline-flex items-center gap-1.5 uppercase tabular-nums" v-if="primary.live">
        <span class="size-3.5 rounded-full" :style="{background: primary.live}"/>{{ primary.live }}
        <span class="size-3.5 rounded-full" :style="{background: content.live ?? ''}"/>{{ content.live }}
      </span>
      <span v-else>{{ t('admin.version.none') }}</span>
    </template>

    <span class="flex gap-2">
      <label class="relative flex-1 min-w-0 flex items-center gap-2"
             v-for="key in (['brand_primary', 'brand_primary_content'] as const)" :key="key">
        <span class="size-9 shrink-0 rounded-md shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
              :style="{background: (key === 'brand_primary' ? primary : content).value ?? '#ffffff'}"/>
        <input class="e-input h-9 min-w-0 uppercase tabular-nums"
               type="text"
               maxlength="7"
               :aria-label="t(key === 'brand_primary' ? 'editor.brand.primary' : 'editor.brand.content')"
               :class="{'e-changed': (key === 'brand_primary' ? primary : content).changed}"
               :value="(key === 'brand_primary' ? primary : content).value ?? ''"
               :disabled="store.readOnly"
               @change="setColor(key, ($event.target as HTMLInputElement).value)"/>
      </label>
    </span>
  </PropRow>

  <PhotoStrip :field="media" :max="MAX_PHOTOS"/>

  <p class="px-3 pt-3.5 pb-1.5 e-section">{{ t('editor.sections.notes') }}</p>

  <template v-for="(note, index) in notes" :key="note.key">
    <TextRow :field="note.field('text')"
             :label="t('editor.notes.note', {number: index + 1})"
             :maxlength="120"
             :is-new="note.isNew"/>

    <div class="flex justify-end gap-1 px-3 -mt-px py-1 border-b border-[#f0f0f1]" v-if="!store.readOnly">
      <button type="button"
              class="e-btn h-7 px-2 text-xs"
              @click="note.field('is_hidden').set(!note.field('is_hidden').value, 0)">
        <EyeOff class="size-3.5" v-if="!note.field('is_hidden').value"/>
        <Eye class="size-3.5" v-else/>
        {{ t(note.field('is_hidden').value ? 'editor.notes.show' : 'editor.notes.hide') }}
        <span class="text-[#a16207]" v-if="note.field('is_hidden').changed && !note.isNew">·</span>
      </button>

      <button type="button"
              class="e-btn h-7 px-2 text-xs text-red-700"
              v-if="note.isNew"
              @click="store.remove(note.target.changeId!)">
        <Trash2 class="size-3.5"/>
        {{ t('admin.version.remove') }}
      </button>
    </div>
  </template>

  <div class="px-3 py-2.5" v-if="!store.readOnly">
    <input class="e-input h-9"
           type="text"
           maxlength="120"
           autofocus
           :placeholder="t('admin.version.new_note')"
           v-model="newText"
           v-if="adding"
           @keydown.enter="addNote"
           @blur="addNote"/>

    <button type="button"
            class="inline-flex items-center gap-1.5 text-blue-600 font-semibold rounded e-focus"
            v-else
            @click="adding = true">
      <Plus class="size-4"/>
      {{ t('editor.notes.add') }}
    </button>
  </div>
</template>
