<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
import { useTournamentStore } from '~/stores/tournamentStore'

const { t } = useI18n()
const tournamentStore = useTournamentStore()

const { data: overview, refresh, status } = await useAsyncData('dashboard-overview', () => tournamentStore.fetchDashboardOverview())

const items = computed(() => [[{
  label: t('home.create_tournament'),
  icon: 'i-lucide-plus',
  to: '/dashboard/tournaments/create'
}]] satisfies DropdownMenuItem[][])

const getParticipantName = (p: any) => {
  if (!p) return 'TBD'
  return p.team?.name || p.user?.name || 'TBD'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<template>
  <UDashboardPanel id="home">
    <template #header>
      <UDashboardNavbar :title="$t('home.summary')" :ui="{ right: 'gap-3' }">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>

        <template #right>
          <UButton
            variant="ghost"
            color="neutral"
            icon="i-lucide-rotate-cw"
            :loading="status === 'pending'"
            @click="refresh()"
          />

          <UDropdownMenu :items="items">
            <UButton icon="i-lucide-plus" size="md" class="rounded-full" color="primary" />
          </UDropdownMenu>
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="space-y-8 p-4 lg:p-8">
        <!-- Stats Row -->
        <HomeStats :stats="overview?.stats" />

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
          <!-- Left Col: Upcoming Matches & Recent Tournaments -->
          <div class="xl:col-span-2 space-y-8">
            <!-- Upcoming Matches Section -->
            <section class="space-y-4">
              <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                  <div class="size-8 rounded-lg bg-primary-500/10 flex items-center justify-center">
                    <UIcon name="i-lucide-calendar-days" class="size-5 text-primary-500" />
                  </div>
                  <h3 class="text-sm font-black text-neutral-900 dark:text-white uppercase tracking-widest">Upcoming Matches</h3>
                </div>
                <UButton variant="link" color="neutral" size="xs" to="/dashboard/tournaments" class="font-bold uppercase tracking-widest text-[10px]">Lihat Semua</UButton>
              </div>

              <div v-if="overview?.upcoming_matches.length === 0" class="p-12 text-center bg-white dark:bg-neutral-900 rounded-[2rem] border border-neutral-200 dark:border-white/5 border-dashed">
                <p class="text-[11px] font-bold text-neutral-500 uppercase tracking-widest">Tidak ada jadwal terdekat</p>
              </div>

              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="match in overview?.upcoming_matches" 
                  :key="match.id"
                  class="p-6 bg-white dark:bg-neutral-900/50 rounded-[2rem] border border-neutral-200 dark:border-white/5 hover:border-primary-500/30 transition-all group relative overflow-hidden"
                >
                  <div class="flex flex-col gap-4 relative z-10">
                    <div class="flex items-center justify-between">
                      <span class="text-[9px] font-black text-primary-500 uppercase tracking-[0.2em]">{{ match.stage?.tournament?.title }}</span>
                      <span class="text-[9px] font-bold text-neutral-400 italic">{{ formatDate(match.scheduled_at) }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                      <div class="flex-1 text-center">
                        <p class="text-xs font-black text-neutral-900 dark:text-white uppercase truncate">{{ getParticipantName(match.participant1) }}</p>
                      </div>
                      <span class="text-[10px] font-black text-neutral-300 dark:text-neutral-700 italic">VS</span>
                      <div class="flex-1 text-center">
                        <p class="text-xs font-black text-neutral-900 dark:text-white uppercase truncate">{{ getParticipantName(match.participant2) }}</p>
                      </div>
                    </div>
                  </div>
                  <!-- BG Glow -->
                  <div class="absolute -top-10 -right-10 size-24 bg-primary-500/5 blur-2xl rounded-full group-hover:bg-primary-500/10 transition-colors"></div>
                </div>
              </div>
            </section>

            <!-- Recent Tournaments -->
            <section class="space-y-4">
               <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2">
                  <div class="size-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <UIcon name="i-lucide-layout-grid" class="size-5 text-emerald-500" />
                  </div>
                  <h3 class="text-sm font-black text-neutral-900 dark:text-white uppercase tracking-widest">Turnamen Terbaru</h3>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-3">
                 <NuxtLink 
                   v-for="t in overview?.recent_tournaments" 
                   :key="t.id"
                   :to="`/dashboard/tournaments/${t.slug}/stages`"
                   class="flex items-center justify-between p-5 bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200 dark:border-white/5 hover:bg-neutral-50 dark:hover:bg-white/5 transition-all"
                 >
                    <div class="flex items-center gap-4">
                       <div class="size-10 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-white/5 overflow-hidden">
                          <img v-if="t.banner" :src="t.banner" class="size-full object-cover" />
                          <UIcon v-else name="i-lucide-trophy" class="size-5 text-neutral-400" />
                       </div>
                       <div>
                          <p class="text-xs font-black text-neutral-900 dark:text-white uppercase">{{ t.title }}</p>
                          <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mt-0.5">{{ t.participants_count }} Peserta • {{ t.status }}</p>
                       </div>
                    </div>
                    <UIcon name="i-lucide-chevron-right" class="size-4 text-neutral-300" />
                 </NuxtLink>
              </div>
            </section>
          </div>

          <!-- Right Col: Recent Registrations -->
          <div class="space-y-6">
             <section class="space-y-4">
                <div class="flex items-center gap-2 px-2">
                  <div class="size-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <UIcon name="i-lucide-user-check" class="size-5 text-amber-500" />
                  </div>
                  <h3 class="text-sm font-black text-neutral-900 dark:text-white uppercase tracking-widest">Pendaftaran Baru</h3>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-[2.5rem] border border-neutral-200 dark:border-white/5 p-6 space-y-4 relative overflow-hidden">
                   <div v-if="overview?.recent_participants.length === 0" class="py-12 text-center">
                      <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Belum ada pendaftaran baru</p>
                   </div>
                   
                   <div v-for="p in overview?.recent_participants" :key="p.id" class="flex items-center gap-4 group">
                      <UAvatar 
                        :src="p.team?.logo || p.user?.avatar" 
                        size="md" 
                        class="ring-2 ring-neutral-100 dark:ring-white/5 group-hover:scale-105 transition-transform" 
                      />
                      <div class="flex-1 min-w-0">
                         <p class="text-xs font-black text-neutral-900 dark:text-white uppercase truncate">{{ p.team?.name || p.user?.name }}</p>
                         <p class="text-[9px] font-bold text-neutral-400 uppercase tracking-tighter truncate">{{ p.tournament?.title }}</p>
                      </div>
                      <UButton 
                        icon="i-lucide-chevron-right" 
                        variant="ghost" 
                        color="neutral" 
                        size="xs" 
                        :to="`/dashboard/tournaments/${p.tournament?.slug}/participants`" 
                      />
                   </div>

                   <!-- Decoration -->
                   <div class="absolute -bottom-20 -right-20 size-40 bg-amber-500/5 blur-3xl rounded-full"></div>
                </div>
             </section>

             <!-- Quick Help Card -->
             <div class="p-8 bg-gradient-to-br from-primary-600 to-primary-800 rounded-[2.5rem] text-white relative overflow-hidden group shadow-xl">
                <div class="relative z-10 space-y-4">
                   <UIcon name="i-lucide-help-circle" class="size-8 text-white/50" />
                   <h4 class="text-lg font-black uppercase leading-tight">Butuh Bantuan Mengelola Turnamen?</h4>
                   <p class="text-[11px] text-white/70 font-medium leading-relaxed">Cek panduan lengkap kami untuk membuat bracket, mengatur jadwal, hingga melakukan siaran langsung.</p>
                   <UButton color="neutral" variant="solid" class="bg-white text-primary-900 font-black uppercase tracking-widest text-[10px] rounded-xl px-6">Buka Panduan</UButton>
                </div>
                <!-- Abstract Pattern -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 blur-[60px] rounded-full translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-black/20 blur-[60px] rounded-full -translate-x-1/2 translate-y-1/2"></div>
             </div>
          </div>
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>
