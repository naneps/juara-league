<script setup lang="ts">
import type { Tournament } from '~/types/tournament';
import { getTournamentStatus } from '~/utils/tournamentStatus';

interface Props {
  tournament: Tournament
}

const props = defineProps<Props>()

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
      <div class="grid grid-cols-2 gap-4 mt-auto">
        <div class="flex flex-col gap-1.5">
          <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Prize Pool</span>
          <div class="flex items-center gap-2">
            <div class="p-1 bg-yellow-500/10 rounded-md">
              <UIcon name="i-lucide-trophy" class="text-yellow-500 size-4" />
            </div>
            <span class="text-base font-black text-neutral-900 dark:text-white tracking-tight">{{ formatCurrency(tournament.prize_pool) }}</span>
          </div>
        </div>
        <div class="flex flex-col gap-1.5">
          <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Slots</span>
          <div class="flex items-center gap-2">
            <div class="p-1 bg-primary-500/10 rounded-md">
              <UIcon name="i-lucide-users-2" class="text-primary-500 dark:text-primary-400 size-4" />
            </div>
            <span class="text-base font-black text-neutral-900 dark:text-white tracking-tight">{{ tournament.current_participants ?? 0 }}/{{ tournament.max_participants }}</span>
          </div>
        </div>
      </div>

      <div class="h-px bg-neutral-100 dark:bg-white/10 my-6"></div>

      <!-- Footer Info -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="relative">
            <UAvatar 
              v-if="tournament.user"
              :src="tournament.user.avatar || `https://i.pravatar.cc/150?u=${tournament.user.email}`" 
              :alt="tournament.user.name" 
              size="xs"
              class="ring-2 ring-neutral-100 dark:ring-white/5 rounded-lg"
            />
            <div v-if="tournament.user" class="absolute -bottom-1 -right-1 bg-blue-500 rounded-full p-0.5 ring-2 ring-white dark:ring-neutral-950">
              <UIcon name="i-lucide-check" class="text-white size-1.5" />
            </div>
          </div>
          <div v-if="tournament.user" class="flex flex-col min-w-0">
            <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 truncate">{{ tournament.user.name }}</span>
            <span class="text-[9px] font-medium text-neutral-400 dark:text-neutral-600 uppercase tracking-wide">Organizer</span>
          </div>
        </div>

        <div class="text-right shrink-0">
          <span class="block text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-0.5">Entry Fee</span>
          <span class="text-sm font-black text-primary-600 dark:text-primary-400">{{ tournament.entry_fee == 0 ? 'Gratis' : formatCurrency(tournament.entry_fee) }}</span>
        </div>
      </div>
    </div>
    
    <!-- Action Overlay -->
    <NuxtLink :to="`/tournaments/${tournament.slug}`" class="absolute inset-0 z-10" />
  </div>
</template>
