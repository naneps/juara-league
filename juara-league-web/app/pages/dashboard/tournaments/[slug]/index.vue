<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import { getApprovalStatus, getTournamentStatus } from '~/utils/tournamentStatus'

const route = useRoute()
const router = useRouter()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()
const { t } = useI18n()

const { data: tournament, refresh: refreshTournament } = await useAsyncData(`tournament-${slug}`, () => tournamentStore.getBySlug(slug))
const { data: stats, refresh: refreshStats } = await useAsyncData(`tournament-stats-${slug}`, () => tournamentStore.fetchStats(slug))

const refresh = async () => {
  await Promise.all([refreshTournament(), refreshStats()])
}

const isPublishing = ref(false)
const isPublishModalOpen = ref(false)
const isPrizeModalOpen = ref(false)
const isSavingPrizes = ref(false)
const prizesState = ref<any[]>([])

const openPrizeModal = () => {
  prizesState.value = JSON.parse(JSON.stringify(tournament.value?.prizes || []))
  isPrizeModalOpen.value = true
}

const savePrizes = async () => {
  if (!tournament.value) return
  isSavingPrizes.value = true
  try {
    const total = prizesState.value.reduce((sum, p) => sum + (Number(p.prize_amount) || 0), 0)
    await tournamentStore.updateTournament(tournament.value.slug, {
      prizes: prizesState.value,
      prize_pool: total > 0 ? total : tournament.value.prize_pool
    } as any)
    toast.add({ title: t('common.success'), description: 'Pembagian hadiah berhasil diperbarui', color: 'success' })
    isPrizeModalOpen.value = false
    await refresh()
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.data?.message || 'Gagal memperbarui hadiah', color: 'error' })
  } finally {
    isSavingPrizes.value = false
  }
}

const handlePublish = async () => {
  if (tournament.value?.approval_status === 'pending_review') {
    toast.add({ title: t('tournament_detail.toast.reviewing'), description: t('tournament_detail.toast.reviewing_desc'), color: 'warning' })
    return
  }
  
  if (tournament.value?.approval_status === 'rejected') {
    toast.add({ title: t('tournament_detail.toast.rejected'), description: t('tournament_detail.toast.rejected_desc'), color: 'error' })
    return
  }

  isPublishModalOpen.value = true
}

const confirmPublish = async () => {
  isPublishing.value = true
  try {
    await tournamentStore.publishTournament(slug)
    toast.add({ title: t('tournament_detail.toast.publish_success'), description: t('tournament_detail.toast.publish_success_desc'), color: 'success' })
    await refresh()
    isPublishModalOpen.value = false
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.data?.message || t('tournament_detail.toast.publish_failed'), color: 'error' })
  } finally {
    isPublishing.value = false
  }
}

const tournamentStatus = computed(() => getTournamentStatus(tournament.value?.status))
const approvalStatus = computed(() => getApprovalStatus(tournament.value?.approval_status))

const formatDate = (d: string | null | undefined) => {
  if (!d) return '—'
  const { locale } = useI18n()
  return new Date(d).toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', { day: 'numeric', month: 'short', year: 'numeric' })
}

// Match Management
const matchDetailModalRef = ref()
const selectedMatchForModal = computed(() => matchDetailModalRef.value?.selectedMatch)

const selectedMatchStageId = computed(() => {
  if (!matchDetailModalRef.value?.selectedMatch) return ''
  return matchDetailModalRef.value.selectedMatch.stage_id
})

const selectedMatchBoFormat = computed(() => {
  if (!matchDetailModalRef.value?.selectedMatch) return 'bo1'
  const stage = tournament.value?.stages?.find((s: any) => s.id === matchDetailModalRef.value.selectedMatch.stage_id)
  return stage?.bo_format || 'bo1'
})

const getParticipantName = (p: any) => {
  if (!p) return t('match.bracket.tbd')
  const participant = p.participant || p 
  return participant.team?.name || participant.user?.name || t('match.bracket.tbd')
}

const getRankIcon = (rank: number) => {
  if (rank === 1) return 'i-lucide-trophy'
  if (rank === 2) return 'i-lucide-medal'
  if (rank === 3) return 'i-lucide-award'
  return 'i-lucide-star'
}

const getRankColorClass = (rank: number) => {
  if (rank === 1) return 'bg-amber-500/10 text-amber-600 border-amber-500/20'
  if (rank === 2) return 'bg-slate-400/10 text-slate-500 border-slate-400/20'
  if (rank === 3) return 'bg-orange-500/10 text-orange-600 border-orange-500/20'
  return 'bg-neutral-100 text-neutral-500 border-neutral-200'
}
</script>

