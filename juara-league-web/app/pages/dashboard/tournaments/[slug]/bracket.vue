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
  if (route.query.stage) {
    selectedStageId.value = route.query.stage as string
  } else if (tournament.value?.stages?.length) {
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
        <USelectMenu
          v-if="tournament.stages && tournament.stages.length > 0"
          v-model="selectedStageId"
          :items="tournament.stages"
          value-key="id"
          label-key="name"
          variant="none"
          class="w-64"
          trailing-icon=""
          :ui="{ 
            content: 'rounded-[1.5rem] bg-white dark:bg-neutral-900 border-neutral-200 dark:border-white/5 shadow-2xl overflow-hidden p-1',
          }"
        >
          <template #default="{ open }">
            <UButton 
              color="neutral" 
              variant="ghost" 
              class="w-full justify-between rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-white/5 py-2 px-4 shadow-none"
            >
              <div class="flex items-center gap-2">
                <UIcon name="i-lucide-layers" class="size-3.5 text-indigo-500" />
                <span class="font-black uppercase text-[10px] tracking-widest text-neutral-700 dark:text-neutral-200">
                  {{ tournament.stages.find(s => s.id === selectedStageId)?.name || $t('dashboard.select_stage') }}
                </span>
              </div>
              <UIcon 
                name="i-lucide-chevron-down" 
                class="size-4 text-neutral-400 transition-transform duration-200"
                :class="{ 'rotate-180': open }"
              />
            </UButton>
          </template>
          
          <template #item="{ item }">
             <div class="flex items-center gap-2 py-0.5">
                <UIcon :name="item.status === 'ongoing' ? 'i-lucide-play-circle' : 'i-lucide-layers'" class="size-3.5" :class="item.status === 'ongoing' ? 'text-primary-500' : 'text-neutral-400'" />
                <span class="font-black uppercase text-[10px] tracking-widest">{{ item.name }}</span>
                <UBadge v-if="item.status === 'ongoing'" color="primary" variant="subtle" size="xs" class="ml-auto text-[8px] font-black uppercase">LIVE</UBadge>
             </div>
          </template>
        </USelectMenu>
        
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
          @match-click="router.push(`/dashboard/tournaments/${slug}/stages/${selectedStageId}/matches/${$event.id}`)"
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
