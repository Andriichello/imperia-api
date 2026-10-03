<script setup lang="ts">
  import {computed, onMounted, PropType, ref, watchEffect} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ArrowLeft, TriangleAlert} from 'lucide-vue-next'
  import AuthLayout from '@/Components/Admin/AuthLayout.vue'
  import PasswordField from '@/Components/Admin/PasswordField.vue'

  /**
   * A new password, set with the link from the email (see `PasswordResetController`).
   */
  interface ResetPasswordProps {
    token: string
    email: string
    error: 'invalid_link' | null
    // fields with problems, e.g. "password"
    fields: string[]
    urls: { submit: string, forgot: string, sign_in: string }
  }

  const props = defineProps({
    props: {type: Object as PropType<ResetPasswordProps>, required: true},
  })

  const {t} = useI18n()

  const password = ref('')
  const confirmation = ref('')
  const submitting = ref(false)
  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''

  const passwordInvalid = computed(() => props.props.fields.includes('password'))

  const error = computed(() => {
    if (props.props.error) {
      return t('admin.reset.' + props.props.error)
    }

    return passwordInvalid.value ? t('admin.reset.password_error') : null
  })

  const forgotUrl = computed(() => props.props.urls.forgot
    + (props.props.email ? '?email=' + encodeURIComponent(props.props.email) : ''))

  watchEffect(() => {
    document.title = t('admin.reset.title')
  })

  onMounted(() => document.getElementById('reset-password')?.focus())
</script>

<template>
  <AuthLayout>
    <div class="flex flex-col gap-1.5">
      <h1 class="text-[28px]/9 font-bold tracking-[-0.01em]">{{ t('admin.reset.title') }}</h1>
      <p class="text-[15px]/[22px] text-zinc-600">{{ t('admin.reset.subtitle', {email: props.props.email}) }}</p>
    </div>

    <div class="flex gap-2.5 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-[13px]/[18px]"
         role="alert"
         v-if="error">
      <TriangleAlert class="size-4 shrink-0 mt-px"/>
      <div class="flex flex-col gap-1">
        <p>{{ error }}</p>
        <a class="font-semibold underline" :href="forgotUrl" v-if="props.props.error === 'invalid_link'">
          {{ t('admin.reset.ask_again') }}
        </a>
      </div>
    </div>

    <form class="flex flex-col gap-[18px]"
          method="post"
          :action="props.props.urls.submit"
          @submit="submitting = true">
      <input type="hidden" name="_token" :value="csrfToken"/>
      <input type="hidden" name="token" :value="props.props.token"/>
      <input type="hidden" name="email" :value="props.props.email"/>
      <!-- for password managers -->
      <input type="email" class="hidden" autocomplete="username" :value="props.props.email" readonly/>

      <div class="flex flex-col gap-1.5">
        <label class="e-label" for="reset-password">{{ t('admin.reset.password') }}</label>
        <PasswordField id="reset-password"
                       name="password"
                       autocomplete="new-password"
                       :minlength="8"
                       :invalid="passwordInvalid"
                       v-model="password"/>
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="e-label" for="reset-password-confirmation">{{ t('admin.reset.confirmation') }}</label>
        <PasswordField id="reset-password-confirmation"
                       name="password_confirmation"
                       autocomplete="new-password"
                       :minlength="8"
                       :invalid="passwordInvalid"
                       v-model="confirmation"/>
      </div>

      <button type="submit"
              class="e-btn e-btn-primary h-11! text-[15px]!"
              :disabled="submitting">
        {{ t('admin.reset.submit') }}
      </button>

      <a class="self-center e-link" :href="props.props.urls.sign_in">
        <ArrowLeft class="size-3.5"/>
        {{ t('admin.forgot.back') }}
      </a>
    </form>
  </AuthLayout>
</template>
