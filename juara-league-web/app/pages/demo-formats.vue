<template>
  <div class="min-h-screen bg-neutral-950 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] pt-24 pb-24 overflow-x-auto selection:bg-primary-500/30">
    
    <!-- Background Glows -->
    <div class="fixed top-0 left-1/4 w-[500px] h-[500px] bg-primary-600/5 blur-[120px] rounded-full pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/4 w-[500px] h-[500px] bg-purple-600/5 blur-[120px] rounded-full pointer-events-none"></div>

    <div class="max-w-[1600px] mx-auto px-8 mb-12 relative z-10">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <NuxtLink to="/demo-bracket" class="text-neutral-500 hover:text-white flex items-center gap-2 mb-4 text-sm font-medium transition-colors w-fit">
            <UIcon name="i-lucide-arrow-left" class="size-4" /> Kembali ke Demo Dasar
          </NuxtLink>
          <h1 class="text-5xl font-black text-white mb-3 tracking-tighter">Bracket Showcase</h1>
          <p class="text-neutral-400 max-w-2xl text-lg">Eksplorasi berbagai format turnamen dengan animasi neon liquid yang mulus dan layout otomatis yang responsif.</p>
        </div>

        <div class="flex bg-neutral-900 p-1 rounded-xl border border-white/5 shadow-inner">
          <button 
            v-for="f in formats" :key="f.id"
            @click="activeFormat = f.id"
            class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 flex items-center gap-2"
            :class="activeFormat === f.id ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/20' : 'text-neutral-500 hover:text-neutral-300'"
          >
            <UIcon :name="f.icon" class="size-4" />
            {{ f.name }}
          </button>
        </div>
      </div>
    </div>

    <!-- Bracket Display Container -->
    <div class="h-[calc(100vh-250px)] w-full overflow-hidden transition-opacity duration-500" :class="isChanging ? 'opacity-0' : 'opacity-100'">
      <InteractiveCanvas>
        <div class="p-24 flex items-center justify-center">
          
          <!-- Single Elimination 16 -->
          <div v-if="activeFormat === 'single-16'" class="flex items-center gap-0 min-w-max justify-center">
            <div v-for="(round, rIdx) in single16Rounds" :key="rIdx" class="flex items-center">
              <!-- Matches in Round -->
              <div class="flex flex-col justify-around h-full" :style="{ gap: `${round.gap}px` }">
                <div v-for="m in round.matches" :key="m.id">
                   <BracketNode 
                    :number="m.id" 
                    :time="m.time"
                    :match="m"
                   />
                </div>
              </div>
              <!-- Connector -->
              <div v-if="rIdx < single16Rounds.length - 1" class="flex flex-col justify-around h-full" :style="{ paddingBlock: `${round.padding}px` }">
                <div v-for="i in (round.matches.length / 2)" :key="i">
                   <BracketConnector :height="round.connectHeight" />
                </div>
              </div>
            </div>
          </div>

          <!-- Double Elimination 8 -->
          <div v-if="activeFormat === 'double-8'" class="flex flex-col gap-24 min-w-max items-center">
            <!-- Winners Bracket -->
            <div class="space-y-4">
              <h2 class="text-white/20 font-black text-4xl uppercase tracking-widest text-center">Winners Bracket</h2>
              <div class="flex items-center justify-center">
                 <div v-for="(round, rIdx) in winnersRounds" :key="rIdx" class="flex items-center">
                    <div class="flex flex-col justify-around h-full" :style="{ gap: `${round.gap}px` }">
                      <BracketNode v-for="m in round.matches" :key="m.id" :number="m.id" :time="m.time" :match="m" />
                    </div>
                    <div v-if="rIdx < winnersRounds.length - 1" class="flex flex-col justify-around h-full" :style="{ paddingBlock: `${round.padding}px` }">
                       <BracketConnector v-for="i in (round.matches.length / 2)" :key="i" :height="round.connectHeight" />
                    </div>
                 </div>
              </div>
            </div>

            <!-- Losers Bracket -->
            <div class="space-y-4">
              <h2 class="text-white/20 font-black text-4xl uppercase tracking-widest text-center">Losers Bracket</h2>
              <div class="flex items-center justify-center">
                 <div v-for="(round, rIdx) in losersRounds" :key="rIdx" class="flex items-center">
                    <div class="flex flex-col justify-around h-full" :style="{ gap: `${round.gap}px` }">
                      <BracketNode v-for="m in round.matches" :key="m.id" :number="m.id" :time="m.time" :match="m" />
                    </div>
                    <!-- Special logic for Losers Connectors -->
                    <div v-if="rIdx < losersRounds.length - 1" class="flex flex-col justify-around h-full" :style="{ paddingBlock: `${round.padding}px` }">
                       <template v-if="round.isRoundOf2">
                          <div v-for="i in (round.matches.length / 2)" :key="i">
                             <BracketConnector :height="round.connectHeight" />
                          </div>
                       </template>
                       <template v-else>
                          <div v-for="i in round.matches.length" :key="i" class="w-16 h-[140px] flex items-center justify-center relative">
                             <div class="w-full h-0.5 bg-white/5 relative overflow-hidden">
                                <div class="absolute inset-0 bg-primary-500/50 animate-liquid-flat"></div>
                             </div>
                          </div>
                       </template>
                    </div>
                 </div>
              </div>
            </div>
          </div>

          <!-- Group Stage Demo -->
          <div v-if="activeFormat === 'groups'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
            <div v-for="g in ['A', 'B', 'C', 'D']" :key="g" class="bg-neutral-900/50 backdrop-blur border border-white/5 rounded-2xl p-6 hover:border-primary-500/30 transition-all group w-[280px]">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-black text-white italic tracking-tighter">GROUP {{ g }}</h3>
                <span class="text-[10px] font-bold text-primary-400 bg-primary-500/10 px-2 py-1 rounded">ONGOING</span>
              </div>
              <div class="space-y-3">
                 <div v-for="i in 4" :key="i" class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5 group-hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-3">
                       <span class="text-neutral-500 font-mono text-xs w-4">{{ i }}</span>
                       <UAvatar src="https://i.pravatar.cc/50" size="xs" />
                       <span class="text-sm font-bold text-neutral-300">Team {{ i }}</span>
                    </div>
                    <span class="text-xs font-mono text-neutral-500">6 pts</span>
                 </div>
              </div>
            </div>
          </div>

        </div>
      </InteractiveCanvas>
    </div>
  </div>
