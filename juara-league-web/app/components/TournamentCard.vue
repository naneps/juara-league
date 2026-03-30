<script setup lang="ts">
import type { Tournament } from '~/types/tournament'

interface Props {
  tournament: Tournament
}

const props = defineProps<Props>()

type BadgeColor = 'primary' | 'neutral' | 'success' | 'info' | 'warning' | 'error' | 'secondary'

const statusConfig: Record<string, { label: string, color: BadgeColor, icon: string }> = {
  open: { label: 'Pendaftaran Buka', color: 'primary', icon: 'i-lucide-users' },
  ongoing: { label: 'Sedang Berlangsung', color: 'warning', icon: 'i-lucide-play-circle' },
  finished: { label: 'Selesai', color: 'neutral', icon: 'i-lucide-check-circle' },
  draft: { label: 'Draft', color: 'neutral', icon: 'i-lucide-file-text' }
}

const status = computed(() => statusConfig[props.tournament.status] || statusConfig.draft)
</script>

<template>
  <div class="group relative bg-neutral-900/60 backdrop-blur-3xl rounded-[2.5rem] border border-white/5 overflow-hidden transition-all duration-500 hover:border-primary-500/40 hover:shadow-[0_0_50px_-12px_rgba(234,179,8,0.15)] flex flex-col h-full">
    <!-- Thumbnail Image -->
    <div class="relative h-56 overflow-hidden">
      <img 
        :src="tournament.image" 
        :alt="tournament.title"
        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
      />
      <!-- Darker Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/20 to-transparent"></div>
      
      <!-- Badges -->
      <div class="absolute top-6 left-6 flex flex-col gap-2">
        <UBadge 
          :color="status.color" 
          variant="solid" 
          class="font-black px-4 py-1.5 rounded-xl backdrop-blur-md ring-1 ring-white/20 uppercase text-[10px] tracking-widest flex items-center gap-2 shadow-2xl"
        >
          <div class="size-1.5 rounded-full animate-pulse" :class="status.color === 'primary' ? 'bg-white' : 'bg-current'"></div>
          {{ status.label }}
        </UBadge>
      </div>

      <div class="absolute top-6 right-6">
        <UBadge 
          color="neutral" 
          variant="outline" 
          class="font-black px-4 py-1.5 rounded-xl backdrop-blur-md border-white/20 text-[10px] uppercase tracking-widest shadow-2xl text-white bg-neutral-950/40"
        >
          {{ tournament.mode }}
        </UBadge>
      </div>
    </div>

    <!-- Content -->
    <div class="p-8 flex flex-col flex-grow relative">
      <div class="flex items-center gap-3 mb-4">
        <div class="px-2 py-1 bg-primary-500/10 rounded-md ring-1 ring-primary-500/20">
          <span class="text-[10px] font-black text-primary-400 uppercase tracking-widest">{{ tournament.category }}</span>
        </div>
        <span class="text-neutral-700">/</span>
        <span class="text-[11px] font-bold text-neutral-500 uppercase tracking-wider">{{ tournament.start_date }}</span>
      </div>

      <h3 class="text-2xl font-black text-white leading-tight mb-6 group-hover:text-primary-500 transition-colors line-clamp-2 tracking-tight">
        {{ tournament.title }}
      </h3>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 gap-6 mt-auto">
        <div class="flex flex-col gap-2">
          <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Prize Pool</span>
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 bg-yellow-500/10 rounded-lg">
              <UIcon name="i-lucide-trophy" class="text-yellow-500 size-5" />
            </div>
            <span class="text-lg font-black text-white tracking-tight">{{ tournament.prize_pool }}</span>
          </div>
        </div>
        <div class="flex flex-col gap-2">
          <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Slots</span>
          <div class="flex items-center gap-2.5">
            <div class="p-1.5 bg-primary-500/10 rounded-lg">
              <UIcon name="i-lucide-users-2" class="text-primary-400 size-5" />
            </div>
            <span class="text-lg font-black text-white tracking-tight">{{ tournament.current_participants }}/{{ tournament.max_participants }}</span>
          </div>
        </div>
      </div>

      <div class="h-px bg-gradient-to-r from-white/10 via-white/5 to-transparent my-8"></div>

      <!-- Footer Info -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="relative">
            <UAvatar 
              :src="tournament.organizer.avatar" 
              :alt="tournament.organizer.name" 
              size="sm"
              class="ring-2 ring-white/5 rounded-xl"
            />
            <div v-if="tournament.organizer.is_verified" class="absolute -bottom-1 -right-1 bg-blue-500 rounded-full p-0.5 ring-2 ring-neutral-900">
              <UIcon name="i-lucide-check" class="text-white size-2" />
            </div>
          </div>
          <div class="flex flex-col">
            <span class="text-sm font-bold text-neutral-200">{{ tournament.organizer.name }}</span>
            <span class="text-[10px] font-medium text-neutral-600 uppercase tracking-wide">Organizer</span>
          </div>
        </div>

        <div class="text-right">
          <span class="block text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Entry Fee</span>
          <span class="text-base font-black text-primary-400">{{ tournament.entry_fee }}</span>
        </div>
      </div>
    </div>
    
    <!-- Action Overlay -->
    <NuxtLink :to="`/tournaments/${tournament.slug}`" class="absolute inset-0 z-10" />
  </div>
</template>
