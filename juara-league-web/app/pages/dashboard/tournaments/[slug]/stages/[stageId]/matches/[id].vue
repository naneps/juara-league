<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch, Tournament, Stage } from '~/types/tournament'

definePageMeta({
  layout: 'dashboard'
})

const route = useRoute()
const router = useRouter()
const { slug, stageId, id: matchId } = route.params as Record<string, string>
const tournamentStore = useTournamentStore()
const toast = useToast()
const { t } = useI18n()

const match = ref<TournamentMatch | null>(null)
const isLoading = ref(true)
const isSubmitting = ref(false)

// Input state
const gameWinnerId = ref('')
const gameScoreP1 = ref<number | undefined>()
const gameScoreP2 = ref<number | undefined>()

const fetchMatch = async () => {
  try {
    match.value = await tournamentStore.fetchMatchDetail(slug, stageId, matchId)
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.message, color: 'error' })
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchMatch()
})

const scoringMethod = computed(() => match.value?.stage?.settings?.scoring_method || 'result_based')
const boNumber = computed(() => {
  if (match.value?.stage?.settings?.match_format === 'best_of') {
    return (match.value.stage.settings.win_condition * 2) - 1
  }
  return 1
})
const winCondition = computed(() => match.value?.stage?.settings?.win_condition || 1)
const nextGameNumber = computed(() => (match.value?.games?.length || 0) + 1)
const canInputGame = computed(() => match.value?.status === 'ongoing' && nextGameNumber.value <= (boNumber.value || 1) && gameWinnerId.value)

// Auto-pick winner
watch([gameScoreP1, gameScoreP2], ([s1, s2]) => {
  if (scoringMethod.value === 'score_based' && s1 !== undefined && s2 !== undefined) {
    if (s1 > s2) gameWinnerId.value = match.value?.participant_1_id || ''
    else if (s2 > s1) gameWinnerId.value = match.value?.participant_2_id || ''
  }
})

