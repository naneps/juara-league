<script setup lang="ts">
import type { Row } from '@tanstack/table-core'
import type { Team } from '~/types/team.types'

const props = defineProps<{
  rows?: Row<Team>[]
}>()

const emit = defineEmits(['success'])
const teamStore = useTeamStore()
const toast = useToast()
const open = ref(false)

const count = computed(() => props.rows?.length || 0)

async function onConfirm() {
  if (!props.rows?.length) return

  try {
    await Promise.all(props.rows.map(row => teamStore.deleteTeam(row.original.id)))

    toast.add({
      title: 'Berhasil',
      description: `${count.value} tim berhasil dihapus`,
      color: 'success'
    })

    emit('success')
    open.value = false
  } catch (error: any) {
    toast.add({
      title: 'Gagal menghapus',
      description: error.data?.message || 'Pastikan Anda adalah kapten dari tim tersebut',
      color: 'error'
    })
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="`Hapus ${count} Tim`"
    description="Tindakan ini tidak dapat dibatalkan."
  >
    <slot />

    <template #body>
      <p class="text-sm text-muted mb-4">
        Anda akan menghapus <strong>{{ count }} tim</strong>. Hanya tim yang Anda pimpin
        sebagai kapten yang dapat dihapus.
      </p>
      <div class="flex justify-end gap-2">
        <UButton label="Batal" color="neutral" variant="subtle" @click="open = false" />
        <UButton
          label="Hapus"
          color="error"
          variant="solid"
          :loading="teamStore.isLoading"
          @click="onConfirm"
        />
      </div>
    </template>
  </UModal>
</template>
