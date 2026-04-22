<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Tournament, Stage, TournamentMatch } from '~/types/tournament'
import { getTournamentStatus } from '~/utils/tournamentStatus'

const route = useRoute()
const tournamentStore = useTournamentStore()
const { user } = useAuth()
const { isLoading, error } = storeToRefs(tournamentStore)

const tournament = ref<Tournament | null>(null)
const tournamentStatus = computed(() => getTournamentStatus(tournament.value?.status))

// Use store isLoading for global loading state
const isPageLoading = computed(() => isLoading.value && !tournament.value)


// Initial fetch
const { data, refresh } = await useAsyncData(`tournament-${route.params.slug}`, () => 
  tournamentStore.getBySlug(route.params.slug as string)
)

// Keep tournament ref in sync with async data
watch(data, (newData) => {
  if (newData) {
    tournament.value = newData as Tournament
  }
}, { immediate: true })

if (data.value) {
  tournament.value = data.value as Tournament
}

const isOwner = computed(() => {
  return Boolean(user.value && tournament.value?.user?.id === (user.value as any).id)
})


const isPublishing = ref(false)
const isPublishModalOpen = ref(false)
const isJoinModalOpen = ref(false)

const handleJoinClick = () => {
  if (!user.value) {
    void navigateTo('/login')
    return
  }
  isJoinModalOpen.value = true
}

// Computed state for current user's participation
const userParticipation = computed(() => {
  return tournament.value?.user_participation ?? null
})

const handleJoinSuccess = async () => {
  await refresh()
}

const publish = async () => {
  if (!tournament.value) return
  isPublishing.value = true
  try {
    await tournamentStore.publishTournament(tournament.value.slug)
    await refresh()
    isPublishModalOpen.value = false
    useToast().add({ title: 'Berhasil!', description: 'Turnamen telah dipublikasikan ke publik.', color: 'success' })
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal mempublikasikan turnamen', color: 'error' })
  } finally {
    isPublishing.value = false
  }
}

// ── State Definitions (Moved up to avoid initialization errors) ──
const activeTabIndex = ref<number | undefined>(undefined)
const liveMatches = ref<TournamentMatch[]>([])
const isLoadingLiveMatches = ref(false)
const allMatches = ref<TournamentMatch[]>([])
const isLoadingAllMatches = ref(false)

const publicParticipants = ref<Participant[]>([])
const isLoadingParticipants = ref(false)

const standings = ref<any[]>([])
const isLoadingStandings = ref(false)
const selectedGroupId = ref<string | null>(null)

const fetchStandingsData = async () => {
  if (!tournament.value || !selectedStage.value) return
  
  // Find group ID
  // If it's round_robin, it might have multiple groups
  // For now we take the first group or a selected one
  const groupId = selectedGroupId.value || selectedStage.value.groups?.[0]?.id
  if (!groupId) return

  isLoadingStandings.value = true
  try {
    const data = await tournamentStore.fetchStandings(tournament.value.slug, selectedStage.value.id, groupId)
    standings.value = data
  } catch (e) {
    console.error('Failed to fetch standings', e)
  } finally {
    isLoadingStandings.value = false
  }
}

const fetchPublicParticipants = async () => {
  if (!tournament.value) return
  isLoadingParticipants.value = true
  try {
    const data = await tournamentStore.fetchParticipants(tournament.value.slug)
    publicParticipants.value = data
  } catch (e) {
    console.error('Failed to fetch participants', e)
  } finally {
    isLoadingParticipants.value = false
  }
}

const tabs = computed(() => {

  const allTabs = [
    { label: 'Informasi', icon: 'i-lucide-info', slot: 'info' },
    { 
      label: 'Live', 
      icon: 'i-lucide-play-circle', 
      slot: 'live',
      show: liveMatches.value.length > 0 || isOwner.value
    },
    { 
      label: 'Laga', 
      icon: 'i-lucide-calendar', 
      slot: 'matches'
    },
    { 
      label: 'Manajemen Tahapan', 
      icon: 'i-lucide-settings-2', 
      slot: 'management',
      show: isOwner.value 
    },
    { 
      label: 'Manajemen Staf', 
      icon: 'i-lucide-shield-check', 
      slot: 'staff',
      show: isOwner.value 
    },
    { label: 'Bracket', icon: 'i-lucide-git-branch', slot: 'bracket' },
    { 
      label: 'Klasemen', 
      icon: 'i-lucide-list-ordered', 
      slot: 'standings',
      show: tournament.value?.stages?.some(s => ['round_robin', 'swiss'].includes(s.type))
    },
    { label: 'Partisipan', icon: 'i-lucide-users', slot: 'participants' }
  ]

  return allTabs.filter(tab => tab.show !== false)
})

