<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentFilter } from '~/types/tournament'

const tournamentStore = useTournamentStore()
const sportStore = useSportStore()
const { tournaments, isLoading, error } = storeToRefs(tournamentStore)
const { sports } = storeToRefs(sportStore)

// Initial fetch
const { pending } = await useAsyncData('tournaments-data', async () => {
  await Promise.all([
    tournamentStore.fetchTournaments(),
    sportStore.fetchSports()
  ])
})

const filters = reactive<TournamentFilter>({
  search: '',
  sport_id: 'all',
  status: 'Semua Status',
  mode: 'Semua Mode'
})

const categories = computed(() => [
  { id: 'all', name: 'Semua Cabang' },
  ...sports.value.map(s => ({ id: s.id, name: s.name }))
])
const statuses = ['Semua Status', 'open', 'ongoing', 'finished']
const modes = ['Semua Mode', 'online', 'offline']

// Computed filter logic (client-side for now, can be moved to API later)
const filteredTournaments = computed(() => {
  return tournaments.value.filter(t => {
    const sMatch = t.title.toLowerCase().includes(filters.search.toLowerCase())
    const cMatch = filters.sport_id === 'all' || t.sport_id === filters.sport_id
    const stMatch = filters.status === 'Semua Status' || t.status === filters.status
    const mMatch = filters.mode === 'Semua Mode' || t.mode === filters.mode
    return sMatch && cMatch && stMatch && mMatch
  })
})

const refreshTournaments = () => {
  tournamentStore.fetchTournaments()
}
</script>

