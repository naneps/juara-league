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
      title: 'Berhasil',
      description: 'Profil Anda telah diperbarui.',
      icon: 'i-lucide-check',
      color: 'success'
    })
  } catch (error: any) {
    toast.add({
      title: 'Gagal',
      description: error.data?.message || 'Gagal memperbarui profil.',
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
      title="Profile"
      description="These informations will be displayed publicly."
      variant="naked"
      orientation="horizontal"
      class="mb-4"
    >
      <UButton
        form="settings"
        label="Simpan Perubahan"
        color="neutral"
        type="submit"
        class="w-fit lg:ms-auto font-bold"
        :loading="isLoading"
      />
    </UPageCard>

    <UPageCard variant="subtle">
      <UFormField
        name="name"
        label="Name"
        description="Will appear on receipts, invoices, and other communication."
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
        label="Email"
        description="Used to sign in, for email receipts and product updates."
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
        label="Username"
        description="Your unique username for logging in and your profile URL."
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
        label="Avatar"
        description="JPG, GIF or PNG. 1MB Max."
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
        label="Bio"
        description="Brief description for your profile. URLs are hyperlinked."
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
        label="Nomor Telepon"
        description="Nomor WhatsApp/Telepon untuk koordinasi turnamen."
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.phone"
          type="tel"
          autocomplete="off"
          placeholder="Contoh: 08123456789"
        />
      </UFormField>
    </UPageCard>
  </UForm>
</template>
