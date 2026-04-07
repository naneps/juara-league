<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useTournamentStore } from '~/stores/tournamentStore'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()
const toast = useToast()

const { data: tournament, refresh } = await useAsyncData(`tournament-${slug}`, () => tournamentStore.getBySlug(slug))

const isPublishing = ref(false)

const handlePublish = async () => {
  if (!confirm('Apakah Anda yakin ingin mempublikasikan turnamen ini?')) return
  
  isPublishing.value = true
  try {
    await tournamentStore.publishTournament(slug)
    toast.add({ title: 'Berhasil!', description: 'Turnamen sekarang terbuka untuk pendaftaran.', color: 'success' })
    await refresh()
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal mempublikasikan turnamen', color: 'error' })
  } finally {
    isPublishing.value = false
  }
}

const statusMap: Record<string, { label: string, color: string }> = {
  open: { label: 'Registration Open', color: 'success' },
  ongoing: { label: 'Ongoing', color: 'info' },
  finished: { label: 'Finished', color: 'neutral' },
  draft: { label: 'Draft', color: 'warning' }
}

const getStatus = (status: string) => {
  return (statusMap[status] ?? statusMap['draft']) as { label: string, color: string }
}
const getStatusColor = (status: string) => getStatus(status).color
const getStatusLabel = (status: string) => getStatus(status).label
</script>

<template>
  <div v-if="tournament" class="space-y-12">
    <!-- Header Card -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
      <div class="flex items-center gap-6">
        <div class="size-20 bg-neutral-100 dark:bg-neutral-900 rounded-[2rem] border border-neutral-200 dark:border-white/5 flex items-center justify-center p-3 shadow-sm">
          <img v-if="tournament.sport?.icon_url" :src="tournament.sport.icon_url" class="size-full object-contain" />
          <UIcon v-else name="i-lucide-trophy" class="size-10 text-neutral-400" />
        </div>
        <div>
          <h1 class="text-3xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2">{{ tournament.title }}</h1>
          <div class="flex items-center gap-3">
            <UBadge :color="(getStatusColor(tournament.status) as any)" variant="soft" class="rounded-lg text-[10px] font-black uppercase tracking-widest px-4 py-1.5">
              {{ getStatusLabel(tournament.status) }}
            </UBadge>
            <span class="text-xs font-bold text-neutral-500 uppercase tracking-widest">{{ tournament.mode }} • {{ tournament.bracket_type.replace('_', ' ') }}</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <UButton 
          v-if="tournament.status === 'draft'"
          color="primary" 
          icon="i-lucide-send" 
          label="Publish Turnamen" 
          class="rounded-2xl px-8 py-3 font-black uppercase tracking-widest text-xs h-12 shadow-xl shadow-primary-500/20"
          :loading="isPublishing"
          @click="handlePublish"
        />
        <UButton 
          color="neutral" 
          variant="ghost" 
          icon="i-lucide-edit" 
          label="Edit Info" 
          class="rounded-xl font-bold uppercase tracking-widest text-[10px]"
          :to="`/dashboard/tournaments/create?edit=${tournament.id}`"
        />
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="stat in [
          { label: 'Pendaftar', value: tournament.participants_count || 0, sub: `Max: ${tournament.max_participants}`, icon: 'i-lucide-users', color: 'bg-primary-500' },
          { label: 'Total Hadiah', value: tournament.prize_pool || 'Rp 0', sub: 'Pool Prize', icon: 'i-lucide-trophy', color: 'bg-amber-500' },
          { label: 'Biaya Daftar', value: tournament.entry_fee || 'Gratis', sub: 'Per Tim/Orang', icon: 'i-lucide-ticket', color: 'bg-blue-500' },
          { label: 'Mulai', value: new Date(tournament.start_at || '').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }), sub: 'Tanggal Main', icon: 'i-lucide-calendar', color: 'bg-emerald-500' },
        ]" 
        :key="stat.label"
        class="bg-white dark:bg-neutral-900/60 p-6 rounded-[2rem] border border-neutral-200 dark:border-white/5 hover:border-primary-500/30 transition-all duration-300 shadow-sm dark:shadow-none"
      >
        <div class="flex items-center gap-4">
          <div :class="['size-12 rounded-2xl flex items-center justify-center text-white p-3', stat.color]">
            <UIcon :name="stat.icon" class="size-full" />
          </div>
          <div>
            <p class="text-[10px] font-black text-neutral-500 uppercase tracking-widest mb-1">{{ stat.label }}</p>
            <h4 class="text-xl font-black text-neutral-900 dark:text-white leading-none mb-1">{{ stat.value }}</h4>
            <span class="text-[10px] text-neutral-400 font-bold uppercase tracking-widest">{{ stat.sub }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="bg-white dark:bg-neutral-900/40 p-8 rounded-[3rem] border border-neutral-200 dark:border-white/5 shadow-sm dark:shadow-none">
      <h3 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-6 flex items-center gap-3">
        <UIcon name="i-lucide-file-text" class="size-6 text-primary-500" />
        Deskripsi & Aturan
      </h3>
      <div v-if="tournament.description" class="prose prose-sm dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400 font-medium">
        {{ tournament.description }}
      </div>
      <div v-else class="text-neutral-500 font-bold uppercase tracking-widest text-[10px] py-12 text-center">
        Belum ada deskripsi yang ditambahkan.
      </div>
    </div>
  </div>
</template>
