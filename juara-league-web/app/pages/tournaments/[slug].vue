<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Tournament } from '~/types/tournament'
import { getTournamentStatus } from '~/utils/tournamentStatus'

const route = useRoute()
const tournamentStore = useTournamentStore()
const { user } = useAuth()
const { isLoading, error } = storeToRefs(tournamentStore)

const tournament = ref<Tournament | null>(null)
const tournamentStatus = computed(() => getTournamentStatus(tournament.value?.status))

// Initial fetch
const { data, refresh } = await useAsyncData(`tournament-${route.params.slug}`, () => 
  tournamentStore.getBySlug(route.params.slug as string)
)

if (data.value) {
  tournament.value = data.value as Tournament
}

const isOwner = computed(() => {
  return user.value && tournament.value?.user?.id === user.value.id
})

const isPublishing = ref(false)
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
  if (data.value) {
    tournament.value = data.value as any
  }
}

const publish = async () => {
  if (!tournament.value) return
  isPublishing.value = true
  try {
    await tournamentStore.publishTournament(tournament.value.slug)
    await refresh()
    tournament.value = data.value as Tournament
    useToast().add({ title: 'Berhasil!', description: 'Turnamen telah dipublikasikan ke publik.', color: 'success' })
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal mempublikasikan turnamen', color: 'error' })
  } finally {
    isPublishing.value = false
  }
}

