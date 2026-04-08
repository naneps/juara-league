<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

const { updatePassword } = useAuth()

const passwordSchema = z.object({
  current: z.string().min(8, 'Minimal 8 karakter'),
  new: z.string().min(8, 'Minimal 8 karakter')
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
    errors.push({ name: 'new', message: 'Password baru harus berbeda dengan password saat ini' })
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
      title: 'Berhasil',
      description: 'Password Anda telah diperbarui.',
      icon: 'i-lucide-check',
      color: 'success'
    })
    
    // Clear form
    password.current = ''
    password.new = ''
  } catch (error: any) {
    toast.add({
      title: 'Gagal',
      description: error.data?.message || 'Gagal memperbarui password.',
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
    title="Keamanan & Password"
    description="Konfirmasi password lama Anda sebelum mengatur password baru."
    variant="subtle"
  >
    <UForm
      :schema="passwordSchema"
      :state="password"
      :validate="validate"
      class="flex flex-col gap-4 max-w-sm"
      @submit="onSubmit"
    >
      <UFormField name="current" label="Password Saat Ini">
        <UInput
          v-model="password.current"
          type="password"
          placeholder="Masukkan password lama"
          class="w-full"
        />
      </UFormField>

      <UFormField name="new" label="Password Baru">
        <UInput
          v-model="password.new"
          type="password"
          placeholder="Masukkan password baru"
          class="w-full"
        />
      </UFormField>

      <UButton 
        label="Perbarui Password" 
        class="w-fit font-bold" 
        type="submit" 
        :loading="isLoading"
      />
    </UForm>
  </UPageCard>

  <UPageCard
    title="Akun"
    description="Sudah tidak ingin menggunakan layanan kami? Anda dapat menghapus akun Anda. Tindakan ini tidak dapat dibatalkan. Semua data terkait akun ini akan dihapus secara permanen."
    class="bg-gradient-to-tl from-error/10 from-5% to-default"
  >
    <template #footer>
      <UButton label="Hapus Akun" color="error" variant="ghost" />
    </template>
  </UPageCard>
</template>
