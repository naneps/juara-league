<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

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
  name: '',
  description: '',
  logo_url: ''
})

async function onSubmit(event: FormSubmitEvent<Schema>) {
  try {
    await teamStore.createTeam({
      name: event.data.name,
      description: event.data.description,
      logo_url: event.data.logo_url || undefined
    })

    toast.add({
      title: t('teams.add_modal.success_title'),
      description: t('teams.add_modal.success_desc', { name: event.data.name }),
      color: 'success'
    })

    emit('success')
    open.value = false
    Object.assign(state, { name: '', description: '', logo_url: '' })
  } catch (error: any) {
    toast.add({
      title: t('teams.add_modal.failed_title'),
      description: error.data?.message || t('common.error'),
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="$t('teams.add_modal.title')"
    :description="$t('teams.add_modal.desc')"
  >
    <UButton :label="$t('teams.add_modal.title')" icon="i-lucide-plus" />

    <template #body>
      <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
        <UFormField :label="$t('teams.add_modal.name_label')" name="name">
          <UInput v-model="state.name" class="w-full" :placeholder="$t('teams.add_modal.name_placeholder')" />
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
            :label="$t('teams.add_modal.submit')"
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
