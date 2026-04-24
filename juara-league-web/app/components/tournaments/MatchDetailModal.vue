<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch, Game } from '~/types/tournament'

const { t } = useI18n()

const props = defineProps<{
  tournamentSlug: string
  stageId: string
  boFormat: string
  isStaff?: boolean
  isOwner?: boolean
}>()

const model = defineModel<boolean>()
const emit = defineEmits(['updated'])

const selectedMatch = ref<TournamentMatch | null>(null)
const isLoadingDetail = ref(false)
const isSubmitting = ref(false)
const tournamentStore = useTournamentStore()

const maxGames = computed(() => {
  if (selectedMatch.value?.stage?.settings?.match_format === 'best_of') {
    return (selectedMatch.value.stage.settings.win_condition * 2) - 1
  }
  if (selectedMatch.value?.stage?.settings?.match_format === 'single_game') return 1
  if (selectedMatch.value?.stage?.settings?.match_format === 'leg') return 2
  
  const map: Record<string, number> = { bo1: 1, bo3: 3, bo5: 5, bo7: 7 }
  return map[props.boFormat] || 1
})

const winCondition = computed(() => {
  if (selectedMatch.value?.stage?.settings?.match_format === 'best_of') {
    return selectedMatch.value.stage.settings.win_condition
  }
  return 1
})

const getParticipantName = (p: any) => {
  if (!p) return t('match.bracket.tbd')
  const participant = p.participant || p // Handle MatchParticipant pivot or direct Participant
  return participant.team?.name || participant.user?.name || t('match.bracket.tbd')
}

const open = (match: TournamentMatch) => {
  selectedMatch.value = match
  model.value = true
  loadMatchDetail()
}

const loadMatchDetail = async () => {
  if (!selectedMatch.value) return
  isLoadingDetail.value = true
  try {
    selectedMatch.value = await tournamentStore.fetchMatchDetail(
      props.tournamentSlug, props.stageId, selectedMatch.value.id
    )
  } catch (e) {
    console.error(e)
  } finally {
    isLoadingDetail.value = false
  }
}

