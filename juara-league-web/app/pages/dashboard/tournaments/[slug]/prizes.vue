<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()
const { t } = useI18n()

const { data: tournament, refresh } = await useAsyncData(`tournament-prizes-${slug}`, () => tournamentStore.getBySlug(slug))

const isSaving = ref(false)
const prizesState = ref<any[]>([])

watch(tournament, (newVal) => {
  if (newVal) {
    prizesState.value = JSON.parse(JSON.stringify(newVal.prizes || []))
  }
}, { immediate: true })

const savePrizes = async () => {
  if (!tournament.value) return
  isSaving.value = true
  try {
    const total = prizesState.value.reduce((sum, p) => sum + (Number(p.prize_amount) || 0), 0)
    await tournamentStore.updateTournament(tournament.value.slug, {
      prizes: prizesState.value,
      prize_pool: total > 0 ? total : tournament.value.prize_pool
    } as any)
    toast.add({ title: t('common.success'), description: 'Pembagian hadiah berhasil diperbarui', color: 'success' })
    await refresh()
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.data?.message || 'Gagal memperbarui hadiah', color: 'error' })
  } finally {
    isSaving.value = false
  }
}

const formatCurrency = (val: number | string | undefined | null) => {
  if (!val) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(Number(val))
}

const totalPrize = computed(() => {
  return prizesState.value.reduce((sum, p) => sum + (Number(p.prize_amount) || 0), 0)
})
</script>

<template>
  <div v-if="tournament" class="space-y-6">
    <!-- Header Card -->
    <div class="bg-neutral-900 border border-white/5 rounded-[2.5rem] p-8 relative overflow-hidden group">
      <div class="absolute -top-10 -right-10 size-40 bg-amber-500/10 blur-[80px] group-hover:bg-amber-500/20 transition-all"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
          <div class="size-14 rounded-2xl bg-amber-500/10 flex items-center justify-center border border-amber-500/20 shadow-inner">
            <UIcon name="i-lucide-award" class="size-8 text-amber-500" />
          </div>
          <div>
            <h3 class="text-xl font-black text-white uppercase tracking-[0.1em]">{{ $t('dashboard.prizes') }}</h3>
            <p class="text-xs text-neutral-500 font-bold uppercase tracking-widest mt-1">Kelola pembagian total hadiah untuk para pemenang.</p>
          </div>
        </div>

        <div class="bg-white/5 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/10 text-right">
          <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Total Prize Pool</p>
          <p class="text-2xl font-black text-white italic tracking-tighter">{{ formatCurrency(totalPrize || tournament.prize_pool) }}</p>
        </div>
      </div>
    </div>

    <!-- Manager Section -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 lg:p-10">
      <div class="max-w-4xl mx-auto space-y-8">
        <div class="space-y-2">
          <h4 class="text-lg font-black text-neutral-900 dark:text-white uppercase tracking-tight">Struktur Pembagian Hadiah</h4>
          <p class="text-sm text-neutral-500 font-medium">Tentukan tier juara (misal: Juara 1, Juara 2, Runner Up) dan jumlah hadiah masing-masing.</p>
        </div>

        <TournamentsTournamentPrizeManager v-model="prizesState" />

        <div class="pt-6 border-t border-neutral-100 dark:border-neutral-800 flex justify-end">
          <UButton 
            color="primary" 
            size="xl"
            class="font-black uppercase tracking-wider shadow-xl shadow-primary-500/20 px-8"
            :loading="isSaving"
            icon="i-lucide-save"
            @click="savePrizes"
          >
            {{ $t('common.save') }}
          </UButton>
        </div>
      </div>
    </div>
  </div>
</template>
