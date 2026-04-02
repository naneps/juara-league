<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

const props = defineProps<{ teamId: number }>()
const emit = defineEmits(['success'])

const teamStore = useTeamStore()
const toast = useToast()
const open = ref(false)

const schema = z.object({
  email: z.string().email('Masukkan email yang valid')
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({ email: '' })

async function onSubmit(event: FormSubmitEvent<Schema>) {
  try {
    await teamStore.inviteMember(props.teamId, event.data.email)

    toast.add({
      title: 'Undangan terkirim',
      description: `Undangan dikirim ke ${event.data.email}`,
      color: 'success'
    })

    emit('success')
    open.value = false
    state.email = ''
  } catch (error: any) {
    toast.add({
      title: 'Gagal mengirim undangan',
      description: error.data?.message || 'Terjadi kesalahan',
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    title="Undang Anggota"
    description="Kirim undangan bergabung ke tim via email"
  >
    <slot />

    <template #body>
      <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
        <UFormField label="Email Anggota" name="email">
          <UInput
            v-model="state.email"
            class="w-full"
            type="email"
            placeholder="contoh@email.com"
            icon="i-lucide-mail"
          />
        </UFormField>

        <div class="flex justify-end gap-2">
          <UButton label="Batal" color="neutral" variant="subtle" @click="open = false" />
          <UButton
            label="Kirim Undangan"
            color="primary"
            type="submit"
            icon="i-lucide-send"
            :loading="teamStore.isLoading"
          />
        </div>
      </UForm>
    </template>
  </UModal>
</template>
