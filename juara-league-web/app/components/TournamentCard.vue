<script setup lang="ts">
import type { Tournament } from '~/types/tournament';
import { getTournamentStatus } from '~/utils/tournamentStatus';

interface Props {
  tournament: Tournament
  link?: string
  actionText?: string
  actionIcon?: string
}

const props = withDefaults(defineProps<Props>(), {
  actionText: 'Lihat Turnamen',
  actionIcon: 'i-lucide-arrow-right'
})

const destination = computed(() => props.link || `/tournaments/${props.tournament.slug}`)

const status = computed(() => getTournamentStatus(props.tournament.status))

// Safe formatting for start_at
const formattedDate = computed(() => {
  if (!props.tournament.start_at) return 'TBA'
  try {
    return new Date(props.tournament.start_at).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    })
  } catch (e) {
    return props.tournament.start_at
  }
})
</script>

<template>
  <div class="group relative bg-white dark:bg-neutral-900/60 backdrop-blur-3xl rounded-2xl border border-neutral-200 dark:border-white/5 overflow-hidden transition-all duration-500 hover:border-primary-500/40 hover:shadow-[0_0_50px_-12px_rgba(234,179,8,0.15)] flex flex-col h-full shadow-sm dark:shadow-none">
    <!-- Thumbnail Image -->
    <div class="relative h-48 overflow-hidden bg-neutral-100 dark:bg-neutral-900">
      <img 
        v-if="tournament.banner_url"
        :src="tournament.banner_url" 
        :alt="tournament.title"
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
      />
      <div v-else class="w-full h-full flex items-center justify-center bg-neutral-200 dark:bg-neutral-800">
        <UIcon name="i-lucide-image" class="size-10 text-neutral-400 dark:text-neutral-700" />
      </div>
      <!-- Darker Gradient Overlay (Reduced to be less aggressive in light mode) -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
      
      <!-- Badges -->
      <div class="absolute top-4 left-4 flex flex-col gap-2">
        <UBadge 
          :color="status.color" 
          variant="solid" 
          class="font-black px-3 py-1 rounded-lg backdrop-blur-md ring-1 ring-white/20 uppercase text-[9px] tracking-widest flex items-center gap-2 shadow-xl"
        >
          <div class="size-1.5 rounded-full animate-pulse" :class="status.color === 'primary' ? 'bg-white' : 'bg-current'"></div>
          {{ status.label }}
        </UBadge>
      </div>

      <div class="absolute top-4 right-4">
        <UBadge 
          color="neutral" 
          variant="soft" 
          class="font-black px-3 py-1 rounded-lg backdrop-blur-md border border-white/20 dark:border-white/10 text-[9px] uppercase tracking-widest shadow-xl text-white bg-black/40"
        >
          {{ tournament.mode }}
        </UBadge>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6 flex flex-col flex-grow relative">
      <div class="flex items-center gap-2 mb-3">
        <div class="px-2 py-0.5 bg-primary-500/10 rounded ring-1 ring-primary-500/20">
          <span class="text-[9px] font-black text-primary-500 dark:text-primary-400 uppercase tracking-widest">{{ tournament.sport?.name || tournament.category }}</span>
        </div>
        <span v-if="tournament.sport && tournament.category" class="text-neutral-300 dark:text-neutral-700">/</span>
        <span v-if="tournament.sport && tournament.category" class="text-[9px] font-bold text-neutral-500 uppercase tracking-widest">{{ tournament.category }}</span>
        <span class="text-neutral-300 dark:text-neutral-700">/</span>
        <span class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ formattedDate }}</span>
      </div>

      <h3 class="text-xl font-black text-neutral-900 dark:text-white leading-tight mb-4 group-hover:text-primary-500 transition-colors line-clamp-2 tracking-tight">
        {{ tournament.title }}
      </h3>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 gap-y-5 gap-x-2 mt-auto">
        <div class="flex flex-col gap-1.5">
          <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Prize Pool</span>
          <div class="flex items-center gap-2">
            <div class="p-1 bg-yellow-500/10 rounded-md ring-1 ring-yellow-500/20">
              <UIcon name="i-lucide-trophy" class="text-yellow-500 size-4" />
            </div>
            <span class="text-sm sm:text-base font-black text-neutral-900 dark:text-white tracking-tight">{{ formatCurrency(tournament.prize_pool) }}</span>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Slots</span>
          <div class="flex items-center gap-2">
            <div class="p-1 bg-primary-500/10 rounded-md ring-1 ring-primary-500/20">
              <UIcon name="i-lucide-users-2" class="text-primary-500 dark:text-primary-400 size-4" />
            </div>
            <span class="text-sm sm:text-base font-black text-neutral-900 dark:text-white tracking-tight">{{ tournament.current_participants ?? 0 }}/{{ tournament.max_participants }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-1.5">
          <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Tipe & Format</span>
          <div class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300">
            <UIcon :name="tournament.participant_type === 'team' ? 'i-lucide-users' : 'i-lucide-user'" class="size-3.5 text-neutral-400 dark:text-neutral-500" />
            <span class="truncate">{{ tournament.participant_type === 'team' ? `Tim ` + (tournament.team_size ? `(${tournament.team_size})` : '') : 'Individu' }}</span>
            <span class="text-neutral-300 dark:text-neutral-700">•</span>
            <span class="truncate">{{ tournament.bracket_type === 'single' ? 'Single Elim' : tournament.bracket_type === 'double' ? 'Double Elim' : tournament.bracket_type === 'round_robin' ? 'Round Robin' : tournament.bracket_type }}</span>
          </div>
        </div>
        
        <div class="flex flex-col gap-1.5">
          <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Lokasi & Venue</span>
          <div class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300">
            <UIcon :name="tournament.venue?.toLowerCase() === 'online' ? 'i-lucide-globe' : 'i-lucide-map-pin'" class="size-3.5 text-neutral-400 dark:text-neutral-500" />
            <span class="truncate">{{ tournament.venue || 'TBA' }}</span>
          </div>
        </div>
      </div>

      <div class="h-px bg-neutral-100 dark:bg-white/5 my-5"></div>

      <!-- Footer Info -->
      <div class="flex flex-col gap-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="relative flex-shrink-0">
              <UAvatar 
                v-if="tournament.user"
                :src="tournament.user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(tournament.user.name)}&background=random`" 
                :alt="tournament.user.name" 
                size="sm"
                class="ring-1 ring-neutral-200 dark:ring-white/10 rounded-lg shadow-sm"
              />
            </div>
            <div v-if="tournament.user" class="flex flex-col min-w-0 pr-2">
              <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 truncate">{{ tournament.user.name }}</span>
              <span class="text-[9px] font-medium text-neutral-400 dark:text-neutral-500 uppercase tracking-wide">Organizer</span>
            </div>
          </div>

          <div class="text-right shrink-0 bg-neutral-50 dark:bg-neutral-800/50 px-3 py-1.5 rounded-lg border border-neutral-100 dark:border-white/5">
            <span class="block text-[8px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-0.5">Biaya Daftar</span>
            <span class="text-xs sm:text-sm font-black text-primary-600 dark:text-primary-400">{{ tournament.entry_fee == 0 ? 'GRATIS' : formatCurrency(tournament.entry_fee) }}</span>
          </div>
        </div>

        <!-- Action Button (Visual only, overlapping is handled by main NuxtLink) -->
        <div class="w-full flex items-center justify-center py-3 bg-neutral-900 dark:bg-white/5 group-hover:bg-primary-500 text-white rounded-xl font-bold text-sm tracking-widest uppercase transition-all duration-300 shadow-md transform group-hover:-translate-y-0.5 relative overflow-hidden group-hover:shadow-[0_4px_20px_-5px_rgba(var(--color-primary-500),0.5)]">
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700 pointer-events-none"></div>
          <span class="flex items-center gap-2 relative z-10 transition-transform duration-300 group-hover:scale-105">
            {{ actionText }} <UIcon :name="actionIcon" class="size-4" />
          </span>
        </div>
      </div>
    </div>
    
    <!-- Action Overlay -->
    <NuxtLink :to="destination" class="absolute inset-0 z-10" />
  </div>
</template>
