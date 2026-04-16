<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import { getApprovalStatus, getTournamentStatus } from '~/utils/tournamentStatus'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()

const { data: tournament, refresh } = await useAsyncData(`tournament-${slug}`, () => tournamentStore.getBySlug(slug))

const isPublishing = ref(false)
const isPublishModalOpen = ref(false)

const handlePublish = async () => {
  if (tournament.value?.approval_status === 'pending_review') {
    toast.add({ title: 'Menunggu Review', description: 'Turnamen Anda sedang menunggu peninjauan dari tim platform.', color: 'warning' })
    return
  }
  
  if (tournament.value?.approval_status === 'rejected') {
    toast.add({ title: 'Turnamen Ditolak', description: 'Maaf, turnamen ini tidak disetujui untuk dipublikasikan.', color: 'error' })
    return
  }

  isPublishModalOpen.value = true
}

const confirmPublish = async () => {
  isPublishing.value = true
  try {
    await tournamentStore.publishTournament(slug)
    toast.add({ title: 'Berhasil dipublish!', description: 'Turnamen sekarang terbuka untuk pendaftaran.', color: 'success' })
    await refresh()
    isPublishModalOpen.value = false
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal mempublikasikan turnamen', color: 'error' })
  } finally {
    isPublishing.value = false
  }
}

const tournamentStatus = computed(() => getTournamentStatus(tournament.value?.status))
const approvalStatus = computed(() => getApprovalStatus(tournament.value?.approval_status))

const formatDate = (d: string | null | undefined) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
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
        title="Sedang Ditinjau"
        description="Turnamen ini memerlukan peninjauan manual oleh tim Juara League. Anda bisa melanjutkan persiapan babak (stage) dan detail lainnya, namun fitur Publish akan terbuka setelah disetujui."
        class="border-warning-500/20"
      />
      <UAlert
        v-else-if="tournament.approval_status === 'rejected'"
        icon="i-lucide-alert-octagon"
        color="error"
        variant="subtle"
        title="Turnamen Ditolak"
        description="Mohon maaf, turnamen Anda tidak disetujui oleh platform. Silakan hubungi dukungan atau periksa kembali deskripsi turnamen Anda."
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
          Publish
        </UButton>
        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-pencil"
          size="sm"
          :to="`/dashboard/tournaments/create?edit=${tournament.slug}`"
        >
          Edit
        </UButton>
      </div>
    </div>

    <!-- ── Stat Cards ── -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="stat in [
          { label: 'Peserta',      value: String(tournament.participants_count ?? 0),                                                           sub: `dari ${tournament.max_participants}`, icon: 'i-lucide-users' },
          { label: 'Total Hadiah', value: tournament.prize_pool ? formatCurrency(tournament.prize_pool) : '—',                                  sub: 'Prize pool',                         icon: 'i-lucide-trophy' },
          { label: 'Entry Fee',    value: !tournament.entry_fee || tournament.entry_fee == 0 ? 'Gratis' : formatCurrency(tournament.entry_fee), sub: 'Per peserta',                        icon: 'i-lucide-ticket' },
          { label: 'Reg. Tutup',   value: formatDate(tournament.registration_end_at),                                                            sub: 'Batas akhir pendaftaran',             icon: 'i-lucide-timer' },
          { label: 'Kickoff',      value: formatDate(tournament.start_at),                                                                       sub: 'Hari pertama pertandingan',           icon: 'i-lucide-calendar' },
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
            Deskripsi & Aturan
          </h3>
          <UButton
            size="xs"
            color="neutral"
            variant="ghost"
            icon="i-lucide-pencil"
            :to="`/dashboard/tournaments/create?edit=${tournament.slug}`"
          >
            Edit
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
            <p class="text-sm text-neutral-400 dark:text-neutral-500">Belum ada deskripsi.</p>
          </div>
        </div>
      </div>

      <!-- Detail Hadiah -->
      <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 dark:border-neutral-800">
          <h3 class="text-sm font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
            <UIcon name="i-lucide-trophy" class="size-4 text-amber-500" />
            Breakdown Hadiah
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
            <p class="text-sm text-neutral-400 dark:text-neutral-500">Belum ada rincian hadiah.</p>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Inline managers ── -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <TournamentsTournamentParticipantManager
        :tournament-slug="tournament.slug"
        :initial-participants="tournament.participants || []"
      />
      <TournamentsTournamentStageManager
        :tournament-slug="tournament.slug"
        :initial-stages="tournament.stages || []"
      />
    </div>

  </div>

  <!-- Not found -->
  <div v-else class="flex items-center justify-center py-32">
    <div class="text-center">
      <UIcon name="i-lucide-search-x" class="size-12 text-neutral-300 dark:text-neutral-700 mx-auto mb-4" />
      <p class="text-neutral-500 dark:text-neutral-400">Turnamen tidak ditemukan.</p>
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
          @click="confirmPublish"
        >
          Ya, Publish
        </UButton>
      </div>
    </template>
  </UModal>
</template>
