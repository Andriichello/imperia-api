<script setup lang="ts">
  import {computed, nextTick, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Eye, EyeOff, GripVertical, Plus, Trash2} from 'lucide-vue-next'
  import {VueDraggable} from 'vue-draggable-plus'
  import {updateEditorRestaurantNotes} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import GripHandle from '@/Components/Editor/Fields/GripHandle.vue'
  import {moveItem} from '@/editor/lists'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {NoteDraft, notesOf, notesPreview, notesRequest} from '@/editor/drafts'
  import {translationsOf} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Short messages under the restaurant's name: in their order, hidden ones are kept
   * for later. A click on a note edits it.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  /** The longest note, and the most of them. */
  const MAX_LENGTH = 120
  const MAX_NOTES = 20

  const restaurant = computed(() => editor.restaurant!)

  const {draft, dirty, saving, failed, discard, save, error} = usePanelDraft({
    saved: () => notesOf(restaurant.value),
    save: async (notes) => (await updateEditorRestaurantNotes(restaurant.value.id, notesRequest(notes))).data.data,
    preview: (notes, locale) => notesPreview(notes, locale, editor.defaultLocale),
    canSave: () => canSave.value,
    lists: {notes: (notes) => notes.map((note) => note.key)},
  })

  const {locale, languages, placeholder, textError} = useContentLocale(
    () => draft.value.map((note) => note.text)
  )

  // every note has its text in the default language
  const canSave = computed(() => draft.value.every((note) => !!note.text[editor.defaultLocale]?.trim()))

  // the note being edited, and its text before
  const editing = ref<string | null>(null)
  let textBefore = ''
  let added = false
  let newNotes = 0

  function edit(note: NoteDraft, isNew: boolean = false) {
    editing.value = note.key
    textBefore = note.text[locale.value]
    added = isNew

    nextTick(() => {
      const field = document.getElementById(`note-${note.key}`) as HTMLTextAreaElement | null

      field?.focus()
      field?.setSelectionRange(field.value.length, field.value.length)
      grow(field)
    })
  }

  /** The text area is as tall as its text. */
  function grow(field: HTMLTextAreaElement | null) {
    if (field) {
      field.style.height = 'auto'
      field.style.height = `${field.scrollHeight}px`
    }
  }

  function isEmpty(note: NoteDraft): boolean {
    return Object.values(note.text).every((text) => !text.trim())
  }

  function remove(note: NoteDraft) {
    draft.value = draft.value.filter((item) => item.key !== note.key)
  }

  function finish(note: NoteDraft) {
    if (editing.value !== note.key) {
      return
    }

    editing.value = null

    // a new note, which wasn't written
    if (isEmpty(note)) {
      remove(note)
    }
  }

  function cancel(note: NoteDraft) {
    note.text[locale.value] = textBefore
    editing.value = null

    if (added && isEmpty(note)) {
      remove(note)
    }
  }

  function onKeydown(event: KeyboardEvent, note: NoteDraft) {
    if (event.key === 'Enter') {
      event.preventDefault()
      finish(note)
    }

    if (event.key === 'Escape') {
      // the note is cancelled, the panel stays
      event.preventDefault()
      cancel(note)
    }
  }

  function add() {
    const note: NoteDraft = {
      key: `new-${++newNotes}`,
      id: null,
      text: translationsOf(null, editor.locales),
      is_hidden: false,
    }

    draft.value = [...draft.value, note]
    edit(draft.value[draft.value.length - 1], true)
  }

  function indexOf(note: NoteDraft): number {
    return draft.value.findIndex((item) => item.key === note.key)
  }
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.notes')"
              :subtitle="t('editor.subtitles.notes')"
              :languages="languages"
              v-model:locale="locale"
              :dirty="dirty"
              :saving="saving"
              :failed="failed"
              :can-save="canSave"
              @navigate="editor.close()"
              @close="editor.close()"
              @discard="editing = null; discard()"
              @save="editing = null; save()">
    <section class="flex flex-col gap-2">
      <FieldLabel :label="t('editor.notes.label')"
                  :locale="locale">
        <span class="e-help ml-auto" v-if="draft.length > 1">{{ t('editor.notes.order') }}</span>
      </FieldLabel>

      <p class="py-2 text-[13px] text-zinc-500"
         v-if="!draft.length">
        {{ t('editor.notes.empty') }}
      </p>

      <VueDraggable class="flex flex-col gap-2"
                    v-model="draft"
                    handle=".e-grip"
                    ghost-class="e-drag-ghost"
                    :animation="150">
        <div class="flex flex-col gap-1"
             v-for="note in draft" :key="note.key">
          <!-- edited -->
          <div class="flex items-start gap-1 pt-1 pr-2 pb-2 pl-0.5 border border-blue-600 rounded-lg bg-white shadow-[0_0_0_3px_rgba(37,99,235,0.15)]"
               v-if="editing === note.key">
            <span class="e-grip mt-0.5" aria-hidden="true">
              <GripVertical class="size-4"/>
            </span>

            <div class="flex-1 min-w-0 flex flex-col gap-1">
              <label class="sr-only" :for="`note-${note.key}`">
                {{ t('editor.notes.note', {number: indexOf(note) + 1}) }}
              </label>

              <textarea class="w-full pt-1.5 border-0 outline-none resize-none bg-transparent text-sm/5"
                        rows="1"
                        :id="`note-${note.key}`"
                        :maxlength="MAX_LENGTH"
                        :placeholder="placeholder(note.text)"
                        v-model="note.text[locale]"
                        @input="grow($event.target as HTMLTextAreaElement)"
                        @keydown="onKeydown($event, note)"
                        @blur="finish(note)"/>

              <div class="e-help flex justify-between">
                <span>{{ t('editor.notes.edit_help') }}</span>
                <span class="tabular-nums">{{ note.text[locale].length }} / {{ MAX_LENGTH }}</span>
              </div>
            </div>
          </div>

          <!-- shown -->
          <div class="flex items-center gap-1 py-1 pr-1 pl-0.5 border rounded-lg"
               :class="note.is_hidden ? 'border-dashed border-zinc-300 bg-zinc-50' : 'border-zinc-200 bg-white'"
               v-else>
            <GripHandle :name="note.text[locale] || placeholder(note.text)"
                        :index="indexOf(note)"
                        :count="draft.length"
                        @move="(from, to) => draft = moveItem(draft, from, to)"/>

            <button type="button"
                    class="flex-1 min-w-0 py-1.5 text-start text-sm/5 rounded e-focus"
                    :class="{'text-zinc-400': note.is_hidden}"
                    :title="t('editor.notes.click_to_edit')"
                    @click="edit(note)">
              <template v-if="note.text[locale]">{{ note.text[locale] }}</template>
              <span class="italic text-zinc-400" v-else>
                {{ placeholder(note.text) || t('editor.notes.no_text') }}
              </span>
            </button>

            <span class="e-pill bg-zinc-100 text-zinc-500"
                  v-if="note.is_hidden">
              {{ t('editor.notes.hidden') }}
            </span>

            <button type="button"
                    class="e-icon-btn"
                    :aria-label="t(note.is_hidden ? 'editor.notes.show' : 'editor.notes.hide')"
                    :title="t(note.is_hidden ? 'editor.notes.show' : 'editor.notes.hide')"
                    @click="note.is_hidden = !note.is_hidden">
              <EyeOff class="size-4" v-if="note.is_hidden"/>
              <Eye class="size-4" v-else/>
            </button>

            <button type="button"
                    class="e-icon-btn"
                    :aria-label="t('editor.notes.delete')"
                    :title="t('editor.notes.delete')"
                    @click="remove(note)">
              <Trash2 class="size-4"/>
            </button>
          </div>

          <p class="e-error px-1"
             v-if="textError(error, `notes.${note.key}.text`)">
            {{ textError(error, `notes.${note.key}.text`) }}
          </p>
        </div>
      </VueDraggable>

      <p class="e-error" v-if="error('notes')">{{ error('notes') }}</p>

      <button type="button"
              class="h-11 flex items-center justify-center gap-1.5 border border-dashed border-zinc-400 rounded-lg text-zinc-700 font-semibold hover:bg-zinc-50 e-focus"
              v-if="draft.length < MAX_NOTES"
              @click="add">
        <Plus class="size-4"/>
        {{ t('editor.notes.add') }}
      </button>
    </section>

    <InfoBox>{{ t('editor.notes.info') }}</InfoBox>
  </PanelShell>
</template>
