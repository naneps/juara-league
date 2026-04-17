<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch, Stage } from '~/types/tournament'

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

// Group matches by round
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
const isRoundRobin = computed(() => props.stage.type === 'round_robin')

// Separation logic
const upperBracketRounds = computed(() =>
  rounds.value.filter(r => r.matches.some(m => m.bracket_side === 'upper' || !m.bracket_side))
    .map(r => ({ ...r, matches: r.matches.filter(m => m.bracket_side === 'upper' || (!m.bracket_side && !m.group_id)) }))
    .filter(r => r.matches.length > 0)
)

const lowerBracketRounds = computed(() =>
  rounds.value.filter(r => r.matches.some(m => m.bracket_side === 'lower'))
    .map(r => ({ ...r, matches: r.matches.filter(m => m.bracket_side === 'lower') }))
    .filter(r => r.matches.length > 0)
)

const grandFinalMatches = computed(() =>
  matches.value.filter(m => m.bracket_side === 'grand_final')
)

const getParticipantName = (p: any) => {
  if (!p) return 'TBD'
  return p.team?.name || p.user?.name || 'TBD'
}

const matchStatusColor = (status: string) => {
  const map: Record<string, string> = {
    upcoming: 'neutral',
    ongoing: 'primary',
    completed: 'success',
    bye: 'warning',
  }
  return map[status] || 'neutral'
}

