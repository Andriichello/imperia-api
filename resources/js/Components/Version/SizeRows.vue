<script setup lang="ts">
  import {computed, PropType, reactive, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ArrowRight, ArrowUpNarrowWide, Eye, EyeOff, Plus, RotateCcw, Trash2, Undo2} from 'lucide-vue-next'
  import {EditorSizeWeightUnit} from '@/api'
  import {priceFormatted} from '@/helpers'
  import type {TreeNode} from '@/version/model'
  import {numberInput, SIZE_FIELDS, SizeField, SizeRow, sizeRows, weightLabel} from '@/version/sizes'
  import {useVersionStore} from '@/stores/version'

  /**
   * Sizes of a dish on the version's page: each one's live values, and the ones from the version's
   * date (amount, unit, price, time, calories), which can be hidden from that date. New ones are
   * added, once their price is typed.
   */
  const props = defineProps({
    dish: {
      type: Object as PropType<TreeNode>,
      required: true,
    },
  })

  const store = useVersionStore()
  const {t} = useI18n()

  const UNITS = Object.values(EditorSizeWeightUnit).filter(Boolean) as string[]

  const rows = computed<SizeRow[]>(() => sizeRows(props.dish))
  const currency = computed(() => (store.restaurant?.currency ?? 'uah').toLowerCase())
  const symbol = computed(() => t(`currency_symbol.${currency.value}`))
  const price = (value: unknown) => value === null || value === undefined || value === ''
    ? '' : (priceFormatted(Number(value), currency.value) ?? '')

  const text = (value: unknown) => value === null || value === undefined ? '' : String(value)

  /** "15 min · 380 kcal" */
  function details(values: Record<string, unknown> | null): string {
    return [
      values?.preparation_time ? t('badges.time', {minutes: values.preparation_time}) : null,
      values?.calories ? t('badges.calories', {calories: values.calories}) : null,
    ].filter(Boolean).join(' · ')
  }

  const changedFields = (row: SizeRow) => [...SIZE_FIELDS, 'archived' as const].filter((field) => row.changed(field))

  // a new size, which is added once its price is typed
  const adding = ref(false)
  const addingRow = ref<HTMLElement | null>(null)

  // it's added, once the focus leaves its fields
  function onFocusOut(event: FocusEvent) {
    if (!addingRow.value?.contains(event.relatedTarget as Node | null)) {
      add()
    }
  }
  const draft = reactive<Record<string, string>>({weight: '', weight_unit: 'g', price: '', preparation_time: '', calories: ''})

  function startAdding() {
    const last = rows.value[rows.value.length - 1]

    Object.assign(draft, {weight: '', weight_unit: String(last?.values.weight_unit ?? 'g'), price: '', preparation_time: '', calories: ''})
    adding.value = true
  }

  function add() {
    if (!/^\d+([.,]\d+)?$/.test(draft.price.trim())) {
      return
    }

    const values = {
      price: numberInput(draft.price),
      weight: numberInput(draft.weight),
      weight_unit: draft.weight.trim() ? draft.weight_unit : null,
      preparation_time: numberInput(draft.preparation_time),
      calories: numberInput(draft.calories),
      is_hidden: false,
    }

    if (props.dish.isNew) {
      const sizes = rows.value.map((row) => row.values)
      store.edit(props.dish.target!, 'sizes', [...sizes, values], 0)
    } else {
      store.add({type: 'dish-variants', id: null, parentId: props.dish.target!.id}, values)
    }

    adding.value = false
  }

  /** The size is shown from the version's date: a dish keeps one, which guests see. */
  function toggleHidden(row: SizeRow) {
    row.set('is_hidden', !row.values.is_hidden, 0)
  }
</script>

