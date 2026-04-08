<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Participant } from '~/types/tournament'

const props = defineProps<{
  tournamentSlug: string
  initialParticipants?: Participant[]
}>()

const tournamentStore = useTournamentStore()
const participants = ref<Participant[]>(props.initialParticipants || [])
const isSubmitting = ref(false)

const fetchParticipants = async () => {
  try {
    const data = await tournamentStore.fetchParticipants(props.tournamentSlug)
    participants.value = data
  } catch (e) {
    console.error('Failed to fetch participants', e)
  }
}

const updateStatus = async (id: number, status: string) => {
  isSubmitting.value = true
  try {
    await tournamentStore.updateParticipantStatus(id, status)
    await fetchParticipants()
  } catch (e) {
    console.error('Failed to update participant status', e)
  } finally {
    isSubmitting.value = false
  }
}

const statusBadge = (status: string): { color: 'success' | 'error' | 'warning' | 'primary' | 'neutral'; label: string } => {
  switch (status) {
    case 'approved': return { color: 'success', label: 'Disetujui' }
    case 'rejected': return { color: 'error',   label: 'Ditolak' }
    case 'paid':     return { color: 'primary',  label: 'Lunas' }
    default:         return { color: 'warning',  label: 'Menunggu' }
  }
}

const pendingCount = computed(() => participants.value.filter(p => p.status === 'pending').length)

onMounted(() => {
  fetchParticipants()
})
</script>

<template>
  <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden">

    <!-- Header row -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 dark:border-neutral-800">
      <div class="flex items-center gap-3">
        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Peserta</h2>
        <UBadge v-if="participants.length > 0" color="neutral" variant="subtle" size="xs">
          {{ participants.length }}
        </UBadge>
        <UBadge v-if="pendingCount > 0" color="warning" variant="subtle" size="xs">
          {{ pendingCount }} menunggu
        </UBadge>
      </div>
      <UButton size="xs" color="neutral" variant="ghost" icon="i-lucide-refresh-cw" @click="fetchParticipants">
        Refresh
      </UButton>
    </div>

    <!-- Table -->
    <div v-if="participants.length > 0" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-neutral-100 dark:border-neutral-800">
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Peserta</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Tipe</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">Daftar</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
          <tr
            v-for="participant in participants"
            :key="participant.id"
            class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors"
          >
            <!-- Name -->
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <UAvatar
                  :src="participant.user?.avatar || participant.team?.logo_url || `https://i.pravatar.cc/80?u=${participant.id}`"
                  size="sm"
                  class="rounded-lg"
                />
                <span class="font-medium text-neutral-900 dark:text-white">
                  {{ participant.user?.name || participant.team?.name || '—' }}
                </span>
              </div>
            </td>

            <!-- Type -->
            <td class="px-6 py-4">
              <span class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ participant.team ? 'Tim' : 'Individu' }}
              </span>
            </td>

            <!-- Status -->
            <td class="px-6 py-4">
              <UBadge :color="statusBadge(participant.status).color" variant="subtle" size="sm">
                {{ statusBadge(participant.status).label }}
              </UBadge>
            </td>

            <!-- Date -->
            <td class="px-6 py-4">
              <span class="text-xs text-neutral-400 dark:text-neutral-500">
                {{ new Date(participant.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}
              </span>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4">
              <div class="flex items-center justify-end gap-1.5">
                <template v-if="participant.status === 'pending'">
                  <UButton
                    color="success"
                    variant="subtle"
                    icon="i-lucide-check"
                    size="xs"
                    :loading="isSubmitting"
                    @click="updateStatus(participant.id, 'approved')"
                  />
                  <UButton
                    color="error"
                    variant="subtle"
                    icon="i-lucide-x"
                    size="xs"
                    :loading="isSubmitting"
                    @click="updateStatus(participant.id, 'rejected')"
                  />
                </template>
                <UButton
                  v-if="participant.payment_proof_url"
                  color="neutral"
                  variant="ghost"
                  icon="i-lucide-image"
                  size="xs"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div v-else class="py-16 text-center">
      <UIcon name="i-lucide-users" class="size-10 text-neutral-300 dark:text-neutral-700 mx-auto mb-3" />
      <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">Belum ada peserta</p>
      <p class="text-xs text-neutral-400 dark:text-neutral-500">Pendaftar akan muncul di sini setelah turnamen dipublish.</p>
    </div>

  </div>
</template>