</template>


<script setup lang="ts">
const activeFormat = ref('single-16')

const isChanging = ref(false)

const formats = [
  { id: 'single-16', name: 'Single 16', icon: 'i-lucide-list-tree' },
  { id: 'double-8', name: 'Double 8', icon: 'i-lucide-layers' },
  { id: 'groups', name: 'Group Stage', icon: 'i-lucide-layout-grid' }
]

watch(activeFormat, () => {
  isChanging.value = true
  setTimeout(() => isChanging.value = false, 300)
})

// Mock Data for Single 16
const single16Rounds = [
  {
    gap: 32,
    padding: 82,
    connectHeight: 152,
    matches: Array.from({ length: 8 }, (_, i) => ({
      id: i + 1, time: '10:00', t1: { name: 'Team A'+i, score: 2, logo: 'https://i.pravatar.cc/50?u=a'+i }, t2: { name: 'Team B'+i, score: 0, logo: 'https://i.pravatar.cc/50?v=b'+i }, winner: 1
    }))
  },
  {
    gap: 120,
    padding: 126,
    connectHeight: 240,
    matches: Array.from({ length: 4 }, (_, i) => ({
      id: i + 9, time: '14:00', t1: { name: 'Winner W'+(i+1), score: null, logo: 'https://i.pravatar.cc/50?u=w'+i }, t2: { name: 'Winner W'+(i+2), score: null, logo: 'https://i.pravatar.cc/50?v=w'+i }, winner: null
    }))
  },
  {
    gap: 296,
    padding: 214,
    connectHeight: 416,
    matches: Array.from({ length: 2 }, (_, i) => ({
      id: i + 13, time: '18:00', t1: { name: 'Semi Finalist', score: null, logo: 'https://i.pravatar.cc/50?u=s'+i }, t2: { name: 'Semi Finalist', score: null, logo: 'https://i.pravatar.cc/50?v=s'+i }, winner: null
    }))
  },
  {
    gap: 0,
    padding: 0,
    connectHeight: 0,
    matches: [
      { id: 15, time: 'Finals', t1: { name: 'Finalist 1', score: null, logo: 'https://i.pravatar.cc/50?u=f1' }, t2: { name: 'Finalist 2', score: null, logo: 'https://i.pravatar.cc/50?u=f2' }, winner: null }
    ]
  }
]

