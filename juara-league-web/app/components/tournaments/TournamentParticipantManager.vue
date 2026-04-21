<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Participant } from '~/types/tournament'

const props = defineProps<{
  tournamentSlug: string
  initialParticipants?: Participant[]
  participantType?: string
}>()

const tournamentStore = useTournamentStore()
const { t, locale } = useI18n()
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
    toast.add({ 
      title: t('managers.manual_modal.toast_missing_title'), 
      description: t('managers.manual_modal.toast_missing_desc'), 
      color: 'warning' 
    })
    return
  }

  isManualSubmitting.value = true
  try {
    await useApi(`/api/v1/tournaments/${props.tournamentSlug}/participants/manual`, {
      method: 'POST',
      body: manualForm
    })
    toast.add({ 
      title: t('common.success'), 
      description: t('managers.manual_modal.toast_success_desc'), 
      color: 'success' 
    })
    isManualModalOpen.value = false
    await fetchParticipants()
    
    // reset form
    manualForm.name = ''
    manualForm.email = ''
    manualForm.phone = ''
    manualForm.team_name = ''
  } catch (e: any) {
    toast.add({ 
      title: t('common.error'), 
      description: e.data?.message || t('managers.manual_modal.toast_failed_desc'), 
      color: 'error' 
    })
  } finally {
    isManualSubmitting.value = false
  }
}

const statusBadge = (status: string): { color: 'success' | 'error' | 'warning' | 'primary' | 'neutral'; label: string } => {
  switch (status) {
    case 'approved': return { color: 'success', label: t('managers.statuses.approved') }
    case 'rejected': return { color: 'error',   label: t('managers.statuses.rejected') }
    case 'paid':     return { color: 'primary',  label: t('managers.statuses.paid') }
    default:         return { color: 'warning',  label: t('managers.statuses.pending') }
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
        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $t('managers.participants_title') }}</h2>
        <UBadge v-if="participants.length > 0" color="neutral" variant="subtle" size="xs">
          {{ participants.length }}
        </UBadge>
        <UBadge v-if="pendingCount > 0" color="warning" variant="subtle" size="xs">
          {{ pendingCount }} {{ $t('managers.waiting') }}
        </UBadge>
      </div>
      <div class="flex items-center gap-2">
        <UButton size="xs" color="primary" variant="subtle" icon="i-lucide-plus" @click="isManualModalOpen = true">
          {{ $t('managers.add_participant') }}
        </UButton>
        <UButton size="xs" color="neutral" variant="ghost" icon="i-lucide-refresh-cw" @click="fetchParticipants">
          {{ $t('dashboard.refresh') }}
        </UButton>
      </div>
    </div>

    <!-- Table -->
    <div v-if="participants.length > 0" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-neutral-100 dark:border-neutral-800">
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $t('managers.participant') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $t('managers.type') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $t('common.status') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $t('managers.registered') }}</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $t('common.actions') }}</th>
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
                    {{ participant.team ? $t('managers.captain_prefix') + ' ' + (participant.user?.name || '—') : (participant.user?.email || participant.user?.phone || $t('managers.no_contact_data')) }}
                  </span>
                </div>
              </div>
            </td>

            <!-- Type -->
            <td class="px-6 py-4">
              <span class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ participant.team ? $t('managers.types.team') : $t('managers.types.individual') }}
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
                {{ new Date(participant.created_at).toLocaleDateString(locale === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'short' }) }}
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
                  <UTooltip :text="$t('managers.tooltips.disqualify')">
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
                  <UTooltip :text="$t('managers.tooltips.restore')">
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
      <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-1">{{ $t('managers.empty_participants') }}</p>
      <p class="text-xs text-neutral-400 dark:text-neutral-500">{{ $t('managers.empty_participants_desc') }}</p>
    </div>

  </div>

  <!-- Manual Registration Modal -->
  <UModal 
    v-model:open="isManualModalOpen" 
    :ui="{ content: 'rounded-[2.5rem] bg-white dark:bg-neutral-900 border-none' }"
    class="sm:max-w-md"
  >
    <template #content>
      <div class="p-8 space-y-8 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -top-24 -right-24 size-48 bg-indigo-500/10 blur-[80px] rounded-full pointer-events-none"></div>

        <header class="relative z-10">
          <div class="flex items-center justify-between mb-4">
            <div class="size-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20 shadow-inner">
              <UIcon name="i-lucide-user-plus" class="text-indigo-400 size-6" />
            </div>
            <UBadge color="neutral" variant="subtle" class="rounded-full uppercase font-black tracking-widest text-[10px] px-3">
              {{ $t('managers.manual_modal.subtitle') }}
            </UBadge>
          </div>
          <h2 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tight leading-none mb-1">
            {{ $t('managers.manual_modal.title') }}
          </h2>
          <p class="text-neutral-500 font-medium text-xs">{{ props.participantType === 'team' ? $t('managers.manual_modal.team_name_desc') : $t('managers.manual_modal.name_desc') }}</p>
        </header>

        <UForm :state="manualForm" @submit="handleManualSubmit" class="space-y-6 relative z-10">
          <!-- Team Name (If Team Tournament) -->
          <div v-if="props.participantType === 'team'" class="space-y-2">
            <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">{{ $t('managers.manual_modal.team_name') }}</label>
            <UInput 
              v-model="manualForm.team_name" 
              placeholder="Misal: Evos Legends" 
              size="xl" 
              autofocus 
              class="w-full"
              :ui="{ base: 'rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-white/5' }"
            />
          </div>

          <!-- Full Name / Captain Name -->
          <div class="space-y-2">
            <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">
              {{ props.participantType === 'team' ? $t('managers.manual_modal.captain_name') : $t('managers.manual_modal.full_name') }}
            </label>
            <UInput 
              v-model="manualForm.name" 
              placeholder="Misal: Budi Santoso" 
              size="xl" 
              :autofocus="props.participantType !== 'team'" 
              class="w-full"
              :ui="{ base: 'rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-white/5' }"
            />
          </div>

          <!-- Email & Phone -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">{{ $t('settings.profile.email_label') }}</label>
              <UInput 
                v-model="manualForm.email" 
                type="email" 
                placeholder="contoh@mail.com" 
                size="xl" 
                class="w-full"
                :ui="{ base: 'rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-white/5' }"
              />
            </div>
            
            <div class="space-y-2">
              <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">{{ $t('managers.manual_modal.phone_label') }}</label>
              <UInput 
                v-model="manualForm.phone" 
                placeholder="08123xxxx" 
                size="xl" 
                class="w-full"
                :ui="{ base: 'rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-white/5' }"
              />
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="flex flex-col gap-3 pt-4 border-t border-neutral-100 dark:border-white/5 mt-6">
            <UButton 
              type="submit" 
              color="indigo" 
              block
              size="xl"
              class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-xl shadow-indigo-500/20"
              :loading="isManualSubmitting"
            >
              {{ $t('managers.manual_modal.submit') }}
            </UButton>
            <UButton 
              color="neutral" 
              variant="ghost" 
              block
              size="xl"
              class="rounded-2xl font-bold uppercase tracking-widest text-[10px] py-2"
              @click="isManualModalOpen = false" 
              :disabled="isManualSubmitting"
            >
              {{ $t('common.cancel') }}
            </UButton>
          </div>
        </UForm>
      </div>
    </template>
  </UModal>
</template>
