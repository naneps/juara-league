<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Stage, TournamentMatch } from '~/types/tournament'

const route = useRoute()
const router = useRouter()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()

const { data: tournament } = await useAsyncData(`tournament-matches-${slug}`, () => tournamentStore.getBySlug(slug))
const { t } = useI18n()

const selectedStageId = ref('')
const matches = ref<TournamentMatch[]>([])
const isLoadingMatches = ref(false)
const matchDetailModalRef = ref()
const matchScheduleModalRef = ref()
const viewMode = ref<'grid' | 'calendar'>('grid')

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

const formatMatchDate = (dateString?: string) => {
  if (!dateString) return null
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  }).format(date).replace(',', ' |')
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

const openMatchSchedule = (match: TournamentMatch) => {
  matchScheduleModalRef.value?.open(match)
}

const isAutoScheduling = ref(false)
const handleAutoSchedule = async () => {
  if (!selectedStageId.value) return
  isAutoScheduling.value = true
  try {
    const res = await tournamentStore.autoScheduleMatches(slug, selectedStageId.value)
    toast.add({ title: t('match.toast.auto_schedule_success'), color: 'success' })
    await fetchMatches()
  } catch (e: any) {
    toast.add({ title: t('match.toast.auto_schedule_failed'), description: e.data?.message || e.message, color: 'error' })
  } finally {
    isAutoScheduling.value = false
  }
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
        <USelectMenu
          v-if="tournament.stages && tournament.stages.length > 0"
          v-model="selectedStageId"
          :items="tournament.stages"
          value-key="id"
          label-key="name"
          variant="none"
          trailing-icon=""
          class="w-64"
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
          variant="outline"
          icon="i-lucide-calendar-clock"
          @click="handleAutoSchedule"
          :loading="isAutoScheduling"
          class="rounded-2xl shrink-0"
        >
          {{ $t('dashboard.auto_schedule') }}
        </UButton>

        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-refresh-cw"
          @click="fetchMatches"
          :loading="isLoadingMatches"
          class="rounded-2xl"
        >
          {{ $t('dashboard.refresh') }}
        </UButton>

        <div class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-2xl border border-neutral-200 dark:border-white/5">
          <UButton
            :color="viewMode === 'grid' ? 'primary' : 'neutral'"
            :variant="viewMode === 'grid' ? 'solid' : 'ghost'"
            size="sm"
            icon="i-lucide-layout-grid"
            class="rounded-xl"
            @click="viewMode = 'grid'"
          />
          <UButton
            :color="viewMode === 'calendar' ? 'primary' : 'neutral'"
            :variant="viewMode === 'calendar' ? 'solid' : 'ghost'"
            size="sm"
            icon="i-lucide-calendar-days"
            class="rounded-xl"
            @click="viewMode = 'calendar'"
          />
        </div>
      </div>
    </div>

    <div v-if="!selectedStageId" class="py-20 text-center bg-neutral-100 dark:bg-neutral-900/40 rounded-[2rem] border border-dashed border-neutral-300 dark:border-white/5">
      <UIcon name="i-lucide-layers" class="size-12 text-neutral-400 dark:text-neutral-600 mb-4" />
      <p class="text-neutral-500 font-bold uppercase tracking-widest text-xs">{{ $t('dashboard.select_stage_first') }}</p>
    </div>

    <!-- Premium Skeleton Grid Loading State -->
    <div v-else-if="isLoadingMatches" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div v-for="n in 8" :key="n" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-[1.5rem] flex flex-col h-[210px] shimmer overflow-hidden">
        <div class="p-5 flex-1 space-y-4">
          <!-- Header Skeleton -->
          <div class="flex justify-between items-center">
            <div class="w-24 h-3 bg-neutral-100 dark:bg-white/5 rounded"></div>
            <div class="w-16 h-5 bg-neutral-50 dark:bg-white/10 rounded-lg"></div>
          </div>
          <!-- Schedule Skeleton -->
          <div class="w-32 h-3 bg-neutral-50 dark:bg-white/5 rounded"></div>
          <!-- Participants Skeleton -->
          <div class="space-y-4 pt-2">
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-2">
                <div class="size-2 rounded-full bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="w-32 h-4 bg-neutral-100 dark:bg-white/5 rounded"></div>
              </div>
              <div class="w-6 h-6 bg-neutral-50 dark:bg-white/5 rounded"></div>
            </div>
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-2">
                <div class="size-2 rounded-full bg-neutral-200 dark:bg-neutral-800"></div>
                <div class="w-40 h-4 bg-neutral-100 dark:bg-white/5 rounded"></div>
              </div>
              <div class="w-6 h-6 bg-neutral-50 dark:bg-white/5 rounded"></div>
            </div>
          </div>
        </div>
        <!-- Footer Skeleton -->
        <div class="px-5 py-3 border-t border-neutral-100 dark:border-white/5 bg-neutral-50/30 dark:bg-black/20 flex justify-between items-center">
          <div class="flex gap-2">
            <div class="size-8 rounded-xl bg-neutral-100 dark:bg-white/5"></div>
            <div class="w-16 h-4 bg-neutral-100 dark:bg-white/5 rounded mt-2"></div>
          </div>
          <div class="size-4 bg-neutral-100 dark:bg-white/5 rounded"></div>
        </div>
      </div>
    </div>

    <div v-else-if="matches.length === 0" class="py-20 text-center bg-neutral-100 dark:bg-neutral-900/40 rounded-[2rem] border border-dashed border-neutral-300 dark:border-white/5">
      <UIcon name="i-lucide-swords" class="size-12 text-neutral-400 dark:text-neutral-600 mb-4" />
      <p class="text-neutral-500 font-bold uppercase tracking-widest text-xs">{{ $t('dashboard.no_matches') }}</p>
      <p class="text-neutral-400 text-[10px] uppercase font-bold mt-2">{{ $t('dashboard.start_stage_prompt') }}</p>
    </div>

    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div
        v-for="match in matches"
        :key="match.id"
        class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-[1.5rem] flex flex-col transition-all hover:border-primary-500/30 hover:scale-[1.01] group overflow-hidden shadow-sm dark:shadow-none"
      >
        <!-- Top Section: Clickable for Detail -->
        <div 
          class="p-5 flex-1 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors"
          @click="router.push(`/dashboard/tournaments/${slug}/stages/${selectedStageId}/matches/${match.id}`)"
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
              <span v-else class="uppercase">{{ $t('match.' + match.status) }}</span>
            </UBadge>
          </div>

          <!-- Schedule Info -->
          <div v-if="match.scheduled_at" class="mb-4 flex items-center gap-1.5">
             <UIcon name="i-lucide-calendar" class="size-3 text-neutral-400" />
             <span class="text-[9px] font-bold text-neutral-500 uppercase tracking-wider">
                {{ formatMatchDate(match.scheduled_at) }}
             </span>
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
        </div>

        <!-- Bottom Section: Separate Actions -->
        <div class="px-5 py-3 border-t border-neutral-100 dark:border-white/5 flex justify-between items-center bg-neutral-50/30 dark:bg-black/20">
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="size-8 flex items-center justify-center rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-500 hover:text-indigo-500 hover:bg-indigo-500/10 transition-all shadow-sm"
                @click="openMatchSchedule(match)"
              >
                <UIcon name="i-lucide-calendar-clock" class="size-4" />
              </button>
              <NuxtLink 
                :to="`/dashboard/tournaments/${slug}/stages/${selectedStageId}/matches/${match.id}`"
                class="text-[10px] font-black text-neutral-500 uppercase tracking-widest hover:text-primary-500 transition-colors px-2 py-1"
              >
                {{ $t('match.manage') }}
              </NuxtLink>
           </div>
           <UIcon name="i-lucide-arrow-right" class="text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity" />
        </div>
      </div>
    </div>

    <!-- Calendar View -->
    <TournamentsTournamentScheduleViewer
      v-else-if="viewMode === 'calendar'"
      :matches="matches"
      @select-match="router.push(`/dashboard/tournaments/${slug}/stages/${selectedStageId}/matches/${$event.id}`)"
      @schedule-match="openMatchSchedule"
    />

    <!-- Match Detail Modal for Scoring -->
    <TournamentsMatchDetailModal
      ref="matchDetailModalRef"
      :tournament-slug="tournament.slug"
      :stage-id="selectedStageId"
      :bo-format="modalBoFormat"
      is-staff
      @updated="fetchMatches"
    />

    <TournamentsMatchScheduleModal
      ref="matchScheduleModalRef"
      :tournament-slug="tournament.slug"
      :stage-id="selectedStageId"
      :all-matches="matches"
      @updated="fetchMatches"
    />
  </div>
</template>
<style scoped>
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
</style>