const roundLabel = (round: number, index: number, total: number) => {
  if (total === 1) return 'Final'
  if (index === total - 1) return 'Final'
  if (index === total - 2) return 'Semifinal'
  if (index === total - 3) return 'Quarterfinal'
  return `Round ${round}`
}

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
    <p class="text-neutral-500 font-bold uppercase tracking-widest text-sm">Belum ada match di babak ini</p>
    <p class="text-neutral-600 text-[10px] mt-2 font-bold uppercase tracking-widest">Mulai babak untuk generate bracket.</p>
  </div>

  <div v-else class="space-y-20">
    <!-- Bracket Section (Horizontal) -->
    <div class="relative">
      <!-- Upper / Single Bracket -->
      <div class="mb-6 flex items-center gap-4">
        <div class="h-px w-8 bg-primary-500/50"></div>
        <h3 class="text-[10px] font-black text-primary-400 uppercase tracking-[0.4em]">{{ isDoubleElim ? 'Winners Bracket' : 'Tournament Bracket' }}</h3>
      </div>
      
      <div class="flex gap-12 overflow-x-auto pb-10 scrollbar-hide items-stretch px-4">
        <div
          v-for="(r, rIndex) in (isDoubleElim ? upperBracketRounds : rounds)"
          :key="r.round"
          class="flex flex-col shrink-0"
          :class="[
            rIndex === 0 ? '' : 'justify-around'
          ]"
        >
          <!-- Round Label -->
          <div class="mb-10 text-center">
            <span class="text-[10px] font-black text-neutral-600 uppercase tracking-[0.3em] block mb-1">Round {{ rIndex + 1 }}</span>
            <span class="text-xs font-black text-white uppercase tracking-wider">{{ roundLabel(r.round, rIndex, (isDoubleElim ? upperBracketRounds : rounds).length) }}</span>
          </div>

          <div class="flex flex-col gap-8">
            <div
              v-for="(match, mIndex) in r.matches"
              :key="match.id"
              class="relative"
            >
              <!-- Connector Lines (Visual Only using CSS) -->
              <div 
                v-if="rIndex < (isDoubleElim ? upperBracketRounds : rounds).length - 1"
                class="absolute -right-12 top-1/2 -translate-y-1/2 w-12 h-px bg-neutral-800"
              ></div>
              
              <button
                @click="emit('match-click', match)"
                class="w-64 group relative bg-neutral-900 border border-white/5 rounded-xl transition-all hover:border-primary-500/50 hover:shadow-2xl hover:shadow-primary-500/10 overflow-hidden"
              >
                <!-- VS Indicator -->
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 size-6 rounded-full bg-neutral-950 border border-white/5 flex items-center justify-center z-10">
                   <span class="text-[8px] font-black text-neutral-700 italic">VS</span>
                </div>

                <!-- Part 1 -->
                <div 
                  class="flex items-center justify-between px-4 py-3 border-b border-white/5 transition-colors"
                  :class="[match.winner_id === match.participant_1_id ? 'bg-primary-500/5' : '']"
                >
                  <div class="flex items-center gap-3 overflow-hidden">
                    <div class="size-1.5 rounded-full" :class="match.winner_id === match.participant_1_id ? 'bg-primary-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-neutral-800'"></div>
                    <span class="text-[11px] font-black truncate max-w-[140px]" :class="match.winner_id === match.participant_1_id ? 'text-white' : 'text-neutral-500'">
                      {{ getParticipantName(match.participant_1) }}
                    </span>
                  </div>
                  <span class="text-xs font-black" :class="match.winner_id === match.participant_1_id ? 'text-primary-400' : 'text-neutral-700'">
                    {{ match.scores?.participant_1 ?? 0 }}
                  </span>
                </div>

                <!-- Part 2 -->
                <div 
                  class="flex items-center justify-between px-4 py-3 transition-colors"
                  :class="[match.winner_id === match.participant_2_id ? 'bg-primary-500/5' : '']"
                >
                  <div class="flex items-center gap-3 overflow-hidden">
                    <div class="size-1.5 rounded-full" :class="match.winner_id === match.participant_2_id ? 'bg-primary-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-neutral-800'"></div>
                    <span class="text-[11px] font-black truncate max-w-[140px]" :class="match.winner_id === match.participant_2_id ? 'text-white' : 'text-neutral-500'">
                      {{ getParticipantName(match.participant_2) }}
                    </span>
                  </div>
                  <span class="text-xs font-black" :class="match.winner_id === match.participant_2_id ? 'text-primary-400' : 'text-neutral-700'">
                    {{ match.scores?.participant_2 ?? 0 }}
                  </span>
                </div>

                <!-- Footer / Status -->
                <div class="px-4 py-2 bg-neutral-950 flex items-center justify-between border-t border-white/5 opacity-0 group-hover:opacity-100 transition-opacity">
                  <span class="text-[8px] font-bold text-neutral-600 uppercase tracking-widest">M#{{ match.match_number }}</span>
                  <UBadge :color="matchStatusColor(match.status) as any" variant="subtle" size="xs" class="text-[8px] px-2 py-0">
                    {{ match.status.toUpperCase() }}
                  </UBadge>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lower Bracket (if exists) -->
    <div v-if="isDoubleElim && lowerBracketRounds.length > 0" class="relative">
      <div class="mb-6 flex items-center gap-4">
        <div class="h-px w-8 bg-amber-500/50"></div>
        <h3 class="text-[10px] font-black text-amber-400 uppercase tracking-[0.4em]">Losers Bracket</h3>
      </div>

      <div class="flex gap-12 overflow-x-auto pb-10 scrollbar-hide px-4">
        <div
          v-for="(r, rIndex) in lowerBracketRounds"
          :key="r.round"
          class="flex flex-col shrink-0 gap-8"
        >
          <div class="mb-6 text-center">
             <span class="text-[9px] font-black text-neutral-600 uppercase tracking-widest">LR{{ rIndex + 1 }}</span>
          </div>

          <button
            v-for="match in r.matches"
            :key="match.id"
            @click="emit('match-click', match)"
            class="w-64 group relative bg-neutral-900/40 border border-white/5 rounded-xl hover:border-amber-500/30 transition-all text-left"
          >
             <!-- Part 1 -->
             <div class="flex items-center justify-between px-4 py-2.5">
               <span class="text-[10px] font-bold truncate max-w-[150px]" :class="match.winner_id === match.participant_1_id ? 'text-amber-400' : 'text-neutral-500'">
                 {{ getParticipantName(match.participant_1) }}
               </span>
               <span class="text-xs font-black text-neutral-600">{{ match.scores?.participant_1 ?? '-' }}</span>
             </div>
             <!-- Part 2 -->
             <div class="flex items-center justify-between px-4 py-2.5 bg-white/5">
               <span class="text-[10px] font-bold truncate max-w-[150px]" :class="match.winner_id === match.participant_2_id ? 'text-amber-400' : 'text-neutral-500'">
                 {{ getParticipantName(match.participant_2) }}
               </span>
               <span class="text-xs font-black text-neutral-600">{{ match.scores?.participant_2 ?? '-' }}</span>
             </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Grand Final -->
    <div v-if="isDoubleElim && grandFinalMatches.length > 0" class="flex flex-col items-center pt-10">
      <div class="size-1 bg-yellow-400 rounded-full mb-4 shadow-[0_0_15px_rgba(250,204,21,0.5)]"></div>
      <h3 class="text-[11px] font-black text-yellow-500 uppercase tracking-[0.5em] mb-10">Grand Final</h3>
      
      <div class="flex flex-col gap-6 md:flex-row">
        <button
          v-for="match in grandFinalMatches"
          :key="match.id"
          @click="emit('match-click', match)"
          class="w-80 group relative bg-neutral-950 border border-yellow-500/20 rounded-2xl p-1 transition-all hover:shadow-[0_0_30px_rgba(250,204,21,0.1)]"
        >
          <div class="bg-neutral-900 rounded-[0.9rem] overflow-hidden">
             <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
               <span class="text-xs font-black" :class="match.winner_id === match.participant_1_id ? 'text-white' : 'text-neutral-500'">{{ getParticipantName(match.participant_1) }}</span>
               <span class="text-xl font-black text-yellow-500">{{ match.scores?.participant_1 ?? 0 }}</span>
             </div>
             <div class="flex items-center justify-between px-6 py-4">
               <span class="text-xs font-black" :class="match.winner_id === match.participant_2_id ? 'text-white' : 'text-neutral-500'">{{ getParticipantName(match.participant_2) }}</span>
               <span class="text-xl font-black text-yellow-500">{{ match.scores?.participant_2 ?? 0 }}</span>
             </div>
          </div>
          <div class="px-6 py-3 flex items-center justify-between">
            <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">Championship Match</span>
            <UBadge color="warning" variant="solid" size="xs" class="font-black">{{ match.status }}</UBadge>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
