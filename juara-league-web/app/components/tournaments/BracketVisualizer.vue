<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch, Stage } from '~/types/tournament'

const { t } = useI18n()

const props = defineProps<{
  tournamentSlug: string
  stage: Stage
  isStaff?: boolean
}>()

const emit = defineEmits(['match-click', 'refresh'])

const tournamentStore = useTournamentStore()
const matches = ref<TournamentMatch[]>([])
const isLoading = ref(true)

const fetchMatches = async () => {
  isLoading.value = true
  try {
    matches.value = await tournamentStore.fetchMatches(props.tournamentSlug, props.stage.id)
  } catch (e) {
    console.error('Failed to fetch matches', e)
  } finally {
    isLoading.value = false
  }
}

const rounds = computed(() => {
  const grouped: Record<number, TournamentMatch[]> = {}
  for (const match of matches.value) {
    if (!grouped[match.round]) grouped[match.round] = []
    grouped[match.round].push(match)
  }
  return Object.entries(grouped)
    .map(([round, m]) => ({ 
      round: Number(round), 
      matches: m.sort((a, b) => a.match_number - b.match_number) 
    }))
    .sort((a, b) => a.round - b.round)
})

const isDoubleElim = computed(() => props.stage.type === 'double_elim')

const upperBracketRounds = computed(() =>
  rounds.value.filter(r => r.matches.some(m => m.bracket_side === 'upper' || !m.bracket_side))
    .map(r => ({ ...r, matches: r.matches.filter(m => m.bracket_side === 'upper' || (!m.bracket_side && !m.group_id)) }))
    .filter(r => r.matches.length > 0)
)

const grandFinalMatches = computed(() =>
  matches.value.filter(m => m.bracket_side === 'grand_final')
)

const getParticipantName = (p: any) => {
  if (!p) return t('match.bracket.tbd')
  return p.team?.name || p.user?.name || t('match.bracket.tbd')
}

const matchStatusColor = (status: string) => {
  const map: Record<string, string> = { upcoming: 'neutral', ongoing: 'primary', completed: 'success', bye: 'warning' }
  return map[status] || 'neutral'
}

// Connector logic helpers
const getCardHeight = () => 92 // rough height of match card
const getRoundGap = () => 300 // gap between columns

watch(() => props.stage.id, () => { fetchMatches() })
onMounted(() => { fetchMatches() })

defineExpose({ fetchMatches })
</script>

