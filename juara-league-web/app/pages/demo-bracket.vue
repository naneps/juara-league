<template>
  <div class="min-h-screen bg-white dark:bg-neutral-950 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] pt-24 pb-12 overflow-x-auto relative transition-colors duration-500">
    
    <!-- Header -->
    <div class="absolute top-24 left-1/2 -translate-x-1/2 w-[800px] h-[300px] bg-primary-600/10 mix-blend-screen blur-[120px] rounded-full pointer-events-none"></div>
    
    <div class="max-w-[1400px] mx-auto px-8 mb-16 relative z-10 flex items-center justify-between">
      <div>
        <NuxtLink to="/" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white flex items-center gap-2 mb-4 text-sm font-medium transition-colors w-fit">
          <UIcon name="i-lucide-arrow-left" class="size-4" /> Kembali ke Depan
        </NuxtLink>
        <h1 class="text-4xl font-extrabold text-neutral-900 dark:text-white mb-2 tracking-tight">Valorant Community Cup S4</h1>
        <div class="flex items-center gap-4 text-sm text-neutral-500 dark:text-neutral-400">
          <span class="flex items-center gap-1.5"><UIcon name="i-lucide-trophy" class="size-4 text-primary-400" /> Single Elimination (Best of 3)</span>
          <span class="flex items-center gap-1.5"><UIcon name="i-lucide-users" class="size-4" /> 8 Besar</span>
        </div>
      </div>
      
      <div class="text-right hidden sm:block">
        <div class="text-sm text-neutral-400 mb-1">Status Turnamen</div>
        <div class="inline-flex items-center gap-2 bg-red-500/10 text-red-500 px-3 py-1 rounded-full font-bold uppercase text-xs tracking-wider border border-red-500/20">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
          </span>
          Live: Grand Final
        </div>
      </div>
    </div>

    <!-- The Bracket Canvas Container -->
    <div class="h-[calc(100vh-200px)] w-full overflow-hidden">
      <InteractiveCanvas>
        <div class="p-24 flex items-center justify-center min-w-max">
          
          <!-- Quarter Finals Column -->
          <div class="flex flex-col gap-12 relative w-[280px]">
            <BracketNode 
              :number="1" time="10:00 AM"
              :match="{
                t1: { name: 'RRQ Hoshi', score: 2, logo: 'https://i.pravatar.cc/150?u=rrq' },
                t2: { name: 'AURA Fire', score: 0, logo: 'https://i.pravatar.cc/150?u=aura' },
                winner: 1
              }"
            />
            <BracketNode 
              :number="2" time="11:30 AM"
              :match="{
                t1: { name: 'Evos Legends', score: 1, logo: 'https://i.pravatar.cc/150?u=evos' },
                t2: { name: 'ONIC Esports', score: 2, logo: 'https://i.pravatar.cc/150?u=onic' },
                winner: 2
              }"
            />
            <BracketNode 
              :number="3" time="13:00 PM"
              :match="{
                t1: { name: 'Bigetron Alpha', score: 2, logo: 'https://i.pravatar.cc/150?u=btr' },
                t2: { name: 'Alter Ego', score: 1, logo: 'https://i.pravatar.cc/150?u=ae' },
                winner: 1
              }"
            />
            <BracketNode 
              :number="4" time="14:30 PM"
              :match="{
                t1: { name: 'Geek Fam', score: 0, logo: 'https://i.pravatar.cc/150?u=geek' },
                t2: { name: 'Rebellion Zion', score: 2, logo: 'https://i.pravatar.cc/150?u=rbg' },
                winner: 2
              }"
            />
          </div>

          <!-- Connector 1 -->
          <div class="flex flex-col justify-around h-full py-[5.25rem]">
            <BracketConnector :height="168" />
            <BracketConnector :height="168" />
          </div>

          <!-- Semi Finals Column -->
          <div class="flex flex-col gap-[9rem] relative w-[280px]">
            <BracketNode 
              :number="5" time="16:00 PM"
              :match="{
                t1: { name: 'RRQ Hoshi', score: 1, logo: 'https://i.pravatar.cc/150?u=rrq' },
                t2: { name: 'ONIC Esports', score: 2, logo: 'https://i.pravatar.cc/150?u=onic' },
                winner: 2
              }"
            />
            <BracketNode 
              :number="6" time="18:00 PM"
              :match="{
                t1: { name: 'Bigetron Alpha', score: 2, logo: 'https://i.pravatar.cc/150?u=btr' },
                t2: { name: 'Rebellion Zion', score: 0, logo: 'https://i.pravatar.cc/150?u=rbg' },
                winner: 1
              }"
            />
          </div>
          
          <!-- Connector 2 -->
          <div class="flex flex-col justify-center h-full">
             <BracketConnector :height="262" />
          </div>

          <!-- Grand Final Column -->
          <div class="flex flex-col justify-center relative w-[280px] h-full">
            <!-- Glow Behind Winner -->
            <div class="absolute inset-0 bg-primary-500/20 blur-2xl rounded-full scale-150 animate-pulse pointer-events-none"></div>
            <div class="text-center mb-4 relative z-10 flex flex-col items-center">
               <UIcon name="i-lucide-medal" class="size-10 text-primary-400 drop-shadow-lg mb-2" />
               <span class="uppercase tracking-[0.2em] font-bold text-xs text-primary-400">Grand Final</span>
            </div>
            <BracketNode 
              class="border-primary-500/50 shadow-2xl shadow-primary-900/40 relative z-10 scale-110 mx-auto"
              :number="7" time="LIVE NOW"
              :match="{
                t1: { name: 'ONIC Esports', score: 2, logo: 'https://i.pravatar.cc/150?u=onic' },
                t2: { name: 'Bigetron Alpha', score: 2, logo: 'https://i.pravatar.cc/150?u=btr' },
                winner: null
              }"
            />
          </div>

        </div>
      </InteractiveCanvas>
    </div>

  </div>
</template>

<script setup lang="ts">
// Disable normal application layout so we have a full wide canvas
definePageMeta({
  layout: false
})
</script>
