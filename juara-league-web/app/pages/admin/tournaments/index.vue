<script setup lang="ts">
import { useAdminStore } from '~/stores/admin.store'
import type { Tournament } from '~/types/tournament'

definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const adminStore = useAdminStore()
const { pendingTournaments, isLoading, error } = storeToRefs(adminStore)
const toast = useToast()

const columns: any[] = [{
  accessorKey: 'title',
  header: 'Turnamen'
}, {
  accessorKey: 'user',
  header: 'Penyelenggara'
}, {
  accessorKey: 'sport',
  header: 'Olahraga'
}, {
  accessorKey: 'status',
  header: 'Status'
}, {
  id: 'actions',
  header: 'Aksi'
}]

// Fetch data logic
const { refresh, status } = await useAsyncData('admin-pending-tournaments-list', () => 
  adminStore.fetchPendingTournaments().then(() => true),
  { lazy: true }
)

// --- MODAL REJECT ---
const showRejectModal = ref(false)
const selectedTournamentId = ref<string | null>(null)
const rejectLoading = ref(false)
const rejectionForm = reactive({
  reason: '',
  note: ''
})

const rejectionReasons = [
  'Spam atau iklan tidak relevan',
  'Informasi turnamen tidak lengkap',
  'Melanggar aturan platform',
  'Cabang olahraga tidak sesuai',
  'Lainnya'
]

const openRejectModal = (id: string | number) => {
  selectedTournamentId.value = String(id)
  rejectionForm.reason = rejectionReasons[0]
  rejectionForm.note = ''
  showRejectModal.value = true
}

const handleReject = async () => {
  if (!selectedTournamentId.value) return
  
  rejectLoading.value = true
  try {
    const finalReason = rejectionForm.reason === 'Lainnya' 
      ? rejectionForm.note 
      : `${rejectionForm.reason}: ${rejectionForm.note}`

    await adminStore.rejectTournament(selectedTournamentId.value, finalReason)
    
    toast.add({
      title: 'Ditolak',
      description: 'Turnamen telah ditolak.',
      color: 'warning'
    })
    
    showRejectModal.value = false
  } catch (err: any) {
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Gagal menolak turnamen.',
      color: 'danger'
    })
  } finally {
    rejectLoading.value = false
  }
}

// --- SLIDEOVER DETAIL ---
const showDetailSlideover = ref(false)
const detailTournament = ref<Tournament | null>(null)

const openDetail = (tournament: Tournament) => {
  detailTournament.value = tournament
  showDetailSlideover.value = true
}

const handleApprove = async (id: string | number) => {
  try {
    await adminStore.approveTournament(id)
    toast.add({
      title: 'Disetujui',
      description: 'Turnamen berhasil disetujui.',
      color: 'success'
    })
    showDetailSlideover.value = false
  } catch (err: any) {
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Gagal menyetujui turnamen.',
      color: 'danger'
    })
  }
}

const formatDate = (date: any) => {
  if (!date) return '-'
  try {
    return new Date(date).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    })
  } catch (e) {
    return '-'
  }
}
</script>