<template>
  <div v-if="isLoading" class="py-20 flex justify-center">
    <UIcon name="i-lucide-loader-2" class="size-8 text-primary-500 animate-spin" />
  </div>

  <div v-else-if="matches.length === 0" class="py-20 text-center">
    <UIcon name="i-lucide-calendar-x" class="size-12 text-neutral-700 mx-auto mb-4" />
    <p class="text-neutral-500 font-bold uppercase tracking-widest text-sm">{{ $t('match.bracket.empty') }}</p>
  </div>

  <div v-else class="space-y-32">
    <!-- Bracket Section -->
    <div class="relative overflow-x-auto pb-12 scrollbar-hide px-4">
      <!-- Section Title -->
      <div class="mb-12 flex items-center gap-3">
        <div class="h-4 w-1 bg-primary-500 rounded-full"></div>
        <h3 class="text-xs font-black text-white uppercase tracking-[0.4em]">{{ isDoubleElim ? $t('match.bracket.winners') : $t('match.bracket.main') }}</h3>
      </div>

      <div class="flex items-start gap-12 relative min-w-max">
        <!-- SVG Layer for Connectors -->
        <svg class="absolute inset-0 pointer-events-none" style="width: 100%; height: 100%;">
          <g v-for="(r, rIndex) in (isDoubleElim ? upperBracketRounds : rounds)" :key="'svg-'+r.round">
             <template v-if="rIndex < (isDoubleElim ? upperBracketRounds : rounds).length - 1">
               <path 
                 v-for="(match, mIndex) in r.matches" 
                 :key="'path-'+match.id"
                 :d="`
                    M ${ (rIndex * 312) + 256 } ${ 100 + (mIndex * (800 / r.matches.length)) + (400 / r.matches.length) }
                    L ${ (rIndex * 312) + 284 } ${ 100 + (mIndex * (800 / r.matches.length)) + (400 / r.matches.length) }
                    L ${ (rIndex * 312) + 284 } ${ 100 + (Math.floor(mIndex/2) * (800 / upperBracketRounds[rIndex+1]?.matches.length || 1)) + (400 / upperBracketRounds[rIndex+1]?.matches.length || 1) }
                    L ${ (rIndex * 312) + 312 } ${ 100 + (Math.floor(mIndex/2) * (800 / upperBracketRounds[rIndex+1]?.matches.length || 1)) + (400 / upperBracketRounds[rIndex+1]?.matches.length || 1) }
                 `"
                 fill="none"
                 stroke="#262626"
                 stroke-width="2"
               />
             </template>
          </g>
        </svg>

        <!-- Round Columns -->
        <div 
          v-for="(r, rIndex) in (isDoubleElim ? upperBracketRounds : rounds)" 
          :key="r.round"
          class="flex flex-col gap-0 w-64 z-10"
        >
          <!-- Round Header -->
          <div class="text-center mb-10 h-10 flex flex-col justify-center">
            <span class="text-[9px] font-black text-neutral-600 uppercase tracking-widest">{{ $t('match.bracket.round') }} {{ rIndex + 1 }}</span>
            <span class="text-[11px] font-black text-neutral-900 dark:text-white uppercase tracking-widest">{{ r.matches.length === 1 ? $t('match.bracket.final') : r.matches.length === 2 ? $t('match.bracket.semi_final') : $t('match.bracket.qualifiers') }}</span>
          </div>

          <!-- Matches -->
          <div class="flex flex-col justify-around h-[800px]">
            <div 
              v-for="match in r.matches" 
              :key="match.id"
              class="relative"
            >
              <button
                @click="emit('match-click', match)"
                class="w-64 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 shadow-sm dark:shadow-none rounded-xl transition-all hover:border-primary-500/50 hover:shadow-[0_0_30px_rgba(34,197,94,0.1)] group relative overflow-hidden"
              >
                <!-- VS Indicator -->
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 size-5 rounded-full bg-neutral-50 dark:bg-neutral-950 border border-neutral-200 dark:border-white/5 flex items-center justify-center z-10">
                   <span class="text-[7px] font-black text-neutral-400 dark:text-neutral-700">VS</span>
                </div>

                <div class="flex flex-col">
                  <!-- P1 -->
                  <div class="flex items-center justify-between px-3 py-3 border-b border-neutral-100 dark:border-white/5" :class="match.winner_id === match.participant_1_id ? 'bg-primary-500/10' : ''">
                    <span class="text-[11px] font-black truncate" :class="match.winner_id === match.participant_1_id ? 'text-neutral-900 dark:text-white' : 'text-neutral-400 dark:text-neutral-500'">{{ getParticipantName(match.participant_1) }}</span>
                    <span class="text-xs font-black text-primary-500">{{ match.scores?.participant_1 ?? 0 }}</span>
                  </div>
                  <!-- P2 -->
                  <div class="flex items-center justify-between px-3 py-3" :class="match.winner_id === match.participant_2_id ? 'bg-primary-500/10' : ''">
                    <span class="text-[11px] font-black truncate" :class="match.winner_id === match.participant_2_id ? 'text-neutral-900 dark:text-white' : 'text-neutral-400 dark:text-neutral-500'">{{ getParticipantName(match.participant_2) }}</span>
                    <span class="text-xs font-black text-primary-500">{{ match.scores?.participant_2 ?? 0 }}</span>
                  </div>
                </div>

                <!-- Status Badge -->
                <div v-if="match.status !== 'upcoming'" class="absolute right-0 top-0 translate-x-1/2 -translate-y-1/2">
                   <div class="size-2 rounded-full bg-primary-500 shadow-[0_0_10px_rgba(34,197,94,0.5)] animate-pulse"></div>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Grand Final Section -->
    <div v-if="grandFinalMatches.length > 0" class="flex flex-col items-center pb-20">
      <div class="h-24 w-px bg-gradient-to-b from-neutral-800 to-yellow-500/50 mb-12"></div>
      <div class="text-center mb-10">
        <UIcon name="i-lucide-trophy" class="size-10 text-yellow-500 mb-2" />
        <h3 class="text-sm font-black text-yellow-500 uppercase tracking-[0.5em]">{{ $t('match.bracket.grand_final') }}</h3>
      </div>

      <div class="flex gap-10">
        <button
          v-for="match in grandFinalMatches"
          :key="match.id"
          @click="emit('match-click', match)"
          class="w-96 group relative p-1 bg-gradient-to-br from-yellow-500/20 to-transparent rounded-2xl transition-all hover:scale-105"
        >
          <div class="bg-white dark:bg-neutral-950 rounded-[0.9rem] overflow-hidden border border-yellow-500/20 shadow-xl dark:shadow-none">
            <div class="flex items-center justify-between px-8 py-6 border-b border-neutral-100 dark:border-white/5" :class="match.winner_id === match.participant_1_id ? 'bg-yellow-500/5' : ''">
              <span class="text-sm font-black text-neutral-900 dark:text-white uppercase">{{ getParticipantName(match.participant_1) }}</span>
              <span class="text-3xl font-black text-yellow-500 italic">{{ match.scores?.participant_1 ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between px-8 py-6" :class="match.winner_id === match.participant_2_id ? 'bg-yellow-500/5' : ''">
              <span class="text-sm font-black text-neutral-900 dark:text-white uppercase">{{ getParticipantName(match.participant_2) }}</span>
              <span class="text-3xl font-black text-yellow-500 italic">{{ match.scores?.participant_2 ?? 0 }}</span>
            </div>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