// Format date helper

const formatDate = (dateString?: string) => {
  if (!dateString) return 'TBA'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (amount?: number) => {
  if (!amount && amount !== 0) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}


// Bracket state
const selectedStageId = ref<string | null>(null)
const selectedStage = computed(() =>
  tournament.value?.stages?.find((s: Stage) => s.id === selectedStageId.value) || null
)
const ongoingStages = computed(() =>
  (tournament.value?.stages || []).filter((s: Stage) => s.status === 'ongoing' || s.status === 'completed' || (s.matches_count && s.matches_count > 0))
)
// Watch for tabs to be populated and set default
watch(tabs, (newTabs) => {
  if (newTabs.length > 0 && activeTabIndex.value === undefined) {
    activeTabIndex.value = 0
  }
}, { immediate: true })


const fetchLiveMatches = async () => {
  if (!tournament.value) return
  isLoadingLiveMatches.value = true
  try {
    // We'll fetch matches for all ongoing stages
    const results = await Promise.all(
      ongoingStages.value
        .filter(s => s.status === 'ongoing')
        .map(s => tournamentStore.fetchMatches(tournament.value!.slug, s.id, { status: 'ongoing' }))
    )
    liveMatches.value = results.flat()
  } catch (e) {
    console.error('Failed to fetch live matches', e)
  } finally {
    isLoadingLiveMatches.value = false
  }
}

const onTabChange = (index: number) => {
  activeTabIndex.value = index
  const tab = tabs.value[index]
  
  if (tab.slot === 'participants' && publicParticipants.value.length === 0) {
    fetchPublicParticipants()
  }
  if (tab.slot === 'live') {
    fetchLiveMatches()
  }
  if (tab.slot === 'matches') {
    fetchAllMatches()
  }
  if (tab.slot === 'standings') {
    fetchStandingsData()
  }
}

// Watch for stage status changes to refresh live matches
watch([() => tournament.value?.stages, () => activeTabIndex.value], () => {
  if (tournament.value?.stages && tournament.value.stages.length > 0 && !selectedStageId.value) {
    const ongoing = tournament.value.stages.find((s: Stage) => s.status === 'ongoing' || s.status === 'completed' || (s.matches_count && s.matches_count > 0))
    selectedStageId.value = ongoing ? ongoing.id : tournament.value.stages[0].id
  }
}, { deep: true })

onMounted(() => {
  if (tournament.value && publicParticipants.value.length === 0) {
    fetchPublicParticipants()
  }
})

const fetchAllMatches = async () => {

  if (!tournament.value || !tournament.value.stages) return
  isLoadingAllMatches.value = true
  try {
    const results = await Promise.all(
      tournament.value.stages.map(s => 
        tournamentStore.fetchMatches(tournament.value!.slug, s.id)
      )
    )
    allMatches.value = results.flat()
  } catch (e) {
    console.error('Failed to fetch all matches', e)
  } finally {
    isLoadingAllMatches.value = false
  }
}
const isMatchModalOpen = ref(false)
const matchModalRef = ref<any>(null)
const bracketRef = ref<any>(null)

const handleMatchClick = (match: TournamentMatch) => {
  matchModalRef.value?.open(match)
}

const handleStageStarted = async (stageId: string) => {
  selectedStageId.value = stageId
  await refresh()
  
  // Switch to Bracket tab
  const bracketTabIndex = tabs.value.findIndex(t => t.slot === 'bracket')
  if (bracketTabIndex !== -1) {
    activeTabIndex.value = bracketTabIndex
  }
}

const handleMatchUpdated = () => {
  bracketRef.value?.fetchMatches()
  fetchLiveMatches()
}

// Auto-select first stage
watchEffect(() => {
  if (!selectedStageId.value && tournament.value?.stages && tournament.value.stages.length > 0) {
    const ongoing = tournament.value.stages.find((s: Stage) => s.status === 'ongoing' || s.status === 'completed' || (s.matches_count && s.matches_count > 0))
    selectedStageId.value = ongoing ? ongoing.id : tournament.value.stages[0].id
  }
})


// Ensure default tab is 'info' on first load
onMounted(() => {
  if (activeTabIndex.value === -1 || !activeTabIndex.value) {
    activeTabIndex.value = 0
  }
})


</script>

<template>
  <div class="min-h-screen bg-neutral-50 dark:bg-neutral-950 pb-20 relative overflow-hidden transition-colors duration-500">
    <!-- Loading State -->
    <div v-if="isPageLoading" class="fixed inset-0 z-[100] bg-neutral-50 dark:bg-neutral-950 flex flex-col items-center justify-center">
      <div class="relative">
        <div class="size-24 rounded-full border-4 border-primary-500/10 border-t-primary-500 animate-spin"></div>
        <UIcon name="i-lucide-trophy" class="size-10 text-primary-500 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" />
      </div>
      <p class="mt-8 text-neutral-500 dark:text-neutral-400 font-black uppercase tracking-[0.3em] text-xs animate-pulse">Menyiapkan Arena...</p>
    </div>


    <!-- Header Banner -->
    <div class="h-[400px] relative overflow-hidden">
      <img 
        v-if="tournament?.banner_url"
        :src="tournament.banner_url" 
        class="w-full h-full object-cover blur-sm scale-110 opacity-30 dark:opacity-20"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-neutral-50 via-neutral-50/80 to-neutral-50/20 dark:from-neutral-950 dark:via-neutral-950/80 dark:to-neutral-950/20"></div>
      
      <!-- Content Container -->
      <div class="absolute inset-0 flex items-center pt-20">
        <div class="max-w-7xl mx-auto px-4 w-full flex flex-col md:flex-row gap-12 items-end">
          <!-- Main Banner Container -->
          <div class="w-full md:w-[350px] aspect-video md:aspect-[3/4] rounded-[2.5rem] overflow-hidden border border-neutral-200 dark:border-white/10 shadow-2xl relative group shrink-0">
             <img 
              v-if="tournament?.banner_url"
              :src="tournament.banner_url" 
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-neutral-200 dark:bg-neutral-900">
               <UIcon name="i-lucide-trophy" class="size-20 text-neutral-400 dark:text-neutral-800" />
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 dark:from-black/80 to-transparent"></div>
          </div>

          <!-- Info Text -->
          <div class="flex-grow pb-8">
            <div class="flex items-center gap-3 mb-6">
              <UBadge v-if="tournamentStatus" :color="tournamentStatus.color" variant="solid" class="rounded-lg font-black uppercase text-[10px] tracking-widest px-4 py-1.5 flex items-center gap-2">
                <UIcon :name="tournamentStatus.icon" class="size-3" />
                {{ tournamentStatus.label }}
              </UBadge>
              <UBadge v-if="tournament?.sport" color="neutral" variant="outline" class="rounded-lg font-bold border-neutral-300 dark:border-white/20 text-neutral-600 dark:text-neutral-300 text-[10px] uppercase tracking-widest px-4 py-1.5 flex items-center gap-2">
                <UIcon name="i-lucide-award" class="size-3" />
                {{ tournament.sport.name }}
              </UBadge>
              <UBadge color="neutral" variant="outline" class="rounded-lg font-bold border-neutral-300 dark:border-white/20 text-neutral-500 text-[10px] uppercase tracking-widest px-4 py-1.5">
                {{ tournament?.category }}
              </UBadge>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-black text-neutral-900 dark:text-white uppercase tracking-tighter mb-4 leading-none">
              {{ tournament?.title || 'Memuat Turnamen...' }}
            </h1>
            
            <div class="flex flex-wrap items-center gap-8 mt-8">
               <div class="flex items-center gap-3">
                 <div class="size-10 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                   <UIcon name="i-lucide-trophy" class="text-yellow-500 size-5" />
                 </div>
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Total Prize</span>
                   <span class="text-lg font-black text-neutral-900 dark:text-white">{{ formatCurrency(tournament?.prize_pool) }}</span>
                 </div>
               </div>

               <div class="hidden md:block w-px h-10 bg-neutral-200 dark:bg-white/10"></div>

               <div class="flex items-center gap-3">
                 <div class="size-10 rounded-full bg-primary-500/10 flex items-center justify-center border border-primary-500/20">
                   <UIcon name="i-lucide-users-2" class="text-primary-500 dark:text-primary-400 size-5" />
                 </div>
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Partisipan</span>
                   <span class="text-lg font-black text-neutral-900 dark:text-white">{{ tournament?.current_participants || 0 }}/{{ tournament?.max_participants }}</span>
                 </div>
               </div>

               <div class="hidden md:block w-px h-10 bg-neutral-200 dark:bg-white/10"></div>

               <div class="flex items-center gap-3">
                 <div class="size-10 rounded-full bg-neutral-200 dark:bg-neutral-800 flex items-center justify-center border border-neutral-300 dark:border-white/5">
                   <UIcon name="i-lucide-calendar" class="text-neutral-600 dark:text-neutral-400 size-5" />
                 </div>
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Mulai Pada</span>
                   <span class="text-lg font-black text-neutral-900 dark:text-white">{{ formatDate(tournament?.start_at) }}</span>
                 </div>
               </div>

               <!-- Admin Actions -->
               <div v-if="isOwner && tournament?.status === 'draft'" class="ml-auto">
                 <UButton 
                  color="success" 
                  size="xl" 
                  class="rounded-2xl font-black uppercase tracking-widest px-8 shadow-[0_0_20px_rgba(34,197,94,0.3)] animate-pulse"
                  :loading="isPublishing"
                  @click="isPublishModalOpen = true"
                 >
                   Publish Turnamen
                 </UButton>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-if="tournament && tabs.length > 0" class="max-w-7xl mx-auto px-4 mt-20">
      <UTabs 
        v-model="activeTabIndex" 
        :items="tabs" 
        :key="`tabs-${tabs.length}-${tournament.id}`"
        @change="onTabChange"
        :ui="{ 
          list: 'bg-neutral-200/50 dark:bg-neutral-900/50 p-2 rounded-2xl ring-1 ring-neutral-200 dark:ring-white/5 inline-flex mb-12'
        }"
      >


        <template #live>
          <div class="py-8 space-y-8">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight">Pertandingan Berlangsung</h2>
                <p class="text-neutral-500 text-sm font-medium mt-1">Pantau dan update skor pertandingan yang sedang berjalan.</p>
              </div>
              <UButton 
                variant="ghost" 
                color="neutral" 
                icon="i-lucide-refresh-cw" 
                :loading="isLoadingLiveMatches"
                @click="fetchLiveMatches"
              >
                Refresh
              </UButton>
            </div>

            <div v-if="isLoadingLiveMatches" class="py-20 flex justify-center">
              <UIcon name="i-lucide-loader-2" class="size-10 text-primary-500 animate-spin" />
            </div>

            <div v-else-if="liveMatches.length === 0" class="bg-white dark:bg-neutral-900/40 border border-neutral-200 dark:border-white/5 rounded-[2rem] p-20 text-center">
              <UIcon name="i-lucide-play-circle" class="size-16 text-neutral-300 dark:text-neutral-800 mb-6 mx-auto" />
              <p class="text-neutral-500 font-bold uppercase tracking-widest">{{ $t('public.no_live_matches') }}</p>
              <p class="text-neutral-600 text-sm max-w-sm mx-auto mt-2">{{ $t('public.no_live_desc') }}</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <button
                v-for="match in liveMatches"
                :key="match.id"
                @click="handleMatchClick(match)"
                class="group relative bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-[1.5rem] p-6 text-left hover:border-primary-500/30 transition-all overflow-hidden"
              >
                <!-- Decorative background pulse -->
                <div class="absolute -right-10 -top-10 size-40 bg-primary-500/5 blur-3xl group-hover:bg-primary-500/10 transition-all"></div>
                
                <div class="flex items-center justify-between mb-6 relative">
                  <UBadge color="primary" variant="subtle" class="animate-pulse px-3 py-1 text-[10px] font-black uppercase tracking-widest">
                    LIVE NOW
                  </UBadge>
                  <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">M{{ match.match_number }} · Round {{ match.round }}</span>
                </div>

                <div class="space-y-4 relative">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-black text-neutral-900 dark:text-white truncate max-w-[150px]">{{ match.participant_1?.team?.name || match.participant_1?.user?.name || 'TBD' }}</span>
                    <span class="text-2xl font-black text-primary-500 dark:text-primary-400">{{ match.scores?.participant_1 ?? 0 }}</span>
                  </div>
                  <div class="flex items-center gap-4">
                    <div class="h-px flex-grow bg-neutral-200 dark:bg-white/5"></div>
                    <span class="text-[10px] font-black text-neutral-400 dark:text-neutral-700 uppercase tracking-widest italic">{{ $t('public.versus') }}</span>
                    <div class="h-px flex-grow bg-neutral-200 dark:bg-white/5"></div>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-black text-neutral-900 dark:text-white truncate max-w-[150px]">{{ match.participant_2?.team?.name || match.participant_2?.user?.name || 'TBD' }}</span>
                    <span class="text-2xl font-black text-primary-500 dark:text-primary-400">{{ match.scores?.participant_2 ?? 0 }}</span>
                  </div>
                </div>

                <div class="mt-8 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-neutral-600 border-t border-white/5 pt-4">
                  <span>{{ $t('public.click_update') }}</span>
                  <UIcon name="i-lucide-chevron-right" class="size-4 group-hover:translate-x-1 transition-transform" />
                </div>
              </button>
            </div>
          </div>
        </template>
        
        <template #matches>
          <div class="py-8">
            <div class="flex items-center justify-between mb-8">
              <div>
                <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight">Jadwal Pertandingan</h2>
                <p class="text-neutral-500 text-sm font-medium mt-1">Daftar lengkap pertandingan dalam turnamen ini.</p>
              </div>
              <UButton 
                variant="ghost" 
                color="neutral" 
                icon="i-lucide-refresh-cw" 
                :loading="isLoadingAllMatches"
                @click="fetchAllMatches"
              >
                Refresh
              </UButton>
            </div>

            <TournamentsTournamentScheduleViewer 
              :matches="allMatches" 
              :is-loading="isLoadingAllMatches"
              :is-staff="isOwner"
              @select-match="handleMatchClick"
            />
          </div>
        </template>

        <template #info>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 pt-4">
            <!-- Left: Description -->
            <div class="lg:col-span-2 space-y-12">
              <section>
                <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-6">Tentang Turnamen</h2>
                <div class="prose dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400 leading-relaxed font-medium">
                  {{ tournament?.description }}
                </div>
              </section>

              <section v-if="tournament?.venue" class="bg-white dark:bg-neutral-900/40 p-8 rounded-[2rem] border border-neutral-200 dark:border-white/5 shadow-sm">
                <div class="flex items-center gap-4 mb-6">
                  <UIcon name="i-lucide-map-pin" class="text-primary-500 size-6" />
                  <h3 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tight">Lokasi & Venue</h3>
                </div>
                <p class="text-neutral-600 dark:text-neutral-400 font-medium">{{ tournament?.venue }}</p>
              </section>
            </div>

            <!-- Right: Rules & Meta -->
            <div class="space-y-8">
               <div class="bg-primary-500/5 border border-primary-500/20 dark:border-primary-500/10 p-8 rounded-[2rem] relative overflow-hidden">
                 <UIcon name="i-lucide-shield-check" class="absolute -top-4 -right-4 size-24 text-primary-500/10" />
                 <h3 class="text-lg font-black text-neutral-900 dark:text-white uppercase tracking-wider mb-6 relative z-10">Pendaftaran</h3>
                 
                 <div class="space-y-4 mb-8">
                   <div class="flex justify-between items-center text-sm" v-if="tournament?.registration_start_at">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Mulai Daftar</span>
                     <span class="text-neutral-900 dark:text-white font-black uppercase text-xs">{{ formatDate(tournament.registration_start_at) }}</span>
                   </div>
                   <div class="flex justify-between items-center text-sm" v-if="tournament?.registration_end_at">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Tutup Daftar</span>
                     <span class="text-neutral-900 dark:text-white font-black uppercase text-xs">{{ formatDate(tournament.registration_end_at) }}</span>
                   </div>
                   <div class="flex justify-between items-center text-sm">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Biaya Masuk</span>
                     <span class="text-primary-600 dark:text-primary-400 font-black text-lg">{{ tournament?.entry_fee == 0 ? 'Gratis' : formatCurrency(tournament?.entry_fee) }}</span>
                   </div>
                   <div class="flex justify-between items-center text-sm">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Tipe Partisipan</span>
                     <span class="text-neutral-900 dark:text-white font-black uppercase text-xs">{{ tournament?.participant_type }}</span>
                   </div>
                   <div class="flex justify-between items-center text-sm">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Tipe Braket</span>
                     <span class="text-neutral-900 dark:text-white font-black uppercase text-xs">{{ tournament?.bracket_type?.replace('_', ' ') }}</span>
                   </div>
                 </div>

                 <div v-if="tournament">
                    <!-- Not yet joined & tournament is open -->
                    <template v-if="!userParticipation && (tournament.status === 'open' || tournament.status === 'registration')">
                      <UButton 
                        color="primary" 
                        block 
                        size="xl" 
                        class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-xl"
                        @click="handleJoinClick"
                      >
                        <UIcon name="i-lucide-user-plus" class="size-5 mr-2" />
                        Join Turnamen
                      </UButton>
                    </template>

                    <!-- Waiting for approval -->
                    <template v-else-if="userParticipation?.status === 'pending'">
                      <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-center">
                        <UIcon name="i-lucide-clock" class="size-6 text-amber-400 mx-auto mb-2" />
                        <p class="text-amber-400 font-black text-sm uppercase tracking-widest">Menunggu Persetujuan</p>
                        <p class="text-neutral-500 text-xs mt-1">Pendaftaran Anda sedang ditinjau oleh penyelenggara.</p>
                      </div>
                    </template>

                    <!-- Accepted -->
                    <template v-else-if="userParticipation?.status === 'approved' || userParticipation?.status === 'paid'">
                      <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4 text-center">
                        <UIcon name="i-lucide-check-circle-2" class="size-6 text-emerald-400 mx-auto mb-2" />
                        <p class="text-emerald-400 font-black text-sm uppercase tracking-widest">Sudah Bergabung</p>
                        <p class="text-neutral-500 text-xs mt-1">Pendaftaran Anda telah diterima. Selamat!</p>
                      </div>
                    </template>

                    <!-- Rejected -->
                    <template v-else-if="userParticipation?.status === 'rejected'">
                      <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 text-center mb-3">
                        <UIcon name="i-lucide-x-circle" class="size-6 text-red-400 mx-auto mb-2" />
                        <p class="text-red-400 font-black text-sm uppercase tracking-widest">Pendaftaran Ditolak</p>
                        <p class="text-neutral-500 text-xs mt-1">Hubungi penyelenggara untuk info lebih lanjut.</p>
                      </div>
                    </template>

                    <!-- Tournament not open -->
                    <template v-else-if="tournament.status === 'ongoing'">
                      <UButton 
                        color="neutral" 
                        disabled
                        block 
                        size="xl" 
                        class="rounded-2xl font-black uppercase tracking-widest py-4 bg-neutral-200 dark:bg-neutral-800 border-neutral-300 dark:border-white/5 opacity-50"
                      >
                        Sedang Berjalan
                      </UButton>
                    </template>

                    <template v-else-if="tournament.status !== 'open' && tournament.status !== 'registration'">
                      <UButton 
                        color="neutral" 
                        disabled
                        block 
                        size="xl" 
                        class="rounded-2xl font-black uppercase tracking-widest py-4 bg-neutral-200 dark:bg-neutral-800 border-neutral-300 dark:border-white/5 opacity-50"
                      >
                        Pendaftaran Tutup
                      </UButton>
                    </template>
                 </div>
               </div>

               <div class="bg-white dark:bg-neutral-900/40 border border-neutral-200 dark:border-white/5 p-8 rounded-[2rem] shadow-sm">
                 <h3 class="text-sm font-black text-neutral-500 uppercase tracking-widest mb-6">Penyelenggara</h3>
                 <div class="flex items-center gap-4">
                   <UAvatar 
                    :src="tournament?.user?.avatar || `https://i.pravatar.cc/150?u=${tournament?.user?.email}`" 
                    size="md" 
                    class="ring-2 ring-neutral-100 dark:ring-white/5 rounded-2xl"
                   />
                   <div>
                     <p class="text-neutral-900 dark:text-white font-black leading-none mb-1">{{ tournament?.user?.name }}</p>
                     <span class="text-[10px] text-neutral-500 dark:text-neutral-600 uppercase font-bold tracking-wider">Verified Organizer</span>
                   </div>
                 </div>
               </div>

               <TournamentsJoinTournamentModal 
                v-if="tournament"
                v-model="isJoinModalOpen"
                :tournament="tournament"
                @success="handleJoinSuccess"
              />
            </div>
          </div>
        </template>

        <template v-if="isOwner" #management>
          <div class="pt-8">
            <TournamentsTournamentStageManager 
              :tournament-slug="tournament!.slug" 
              :initial-stages="tournament!.stages"
              @stage-started="handleStageStarted"
            />
          </div>
        </template>
        
        <template v-if="isOwner" #staff>
          <div class="pt-8">
            <TournamentsTournamentStaffManager 
              :tournament-slug="tournament!.slug" 
              :initial-staff="tournament!.staff" 
            />
          </div>
        </template>

        <template #bracket>
          <div class="py-8">
            <div class="flex items-center justify-between mb-8">
              <div>
                <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight">Bracket</h2>
                <p class="text-neutral-500 text-sm mt-1">Visualisasi struktur turnamen saat ini.</p>
              </div>
              <!-- Stage Selector -->
              <div v-if="ongoingStages.length > 1" class="flex items-center gap-2">
                <button
                  v-for="s in ongoingStages"
                  :key="s.id"
                  @click="selectedStageId = s.id"
                  :class="[
                    'px-4 py-2 rounded-xl text-xs font-bold border transition-all',
                    selectedStageId === s.id
                      ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                      : 'border-neutral-700 text-neutral-500 hover:border-primary-500/50'
                  ]"
                >
                  {{ s.name }}
                </button>
              </div>
            </div>

            <div class="bg-white dark:bg-neutral-900/40 rounded-[2rem] border border-neutral-200 dark:border-white/5 w-full h-[600px] relative overflow-hidden">
              <template v-if="selectedStage">
                <InteractiveCanvas>
                  <TournamentsBracketVisualizer
                    ref="bracketRef"
                    :tournament-slug="tournament!.slug"
                    :stage="selectedStage"
                    :is-staff="isOwner"
                    @match-click="handleMatchClick"
                  />
                </InteractiveCanvas>
              </template>
              <template v-else>
                <div class="flex flex-col items-center justify-center py-20 text-center h-full">
                  <UIcon name="i-lucide-git-merge" class="size-16 text-neutral-300 dark:text-neutral-800 mb-6" />
                  <p class="text-neutral-500 dark:text-neutral-600 font-bold uppercase tracking-widest mb-2">{{ $t('public.no_bracket') }}</p>
                  <p class="text-neutral-400 dark:text-neutral-700 text-sm max-w-sm">{{ $t('public.no_bracket_desc') }}</p>
                </div>
              </template>
            </div>

            <!-- Match Detail Modal -->
            <TournamentsMatchDetailModal
              ref="matchModalRef"
              v-model="isMatchModalOpen"
              :tournament-slug="tournament!.slug"
              :stage-id="selectedStage?.id || ''"
              :bo-format="selectedStage?.bo_format || 'bo1'"
              :is-staff="isOwner"
              :is-owner="isOwner"
              @updated="handleMatchUpdated"
            />
          </div>
        </template>

        <template #participants>
           <div class="pt-8">
             <!-- Dashboard View for Organizer -->
             <TournamentsTournamentParticipantManager 
               v-if="isOwner"
               :tournament-slug="tournament.slug" 
               :initial-participants="tournament.participants || []" 
               :participant-type="tournament.participant_type"
               :is-read-only="false"
             />

             <!-- Public View for Guests/Players -->
             <div v-else>
                <div v-if="isLoadingParticipants" class="py-20 flex justify-center">
                  <div class="size-12 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin"></div>
                </div>
                <div v-else-if="publicParticipants.filter(p => p.status !== 'rejected').length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                  <div 
                    v-for="p in publicParticipants.filter(p => p.status !== 'rejected')" 
                    :key="p.id" 
                    class="bg-white dark:bg-neutral-900/40 border border-neutral-200 dark:border-white/5 rounded-[2rem] p-6 flex items-center gap-4 hover:border-primary-500/30 transition-colors relative shadow-sm"
                  >
                    <UBadge v-if="p.status === 'pending'" color="amber" variant="subtle" size="xs" class="absolute top-3 right-3 text-[9px] uppercase font-black tracking-widest">
                      Pending
                    </UBadge>
                    <UAvatar 
                      :src="tournament.participant_type === 'team' ? p.team?.logo : p.user?.avatar" 
                      :alt="tournament.participant_type === 'team' ? p.team?.name : p.user?.name"
                      size="lg"
                      class="ring-2 ring-neutral-100 dark:ring-white/10"
                    />
                    <div>
                      <h4 class="text-neutral-900 dark:text-white font-black uppercase tracking-wider text-sm pr-12">{{ tournament.participant_type === 'team' ? p.team?.name : p.user?.name }}</h4>
                      <p class="text-neutral-500 text-xs mt-0.5">{{ tournament.participant_type === 'team' ? (p.team?.members?.length || 1) + ' Anggota' : 'Individual' }}</p>
                    </div>
                  </div>
                </div>
                <!-- Friendly empty state -->
                <div v-else class="py-20 flex flex-col items-center justify-center text-center">
                  <div class="bg-white dark:bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-neutral-200 dark:ring-white/5 shadow-sm">
                    <UIcon name="i-lucide-users" class="size-20 text-neutral-300 dark:text-neutral-700" />
                  </div>
                  <h3 class="text-2xl font-black text-neutral-900 dark:text-white mb-2 uppercase tracking-tight">Belum Ada Pendaftar</h3>
                  <p class="text-neutral-500 dark:text-neutral-600 font-medium max-w-sm">Jadilah tim pertama yang bergabung dalam turnamen bergengsi ini!</p>
                </div>
             </div>
           </div>
        </template>




        <template #standings>
          <div class="py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
              <div>
                <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight">Klasemen Turnamen</h2>
                <p class="text-neutral-500 text-sm mt-1">Peringkat tim berdasarkan performa pertandingan.</p>
              </div>

              <div class="flex items-center gap-3">
                <!-- Group Selector for Round Robin -->
                <div v-if="selectedStage?.type === 'round_robin' && (selectedStage.groups?.length || 0) > 1" class="flex items-center gap-2">
                  <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Grup:</span>
                  <USelectMenu
                    v-model="selectedGroupId"
                    :options="selectedStage.groups || []"
                    value-attribute="id"
                    option-attribute="name"
                    size="sm"
                    class="w-32"
                    @update:model-value="fetchStandingsData"
                  />
                </div>
                
                <UButton 
                  variant="ghost" 
                  color="neutral" 
                  icon="i-lucide-refresh-cw" 
                  :loading="isLoadingStandings"
                  @click="fetchStandingsData"
                >
                  Refresh
                </UButton>
              </div>
            </div>

            <TournamentsStandingsTable 
              :standings="standings" 
              :is-loading="isLoadingStandings" 
            />
          </div>
        </template>

      </UTabs>
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
              <h3 class="text-xl font-black text-white uppercase tracking-wider leading-tight">Konfirmasi Publish</h3>
              <p class="text-sm text-neutral-400 font-medium">Anda yakin mempublikasikan turnamen ini?</p>
            </div>
          </div>
          
          <div class="space-y-3 pl-4 border-l-2 border-white/5 text-sm text-neutral-300">
            <p class="font-medium text-neutral-400">Setelah dipublikasi:</p>
            <ul class="list-disc list-inside space-y-2 text-neutral-400 font-medium ml-2">
              <li>Dapat dilihat oleh semua orang secara publik.</li>
              <li>Pendaftar dapat mulai mendaftar ke turnamen.</li>
              <li>Beberapa pengaturan tidak dapat diubah lagi.</li>
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
            Batal
          </UButton>
          <UButton 
            color="success" 
            class="font-black uppercase tracking-wider shadow-[0_0_15px_rgba(34,197,94,0.3)] px-6"
            :loading="isPublishing"
            @click="publish"
          >
            Ya, Publish
          </UButton>
        </div>
      </template>
    </UModal>
  </div>
</template>