const tabs = computed(() => {
  const items = [
    { label: 'Informasi', icon: 'i-lucide-info', slot: 'info' },
    { label: 'Braket (Demo)', icon: 'i-lucide-git-branch', slot: 'bracket' },
    { label: 'Partisipan', icon: 'i-lucide-users', slot: 'participants' }
  ]

  if (isOwner.value) {
    items.splice(1, 0, { label: 'Manajemen Tahapan', icon: 'i-lucide-settings-2', slot: 'management' })
    items.splice(2, 0, { label: 'Manajemen Staf', icon: 'i-lucide-shield-check', slot: 'staff' })
  }

  return items
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

// Dummy Bracket Data (since Modul 3 is not yet implemented)
const dummyBracket = [
  { id: 1, teamA: 'Team Alpha', teamB: 'Team Beta', scoreA: 2, scoreB: 1, status: 'finished' },
  { id: 2, teamA: 'Team Gamma', teamB: 'Team Delta', scoreA: 0, scoreB: 2, status: 'finished' },
  { id: 3, teamA: 'Team Alpha', teamB: 'Team Delta', scoreA: null, scoreB: null, status: 'upcoming' }
]
</script>

<template>
  <div class="min-h-screen bg-neutral-950 pb-20 relative overflow-hidden">
    <!-- Header Banner -->
    <div class="h-[400px] relative overflow-hidden">
      <img 
        v-if="tournament?.banner_url"
        :src="tournament.banner_url" 
        class="w-full h-full object-cover blur-sm scale-110 opacity-30"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/80 to-neutral-950/20"></div>
      
      <!-- Content Container -->
      <div class="absolute inset-0 flex items-center pt-20">
        <div class="max-w-7xl mx-auto px-4 w-full flex flex-col md:flex-row gap-12 items-end">
          <!-- Main Banner Container -->
          <div class="w-full md:w-[350px] aspect-video md:aspect-[3/4] rounded-[2.5rem] overflow-hidden border border-white/10 shadow-2xl relative group shrink-0">
             <img 
              v-if="tournament?.banner_url"
              :src="tournament.banner_url" 
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-neutral-900">
               <UIcon name="i-lucide-trophy" class="size-20 text-neutral-800" />
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
          </div>

          <!-- Info Text -->
          <div class="flex-grow pb-8">
            <div class="flex items-center gap-3 mb-6">
              <UBadge v-if="tournamentStatus" :color="tournamentStatus.color" variant="solid" class="rounded-lg font-black uppercase text-[10px] tracking-widest px-4 py-1.5 flex items-center gap-2">
                <UIcon :name="tournamentStatus.icon" class="size-3" />
                {{ tournamentStatus.label }}
              </UBadge>
              <UBadge v-if="tournament?.sport" color="neutral" variant="outline" class="rounded-lg font-bold border-white/20 text-neutral-300 text-[10px] uppercase tracking-widest px-4 py-1.5 flex items-center gap-2">
                <UIcon name="i-lucide-award" class="size-3" />
                {{ tournament.sport.name }}
              </UBadge>
              <UBadge color="neutral" variant="outline" class="rounded-lg font-bold border-white/20 text-neutral-500 text-[10px] uppercase tracking-widest px-4 py-1.5">
                {{ tournament?.category }}
              </UBadge>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-black text-white uppercase tracking-tighter mb-4 leading-none">
              {{ tournament?.title || 'Memuat Turnamen...' }}
            </h1>
            
            <div class="flex flex-wrap items-center gap-8 mt-8">
               <div class="flex items-center gap-3">
                 <div class="size-10 rounded-full bg-yellow-500/10 flex items-center justify-center border border-yellow-500/20">
                   <UIcon name="i-lucide-trophy" class="text-yellow-500 size-5" />
                 </div>
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Total Prize</span>
                   <span class="text-lg font-black text-white">{{ formatCurrency(tournament?.prize_pool) }}</span>
                 </div>
               </div>

               <div class="hidden md:block w-px h-10 bg-white/10"></div>

               <div class="flex items-center gap-3">
                 <div class="size-10 rounded-full bg-primary-500/10 flex items-center justify-center border border-primary-500/20">
                   <UIcon name="i-lucide-users-2" class="text-primary-400 size-5" />
                 </div>
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Partisipan</span>
                   <span class="text-lg font-black text-white">{{ tournament?.current_participants || 0 }}/{{ tournament?.max_participants }}</span>
                 </div>
               </div>

               <div class="hidden md:block w-px h-10 bg-white/10"></div>

               <div class="flex items-center gap-3">
                 <div class="size-10 rounded-full bg-neutral-800 flex items-center justify-center border border-white/5">
                   <UIcon name="i-lucide-calendar" class="text-neutral-400 size-5" />
                 </div>
                 <div class="flex flex-col">
                   <span class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Mulai Pada</span>
                   <span class="text-lg font-black text-white">{{ formatDate(tournament?.start_at) }}</span>
                 </div>
               </div>

               <!-- Admin Actions -->
               <div v-if="isOwner && tournament?.status === 'draft'" class="ml-auto">
                 <UButton 
                  color="success" 
                  size="xl" 
                  class="rounded-2xl font-black uppercase tracking-widest px-8 shadow-[0_0_20px_rgba(34,197,94,0.3)] animate-pulse"
                  :loading="isPublishing"
                  @click="publish"
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
    <div class="max-w-7xl mx-auto px-4 mt-20">
      <UTabs :items="tabs" :ui="{ 
        list: 'bg-neutral-900/50 p-2 rounded-2xl ring-1 ring-white/5 inline-flex mb-12'
      }">
        <template #info>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 pt-4">
            <!-- Left: Description -->
            <div class="lg:col-span-2 space-y-12">
              <section>
                <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-6">Tentang Turnamen</h2>
                <div class="prose prose-invert max-w-none text-neutral-400 leading-relaxed font-medium">
                  {{ tournament?.description }}
                </div>
              </section>

              <section v-if="tournament?.venue" class="bg-neutral-900/40 p-8 rounded-[2rem] border border-white/5">
                <div class="flex items-center gap-4 mb-6">
                  <UIcon name="i-lucide-map-pin" class="text-primary-500 size-6" />
                  <h3 class="text-xl font-black text-white uppercase tracking-tight">Lokasi & Venue</h3>
                </div>
                <p class="text-neutral-400 font-medium">{{ tournament?.venue }}</p>
              </section>
            </div>

            <!-- Right: Rules & Meta -->
            <div class="space-y-8">
               <div class="bg-primary-500/5 border border-primary-500/10 p-8 rounded-[2rem] relative overflow-hidden">
                 <UIcon name="i-lucide-shield-check" class="absolute -top-4 -right-4 size-24 text-primary-500/10" />
                 <h3 class="text-lg font-black text-white uppercase tracking-wider mb-6 relative z-10">Pendaftaran</h3>
                 
                 <div class="space-y-4 mb-8">
                   <div class="flex justify-between items-center text-sm">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Biaya Masuk</span>
                     <span class="text-primary-400 font-black text-lg">{{ tournament?.entry_fee == 0 ? 'Gratis' : formatCurrency(tournament?.entry_fee) }}</span>
                   </div>
                   <div class="flex justify-between items-center text-sm">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Tipe Partisipan</span>
                     <span class="text-white font-black uppercase text-xs">{{ tournament?.participant_type }}</span>
                   </div>
                   <div class="flex justify-between items-center text-sm">
                     <span class="text-neutral-500 font-bold uppercase tracking-widest text-[10px]">Tipe Braket</span>
                     <span class="text-white font-black uppercase text-xs">{{ tournament?.bracket_type?.replace('_', ' ') }}</span>
                   </div>
                 </div>

                 <div v-if="tournament">
                    <!-- Not yet joined & tournament is open -->
                    <template v-if="!userParticipation && tournament.status === 'open'">
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
                        class="rounded-2xl font-black uppercase tracking-widest py-4 bg-neutral-800 border-white/5 opacity-50"
                      >
                        Sedang Berjalan
                      </UButton>
                    </template>

                    <template v-else-if="tournament.status !== 'open'">
                      <UButton 
                        color="neutral" 
                        disabled
                        block 
                        size="xl" 
                        class="rounded-2xl font-black uppercase tracking-widest py-4 bg-neutral-800 border-white/5 opacity-50"
                      >
                        Pendaftaran Tutup
                      </UButton>
                    </template>
                 </div>
               </div>

               <div class="bg-neutral-900/40 border border-white/5 p-8 rounded-[2rem]">
                 <h3 class="text-sm font-black text-neutral-500 uppercase tracking-widest mb-6">Penyelenggara</h3>
                 <div class="flex items-center gap-4">
                   <UAvatar 
                    :src="tournament?.user?.avatar || `https://i.pravatar.cc/150?u=${tournament?.user?.email}`" 
                    size="md" 
                    class="ring-2 ring-white/5 rounded-2xl"
                   />
                   <div>
                     <p class="text-white font-black leading-none mb-1">{{ tournament?.user?.name }}</p>
                     <span class="text-[10px] text-neutral-600 uppercase font-bold tracking-wider">Verified Organizer</span>
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
          <div class="py-12 flex flex-col items-center">
            <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-4">Bracket Visualizer</h2>
            <p class="text-neutral-500 mb-12">Visualisasi struktur turnamen saat ini.</p>
            <div class="bg-neutral-900/40 p-12 rounded-[3rem] border border-white/5 w-full min-h-[500px] flex items-center justify-center overflow-auto">
              <!-- Placeholder for Interactive Bracket -->
              <div class="text-center">
                <UIcon name="i-lucide-git-merge" class="size-20 text-neutral-800 mb-6" />
                <p class="text-neutral-600 font-bold uppercase tracking-widest">Integrasi Modul 3 & 4 Segera Hadir</p>
              </div>
            </div>
          </div>
        </template>

        <template #participants>
           <div class="pt-8">
             <template v-if="isOwner">
               <TournamentsTournamentParticipantManager 
                 :tournament-slug="tournament!.slug" 
                 :initial-participants="tournament!.participants" 
               />
             </template>
             <template v-else>
               <div class="py-20 flex flex-col items-center justify-center text-center">
                 <div class="bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-white/5">
                   <UIcon name="i-lucide-users" class="size-20 text-neutral-700" />
                 </div>
                 <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-tight">Belum Ada Pendaftar</h3>
                 <p class="text-neutral-600 font-medium max-w-sm">Jadilah tim pertama yang bergabung dalam turnamen bergengsi ini!</p>
               </div>
             </template>
           </div>
        </template>
      </UTabs>
    </div>
  </div>
</template>
