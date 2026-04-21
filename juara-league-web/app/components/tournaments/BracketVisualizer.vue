<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch, Stage } from '~/types/tournament'
import BracketNode from '~/components/BracketNode.vue'
import BracketConnector from '~/components/BracketConnector.vue'

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
  rounds.value.filter(r => r.matches.some(m => m.bracket_side === 'upper' || (!m.bracket_side && m.bracket_side !== 'grand_final')))
    .map(r => ({ ...r, matches: r.matches.filter(m => m.bracket_side === 'upper' || (!m.bracket_side && !m.group_id && m.bracket_side !== 'grand_final')) }))
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

const getRoundTitle = (round: any, isLower = false) => {
  const count = round.matches.length
  if (isLower) return `L-Round ${round.round}`
  if (count === 1) return t('match.bracket.final')
  if (count === 2) return t('match.bracket.semi_final')
  return t('match.bracket.qualifiers')
}

const calculateConnectorHeight = (roundIndex: number, matchIndex: number) => {
  const baseDistance = 184 
  return baseDistance * Math.pow(2, roundIndex)
}

watch(() => props.stage.id, () => { fetchMatches() })
onMounted(() => { fetchMatches() })

defineExpose({ fetchMatches })
</script>

<template>
  <!-- Premium Skeleton Loading State -->
  <div v-if="isLoading" class="p-20 min-w-max min-h-max flex items-start gap-32 relative overflow-hidden">
    <div v-for="r in (isDoubleElim ? 3 : 2)" :key="r" class="flex flex-col relative animate-pulse-slow">
       <!-- Round Header Skeleton -->
       <div class="mb-12 flex flex-col items-center">
         <div class="w-24 h-6 rounded-full bg-neutral-100 dark:bg-white/5 border border-neutral-200 dark:border-white/10 mb-3 shimmer"></div>
         <div class="w-32 h-4 rounded bg-neutral-50 dark:bg-white/5 shimmer"></div>
       </div>

       <!-- Nodes Skeleton -->
       <div class="flex flex-col gap-12 relative">
          <div v-for="n in (3 - r + 1)" :key="n" class="relative flex items-center">
             <div class="w-[300px] h-[140px] rounded-[1.25rem] bg-neutral-50 dark:bg-neutral-900/60 border border-neutral-200 dark:border-white/5 shimmer relative overflow-hidden">
                <div class="absolute inset-x-5 top-4 h-3 w-20 bg-neutral-200 dark:bg-white/10 rounded"></div>
                <div class="absolute inset-x-5 top-10 h-px bg-neutral-100 dark:bg-white/5"></div>
                <div class="absolute inset-x-5 top-14 h-6 w-40 bg-neutral-100 dark:bg-white/5 rounded"></div>
                <div class="absolute inset-x-5 bottom-4 h-6 w-48 bg-neutral-100 dark:bg-white/5 rounded"></div>
             </div>
             <!-- Connector Skeleton -->
             <div v-if="r < (isDoubleElim ? 3 : 2) && n === 1" class="ml-8 w-16 h-[184px] border-y-2 border-r-2 border-neutral-100 dark:border-white/5 rounded-r-2xl opacity-30"></div>
          </div>
       </div>
    </div>
  </div>

  <div v-else-if="matches.length === 0" class="py-20 text-center">
    <UIcon name="i-lucide-calendar-x" class="size-12 text-neutral-700 mx-auto mb-4" />
    <p class="text-neutral-500 font-bold uppercase tracking-widest text-sm">{{ $t('match.bracket.empty') }}</p>
  </div>

  <div v-else class="p-20 min-w-max min-h-max flex items-center gap-32 relative">
    
    <!-- Left Side: Upper and Lower Brackets -->
    <div class="flex flex-col gap-32">
      <!-- Upper Bracket Area -->
      <div class="flex items-start gap-32 relative">
        <TransitionGroup name="bracket-round" appear>
          <div 
            v-for="(r, rIndex) in (isDoubleElim ? upperBracketRounds : rounds)" 
            :key="'upper-'+r.round"
            class="flex flex-col relative"
            :style="{ transitionDelay: `${rIndex * 150}ms` }"
          >
          <!-- Round Header -->
          <div class="mb-12 flex flex-col items-center">
            <div class="px-4 py-1.5 rounded-full bg-primary-500/10 border border-primary-500/20 mb-3 block">
              <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.2em]">{{ $t('match.bracket.round') }} {{ rIndex + 1 }}</span>
            </div>
            <h3 class="text-xs font-black text-neutral-400 dark:text-white/40 uppercase tracking-[0.3em]">{{ getRoundTitle(r) }}</h3>
            <span v-if="isDoubleElim" class="text-[9px] text-primary-500 font-bold uppercase mt-1 tracking-widest">Upper Bracket</span>
          </div>

          <!-- Matches in Round -->
          <div class="flex flex-col gap-12 relative">
            <div 
              v-for="(match, mIndex) in r.matches" 
              :key="match.id"
              class="relative flex items-center"
            >
              <BracketNode 
                :match="match" 
                :bo-format="props.stage.config?.bo_format"
                @click="emit('match-click', match)"
              />

              <!-- Connector to Next Round (if not final) -->
              <div 
                v-if="rIndex < (isDoubleElim ? upperBracketRounds : rounds).length - 1 && mIndex % 2 === 0"
                class="absolute left-full top-1/2 -translate-y-1/2"
              >
                 <BracketConnector 
                   :height="calculateConnectorHeight(rIndex, mIndex)" 
                   class="ml-8"
                 />
              </div>
            </div>
          </div>
        </div>
        </TransitionGroup>
      </div>

      <!-- Lower Bracket Area -->
      <div v-if="isDoubleElim && lowerBracketRounds.length > 0" class="flex items-start gap-32 relative pt-24 border-t-2 border-dashed border-neutral-300 dark:border-white/10">
        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-6 py-2 bg-neutral-100 dark:bg-neutral-900 border border-neutral-300 dark:border-white/10 rounded-full font-black text-[10px] uppercase tracking-widest text-neutral-500">
          Lower Bracket 
        </div>

        <TransitionGroup name="bracket-round" appear>
          <div 
            v-for="(r, rIndex) in lowerBracketRounds" 
            :key="'lower-'+r.round"
            class="flex flex-col relative"
            :style="{ transitionDelay: `${rIndex * 150}ms` }"
          >
          <!-- Round Header (Lower) -->
          <div class="mb-12 flex flex-col items-center">
            <div class="px-4 py-1.5 rounded-full bg-rose-500/10 border border-rose-500/20 mb-3 block">
              <span class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]">{{ getRoundTitle(r, true) }}</span>
            </div>
            <h3 class="text-xs font-black text-neutral-400 dark:text-white/40 uppercase tracking-[0.3em]">{{ r.matches.length }} Laga</h3>
            <span class="text-[9px] text-rose-500 font-bold uppercase mt-1 tracking-widest">Elimination</span>
          </div>

          <!-- Matches in Lower Round -->
          <div class="flex flex-col gap-8 relative justify-center flex-1">
            <div 
              v-for="match in r.matches" 
              :key="match.id"
              class="relative flex items-center"
            >
              <BracketNode 
                :match="match" 
                :bo-format="props.stage.config?.bo_format"
                class="!border-rose-500/20 hover:!border-rose-500/50 hover:!bg-rose-500/5"
                @click="emit('match-click', match)"
              />
              <!-- Connectors in lower brackets are often complex to draw properly (as matches drop down). For this premium V1 we will omit rendering SVG lines in lower bracket to keep it cleanly readable. -->
            </div>
          </div>
        </div>
        </TransitionGroup>
      </div>
    </div>

    <!-- Grand Final Special Section -->
    <div v-if="grandFinalMatches.length > 0" class="flex flex-col items-center ml-12">
      <div class="mb-12 flex flex-col items-center">
        <div class="size-12 rounded-2xl bg-yellow-500/20 border border-yellow-500/30 flex items-center justify-center mb-4 shadow-[0_0_20px_rgba(234,179,8,0.2)]">
          <UIcon name="i-lucide-trophy" class="size-6 text-yellow-500" />
        </div>
        <h3 class="text-sm font-black text-yellow-500 uppercase tracking-[0.5em]">{{ $t('match.bracket.grand_final') }}</h3>
      </div>

      <div class="flex flex-col gap-8">
        <div 
          v-for="match in grandFinalMatches" 
          :key="match.id"
          class="relative group"
        >
          <!-- Glow behind grand final node -->
          <div class="absolute -inset-4 bg-yellow-500/10 blur-3xl rounded-full opacity-50 group-hover:opacity-100 transition-opacity"></div>
          
          <BracketNode 
            :match="match" 
            :bo-format="props.stage.config?.bo_format"
            class="scale-110 !border-yellow-500/30 bg-white dark:!bg-neutral-900 shadow-[0_0_40px_rgba(234,179,8,0.1)]"
            @click="emit('match-click', match)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bracket-round-enter-active {
  transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bracket-round-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.shimmer {
  position: relative;
  overflow: hidden;
}

.shimmer::after {
  content: "";
  position: absolute;
  inset: 0;
  transform: translateX(-100%);
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.03),
    rgba(251, 191, 36, 0.05),
    rgba(255, 255, 255, 0.03),
    transparent
  );
  animation: shimmer 2s infinite;
}

.dark .shimmer::after {
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.05),
    rgba(251, 191, 36, 0.08),
    rgba(255, 255, 255, 0.05),
    transparent
  );
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}

.animate-pulse-slow {
  animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}
</style>
