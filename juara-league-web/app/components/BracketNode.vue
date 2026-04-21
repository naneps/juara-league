<template>
  <div 
    class="relative flex flex-col justify-center my-3 group w-[300px] shrink-0 border border-neutral-200 dark:border-white/5 bg-white dark:bg-neutral-900/60 backdrop-blur-xl shadow-xl dark:shadow-2xl rounded-[1.25rem] z-10 hover:border-primary-500/50 hover:bg-neutral-50 dark:hover:bg-neutral-900/80 transition-all duration-300 cursor-pointer select-none"
    @click="$emit('click')"
  >
    <!-- Glow Effect Backing -->
    <div class="absolute -inset-px bg-gradient-to-br from-primary-500/10 to-transparent rounded-[1.25rem] opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
    
    <!-- Match Info Handle -->
    <div class="flex justify-between items-center text-[9px] font-black tracking-[0.2em] uppercase text-neutral-400 dark:text-neutral-500 px-5 pt-4 pb-2 relative z-10">
      <div class="flex items-center gap-2">
        <span class="text-neutral-800 dark:text-white/60">MATCH {{ match.match_number }}</span>
        <span v-if="boFormat" class="opacity-20 dark:opacity-30">•</span>
        <span v-if="boFormat" class="text-primary-600 dark:text-primary-500/70">{{ boFormat }}</span>
      </div>
      <div class="flex items-center gap-1.5 bg-neutral-100 dark:bg-white/5 px-2 py-0.5 rounded-full border border-neutral-200 dark:border-white/5">
        <UIcon name="i-lucide-clock" class="size-2.5 text-neutral-400 dark:text-neutral-500" />
        <span class="text-neutral-600 dark:text-white/50">{{ formattedTime }}</span>
      </div>
    </div>

    <!-- Team 1 -->
    <div 
      class="flex items-center justify-between px-5 py-3.5 relative z-10 transition-colors"
      :class="[
        isWinner(match.participant_1_id) ? 'bg-primary-500/5' : '',
        isLoser(match.participant_1_id) ? 'opacity-40 grayscale-[0.5]' : ''
      ]"
    >
      <div class="flex items-center gap-3 truncate">
        <div class="relative">
          <UAvatar 
            :src="match.participant_1?.team?.logo || match.participant_1?.user?.avatar" 
            :alt="getParticipantName(match.participant_1)" 
            size="xs"
            class="ring-1 ring-neutral-200 dark:ring-white/10"
          />
          <div v-if="isWinner(match.participant_1_id)" class="absolute -right-1 -top-1 size-3 bg-primary-500 rounded-full flex items-center justify-center border-2 border-white dark:border-neutral-900">
            <UIcon name="i-lucide-check" class="size-2 text-white" />
          </div>
        </div>
        <span class="font-bold text-xs truncate uppercase tracking-wide" :class="isWinner(match.participant_1_id) ? 'text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400'">
          {{ getParticipantName(match.participant_1) }}
        </span>
      </div>
      <span class="font-black text-lg italic ml-4" :class="isWinner(match.participant_1_id) ? 'text-primary-500 dark:text-primary-400' : 'text-neutral-300 dark:text-neutral-600'">
        {{ match.scores?.participant_1 ?? '-' }}
      </span>
    </div>

    <div class="h-px bg-neutral-100 dark:bg-white/5 mx-5"></div>

    <!-- Team 2 -->
    <div 
      class="flex items-center justify-between px-5 py-3.5 relative z-10 transition-colors"
      :class="[
        isWinner(match.participant_2_id) ? 'bg-primary-500/5' : '',
        isLoser(match.participant_2_id) ? 'opacity-40 grayscale-[0.5]' : ''
      ]"
    >
      <div class="flex items-center gap-3 truncate">
        <div class="relative">
          <UAvatar 
            :src="match.participant_2?.team?.logo || match.participant_2?.user?.avatar" 
            :alt="getParticipantName(match.participant_2)" 
            size="xs"
            class="ring-1 ring-neutral-200 dark:ring-white/10"
          />
          <div v-if="isWinner(match.participant_2_id)" class="absolute -right-1 -top-1 size-3 bg-primary-500 rounded-full flex items-center justify-center border-2 border-white dark:border-neutral-900">
            <UIcon name="i-lucide-check" class="size-2 text-white" />
          </div>
        </div>
        <span class="font-bold text-xs truncate uppercase tracking-wide" :class="isWinner(match.participant_2_id) ? 'text-neutral-900 dark:text-white' : 'text-neutral-500 dark:text-neutral-400'">
          {{ getParticipantName(match.participant_2) }}
        </span>
      </div>
      <span class="font-black text-lg italic ml-4" :class="isWinner(match.participant_2_id) ? 'text-primary-500 dark:text-primary-400' : 'text-neutral-300 dark:text-neutral-600'">
        {{ match.scores?.participant_2 ?? '-' }}
      </span>
    </div>

    <!-- Bottom Status Line -->
    <div v-if="match.status === 'ongoing'" class="absolute bottom-0 left-5 right-5 h-[2px] bg-primary-500 shadow-[0_0_15px_rgba(34,197,94,0.8)] animate-pulse"></div>
  </div>
</template>

<script setup lang="ts">
import type { TournamentMatch } from '~/types/tournament'

const props = defineProps<{
  match: TournamentMatch
  boFormat?: string
}>()

defineEmits(['click'])

const { t } = useI18n()

const getParticipantName = (p: any) => {
  if (!p) return t('match.bracket.tbd')
  return p.team?.name || p.user?.name || t('match.bracket.tbd')
}

const formattedTime = computed(() => {
  if (!props.match.scheduled_at) return 'TBD'
  const date = new Date(props.match.scheduled_at)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })
})

const isWinner = (participantId: string | null) => {
  if (!participantId || !props.match.winner_id) return false
  return props.match.winner_id === participantId
}

const isLoser = (participantId: string | null) => {
  if (!participantId || !props.match.winner_id) return false
  return props.match.winner_id !== participantId && !!props.match.winner_id
}
</script>
