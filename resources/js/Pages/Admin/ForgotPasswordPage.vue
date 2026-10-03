<script setup lang="ts">
  import {computed, onMounted, PropType, ref, watchEffect} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ArrowLeft, CircleCheck, Mail, TriangleAlert} from 'lucide-vue-next'
  import AuthLayout from '@/Components/Admin/AuthLayout.vue'

  /**
   * "Forgot password?" (see `PasswordResetController`): the link is emailed, if the email
   * belongs to someone (the page doesn't tell).
   */
  interface ForgotPasswordProps {
    email: string
    status: 'link_sent' | null
    error: 'throttled' | 'invalid' | null
    urls: { submit: string, sign_in: string }
  }

  const props = defineProps({
    props: {type: Object as PropType<ForgotPasswordProps>, required: true},
  })

  const {t} = useI18n()

  const email = ref(props.props.email)
  const submitting = ref(false)
  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''

  const signInUrl = computed(() => props.props.urls.sign_in
    + (email.value ? '?email=' + encodeURIComponent(email.value) : ''))

  watchEffect(() => {
    document.title = t('admin.forgot.title')
  })

  onMounted(() => document.getElementById('forgot-email')?.focus())
</script>

<template>
  <AuthLayout>
    <div class="flex flex-col gap-1.5">
      <h1 class="text-[28px]/9 font-bold tracking-[-0.01em]">{{ t('admin.forgot.title') }}</h1>
      <p class="text-[15px]/[22px] text-zinc-600">{{ t('admin.forgot.subtitle') }}</p>
    </div>

    <div class="flex gap-2.5 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-[13px]/[18px]"
         role="alert"
         v-if="props.props.error">
      <TriangleAlert class="size-4 shrink-0 mt-px"/>
      <p>{{ t('admin.forgot.' + props.props.error) }}</p>
    </div>

    <div class="flex gap-2.5 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-800 text-[13px]/[18px]"
         role="status"
         v-else-if="props.props.status === 'link_sent'">
      <CircleCheck class="size-4 shrink-0 mt-px"/>
      <p>{{ t('admin.forgot.sent', {email: props.props.email}) }}</p>
    </div>

    <form class="flex flex-col gap-[18px]"
          method="post"
          :action="props.props.urls.submit"
          @submit="submitting = true">
      <input type="hidden" name="_token" :value="csrfToken"/>

      <div class="flex flex-col gap-1.5">
        <label class="e-label" for="forgot-email">{{ t('admin.sign_in.email') }}</label>

        <div class="relative">
          <Mail class="absolute left-3 top-3.5 size-4 text-zinc-400 pointer-events-none" aria-hidden="true"/>
          <input class="e-input h-11! pl-[38px]! text-[15px]!"
                 id="forgot-email"
                 name="email"
                 type="email"
                 autocomplete="username"
                 required
                 :aria-invalid="props.props.error === 'invalid'"
                 v-model="email"/>
        </div>
      </div>

      <button type="submit"
              class="e-btn e-btn-primary h-11! text-[15px]!"
              :disabled="submitting">
        {{ t('admin.forgot.submit') }}
      </button>

      <a class="self-center e-link" :href="signInUrl">
        <ArrowLeft class="size-3.5"/>
        {{ t('admin.forgot.back') }}
      </a>
    </form>
  </AuthLayout>
</template>
