<script setup lang="ts">
import type { Row } from '@tanstack/table-core'
import type { Team } from '~/types/team.types'

const props = defineProps<{ team: Team }>()
const emit = defineEmits(['success'])
const teamStore = useTeamStore()
const toast = useToast()
const { t } = useI18n()
const open = ref(false)

const count = computed(() => props.rows?.length || 0)

async function onConfirm() {
  if (!props.rows?.length) return

  try {
    await Promise.all(props.rows.map(row => teamStore.deleteTeam(row.original.id)))

    toast.add({
      title: t('teams.delete_modal.success_title'),
      description: t('teams.delete_modal.success_desc'),
      color: 'success'
    })

    emit('success')
    open.value = false
  } catch (error: any) {
    toast.add({
      title: t('teams.delete_modal.failed_title'),
      description: error.data?.message || t('common.error'),
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="$t('teams.delete_modal.title')"
    :description="$t('teams.delete_modal.desc', { name: team.name })"
  >
    <UButton
      variant="ghost"
      color="error"
      icon="i-lucide-trash-2"
      size="xs"
      square
    />Anda akan menghapus <strong>{{ count }} tim</strong>. Hanya tim yang Anda pimpin
        sebagai kapten yang dapat dihapus.
      <template #footer>
      <div class="flex justify-end gap-2">
        <UButton :label="$t('teams.add_modal.cancel')" color="neutral" variant="subtle" @click="open = false" />
        <UButton
          :label="$t('teams.delete_modal.confirm')"
          color="error"
          variant="solid"
          :loading="teamStore.isLoading"
          @click="onDelete"
        />
      </div>
    </template>
  </UModal>
</template>
