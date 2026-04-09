<script setup lang="ts">
import { format } from 'date-fns'
import { id } from 'date-fns/locale'
import type { TeamInvitation } from '~/types/team.types'

const teamStore = useTeamStore()

const columns = [
  { key: 'team', label: 'Tim' },
  { key: 'status', label: 'Status' },
  { key: 'date', label: 'Tanggal' }
] as any[]

const history = computed(() => teamStore.invitationHistory)

function getStatusColor(status: string) {
  switch (status) {
    case 'accepted': return 'success'
    case 'declined': return 'error'
    default: return 'neutral'
  }
}

function getStatusLabel(status: string) {
  switch (status) {
    case 'accepted': return 'Diterima'
    case 'declined': return 'Ditolak'
    default: return status
  }
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 px-1">
      <UIcon name="i-lucide-history" class="size-5 text-muted" />
      <h2 class="text-lg font-bold text-highlighted">Riwayat Undangan</h2>
    </div>

    <UCard :ui="{ body: 'p-0 sm:p-0' }" class="overflow-hidden">
      <UTable
        :columns="columns"
        :rows="history"
        :loading="teamStore.isLoading"
      >
        <template #team-data="{ row }">
          <div v-if="row" class="flex items-center gap-3">
            <UAvatar
              :src="(row as any).team.logo_url"
              :alt="(row as any).team.name"
              size="sm"
              class="rounded-lg"
            />
            <div class="flex flex-col min-w-0">
              <span class="font-medium text-highlighted truncate">{{ (row as any).team.name }}</span>
              <span class="text-xs text-muted truncate">Kapten: {{ (row as any).team.captain?.name }}</span>
            </div>
          </div>
        </template>

        <template #status-data="{ row }">
          <UBadge
            v-if="row"
            :label="getStatusLabel((row as any).status)"
            :color="getStatusColor((row as any).status)"
            variant="subtle"
            size="sm"
            class="capitalize"
          />
        </template>

        <template #date-data="{ row }">
          <span v-if="row" class="text-sm text-muted">
            {{ format(new Date((row as any).updated_at), 'PPP', { locale: id }) }}
          </span>
        </template>

        <template #empty-state>
          <div class="flex flex-col items-center justify-center py-10 gap-3">
            <UIcon name="i-lucide-scroll-text" class="size-8 text-muted/50" />
            <p class="text-sm text-muted">Belum ada riwayat undangan.</p>
          </div>
        </template>
      </UTable>
    </UCard>
  </div>
</template>
