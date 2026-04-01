<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

const emit = defineEmits(['success'])
const schema = z.object({
  name: z.string().min(3, 'Nama minimal 3 karakter').max(100),
  description: z.string().max(500).optional(),
  logo_url: z.string().url('URL logo tidak valid').optional().or(z.literal(''))
})
const open = ref(false)

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  name: '',
  description: '',
  logo_url: ''
})

const toast = useToast()
const loading = ref(false)

async function onSubmit(event: FormSubmitEvent<Schema>) {
  loading.value = true
  try {
    await useApi('/api/v1/teams', {
      method: 'POST',
      body: event.data
    })

    toast.add({
      title: 'Berhasil',
      description: `Tim ${event.data.name} berhasil ditambahkan`,
      color: 'success'
    })

    emit('success')
    open.value = false
    state.name = ''
    state.description = ''
    state.logo_url = ''
  } catch (error: any) {
    toast.add({
      title: 'Gagal',
      description: error.data?.message || 'Terjadi kesalahan saat membuat tim',
      color: 'error'
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UModal v-model:open="open" title="Tim Baru" description="Tambah tim baru ke dalam kompetisi">
    <UButton label="Tim Baru" icon="i-lucide-plus" />

    <template #body>
      <UForm
        :schema="schema"
        :state="state"
        class="space-y-4"
        @submit="onSubmit"
      >
        <UFormField label="Nama Tim" placeholder="Misal: Garuda Esports" name="name">
          <UInput v-model="state.name" class="w-full" />
        </UFormField>
        <UFormField label="Deskripsi (Opsional)" placeholder="Jelaskan sedikit tentang tim Anda" name="description">
          <UTextarea v-model="state.description" class="w-full" />
        </UFormField>
        <UFormField label="URL Logo (Opsional)" placeholder="https://..." name="logo_url">
          <UInput v-model="state.logo_url" class="w-full" />
        </UFormField>
        <div class="flex justify-end gap-2">
          <UButton
            label="Batal"
            color="neutral"
            variant="subtle"
            @click="open = false"
          />
          <UButton
            label="Buat Tim"
            color="primary"
            variant="solid"
            type="submit"
            :loading="loading"
          />
        </div>
      </UForm>
    </template>
  </UModal>
</template>