// Mock Data for Double 8
const winnersRounds = [
  {
    gap: 24, padding: 78, connectHeight: 144,
    matches: Array.from({ length: 4 }, (_, i) => ({ id: i + 1, time: 'W1', t1: { name: 'Winner P'+i, score: 2, logo: 'https://i.pravatar.cc/50?u=wp'+i }, t2: { name: 'Winner P'+(i+1), score: 1, logo: 'https://i.pravatar.cc/50?v=wp'+i }, winner: 1 }))
  },
  {
    gap: 112, padding: 122, connectHeight: 232,
    matches: [
      { id: 5, time: 'W2', t1: { name: 'Semi 1', score: 2, logo: 'https://i.pravatar.cc/50?u=ws1' }, t2: { name: 'Semi 2', score: 1, logo: 'https://i.pravatar.cc/50?u=ws2' }, winner: 1 },
      { id: 6, time: 'W2', t1: { name: 'Semi 3', score: 2, logo: 'https://i.pravatar.cc/50?u=ws3' }, t2: { name: 'Semi 4', score: 1, logo: 'https://i.pravatar.cc/50?u=ws4' }, winner: 1 }
    ]
  },
  {
    gap: 0, padding: 0, connectHeight: 0,
    matches: [{ id: 7, time: 'Winners Final', t1: { name: 'W-Finalist 1', score: null, logo: 'https://i.pravatar.cc/50?u=wf1' }, t2: { name: 'W-Finalist 2', score: null, logo: 'https://i.pravatar.cc/50?u=wf2' }, winner: null }]
  }
]

const losersRounds = [
  {
    gap: 24, padding: 10, connectHeight: 0,
    matches: Array.from({ length: 2 }, (_, i) => ({ id: 100 + i, time: 'L1', t1: { name: 'Loser W'+i, score: 2, logo: 'https://i.pravatar.cc/50?u=l'+i }, t2: { name: 'Loser W'+(i+1), score: 0, logo: 'https://i.pravatar.cc/50?v=l'+i }, winner: 1 }))
  },
  {
    gap: 24, padding: 78, connectHeight: 144, isRoundOf2: true,
    matches: Array.from({ length: 2 }, (_, i) => ({ id: 200 + i, time: 'L2', t1: { name: 'Survivor', score: null, logo: 'https://i.pravatar.cc/50?u=ls'+i }, t2: { name: 'Survivor', score: null, logo: 'https://i.pravatar.cc/50?v=ls'+i }, winner: null }))
  },
  {
    gap: 24, padding: 0, connectHeight: 0, isRoundOf2: false,
    matches: [{ id: 300, time: 'Losers Final', t1: { name: 'Last Survivor', score: null, logo: 'https://i.pravatar.cc/50?u=lfs' }, t2: { name: 'TBD', score: null, logo: '' }, winner: null }]
  }
]

</script>

<style scoped>
.animate-liquid-flat {
  background: linear-gradient(90deg, transparent, var(--primary-500), transparent);
  width: 50%;
  animation: liquid-flat 2s linear infinite;
}

@keyframes liquid-flat {
  from { transform: translateX(-200%); }
  to { transform: translateX(200%); }
}
</style>
