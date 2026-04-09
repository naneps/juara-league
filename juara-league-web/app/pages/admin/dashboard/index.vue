<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const { $api } = useNuxtApp()

const { data: stats } = await useAsyncData('admin-stats', async () => {
    // Kita bisa buat endpoint stats nanti, untuk sekarang kita hitung manual dari list atau mock
    const t = await $api<{ data: any[] }>('/admin/tournaments')
    return {
        pending: t.data.filter(i => i.approval_status === 'pending_review').length,
        total: t.data.length
    }
})
</script>

<template>
  <UDashboardPage>
    <UDashboardHeader title="Admin Overview" description="Selamat datang di Panel Administrasi Juara League.">
    </UDashboardHeader>

    <UDashboardPanelContent>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <UCard class="bg-indigo-600 border-none shadow-xl shadow-indigo-500/20">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-white/20 rounded-xl">
              <UIcon name="i-lucide-clipboard-list" class="w-6 h-6 text-white" />
            </div>
            <div>
              <p class="text-indigo-100 text-sm font-medium">Pending Review</p>
              <h3 class="text-3xl font-bold text-white">{{ stats?.pending || 0 }}</h3>
            </div>
          </div>
        </UCard>

        <UCard class="bg-slate-900 border-slate-800">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-slate-800 rounded-xl">
              <UIcon name="i-lucide-trophy" class="w-6 h-6 text-slate-300" />
            </div>
            <div>
              <p class="text-slate-400 text-sm font-medium">Total Turnamen</p>
              <h3 class="text-3xl font-bold text-slate-100">{{ stats?.total || 0 }}</h3>
            </div>
          </div>
        </UCard>

        <UCard class="bg-slate-900 border-slate-800">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-slate-800 rounded-xl">
              <UIcon name="i-lucide-users" class="w-6 h-6 text-slate-300" />
            </div>
            <div>
              <p class="text-slate-400 text-sm font-medium">Platform Status</p>
              <div class="flex items-center gap-2 mt-1">
                 <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                 <span class="text-emerald-500 font-bold uppercase tracking-widest text-xs">Healthy</span>
              </div>
            </div>
          </div>
        </UCard>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <UCard class="bg-slate-900/50 border-slate-800 h-64 flex items-center justify-center">
             <div class="text-center opacity-40">
                <UIcon name="i-lucide-bar-chart-3" class="w-16 h-16 mx-auto mb-4" />
                <p>Statistik Pertumbuhan (Coming Soon)</p>
             </div>
          </UCard>
          <UCard class="bg-slate-900/50 border-slate-800 h-64 flex items-center justify-center">
             <div class="text-center opacity-40">
                <UIcon name="i-lucide-activity" class="w-16 h-16 mx-auto mb-4" />
                <p>Log Aktivitas (Coming Soon)</p>
             </div>
          </UCard>
      </div>
    </UDashboardPanelContent>
  </UDashboardPage>
</template>