const submitGame = async () => {
  if (!match.value || !gameWinnerId.value) return
  isSubmitting.value = true
  try {
    await tournamentStore.inputGame(slug, stageId, matchId, {
      game_number: nextGameNumber.value,
      winner_id: gameWinnerId.value,
      score_p1: gameScoreP1.value,
      score_p2: gameScoreP2.value
    })
    toast.add({ title: 'Game result submitted', color: 'success' })
    gameWinnerId.value = ''
    gameScoreP1.value = undefined
    gameScoreP2.value = undefined
    await fetchMatch()
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.data?.message || e.message, color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const startMatch = async () => {
  if (!match.value) return
  isSubmitting.value = true
  try {
    await tournamentStore.updateMatch(slug, stageId, matchId, { status: 'ongoing' })
    await fetchMatch()
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.data?.message || e.message, color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const getParticipantName = (p: any) => {
  if (!p) return 'TBD'
  const participant = p.participant || p
  return participant.team?.name || participant.user?.name || 'TBD'
}

const goBack = () => router.push(`/dashboard/tournaments/${slug}/matches`)
</script>

<template>
  <div class="min-h-[calc(100vh-120px)] bg-neutral-50 dark:bg-neutral-950 rounded-[3rem] border border-neutral-200 dark:border-white/5 overflow-hidden flex flex-col relative">
    <!-- Header: Match Control Bar -->
    <header class="p-8 border-b border-neutral-200 dark:border-white/5 flex items-center justify-between bg-white/50 dark:bg-neutral-900/50 backdrop-blur-xl relative z-10">
      <div class="flex items-center gap-6">
        <UButton 
          icon="i-lucide-arrow-left" 
          variant="ghost" 
          color="neutral" 
          class="rounded-2xl hover:bg-neutral-100 dark:hover:bg-white/5"
          @click="goBack" 
        />
        <div class="space-y-1">
           <h1 class="text-2xl font-black uppercase tracking-tighter flex items-center gap-3">
             <UIcon name="i-lucide-swords" class="text-primary-500" />
             PRO JUDGE CONTROL ROOM
           </h1>
           <div class="flex items-center gap-2">
              <span class="text-[10px] font-black text-neutral-400 uppercase tracking-[0.2em]">Match #{{ match?.match_number }}</span>
              <span class="text-neutral-200 dark:text-neutral-800">•</span>
              <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.2em]">BO{{ boNumber }} Series</span>
           </div>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <div class="flex flex-col items-end mr-4">
           <p class="text-[9px] font-black text-neutral-400 uppercase tracking-widest">{{ $t('match.status') }}</p>
           <UBadge 
             :color="match?.status === 'completed' ? 'success' : (match?.status === 'ongoing' ? 'primary' : 'neutral')" 
             variant="subtle" 
             size="md" 
             class="rounded-xl font-black uppercase px-4 py-1"
           >
             {{ match?.status }}
           </UBadge>
        </div>
        <div v-if="match?.status === 'ongoing'" class="flex items-center gap-2 px-4 py-2 bg-red-500/10 rounded-2xl border border-red-500/20">
           <span class="size-2 rounded-full bg-red-500 animate-pulse"></span>
           <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">LIVE BROADCAST</span>
        </div>
      </div>
    </header>

    <div v-if="isLoading" class="flex-1 flex items-center justify-center">
       <UIcon name="i-lucide-loader-2" class="size-12 animate-spin text-primary-500" />
    </div>

    <main v-else-if="match" class="flex-1 grid grid-cols-12 gap-8 p-8 relative overflow-hidden">
      <!-- Decorative Elements -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[radial-gradient(circle_at_center,rgba(251,191,36,0.03)_0%,transparent_70%)] pointer-events-none"></div>

      <!-- Main Column: Scoreboard & Management -->
      <div class="col-span-12 lg:col-span-8 space-y-8 relative z-10">
        
        <!-- THE SCOREBOARD -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-[4rem] p-12 shadow-2xl relative group overflow-hidden">
           <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
           
           <div class="flex items-center justify-between gap-8 relative z-10">
              <!-- Team A -->
              <div class="flex-1 text-center space-y-6">
                 <div class="relative inline-block">
                    <div class="size-36 mx-auto rounded-[3rem] bg-neutral-50 dark:bg-neutral-800 border-2 border-neutral-200 dark:border-white/5 flex items-center justify-center shadow-inner transition-transform group-hover:scale-105 duration-500">
                       <UIcon name="i-lucide-shield" class="size-20 text-neutral-200 dark:text-neutral-700" />
                    </div>
                    <div v-if="match.winner_id === match.participant_1_id" class="absolute -top-4 -right-4 size-12 bg-primary-500 rounded-full border-4 border-white dark:border-neutral-900 flex items-center justify-center shadow-lg">
                       <UIcon name="i-lucide-trophy" class="size-6 text-white" />
                    </div>
                 </div>
                 <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-neutral-900 dark:text-white">{{ getParticipantName(match.participant_1) }}</h2>
                    <p class="text-[10px] font-black text-neutral-400 uppercase tracking-[0.3em] mt-1">PLAYER ONE</p>
                 </div>
                 <div class="text-9xl font-black italic tracking-tighter text-neutral-950 dark:text-white drop-shadow-2xl">
                    {{ match.scores?.participant_1 ?? 0 }}
                 </div>
              </div>

              <!-- VS Divider -->
              <div class="flex flex-col items-center gap-6 py-12">
                 <div class="h-24 w-px bg-gradient-to-t from-transparent via-neutral-200 dark:via-neutral-800 to-transparent"></div>
                 <div class="text-4xl font-black italic text-neutral-200 dark:text-neutral-800 select-none">VS</div>
                 <div class="px-6 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 text-[10px] font-black uppercase tracking-[0.4em] text-primary-500">
                    FIRST TO {{ winCondition }}
                 </div>
                 <div class="h-24 w-px bg-gradient-to-b from-transparent via-neutral-200 dark:via-neutral-800 to-transparent"></div>
              </div>

              <!-- Team B -->
              <div class="flex-1 text-center space-y-6">
                 <div class="relative inline-block">
                    <div class="size-36 mx-auto rounded-[3rem] bg-neutral-50 dark:bg-neutral-800 border-2 border-neutral-200 dark:border-white/5 flex items-center justify-center shadow-inner transition-transform group-hover:scale-105 duration-500">
                       <UIcon name="i-lucide-shield" class="size-20 text-neutral-200 dark:text-neutral-700" />
                    </div>
                    <div v-if="match.winner_id === match.participant_2_id" class="absolute -top-4 -right-4 size-12 bg-primary-500 rounded-full border-4 border-white dark:border-neutral-900 flex items-center justify-center shadow-lg">
                       <UIcon name="i-lucide-trophy" class="size-6 text-white" />
                    </div>
                 </div>
                 <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-neutral-900 dark:text-white">{{ getParticipantName(match.participant_2) }}</h2>
                    <p class="text-[10px] font-black text-neutral-400 uppercase tracking-[0.3em] mt-1">PLAYER TWO</p>
                 </div>
                 <div class="text-9xl font-black italic tracking-tighter text-neutral-950 dark:text-white drop-shadow-2xl">
                    {{ match.scores?.participant_2 ?? 0 }}
                 </div>
              </div>
           </div>
        </div>

        <!-- MANAGEMENT CONSOLE -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
           <!-- Input Panel -->
           <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-[3rem] p-8 shadow-xl">
              <div v-if="match.status === 'upcoming'">
                 <div class="text-center space-y-6 py-12">
                    <div class="size-20 mx-auto rounded-3xl bg-primary-500/10 flex items-center justify-center text-primary-500">
                       <UIcon name="i-lucide-play" class="size-10" />
                    </div>
                    <h3 class="text-xl font-black uppercase tracking-tight">{{ $t('match.start_match_title') }}</h3>
                    <p class="text-sm text-neutral-500 font-medium max-w-xs mx-auto">This match is ready to start. Click below to enable live scoring management.</p>
                    <UButton
                      color="primary"
                      size="xl"
                      block
                      class="rounded-2xl font-black uppercase tracking-widest shadow-xl shadow-primary-500/20 py-4"
                      :loading="isSubmitting"
                      @click="startMatch"
                    >
                      INITIALIZE MATCH
                    </UButton>
                 </div>
              </div>

              <div v-else-if="match.status === 'ongoing'" class="space-y-8">
                 <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-widest text-neutral-500">GAME {{ nextGameNumber }} CONSOLE</h3>
                    <UBadge color="primary" variant="solid" size="xs" class="rounded-lg animate-pulse">ACTIVE</UBadge>
                 </div>

                 <!-- Winner Buttons -->
                 <div class="space-y-4">
                    <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest text-center">SELECT GAME WINNER</p>
                    <div class="grid grid-cols-2 gap-4">
                       <button 
                         @click="gameWinnerId = match.participant_1_id || ''"
                         class="h-24 rounded-[2rem] border-2 transition-all flex flex-col items-center justify-center gap-1 group relative overflow-hidden"
                         :class="gameWinnerId === match.participant_1_id ? 'border-primary-500 bg-primary-500/10' : 'border-neutral-100 dark:border-white/5 bg-neutral-50 dark:bg-neutral-800/50 grayscale hover:grayscale-0 hover:border-primary-500/30'"
                       >
                          <span class="text-[8px] font-black uppercase text-neutral-500">CHOOSE</span>
                          <span class="text-xs font-black uppercase px-4 truncate w-full text-center">{{ getParticipantName(match.participant_1) }}</span>
                          <UIcon v-if="gameWinnerId === match.participant_1_id" name="i-lucide-check-circle" class="absolute top-3 right-3 text-primary-500" />
                       </button>

                       <button 
                         @click="gameWinnerId = match.participant_2_id || ''"
                         class="h-24 rounded-[2rem] border-2 transition-all flex flex-col items-center justify-center gap-1 group relative overflow-hidden"
                         :class="gameWinnerId === match.participant_2_id ? 'border-primary-500 bg-primary-500/10' : 'border-neutral-100 dark:border-white/5 bg-neutral-50 dark:bg-neutral-800/50 grayscale hover:grayscale-0 hover:border-primary-500/30'"
                       >
                          <span class="text-[8px] font-black uppercase text-neutral-500">CHOOSE</span>
                          <span class="text-xs font-black uppercase px-4 truncate w-full text-center">{{ getParticipantName(match.participant_2) }}</span>
                          <UIcon v-if="gameWinnerId === match.participant_2_id" name="i-lucide-check-circle" class="absolute top-3 right-3 text-primary-500" />
                       </button>
                    </div>
                 </div>

                 <!-- Score Inputs -->
                 <div v-if="scoringMethod === 'score_based'" class="space-y-4">
                    <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest text-center">INPUT GAME POINTS</p>
                    <div class="grid grid-cols-2 gap-6">
                       <div class="space-y-2">
                          <p class="text-[9px] font-black text-neutral-400 uppercase tracking-widest ml-4">PTS P1</p>
                          <UInput v-model.number="gameScoreP1" type="number" placeholder="0" size="xl" :ui="{ rounded: 'rounded-2xl', base: 'text-center font-black italic bg-neutral-50 dark:bg-neutral-800/50 h-16 text-2xl' }" />
                       </div>
                       <div class="space-y-2">
                          <p class="text-[9px] font-black text-neutral-400 uppercase tracking-widest ml-4">PTS P2</p>
                          <UInput v-model.number="gameScoreP2" type="number" placeholder="0" size="xl" :ui="{ rounded: 'rounded-2xl', base: 'text-center font-black italic bg-neutral-50 dark:bg-neutral-800/50 h-16 text-2xl' }" />
                       </div>
                    </div>
                 </div>

                 <UButton
                   color="primary"
                   block
                   size="xl"
                   class="rounded-3xl font-black uppercase tracking-widest py-6 shadow-xl shadow-primary-500/20"
                   :loading="isSubmitting"
                   :disabled="!canInputGame"
                   @click="submitGame"
                 >
                   SUBMIT GAME {{ nextGameNumber }} RESULT
                 </UButton>
              </div>

              <div v-else class="text-center py-20 space-y-6">
                 <div class="size-20 mx-auto rounded-full bg-green-500/10 flex items-center justify-center text-green-500">
                    <UIcon name="i-lucide-check-circle-2" class="size-12" />
                 </div>
                 <h3 class="text-2xl font-black uppercase tracking-tight">MATCH CONCLUDED</h3>
                 <p class="text-sm text-neutral-500 font-medium">All games have been processed. The winner has been advanced in the bracket.</p>
                 <UButton
                   color="neutral"
                   variant="ghost"
                   class="font-black uppercase tracking-widest"
                   @click="goBack"
                 >
                   RETURN TO MATCH LIST
                 </UButton>
              </div>
           </div>

           <!-- Rules & Info -->
           <div class="bg-neutral-900 border border-white/5 rounded-[3rem] p-8 text-neutral-400 space-y-6 flex flex-col justify-center">
              <div class="space-y-4">
                 <div class="flex items-center gap-3">
                    <UIcon name="i-lucide-info" class="text-primary-500" />
                    <span class="text-xs font-black uppercase tracking-widest text-white">Judge Guidelines</span>
                 </div>
                 <ul class="space-y-4 text-[10px] font-medium leading-relaxed uppercase tracking-wider">
                    <li class="flex gap-3">
                       <span class="text-primary-500">01.</span>
                       <span>Inputting a game result will automatically update the overall match score.</span>
                    </li>
                    <li class="flex gap-3">
                       <span class="text-primary-500">02.</span>
                       <span>Once the win condition (Race to {{ winCondition }}) is met, the match status will change to Completed.</span>
                    </li>
                    <li class="flex gap-3">
                       <span class="text-primary-500">03.</span>
                       <span>Winner advancement to the next bracket node is automated upon match completion.</span>
                    </li>
                 </ul>
              </div>
              
              <div class="pt-6 border-t border-white/5 space-y-3">
                 <p class="text-[9px] font-black uppercase tracking-widest">Scoring Method</p>
                 <div class="flex gap-2">
                    <UBadge color="primary" variant="subtle" size="xs" class="font-black uppercase">{{ scoringMethod.replace('_', ' ') }}</UBadge>
                    <UBadge color="neutral" variant="subtle" size="xs" class="font-black uppercase">BO{{ boNumber }}</UBadge>
                 </div>
              </div>
           </div>
        </div>
      </div>

      <!-- Side Column: History & Log -->
      <div class="col-span-12 lg:col-span-4 space-y-8 relative z-10">
        <!-- GAME LOG -->
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-[3rem] p-8 shadow-xl flex flex-col h-[700px]">
           <div class="flex items-center justify-between mb-8">
              <h3 class="text-xs font-black uppercase tracking-[0.3em] text-neutral-500">LIVE GAME LOG</h3>
              <div class="size-2 rounded-full bg-primary-500 shadow-[0_0_8px_rgba(251,191,36,0.5)]"></div>
           </div>

           <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-4">
              <div v-if="!match.games?.length" class="h-full flex flex-col items-center justify-center text-center opacity-30 grayscale">
                 <UIcon name="i-lucide-terminal" class="size-16 mb-4" />
                 <p class="text-[10px] font-black uppercase tracking-widest">No games recorded yet</p>
              </div>

              <div 
                v-for="game in match.games" 
                :key="game.id"
                class="p-6 bg-neutral-50 dark:bg-neutral-800/50 rounded-3xl border border-neutral-200 dark:border-white/5 relative group overflow-hidden"
              >
                 <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-3">
                       <div class="size-8 rounded-xl bg-white dark:bg-neutral-950 flex items-center justify-center font-black italic text-xs text-primary-500 border border-neutral-200 dark:border-white/5">
                          {{ game.game_number }}
                       </div>
                       <div class="space-y-0.5">
                          <p class="text-[8px] font-black text-neutral-400 uppercase tracking-widest">WINNER</p>
                          <p class="text-[10px] font-black uppercase tracking-tight text-neutral-900 dark:text-white">{{ getParticipantName(game.winner) }}</p>
                       </div>
                    </div>
                    <div class="text-right">
                       <p class="text-[8px] font-black text-neutral-400 uppercase tracking-widest">RESULT</p>
                       <p class="text-lg font-black italic tracking-tighter text-neutral-950 dark:text-white">
                          {{ game.score_p1 ?? 0 }} - {{ game.score_p2 ?? 0 }}
                       </p>
                    </div>
                 </div>
              </div>
           </div>
           
           <div class="mt-8 pt-8 border-t border-neutral-100 dark:border-white/5 space-y-4">
              <p class="text-[9px] font-black text-neutral-400 uppercase tracking-widest">ADVANCED ACTIONS</p>
              <div class="grid grid-cols-2 gap-3">
                 <UButton color="neutral" variant="soft" block size="sm" icon="i-lucide-history" class="rounded-xl font-bold uppercase text-[9px]">{{ $t('match.history') }}</UButton>
                 <UButton color="red" variant="soft" block size="sm" icon="i-lucide-alert-octagon" class="rounded-xl font-bold uppercase text-[9px]">{{ $t('match.report') }}</UButton>
              </div>
           </div>
        </div>
      </div>
    </main>

    <!-- Footer Status Bar -->
    <footer class="p-6 border-t border-neutral-200 dark:border-white/5 bg-white/50 dark:bg-neutral-900/50 flex justify-between items-center relative z-10">
       <span class="text-[9px] font-bold text-neutral-300 dark:text-neutral-700 uppercase tracking-[0.5em]">JUARA LEAGUE PRO JUDGE V1.0 // BROADCAST STABLE</span>
       <div class="flex items-center gap-6">
          <div class="flex items-center gap-2">
             <div class="size-1.5 rounded-full bg-green-500"></div>
             <span class="text-[9px] font-black text-neutral-400 uppercase">Latency: 24ms</span>
          </div>
          <div class="flex items-center gap-2">
             <div class="size-1.5 rounded-full bg-green-500"></div>
             <span class="text-[9px] font-black text-neutral-400 uppercase">Sync: Active</span>
          </div>
       </div>
    </footer>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(var(--color-primary-500), 0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--color-primary-500), 0.4);
}
</style>
