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
  rounds.value.filter(r => r.matches.some(m => m.bracket_side === 'upper' || !m.bracket_side))
    .map(r => ({ ...r, matches: r.matches.filter(m => m.bracket_side === 'upper' || (!m.bracket_side && !m.group_id)) }))
    .filter(r => r.matches.length > 0)
)

const grandFinalMatches = computed(() =>
  matches.value.filter(m => m.bracket_side === 'grand_final')
)

const getRoundTitle = (round: any) => {
  const count = round.matches.length
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
  <div v-if="isLoading" class="py-20 flex justify-center">
    <UIcon name="i-lucide-loader-2" class="size-8 text-primary-500 animate-spin" />
  </div>

  <div v-else-if="matches.length === 0" class="py-20 text-center">
    <UIcon name="i-lucide-calendar-x" class="size-12 text-neutral-700 mx-auto mb-4" />
    <p class="text-neutral-500 font-bold uppercase tracking-widest text-sm">{{ $t('match.bracket.empty') }}</p>
  </div>

  <div v-else class="p-20 min-w-max min-h-max flex items-start gap-32 relative">
    <!-- Rounds Loop -->
    <TransitionGroup name="bracket-round" appear>
      <div 
        v-for="(r, rIndex) in (isDoubleElim ? upperBracketRounds : rounds)" 
        :key="r.round"
        class="flex flex-col relative"
        :style="{ transitionDelay: `${rIndex * 150}ms` }"
      >
      <!-- Round Header -->
      <div class="mb-12 flex flex-col items-center">
        <div class="px-4 py-1.5 rounded-full bg-primary-500/10 border border-primary-500/20 mb-3">
          <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.2em]">{{ $t('match.bracket.round') }} {{ rIndex + 1 }}</span>
        </div>
        <h3 class="text-xs font-black text-white/40 uppercase tracking-[0.3em]">{{ getRoundTitle(r) }}</h3>
      </div>

      <!-- Matches in Round -->
      <div class="flex flex-col gap-12 relative">
        <div 
          v-for="(match, mIndex) in r.matches" 
          :key="match.id"
          class="relative flex items-center"
        >
          <!-- Match Node Component -->
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

    <!-- Grand Final Special Section -->
    <div v-if="grandFinalMatches.length > 0" class="flex flex-col items-center ml-20">
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
            class="scale-110 !border-yellow-500/30 !bg-neutral-900 shadow-[0_0_40px_rgba(234,179,8,0.1)]"
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
</style>
