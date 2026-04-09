<template>
  <div class="min-h-screen bg-neutral-950 text-white">

    <!-- Subtle background glow -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[60rem] h-[30rem] bg-primary-500/6 rounded-full blur-[120px]" />
    </div>

    <!-- ─── NAVBAR ─── -->
    <header class="relative z-20 border-b border-white/5">
      <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <UIcon name="i-lucide-trophy" class="size-5 text-primary-400" />
          <span class="font-semibold text-white tracking-tight">Juara League</span>
        </div>
        <nav class="hidden md:flex items-center gap-8">
          <a href="#fitur" class="text-sm text-neutral-400 hover:text-white transition-colors">Fitur</a>
          <a href="#turnamen" class="text-sm text-neutral-400 hover:text-white transition-colors">Turnamen</a>
          <a href="#format" class="text-sm text-neutral-400 hover:text-white transition-colors">Format</a>
        </nav>
        <div class="flex items-center gap-3">
          <NuxtLink to="/login">
            <UButton variant="ghost" color="neutral" size="sm">Masuk</UButton>
          </NuxtLink>
          <NuxtLink to="/register">
            <UButton size="sm">Daftar Gratis</UButton>
          </NuxtLink>
        </div>
      </div>
    </header>

    <!-- ─── HERO ─── -->
    <section class="relative z-10 max-w-6xl mx-auto px-6 pt-28 pb-24 text-center">

      <!-- Badge -->
      <div class="hero-item inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 text-sm text-neutral-400 mb-10">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75" />
          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-400" />
        </span>
        Platform manajemen turnamen olahraga
      </div>

      <!-- Headline -->
      <h1 class="hero-item text-5xl md:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.1] mb-6" style="animation-delay:.08s">
        Atur turnamen<br />
        <span class="text-primary-400">tanpa ribet.</span>
      </h1>

      <!-- Sub -->
      <p class="hero-item text-lg md:text-xl text-neutral-400 max-w-xl mx-auto leading-relaxed mb-10" style="animation-delay:.16s">
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
      <p class="hero-item mt-8 text-sm text-neutral-600" style="animation-delay:.32s">
        Sudah dipakai untuk ratusan turnamen lokal di Indonesia
      </p>
    </section>

    <!-- ─── DIVIDER ─── -->
    <div class="max-w-6xl mx-auto px-6">
      <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent" />
    </div>

    <!-- ─── FITUR ─── -->
    <section id="fitur" class="relative z-10 max-w-6xl mx-auto px-6 py-24">
      <div class="text-center mb-16">
        <p class="text-sm text-primary-400 font-medium uppercase tracking-widest mb-3">Fitur</p>
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Semua yang kamu butuhkan</h2>
        <p class="text-neutral-400 max-w-lg mx-auto">Dirancang untuk organizer yang mau fokus ke turnamen, bukan ngurusin teknis.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
          <div class="w-10 h-10 rounded-xl border bg-primary-500/10 border-primary-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-git-branch" class="size-5 text-primary-400" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">Multi-format Bracket</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Single/Double Elimination, Round Robin, Swiss. Kamu pilih, sistem yang urus.</p>
        </div>

        <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
          <div class="w-10 h-10 rounded-xl border bg-sky-500/10 border-sky-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-activity" class="size-5 text-sky-400" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">Bracket Publik Real-time</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Bagikan satu link ke penonton. Mereka bisa pantau hasil tanpa perlu login.</p>
        </div>

        <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
          <div class="w-10 h-10 rounded-xl border bg-emerald-500/10 border-emerald-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-users" class="size-5 text-emerald-400" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">Manajemen Tim & Peserta</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Registrasi peserta, approve tim, atur kapasitas — mudah dari dashboard.</p>
        </div>

        <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
          <div class="w-10 h-10 rounded-xl border bg-amber-500/10 border-amber-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-shield-check" class="size-5 text-amber-400" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">Akses Berbasis Role</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Owner, wasit, kapten tim — masing-masing punya hak akses yang pas.</p>
        </div>

        <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
          <div class="w-10 h-10 rounded-xl border bg-purple-500/10 border-purple-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-layers" class="size-5 text-purple-400" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">Multi-stage Turnamen</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Fase grup → knockout dalam satu turnamen. Fleksibel sesuai kebutuhan.</p>
        </div>

        <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
          <div class="w-10 h-10 rounded-xl border bg-rose-500/10 border-rose-500/20 flex items-center justify-center mb-4">
            <UIcon name="i-lucide-smartphone" class="size-5 text-rose-400" />
          </div>
          <h3 class="text-base font-semibold text-white mb-1.5">Mobile-friendly</h3>
          <p class="text-sm text-neutral-500 leading-relaxed">Works di HP, tablet, laptop. Pantau turnamen dari mana aja.</p>
        </div>

      </div>
    </section>

    <!-- ─── TURNAMEN AKTIF ─── -->
    <section id="turnamen" class="relative z-10 max-w-6xl mx-auto px-6 pb-24">

      <div class="flex items-end justify-between mb-10">
        <div>
          <p class="text-sm text-primary-400 font-medium uppercase tracking-widest mb-2">Live Sekarang</p>
          <h2 class="text-2xl md:text-3xl font-bold text-white">Turnamen yang sedang jalan</h2>
        </div>
        <UButton color="neutral" variant="ghost" size="sm" trailing-icon="i-lucide-arrow-right" class="hidden sm:flex">
          Lihat Semua
        </UButton>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <!-- Card 1 -->
        <NuxtLink to="/demo-bracket" class="group bg-neutral-900 border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition-all duration-200 text-left block">
          <div class="h-28 bg-gradient-to-br from-indigo-950 to-neutral-900 relative overflow-hidden">
            <img
              src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=600&auto=format&fit=crop"
              class="w-full h-full object-cover opacity-30 mix-blend-luminosity"
              alt="esports"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 via-neutral-900/60 to-transparent" />
            <span class="absolute top-3 left-3 text-xs bg-red-500 text-white px-2.5 py-0.5 rounded-full font-medium flex items-center gap-1.5 w-fit">
              <span class="size-1.5 rounded-full bg-white animate-pulse" />
              Live
            </span>
          </div>
          <div class="p-5">
            <h3 class="font-semibold text-white mb-1 group-hover:text-primary-400 transition-colors">Valorant Community Cup S4</h3>
            <p class="text-xs text-neutral-500 mb-4">Semi Final · Single Elimination</p>
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-3 text-neutral-400">
                <span class="flex items-center gap-1"><UIcon name="i-lucide-users" class="size-3.5" /> 32 Tim</span>
                <span class="flex items-center gap-1 text-emerald-400"><UIcon name="i-lucide-banknote" class="size-3.5" /> 10jt</span>
              </div>
              <UIcon name="i-lucide-chevron-right" class="size-4 text-neutral-600 group-hover:text-primary-400 transition-colors" />
            </div>
          </div>
        </NuxtLink>

        <!-- Card 2 -->
        <NuxtLink to="/demo-bracket" class="group bg-neutral-900 border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition-all duration-200 text-left block">
          <div class="h-28 bg-gradient-to-br from-sky-950 to-neutral-900 relative overflow-hidden">
            <img
              src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?q=80&w=600&auto=format&fit=crop"
              class="w-full h-full object-cover opacity-30 mix-blend-luminosity"
              alt="futsal"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 via-neutral-900/60 to-transparent" />
            <span class="absolute top-3 left-3 text-xs bg-amber-500 text-white px-2.5 py-0.5 rounded-full font-medium w-fit">
              Group Stage
            </span>
          </div>
          <div class="p-5">
            <h3 class="font-semibold text-white mb-1 group-hover:text-sky-400 transition-colors">Tarkam Futsal League 2026</h3>
            <p class="text-xs text-neutral-500 mb-4">Fase Grup · Round Robin</p>
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-3 text-neutral-400">
                <span class="flex items-center gap-1"><UIcon name="i-lucide-users" class="size-3.5" /> 16 Klub</span>
                <span class="flex items-center gap-1 text-emerald-400"><UIcon name="i-lucide-banknote" class="size-3.5" /> 5.5jt</span>
              </div>
              <UIcon name="i-lucide-chevron-right" class="size-4 text-neutral-600 group-hover:text-sky-400 transition-colors" />
            </div>
          </div>
        </NuxtLink>

        <!-- Card 3 -->
        <NuxtLink to="/demo-bracket" class="group bg-neutral-900 border border-white/5 rounded-2xl overflow-hidden hover:border-white/10 transition-all duration-200 text-left hidden lg:block">
          <div class="h-28 bg-gradient-to-br from-emerald-950 to-neutral-900 relative overflow-hidden">
            <img
              src="https://images.unsplash.com/photo-1550989460-0adf9ea622e2?q=80&w=600&auto=format&fit=crop"
              class="w-full h-full object-cover opacity-30 mix-blend-luminosity"
              alt="basket"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 via-neutral-900/60 to-transparent" />
            <span class="absolute top-3 left-3 text-xs bg-red-500 text-white px-2.5 py-0.5 rounded-full font-medium flex items-center gap-1.5 w-fit">
              <span class="size-1.5 rounded-full bg-white animate-pulse" />
              Live
            </span>
          </div>
          <div class="p-5">
            <h3 class="font-semibold text-white mb-1 group-hover:text-emerald-400 transition-colors">SSB U-15 National Cup</h3>
            <p class="text-xs text-neutral-500 mb-4">Quarter Final · Double Elimination</p>
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-3 text-neutral-400">
                <span class="flex items-center gap-1"><UIcon name="i-lucide-users" class="size-3.5" /> 64 SSB</span>
                <span class="flex items-center gap-1 text-emerald-400"><UIcon name="i-lucide-trophy" class="size-3.5" /> Piala Presiden</span>
              </div>
              <UIcon name="i-lucide-chevron-right" class="size-4 text-neutral-600 group-hover:text-emerald-400 transition-colors" />
            </div>
          </div>
        </NuxtLink>

      </div>
    </section>

    <!-- ─── FORMAT ─── -->
    <section id="format" class="relative z-10 border-t border-white/5">
      <div class="max-w-6xl mx-auto px-6 py-24">

        <div class="text-center mb-16">
          <p class="text-sm text-primary-400 font-medium uppercase tracking-widest mb-3">Format</p>
          <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Format turnamen yang tersedia</h2>
          <p class="text-neutral-400 max-w-md mx-auto">Pilih format yang paling cocok, atau kombinasikan sesuai kebutuhanmu.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-red-500/10 border-red-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-swords" class="size-5 text-red-400" />
              </div>
              <div>
                <h3 class="font-semibold text-white mb-1.5">Single Elimination</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Kalah sekali, langsung gugur. Format paling cepat dan mudah diikuti penonton. Cocok untuk turnamen dengan banyak peserta.</p>
              </div>
            </div>
          </div>

          <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-orange-500/10 border-orange-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-repeat-2" class="size-5 text-orange-400" />
              </div>
              <div>
                <h3 class="font-semibold text-white mb-1.5">Double Elimination</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Ada second chance. Tim yang kalah di upper bracket masih bisa comeback lewat lower bracket. Lebih adil, lebih seru.</p>
              </div>
            </div>
          </div>

          <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-indigo-500/10 border-indigo-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-table-properties" class="size-5 text-indigo-400" />
              </div>
              <div>
                <h3 class="font-semibold text-white mb-1.5">Round Robin</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Semua ketemu semua dalam satu grup. Klasemen ditentukan dari poin — tidak ada yang langsung gugur di awal.</p>
              </div>
            </div>
          </div>

          <div class="group bg-neutral-900 border border-white/5 rounded-2xl p-6 hover:border-white/10 transition-all duration-200">
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl border bg-purple-500/10 border-purple-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-shuffle" class="size-5 text-purple-400" />
              </div>
              <div>
                <h3 class="font-semibold text-white mb-1.5">Swiss System</h3>
                <p class="text-sm text-neutral-500 leading-relaxed">Tiap ronde, kamu lawan tim dengan rekor yang mirip. Tidak ada yang ketemu lawan terlalu kuat atau terlalu lemah di awal.</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ─── CTA BOTTOM ─── -->
    <section class="relative z-10 border-t border-white/5">
      <div class="max-w-6xl mx-auto px-6 py-24 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
          Siap bikin turnamen pertamamu?
        </h2>
        <p class="text-neutral-400 mb-8 max-w-md mx-auto">
          Gratis untuk memulai. Tidak perlu install apapun.
        </p>
        <NuxtLink to="/register">
          <UButton size="lg" icon="i-lucide-zap" class="px-10">
            Mulai Sekarang
          </UButton>
        </NuxtLink>
      </div>
    </section>

    <!-- ─── FOOTER ─── -->
    <footer class="border-t border-white/5">
      <div class="max-w-6xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <UIcon name="i-lucide-trophy" class="size-4 text-primary-400" />
          <span class="text-sm font-medium text-white">Juara League</span>
        </div>
        <p class="text-xs text-neutral-600">© 2026 Juara League. Dibuat dengan ❤️ untuk komunitas olahraga Indonesia.</p>
      </div>
    </footer>

  </div>
</template>

<script setup lang="ts">
const { user, loggedIn } = useAuth()

onMounted(() => {
  if (loggedIn.value && user.value) {
    const isAdmin = user.value.roles?.some((role: string) => ['admin', 'super_admin'].includes(role))
    if (isAdmin) {
      navigateTo('/admin/dashboard')
    } else {
      navigateTo('/dashboard')
    }
  }
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