<template>
  <UDashboardPanel id="tournaments_moderation_container">
    <template #header>
      <UDashboardNavbar title="Moderasi Turnamen" description="Tinjau dan setujui turnamen yang masuk ke platform.">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <UButton
            icon="i-lucide-refresh-cw"
            variant="ghost"
            color="neutral"
            :loading="isLoading || status === 'pending'"
            @click="refresh"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <!-- Error State -->
      <div v-if="error" class="mb-6 p-4 rounded-2xl bg-error-500/10 border border-error-500/20 flex items-center gap-3">
        <UIcon name="i-lucide-alert-circle" class="size-5 text-error-500 shrink-0" />
        <p class="text-sm font-bold text-error-500">{{ error }}</p>
        <UButton
          icon="i-lucide-refresh-cw"
          variant="ghost"
          color="error"
          size="xs"
          class="ml-auto"
          @click="refresh"
        >
          Coba Lagi
        </UButton>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5">
          <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Menunggu Review</p>
          <p class="text-2xl font-black dark:text-white">{{ pendingTournaments?.length || 0 }}</p>
        </div>
      </div>

      <UCard
        class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5 overflow-hidden"
        :ui="{ body: 'p-0 sm:p-0' }"
      >
        <UTable
          :data="pendingTournaments || []"
          :columns="columns"
          :loading="isLoading || status === 'pending'"
          :row-key="row => row.id"
          :ui="{ 
            thead: 'bg-neutral-50 dark:bg-white/[0.02]',
            th: { base: 'text-[10px] uppercase tracking-widest font-black text-neutral-500 py-4 px-4 border-b border-neutral-200 dark:border-white/5' },
            td: { base: 'py-4 px-4' }
          }"
        >
          <template #title-cell="{ row }">
            <div class="flex flex-col py-2">
              <span class="font-bold dark:text-slate-100 hover:text-indigo-400 cursor-pointer transition-colors" @click="openDetail(row.original as any)">
                {{ (row.original as any).title }}
              </span>
              <span class="text-[10px] text-slate-500 uppercase font-medium tracking-tight">
                {{ (row.original as any).category }} • {{ (row.original as any).mode }}
              </span>
            </div>
          </template>

          <template #user-cell="{ row }">
            <div class="flex items-center gap-2">
              <UAvatar :src="(row.original as any).user?.avatar" :alt="(row.original as any).user?.name" size="2xs" />
              <div class="flex flex-col">
                <span class="text-xs font-semibold dark:text-slate-200">{{ (row.original as any).user?.name }}</span>
                <span class="text-[10px] text-slate-500">{{ (row.original as any).user?.email }}</span>
              </div>
            </div>
          </template>

          <template #sport-cell="{ row }">
            <div class="flex items-center gap-2">
              <img v-if="(row.original as any).sport?.icon_url" :src="(row.original as any).sport.icon_url" class="size-4" />
              <span class="text-xs font-bold dark:text-neutral-400 uppercase tracking-tighter">{{ (row.original as any).sport?.name }}</span>
            </div>
          </template>

          <template #status-cell="{ row }">
            <div class="relative flex items-center gap-2">
              <div class="size-2 rounded-full bg-amber-500 animate-pulse" />
              <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Pending Review</span>
            </div>
          </template>

          <template #actions-cell="{ row }">
            <div class="flex items-center gap-1">
              <UButton
                icon="i-lucide-eye"
                color="neutral"
                variant="ghost"
                size="xs"
                @click="openDetail(row.original as any)"
              />
              <div class="w-px h-3 bg-white/10 mx-1" />
              <UButton
                icon="i-lucide-check"
                color="indigo"
                variant="soft"
                size="xs"
                class="font-bold"
                @click="handleApprove((row.original as any).id)"
              >
                Approve
              </UButton>
              <UButton
                icon="i-lucide-x"
                color="neutral"
                variant="ghost"
                size="xs"
                @click="openRejectModal((row.original as any).id)"
              />
            </div>
          </template>

          <template #empty-state>
            <div class="flex flex-col items-center justify-center py-24 text-neutral-600">
              <div class="size-16 rounded-3xl bg-neutral-50 dark:bg-white/5 flex items-center justify-center mb-6">
                <UIcon name="i-lucide-coffee" class="size-8 opacity-20" />
              </div>
              <p class="font-bold text-sm tracking-tight">Kopi dulu, Gan!</p>
              <p class="text-xs opacity-60">Tidak ada turnamen yang perlu dimoderasi saat ini.</p>
            </div>
          </template>
        </UTable>
      </UCard>
    </template>
  </UDashboardPanel>

  <!-- SLIDEOVER: TOURNAMENT DETAIL -->
  <USlideover v-model:open="showDetailSlideover" title="Detail Turnamen" class="z-[60]">
    <template #content>
      <div v-if="detailTournament" class="flex flex-col h-full dark:bg-neutral-900 border-l border-neutral-200 dark:border-white/5 space-y-8 p-6 overflow-y-auto font-sans">
        <div class="relative aspect-video rounded-2xl overflow-hidden border border-neutral-200 dark:border-white/5">
          <img :src="detailTournament.banner_url || '/placeholder-banner.jpg'" class="object-cover w-full h-full" />
          <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
            <UBadge color="indigo" variant="solid" size="sm" class="font-black uppercase tracking-widest">
              {{ detailTournament.sport?.name }}
            </UBadge>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] mb-1">Informasi Dasar</p>
            <h2 class="text-2xl font-black dark:text-white leading-tight uppercase italic tracking-tighter">{{ detailTournament.title }}</h2>
          </div>
          <p class="text-sm dark:text-neutral-400 leading-relaxed">{{ detailTournament.description }}</p>
          <div class="grid grid-cols-2 gap-3">
            <div class="p-3 rounded-xl bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5">
              <p class="text-[9px] font-bold text-neutral-500 uppercase mb-1">Mode</p>
              <p class="text-xs font-bold dark:text-white">{{ detailTournament.mode === 'open' ? 'Terbuka' : 'Invitasi' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5">
              <p class="text-[9px] font-bold text-neutral-500 uppercase mb-1">Tipe Peserta</p>
              <p class="text-xs font-bold dark:text-white uppercase">{{ detailTournament.participant_type }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em]">Logistik</p>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs px-1">
              <span class="text-neutral-500">Maks. Peserta</span>
              <span class="font-bold dark:text-white">{{ detailTournament.max_participants }} Entitas</span>
            </div>
            <div class="flex items-center justify-between text-xs px-1">
              <span class="text-neutral-500">Prize Pool</span>
              <span class="font-bold text-emerald-400">Rp {{ Number(detailTournament.prize_pool || 0).toLocaleString('id-ID') }}</span>
            </div>
            <div class="flex items-center justify-between text-xs px-1">
              <span class="text-neutral-500">Entry Fee</span>
              <span class="font-bold text-amber-400">{{ Number(detailTournament.entry_fee || 0) > 0 ? `Rp ${Number(detailTournament.entry_fee).toLocaleString('id-ID')}` : 'GRATIS' }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em]">Waktu & Lokasi</p>
          <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5 space-y-3">
            <div class="flex items-start gap-3">
              <UIcon name="i-lucide-calendar" class="size-4 text-neutral-500 mt-0.5" />
              <div class="flex flex-col">
                <span class="text-[10px] font-bold text-neutral-500 uppercase">Pendaftaran</span>
                <span class="text-xs dark:text-neutral-300">{{ formatDate(detailTournament.registration_start_at) }} - {{ formatDate(detailTournament.registration_end_at) }}</span>
              </div>
            </div>
            <div class="flex items-start gap-3 border-t border-neutral-200 dark:border-white/5 pt-3">
              <UIcon name="i-lucide-map-pin" class="size-4 text-neutral-500 mt-0.5" />
              <div class="flex flex-col">
                <span class="text-[10px] font-bold text-neutral-500 uppercase">Lokasi / Venue</span>
                <span class="text-xs dark:text-neutral-300 font-bold tracking-tight">{{ detailTournament.venue }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-auto pt-6 flex gap-3">
          <UButton
            color="indigo"
            size="xl"
            block
            class="flex-1 font-black uppercase tracking-widest rounded-xl h-12"
            @click="handleApprove(detailTournament.id)"
          >
            Approve Now
          </UButton>
          <UButton
            color="neutral"
            variant="outline"
            size="xl"
            icon="i-lucide-x"
            class="rounded-xl h-12"
            @click="openRejectModal(detailTournament.id)"
          />
        </div>
      </div>
    </template>
  </USlideover>

  <!-- MODAL: REJECT REASON -->
  <UModal v-model:open="showRejectModal" title="Tolak Turnamen" description="Berikan alasan penolakan agar penyelenggara bisa memperbaiki data mereka." class="z-[70]">
    <template #content>
      <div class="p-6 space-y-6">
        <UFormField label="Alasan Utama" required>
          <USelectMenu
            v-model="rejectionForm.reason"
            :items="rejectionReasons"
            size="lg"
            class="w-full"
          />
        </UFormField>

        <UFormField label="Catatan Tambahan (Opsional)">
          <UTextarea
            v-model="rejectionForm.note"
            placeholder="Berikan instruksi spesifik apa yang harus diperbaiki..."
            :rows="4"
            size="lg"
          />
        </UFormField>

        <div class="flex justify-end gap-3 pt-2">
          <UButton variant="ghost" color="neutral" @click="showRejectModal = false">Batal</UButton>
          <UButton
            color="error"
            :loading="rejectLoading"
            class="font-bold px-6"
            @click="handleReject"
          >
            Konfirmasi Tolak
          </UButton>
        </div>
      </div>
    </template>
  </UModal>
</template>
