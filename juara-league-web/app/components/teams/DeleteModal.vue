<script setup lang="ts">
import type { Row } from '@tanstack/table-core'
import type { Team } from '~/types/tournament'

const props = defineProps<{
  rows?: Row<Team>[]
}>()

const emit = defineEmits(['success'])
const count = computed(() => props.rows?.length || 0)
const open = ref(false)
const toast = useToast()
const loading = ref(false)

async function onSubmit() {
  if (!props.rows) return

  loading.value = true
  try {
    const promises = props.rows.map(row =>
      useApi(`/api/v1/teams/${row.original.id}`, { method: 'DELETE' })
    )

    await Promise.all(promises)

    toast.add({
      title: 'Berhasil',
      description: `${count.value} tim berhasil dihapus`,
      color: 'success'
    })

    emit('success')
    open.value = false
  } catch (error: any) {
    toast.add({
      title: 'Gagal',
      description: error.data?.message || 'Gagal menghapus beberapa tim. Pastikan Anda adalah kapten tim.',
      color: 'error'
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    :title="`Hapus ${count} tim`"
    :description="`Apakah Anda yakin? Tindakan ini tidak dapat dibatalkan.`"
  >
    <slot />

    <template #body>
      <div class="flex justify-end gap-2">
        <UButton
          label="Batal"
          color="neutral"
          variant="subtle"
          @click="open = false"
        />
        <UButton
          label="Hapus"
          color="error"
          variant="solid"
          loading-auto
          @click="onSubmit"
        />
      </div>
    </template>
  </UModal>
</template>
