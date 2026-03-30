<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useTournamentStore } from '~/stores/tournamentStore'

definePageMeta({
  middleware: 'auth'
})

const tournamentStore = useTournamentStore()
const { myTournaments, isLoading, error } = storeToRefs(tournamentStore)

// Initial fetch
const { pending } = await useAsyncData('my-tournaments', () => tournamentStore.fetchMyTournaments())

const refreshMyTournaments = () => {
  tournamentStore.fetchMyTournaments()
}
</script>

<template>
  <div class="min-h-screen bg-neutral-950 pt-32 pb-20 relative overflow-hidden">
    <!-- Decoraive Background -->
    <div class="absolute top-0 right-0 w-full h-[600px] bg-primary-500/5 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
        <div>
          <h1 class="text-4xl font-black text-white uppercase tracking-tight mb-2">
            Turnamen <span class="text-primary-500">Saya</span>
          </h1>
          <p class="text-neutral-500 font-medium">Kelola turnamen yang Anda buat dan pantau pendaftarannya.</p>
        </div>
        
        <div class="flex items-center gap-3">
          <UButton 
            icon="i-lucide-refresh-cw" 
            color="neutral" 
            variant="ghost" 
            size="lg" 
            :loading="isLoading"
            class="rounded-xl"
            @click="refreshMyTournaments"
          />
          <UButton 
            to="/tournaments/create" 
            icon="i-lucide-plus" 
            color="primary" 
            size="lg" 
            class="rounded-xl font-bold px-6 shadow-[0_0_20px_-5px_rgba(234,179,8,0.3)] hover:scale-105 active:scale-95 transition-all"
          >
            Buat Turnamen
          </UButton>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading && myTournaments.length === 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="i in 3" :key="i" class="h-96 bg-neutral-900/40 rounded-[2.5rem] animate-pulse"></div>
      </div>

      <!-- Results Grid -->
      <div v-else-if="myTournaments.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="tournament in myTournaments" :key="tournament.id" class="relative group">
          <TournamentCard :tournament="tournament" />
          
          <!-- Management Overlay Actions -->
          <div class="absolute top-4 right-4 z-20 opacity-0 group-hover:opacity-100 transition-opacity">
            <div class="flex gap-2">
              <UButton 
                icon="i-lucide-edit" 
                color="primary" 
                variant="solid" 
                size="sm" 
                class="rounded-lg shadow-xl"
              />
              <UButton 
                icon="i-lucide-trash" 
                color="error" 
                variant="solid" 
                size="sm" 
                class="rounded-lg shadow-xl"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="py-32 flex flex-col items-center justify-center text-center">
        <div class="bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-white/5 relative overflow-hidden group">
          <div class="absolute inset-0 bg-primary-500/10 scale-0 group-hover:scale-100 transition-transform duration-500 rounded-full"></div>
          <UIcon name="i-lucide-trophy" class="size-20 text-neutral-700 group-hover:text-primary-500 transition-colors relative z-10" />
        </div>
        <h3 class="text-3xl font-black text-white mb-4 uppercase tracking-tight">Belum Ada Turnamen</h3>
        <p class="text-neutral-500 font-medium max-w-sm mx-auto mb-10 leading-relaxed">
          Anda belum membuat turnamen apapun. Mulai kompetisi Anda sendiri sekarang dan kumpulkan para juara!
        </p>
        <UButton 
          to="/tournaments/create" 
          icon="i-lucide-plus" 
          color="primary" 
          size="xl" 
          class="font-black rounded-2xl px-12 py-4 uppercase tracking-widest shadow-[0_0_30px_-5px_rgba(234,179,8,0.3)] hover:scale-105 active:scale-95 transition-all"
        >
          Buat Turnamen Pertama
        </UButton>
      </div>
    </div>
  </div>
</template>
