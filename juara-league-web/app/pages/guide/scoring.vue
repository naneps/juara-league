<script setup lang="ts">
const { t } = useI18n()

definePageMeta({
  layout: 'default'
})

const scoringItems = [
  {
    title: 'guide.scoring.score.title',
    desc: 'guide.scoring.score.desc',
    example: 'guide.scoring.score.example',
    icon: 'i-lucide-hash',
    color: 'primary',
    bg: 'bg-primary-500/10',
    border: 'border-primary-500/20',
    text: 'text-primary-500'
  },
  {
    title: 'guide.scoring.points.title',
    desc: 'guide.scoring.points.desc',
    example: 'guide.scoring.points.example',
    icon: 'i-lucide-award',
    color: 'amber',
    bg: 'bg-amber-500/10',
    border: 'border-amber-500/20',
    text: 'text-amber-500'
  }
]

const formats = [
  {
    title: 'guide.scoring.formats.elimination.title',
    desc: 'guide.scoring.formats.elimination.desc',
    icon: 'i-lucide-swords',
    accent: 'bg-red-500/10 text-red-500'
  },
  {
    title: 'guide.scoring.formats.league.title',
    desc: 'guide.scoring.formats.league.desc',
    icon: 'i-lucide-table-properties',
    accent: 'bg-indigo-500/10 text-indigo-500'
  }
]

const tieBreakers = [
  { label: 'guide.scoring.tie_breaker.step1', icon: 'i-lucide-arrow-right-left' },
  { label: 'guide.scoring.tie_breaker.step2', icon: 'i-lucide-trending-up' },
  { label: 'guide.scoring.tie_breaker.step3', icon: 'i-lucide-user-check' }
]
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-neutral-950 pt-32 pb-24 relative overflow-hidden transition-colors duration-500">
    
    <!-- Background Decoration -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-primary-500/5 blur-[120px] rounded-full pointer-events-none" />
    <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-amber-500/5 blur-[120px] rounded-full pointer-events-none" />

    <UContainer>
      <!-- Hero Section -->
      <header class="max-w-3xl mx-auto text-center mb-20 animate-fade-up">
        <UBadge variant="subtle" color="primary" class="mb-6 px-4 py-1 rounded-full font-bold uppercase tracking-widest text-[10px]">
          Resources & Guides
        </UBadge>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-neutral-900 dark:text-white mb-6 tracking-tight leading-tight">
          {{ t('guide.scoring.title') }}
        </h1>
        <p class="text-lg md:text-xl text-neutral-600 dark:text-neutral-400 font-medium">
          {{ t('guide.scoring.subtitle') }}
        </p>
      </header>

      <!-- Main Comparison -->
      <section class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-24 relative z-10">
        <div 
          v-for="(item, idx) in scoringItems" 
          :key="idx"
          class="group relative p-8 rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 hover:border-primary-500/30 transition-all duration-300 shadow-xl shadow-neutral-200/20 dark:shadow-none animate-fade-up"
          :style="{ animationDelay: `${0.1 + (idx * 0.1)}s` }"
        >
          <div :class="[item.bg, 'w-16 h-16 rounded-2xl flex items-center justify-center mb-8 ring-1', item.border]">
            <UIcon :name="item.icon" :class="['size-8', item.text]" />
          </div>
          
          <h2 class="text-2xl font-black text-neutral-900 dark:text-white mb-4 italic tracking-tighter uppercase">
            {{ t(item.title) }}
          </h2>
          
          <p class="text-neutral-600 dark:text-neutral-300 mb-6 leading-relaxed font-medium">
            {{ t(item.desc) }}
          </p>

          <div class="p-4 rounded-xl bg-neutral-50 dark:bg-white/5 border border-dashed border-neutral-200 dark:border-white/10">
            <span class="text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest block mb-2">Pro Tip / Contoh:</span>
            <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">
              {{ t(item.example) }}
            </p>
          </div>
          
          <!-- Decoration -->
          <div class="absolute -bottom-2 -right-2 w-24 h-24 opacity-5 group-hover:opacity-10 transition-opacity">
            <UIcon :name="item.icon" class="size-full text-neutral-500" />
          </div>
        </div>
      </section>

      <!-- Format Breakdown -->
      <section class="mb-24 animate-fade-up" style="animation-delay: 0.3s">
        <div class="text-center mb-12">
          <h3 class="text-3xl font-black text-neutral-900 dark:text-white mb-4">Penggunaan di Setiap Format</h3>
          <div class="h-1 w-20 bg-primary-500 mx-auto rounded-full" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <UCard 
            v-for="(f, idx) in formats" 
            :key="idx"
            class="overflow-hidden hover:shadow-2xl transition-shadow border-neutral-200 dark:border-white/5"
            :ui="{ body: { padding: 'p-0' } }"
          >
            <div class="flex items-stretch h-full">
              <div :class="[f.accent, 'w-16 flex items-center justify-center shrink-0 border-r border-white/10']">
                <UIcon :name="f.icon" class="size-8" />
              </div>
              <div class="p-6">
                <h4 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">{{ t(f.title) }}</h4>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed italic">
                  {{ t(f.desc) }}
                </p>
              </div>
            </div>
          </UCard>
        </div>
      </section>

      <!-- Tie-breakers -->
      <section class="max-w-4xl mx-auto animate-fade-up" style="animation-delay: 0.4s">
        <div class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-3xl p-8 md:p-12 text-white shadow-2xl shadow-primary-500/20 relative overflow-hidden">
          <!-- Glassmorphism overlay -->
          <div class="absolute inset-0 bg-white/5 backdrop-blur-sm pointer-events-none" />
          
          <div class="relative z-10 flex flex-col md:flex-row items-center gap-12">
            <div class="shrink-0 text-center md:text-left">
              <div class="w-20 h-20 bg-white/20 rounded-3xl flex items-center justify-center mb-6 mx-auto md:mx-0 shadow-inner ring-1 ring-white/30">
                <UIcon name="i-lucide-layers-2" class="size-10 text-white" />
              </div>
              <h3 class="text-2xl font-black mb-4 uppercase tracking-tighter italic">{{ t('guide.scoring.tie_breaker.title') }}</h3>
              <p class="text-primary-100 max-w-xs leading-relaxed">
                {{ t('guide.scoring.tie_breaker.desc') }}
              </p>
            </div>

            <div class="flex-grow space-y-4 w-full">
              <div 
                v-for="(step, sIdx) in tieBreakers" 
                :key="sIdx"
                class="flex items-center gap-4 bg-white/10 p-4 rounded-2xl border border-white/10 hover:bg-white/20 transition-colors shadow-sm"
              >
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm font-black">{{ sIdx + 1 }}</div>
                <UIcon :name="step.icon" class="size-5 text-primary-200" />
                <span class="font-bold text-lg tracking-tight">{{ t(step.label) }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer CTA -->
      <footer class="mt-24 text-center animate-fade-up" style="animation-delay: 0.5s">
        <NuxtLink to="/tournaments">
          <UButton 
            size="xl" 
            color="primary" 
            label="Mulai Lihat Turnamen" 
            trailing-icon="i-lucide-arrow-right"
            class="px-8 py-4 rounded-2xl shadow-xl shadow-primary-500/20 font-black uppercase tracking-widest text-xs"
          />
        </NuxtLink>
      </footer>
    </UContainer>
  </div>
</template>

<style scoped>
@keyframes fade-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-up {
  animation: fade-up 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}
</style>
