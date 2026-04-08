<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'

import { getTournamentStatus } from '~/utils/tournamentStatus'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()

const { data: tournament, refresh } = await useAsyncData(`tournament-${slug}`, () => tournamentStore.getBySlug(slug))

const isPublishing = ref(false)

const handlePublish = async () => {
  if (!confirm('Publish turnamen ini? Peserta bisa mulai mendaftar setelah ini.')) return

  isPublishing.value = true
  try {
    await tournamentStore.publishTournament(slug)
    toast.add({ title: 'Berhasil dipublish!', description: 'Turnamen sekarang terbuka untuk pendaftaran.', color: 'success' })
    await refresh()
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal mempublikasikan turnamen', color: 'error' })
  } finally {
    isPublishing.value = false
  }
}

const tournamentStatus = computed(() => getTournamentStatus(tournament.value?.status))

const formatDate = (d: string | null | undefined) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div v-if="tournament" class="space-y-6">

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
          { label: 'Mulai',        value: formatDate(tournament.start_at),                                                                       sub: 'Tanggal pertandingan',                icon: 'i-lucide-calendar' },
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

    <!-- ── Deskripsi ── -->
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
</template>
