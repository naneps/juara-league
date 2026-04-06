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

const getStatusColor = (status: string) => {
  switch (status) {
    case 'approved': return 'success'
    case 'rejected': return 'error'
    case 'pending': return 'warning'
    case 'paid': return 'primary'
    default: return 'neutral'
  }
}
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-black text-white uppercase tracking-tight">Manajemen Peserta</h2>
      <div class="flex items-center gap-2">
        <UBadge color="neutral" variant="outline" class="rounded-lg text-[10px] font-black uppercase tracking-widest px-3 py-1">
          Total: {{ participants.length }}
        </UBadge>
      </div>
    </div>

    <!-- Participants Table -->
    <div v-if="participants.length > 0" class="overflow-hidden bg-neutral-900/40 rounded-[2rem] border border-white/5 p-4">
      <table class="w-full text-left">
        <thead>
          <tr class="border-b border-white/5">
            <th class="px-6 py-4 text-[10px] font-black text-neutral-500 uppercase tracking-widest">Partisipan</th>
            <th class="px-6 py-4 text-[10px] font-black text-neutral-500 uppercase tracking-widest">Status</th>
            <th class="px-6 py-4 text-[10px] font-black text-neutral-500 uppercase tracking-widest">Pendaftaran</th>
            <th class="px-6 py-4 text-right text-[10px] font-black text-neutral-500 uppercase tracking-widest">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="participant in participants" :key="participant.id" class="group hover:bg-white/[0.02] transition-colors">
            <td class="px-6 py-6">
              <div class="flex items-center gap-4">
                <UAvatar 
                  :src="participant.user?.avatar || participant.team?.logo_url || `https://i.pravatar.cc/150?u=${participant.id}`" 
                  size="md" 
                  class="rounded-xl ring-2 ring-white/5"
                />
                <div>
                  <p class="text-white font-black leading-none mb-1">{{ participant.user?.name || participant.team?.name }}</p>
                  <span class="text-[10px] text-neutral-500 font-bold uppercase tracking-wider">{{ participant.team ? 'Team' : 'Solo Player' }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-6">
              <UBadge :color="getStatusColor(participant.status)" variant="soft" class="rounded-lg text-[10px] font-black uppercase tracking-widest px-3 py-1">
                {{ participant.status }}
              </UBadge>
            </td>
            <td class="px-6 py-6">
              <span class="text-neutral-400 font-medium text-xs">{{ new Date(participant.created_at).toLocaleDateString() }}</span>
            </td>
            <td class="px-6 py-6 text-right">
              <div class="flex items-center justify-end gap-2 px-6">
                <!-- Approve Button -->
                <UButton 
                  v-if="participant.status === 'pending'"
                  color="success" 
                  icon="i-lucide-check" 
                  size="xs" 
                  class="rounded-lg" 
                  variant="soft"
                  @click="updateStatus(participant.id, 'approved')"
                />
                <!-- Reject Button -->
                <UButton 
                  v-if="participant.status === 'pending'"
                  color="error" 
                  icon="i-lucide-x" 
                  size="xs" 
                  class="rounded-lg" 
                  variant="soft"
                  @click="updateStatus(participant.id, 'rejected')"
                />
                <!-- View Proof Button if applicable (dummy for now) -->
                <UButton 
                  v-if="participant.payment_proof_url"
                  color="primary" 
                  icon="i-lucide-image" 
                  size="xs" 
                  class="rounded-lg" 
                  variant="ghost"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20 bg-neutral-900/20 rounded-[3rem] border border-dashed border-white/10">
      <div class="bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-white/5 inline-block mx-auto">
        <UIcon name="i-lucide-users" class="size-16 text-neutral-800" />
      </div>
      <h3 class="text-xl font-black text-white mb-2 uppercase tracking-tight">Belum Ada Pendaftar</h3>
      <p class="text-neutral-600 font-bold uppercase tracking-widest text-[10px]">Turnamen ini sedang menunggu tim pertama!</p>
    </div>
  </div>
</template>
