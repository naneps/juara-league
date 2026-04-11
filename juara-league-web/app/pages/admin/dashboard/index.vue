<script setup lang="ts">
import { useAdminStore } from '~/stores/admin.store'

definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const adminStore = useAdminStore()
const { stats, isLoading, error } = storeToRefs(adminStore)

// Menggunakan useAsyncData untuk inisialisasi data, 
// tapi kita handle di store agar logic terpusat.
const { refresh, status } = await useAsyncData('admin-stats-init', () => 
  adminStore.fetchStats().then(() => true),
  { lazy: true }
)

const handleRefresh = async () => {
  await refresh()
}
</script>

<template>
  <UDashboardPanel id="admin_overview_panel">
    <template #header>
      <UDashboardNavbar title="Panel Administrasi" description="Kelola platform Juara League secara menyeluruh.">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <UButton
            icon="i-lucide-refresh-cw"
            variant="ghost"
            color="neutral"
            :loading="isLoading || status === 'pending'"
            @click="handleRefresh"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div v-if="error && !stats" class="p-12 text-center">
        <div class="p-8 rounded-3xl bg-error-500/10 border border-error-500/20 max-w-md mx-auto">
          <UIcon name="i-lucide-alert-triangle" class="size-12 text-error-500 mb-4" />
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Gagal Memuat Statistik</h3>
          <p class="text-sm text-gray-600 dark:text-neutral-400 mb-6">{{ error }}</p>
          <UButton color="neutral" variant="outline" @click="handleRefresh">Coba Lagi</UButton>
        </div>
      </div>

      <div v-else-if="stats" class="space-y-8 pb-12">
        <!-- High-Level Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- User Stats -->
          <UCard class="bg-indigo-500 dark:bg-indigo-600 border-none shadow-xl shadow-indigo-500/20 group hover:scale-[1.02] transition-transform duration-300">
            <div class="flex flex-col gap-4">
              <div class="flex items-center justify-between">
                <div class="p-3 bg-white/20 rounded-2xl">
                  <UIcon name="i-lucide-users" class="w-6 h-6 text-gray-900 dark:text-white" />
                </div>
                <div class="flex flex-col items-end">
                  <span class="text-[10px] font-black text-gray-900 dark:text-white/60 uppercase tracking-widest">Growth (30d)</span>
                  <span class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1">
                    <UIcon name="i-lucide-trending-up" class="size-3" />
                    {{ stats.overview?.users?.growth || 0 }}%
                  </span>
                </div>
              </div>
              <div>
                <p class="text-indigo-100 text-sm font-bold tracking-tight">Total Pengguna</p>
                <h3 class="text-4xl font-black text-gray-900 dark:text-white italic tracking-tighter">{{ stats.overview?.users?.total || 0 }}</h3>
                <p class="text-[10px] text-gray-900 dark:text-white/50 mt-1 uppercase font-bold tracking-widest">{{ stats.overview?.users?.verified || 0 }} Verified</p>
              </div>
            </div>
          </UCard>

          <!-- Tournament Stats -->
          <UCard class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-white/5 hover:border-indigo-500/30 transition-all duration-300 group">
            <div class="flex flex-col gap-4">
              <div class="flex items-center justify-between">
                <div class="p-3 bg-amber-500/10 rounded-2xl group-hover:bg-amber-500/20 transition-colors">
                  <UIcon name="i-lucide-trophy" class="w-6 h-6 text-amber-500" />
                </div>
                <UBadge v-if="stats.overview?.tournaments?.pending > 0" color="error" variant="subtle" size="sm" class="animate-pulse">
                  {{ stats.overview?.tournaments?.pending }} Review
                </UBadge>
              </div>
              <div>
                <p class="text-gray-600 dark:text-neutral-400 text-sm font-bold tracking-tight">Total Turnamen</p>
                <h3 class="text-4xl font-black text-gray-900 dark:text-white italic tracking-tighter">{{ stats.overview?.tournaments?.total || 0 }}</h3>
                <p class="text-[10px] text-neutral-500 mt-1 uppercase font-bold tracking-widest">{{ stats.overview?.tournaments?.active || 0 }} Sedang Berlangsung</p>
              </div>
            </div>
          </UCard>

          <!-- Team Stats -->
          <UCard class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-white/5 hover:border-emerald-500/30 transition-all duration-300 group">
            <div class="flex flex-col gap-4">
              <div class="flex items-center justify-between">
                <div class="p-3 bg-emerald-500/10 rounded-2xl group-hover:bg-emerald-500/20 transition-colors">
                  <UIcon name="i-lucide-users-round" class="w-6 h-6 text-emerald-500" />
                </div>
              </div>
              <div>
                <p class="text-gray-600 dark:text-neutral-400 text-sm font-bold tracking-tight">Total Tim</p>
                <h3 class="text-4xl font-black text-gray-900 dark:text-white italic tracking-tighter">{{ stats.overview?.teams?.total || 0 }}</h3>
                <p class="text-[10px] text-neutral-500 mt-1 uppercase font-bold tracking-widest">Active squads</p>
              </div>
            </div>
          </UCard>

          <!-- Platform Status -->
          <UCard class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-white/5">
            <div class="flex flex-col h-full justify-between gap-4">
              <div class="flex items-center justify-between">
                <div class="p-3 bg-gray-100 dark:bg-white/5 rounded-2xl">
                  <UIcon name="i-lucide-activity" class="w-6 h-6 text-gray-600 dark:text-neutral-400" />
                </div>
              </div>
              <div>
                <p class="text-neutral-500 text-xs font-bold uppercase tracking-widest mb-2">Platform Health</p>
                <div class="flex items-center gap-3">
                  <div class="size-4 rounded-full bg-emerald-500 animate-ping absolute opacity-50" />
                  <div class="size-4 rounded-full bg-emerald-500 relative" />
                  <span class="text-xl font-black text-gray-900 dark:text-white uppercase italic tracking-widest">Optimal</span>
                </div>
              </div>
            </div>
          </UCard>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Recent Activity Feed -->
          <UCard class="lg:col-span-2 bg-gray-100 dark:bg-white/50 dark:bg-neutral-900/40 backdrop-blur-xl border border-gray-200 dark:border-white/5" :ui="{ header: 'border-b border-gray-200 dark:border-white/5', body: 'p-0' }">
            <template #header>
              <div class="flex items-center justify-between px-2">
                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Aktivitas Terkini</h3>
                <UButton label="Lihat Semua" variant="link" color="neutral" size="xs" />
              </div>
            </template>

            <div class="divide-y divide-white/5">
              <div v-for="activity in stats.activities" :key="activity.id" class="p-5 flex items-start gap-4 hover:bg-gray-50 dark:bg-white/[0.02] transition-colors group">
                <div class="size-10 rounded-xl bg-indigo-500/10 flex items-center justify-center shrink-0 border border-indigo-500/20">
                  <UIcon name="i-lucide-award" class="size-5 text-indigo-400" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-neutral-200 font-medium">
                    <span class="font-bold text-gray-900 dark:text-white">{{ activity.user }}</span> membuat turnamen
                    <span class="font-bold text-indigo-400 italic">"{{ activity.title }}"</span>
                  </p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] text-neutral-500 flex items-center gap-1 uppercase tracking-widest">
                      <UIcon name="i-lucide-clock" class="size-3" />
                      {{ activity.time }}
                    </span>
                  </div>
                </div>
                <UButton icon="i-lucide-chevron-right" variant="ghost" color="neutral" size="xs" class="opacity-0 group-hover:opacity-100 transition-opacity" />
              </div>

              <div v-if="!stats.activities?.length" class="py-20 text-center opacity-20">
                <UIcon name="i-lucide-inbox" class="size-12 mx-auto mb-4" />
                <p class="text-xs uppercase tracking-widest font-bold">Belum ada aktivitas</p>
              </div>
            </div>
          </UCard>

          <!-- Sport Distribution -->
          <UCard class="bg-gray-100 dark:bg-white/50 dark:bg-neutral-900/40 backdrop-blur-xl border border-gray-200 dark:border-white/5" :ui="{ header: 'border-b border-gray-200 dark:border-white/5' }">
            <template #header>
              <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest">Olahraga Terpopuler</h3>
            </template>

            <div class="space-y-6">
              <div v-for="sport in stats.distribution" :key="sport.name" class="space-y-2">
                <div class="flex justify-between items-end">
                  <span class="text-xs font-bold text-gray-700 dark:text-neutral-300 uppercase tracking-tight">{{ sport.name }}</span>
                  <span class="text-[10px] font-black text-indigo-400">{{ sport.count }} Turnamen</span>
                </div>
                <div class="h-1.5 w-full bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-indigo-600 to-indigo-400 rounded-full transition-all duration-1000"
                    :style="{ width: stats.overview?.tournaments?.total ? `${(sport.count / stats.overview.tournaments.total) * 100}%` : '0%' }"
                  ></div>
                </div>
              </div>

              <div v-if="!stats.distribution?.length" class="py-20 text-center opacity-20">
                <p class="text-[10px] uppercase tracking-widest font-bold">Data tidak tersedia</p>
              </div>
            </div>
          </UCard>
        </div>
      </div>

      <!-- Skeleton Loaders -->
      <div v-else class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <USkeleton v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-gray-100 dark:bg-white/5" />
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <USkeleton class="lg:col-span-2 h-96 rounded-2xl bg-gray-100 dark:bg-white/5" />
          <USkeleton class="h-96 rounded-2xl bg-gray-100 dark:bg-white/5" />
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>
