<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {
    Archive,
    CalendarClock,
    Copy,
    Eye,
    EyeOff,
    MessageSquare,
    MoreHorizontal,
    Pause,
    Pencil,
    Play,
    Plus,
    Tag,
    Trash2,
  } from 'lucide-vue-next'
  import {
    activateEditorVersion,
    applyEditorVersion,
    deactivateEditorVersion,
    deleteEditorVersion,
    duplicateEditorVersion,
    type EditorVersion,
  } from '@/api'
  import ConfirmDialog from '@/Components/Admin/ConfirmDialog.vue'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import {describeVersion, RowIcon, Tone, VersionRow} from '@/admin/versions'

  /**
   * Versions as rows: changes scheduled on their own by what they change, the others by their
   * names, with when they go live. A row opens the version's page; each can be applied now,
   * deactivated or activated, duplicated and deleted (one, which went live, is only duplicated
   * or deleted).
   */
  const props = defineProps({
    versions: {type: Array as PropType<EditorVersion[]>, required: true},
    currency: {type: String as PropType<string | null>, default: null},
    // of a version: this one and `/{id}`
    versionUrl: {type: String, required: true},
  })

  const emit = defineEmits<{
    // the versions changed: they're loaded again
    changed: []
    notify: [message: string]
  }>()

  const {t, locale} = useI18n()
  const key = 'admin.dashboard.versions.'

  const rows = computed(() => props.versions.map((version) => describeVersion(version, {
    t: t as never,
    locale: locale.value,
    currency: props.currency,
  })))

  const ICONS: Record<RowIcon, unknown> = {
    price: Tag,
    hide: EyeOff,
    show: Eye,
    note: MessageSquare,
    archive: Archive,
    new: Plus,
    edit: Pencil,
    version: CalendarClock,
  }

  const TILES: Record<Tone, string> = {
    neutral: 'bg-zinc-100 text-zinc-700',
    blue: 'bg-blue-50 text-blue-700',
    amber: 'bg-amber-50 text-yellow-700',
    red: 'bg-red-50 text-red-700',
  }

  const STATUS_TONES = {muted: 'text-zinc-500', amber: 'text-yellow-700', red: 'text-red-700'}

  // the action, which waits for a confirmation
  const asking = ref<{ action: 'apply' | 'delete', row: VersionRow } | null>(null)
  const askingOpen = ref(false)
  const busy = ref(false)

  function ask(action: 'apply' | 'delete', row: VersionRow) {
    asking.value = {action, row}
    askingOpen.value = true
  }

  async function run(action: (id: number) => Promise<unknown>, version: EditorVersion, done?: string) {
    busy.value = true

    try {
      await action(version.id)

      if (done) {
        emit('notify', done)
      }
    } catch (error) {
      // a version, which failed, says why
      const reason = (error as { response?: { data?: { data?: EditorVersion } } }).response?.data?.data?.failure_reason

      emit('notify', reason ? t(key + 'apply_failed', {reason}) : t(key + 'error'))
    } finally {
      busy.value = false
      emit('changed')
    }
  }

  function confirmed() {
    const {action, row} = asking.value!

    if (action === 'apply') {
      run(applyEditorVersion, row.version, t(key + 'applied'))
    } else {
      run(deleteEditorVersion, row.version)
    }
  }
</script>

<template>
  <div>
    <ul :aria-busy="busy">
      <li class="flex items-center gap-3 py-2 border-t border-[#f0f0f1]"
          v-for="row in rows" :key="row.version.id">
        <span class="size-8 shrink-0 flex items-center justify-center rounded-lg"
              :class="TILES[row.tone]"
              aria-hidden="true">
          <component :is="ICONS[row.icon]" class="size-4"/>
        </span>

        <a class="flex-1 min-w-0 flex flex-col" :href="`${versionUrl}/${row.version.id}`">
          <span class="flex items-center gap-1.5 whitespace-nowrap overflow-hidden">
            <span class="truncate">
              <b class="font-semibold">{{ row.title }}</b>
              <template v-if="row.from !== null">
                · <s class="text-zinc-400">{{ row.from }}</s> → <b class="font-semibold">{{ row.to }}</b>
              </template>
              <template v-else-if="row.what"> · {{ row.what }}</template>
            </span>
            <span class="e-pill h-5! bg-blue-50 text-blue-700" v-if="row.isVersion">{{ t(key + 'version') }}</span>
          </span>
          <span class="text-xs text-zinc-500 truncate">{{ row.meta }}</span>
        </a>

        <span class="w-[132px] shrink-0 flex flex-col items-end">
          <span class="text-[13px] font-semibold whitespace-nowrap">{{ row.date }}</span>
          <span class="text-xs" :class="STATUS_TONES[row.statusTone]">{{ row.status }}</span>
        </span>

        <DropdownMenu align="end">
          <template #trigger="{open, toggle}">
            <button type="button"
                    class="e-icon-btn"
                    aria-haspopup="menu"
                    :aria-expanded="open"
                    :aria-label="t(key + 'actions', {name: row.name})"
                    :disabled="busy"
                    @click="toggle">
              <MoreHorizontal class="size-4"/>
            </button>
          </template>

          <button type="button"
                  class="e-dropdown-item"
                  role="menuitem"
                  v-if="row.version.status !== 'applied'"
                  @click="ask('apply', row)">
            <Play class="size-4 text-zinc-500"/>
            {{ t(key + 'apply') }}
          </button>
          <button type="button"
                  class="e-dropdown-item"
                  role="menuitem"
                  v-if="row.version.status === 'scheduled'"
                  @click="run(deactivateEditorVersion, row.version)">
            <Pause class="size-4 text-zinc-500"/>
            {{ t(key + 'deactivate') }}
          </button>
          <button type="button"
                  class="e-dropdown-item"
                  role="menuitem"
                  v-if="row.version.status === 'inactive'"
                  @click="run(activateEditorVersion, row.version)">
            <CalendarClock class="size-4 text-zinc-500"/>
            {{ t(key + 'activate') }}
          </button>
          <button type="button" class="e-dropdown-item" role="menuitem" @click="run(duplicateEditorVersion, row.version)">
            <Copy class="size-4 text-zinc-500"/>
            {{ t(key + 'duplicate') }}
          </button>
          <div class="h-px my-1 bg-[#f0f0f1]"/>
          <button type="button" class="e-dropdown-item text-red-700" role="menuitem" @click="ask('delete', row)">
            <Trash2 class="size-4"/>
            {{ t(key + 'delete') }}
          </button>
        </DropdownMenu>
      </li>
    </ul>

    <ConfirmDialog :title="t(key + (asking?.action === 'delete' ? 'confirm_delete' : 'confirm_apply'), {name: asking?.row.name ?? ''})"
                   :message="t(key + (asking?.action === 'delete' ? 'confirm_delete_message' : 'confirm_apply_message'))"
                   :confirm="t(key + (asking?.action === 'delete' ? 'delete' : 'apply'))"
                   :cancel="t(key + 'cancel')"
                   :danger="asking?.action === 'delete'"
                   v-model="askingOpen"
                   @confirmed="confirmed"/>
  </div>
</template>
