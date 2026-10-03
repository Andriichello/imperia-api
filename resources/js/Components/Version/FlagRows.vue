<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Check, Flame, Plus, X} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import PropRow from '@/Components/Version/PropRow.vue'
  import {ALLERGENS, DISH_TAGS, getAllergenLabel} from '@/flags'
  import type {VersionField} from '@/version/fields'
  import {useVersionStore} from '@/stores/version'

  /**
   * Diet tags and allergens of a dish (its flags) from the version's date.
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

  const HOTNESS = ['hotness', 'low-hotness', 'medium-hotness', 'high-hotness', 'extreme-hotness']

  const tags = DISH_TAGS.filter((tag) => tag.key !== 'hotness')

  const flags = computed(() => props.field.value ?? [])
  const live = computed(() => props.field.live ?? [])

  const isDiet = (flag: string) => !ALLERGENS.includes(flag)
  const diet = (values: string[]) => values.filter(isDiet).sort()
  const allergens = (values: string[]) => values.filter((flag) => ALLERGENS.includes(flag)).sort()

  const dietChanged = computed(() => props.isNew || diet(flags.value).join() !== diet(live.value).join())
  const allergensChanged = computed(() => props.isNew || allergens(flags.value).join() !== allergens(live.value).join())

  const spicy = (values: string[]) => values.some((flag) => HOTNESS.includes(flag))

  /** "Vegetarian, Spicy" */
  function dietLabels(values: string[]): string {
    const labels = [
      ...tags.filter((tag) => values.includes(tag.key)).map((tag) => t(tag.label)),
      ...(spicy(values) ? [t('editor.dish.spicy')] : []),
    ]

    return labels.join(', ') || t('admin.version.none')
  }

  function allergenLabels(values: string[]): string {
    return allergens(values).map((flag) => t(getAllergenLabel(flag))).join(', ') || t('admin.version.none')
  }

  function set(values: string[]) {
    props.field.set([...new Set(values)].sort(), 0)
  }

  function toggle(flag: string) {
    set(flags.value.includes(flag) ? flags.value.filter((other) => other !== flag) : [...flags.value, flag])
  }

  function toggleSpicy() {
    set(spicy(flags.value) ? flags.value.filter((flag) => !HOTNESS.includes(flag)) : [...flags.value, 'medium-hotness'])
  }

  /** Revert one of the two rows: the other one's flags stay as they are. */
  function revert(part: 'diet' | 'allergens') {
    const kept = flags.value.filter((flag) => part === 'diet' ? !isDiet(flag) : isDiet(flag))
    const restored = live.value.filter((flag) => part === 'diet' ? isDiet(flag) : !isDiet(flag))

    set([...kept, ...restored])
  }
</script>

<template>
  <PropRow :label="t('editor.dish.diet')"
           :changed="dietChanged"
           :revertable="!isNew"
           @revert="revert('diet')">
    <template #live>{{ isNew ? '—' : dietLabels(live) }}</template>

    <div class="flex flex-wrap gap-1.5">
      <button type="button"
              class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
              :class="flags.includes(tag.key)
                ? 'border-zinc-900 bg-zinc-900 text-white'
                : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
              :aria-pressed="flags.includes(tag.key)"
              :disabled="store.readOnly"
              v-for="tag in tags" :key="tag.key"
              @click="toggle(tag.key)">
        <Check class="size-3.5" v-if="flags.includes(tag.key)"/>
        <component :is="tag.icon" class="size-3.5" v-else/>
        {{ t(tag.label) }}
      </button>

      <button type="button"
              class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
              :class="spicy(flags)
                ? 'border-zinc-900 bg-zinc-900 text-white'
                : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
              :aria-pressed="spicy(flags)"
              :disabled="store.readOnly"
              @click="toggleSpicy">
        <Check class="size-3.5" v-if="spicy(flags)"/>
        <Flame class="size-3.5" v-else/>
        {{ t('editor.dish.spicy') }}
      </button>
    </div>
  </PropRow>

  <PropRow :label="t('editor.dish.allergens')"
           :changed="allergensChanged"
           :error="field.error"
           :revertable="!isNew"
           @revert="revert('allergens')">
    <template #live>{{ isNew ? '—' : allergenLabels(live) }}</template>

    <div class="flex flex-wrap items-center gap-1.5">
      <span class="h-8 inline-flex items-center gap-1 pl-2.5 pr-1 rounded-full border border-[#ca3500] bg-[#fbefeb] text-[#ca3500] text-[13px] font-semibold"
            v-for="flag in allergens(flags)" :key="flag">
        {{ t(getAllergenLabel(flag)) }}
        <button type="button"
                class="size-6 flex items-center justify-center rounded-full hover:bg-[#ca3500]/10 e-focus"
                :aria-label="t('admin.version.remove_allergen', {name: t(getAllergenLabel(flag))})"
                :disabled="store.readOnly"
                @click="toggle(flag)">
          <X class="size-3.5"/>
        </button>
      </span>

      <DropdownMenu v-if="!store.readOnly && allergens(flags).length < ALLERGENS.length">
        <template #trigger="{open, toggle: toggleMenu}">
          <button type="button"
                  class="h-8 inline-flex items-center gap-1 px-2.5 rounded-full border border-zinc-300 bg-white text-zinc-700 text-[13px] font-semibold hover:bg-zinc-50 e-focus"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  @click="toggleMenu">
            <Plus class="size-3.5"/>
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
