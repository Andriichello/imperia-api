<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {LogOut} from 'lucide-vue-next'
  import AuthLayout from '@/Components/Admin/AuthLayout.vue'

  /**
   * The admin's home for staff, who can't edit a restaurant (see `DashboardController`): they're
   * told so, and can sign out.
   */
  const props = defineProps({
    props: {
      type: Object as PropType<{ user: { name: string, email: string }, urls: { logout: string } }>,
      required: true,
    },
  })

  const {t} = useI18n()

  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
</script>

<template>
  <AuthLayout>
    <div class="flex flex-col gap-1.5">
      <h1 class="text-[28px]/9 font-bold tracking-[-0.01em]">{{ t('admin.no_restaurant.title') }}</h1>
      <p class="text-[15px]/[22px] text-zinc-600">{{ t('admin.no_restaurant.help', {email: props.props.user.email}) }}</p>
    </div>

    <form method="post" :action="props.props.urls.logout">
      <input type="hidden" name="_token" :value="csrfToken"/>

      <button type="submit" class="w-full e-btn e-btn-primary h-11! text-[15px]!">
        <LogOut class="size-4"/>
        {{ t('admin.nav.sign_out') }}
      </button>
    </form>
  </AuthLayout>
</template>
