<script setup lang="ts">
import type { Period, Range, Stat } from '~/types'

const { t, locale } = useI18n()

const props = defineProps<{
  stats?: {
    total_tournaments: number;
    ongoing_tournaments: number;
    pending_participants: number;
    matches_today: number;
  }
}>()

const displayStats = computed(() => [
  {
    title: t('home.stats.total_tournaments'),
    icon: 'i-lucide-trophy',
    value: props.stats?.total_tournaments ?? 0,
    color: 'primary'
  },
  {
    title: t('home.stats.ongoing_stages'),
    icon: 'i-lucide-play-circle',
    value: props.stats?.ongoing_tournaments ?? 0,
    color: 'success'
  },
  {
    title: t('home.stats.pending_participants'),
    icon: 'i-lucide-user-plus',
    value: props.stats?.pending_participants ?? 0,
    color: 'warning'
  },
  {
    title: t('home.stats.matches_today'),
    icon: 'i-lucide-swords',
    value: props.stats?.matches_today ?? 0,
    color: 'info'
  }
])
</script>

<template>
  <UPageGrid class="lg:grid-cols-4 gap-4 sm:gap-6">
    <UPageCard
      v-for="(stat, index) in displayStats"
      :key="index"
      :icon="stat.icon"
      :title="stat.title"
      variant="subtle"
      :ui="{
        container: 'gap-y-1.5',
        wrapper: 'items-start',
        leading: `p-2.5 rounded-2xl bg-${stat.color}-500/10 ring-1 ring-inset ring-${stat.color}-500/25 flex-col`,
        title: 'font-bold text-neutral-500 text-[10px] uppercase tracking-widest'
      }"
      class="rounded-3xl border border-neutral-200 dark:border-white/5 hover:border-primary-500/30 transition-all group overflow-hidden"
    >
      <div class="relative">
        <div class="flex items-center gap-2 relative z-10">
          <span class="text-3xl font-black text-neutral-900 dark:text-white italic tracking-tighter">
            {{ stat.value }}
          </span>
        </div>
        <!-- Subtle Glow -->
        <div :class="`absolute -bottom-10 -right-10 size-20 bg-${stat.color}-500/10 blur-2xl rounded-full group-hover:scale-150 transition-transform duration-700`"></div>
      </div>
    </UPageCard>
  </UPageGrid>
</template>
