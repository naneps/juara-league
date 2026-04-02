<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'
import type { Team } from '~/types/team.types'

const props = defineProps<{
  team: Team | null
  open?: boolean
}>()
const emit = defineEmits<{
  success: []
  'update:open': [value: boolean]
}>()

const teamStore = useTeamStore()
const toast = useToast()

// Bisa dipakai via v-model:open (dari parent) atau internal (via slot trigger)
const internalOpen = ref(false)
const open = computed({
  get: () => props.open ?? internalOpen.value,
  set: (v) => {
    internalOpen.value = v
    emit('update:open', v)
  }
})

const schema = z.object({
  name: z.string().min(3, 'Nama minimal 3 karakter').max(100),
  description: z.string().max(500).optional(),
  logo_url: z.string().url('URL logo tidak valid').optional().or(z.literal(''))
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({
  name: props.team?.name ?? '',
  description: props.team?.description ?? '',
  logo_url: props.team?.logo_url ?? ''
})

// Sync state kalau prop team berubah (ganti tim yang diedit)
watch(() => props.team, (team) => {
  if (!team) return
  state.name = team.name
  state.description = team.description ?? ''
  state.logo_url = team.logo_url ?? ''
})

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (!props.team) return
  try {
    await teamStore.updateTeam(props.team.id, {
      name: event.data.name,
      description: event.data.description,
      logo_url: event.data.logo_url || undefined
    })

    toast.add({
      title: 'Berhasil',
      description: 'Informasi tim berhasil diperbarui',
      color: 'success'
    })

    emit('success')
    open.value = false
  } catch (error: any) {
    toast.add({
      title: 'Gagal memperbarui',
      description: error.data?.message || 'Terjadi kesalahan',
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    title="Edit Tim"
    description="Perbarui informasi tim Anda"
  >
    <slot />

    <template #body>
      <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
        <UFormField label="Nama Tim" name="name">
          <UInput v-model="state.name" class="w-full" />
        </UFormField>

        <UFormField label="Deskripsi (Opsional)" name="description">
          <UTextarea v-model="state.description" class="w-full" />
        </UFormField>

        <UFormField label="URL Logo (Opsional)" name="logo_url">
          <UInput v-model="state.logo_url" class="w-full" placeholder="https://..." />
        </UFormField>

        <div class="flex justify-end gap-2">
          <UButton label="Batal" color="neutral" variant="subtle" @click="open = false" />
          <UButton
            label="Simpan"
            color="primary"
            type="submit"
            :loading="teamStore.isLoading"
          />
        </div>
      </UForm>
    </template>
  </UModal>
</template>