<template>
  <div class="min-h-screen bg-neutral-950 pb-20 overflow-hidden relative">
    <!-- Decoraive Background -->
    <div class="absolute top-0 right-0 w-full h-[600px] bg-primary-500/5 blur-[120px] rounded-full pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-full h-[400px] bg-primary-600/5 blur-[100px] rounded-full pointer-events-none"></div>

    <!-- Header Section -->
    <div class="pt-32 pb-16 relative z-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 border border-primary-500/20 mb-6">
          <div class="size-1.5 rounded-full bg-primary-500 animate-pulse"></div>
          <span class="text-[10px] font-black text-primary-400 uppercase tracking-widest">Tournament Center</span>
        </div>
        <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight mb-6 uppercase">
          Temukan <span class="text-primary-500">Kemenanganmu</span>
        </h1>
        <p class="text-neutral-400 text-lg max-w-2xl mx-auto font-medium">
          Daftar dan kelola turnamen favoritmu. Dari sepak bola hingga e-sports, semua kompetisi ada di sini.
        </p>
      </div>

      <!-- Filters & Search Bar -->
      <div class="bg-neutral-900/40 backdrop-blur-2xl p-4 sm:p-2 rounded-3xl sm:rounded-full border border-white/10 shadow-2xl relative overflow-hidden group mb-12 ring-1 ring-white/5">
        <div class="flex flex-col lg:flex-row items-center gap-2">
          <!-- Search -->
          <div class="w-full lg:flex-1">
            <UInput 
              v-model="filters.search" 
              placeholder="Cari turnamen..." 
              icon="i-lucide-search"
              size="xl"
              variant="none"
              class="w-full"
              :ui="{ 
                base: 'bg-transparent border-none focus:ring-0 text-white placeholder-neutral-500 rounded-full py-4',
                leading: 'text-neutral-500 group-focus-within:text-primary-400 transition-colors'
              }"
            />
          </div>

          <div class="hidden lg:block w-px h-8 bg-white/10"></div>
          
          <!-- Filters Group -->
          <div class="w-full lg:w-auto flex flex-col sm:flex-row items-center gap-2 p-2 lg:p-0">
            <!-- Category -->
            <USelectMenu 
              v-model="filters.sport_id" 
              :options="categories"
              value-attribute="id"
              option-attribute="name"
              size="lg"
              variant="none"
              class="w-full sm:w-44"
              :ui="{ 
                base: 'bg-white/5 hover:bg-white/10 border-none focus:ring-primary-500/50 rounded-2xl sm:rounded-full transition-all text-neutral-300 font-bold px-4',
                content: 'bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl p-2',
                item: 'rounded-xl hover:bg-primary-500/10 hover:text-primary-400 font-medium'
              }"
            >
              <template #leading>
                <UIcon name="i-lucide-award" class="size-4 text-primary-400" />
              </template>
            </USelectMenu>

            <!-- Status -->
            <USelectMenu 
              v-model="filters.status" 
              :options="statuses"
              size="lg"
              variant="none"
              class="w-full sm:w-44"
              :ui="{ 
                base: 'bg-white/5 hover:bg-white/10 border-none focus:ring-primary-500/50 rounded-2xl sm:rounded-full transition-all text-neutral-300 font-bold px-4',
                content: 'bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl p-2',
                item: 'rounded-xl hover:bg-primary-500/10 hover:text-primary-400 font-medium'
              }"
            >
              <template #leading>
                <UIcon name="i-lucide-activity" class="size-4 text-primary-400" />
              </template>
            </USelectMenu>

            <!-- Mode -->
            <USelectMenu 
              v-model="filters.mode" 
              :options="modes"
              size="lg"
              variant="none"
              class="w-full sm:w-44"
              :ui="{ 
                base: 'bg-white/5 hover:bg-white/10 border-none focus:ring-primary-500/50 rounded-2xl sm:rounded-full transition-all text-neutral-300 font-bold px-4',
                content: 'bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl p-2',
                item: 'rounded-xl hover:bg-primary-500/10 hover:text-primary-400 font-medium'
              }"
            >
              <template #leading>
                <UIcon name="i-lucide-globe" class="size-4 text-primary-400" />
              </template>
            </USelectMenu>

            <!-- Reset -->
            <UButton 
              icon="i-lucide-refresh-cw" 
              color="primary" 
              variant="soft" 
              size="lg" 
              :loading="isLoading"
              class="w-full sm:w-auto rounded-2xl sm:rounded-full hover:scale-105 active:scale-95 transition-all px-4"
              @click="refreshTournaments"
            />
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading && filteredTournaments.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="i in 6" :key="i" class="h-96 bg-neutral-900/40 rounded-[2.5rem] animate-pulse"></div>
      </div>

      <!-- Results Grid -->
      <div v-else-if="filteredTournaments.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <TournamentCard 
          v-for="tournament in filteredTournaments" 
          :key="tournament.id" 
          :tournament="tournament" 
        />
      </div>

      <!-- Empty State -->
      <div v-else class="py-20 flex flex-col items-center justify-center text-center">
        <div class="bg-neutral-900/50 p-8 rounded-full mb-6 ring-1 ring-white/5">
          <UIcon :name="error ? 'i-lucide-alert-triangle' : 'i-lucide-search-x'" class="size-16" :class="error ? 'text-red-500' : 'text-neutral-600'" />
        </div>
        <h3 class="text-2xl font-bold text-white mb-2">{{ error ? 'Terjadi Kesalahan' : 'Tidak Menemukan Apapun' }}</h3>
        <p class="text-neutral-500 font-medium max-w-xs mx-auto">
          {{ error || 'Coba atur ulang filter atau kata kunci pencarian Anda untuk hasil yang lebih baik.' }}
        </p>
        <UButton 
          icon="i-lucide-rotate-ccw" 
          color="primary" 
          variant="soft" 
          class="mt-8 font-bold rounded-xl"
          @click="Object.assign(filters, { search: '', sport_id: 'all', status: 'Semua Status', mode: 'Semua Mode' }); refreshTournaments()"
        >
          {{ error ? 'Coba Lagi' : 'Reset Filter' }}
        </UButton>
      </div>
    </div>
  </div>
</template>
