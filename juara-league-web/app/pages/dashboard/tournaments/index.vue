<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useTournamentStore } from '~/stores/tournamentStore'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const tournamentStore = useTournamentStore()
const { myTournaments, isLoading, error } = storeToRefs(tournamentStore)
const { t } = useI18n()

// Initial fetch
const { pending } = await useAsyncData('dashboard-my-tournaments', () => tournamentStore.fetchMyTournaments())

const refreshMyTournaments = () => {
  tournamentStore.fetchMyTournaments()
}

const searchQuery = ref('')
const statusFilter = ref({ label: t('tournament_manager.filter_all'), value: 'all' })

const filteredTournaments = computed(() => {
  return myTournaments.value.filter(t => {
    const sMatch = t.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    const stMatch = statusFilter.value.value === 'all' || t.status === statusFilter.value.value
    return sMatch && stMatch
  })
})

const statuses = computed(() => [
  { label: t('tournament_manager.filter_all'), value: 'all' },
  { label: t('tournament_manager.filter_draft'), value: 'draft' },
  { label: t('tournament_manager.filter_open'), value: 'open' },
  { label: t('tournament_manager.filter_ongoing'), value: 'ongoing' },
  { label: t('tournament_manager.filter_finished'), value: 'finished' }
])
</script>

<template>
  <UDashboardPanel id="tournaments" grow>
    <template #header>
      <UDashboardNavbar :title="$t('tournament_manager.title')">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <UButton 
            to="/dashboard/tournaments/create" 
            icon="i-lucide-plus" 
            color="primary" 
            :label="$t('tournament_manager.create_button')"
            class="rounded-xl font-bold px-4 h-9 shadow-lg shadow-primary-500/20"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <!-- Toolbar -->
      <UDashboardToolbar>
        <template #left>
          <UInput 
            v-model="searchQuery" 
            :placeholder="$t('tournament_manager.search_placeholder')" 
            icon="i-lucide-search"
            class="w-64"
          />
          <USelectMenu 
            v-model="statusFilter" 
            :items="statuses" 
            class="w-40"
          />
        </template>
        <template #right>
          <UButton 
            icon="i-lucide-refresh-cw" 
            color="neutral" 
            variant="ghost" 
            size="sm" 
            :loading="isLoading"
            @click="refreshMyTournaments"
          />
        </template>
      </UDashboardToolbar>

      <!-- Content -->
      <div v-if="isLoading && myTournaments.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        <div v-for="i in 3" :key="i" class="h-80 bg-neutral-900/40 rounded-3xl animate-pulse border border-white/5"></div>
      </div>

      <div v-else-if="filteredTournaments.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        <div v-for="tournament in filteredTournaments" :key="tournament.id" class="group relative">
           <TournamentCard 
             :tournament="tournament" 
             :link="`/dashboard/tournaments/${tournament.slug}`"
             :actionText="$t('tournament_manager.manage_action')"
             actionIcon="i-lucide-settings-2"
           />
           <!-- Management Overlay -->
           <div class="absolute top-4 right-4 z-20 opacity-0 group-hover:opacity-100 transition-all scale-95 group-hover:scale-100 flex gap-2">
             <UButton 
              icon="i-lucide-trash-2" 
              color="error" 
              variant="solid" 
              size="xs" 
              class="rounded-lg shadow-2xl drop-shadow-lg"
              :title="$t('tournament_manager.delete_action')"
             />
           </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-32 text-center">
        <div class="bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-white/5 relative group">
           <UIcon name="i-lucide-trophy" class="size-20 text-neutral-800 group-hover:text-primary-500 transition-colors" />
        </div>
        <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-tight">
          {{ searchQuery ? $t('tournament_manager.no_results') : $t('tournament_manager.no_tournaments') }}
        </h3>
        <p class="text-neutral-500 font-medium max-w-sm mb-8">
          {{ searchQuery ? $t('tournament_manager.no_results_desc') : $t('tournament_manager.no_tournaments_desc') }}
        </p>
        <UButton 
          v-if="!searchQuery"
          to="/dashboard/tournaments/create" 
          icon="i-lucide-plus" 
          color="primary" 
          :label="$t('tournament_manager.create_first')"
          class="font-black rounded-2xl px-8 py-3 uppercase tracking-widest shadow-xl shadow-primary-500/20"
        />
      </div>
    </template>
  </UDashboardPanel>
</template>
