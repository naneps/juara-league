<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

const { updatePassword } = useAuth()
const { t } = useI18n()

const passwordSchema = z.object({
  current: z.string().min(8, t('settings.security.error_min_length')),
  new: z.string().min(8, t('settings.security.error_min_length'))
})

type PasswordSchema = z.output<typeof passwordSchema>

const password = reactive<Partial<PasswordSchema>>({
  current: '',
  new: ''
})

const isLoading = ref(false)
const toast = useToast()

const validate = (state: Partial<PasswordSchema>): FormError[] => {
  const errors: FormError[] = []
  if (state.current && state.new && state.current === state.new) {
    errors.push({ name: 'new', message: t('settings.security.error_must_be_different') })
  }
  return errors
}

async function onSubmit(event: FormSubmitEvent<PasswordSchema>) {
  isLoading.value = true
  try {
    await updatePassword({
      current_password: event.data.current,
      new_password: event.data.new
    })
    
    toast.add({
      title: t('common.success'),
      description: t('settings.security.success'),
      icon: 'i-lucide-check',
      color: 'success'
    })
    
    // Clear form
    password.current = ''
    password.new = ''
  } catch (error: any) {
    toast.add({
      title: t('common.error'),
      description: error.data?.message || t('settings.security.failed'),
      icon: 'i-lucide-x',
      color: 'error'
    })
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <UPageCard
    :title="$t('settings.security.title')"
    :description="$t('settings.security.desc')"
    variant="subtle"
  >
    <UForm
      :schema="passwordSchema"
      :state="password"
      :validate="validate"
      class="flex flex-col gap-4 max-w-sm"
      @submit="onSubmit"
    >
      <UFormField name="current" :label="$t('settings.security.current_password_label')">
        <UInput
          v-model="password.current"
          type="password"
          :placeholder="$t('settings.security.current_password_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UFormField name="new" :label="$t('settings.security.new_password_label')">
        <UInput
          v-model="password.new"
          type="password"
          :placeholder="$t('settings.security.new_password_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UButton 
        :label="$t('settings.security.update_button')" 
        class="w-fit font-bold" 
        type="submit" 
        :loading="isLoading"
      />
    </UForm>
  </UPageCard>

  <UPageCard
    :title="$t('settings.security.delete_account_title')"
    :description="$t('settings.security.delete_account_desc')"
    class="bg-gradient-to-tl from-error/10 from-5% to-default"
  >
    <template #footer>
      <UButton :label="$t('settings.security.delete_account_button')" color="error" variant="ghost" />
    </template>
  </UPageCard>
</template>