<template>
  <div>
    <div class="grid grid-cols-[136px_minmax(0,0.8fr)_20px_minmax(0,1.3fr)_64px] items-end gap-x-3 px-3 pt-3.5 pb-1 text-[11px]/[14px] font-semibold text-zinc-500">
      <span class="e-section text-[11px]">{{ t('editor.dish.sizes') }}</span>
      <span class="flex items-center gap-1"><ArrowUpNarrowWide class="size-3.5"/>{{ t('admin.version.sorted') }}</span>
      <span/>
      <span class="flex gap-1.5">
        <span class="w-[60px] text-end">{{ t('admin.version.amount') }}</span>
        <span class="w-[58px]">{{ t('admin.version.unit') }}</span>
        <span class="w-[84px] text-end">{{ t('editor.dish.price_label') }}</span>
        <span class="w-[70px] text-end">{{ t('admin.version.time') }}</span>
        <span class="w-[82px] text-end">{{ t('editor.dish.calories') }}</span>
      </span>
      <span class="text-end">{{ t('admin.version.visible') }}</span>
    </div>

    <div class="e-vrow items-center"
         :class="{'e-vrow-changed': changedFields(row).length}"
         v-for="(row, index) in rows" :key="row.key">
      <span class="e-label">{{ t('editor.dish.size', {number: index + 1}) }}</span>

      <div class="min-w-0 text-[13px]/[18px] text-zinc-600">
        <template v-if="row.live">
          <span class="tabular-nums text-zinc-900">
            <span :class="{'line-through decoration-amber-500': row.changed('weight') || row.changed('weight_unit')}">{{ weightLabel(row.live) }}</span>
            <template v-if="weightLabel(row.live)"> · </template>
            <span :class="{'line-through decoration-amber-500': row.changed('price')}">{{ price(row.live.price) }}</span>
          </span>
          <span class="block text-xs text-zinc-500">{{ details(row.live) }}</span>
        </template>
        <span v-else>—</span>
      </div>

      <span class="flex justify-center">
        <ArrowRight class="size-4" :class="changedFields(row).length ? 'text-amber-500' : 'text-zinc-300'"/>
      </span>

      <div class="min-w-0">
        <div class="flex items-center gap-2" v-if="row.values.archived">
          <span class="e-pill bg-red-100 text-red-800">{{ t('admin.version.archived') }}</span>
          <span class="text-[13px] text-zinc-500">{{ t('admin.version.size_archived') }}</span>
          <button type="button"
                  class="e-btn e-btn-secondary h-8 px-2.5"
                  :disabled="store.readOnly"
                  @click="row.live?.archived ? row.set('archived', false, 0) : row.revert?.(['archived'])">
            <RotateCcw class="size-[15px]"/>
            {{ t('editor.actions.restore') }}
          </button>
        </div>

        <template v-else>
          <div class="flex items-center gap-1.5" :class="{'opacity-50': row.values.is_hidden}">
            <input class="e-input h-[34px] w-[60px] px-2 text-end text-[13px] tabular-nums"
                   type="text"
                   inputmode="decimal"
                   :aria-label="t('admin.version.amount')"
                   :class="{'e-changed': row.changed('weight') && !row.isNew}"
                   :value="text(row.values.weight)"
                   :disabled="store.readOnly"
                   @input="row.set('weight', numberInput(($event.target as HTMLInputElement).value))"/>

            <select class="e-input h-[34px] w-[58px] pl-2 pr-5 text-[13px] bg-[position:right_4px_center]"
                    :aria-label="t('admin.version.unit')"
                    :class="{'e-changed': row.changed('weight_unit') && !row.isNew}"
                    :value="row.values.weight_unit ?? 'g'"
                    :disabled="store.readOnly"
                    @change="row.set('weight_unit', ($event.target as HTMLSelectElement).value, 0)">
              <option :value="unit" v-for="unit in UNITS" :key="unit">{{ t(`weight_unit.${unit}`) }}</option>
            </select>

            <span class="relative w-[84px]">
              <input class="e-input h-[34px] pl-2 pr-5 text-end text-[13px] tabular-nums"
                     type="text"
                     inputmode="decimal"
                     :aria-label="t('editor.dish.price_label')"
                     :class="{'e-changed': row.changed('price') && !row.isNew}"
                     :value="text(row.values.price)"
                     :disabled="store.readOnly"
                     @input="row.set('price', numberInput(($event.target as HTMLInputElement).value))"/>
              <span class="absolute right-2 top-2 text-xs text-zinc-500">{{ symbol }}</span>
            </span>

            <span class="relative w-[70px]">
              <input class="e-input h-[34px] pl-2 pr-8 text-end text-[13px] tabular-nums"
                     type="text"
                     inputmode="numeric"
                     :aria-label="t('admin.version.time')"
                     :class="{'e-changed': row.changed('preparation_time') && !row.isNew}"
                     :value="text(row.values.preparation_time)"
                     :disabled="store.readOnly"
                     @input="row.set('preparation_time', numberInput(($event.target as HTMLInputElement).value))"/>
              <span class="absolute right-2 top-2 text-xs text-zinc-500">{{ t('editor.dish.minutes') }}</span>
            </span>

            <span class="relative w-[82px]">
              <input class="e-input h-[34px] pl-2 pr-9 text-end text-[13px] tabular-nums"
                     type="text"
                     inputmode="numeric"
                     :aria-label="t('editor.dish.calories')"
                     :class="{'e-changed': row.changed('calories') && !row.isNew}"
                     :value="text(row.values.calories)"
                     :disabled="store.readOnly"
                     @input="row.set('calories', numberInput(($event.target as HTMLInputElement).value))"/>
              <span class="absolute right-2 top-2 text-xs text-zinc-500">{{ t('editor.dish.kcal') }}</span>
            </span>
          </div>

          <p class="mt-1 e-help" v-if="row.values.is_hidden">{{ t('admin.version.size_hidden') }}</p>
        </template>
      </div>

      <div class="flex justify-end gap-0.5">
        <button type="button"
                class="e-icon-btn size-7"
                :class="{'text-blue-700! ring-1 ring-amber-300 bg-amber-50': row.values.is_hidden}"
                :aria-pressed="!!row.values.is_hidden"
                :aria-label="t(row.values.is_hidden ? 'editor.dish.show_size' : 'editor.dish.hide_size', {size: weightLabel(row.values)})"
                :title="t(row.values.is_hidden ? 'editor.dish.show_size' : 'editor.dish.hide_size', {size: weightLabel(row.values)})"
                :disabled="store.readOnly"
                v-if="!row.values.archived"
                @click="toggleHidden(row)">
          <EyeOff class="size-4" v-if="row.values.is_hidden"/>
          <Eye class="size-4" v-else/>
        </button>

        <button type="button"
                class="e-icon-btn size-7"
                :aria-label="t('admin.version.revert_field', {field: t('editor.dish.size', {number: index + 1})})"
                :title="t('admin.version.revert')"
                v-if="row.revert && changedFields(row).length && !store.readOnly"
                @click="row.revert(changedFields(row))">
          <Undo2 class="size-4"/>
        </button>

        <button type="button"
                class="e-icon-btn size-7"
                :aria-label="t('admin.version.remove_size', {number: index + 1})"
                :title="t('admin.version.remove_size', {number: index + 1})"
                v-if="row.remove && !store.readOnly"
                @click="row.remove()">
          <Trash2 class="size-4"/>
        </button>
      </div>
    </div>

    <!-- a new size, added once its price is typed -->
    <div class="e-vrow items-center e-vrow-changed" v-if="adding">
      <span class="e-label">{{ t('editor.dish.size', {number: rows.length + 1}) }}</span>
      <span class="text-[13px] text-zinc-500">—</span>
      <span class="flex justify-center"><ArrowRight class="size-4 text-amber-500"/></span>
      <div class="flex items-center gap-1.5" ref="addingRow" @focusout="onFocusOut">
        <input class="e-input h-[34px] w-[60px] px-2 text-end text-[13px]" type="text" inputmode="decimal"
               :aria-label="t('admin.version.amount')" v-model="draft.weight"/>
        <select class="e-input h-[34px] w-[58px] pl-2 pr-5 text-[13px] bg-[position:right_4px_center]"
                :aria-label="t('admin.version.unit')" v-model="draft.weight_unit">
          <option :value="unit" v-for="unit in UNITS" :key="unit">{{ t(`weight_unit.${unit}`) }}</option>
        </select>
        <span class="relative w-[84px]">
          <input class="e-input h-[34px] pl-2 pr-5 text-end text-[13px]" type="text" inputmode="decimal"
                 :aria-label="t('editor.dish.price_label')" :placeholder="t('admin.version.price_first')"
                 v-model="draft.price" @keydown.enter="add"/>
          <span class="absolute right-2 top-2 text-xs text-zinc-500">{{ symbol }}</span>
        </span>
        <input class="e-input h-[34px] w-[70px] px-2 text-end text-[13px]" type="text" inputmode="numeric"
               :aria-label="t('admin.version.time')" v-model="draft.preparation_time"/>
        <input class="e-input h-[34px] w-[82px] px-2 text-end text-[13px]" type="text" inputmode="numeric"
               :aria-label="t('editor.dish.calories')" v-model="draft.calories"/>
      </div>
      <div class="flex justify-end">
        <button type="button" class="e-icon-btn size-7" :aria-label="t('editor.confirm.cancel')" @click="adding = false">
          <Trash2 class="size-4"/>
        </button>
      </div>
    </div>

    <div class="px-3 py-2.5 border-b border-[#f0f0f1] grid grid-cols-[136px_minmax(0,0.8fr)_20px_minmax(0,1.3fr)_64px] gap-x-3"
         v-if="!store.readOnly && !adding">
      <span class="col-start-4">
        <button type="button"
                class="inline-flex items-center gap-1.5 text-blue-600 font-semibold rounded e-focus"
                @click="startAdding">
          <Plus class="size-4"/>
          {{ t('editor.dish.add_size') }}
        </button>
      </span>
    </div>
  </div>
</template>
