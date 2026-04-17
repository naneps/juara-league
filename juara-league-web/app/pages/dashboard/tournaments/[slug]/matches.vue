<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch, Stage } from '~/types/tournament'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()

const { data: tournament } = await useAsyncData(`tournament-matches-${slug}`, () => tournamentStore.getBySlug(slug))
const { t } = useI18n()

const selectedStageId = ref('')
const matches = ref<TournamentMatch[]>([])
const isLoadingMatches = ref(false)
const matchDetailModalRef = ref()

const selectedStage = computed(() => {
  return tournament.value?.stages?.find((s: Stage) => s.id === selectedStageId.value)
})

const getParticipantName = (p: any) => {
  if (!p) return t('match.bracket.tbd')
  return p.team?.name || p.user?.name || t('match.bracket.tbd')
}

const matchStatusColor = (status: string) => {
  const map: Record<string, string> = { upcoming: 'neutral', ongoing: 'primary', completed: 'success', bye: 'warning' }
  return map[status] || 'neutral'
}

const fetchMatches = async () => {
  if (!selectedStageId.value) return
  isLoadingMatches.value = true
  try {
    matches.value = await tournamentStore.fetchMatches(slug, selectedStageId.value)
  } catch (e: any) {
    toast.add({ title: t('match.toast.load_failed'), description: e.message, color: 'error' })
  } finally {
    isLoadingMatches.value = false
  }
}

watch(selectedStageId, () => {
  fetchMatches()
})

onMounted(() => {
  if (tournament.value?.stages?.length) {
    selectedStageId.value = tournament.value.stages[0].id
  }
})

const openMatchDetail = (match: TournamentMatch) => {
  matchDetailModalRef.value?.open(match)
}

// Modal Props
const modalBoFormat = computed(() => selectedStage.value?.config?.bo_format || 'bo1')
</script>

<template>
  <div v-if="tournament" class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2">{{ $t('dashboard.title_matches') }}</h2>
        <p class="text-neutral-500 font-medium text-sm">{{ $t('dashboard.subtitle_matches') }}</p>
      </div>

      <div class="flex items-center gap-4">
        <USelect
          v-if="tournament.stages && tournament.stages.length > 0"
          v-model="selectedStageId"
          :options="tournament.stages.map((s: any) => ({ value: s.id, label: s.name }))"
          value-attribute="value"
          option-attribute="label"
          :placeholder="$t('dashboard.select_stage')"
          class="w-56"
          :ui="{ rounded: 'rounded-xl' }"
        />
        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-refresh-cw"
          @click="fetchMatches"
          :loading="isLoadingMatches"
        >
          {{ $t('dashboard.refresh') }}
        </UButton>
      </div>
    </div>

    <div v-if="!selectedStageId" class="py-20 text-center bg-neutral-100 dark:bg-neutral-900/40 rounded-[2rem] border border-dashed border-neutral-300 dark:border-white/5">
      <UIcon name="i-lucide-layers" class="size-12 text-neutral-400 dark:text-neutral-600 mb-4" />
      <p class="text-neutral-500 font-bold uppercase tracking-widest text-xs">{{ $t('dashboard.select_stage_first') }}</p>
    </div>

    <div v-else-if="isLoadingMatches" class="py-20 flex justify-center">
      <UIcon name="i-lucide-loader-2" class="size-8 text-primary-500 animate-spin" />
    </div>

    <div v-else-if="matches.length === 0" class="py-20 text-center bg-neutral-100 dark:bg-neutral-900/40 rounded-[2rem] border border-dashed border-neutral-300 dark:border-white/5">
      <UIcon name="i-lucide-swords" class="size-12 text-neutral-400 dark:text-neutral-600 mb-4" />
      <p class="text-neutral-500 font-bold uppercase tracking-widest text-xs">{{ $t('dashboard.no_matches') }}</p>
      <p class="text-neutral-400 text-[10px] uppercase font-bold mt-2">{{ $t('dashboard.start_stage_prompt') }}</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <button
        v-for="match in matches"
        :key="match.id"
        @click="openMatchDetail(match)"
        class="bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 border border-neutral-200 dark:border-white/5 rounded-[1.5rem] p-5 text-left transition-all hover:border-primary-500/30 hover:scale-[1.01] group relative overflow-hidden focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm dark:shadow-none"
      >
        <!-- Status & Round Header -->
        <div class="flex items-center justify-between mb-4">
          <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">
            {{ $t('match.bracket.round') }} {{ match.round }} | M#{{ match.match_number }}
          </span>
          <UBadge :color="matchStatusColor(match.status) as any" variant="subtle" size="xs" class="font-black">
            <span v-if="match.status === 'ongoing'" class="flex items-center gap-1.5 w-full">
               <span class="size-1.5 rounded-full bg-primary-500 animate-pulse"></span>
               {{ $t('match.live') }}
            </span>
            <span v-else class="uppercase">{{ match.status }}</span>
          </UBadge>
        </div>

        <!-- Participants & Scores -->
        <div class="space-y-3">
          <div class="flex items-center justify-between" :class="match.winner_id === match.participant_1_id ? 'text-primary-500' : 'text-neutral-900 dark:text-white'">
             <div class="flex items-center gap-2 overflow-hidden pr-2">
                <span class="size-1.5 rounded-full shrink-0" :class="match.winner_id === match.participant_1_id ? 'bg-primary-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-neutral-300 dark:bg-neutral-700'"></span>
                <span class="text-xs font-black uppercase truncate">{{ getParticipantName(match.participant_1) }}</span>
             </div>
             <span class="text-lg font-black italic">{{ match.scores?.participant_1 ?? 0 }}</span>
          </div>

          <div class="flex items-center justify-between" :class="match.winner_id === match.participant_2_id ? 'text-primary-500' : 'text-neutral-900 dark:text-white'">
             <div class="flex items-center gap-2 overflow-hidden pr-2">
                <span class="size-1.5 rounded-full shrink-0" :class="match.winner_id === match.participant_2_id ? 'bg-primary-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-neutral-300 dark:bg-neutral-700'"></span>
                <span class="text-xs font-black uppercase truncate">{{ getParticipantName(match.participant_2) }}</span>
             </div>
             <span class="text-lg font-black italic">{{ match.scores?.participant_2 ?? 0 }}</span>
          </div>
        </div>

        <!-- Footer Action indicator -->
        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-white/5 opacity-0 group-hover:opacity-100 transition-opacity flex justify-between items-center text-[10px] font-bold text-neutral-500 uppercase tracking-widest">
           <span>{{ $t('match.manage') }}</span>
           <UIcon name="i-lucide-arrow-right" class="text-primary-500" />
        </div>
      </button>
    </div>

    <!-- Match Detail Modal for Scoring -->
    <TournamentsMatchDetailModal
      ref="matchDetailModalRef"
      :tournament-slug="tournament.slug"
      :stage-id="selectedStageId"
      :bo-format="modalBoFormat"
      is-staff
      @updated="fetchMatches"
    />
  </div>
</template>
