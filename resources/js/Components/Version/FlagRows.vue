<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Plus, X} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import PropRow from '@/Components/Version/PropRow.vue'
  import {ALLERGENS, getAllergenLabel, getAllergens, getDishTags, HOTNESS, TAG_GROUPS, tagsOf, withTag} from '@/flags'
  import type {VersionField} from '@/version/fields'
  import {useVersionStore} from '@/stores/version'

  /**
   * Tags and allergens of a dish (its flags) from the version's date: the picked ones as chips,
   * which are removed with their ×, and the others added from a menu.
   */
  const props = defineProps({
    field: {
      type: Object as PropType<VersionField<string[] | null>>,
      required: true,
    },
    isNew: {
      type: Boolean,
      default: false,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  const flags = computed(() => props.field.value ?? [])
  const live = computed(() => props.field.live ?? [])

  const isTag = (flag: string) => !ALLERGENS.includes(flag)
  const tagFlags = (values: string[]) => values.filter(isTag).sort()

  const tagsChanged = computed(() => props.isNew || tagFlags(flags.value).join() !== tagFlags(live.value).join())
  const allergensChanged = computed(() => props.isNew || getAllergens(flags.value).join() !== getAllergens(live.value).join())

  const tags = computed(() => getDishTags(flags.value))

  // the tags, which can be added, by their groups (another level of hotness replaces the picked one)
  const addable = computed(() => TAG_GROUPS
    .map((group) => ({group, options: tagsOf(group).filter((tag) => !flags.value.includes(tag.key))}))
    .filter(({options}) => options.length))

  /** "Vegetarian, Low calorie" */
  function tagLabels(values: string[]): string {
    return getDishTags(values).map((tag) => t(tag.label)).join(', ') || t('admin.version.none')
  }

  function allergenLabels(values: string[]): string {
    return getAllergens(values).map((flag) => t(getAllergenLabel(flag))).join(', ') || t('admin.version.none')
  }

  function set(values: string[]) {
    props.field.set([...new Set(values)].sort(), 0)
  }

  function toggle(flag: string) {
    set(flags.value.includes(flag) ? flags.value.filter((other) => other !== flag) : [...flags.value, flag])
  }

  /** Remove a tag: the one of hotness (the hottest is shown) takes all of its flags with it. */
  function removeTag(key: string) {
    set(flags.value.filter((flag) => flag !== key && !(HOTNESS.includes(key) && HOTNESS.includes(flag))))
  }

  /** Revert one of the two rows: the other one's flags stay as they are. */
  function revert(part: 'tags' | 'allergens') {
    const kept = flags.value.filter((flag) => part === 'tags' ? !isTag(flag) : isTag(flag))
    const restored = live.value.filter((flag) => part === 'tags' ? isTag(flag) : !isTag(flag))

    set([...kept, ...restored])
  }
</script>

<template>
  <PropRow :label="t('editor.dish.tags')"
           :changed="tagsChanged"
           :revertable="!isNew"
           @revert="revert('tags')">
    <template #live>{{ isNew ? '—' : tagLabels(live) }}</template>

    <div class="flex flex-wrap items-center gap-1.5">
      <span class="h-7 inline-flex items-center gap-1.5 pl-2.5 pr-1 rounded-full border border-zinc-900 bg-zinc-900 text-white text-[13px] font-semibold"
            v-for="tag in tags" :key="tag.key">
        <component :is="tag.icon" class="size-[13px] shrink-0"/>
        {{ t(tag.label) }}
        <button type="button"
                class="size-5 flex items-center justify-center rounded-full hover:bg-white/15 e-focus"
                :aria-label="t('admin.version.remove_tag', {name: t(tag.label)})"
                :disabled="store.readOnly"
                @click="removeTag(tag.key)">
          <X class="size-3"/>
        </button>
      </span>

      <DropdownMenu v-if="!store.readOnly && addable.length">
        <template #trigger="{open, toggle: toggleMenu}">
          <button type="button"
                  class="h-7 inline-flex items-center gap-1 px-2.5 rounded-full border border-zinc-300 bg-white text-zinc-700 text-[13px] font-semibold hover:bg-zinc-50 e-focus"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  @click="toggleMenu">
            <Plus class="size-3"/>
            {{ t('admin.version.add') }}
          </button>
        </template>

        <div role="group"
             :aria-label="t(`editor.dish.tag_groups.${group}`)"
             v-for="{group, options} in addable" :key="group">
          <p class="e-section px-2.5 pt-2 pb-1" aria-hidden="true">{{ t(`editor.dish.tag_groups.${group}`) }}</p>

          <button type="button"
                  class="e-dropdown-item"
                  role="menuitem"
                  v-for="tag in options" :key="tag.key"
                  @click="set(withTag(flags, tag.key))">
            <component :is="tag.icon" class="size-4 text-zinc-500"/>
            {{ t(tag.label) }}
          </button>
        </div>
      </DropdownMenu>
    </div>
  </PropRow>

  <PropRow :label="t('editor.dish.allergens')"
           :changed="allergensChanged"
           :error="field.error"
           :revertable="!isNew"
           @revert="revert('allergens')">
    <template #live>{{ isNew ? '—' : allergenLabels(live) }}</template>

    <div class="flex flex-wrap items-center gap-1.5">
      <span class="h-7 inline-flex items-center gap-1 pl-2.5 pr-1 rounded-full border border-[#ca3500] bg-[#fbefeb] text-[#ca3500] text-[13px] font-semibold"
            v-for="flag in getAllergens(flags)" :key="flag">
        {{ t(getAllergenLabel(flag)) }}
        <button type="button"
                class="size-5 flex items-center justify-center rounded-full hover:bg-[#ca3500]/10 e-focus"
                :aria-label="t('admin.version.remove_allergen', {name: t(getAllergenLabel(flag))})"
                :disabled="store.readOnly"
                @click="toggle(flag)">
          <X class="size-3"/>
        </button>
      </span>

      <DropdownMenu v-if="!store.readOnly && getAllergens(flags).length < ALLERGENS.length">
        <template #trigger="{open, toggle: toggleMenu}">
          <button type="button"
                  class="h-7 inline-flex items-center gap-1 px-2.5 rounded-full border border-zinc-300 bg-white text-zinc-700 text-[13px] font-semibold hover:bg-zinc-50 e-focus"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  @click="toggleMenu">
            <Plus class="size-3"/>
            {{ t('admin.version.add') }}
          </button>
        </template>

        <button type="button"
                class="e-dropdown-item"
                role="menuitem"
                v-for="flag in ALLERGENS.filter((other) => !flags.includes(other))" :key="flag"
                @click="toggle(flag)">
          {{ t(getAllergenLabel(flag)) }}
        </button>
      </DropdownMenu>
    </div>
  </PropRow>
</template>
