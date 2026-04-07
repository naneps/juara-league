<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import InteractiveCanvas from '~/components/InteractiveCanvas.vue'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()

const { data: tournament } = await useAsyncData(`tournament-bracket-${slug}`, () => tournamentStore.getBySlug(slug))
</script>

<template>
  <div v-if="tournament" class="space-y-12">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2">Bagan Pertandingan</h2>
        <p class="text-neutral-500 font-medium text-sm">Visualisasi struktur turnamen dan jadwal pertandingan.</p>
      </div>
      
      <UButton 
        color="neutral" 
        variant="ghost" 
        icon="i-lucide-maximize" 
        label="Fullscreen" 
        class="rounded-xl font-bold uppercase tracking-widest text-[10px]"
      />
    </div>

    <!-- Bracket Canvas -->
    <div class="h-[600px] w-full rounded-[3rem] border border-neutral-200 dark:border-white/5 bg-white dark:bg-neutral-900/40 relative overflow-hidden group shadow-sm dark:shadow-none">
      <InteractiveCanvas>
        <!-- For now, we'll show a centered empty state or a dummy bracket -->
        <div class="flex flex-col items-center justify-center h-full p-20 text-center opacity-40 group-hover:opacity-100 transition-opacity duration-700">
          <div class="bg-neutral-100 dark:bg-neutral-900 p-12 rounded-full mb-8 ring-1 ring-neutral-200 dark:ring-white/5 relative">
            <UIcon name="i-lucide-git-branch" class="size-20 text-neutral-400 dark:text-neutral-600" />
            <div class="absolute -top-2 -right-2 size-8 bg-primary-500 rounded-full flex items-center justify-center text-white p-1">
              <UIcon name="i-lucide-construction" class="size-full" />
            </div>
          </div>
          <h3 class="text-2xl font-black text-neutral-900 dark:text-white mb-2 uppercase tracking-tight">Generate Bracket</h3>
          <p class="text-neutral-500 font-bold uppercase tracking-widest text-[10px] max-w-sm">Anda harus memiliki minimal 2 peserta untuk menyusun bagan pertandingan.</p>
          
          <UButton 
            v-if="tournament.participants_count && tournament.participants_count >= 2"
            color="primary" 
            label="Generate Otomatis" 
            class="mt-8 rounded-2xl px-10 py-4 font-black uppercase tracking-widest text-xs shadow-2xl shadow-primary-500/30"
          />
        </div>
      </InteractiveCanvas>
    </div>
  </div>
</template>
