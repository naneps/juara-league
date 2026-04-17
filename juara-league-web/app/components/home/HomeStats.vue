<script setup lang="ts">
import type { Period, Range, Stat } from '~/types'

const { t, locale } = useI18n()

const props = defineProps<{
  period: Period
  range: Range
}>()

function formatCurrency(value: number): string {
  return value.toLocaleString(locale.value === 'id' ? 'id-ID' : 'en-US', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  })
}

const baseStats = computed(() => [{
  title: t('home.stats.total_teams'),
  icon: 'i-lucide-users-round',
  minValue: 32,
  maxValue: 128,
  minVariation: -5,
  maxVariation: 15
}, {
  title: t('home.stats.registrations'),
  icon: 'i-lucide-user-plus',
  minValue: 4,
  maxValue: 20,
  minVariation: -10,
  maxVariation: 25
}, {
  title: t('home.stats.prize_pool'),
  icon: 'i-lucide-trophy',
  minValue: 5000000,
  maxValue: 25000000,
  minVariation: 5,
  maxVariation: 10,
  formatter: formatCurrency
}, {
  title: t('home.stats.matches'),
  icon: 'i-lucide-swords',
  minValue: 10,
  maxValue: 50,
  minVariation: 0,
  maxVariation: 30
}])

const { data: stats } = await useAsyncData<Stat[]>('stats', async () => {
  return baseStats.value.map((stat) => {
    const value = randomInt(stat.minValue, stat.maxValue)
    const variation = randomInt(stat.minVariation, stat.maxVariation)

    return {
      title: stat.title,
      icon: stat.icon,
      value: stat.formatter ? stat.formatter(value) : value,
      variation
    }
  })
}, {
  watch: [() => props.period, () => props.range],
  default: () => []
})
</script>

<template>
  <UPageGrid class="lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-px">
    <UPageCard
      v-for="(stat, index) in stats"
      :key="index"
      :icon="stat.icon"
      :title="stat.title"
      to="/dashboard/teams"
      variant="subtle"
      :ui="{
        container: 'gap-y-1.5',
        wrapper: 'items-start',
        leading: 'p-2.5 rounded-full bg-primary/10 ring ring-inset ring-primary/25 flex-col',
        title: 'font-normal text-muted text-xs uppercase'
      }"
      class="lg:rounded-none first:rounded-l-lg last:rounded-r-lg hover:z-1"
    >
      <div class="flex items-center gap-2">
        <span class="text-2xl font-semibold text-highlighted">
          {{ stat.value }}
        </span>

        <UBadge
          :color="stat.variation > 0 ? 'success' : 'error'"
          variant="subtle"
          class="text-xs"
        >
          {{ stat.variation > 0 ? '+' : '' }}{{ stat.variation }}%
        </UBadge>
      </div>
    </UPageCard>
  </UPageGrid>
</template>
