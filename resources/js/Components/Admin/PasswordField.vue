<script setup lang="ts">
  import {PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Eye, EyeOff, Lock} from 'lucide-vue-next'

  /**
   * A password input with a lock icon and a button, which shows the password.
   */
  defineProps({
    id: {type: String, required: true},
    name: {type: String, required: true},
    autocomplete: {type: String, default: 'current-password'},
    invalid: {type: Boolean, default: false},
    required: {type: Boolean, default: true},
    minlength: {type: Number as PropType<number | null>, default: null},
    modelValue: {type: String, default: ''},
  })

  defineEmits<{ 'update:modelValue': [value: string] }>()

  const {t} = useI18n()
  const shown = ref(false)
</script>

<template>
  <div class="relative">
    <Lock class="absolute left-3 top-3.5 size-4 text-zinc-400 pointer-events-none" aria-hidden="true"/>

    <input class="e-input h-11! pl-[38px]! pr-11! text-[15px]!"
           :id="id"
           :name="name"
           :type="shown ? 'text' : 'password'"
           :autocomplete="autocomplete"
           :required="required"
           :minlength="minlength ?? undefined"
           :aria-invalid="invalid"
           :value="modelValue"
           @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"/>

    <button type="button"
            class="e-icon-btn absolute right-1.5 top-1.5"
            :aria-label="t(shown ? 'admin.sign_in.hide_password' : 'admin.sign_in.show_password')"
            :aria-pressed="shown"
            @click="shown = !shown">
      <EyeOff class="size-4" v-if="shown"/>
      <Eye class="size-4" v-else/>
    </button>
  </div>
</template>
