<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

const { user, updateProfile } = useAuth()
const { uploadFile, isUploading } = useUpload()

const fileRef = ref<HTMLInputElement>()

const profileSchema = z.object({
  name: z.string().min(2, 'Terlalu pendek'),
  email: z.string().email('Email tidak valid'),
  username: z.string().min(2, 'Terlalu pendek'),
  avatar: z.string().optional(),
  bio: z.string().optional(),
  phone: z.string().optional()
})

const { t } = useI18n()

type ProfileSchema = z.output<typeof profileSchema>

const profile = reactive<Partial<ProfileSchema>>({
  name: user.value?.name || '',
  email: user.value?.email || '',
  username: user.value?.username || '',
  avatar: user.value?.avatar || undefined,
  bio: user.value?.bio || undefined,
  phone: user.value?.phone || undefined
})

const isLoading = ref(false)
const toast = useToast()

async function onSubmit(event: FormSubmitEvent<ProfileSchema>) {
  isLoading.value = true
  try {
    await updateProfile(event.data)
    toast.add({
      title: t('common.success'),
      description: t('settings.profile.success'),
      icon: 'i-lucide-check',
      color: 'success'
    })
  } catch (error: any) {
    toast.add({
      title: t('common.error'),
      description: error.data?.message || t('settings.profile.failed'),
      icon: 'i-lucide-x',
      color: 'error'
    })
  } finally {
    isLoading.value = false
  }
}

async function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement

  if (!input.files || input.files.length === 0) {
    return
  }

  const file = input.files[0]
  if (!file) return

  const result = await uploadFile(file, 'avatars')
  
  if (result) {
    profile.avatar = result.url
  }
}

function onFileClick() {
  fileRef.value?.click()
}
</script>

<template>
  <UForm
    id="settings"
    :schema="profileSchema"
    :state="profile"
    @submit="onSubmit"
  >
    <UPageCard
      :title="$t('settings.profile.title')"
      :description="$t('settings.profile.desc')"
      variant="naked"
      orientation="horizontal"
      class="mb-4"
    >
      <UButton
        form="settings"
        :label="$t('common.save')"
        color="neutral"
        type="submit"
        class="w-fit lg:ms-auto font-bold"
        :loading="isLoading"
      />
    </UPageCard>

    <UPageCard variant="subtle">
      <UFormField
        name="name"
        :label="$t('settings.profile.name_label')"
        :description="$t('settings.profile.name_desc')"
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.name"
          autocomplete="off"
        />
      </UFormField>
      <USeparator />
      <UFormField
        name="email"
        :label="$t('settings.profile.email_label')"
        :description="$t('settings.profile.email_desc')"
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.email"
          type="email"
          autocomplete="off"
        />
      </UFormField>
      <USeparator />
      <UFormField
        name="username"
        :label="$t('settings.profile.username_label')"
        :description="$t('settings.profile.username_desc')"
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.username"
          type="username"
          autocomplete="off"
        />
      </UFormField>
      <USeparator />
      <UFormField
        name="avatar"
        :label="$t('settings.profile.avatar_label')"
        :description="$t('settings.profile.avatar_desc')"
        class="flex max-sm:flex-col justify-between sm:items-center gap-4"
      >
        <div class="flex flex-wrap items-center gap-3">
          <UAvatar
            :src="profile.avatar"
            :alt="profile.name"
            size="lg"
          />
          <UButton
            label="Choose"
            color="neutral"
            @click="onFileClick"
          />
          <input
            ref="fileRef"
            type="file"
            class="hidden"
            accept=".jpg, .jpeg, .png, .gif"
            @change="onFileChange"
          >
        </div>
      </UFormField>
      <USeparator />
      <UFormField
        name="bio"
        :label="$t('settings.profile.bio_label')"
        :description="$t('settings.profile.bio_desc')"
        class="flex max-sm:flex-col justify-between items-start gap-4"
        :ui="{ container: 'w-full' }"
      >
        <UTextarea
          v-model="profile.bio"
          :rows="5"
          autoresize
          class="w-full"
        />
      </UFormField>
      <USeparator />
      <UFormField
        name="phone"
        :label="$t('settings.profile.phone_label')"
        :description="$t('settings.profile.phone_desc')"
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.phone"
          type="tel"
          autocomplete="off"
          :placeholder="$t('settings.profile.phone_placeholder')"
        />
      </UFormField>
    </UPageCard>
  </UForm>
</template>