<template>
  <div v-if="tournament" class="space-y-6">

    <!-- ── Review Notice ── -->
    <template v-if="tournament.status === 'draft'">
      <UAlert
        v-if="tournament.approval_status === 'pending_review'"
        icon="i-lucide-scroll-text"
        color="warning"
        variant="subtle"
        :title="$t('tournament_detail.notices.under_review')"
        :description="$t('tournament_detail.notices.under_review_desc')"
        class="border-warning-500/20"
      />
      <UAlert
        v-else-if="tournament.approval_status === 'rejected'"
        icon="i-lucide-alert-octagon"
        color="error"
        variant="subtle"
        :title="$t('tournament_detail.notices.rejected')"
        :description="$t('tournament_detail.notices.rejected_desc')"
        class="border-error-500/20"
      />
    </template>

    <!-- ── Page Header ── -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

      <!-- Left: Title + status -->
      <div class="flex items-center gap-4">
        <div class="size-14 shrink-0 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex items-center justify-center">
          <img v-if="tournament.sport?.icon_url" :src="tournament.sport?.icon_url" class="size-8 object-contain" />
          <UIcon v-else name="i-lucide-trophy" class="size-7 text-neutral-400" />
        </div>
        <div>
          <h1 class="text-xl font-bold text-neutral-900 dark:text-white leading-tight mb-1">
            {{ tournament.title }}
          </h1>
          <div class="flex flex-wrap items-center gap-2">
            <UBadge :color="tournamentStatus.color" variant="subtle" size="sm">{{ tournamentStatus.label }}</UBadge>
            <UBadge v-if="tournament.approval_status !== 'auto_approved'" :color="approvalStatus.color" variant="outline" size="sm" class="border-current">
              {{ approvalStatus.label }}
            </UBadge>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
              {{ tournament.category }}
              <template v-if="tournament.mode"> · {{ tournament.mode }}</template>
              <template v-if="tournament.bracket_type"> · {{ (tournament.bracket_type ?? '').replace(/_/g, ' ') }}</template>
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Actions -->
      <div class="flex items-center gap-2 shrink-0">
        <UButton
          v-if="tournament.status === 'draft'"
          color="primary"
          icon="i-lucide-send"
          :loading="isPublishing"
          :disabled="tournament.approval_status === 'pending_review' || tournament.approval_status === 'rejected'"
          size="sm"
          @click="handlePublish"
        >
          {{ $t('dashboard.publish') }}
        </UButton>
        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-pencil"
          size="sm"
          :to="`/dashboard/tournaments/create?edit=${tournament.slug}`"
        >
          {{ $t('common.edit') }}
        </UButton>
      </div>
    </div>

    <!-- ── Live & Progress ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Progress Card -->
      <div class="lg:col-span-1 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-[2rem] p-6 relative overflow-hidden">
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xs font-black text-neutral-400 uppercase tracking-widest">{{ $t('dashboard.stats.tournament_progress') }}</h3>
            <UIcon name="i-lucide-activity" class="size-4 text-primary-500" />
          </div>
          
          <div class="flex flex-col items-center justify-center py-4">
            <div class="relative size-32 mb-6">
              <svg class="size-full" viewBox="0 0 36 36">
                <circle cx="18" cy="18" r="16" fill="none" class="stroke-neutral-100 dark:stroke-neutral-800" stroke-width="3"></circle>
                <circle cx="18" cy="18" r="16" fill="none" class="stroke-primary-500" stroke-width="3" stroke-dasharray="100" :stroke-dashoffset="100 - (stats?.completion_rate || 0)" stroke-linecap="round" transform="rotate(-90 18 18)"></circle>
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-black text-neutral-900 dark:text-white">{{ stats?.completion_rate || 0 }}%</span>
                <span class="text-[9px] font-bold text-neutral-400 uppercase">{{ $t('dashboard.stats.completed') }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-8 w-full border-t border-neutral-100 dark:border-neutral-800 pt-6">
              <div class="text-center">
                <p class="text-lg font-black text-neutral-900 dark:text-white leading-none mb-1">{{ stats?.matches_completed || 0 }}</p>
                <p class="text-[10px] text-neutral-400 font-bold uppercase">{{ $t('dashboard.stats.matches') }}</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-black text-neutral-900 dark:text-white leading-none mb-1">{{ stats?.participants_active || 0 }}</p>
                <p class="text-[10px] text-neutral-400 font-bold uppercase">{{ $t('dashboard.stats.active') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Live Matches / Active Stage -->
      <div class="lg:col-span-2 space-y-6">
        <div v-if="stats?.active_stage" class="bg-primary-500 rounded-[2rem] p-6 text-white relative overflow-hidden shadow-xl shadow-primary-500/20">
          <div class="absolute -top-12 -right-12 size-48 bg-white/10 blur-3xl rounded-full"></div>
          <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
              <p class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-1">{{ $t('dashboard.stats.active_stage') }}</p>
              <h3 class="text-2xl font-black uppercase tracking-tight">{{ stats.active_stage.name }}</h3>
            </div>
            <div class="flex-1 md:max-w-xs">
              <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-white/80 uppercase">{{ $t('dashboard.stats.stage_progress') }}</span>
                <span class="text-xs font-black">{{ stats.active_stage.progress }}%</span>
              </div>
              <UProgress :value="stats.active_stage.progress" color="neutral" size="sm" class="bg-white/20" />
            </div>
          </div>
        </div>

        <div v-if="tournament.stages?.some(s => s.matches?.some((m: any) => m.status === 'ongoing'))" class="bg-neutral-900 border border-white/5 rounded-[2.5rem] p-8 relative overflow-hidden group">
          <div class="absolute -top-10 -right-10 size-40 bg-primary-500/10 blur-[80px] group-hover:bg-primary-500/20 transition-all"></div>
          
          <div class="flex items-center justify-between mb-8 relative z-10">
            <div class="flex items-center gap-4">
              <div class="size-12 rounded-2xl bg-neutral-800 flex items-center justify-center border border-white/5 shadow-inner">
                <UIcon name="i-lucide-play-circle" class="size-6 text-primary-500 animate-pulse" />
              </div>
              <div>
                <h3 class="text-sm font-black text-white uppercase tracking-[0.2em]">{{ $t('public.live_matches') }}</h3>
                <p class="text-[10px] text-neutral-500 font-bold uppercase tracking-widest mt-1">{{ $t('tournament_detail.live_subtitle') }}</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
            <template v-for="stage in tournament.stages">
              <template v-for="match in stage.matches">
                <button 
                  v-if="match.status === 'ongoing'" 
                  :key="match.id"
                  @click="router.push(`/dashboard/tournaments/${slug}/stages/${stage.id}/matches/${match.id}`)"
                  class="flex flex-col bg-neutral-950 border border-white/5 rounded-2xl p-5 transition-all hover:border-primary-500/40 hover:scale-[1.02] text-left group/card shadow-lg"
                >
                  <div class="flex items-center justify-between mb-6">
                    <span class="text-[9px] font-black text-neutral-600 uppercase tracking-widest">{{ stage.name }}</span>
                    <div class="flex items-center gap-1.5">
                      <span class="size-1.5 rounded-full bg-primary-500 animate-ping"></span>
                      <span class="text-primary-500 font-black text-[8px] uppercase tracking-widest">LIVE</span>
                    </div>
                  </div>
                  
                  <div class="space-y-4">
                    <div class="flex items-center justify-between gap-4">
                      <span class="text-xs font-black text-white truncate flex-1 uppercase tracking-tight">{{ getParticipantName(match.participant_1) }}</span>
                      <span class="text-2xl font-black text-primary-500 italic">{{ match.scores?.participant_1 ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                      <span class="text-xs font-black text-white truncate flex-1 uppercase tracking-tight">{{ getParticipantName(match.participant_2) }}</span>
                      <span class="text-2xl font-black text-primary-500 italic">{{ match.scores?.participant_2 ?? 0 }}</span>
                    </div>
                  </div>

                  <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between opacity-40 group-hover/card:opacity-100 transition-opacity">
                    <span class="text-[8px] font-black text-neutral-500 uppercase tracking-[0.2em]">{{ $t('public.click_update') }}</span>
                    <UIcon name="i-lucide-arrow-right" class="size-3 text-primary-500" />
                  </div>
                </button>
              </template>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Stat Cards (Secondary) ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="stat in [
          { label: $t('tournament_detail.stats.participants'),      value: String(tournament.participants_count ?? 0),                                                           sub: `${$t('tournament_detail.stats.from')} ${tournament.max_participants}`, icon: 'i-lucide-users' },
          { label: $t('tournament_detail.stats.prize'), value: tournament.prize_pool ? formatCurrency(tournament.prize_pool) : '—',                                  sub: $t('tournament_detail.stats.prize_pool_sub'),                         icon: 'i-lucide-trophy' },
          { label: $t('tournament_detail.stats.revenue'),    value: stats?.revenue !== undefined ? formatCurrency(stats.revenue) : '—', sub: $t('tournament_detail.stats.projected'),                        icon: 'i-lucide-trending-up' },
          { label: $t('tournament_detail.stats.reg_close'),   value: formatDate(tournament.registration_end_at),                                                            sub: $t('tournament_detail.stats.reg_deadline'),             icon: 'i-lucide-timer' },
        ]"
        :key="stat.label"
        class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-5"
      >
        <div class="flex items-center gap-2 mb-3">
          <UIcon :name="stat.icon" class="size-4 text-neutral-400 dark:text-neutral-500" />
          <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ stat.label }}</span>
        </div>
        <p class="text-xl font-bold text-neutral-900 dark:text-white leading-none mb-1">{{ stat.value }}</p>
        <p class="text-xs text-neutral-400 dark:text-neutral-500">{{ stat.sub }}</p>
      </div>
    </div>

    <!-- ── Deskripsi & Hadiah ── -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Deskripsi -->
      <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 dark:border-neutral-800">
          <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
            <UIcon name="i-lucide-file-text" class="size-4 text-neutral-400" />
            {{ $t('tournament_detail.sections.rules') }}
          </h3>
          <UButton
            size="xs"
            color="neutral"
            variant="ghost"
            icon="i-lucide-pencil"
            :to="`/dashboard/tournaments/create?edit=${tournament.slug}`"
          >
            {{ $t('common.edit') }}
          </UButton>
        </div>
        <div class="px-6 py-5">
          <p
            v-if="tournament.description"
            class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed whitespace-pre-wrap"
          >
            {{ tournament.description }}
          </p>
          <div v-else class="py-10 text-center">
            <UIcon name="i-lucide-file-x" class="size-9 text-neutral-300 dark:text-neutral-700 mx-auto mb-2" />
            <p class="text-sm text-neutral-400 dark:text-neutral-500">{{ $t('tournament_detail.empty.desc') }}</p>
          </div>
        </div>
      </div>

      <!-- Detail Hadiah -->
      <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 dark:border-neutral-800">
          <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
            <UIcon name="i-lucide-trophy" class="size-4 text-amber-500" />
            {{ $t('tournament_detail.sections.prizes') }}
          </h3>
          <UButton
            size="xs"
            color="neutral"
            variant="ghost"
            icon="i-lucide-pencil"
            @click="openPrizeModal"
          >
            {{ $t('common.edit') }}
          </UButton>
        </div>
        <div class="px-6 py-5">
          <div v-if="tournament.prizes && tournament.prizes.length > 0" class="space-y-3">
             <div 
               v-for="prize in tournament.prizes" 
               :key="prize.id"
               class="flex items-center justify-between p-3 rounded-xl bg-neutral-50 dark:bg-white/[0.02] border border-neutral-100 dark:border-white/5 transition-all hover:bg-amber-500/5 hover:border-amber-500/20"
             >
                <div class="flex items-center gap-3">
                  <div 
                    class="size-10 rounded-xl flex items-center justify-center border shadow-sm"
                    :class="getRankColorClass(prize.rank)"
                  >
                    <UIcon :name="getRankIcon(prize.rank)" class="size-5" />
                  </div>
                  <div>
                    <p class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-tight leading-none mb-1">{{ prize.tier_name }}</p>
                    <p v-if="prize.description" class="text-[10px] text-neutral-500 leading-none">{{ prize.description }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-black text-primary-500 italic">{{ formatCurrency(prize.prize_amount) }}</p>
                </div>
             </div>
          </div>
          <p
            v-else-if="tournament.prize_description"
            class="text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed whitespace-pre-wrap font-mono bg-neutral-50 dark:bg-neutral-800/50 p-4 rounded-xl border border-neutral-100 dark:border-white/5"
          >
            {{ tournament.prize_description }}
          </p>
          <div v-else class="py-10 text-center">
            <UIcon name="i-lucide-award" class="size-9 text-neutral-300 dark:text-neutral-700 mx-auto mb-2" />
            <p class="text-sm text-neutral-400 dark:text-neutral-500">{{ $t('tournament_detail.empty.prizes') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Inline managers ── -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <TournamentsTournamentParticipantManager
        :tournament-slug="tournament.slug"
        :initial-participants="tournament.participants || []"
        :participant-type="tournament.participant_type"
      />
      <TournamentsTournamentStageManager
        :tournament-slug="tournament.slug"
        :initial-stages="tournament.stages || []"
        @refresh="refresh"
      />
    </div>

    <!-- Match Detail Modal -->
    <TournamentsMatchDetailModal
      ref="matchDetailModalRef"
      :tournament-slug="tournament.slug"
      :stage-id="selectedMatchStageId"
      :bo-format="selectedMatchBoFormat"
      is-staff
      @updated="refresh"
    />
  </div>

  <!-- Not found -->
  <div v-else class="flex items-center justify-center py-32">
    <div class="text-center">
      <UIcon name="i-lucide-search-x" class="size-12 text-neutral-300 dark:text-neutral-700 mx-auto mb-4" />
      <p class="text-neutral-500 dark:text-neutral-400">{{ $t('tournament_detail.empty.not_found') }}</p>
    </div>
  </div>

  <!-- Publish Confirmation Modal -->
  <UModal v-model:open="isPublishModalOpen" class="w-full sm:max-w-lg" :ui="{ content: 'rounded-[1.5rem] bg-neutral-900 border border-white/5 shadow-2xl' }">
    <template #body>
      <div class="p-2 space-y-6">
        <div class="flex items-center gap-4">
          <div class="flex-shrink-0 size-12 rounded-full bg-amber-500/10 flex items-center justify-center border border-amber-500/20">
            <UIcon name="i-lucide-alert-triangle" class="text-amber-500 size-6" />
          </div>
          <div>
            <h3 class="text-xl font-black text-white uppercase tracking-wider leading-tight">{{ $t('tournament_detail.publish_modal.title') }}</h3>
            <p class="text-sm text-neutral-400 font-medium">{{ $t('tournament_detail.publish_modal.subtitle') }}</p>
          </div>
        </div>
        
        <div class="space-y-3 pl-4 border-l-2 border-white/5 text-sm text-neutral-300">
          <p class="font-medium text-neutral-400">{{ $t('tournament_detail.publish_modal.after_publish') }}</p>
          <ul class="list-disc list-inside space-y-2 text-neutral-400 font-medium ml-2">
            <li>{{ $t('tournament_detail.publish_modal.item1') }}</li>
            <li>{{ $t('tournament_detail.publish_modal.item2') }}</li>
            <li>{{ $t('tournament_detail.publish_modal.item3') }}</li>
          </ul>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <UButton 
          color="neutral" 
          variant="ghost" 
          class="font-bold uppercase tracking-wider"
          @click="isPublishModalOpen = false"
          :disabled="isPublishing"
        >
          {{ $t('common.cancel') }}
        </UButton>
        <UButton 
          color="success" 
          class="font-black uppercase tracking-wider shadow-[0_0_15px_rgba(34,197,94,0.3)] px-6"
          :loading="isPublishing"
          @click="confirmPublish"
        >
          {{ $t('tournament_detail.publish_modal.confirm') }}
        </UButton>
      </div>
    </template>
  </UModal>

  <!-- Prize Management Modal -->
  <UModal v-model:open="isPrizeModalOpen" class="w-full sm:max-w-2xl" :ui="{ content: 'rounded-[1.5rem] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 shadow-2xl' }">
    <template #body>
      <div class="p-2 space-y-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="size-12 rounded-2xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20">
              <UIcon name="i-lucide-trophy" class="text-amber-500 size-6" />
            </div>
            <div>
              <h3 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-wider leading-tight">Kelola Pembagian Hadiah</h3>
              <p class="text-sm text-neutral-500 font-medium">Atur detail hadiah untuk setiap peringkat pemenang.</p>
            </div>
          </div>
        </div>
        
        <div class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
          <TournamentsTournamentPrizeManager v-model="prizesState" />
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <UButton 
          color="neutral" 
          variant="ghost" 
          class="font-bold uppercase tracking-wider"
          @click="isPrizeModalOpen = false"
          :disabled="isSavingPrizes"
        >
          {{ $t('common.cancel') }}
        </UButton>
        <UButton 
          color="primary" 
          class="font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 px-6"
          :loading="isSavingPrizes"
          @click="savePrizes"
        >
          {{ $t('common.save') }}
        </UButton>
      </div>
    </template>
  </UModal>
</template>
