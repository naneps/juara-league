<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import { getApprovalStatus, getTournamentStatus } from '~/utils/tournamentStatus'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()
const { t } = useI18n()

const { data: tournament, refresh } = await useAsyncData(`tournament-${slug}`, () => tournamentStore.getBySlug(slug))

const isPublishing = ref(false)
const isPublishModalOpen = ref(false)

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
  return stage?.config?.bo_format || 'bo1'
})
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

    <!-- ── Stat Cards ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="stat in [
          { label: $t('tournament_detail.stats.participants'),      value: String(tournament.participants_count ?? 0),                                                           sub: `${$t('tournament_detail.stats.from')} ${tournament.max_participants}`, icon: 'i-lucide-users' },
          { label: $t('tournament_detail.stats.prize'), value: tournament.prize_pool ? formatCurrency(tournament.prize_pool) : '—',                                  sub: $t('tournament_detail.stats.prize_pool_sub'),                         icon: 'i-lucide-trophy' },
          { label: $t('tournament_detail.stats.fee'),    value: !tournament.entry_fee || tournament.entry_fee == 0 ? $t('tournament_detail.stats.free') : formatCurrency(tournament.entry_fee), sub: $t('tournament_detail.stats.per_participant'),                        icon: 'i-lucide-ticket' },
          { label: $t('tournament_detail.stats.reg_close'),   value: formatDate(tournament.registration_end_at),                                                            sub: $t('tournament_detail.stats.reg_deadline'),             icon: 'i-lucide-timer' },
          { label: $t('tournament_detail.stats.kickoff'),      value: formatDate(tournament.start_at),                                                                       sub: $t('tournament_detail.stats.first_day'),           icon: 'i-lucide-calendar' },
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
        </div>
        <div class="px-6 py-5">
          <p
            v-if="tournament.prize_description"
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

    <!-- ── Live Matches Management ── -->
    <div v-if="tournament.stages?.some(s => s.matches?.some((m: any) => m.status === 'ongoing'))" class="bg-neutral-900 border border-primary-500/20 rounded-[2.5rem] p-8 relative overflow-hidden group">
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

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 relative z-10">
        <template v-for="stage in tournament.stages">
          <template v-for="match in stage.matches">
            <button 
              v-if="match.status === 'ongoing'" 
              :key="match.id"
              @click="matchDetailModalRef?.open(match)"
              class="flex flex-col bg-neutral-950 border border-white/5 rounded-2xl p-4 transition-all hover:border-primary-500/40 hover:scale-[1.02] text-left group/card"
            >
              <div class="flex items-center justify-between mb-4">
                <span class="text-[9px] font-black text-neutral-600 uppercase tracking-widest">{{ stage.name }}</span>
                <UBadge color="primary" variant="subtle" size="xs" class="font-black text-[8px]">LIVE NOW</UBadge>
              </div>
              
              <div class="flex items-center justify-between gap-4 mb-2">
                <span class="text-xs font-black text-white truncate flex-1 uppercase tracking-tight">{{ match.participant_1?.team?.name || match.participant_1?.user?.name || 'TBD' }}</span>
                <span class="text-xl font-black text-primary-500 italic">{{ match.scores?.participant_1 ?? 0 }}</span>
              </div>
              <div class="flex items-center justify-between gap-4">
                <span class="text-xs font-black text-white truncate flex-1 uppercase tracking-tight">{{ match.participant_2?.team?.name || match.participant_2?.user?.name || 'TBD' }}</span>
                <span class="text-xl font-black text-primary-500 italic">{{ match.scores?.participant_2 ?? 0 }}</span>
              </div>

              <div class="mt-4 pt-4 border-t border-white/5 flex items-center justify-between opacity-0 group-hover/card:opacity-100 transition-opacity">
                <span class="text-[8px] font-bold text-neutral-500 uppercase tracking-widest">{{ $t('public.click_update') }}</span>
                <UIcon name="i-lucide-arrow-right" class="size-3 text-primary-500" />
              </div>
            </button>
          </template>
        </template>
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
</template>
