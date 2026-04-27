<script setup lang="ts">
import type { Tournament } from '~/types/tournament';
import { getTournamentStatus } from '~/utils/tournamentStatus';

interface Props {
  tournament: Tournament
  link?: string
  actionText?: string
  actionIcon?: string
}

const { t, locale } = useI18n()

const props = withDefaults(defineProps<Props>(), {
  actionText: '', // Will be handled in template if empty
  actionIcon: 'i-lucide-arrow-right'
})

const destination = computed(() => props.link || `/tournaments/${props.tournament.slug}`)

const status = computed(() => getTournamentStatus(props.tournament.status))

// Safe formatting for start_at
const formattedDate = computed(() => {
  if (!props.tournament.start_at) return 'TBA'
  try {
    return new Date(props.tournament.start_at).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    })
  } catch (e) {
    return props.tournament.start_at
  }
})

const getRankIcon = (rank: number) => {
  if (rank === 1) return 'i-lucide-trophy'
  if (rank === 2) return 'i-lucide-medal'
  if (rank === 3) return 'i-lucide-award'
  return 'i-lucide-star'
}

const getRankColorClass = (rank: number) => {
  if (rank === 1) return 'text-amber-500'
  if (rank === 2) return 'text-slate-400'
  if (rank === 3) return 'text-orange-500'
  return 'text-neutral-500'
}
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
    <div class="p-4 flex flex-col flex-grow relative">
      <div class="flex items-center gap-2 mb-1.5">
        <div class="px-1.5 py-0.5 bg-primary-500/10 rounded ring-1 ring-primary-500/20">
          <span class="text-[8px] font-black text-primary-500 dark:text-primary-400 uppercase tracking-widest">{{ tournament.sport?.name || tournament.category }}</span>
        </div>
        <span class="text-[9px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ formattedDate }}</span>
      </div>

      <h3 class="text-base font-black text-neutral-900 dark:text-white leading-tight mb-3 group-hover:text-primary-500 transition-colors line-clamp-1 tracking-tight">
        {{ tournament.title }}
      </h3>

      <!-- Stats Grid (Compact) -->
      <div class="grid grid-cols-2 gap-x-4 gap-y-3 mt-auto mb-4">
        <div class="flex flex-col gap-0.5">
          <span class="text-[7px] font-bold text-neutral-400 uppercase tracking-widest">{{ $t('tournament_card.prize_pool') }}</span>
          <div class="flex items-center gap-1">
            <UIcon name="i-lucide-trophy" class="text-yellow-500 size-3" />
            <span class="text-[11px] font-black text-neutral-900 dark:text-white">{{ formatCurrency(tournament.prize_pool) }}</span>
          </div>
        </div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[7px] font-bold text-neutral-400 uppercase tracking-widest">{{ $t('tournament_card.slots') }}</span>
          <div class="flex items-center gap-1">
            <UIcon name="i-lucide-users-2" class="text-primary-500 size-3" />
            <span class="text-[11px] font-black text-neutral-900 dark:text-white">{{ (tournament.current_participants ?? 0) || (tournament.participants_count ?? 0) }}/{{ tournament.max_participants }}</span>
          </div>
        </div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[7px] font-bold text-neutral-400 uppercase tracking-widest">{{ $t('tournament_card.type_format') }}</span>
          <div class="flex items-center gap-1 text-[10px] font-bold text-neutral-700 dark:text-neutral-300">
            <span class="truncate">{{ tournament.participant_type === 'team' ? $t('tournament_form.participants.team') : $t('tournament_form.participants.individual') }}</span>
            <span class="text-neutral-300 dark:text-neutral-700">•</span>
            <span class="truncate">{{ tournament.format_summary || $t(`tournament_card.bracket_types.${tournament.bracket_type}`) }}</span>
          </div>
        </div>
        <div class="flex flex-col gap-0.5">
          <span class="text-[7px] font-bold text-neutral-400 uppercase tracking-widest">{{ $t('tournament_card.location_venue') }}</span>
          <div class="flex items-center gap-1 text-[10px] font-bold text-neutral-700 dark:text-neutral-300">
            <UIcon :name="tournament.venue?.toLowerCase() === 'online' ? 'i-lucide-globe' : 'i-lucide-map-pin'" class="size-3 text-neutral-400" />
            <span class="truncate">{{ tournament.venue || 'TBA' }}</span>
          </div>
        </div>
      </div>

      <!-- Mini Prize Breakdown (Small & Sleek) -->
      <div v-if="tournament.prizes && tournament.prizes.length > 0" class="flex flex-wrap gap-1.5 mb-4 border-t border-neutral-100 dark:border-white/5 pt-4">
        <div 
          v-for="prize in tournament.prizes.slice(0, 3)" 
          :key="prize.id"
          class="flex items-center gap-1 px-2 py-1 rounded-lg bg-neutral-50 dark:bg-white/[0.03] border border-neutral-100 dark:border-white/5"
        >
          <UIcon :name="getRankIcon(prize.rank)" :class="getRankColorClass(prize.rank)" class="size-3" />
          <span class="text-[9px] font-black text-neutral-900 dark:text-white">{{ formatCurrency(prize.prize_amount) }}</span>
        </div>
        <div v-if="tournament.prizes.length > 3" class="text-[8px] font-bold text-neutral-400 py-1">
          +{{ tournament.prizes.length - 3 }}
        </div>
      </div>

      <!-- Footer (Organizer & Entry Fee) -->
      <div class="flex items-center justify-between pt-3 border-t border-neutral-100 dark:border-white/5 mt-auto">
        <div class="flex items-center gap-2 min-w-0">
          <UAvatar 
            v-if="tournament.user"
            :src="tournament.user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(tournament.user.name)}&background=random`" 
            size="2xs"
            class="ring-1 ring-neutral-200 dark:ring-white/10 rounded-md"
          />
          <span class="text-[10px] font-bold text-neutral-700 dark:text-neutral-300 truncate">{{ tournament.user?.name }}</span>
        </div>
        <div class="text-right shrink-0">
          <span class="text-[11px] font-black" :class="tournament.entry_fee == 0 ? 'text-emerald-500' : 'text-primary-600 dark:text-primary-400'">
            {{ tournament.entry_fee == 0 ? $t('tournament_card.free') : formatCurrency(tournament.entry_fee) }}
          </span>
        </div>
      </div>

      <!-- Compact Action Strip -->
      <div class="mt-4 w-full h-8 flex items-center justify-center bg-neutral-900 dark:bg-white/5 group-hover:bg-primary-500 text-white rounded-lg font-black text-[10px] tracking-widest uppercase transition-all duration-300 shadow-sm relative overflow-hidden">
        <span class="flex items-center gap-1.5 relative z-10 transition-transform duration-300 group-hover:scale-105">
          {{ actionText || $t('tournament_card.view_tournament') }} <UIcon :name="actionIcon" class="size-3" />
        </span>
      </div>
    </div>
    
    <!-- Action Overlay -->
    <NuxtLink :to="destination" class="absolute inset-0 z-10" />
  </div>
</template>
