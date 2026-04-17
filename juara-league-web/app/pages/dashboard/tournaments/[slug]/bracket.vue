<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()

const { data: tournament, refresh } = await useAsyncData(`tournament-bracket-${slug}`, () => tournamentStore.getBySlug(slug))

const selectedStageId = ref('')
const matchDetailModalRef = ref()

const selectedStage = computed(() => {
  return tournament.value?.stages?.find(s => s.id === selectedStageId.value) || tournament.value?.stages?.[0]
})

onMounted(() => {
  if (tournament.value?.stages?.length) {
    selectedStageId.value = tournament.value.stages[0].id
  }
})

// Modal props
const selectedMatchStageId = computed(() => {
  if (!matchDetailModalRef.value?.selectedMatch) return ''
  return matchDetailModalRef.value.selectedMatch.stage_id
})

const selectedMatchBoFormat = computed(() => {
  if (!matchDetailModalRef.value?.selectedMatch) return 'bo1'
  const stage = tournament.value?.stages?.find((s: any) => s.id === matchDetailModalRef.value.selectedMatch.stage_id)
  return stage?.config?.bo_format || 'bo1'
})
</script>

<template>
  <div v-if="tournament" class="space-y-12">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2">{{ $t('dashboard.title_bracket') }}</h2>
        <p class="text-neutral-500 font-medium text-sm">{{ $t('dashboard.subtitle_bracket') }}</p>
      </div>

      <div class="flex items-center gap-4">
        <USelect
          v-if="tournament.stages && tournament.stages.length > 1"
          v-model="selectedStageId"
          :options="tournament.stages.map((s: any) => ({ value: s.id, label: s.name }))"
          value-attribute="value"
          option-attribute="label"
          :placeholder="$t('dashboard.select_stage')"
          class="w-48"
          :ui="{ rounded: 'rounded-xl' }"
        />
        
        <UButton 
          color="neutral" 
          variant="ghost" 
          icon="i-lucide-maximize" 
          :label="$t('common.fullscreen')" 
          class="rounded-xl font-bold uppercase tracking-widest text-[10px]"
        />
      </div>
    </div>

    <!-- Bracket Section (Pro Visualizer) -->
    <div v-if="selectedStage" class="h-[600px] w-full rounded-[3rem] border border-neutral-200 dark:border-white/5 bg-white dark:bg-neutral-900/40 relative overflow-hidden group shadow-sm dark:shadow-none">
      <InteractiveCanvas>
        <TournamentsBracketVisualizer 
          :tournament-slug="slug" 
          :stage="selectedStage"
          @match-click="matchDetailModalRef?.open($event)"
        />
      </InteractiveCanvas>
    </div>

    <div v-else class="py-20 text-center bg-neutral-100 dark:bg-neutral-900/40 rounded-[3rem] border border-dashed border-neutral-300 dark:border-white/5">
       <UIcon name="i-lucide-git-branch" class="size-16 text-neutral-300 dark:text-neutral-700 mb-4" />
       <p class="text-neutral-500 font-bold uppercase tracking-widest text-xs">{{ $t('dashboard.no_active_stage') }}</p>
    </div>

    <!-- Match Detail Modal for Scoring -->
    <TournamentsMatchDetailModal
      ref="matchDetailModalRef"
      :tournament-slug="tournament.slug"
      :stage-id="selectedMatchStageId"
      :bo-format="selectedMatchBoFormat"
      is-staff
      @updated="refresh"
    />
  </div>
</template>
