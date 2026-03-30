<script setup lang="ts">
import type { Tournament, TournamentFilter } from '~/types/tournament'

const filters = reactive<TournamentFilter>({
  search: '',
  category: 'Semua Kategori',
  status: 'Semua Status',
  mode: 'Semua Mode'
})

const categories = ['Semua Kategori', 'Sepak Bola', 'Badminton', 'Basket', 'E-sports', 'Lainnya']
const statuses = ['Semua Status', 'open', 'ongoing', 'finished']
const modes = ['Semua Mode', 'online', 'offline']

// Dummy Data
const dummyTournaments: Tournament[] = [
  {
    id: 1,
    title: 'Liga Mahasiswa Futsal Indonesia 2026',
    slug: 'liga-mahasiswa-futsal-2026',
    description: 'Turnamen futsal antar mahasiswa se-Indonesia memperebutkan piala bergilir.',
    category: 'Sepak Bola',
    status: 'open',
    mode: 'offline',
    location: 'Jakarta',
    image: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1000',
    organizer: { name: 'Kemenpora RI', avatar: 'https://i.pravatar.cc/150?u=kemenpora', is_verified: true },
    prize_pool: 'Rp 50jt',
    entry_fee: 'Rp 200rb',
    start_date: '15 Mei 2026',
    max_participants: 32,
    current_participants: 12,
    bracket_type: 'single_elimination'
  },
  {
    id: 2,
    title: 'Mobile Legends: Bang Bang Championship S3',
    slug: 'mlbb-championship-s3',
    description: 'Buktikan tim kamu yang terbaik di Land of Dawn!',
    category: 'E-sports',
    status: 'ongoing',
    mode: 'online',
    image: 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1000',
    organizer: { name: 'Moonton', avatar: 'https://i.pravatar.cc/150?u=moonton', is_verified: true },
    prize_pool: 'Rp 100jt',
    entry_fee: 'Gratis',
    start_date: '10 April 2026',
    max_participants: 128,
    current_participants: 128,
    bracket_type: 'double_elimination'
  },
  {
    id: 3,
    title: 'Badminton Open: Piala Gubernur Jabar',
    slug: 'piala-gubernur-jabar-badminton',
    description: 'Turnamen badminton terbuka untuk kategori ganda putra.',
    category: 'Badminton',
    status: 'open',
    mode: 'offline',
    location: 'Bandung',
    image: 'https://images.unsplash.com/photo-1626224580194-860f4693f1aa?q=80&w=1000',
    organizer: { name: 'PBSI Jabar', avatar: 'https://i.pravatar.cc/150?u=pbsi', is_verified: true },
    prize_pool: 'Rp 25jt',
    entry_fee: 'Rp 150rb',
    start_date: '20 Mei 2026',
    max_participants: 64,
    current_participants: 45,
    bracket_type: 'single_elimination'
  },
  {
    id: 4,
    title: 'Valorant Indonesia Challenger Academy',
    slug: 'valorant-academy-2026',
    description: 'Tempat bertemunya para talenta muda Valorant Indonesia.',
    category: 'E-sports',
    status: 'open',
    mode: 'online',
    image: 'https://images.unsplash.com/photo-1614017414570-17d12bf38cad?q=80&w=1000',
    organizer: { name: 'Riot Games', avatar: 'https://i.pravatar.cc/150?u=riot', is_verified: true },
    prize_pool: 'Rp 30jt',
    entry_fee: 'Rp 50rb',
    start_date: '1 Juni 2026',
    max_participants: 64,
    current_participants: 28,
    bracket_type: 'single_elimination'
  },
  {
    id: 5,
    title: 'PUBG Mobile: Pro Player Hunt',
    slug: 'pubgm-hunt-2026',
    description: 'Cari bakat terpendam untuk tim esport profesional.',
    category: 'E-sports',
    status: 'finished',
    mode: 'online',
    image: 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?q=80&w=1000',
    organizer: { name: 'Tencent', avatar: 'https://i.pravatar.cc/150?u=tencent', is_verified: true },
    prize_pool: 'Rp 15jt',
    entry_fee: 'Gratis',
    start_date: '25 Maret 2026',
    max_participants: 100,
    current_participants: 100,
    bracket_type: 'round_robin'
  },
  {
    id: 6,
    title: 'Jakarta Basket Street Jam 3x3',
    slug: 'jakarta-street-jam-3x3',
    description: 'Kompetisi basket 3x3 jalanan paling prestisius di ibu kota.',
    category: 'Basket',
    status: 'open',
    mode: 'offline',
    location: 'Jakarta',
    image: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?q=80&w=1000',
    organizer: { name: 'Perbasi DKI', avatar: 'https://i.pravatar.cc/150?u=perbasi', is_verified: false },
    prize_pool: 'Rp 10jt',
    entry_fee: 'Rp 100rb',
    start_date: '5 Juni 2026',
    max_participants: 16,
    current_participants: 4,
    bracket_type: 'double_elimination'
  }
]

const filteredTournaments = computed(() => {
  return dummyTournaments.filter(t => {
    const sMatch = t.title.toLowerCase().includes(filters.search.toLowerCase())
    const cMatch = filters.category === 'Semua Kategori' || t.category === filters.category
    const stMatch = filters.status === 'Semua Status' || t.status === filters.status
    const mMatch = filters.mode === 'Semua Mode' || t.mode === filters.mode
    return sMatch && cMatch && stMatch && mMatch
  })
})
</script>

