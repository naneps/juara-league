<template>
  <div class="min-h-screen relative text-neutral-900 dark:text-white transition-colors duration-500">

    <ClientOnly>
      <HeroBackground />
    </ClientOnly>


    <!-- ─── HERO ─── -->
    <section class="relative z-10 max-w-6xl mx-auto px-6 pt-28 pb-24 text-center">

      <!-- Badge -->
      <div class="hero-item inline-flex items-center gap-2 bg-neutral-100 dark:bg-white/5 border border-neutral-200 dark:border-white/10 rounded-full px-4 py-1.5 text-sm text-neutral-500 dark:text-neutral-400 mb-10">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-500 dark:bg-primary-400 opacity-75" />
          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500 dark:bg-primary-400" />
        </span>
        Platform manajemen turnamen olahraga
      </div>

      <!-- Headline -->
      <h1 class="hero-item text-5xl md:text-6xl lg:text-7xl font-black tracking-tight leading-[1.1] text-neutral-900 dark:text-white mb-6" style="animation-delay:.08s">
        Atur turnamen<br />
        <span class="text-primary-500 dark:text-primary-400">tanpa ribet.</span>
      </h1>

      <!-- Sub -->
      <p class="hero-item text-lg md:text-xl text-neutral-600 dark:text-neutral-400 max-w-xl mx-auto leading-relaxed mb-10" style="animation-delay:.16s">
        Buat bracket, kelola peserta, dan pantau hasil — semua di satu tempat. Mulai gratis, tanpa kartu kredit.
      </p>

      <!-- CTA -->
      <div class="hero-item flex flex-col sm:flex-row items-center justify-center gap-3" style="animation-delay:.24s">
        <NuxtLink to="/register">
          <UButton size="lg" icon="i-lucide-zap" class="px-8">
            Buat Turnamen Sekarang
          </UButton>
        </NuxtLink>
        <NuxtLink to="/demo-bracket">
          <UButton size="lg" color="neutral" variant="ghost" trailing-icon="i-lucide-arrow-right">
            Lihat Demo Bracket
          </UButton>
        </NuxtLink>
      </div>

      <!-- Social proof -->
      <p class="hero-item mt-8 text-sm text-neutral-400 dark:text-neutral-600" style="animation-delay:.32s">
        Sudah dipakai untuk ratusan turnamen lokal di Indonesia
      </p>
    </section>

    <!-- ─── DIVIDER ─── -->
    <div class="max-w-6xl mx-auto px-6">
      <div class="h-px bg-gradient-to-r from-transparent via-neutral-200 dark:via-white/10 to-transparent" />
    </div>

    <!-- ─── FITUR ─── -->
    <section id="fitur" class="relative z-10 max-w-6xl mx-auto px-6 py-24">
      <div class="text-center mb-16">
        <p class="text-sm text-primary-500 dark:text-primary-400 font-bold uppercase tracking-widest mb-3">Fitur</p>
        <h2 class="text-3xl md:text-4xl font-black text-neutral-900 dark:text-white mb-4">Semua yang kamu butuhkan</h2>
        <p class="text-neutral-600 dark:text-neutral-400 max-w-lg mx-auto">Dirancang untuk organizer yang mau fokus ke turnamen, bukan ngurusin teknis.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-primary-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
          <div class="w-10 h-10 rounded-xl border bg-primary-500/10 border-primary-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-git-branch" class="size-5 text-primary-500 dark:text-primary-400" />
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1.5">Multi-format Bracket</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Single/Double Elimination, Round Robin, Swiss. Kamu pilih, sistem yang urus.</p>
        </div>

        <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-sky-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
          <div class="w-10 h-10 rounded-xl border bg-sky-500/10 border-sky-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-activity" class="size-5 text-sky-500 dark:text-sky-400" />
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1.5">Bracket Publik Real-time</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Bagikan satu link ke penonton. Mereka bisa pantau hasil tanpa perlu login.</p>
        </div>

        <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-emerald-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
          <div class="w-10 h-10 rounded-xl border bg-emerald-500/10 border-emerald-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-users" class="size-5 text-emerald-500 dark:text-emerald-400" />
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1.5">Manajemen Tim & Peserta</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Registrasi peserta, approve tim, atur kapasitas — mudah dari dashboard.</p>
        </div>

        <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-amber-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
          <div class="w-10 h-10 rounded-xl border bg-amber-500/10 border-amber-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-shield-check" class="size-5 text-amber-500 dark:text-amber-400" />
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1.5">Akses Berbasis Role</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Owner, wasit, kapten tim — masing-masing punya hak akses yang pas.</p>
        </div>

        <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-purple-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
          <div class="w-10 h-10 rounded-xl border bg-purple-500/10 border-purple-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-layers" class="size-5 text-purple-500 dark:text-purple-400" />
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1.5">Multi-stage Turnamen</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Fase grup → knockout dalam satu turnamen. Fleksibel sesuai kebutuhan.</p>
        </div>

        <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-rose-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
          <div class="w-10 h-10 rounded-xl border bg-rose-500/10 border-rose-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-smartphone" class="size-5 text-rose-500 dark:text-rose-400" />
          </div>
          <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1.5">Mobile-friendly</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Works di HP, tablet, laptop. Pantau turnamen dari mana aja.</p>
        </div>

      </div>
    </section>

    <!-- ─── TURNAMEN AKTIF ─── -->
    <section id="turnamen" class="relative z-10 max-w-6xl mx-auto px-6 pb-24">

      <div class="flex items-end justify-between mb-10">
        <div>
          <p class="text-sm text-primary-500 dark:text-primary-400 font-bold uppercase tracking-widest mb-2">Live Sekarang</p>
          <h2 class="text-2xl md:text-3xl font-black text-neutral-900 dark:text-white">Turnamen yang sedang jalan</h2>
        </div>
        <NuxtLink to="/tournaments">
          <UButton color="neutral" variant="ghost" size="sm" trailing-icon="i-lucide-arrow-right" class="hidden sm:flex">
            Lihat Semua
          </UButton>
        </NuxtLink>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="i in 3" :key="i" class="h-80 bg-neutral-100 dark:bg-neutral-900 animate-pulse rounded-2xl" />
      </div>

      <!-- Empty State -->
      <div v-else-if="liveTournaments.length === 0" class="bg-neutral-50 dark:bg-neutral-900/40 border border-dashed border-neutral-200 dark:border-white/10 rounded-2xl p-12 text-center">
        <UIcon name="i-lucide-trophy" class="size-12 text-neutral-300 dark:text-neutral-700 mx-auto mb-4" />
        <p class="text-neutral-500 dark:text-neutral-400 font-medium">Belum ada turnamen yang sedang berlangsung saat ini.</p>
        <NuxtLink to="/tournaments" class="text-primary-500 font-bold text-sm mt-2 block hover:underline">Lihat semua turnamen &rarr;</NuxtLink>
      </div>

      <!-- Live Tournaments Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <TournamentCard 
          v-for="t in liveTournaments" 
          :key="t.id" 
          :tournament="t" 
        />
      </div>
    </section>

    <!-- ─── FORMAT ─── -->
    <section id="format" class="relative z-10 border-t border-neutral-100 dark:border-white/5">
      <div class="max-w-6xl mx-auto px-6 py-24">
        <div class="text-center mb-16">
          <p class="text-sm text-primary-500 dark:text-primary-400 font-bold uppercase tracking-widest mb-3">Format</p>
          <h2 class="text-3xl md:text-4xl font-black text-neutral-900 dark:text-white mb-4">Format turnamen yang tersedia</h2>
          <p class="text-neutral-600 dark:text-neutral-400 max-w-md mx-auto">Pilih format yang paling cocok, atau kombinasikan sesuai kebutuhanmu.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-red-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-red-500/10 border-red-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-swords" class="size-5 text-red-500 dark:text-red-400" />
              </div>
              <div>
                <h3 class="font-bold text-neutral-900 dark:text-white mb-1.5">Single Elimination</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Kalah sekali, langsung gugur. Format paling cepat dan mudah diikuti penonton. Cocok untuk turnamen dengan banyak peserta.</p>
              </div>
            </div>
          </div>

          <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-orange-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-orange-500/10 border-orange-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-repeat-2" class="size-5 text-orange-500 dark:text-orange-400" />
              </div>
              <div>
                <h3 class="font-bold text-neutral-900 dark:text-white mb-1.5">Double Elimination</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Ada second chance. Tim yang kalah di upper bracket masih bisa comeback lewat lower bracket. Lebih adil, lebih seru.</p>
              </div>
            </div>
          </div>

          <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-indigo-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-indigo-500/10 border-indigo-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-table-properties" class="size-5 text-indigo-500 dark:text-indigo-400" />
              </div>
              <div>
                <h3 class="font-bold text-neutral-900 dark:text-white mb-1.5">Round Robin</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Semua ketemu semua dalam satu grup. Klasemen ditentukan dari poin — tidak ada yang langsung gugur di awal.</p>
              </div>
            </div>
          </div>

          <div class="group bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-2xl p-6 hover:border-purple-500/30 dark:hover:border-white/10 transition-all duration-200 shadow-sm">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-purple-500/10 border-purple-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-shuffle" class="size-5 text-purple-500 dark:text-purple-400" />
              </div>
              <div>
                <h3 class="font-bold text-neutral-900 dark:text-white mb-1.5">Swiss System</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Tiap ronde, kamu lawan tim dengan rekor yang mirip. Tidak ada yang ketemu lawan terlalu kuat atau terlalu lemah di awal.</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ─── CTA BOTTOM ─── -->
    <section class="relative z-10 border-t border-neutral-100 dark:border-white/5">
      <div class="max-w-6xl mx-auto px-6 py-24 text-center">
        <h2 class="text-3xl md:text-4xl font-black text-neutral-900 dark:text-white mb-4">
          Siap bikin turnamen pertamamu?
        </h2>
        <p class="text-neutral-600 dark:text-neutral-400 mb-8 max-w-md mx-auto">
          Gratis untuk memulai. Tidak perlu install apapun.
        </p>
        <NuxtLink to="/register">
          <UButton size="lg" icon="i-lucide-zap" class="px-10">
            Mulai Sekarang
          </UButton>
        </NuxtLink>
      </div>
    </section>


  </div>
</template>

<script setup lang="ts">
const { user, loggedIn } = useAuth()
const tournamentStore = useTournamentStore()
const { tournaments, isLoading } = storeToRefs(tournamentStore)

const liveTournaments = computed(() => {
  return tournaments.value
    .filter(t => ['ongoing', 'live'].includes(t.status || ''))
    .slice(0, 3)
})

onMounted(async () => {
  if (loggedIn.value && user.value) {
    const isAdmin = user.value.roles?.some((role: string) => ['admin', 'super_admin'].includes(role))
    if (isAdmin) {
      navigateTo('/admin/dashboard')
    } else {
      navigateTo('/dashboard')
    }
    return
  }

  // Only fetch if not logged in (showing landing page)
  await tournamentStore.fetchTournaments()
})
</script>

<style scoped>
.hero-item {
  opacity: 0;
  animation: fadeUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(14px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
