<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Participant } from '~/types/tournament'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const tournamentStore = useTournamentStore()
const { myParticipations, isLoading, error } = storeToRefs(tournamentStore)
const { t, locale } = useI18n()

// Initial fetch
const { pending } = await useAsyncData('dashboard-my-participations', () => tournamentStore.fetchMyParticipations())

const refreshParticipations = () => {
  tournamentStore.fetchMyParticipations()
}

const statusConfig = computed(() => ({
  pending: { label: t('participations.status.pending'), color: 'warning', icon: 'i-lucide-clock' },
  approved: { label: t('participations.status.approved'), color: 'success', icon: 'i-lucide-check-circle' },
  rejected: { label: t('participations.status.rejected'), color: 'error', icon: 'i-lucide-x-circle' },
  paid: { label: t('participations.status.paid'), color: 'primary', icon: 'i-lucide-credit-card' }
}))

const getStatus = (status: Participant['status']) => {
  return statusConfig.value[status]
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
</script>

<template>
  <UDashboardPanel id="participations" grow>
    <template #header>
      <UDashboardNavbar :title="$t('participations.title')">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <UButton 
            icon="i-lucide-refresh-cw" 
            color="neutral" 
            variant="ghost" 
            size="sm" 
            :loading="isLoading"
            @click="refreshParticipations"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div v-if="isLoading && myParticipations.length === 0" class="flex flex-col gap-4 p-4">
        <div v-for="i in 3" :key="i" class="h-32 bg-neutral-900/40 rounded-3xl animate-pulse border border-white/5"></div>
      </div>

      <div v-else-if="myParticipations.length > 0" class="flex flex-col gap-4 p-4">
        <div 
          v-for="participation in myParticipations" 
          :key="participation.id"
          class="group relative bg-neutral-900/40 border border-white/5 rounded-3xl p-6 hover:bg-neutral-900/60 transition-all hover:border-primary-500/20"
        >
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <!-- Tournament Info -->
            <div class="flex items-center gap-6">
              <div class="size-16 rounded-2xl bg-neutral-800 overflow-hidden flex-shrink-0 border border-white/5">
                <img 
                  v-if="participation.tournament?.banner_url" 
                  :src="participation.tournament.banner_url" 
                  class="size-full object-cover"
                />
                <div v-else class="size-full flex items-center justify-center">
                   <UIcon name="i-lucide-trophy" class="size-8 text-neutral-700" />
                </div>
              </div>
              
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-[10px] font-black uppercase tracking-widest text-primary-500">
                    {{ participation.tournament?.sport?.name || 'Tournament' }}
                  </span>
                  <span class="text-neutral-700">•</span>
                  <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">
                    {{ participation.tournament?.category }}
                  </span>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight group-hover:text-primary-400 transition-colors">
                  {{ participation.tournament?.title }}
                </h3>
                <div class="text-sm text-neutral-500 font-medium">
                  {{ $t('participations.registered_on', { date: formatDate(participation.created_at) }) }}
                </div>
              </div>
            </div>

            <!-- Status & Action -->
            <div class="flex items-center gap-4 ml-auto md:ml-0">
              <div class="flex flex-col items-end gap-2">
                <UBadge 
                  :color="getStatus(participation.status).color" 
                  variant="subtle"
                  class="rounded-lg font-bold uppercase text-[10px] tracking-widest px-3 py-1 flex items-center gap-1.5"
                >
                  <UIcon :name="getStatus(participation.status).icon" class="size-3" />
                  {{ getStatus(participation.status).label }}
                </UBadge>
                <div v-if="participation.team" class="text-[10px] font-bold text-neutral-500 uppercase flex items-center gap-1">
                  <UIcon name="i-lucide-users" class="size-3" />
                  {{ $t('participations.type.team', { name: participation.team.name }) }}
                </div>
                <div v-else class="text-[10px] font-bold text-neutral-500 uppercase flex items-center gap-1">
                  <UIcon name="i-lucide-user" class="size-3" />
                  {{ $t('participations.type.individual') }}
                </div>
              </div>
              
              <div class="h-12 w-px bg-white/5 hidden md:block"></div>
              
              <UButton 
                :to="`/tournaments/${participation.tournament?.slug}`"
                color="neutral"
                variant="solid"
                icon="i-lucide-arrow-right"
                class="rounded-2xl size-12 flex items-center justify-center bg-white/5 border border-white/10 hover:bg-white/10"
              />
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-32 text-center">
        <div class="bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-white/5 relative group">
           <UIcon name="i-lucide-swords" class="size-20 text-neutral-800 group-hover:text-primary-500 transition-colors" />
        </div>
        <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-tight">
          {{ $t('participations.no_participations') }}
        </h3>
        <p class="text-neutral-500 font-medium max-w-sm mb-8">
          {{ $t('participations.no_participations_desc') }}
        </p>
        <UButton 
          to="/tournaments" 
          icon="i-lucide-search" 
          color="primary" 
          :label="$t('participations.find_tournament')"
          class="font-black rounded-2xl px-8 py-3 uppercase tracking-widest shadow-xl shadow-primary-500/20"
        />
      </div>
    </template>
  </UDashboardPanel>
</template>