<template>
  <div class="min-h-screen bg-neutral-950 pb-20 overflow-hidden relative">
    <!-- Decoraive Background -->
    <div class="absolute top-0 right-0 w-full h-[600px] bg-primary-500/5 blur-[120px] rounded-full pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-full h-[400px] bg-primary-600/5 blur-[100px] rounded-full pointer-events-none"></div>

    <!-- Header Section -->
    <div class="pt-32 pb-16 relative z-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight mb-6 uppercase">
          Temukan <span class="text-primary-500">Kemenanganmu</span>
        </h1>
        <p class="text-neutral-400 text-lg max-w-2xl mx-auto font-medium">
          Daftar dan kelola turnamen favoritmu. Dari sepak bola hingga e-sports, semua kompetisi ada di sini.
        </p>
      </div>

      <!-- Filters & Search Bar -->
      <div class="bg-neutral-900/40 backdrop-blur-2xl p-4 sm:p-2 rounded-3xl sm:rounded-full border border-white/10 shadow-2xl relative overflow-hidden group mb-12 ring-1 ring-white/5">
        <div class="flex flex-col lg:flex-row items-center gap-2">
          <!-- Search -->
          <div class="w-full lg:flex-1">
            <UInput 
              v-model="filters.search" 
              placeholder="Cari turnamen..." 
              icon="i-lucide-search"
              size="xl"
              variant="none"
              class="w-full"
              :ui="{ 
                base: 'bg-transparent border-none focus:ring-0 text-white placeholder-neutral-500 rounded-full py-4',
                leading: 'text-neutral-500 group-focus-within:text-primary-400 transition-colors'
              }"
            />
          </div>

          <div class="hidden lg:block w-px h-8 bg-white/10"></div>
          
          <!-- Filters Group -->
          <div class="w-full lg:w-auto flex flex-col sm:flex-row items-center gap-2 p-2 lg:p-0">
            <!-- Category -->
            <USelectMenu 
              v-model="filters.category" 
              :options="categories"
              size="lg"
              variant="none"
              class="w-full sm:w-44"
              :ui="{ 
                base: 'bg-white/5 hover:bg-white/10 border-none focus:ring-primary-500/50 rounded-2xl sm:rounded-full transition-all text-neutral-300 font-bold px-4',
                content: 'bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl p-2',
                item: 'rounded-xl hover:bg-primary-500/10 hover:text-primary-400 font-medium'
              }"
            >
              <template #leading>
                <UIcon name="i-lucide-layout-grid" class="size-4 text-primary-400" />
              </template>
            </USelectMenu>

            <!-- Status -->
            <USelectMenu 
              v-model="filters.status" 
              :options="statuses"
              size="lg"
              variant="none"
              class="w-full sm:w-44"
              :ui="{ 
                base: 'bg-white/5 hover:bg-white/10 border-none focus:ring-primary-500/50 rounded-2xl sm:rounded-full transition-all text-neutral-300 font-bold px-4',
                content: 'bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl p-2',
                item: 'rounded-xl hover:bg-primary-500/10 hover:text-primary-400 font-medium'
              }"
            >
              <template #leading>
                <UIcon name="i-lucide-activity" class="size-4 text-primary-400" />
              </template>
            </USelectMenu>

            <!-- Mode -->
            <USelectMenu 
              v-model="filters.mode" 
              :options="modes"
              size="lg"
              variant="none"
              class="w-full sm:w-44"
              :ui="{ 
                base: 'bg-white/5 hover:bg-white/10 border-none focus:ring-primary-500/50 rounded-2xl sm:rounded-full transition-all text-neutral-300 font-bold px-4',
                content: 'bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl p-2',
                item: 'rounded-xl hover:bg-primary-500/10 hover:text-primary-400 font-medium'
              }"
            >
              <template #leading>
                <UIcon name="i-lucide-globe" class="size-4 text-primary-400" />
              </template>
            </USelectMenu>

            <!-- Reset -->
            <UButton 
              icon="i-lucide-refresh-cw" 
              color="primary" 
              variant="soft" 
              size="lg" 
              class="w-full sm:w-auto rounded-2xl sm:rounded-full hover:scale-105 active:scale-95 transition-all px-4"
              @click="Object.assign(filters, { search: '', category: 'Semua Kategori', status: 'Semua Status', mode: 'Semua Mode' })"
            />
          </div>
        </div>
      </div>

      <!-- Results Grid -->
      <div v-if="filteredTournaments.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <TournamentCard 
          v-for="tournament in filteredTournaments" 
          :key="tournament.id" 
          :tournament="tournament" 
        />
      </div>

      <!-- Empty State -->
      <div v-else class="py-20 flex flex-col items-center justify-center text-center">
        <div class="bg-neutral-900/50 p-8 rounded-full mb-6 ring-1 ring-white/5">
          <UIcon name="i-lucide-search-x" class="size-16 text-neutral-600" />
        </div>
        <h3 class="text-2xl font-bold text-white mb-2">Tidak Menemukan Apapun</h3>
        <p class="text-neutral-500 font-medium max-w-xs mx-auto">
          Coba atur ulang filter atau kata kunci pencarian Anda untuk hasil yang lebih baik.
        </p>
        <UButton 
          icon="i-lucide-rotate-ccw" 
          color="primary" 
          variant="soft" 
          class="mt-8 font-bold rounded-xl"
          @click="Object.assign(filters, { search: '', category: 'Semua Kategori', status: 'Semua Status', mode: 'Semua Mode' })"
        >
          Reset Filter
        </UButton>
      </div>
    </div>
  </div>
</template>
