<template>
  <div class="relative flex flex-col justify-center my-3 group w-[280px] shrink-0 border border-white/10 bg-neutral-950/80 backdrop-blur shadow-2xl rounded-xl z-10 hover:border-primary-500/50 transition-colors cursor-pointer select-none">
    
    <!-- Match Info Handle -->
    <div class="flex justify-between items-center text-[10px] font-bold tracking-widest uppercase text-neutral-500 px-4 pt-3 pb-1">
      <span class="text-white/50">Match {{ number }}</span>
      <span class="bg-neutral-800 text-white/40 px-1.5 py-0.5 rounded">{{ time }}</span>
    </div>

    <!-- Team 1 -->
    <div 
      class="flex items-center justify-between px-4 py-2 border-l-2 transition-colors"
      :class="[
        match.winner === 1 ? 'border-primary-500 bg-primary-500/5' : 'border-transparent',
        match.winner === 2 ? 'opacity-50' : ''
      ]"
    >
      <div class="flex items-center gap-3 truncate">
        <UAvatar :src="match.t1.logo" :alt="match.t1.name" size="xs" />
        <span class="font-semibold text-sm truncate" :class="match.winner === 1 ? 'text-white' : 'text-neutral-300'">{{ match.t1.name || 'TBD' }}</span>
      </div>
      <span class="font-mono text-base ml-4" :class="match.winner === 1 ? 'text-primary-400 font-bold' : 'text-neutral-500'">{{ match.t1.score ?? '-' }}</span>
    </div>

    <div class="h-px bg-white/5 mx-4"></div>

    <!-- Team 2 -->
    <div 
      class="flex items-center justify-between px-4 py-2 border-l-2 transition-colors"
      :class="[
        match.winner === 2 ? 'border-primary-500 bg-primary-500/5' : 'border-transparent',
        match.winner === 1 ? 'opacity-50' : ''
      ]"
    >
      <div class="flex items-center gap-3 truncate">
        <UAvatar :src="match.t2.logo" :alt="match.t2.name" size="xs" />
        <span class="font-semibold text-sm truncate" :class="match.winner === 2 ? 'text-white' : 'text-neutral-300'">{{ match.t2.name || 'TBD' }}</span>
      </div>
      <span class="font-mono text-base ml-4" :class="match.winner === 2 ? 'text-primary-400 font-bold' : 'text-neutral-500'">{{ match.t2.score ?? '-' }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  number: number,
  time: string,
  match: {
    t1: { name: string, score: number | null, logo: string },
    t2: { name: string, score: number | null, logo: string },
    winner: number | null
  }
}>()
</script>
