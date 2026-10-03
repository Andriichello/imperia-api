<script setup lang="ts">
  import {computed, onMounted, PropType, ref, watchEffect} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {CircleCheck, Mail, TriangleAlert} from 'lucide-vue-next'
  import AuthLayout from '@/Components/Admin/AuthLayout.vue'
  import PasswordField from '@/Components/Admin/PasswordField.vue'

  /**
   * Signing in (see `SignInController`): a plain form, posted to the server, which sends
   * it back here with the reason, when it fails.
   */
  interface SignInProps {
    email: string
    remember: boolean
    error: { code: 'credentials' | 'throttled' | 'not_allowed' | 'invalid', seconds?: number } | null
    status: 'password_reset' | null
    urls: { submit: string, forgot: string }
  }

  const props = defineProps({
    props: {type: Object as PropType<SignInProps>, required: true},
  })

  const {t} = useI18n()

  const email = ref(props.props.email)
  const password = ref('')
  const remember = ref(props.props.remember)
  const submitting = ref(false)

  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''

  const error = computed(() => {
    const error = props.props.error

    if (!error) {
      return null
    }

    return error.code === 'throttled'
      ? t('admin.sign_in.errors.throttled', error.seconds ?? 60)
      : t('admin.sign_in.errors.' + error.code)
  })

  // the fields are marked, when they don't match
  const invalid = computed(() => ['credentials', 'invalid'].includes(props.props.error?.code ?? ''))

  const forgotUrl = computed(() => props.props.urls.forgot
    + (email.value ? '?email=' + encodeURIComponent(email.value) : ''))

  watchEffect(() => {
    document.title = t('admin.sign_in.title')
  })

  // straight to the password, when the email is known
  onMounted(() => document.getElementById(email.value ? 'sign-in-password' : 'sign-in-email')?.focus())
</script>

<template>
  <AuthLayout>
    <div class="flex flex-col gap-1.5">
      <h1 class="text-[28px]/9 font-bold tracking-[-0.01em]">{{ t('admin.sign_in.title') }}</h1>
      <p class="text-[15px]/[22px] text-zinc-600">{{ t('admin.sign_in.subtitle') }}</p>
    </div>

    <div class="flex gap-2.5 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-[13px]/[18px]"
         role="alert"
         v-if="error">
      <TriangleAlert class="size-4 shrink-0 mt-px"/>
      <p>{{ error }}</p>
    </div>

    <div class="flex gap-2.5 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-800 text-[13px]/[18px]"
         role="status"
         v-else-if="props.props.status === 'password_reset'">
      <CircleCheck class="size-4 shrink-0 mt-px"/>
      <p>{{ t('admin.sign_in.password_reset') }}</p>
    </div>

    <form class="flex flex-col gap-[18px]"
          method="post"
          :action="props.props.urls.submit"
          @submit="submitting = true">
      <input type="hidden" name="_token" :value="csrfToken"/>

      <div class="flex flex-col gap-1.5">
        <label class="e-label" for="sign-in-email">{{ t('admin.sign_in.email') }}</label>

        <div class="relative">
          <Mail class="absolute left-3 top-3.5 size-4 text-zinc-400 pointer-events-none" aria-hidden="true"/>
          <input class="e-input h-11! pl-[38px]! text-[15px]!"
                 id="sign-in-email"
                 name="email"
                 type="email"
                 autocomplete="username"
                 required
                 :aria-invalid="invalid"
                 v-model="email"/>
        </div>
      </div>

      <div class="flex flex-col gap-1.5">
        <div class="flex items-center">
          <label class="e-label" for="sign-in-password">{{ t('admin.sign_in.password') }}</label>
          <a class="ml-auto e-link" :href="forgotUrl">{{ t('admin.sign_in.forgot') }}</a>
        </div>

        <PasswordField id="sign-in-password"
                       name="password"
                       :invalid="invalid"
                       v-model="password"/>
      </div>

      <label class="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
        <input type="hidden" name="remember" value="0"/>
        <input class="size-4 m-0 accent-zinc-900"
               type="checkbox"
               name="remember"
               value="1"
               v-model="remember"/>
        {{ t('admin.sign_in.remember') }}
      </label>

      <button type="submit"
              class="e-btn e-btn-primary h-11! text-[15px]!"
              :disabled="submitting">
        {{ t('admin.sign_in.submit') }}
      </button>

      <p class="text-[13px]/[18px] text-zinc-500 text-center">{{ t('admin.sign_in.no_account') }}</p>
    </form>
  </AuthLayout>
</template>
