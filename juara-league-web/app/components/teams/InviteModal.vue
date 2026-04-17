<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

const props = defineProps<{ team: Team }>()
const emit = defineEmits(['success'])

const teamStore = useTeamStore()
const toast = useToast()
const { t } = useI18n()
const open = ref(false)
const selectedUser = ref()

const schema = z.object({
  email: z.string().email('Masukkan email yang valid')
})

type Schema = z.output<typeof schema>

const state = reactive<Partial<Schema>>({ email: '' })

async function onInvite() {
  try {
    await teamStore.inviteMember(props.team.id, selectedUser.value.id)

    toast.add({
      title: t('teams.invite_modal.success_title'),
      description: t('teams.invite_modal.success_desc', { name: selectedUser.value.name }),
      color: 'success'
    })

    emit('success')
    open.value = false
  } catch (error: any) {
    toast.add({
      title: t('teams.invite_modal.failed_title'),
      description: error.data?.message || t('common.error'),
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="$t('teams.invite_modal.title')"
    :description="$t('teams.invite_modal.desc')"
  >
    <UButton
      variant="ghost"
      color="primary"
      icon="i-lucide-user-plus"
      size="xs"
      square
    />

    <template #body>
      <div class="space-y-4">
        <USelectMenu
          v-model="selectedUser"
          :items="[]"
          :placeholder="$t('teams.invite_modal.search_placeholder')"
          class="w-full"
          size="xl"
          icon="i-lucide-search"
        />

        <div class="flex justify-end gap-2">
          <UButton :label="$t('teams.add_modal.cancel')" color="neutral" variant="subtle" @click="open = false" />
          <UButton
            :label="$t('teams.invite_modal.submit')"
            color="primary"
            variant="solid"
            :loading="teamStore.isLoading"
            :disabled="!selectedUser"
            @click="onInvite"
          />
        </div>
      </div>
    </template>
  </UModal>
</template>
