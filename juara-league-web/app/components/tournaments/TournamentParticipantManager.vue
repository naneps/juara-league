<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Participant } from '~/types/tournament'

const props = defineProps<{
  tournamentSlug: string
  initialParticipants?: Participant[]
  participantType?: string
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

const isManualModalOpen = ref(false)
const manualForm = reactive({
  name: '',
  email: '',
  phone: '',
  team_name: ''
})
const isManualSubmitting = ref(false)
const toast = useToast()

const handleManualSubmit = async () => {
  if (!manualForm.email && !manualForm.phone) {
    toast.add({ title: 'Kekurangan Data', description: 'Wajib mengisi Email atau Nomor Telepon.', color: 'warning' })
    return
  }

  isManualSubmitting.value = true
  try {
    await useApi(`/api/v1/tournaments/${props.tournamentSlug}/participants/manual`, {
      method: 'POST',
      body: manualForm
    })
    toast.add({ title: 'Berhasil', description: 'Peserta manual berhasil ditambahkan.', color: 'success' })
    isManualModalOpen.value = false
    await fetchParticipants()
    
    // reset form
    manualForm.name = ''
    manualForm.email = ''
    manualForm.phone = ''
    manualForm.team_name = ''
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal mendaftar manual', color: 'error' })
  } finally {
    isManualSubmitting.value = false
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
      <div class="flex items-center gap-2">
        <UButton size="xs" color="primary" variant="subtle" icon="i-lucide-plus" @click="isManualModalOpen = true">
          Tambah Manual
        </UButton>
        <UButton size="xs" color="neutral" variant="ghost" icon="i-lucide-refresh-cw" @click="fetchParticipants">
          Refresh
        </UButton>
      </div>
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
                <div class="flex flex-col">
                  <span class="font-bold text-neutral-900 dark:text-white leading-tight">
                    {{ participant.team ? participant.team.name : participant.user?.name || '—' }}
                  </span>
                  <span class="text-[10px] text-neutral-500 font-medium">
                    {{ participant.team ? 'Kapt: ' + participant.user?.name : (participant.user?.email || participant.user?.phone || 'No Contact Data') }}
                  </span>
                </div>
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
                <template v-else-if="participant.status === 'approved' || participant.status === 'paid'">
                  <UTooltip text="Diskualifikasi / Tolak">
                    <UButton
                      color="error"
                      variant="ghost"
                      icon="i-lucide-user-minus"
                      size="xs"
                      :loading="isSubmitting"
                      @click="updateStatus(participant.id, 'rejected')"
                    />
                  </UTooltip>
                </template>
                <template v-else-if="participant.status === 'rejected'">
                  <UTooltip text="Pulihkan (Approve)">
                    <UButton
                      color="success"
                      variant="ghost"
                      icon="i-lucide-user-check"
                      size="xs"
                      :loading="isSubmitting"
                      @click="updateStatus(participant.id, 'approved')"
                    />
                  </UTooltip>
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

  <!-- Manual Registration Modal -->
  <UModal v-model:open="isManualModalOpen" :ui="{ content: 'sm:max-w-md' }">
    <template #content>
      <div class="flex items-center gap-4 p-6 border-b border-white/5 bg-neutral-900/50">
        <div class="size-12 rounded-xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20 shadow-inner">
          <UIcon name="i-lucide-user-plus" class="text-indigo-400 size-6" />
        </div>
        <div>
          <h3 class="text-lg font-black text-white uppercase tracking-widest leading-tight">Tambah Peserta</h3>
          <p class="text-xs text-neutral-400 mt-1">Auto-create data via dashboard organizer.</p>
        </div>
      </div>

      <div class="p-6 bg-neutral-900">
        <UForm :state="manualForm" @submit="handleManualSubmit" class="space-y-5">
          <UFormField v-if="props.participantType === 'team'" label="Nama Tim" name="team_name" description="Wajib untuk turnamen berbasis tim.">
            <UInput v-model="manualForm.team_name" placeholder="Misal: Evos Legends" size="lg" autofocus class="w-full" />
          </UFormField>

          <UFormField :label="props.participantType === 'team' ? 'Nama Kapten' : 'Nama Lengkap'" name="name" description="Wajib diisi untuk ditampilkan di braket.">
            <UInput v-model="manualForm.name" placeholder="Misal: Budi Santoso" size="lg" :autofocus="props.participantType !== 'team'" class="w-full" />
          </UFormField>

          <div class="flex flex-col sm:flex-row gap-5">
            <UFormField label="Email" name="email" description="Opsional jika nomor HP diisi." class="flex-1">
              <UInput v-model="manualForm.email" type="email" placeholder="contoh@mail.com" size="lg" class="w-full" />
            </UFormField>
            
            <UFormField label="No. Handphone" name="phone" description="Opsional jika email diisi." class="flex-1">
              <UInput v-model="manualForm.phone" placeholder="08123xxxx" size="lg" class="w-full" />
            </UFormField>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-white/5 mt-4">
            <UButton color="neutral" variant="ghost" @click="isManualModalOpen = false" :disabled="isManualSubmitting" size="lg">Batal</UButton>
            <UButton type="submit" color="indigo" class="font-bold px-6 tracking-wide" :loading="isManualSubmitting" size="lg">
              Tambahkan
            </UButton>
          </div>
        </UForm>
      </div>
    </template>
  </UModal>
</template>
