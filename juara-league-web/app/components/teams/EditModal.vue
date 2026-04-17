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
const props = defineProps<{ team: Team }>()
const emit = defineEmits(['success'])
const teamStore = useTeamStore()
const toast = useToast()
const { t } = useI18n()
const open = ref(false)

const schema = z.object({
  name: z.string().min(3, t('teams.add_modal.error_name_min')).max(100),
  description: z.string().max(500).optional(),
  logo_url: z.string().url(t('teams.add_modal.error_logo_url')).optional().or(z.literal(''))
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
      title: t('teams.edit_modal.success_title'),
      description: t('teams.edit_modal.success_desc'),
      color: 'success'
    })

    emit('success')
    open.value = false
  } catch (error: any) {
    toast.add({
      title: t('teams.edit_modal.failed_title'),
      description: error.data?.message || t('common.error'),
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="$t('teams.edit_modal.title')"
    :description="$t('teams.edit_modal.desc')"
  >
    <UButton
      variant="ghost"
      color="neutral"
      icon="i-lucide-pencil"
      size="xs"
      square
    />

    <template #body>
      <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
        <UFormField :label="$t('teams.add_modal.name_label')" name="name">
          <UInput v-model="state.name" class="w-full" />
        </UFormField>

        <UFormField :label="$t('teams.add_modal.desc_label')" name="description">
          <UTextarea
            v-model="state.description"
            class="w-full"
            :placeholder="$t('teams.add_modal.desc_placeholder')"
          />
        </UFormField>

        <UFormField :label="$t('teams.add_modal.logo_label')" name="logo_url">
          <UInput v-model="state.logo_url" class="w-full" :placeholder="$t('teams.add_modal.logo_placeholder')" />
        </UFormField>

        <div class="flex justify-end gap-2">
          <UButton :label="$t('teams.add_modal.cancel')" color="neutral" variant="subtle" @click="open = false" />
          <UButton
            :label="$t('teams.edit_modal.submit')"
            color="primary"
            variant="solid"
            type="submit"
            :loading="teamStore.isLoading"
          />
        </div>
      </UForm>
    </template>
  </UModal>
</template>