// Start match
const startMatch = async () => {
  if (!selectedMatch.value) return
  isSubmitting.value = true
  try {
    await tournamentStore.updateMatch(props.tournamentSlug, props.stageId, selectedMatch.value.id, { status: 'ongoing' })
    useToast().add({ title: t('match.toast.start_success'), description: t('match.toast.start_success_desc'), color: 'success' })
    await loadMatchDetail()
    emit('updated')
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('common.error'), color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

// Input game
const gameWinnerId = ref('')
const gameScoreP1 = ref<number | undefined>()
const gameScoreP2 = ref<number | undefined>()

const nextGameNumber = computed(() => {
  if (!selectedMatch.value?.games) return 1
  return (selectedMatch.value.games.length || 0) + 1
})

const canInputGame = computed(() => {
  return selectedMatch.value?.status === 'ongoing' && nextGameNumber.value <= maxGames.value && gameWinnerId.value
})

const submitGame = async () => {
  if (!selectedMatch.value || !gameWinnerId.value) return
  isSubmitting.value = true
  try {
    const result = await tournamentStore.inputGame(
      props.tournamentSlug, props.stageId, selectedMatch.value.id,
      {
        game_number: nextGameNumber.value,
        winner_id: gameWinnerId.value,
        score_p1: gameScoreP1.value,
        score_p2: gameScoreP2.value,
      }
    )
    useToast().add({ title: result.message, color: 'success' })
    gameWinnerId.value = ''
    gameScoreP1.value = undefined
    gameScoreP2.value = undefined
    await loadMatchDetail()
    emit('updated')
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('common.error'), color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const matchStatusColor = (status: string) => {
  const map: Record<string, string> = { upcoming: 'neutral', ongoing: 'primary', completed: 'success', bye: 'warning' }
  return map[status] || 'neutral'
}

defineExpose({ open })
</script>

<template>
  <UModal 
    v-model:open="model" 
    :ui="{ 
      content: 'sm:max-w-xl w-full rounded-[2.5rem] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/10 shadow-[0_0_50px_rgba(0,0,0,0.1)] dark:shadow-[0_0_50px_rgba(0,0,0,0.5)] overflow-hidden',
      overlay: 'bg-black/40 dark:bg-black/80 backdrop-blur-sm',
    }"
  >
    <template #body>
      <div v-if="!selectedMatch" class="p-8 text-center text-neutral-500 relative">
        <button 
          @click="model = false"
          class="absolute top-6 right-6 z-20 size-8 flex items-center justify-center rounded-full bg-neutral-100 dark:bg-white/5 hover:bg-neutral-200 dark:hover:bg-white/10 text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-all border border-neutral-200 dark:border-white/5"
        >
          <UIcon name="i-lucide-x" class="size-4" />
        </button>
        {{ $t('match.no_match_selected') }}
      </div>
      
      <div v-else class="p-8 space-y-8 relative overflow-x-hidden custom-scrollbar max-h-[85vh]">
        <!-- Close Button -->
        <button 
          @click="model = false"
          class="absolute top-6 right-6 z-20 size-8 flex items-center justify-center rounded-full bg-neutral-100 dark:bg-white/5 hover:bg-neutral-200 dark:hover:bg-white/10 text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-all border border-neutral-200 dark:border-white/5"
        >
          <UIcon name="i-lucide-x" class="size-4" />
        </button>

        <!-- Adaptive Background Glows -->
        <div class="absolute -top-32 -left-32 size-96 bg-primary-500/5 dark:bg-primary-500/10 blur-[120px] pointer-events-none rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-80 bg-primary-500/5 dark:bg-primary-500/5 blur-[120px] pointer-events-none"></div>

        <!-- Match Header -->
        <div class="flex items-center justify-between relative z-10">
          <div class="flex items-center gap-4">
            <div class="size-12 rounded-2xl bg-neutral-50 dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-white/5 shadow-inner">
              <UIcon name="i-lucide-swords" class="size-6 text-primary-500 dark:text-primary-400" />
            </div>
            <div>
              <h3 class="text-sm font-black text-neutral-950 dark:text-white uppercase tracking-wider flex items-center gap-2">
                Match #{{ selectedMatch.match_number }}
                <span class="text-neutral-300 dark:text-neutral-700">/</span>
                <span class="text-primary-500">
                  <template v-if="selectedMatch.stage?.settings?.match_format === 'best_of'">
                    BO{{ (selectedMatch.stage.settings.win_condition * 2) - 1 }}
                  </template>
                  <template v-else-if="selectedMatch.stage?.settings?.match_format === 'single_game'">
                    SINGLE
                  </template>
                  <template v-else>
                    {{ boFormat.toUpperCase() }}
                  </template>
                </span>
              </h3>
              <p class="text-[10px] text-neutral-500 font-bold uppercase tracking-widest mt-1">{{ $t('match.round') }} {{ selectedMatch.round }}</p>
            </div>
          </div>
          <UBadge :color="matchStatusColor(selectedMatch.status) as any" variant="solid" size="md" class="rounded-xl font-black uppercase tracking-widest px-4 py-1.5 shadow-lg">
            {{ selectedMatch.status }}
          </UBadge>
        </div>

        <!-- VS Pro Banner -->
        <div class="relative group">
          <div class="absolute inset-0 bg-primary-500/5 blur-xl group-hover:bg-primary-500/10 transition-all rounded-3xl"></div>
          <div class="relative bg-neutral-50/50 dark:bg-neutral-950/50 border border-neutral-200 dark:border-white/5 rounded-3xl p-8 overflow-hidden">
            <div class="flex items-center justify-between gap-6">
              <!-- P1 -->
              <div class="flex-1 text-center space-y-3">
                <div class="size-16 mx-auto rounded-3xl bg-neutral-100 dark:bg-neutral-900 flex items-center justify-center border border-neutral-200 dark:border-white/5 shadow-inner">
                  <UIcon name="i-lucide-shield" class="size-8 text-neutral-400 dark:text-neutral-800" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-1">{{ $t('match.team_a') }}</p>
                  <p class="text-xs font-black text-neutral-900 dark:text-white uppercase">{{ getParticipantName(selectedMatch.participant_1) }}</p>
                </div>
                <p class="text-5xl font-black italic tracking-tighter" :class="selectedMatch.winner_id === selectedMatch.participant_1_id ? 'text-primary-500 dark:text-primary-400' : 'text-neutral-200 dark:text-neutral-800'">
                  {{ selectedMatch.scores?.participant_1 ?? 0 }}
                </p>
              </div>

              <!-- VS -->
              <div class="flex flex-col items-center gap-2">
                 <div class="h-10 w-px bg-gradient-to-t from-transparent via-neutral-200 dark:via-neutral-800 to-transparent"></div>
                 <span class="text-sm font-black text-neutral-400 dark:text-neutral-700 italic">VS</span>
                 <div class="h-10 w-px bg-gradient-to-b from-transparent via-neutral-200 dark:via-neutral-800 to-transparent"></div>
              </div>

              <!-- P2 -->
              <div class="flex-1 text-center space-y-3">
                <div class="size-16 mx-auto rounded-3xl bg-neutral-100 dark:bg-neutral-900 flex items-center justify-center border border-neutral-200 dark:border-white/5 shadow-inner">
                  <UIcon name="i-lucide-shield" class="size-8 text-neutral-400 dark:text-neutral-800" />
                </div>
                <div>
                  <p class="text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest mb-1">{{ $t('match.team_b') }}</p>
                  <p class="text-xs font-black text-neutral-900 dark:text-white uppercase">{{ getParticipantName(selectedMatch.participant_2) }}</p>
                </div>
                <p class="text-5xl font-black italic tracking-tighter" :class="selectedMatch.winner_id === selectedMatch.participant_2_id ? 'text-primary-500 dark:text-primary-400' : 'text-neutral-200 dark:text-neutral-800'">
                  {{ selectedMatch.scores?.participant_2 ?? 0 }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Match Timeline / Games Progress -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-[0.2em]">{{ $t('match.match_progress') }}</h4>
            <span class="text-[9px] font-black text-primary-500 uppercase tracking-widest">{{ $t('match.first_to') }} {{ winCondition }}</span>
          </div>
          
          <div class="flex gap-2">
            <div 
              v-for="i in maxGames" 
              :key="i"
              class="h-1.5 flex-1 rounded-full overflow-hidden transition-all duration-500"
              :class="[
                selectedMatch.games?.[i-1] ? 'bg-primary-500/10 dark:bg-primary-500/20' : 'bg-neutral-100 dark:bg-neutral-800'
              ]"
            >
              <div 
                v-if="selectedMatch.games?.[i-1]"
                class="h-full bg-primary-500 shadow-[0_0_10px_rgba(251,191,36,0.3)]"
              ></div>
            </div>
          </div>

          <!-- Game History -->
          <div v-if="selectedMatch.games && selectedMatch.games.length > 0" class="grid grid-cols-1 gap-2 pt-2">
            <div
              v-for="game in selectedMatch.games"
              :key="game.id"
              class="flex items-center justify-between px-4 py-3 bg-neutral-50/50 dark:bg-neutral-950/50 rounded-2xl border border-neutral-200 dark:border-white/5 group hover:border-primary-500/20 transition-all"
            >
              <div class="flex items-center gap-3">
                <div class="size-6 rounded-lg bg-white dark:bg-neutral-900 flex items-center justify-center text-[10px] font-black text-neutral-400 dark:text-neutral-500 border border-neutral-200 dark:border-white/5">
                  {{ game.game_number }}
                </div>
                <span class="text-[10px] font-black text-neutral-900 dark:text-white uppercase tracking-widest">{{ $t('match.game_win') }}</span>
              </div>
              
              <div class="flex items-center gap-8">
                <span :class="['text-sm font-black italic', game.winner_id === selectedMatch.participant_1_id ? 'text-primary-500' : 'text-neutral-300 dark:text-neutral-700']">
                  {{ game.score_p1 ?? '-' }}
                </span>
                <span :class="['text-sm font-black italic', game.winner_id === selectedMatch.participant_2_id ? 'text-primary-500' : 'text-neutral-300 dark:text-neutral-700']">
                  {{ game.score_p2 ?? '-' }}
                </span>
              </div>

              <div class="flex items-center gap-2">
                <UBadge v-if="game.status === 'corrected'" color="warning" variant="subtle" size="xs" class="text-[8px] font-black uppercase">CORRECTED</UBadge>
                <div class="size-6 rounded-full flex items-center justify-center bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5">
                   <UIcon name="i-lucide-check" class="size-3.5" :class="game.status === 'completed' ? 'text-primary-500' : 'text-neutral-200 dark:text-neutral-800'" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Winner Celebration -->
        <div v-if="selectedMatch.status === 'completed' && selectedMatch.winner" class="relative group">
           <div class="absolute inset-0 bg-primary-500/10 blur-2xl rounded-3xl animate-pulse"></div>
           <div class="relative bg-gradient-to-br from-primary-500/20 to-primary-500/5 border border-primary-500/20 rounded-3xl p-6 text-center">
              <UIcon name="i-lucide-trophy" class="size-8 text-primary-500 mb-2 bounce" />
              <p class="text-[10px] font-black text-primary-500 uppercase tracking-[0.4em] mb-1">{{ $t('match.match_champion') }}</p>
              <p class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tighter">{{ getParticipantName(selectedMatch.winner) }}</p>
           </div>
        </div>

        <!-- Staff Controls -->
        <div v-if="isStaff" class="pt-4 border-t border-neutral-100 dark:border-white/5 space-y-6">
          <div class="flex items-start gap-3 p-4 bg-primary-500/5 rounded-2xl border border-primary-500/10">
            <UIcon name="i-lucide-info" class="size-5 text-primary-500 shrink-0" />
            <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 leading-relaxed">
              <span class="text-primary-500 dark:text-primary-400 uppercase tracking-widest font-black block mb-1">{{ $t('match.pro_judge_info') }}:</span>
              {{ $t('match.pro_judge_desc') }}
            </p>
          </div>

          <!-- Actions -->
          <div v-if="selectedMatch.status === 'upcoming'">
            <UButton
              color="primary"
              variant="solid"
              block
              size="xl"
              class="rounded-2xl font-black uppercase tracking-widest shadow-lg dark:shadow-[0_0_20px_rgba(251,191,36,0.3)]"
              :loading="isSubmitting"
              @click="startMatch"
            >
              {{ $t('match.start_live') }}
            </UButton>
          </div>

          <div v-if="selectedMatch.status === 'ongoing'" class="space-y-6">
             <div class="space-y-4">
                <p class="text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest text-center">{{ $t('match.determine_winner') }} {{ nextGameNumber }}</p>
                <div class="grid grid-cols-2 gap-4">
                   <button 
                     @click="gameWinnerId = selectedMatch.participant_1_id || ''"
                     class="group relative h-24 rounded-3xl border-2 transition-all overflow-hidden"
                     :class="gameWinnerId === selectedMatch.participant_1_id ? 'border-primary-500 bg-primary-500/10 scale-95' : 'border-neutral-100 dark:border-white/5 bg-neutral-50 dark:bg-neutral-800/50 grayscale hover:grayscale-0'"
                   >
                     <div class="flex flex-col items-center gap-1">
                        <span class="text-[9px] font-black uppercase text-neutral-400 dark:text-neutral-500">{{ $t('match.pick') }}</span>
                        <span class="text-xs font-black text-neutral-900 dark:text-white uppercase px-4 truncate">{{ getParticipantName(selectedMatch.participant_1) }}</span>
                     </div>
                   </button>
                   <button 
                     @click="gameWinnerId = selectedMatch.participant_2_id || ''"
                     class="group relative h-24 rounded-3xl border-2 transition-all overflow-hidden"
                     :class="gameWinnerId === selectedMatch.participant_2_id ? 'border-primary-500 bg-primary-500/10 scale-95' : 'border-neutral-100 dark:border-white/5 bg-neutral-50 dark:bg-neutral-800/50 grayscale hover:grayscale-0'"
                   >
                     <div class="flex flex-col items-center gap-1">
                        <span class="text-[9px] font-black uppercase text-neutral-400 dark:text-neutral-500">{{ $t('match.pick') }}</span>
                        <span class="text-xs font-black text-neutral-900 dark:text-white uppercase px-4 truncate">{{ getParticipantName(selectedMatch.participant_2) }}</span>
                     </div>
                   </button>
                </div>
             </div>

             <div class="grid grid-cols-2 gap-4">
                <UInput v-model.number="gameScoreP1" type="number" :placeholder="$t('match.score_p1')" size="xl" :ui="{ rounded: 'rounded-2xl', base: 'bg-white dark:bg-neutral-900' }" />
                <UInput v-model.number="gameScoreP2" type="number" :placeholder="$t('match.score_p2')" size="xl" :ui="{ rounded: 'rounded-2xl', base: 'bg-white dark:bg-neutral-900' }" />
             </div>

             <UButton
               color="primary"
               block
               size="xl"
               class="rounded-2xl font-black uppercase tracking-widest shadow-lg"
               :loading="isSubmitting"
               :disabled="!canInputGame"
               @click="submitGame"
             >
               {{ $t('match.submit_results') }}
             </UButton>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="px-6 pb-6 flex justify-between items-center relative z-10">
        <span class="text-[9px] font-bold text-neutral-300 dark:text-neutral-700 uppercase tracking-widest">{{ $t('match.system_footer') }}</span>
        <UButton variant="ghost" color="neutral" class="font-black uppercase tracking-widest text-[10px]" @click="model = false">{{ $t('match.close_monitor') }}</UButton>
      </div>
    </template>
  </UModal>
</template>

<style scoped>
.bounce {
  animation: bounce 2s infinite;
}
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
</style>
