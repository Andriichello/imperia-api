<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ArrowRight, Eye, EyeOff, Undo2} from 'lucide-vue-next'
  import PropRow from '@/Components/Version/PropRow.vue'
  import TextRow from '@/Components/Version/TextRow.vue'
  import ToggleSwitch from '@/Components/Editor/Fields/ToggleSwitch.vue'
  import {priceFormatted} from '@/helpers'
  import {fieldsFor} from '@/version/fields'
  import {liveOf, TreeNode} from '@/version/model'
  import {numberInput, SizeRow, sizeRows, weightLabel} from '@/version/sizes'
  import {useVersionStore} from '@/stores/version'

  /**
   * A category on the version's page: its own fields, and the prices of all its dishes in one
   * table (Tab moves to the next price, a dish's name opens it).
   */
  const props = defineProps({
    node: {
      type: Object as PropType<TreeNode>,
      required: true,
    },
    dateLabel: {
      type: String,
      required: true,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  const live = computed(() => props.node.item ? liveOf('dish-categories', props.node.item, store.restaurant!) : null)
  const field = computed(() => fieldsFor(props.node.target!, live.value, props.node.change))
  const hidden = computed(() => field.value<boolean>('is_hidden'))

  const currency = computed(() => (store.restaurant?.currency ?? 'uah').toLowerCase())
  const symbol = computed(() => t(`currency_symbol.${currency.value}`))
  const price = (value: unknown) => value === null || value === undefined ? '—' : (priceFormatted(Number(value), currency.value) ?? '')

  // each dish with its sizes
  const dishes = computed(() => props.node.children.map((dish) => ({dish, sizes: sizeRows(dish)})))

  const changed = (row: SizeRow) => row.changed('price') || row.changed('is_hidden')
</script>

<template>
  <PropRow :label="t('admin.version.on_the_menu')"
           :changed="hidden.changed"
           :revertable="!node.isNew"
           @revert="hidden.revert()">
    <template #live>{{ t(hidden.live ? 'admin.version.hidden' : 'admin.version.shown') }}</template>

    <span class="inline-flex items-center gap-2">
      <ToggleSwitch :model-value="!hidden.value"
                    :label="t('admin.version.on_the_menu')"
                    :disabled="store.readOnly"
                    @update:model-value="hidden.set(!$event, 0)"/>
      <span class="text-[13px] text-zinc-700">{{ t(hidden.value ? 'admin.version.hidden' : 'admin.version.shown') }}</span>
    </span>
  </PropRow>

  <TextRow :field="field('title')" :label="t('editor.category.name')"/>
  <TextRow :field="field('description')" :label="t('editor.category.description')" multiline :maxlength="1000"/>

  <p class="flex items-center gap-2.5 px-3 pt-3.5 pb-1.5">
    <span class="e-section">{{ t('admin.version.prices') }}</span>
    <span class="e-help">{{ t('admin.version.tab_hint') }}</span>
  </p>

  <div class="grid grid-cols-[minmax(0,1.2fr)_96px_minmax(0,0.7fr)_20px_120px_64px] items-center gap-x-3 px-3 py-2 border-b border-zinc-200 text-xs/4 font-semibold text-zinc-500">
    <span>{{ t('admin.version.dish') }}</span>
    <span>{{ t('admin.version.size') }}</span>
    <span>{{ t('admin.version.now') }}</span>
    <span/>
    <span class="text-blue-700">{{ dateLabel }}</span>
    <span class="text-end">{{ t('admin.version.visible') }}</span>
  </div>

  <p class="px-3 py-4 text-[13px] text-zinc-500" v-if="!dishes.length">{{ t('editor.category.no_dishes') }}</p>

  <template v-for="{dish, sizes} in dishes" :key="dish.key">
    <div class="grid grid-cols-[minmax(0,1.2fr)_96px_minmax(0,0.7fr)_20px_120px_64px] items-center gap-x-3 px-3 py-1.5 border-b border-[#f0f0f1]"
         :class="{'bg-[#fffbeb] shadow-[inset_3px_0_0_#f59e0b]': changed(row) || (index === 0 && dish.isNew)}"
         v-for="(row, index) in sizes" :key="row.key">
      <span class="min-w-0">
        <button type="button"
                class="max-w-full inline-flex items-center gap-1.5 font-semibold text-start rounded e-focus"
                v-if="index === 0"
                @click="store.select(dish.key)">
          <span class="truncate" :class="{'text-zinc-400': dish.archived}">{{ dish.name || t('editor.dish.new') }}</span>
          <span class="e-pill e-pill-sm bg-green-100 text-green-800" v-if="dish.isNew">{{ t('admin.version.new') }}</span>
        </button>
      </span>

      <span class="text-[13px] tabular-nums">
        <template v-if="row.live && (row.changed('weight') || row.changed('weight_unit'))">
          <s class="text-zinc-400">{{ row.live.weight }}</s> → {{ weightLabel(row.values) }}
        </template>
        <template v-else>{{ weightLabel(row.values) || '—' }}</template>
      </span>

      <span class="text-[13px] tabular-nums text-zinc-600">
        <span :class="{'line-through decoration-amber-500': row.live && row.changed('price')}">{{ row.live ? price(row.live.price) : '—' }}</span>
      </span>

      <ArrowRight class="size-4" :class="changed(row) || dish.isNew ? 'text-amber-500' : 'text-zinc-300'"/>

      <span class="relative">
        <input class="e-input h-[34px] pl-2 pr-5 text-end text-[13px] tabular-nums"
               type="text"
               inputmode="decimal"
               :aria-label="t('admin.version.price_of', {dish: dish.name, size: weightLabel(row.values)})"
               :class="{'e-changed': row.changed('price') && !row.isNew}"
               :value="row.values.price ?? ''"
               :disabled="store.readOnly || !!row.values.archived"
               @input="row.set('price', numberInput(($event.target as HTMLInputElement).value))"/>
        <span class="absolute right-2 top-2 text-xs text-zinc-500">{{ symbol }}</span>
      </span>

      <span class="flex justify-end gap-0.5">
        <button type="button"
                class="e-icon-btn size-7"
                :aria-pressed="!!row.values.is_hidden"
                :aria-label="t(row.values.is_hidden ? 'editor.dish.show_size' : 'editor.dish.hide_size', {size: weightLabel(row.values)})"
                :title="t(row.values.is_hidden ? 'editor.dish.show_size' : 'editor.dish.hide_size', {size: weightLabel(row.values)})"
                :disabled="store.readOnly"
                @click="row.set('is_hidden', !row.values.is_hidden, 0)">
          <EyeOff class="size-4" v-if="row.values.is_hidden"/>
          <Eye class="size-4" v-else/>
        </button>

        <button type="button"
                class="e-icon-btn size-7"
                :aria-label="t('admin.version.revert_field', {field: `${dish.name} ${weightLabel(row.values)}`})"
                :title="t('admin.version.revert')"
                v-if="row.revert && changed(row) && !store.readOnly"
                @click="row.revert(['price', 'is_hidden'].filter((name) => row.changed(name as never)) as never)">
          <Undo2 class="size-4"/>
        </button>
      </span>
    </div>
  </template>
</template>
