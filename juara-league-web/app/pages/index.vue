<template>
  <div class="relative min-h-screen overflow-hidden bg-neutral-950">
    <!-- Ambient Background Effects -->
    <div class="absolute inset-0 z-0 pointer-events-none">
      <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-amber-500/10 rounded-full mix-blend-screen filter blur-[100px] animate-pulse glow-slow"></div>
      <div class="absolute bottom-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-blue-600/10 rounded-full mix-blend-screen filter blur-[120px] animate-pulse glow-fast"></div>
      <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-20"></div>
    </div>

    <!-- Main Hero Content -->
    <div class="relative z-10 pt-32 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-center flex flex-col items-center">
      
      <!-- Animated Icon Centerpiece -->
      <div class="relative inline-block mb-10 group cursor-pointer animate-fade-in-up">
        <!-- Pulse ring behind -->
        <div class="absolute inset-0 bg-primary-500/30 rounded-full blur-xl group-hover:bg-primary-500/60 transition-all duration-700 animate-pulse"></div>
        <!-- Glass Icon Container -->
        <div class="relative bg-neutral-900/60 backdrop-blur-xl border border-white/10 p-6 rounded-3xl shadow-2xl shadow-primary-900/20 transform group-hover:scale-110 group-hover:-translate-y-2 group-hover:rotate-3 transition-all duration-500">
          <UIcon name="i-lucide-trophy" class="size-20 text-primary-400 group-hover:text-primary-300 transition-colors drop-shadow-md" />
        </div>
      </div>

      <!-- Hero Headline -->
      <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-neutral-100 to-neutral-500 mb-8 animate-fade-in-up" style="animation-delay: 0.1s;">
        Masa Depan Manajemen <br/>
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-primary-600 drop-shadow-sm">
          Turnamen Olahraga
        </span>
      </h1>
      
      <!-- Subheadline -->
      <p class="text-xl md:text-2xl text-neutral-400 mb-14 max-w-3xl font-light leading-relaxed animate-fade-in-up" style="animation-delay: 0.2s;">
        Platform fleksibel untuk merangkai kompetisi dari format <strong class="text-white font-medium">Single Elimination</strong> hingga <strong class="text-white font-medium">Swiss System</strong>. Diciptakan dengan bracket publik real-time, bebas hambatan.
      </p>
      
      <!-- Call To Actions -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-6 w-full animate-fade-in-up" style="animation-delay: 0.3s;">
        <UButton 
          size="xl" 
          color="primary" 
          icon="i-lucide-zap"
          class="w-full sm:w-auto px-10 py-5 text-lg font-bold shadow-xl shadow-primary-500/20 hover:shadow-primary-500/40 hover:scale-105 transition-all duration-300 ring-1 ring-primary-500/50"
        >
          Buat Turnamen
        </UButton>
        <UButton 
          size="xl" 
          color="neutral" 
          variant="ghost" 
          icon="i-lucide-layout-grid"
          class="w-full sm:w-auto px-10 py-5 text-lg font-bold hover:bg-white/5 hover:text-white transition-all duration-300 ring-1 ring-white/10 hover:ring-white/30"
        >
          Lihat Contoh Bracket
        </UButton>
      </div>

      <!-- Futuristic Glass Features Grid -->
      <div class="mt-40 grid grid-cols-1 md:grid-cols-3 gap-8 text-left animate-fade-in-up" style="animation-delay: 0.5s;">
        
        <!-- Feature 1: Multi-format -->
        <div class="group relative bg-neutral-900/30 backdrop-blur-2xl border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/50 hover:border-primary-500/40 transition-all duration-700 hover:-translate-y-3 hover:shadow-2xl hover:shadow-primary-900/30 overflow-hidden">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-700"></div>
          <div class="bg-primary-500/10 w-16 h-16 rounded-2xl flex items-center justify-center mb-8 relative border border-primary-500/20 group-hover:scale-110 group-hover:bg-primary-500/20 transition-all duration-500">
            <UIcon name="i-lucide-git-branch" class="size-8 text-primary-400 drop-shadow-sm" />
          </div>
          <h3 class="text-2xl font-bold text-white mb-4 relative">Multi-format Engine</h3>
          <p class="text-neutral-400 font-light leading-relaxed relative">Dukung Single/Double Elimination, Round Robin, dan kombinasi format kompleks tanpa perlu software desktop usang.</p>
        </div>
        
        <!-- Feature 2: Public Realtime -->
        <div class="group relative bg-neutral-900/30 backdrop-blur-2xl border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/50 hover:border-sky-500/40 transition-all duration-700 hover:-translate-y-3 hover:shadow-2xl hover:shadow-sky-900/30 overflow-hidden">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl group-hover:bg-sky-500/20 transition-all duration-700"></div>
          <div class="bg-sky-500/10 w-16 h-16 rounded-2xl flex items-center justify-center mb-8 relative border border-sky-500/20 group-hover:scale-110 group-hover:bg-sky-500/20 transition-all duration-500">
            <UIcon name="i-lucide-activity" class="size-8 text-sky-400 drop-shadow-sm" />
          </div>
          <h3 class="text-2xl font-bold text-white mb-4 relative">Public Live Updates</h3>
          <p class="text-neutral-400 font-light leading-relaxed relative">Penonton dapat menikmati hasil bracket secara *real-time* lewat satu URL publik responsif, tanpa perlu login aplikasi.</p>
        </div>

        <!-- Feature 3: Roles & Access -->
        <div class="group relative bg-neutral-900/30 backdrop-blur-2xl border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/50 hover:border-emerald-500/40 transition-all duration-700 hover:-translate-y-3 hover:shadow-2xl hover:shadow-emerald-900/30 overflow-hidden">
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all duration-700"></div>
          <div class="bg-emerald-500/10 w-16 h-16 rounded-2xl flex items-center justify-center mb-8 relative border border-emerald-500/20 group-hover:scale-110 group-hover:bg-emerald-500/20 transition-all duration-500">
            <UIcon name="i-lucide-shield-check" class="size-8 text-emerald-400 drop-shadow-sm" />
          </div>
          <h3 class="text-2xl font-bold text-white mb-4 relative">Hierarki Role Ketat</h3>
          <p class="text-neutral-400 font-light leading-relaxed relative">Kendali akses mendalam untuk tingkat Owner, Event Organizer, Wasit lapangan, hingga peran manajerial Kapten tim.</p>
        </div>

      </div>

      <!-- NEW SECTION: Turnamen Publik -->
      <section id="turnamen" class="mt-40 w-full animate-fade-in-up" style="animation-delay: 0.5s;">
        <div class="flex flex-col sm:flex-row items-center justify-between mb-12">
          <div class="text-left">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-2 flex items-center gap-3">
              <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
              </span>
              Sedang Berlangsung
            </h2>
            <p class="text-neutral-400 font-light">Pantau hasil turnamen publik yang sedang panas-panasnya saat ini.</p>
          </div>
          <UButton color="neutral" variant="ghost" trailing-icon="i-lucide-arrow-right" class="mt-4 sm:mt-0 hover:text-white">Lihat Semua</UButton>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          
          <!-- Card 1 -->
          <NuxtLink to="/demo-bracket" class="group relative bg-neutral-900 border border-white/5 rounded-2xl overflow-hidden hover:border-primary-500/40 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-primary-900/20 text-left">
            <div class="h-32 bg-gradient-to-br from-indigo-900 via-neutral-900 to-black relative">
              <img src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover opacity-40 mix-blend-overlay" alt="esports" />
              <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 to-transparent"></div>
              <UIcon name="i-lucide-gamepad-2" class="absolute top-4 right-4 text-white/50 size-6" />
            </div>
            <div class="p-6">
              <div class="flex justify-between items-start mb-4">
                <h3 class="text-xl font-bold text-white leading-tight group-hover:text-primary-400 transition-colors">Valorant Community Cup S4</h3>
              </div>
              <div class="flex items-center gap-4 text-sm text-neutral-400 mb-6 font-medium">
                <div class="flex items-center gap-1.5"><UIcon name="i-lucide-users" class="size-4" /> 32/32 Tim</div>
                <div class="flex items-center gap-1.5"><UIcon name="i-lucide-banknote" class="size-4 text-emerald-400" /> Rp 10.000.000</div>
              </div>
              <div class="border-t border-white/5 pt-4 flex items-center justify-between">
                <span class="text-xs bg-red-500/20 text-red-400 px-2.5 py-1 rounded font-bold uppercase tracking-wider">Live &middot; Semi Final</span>
                <span class="text-primary-400 text-sm font-medium flex items-center gap-1 group-hover:translate-x-1 transition-transform">Lihat Bracket <UIcon name="i-lucide-chevron-right" /></span>
              </div>
            </div>
          </NuxtLink>

          <!-- Card 2 -->
          <NuxtLink to="/demo-bracket" class="group relative bg-neutral-900 border border-white/5 rounded-2xl overflow-hidden hover:border-sky-500/40 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-sky-900/20 text-left">
            <div class="h-32 bg-gradient-to-br from-sky-900 via-neutral-900 to-black relative">
              <img src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover opacity-40 mix-blend-overlay" alt="futsal" />
              <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 to-transparent"></div>
              <UIcon name="i-lucide-goal" class="absolute top-4 right-4 text-white/50 size-6" />
            </div>
            <div class="p-6">
              <h3 class="text-xl font-bold text-white mb-4 leading-tight group-hover:text-sky-400 transition-colors">Tarkam Futsal League 2026</h3>
              <div class="flex items-center gap-4 text-sm text-neutral-400 mb-6 font-medium">
                <div class="flex items-center gap-1.5"><UIcon name="i-lucide-users" class="size-4" /> 16/16 Klub</div>
                <div class="flex items-center gap-1.5"><UIcon name="i-lucide-banknote" class="size-4 text-emerald-400" /> Rp 5.500.000</div>
              </div>
              <div class="border-t border-white/5 pt-4 flex items-center justify-between">
                <span class="text-xs bg-amber-500/20 text-amber-400 px-2.5 py-1 rounded font-bold uppercase tracking-wider">Group Stage</span>
                <span class="text-sky-400 text-sm font-medium flex items-center gap-1 group-hover:translate-x-1 transition-transform">Klasemen <UIcon name="i-lucide-chevron-right" /></span>
              </div>
            </div>
          </NuxtLink>

          <!-- Card 3 -->
          <NuxtLink to="/demo-bracket" class="group relative bg-neutral-900 border border-white/5 rounded-2xl overflow-hidden hover:border-emerald-500/40 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-900/20 text-left hidden lg:block">
            <div class="h-32 bg-gradient-to-br from-emerald-900 via-neutral-900 to-black relative">
              <img src="https://images.unsplash.com/photo-1550989460-0adf9ea622e2?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover opacity-40 mix-blend-overlay" alt="basket" />
              <div class="absolute inset-0 bg-gradient-to-t from-neutral-900 to-transparent"></div>
              <UIcon name="i-lucide-dribbble" class="absolute top-4 right-4 text-white/50 size-6" />
            </div>
            <div class="p-6">
              <h3 class="text-xl font-bold text-white mb-4 leading-tight group-hover:text-emerald-400 transition-colors">SSB U-15 National Cup</h3>
              <div class="flex items-center gap-4 text-sm text-neutral-400 mb-6 font-medium">
                <div class="flex items-center gap-1.5"><UIcon name="i-lucide-users" class="size-4" /> 64/64 SSB</div>
                <div class="flex items-center gap-1.5"><UIcon name="i-lucide-banknote" class="size-4 text-emerald-400" /> Piala Presiden</div>
              </div>
              <div class="border-t border-white/5 pt-4 flex items-center justify-between">
                <span class="text-xs bg-red-500/20 text-red-400 px-2.5 py-1 rounded font-bold uppercase tracking-wider">Live &middot; Quarter Final</span>
                <span class="text-emerald-400 text-sm font-medium flex items-center gap-1 group-hover:translate-x-1 transition-transform">Lihat Bracket <UIcon name="i-lucide-chevron-right" /></span>
              </div>
            </div>
          </NuxtLink>

        </div>
      </section>

      <!-- NEW SECTION: Formats -->
      <section id="format" class="mt-40 mb-20 w-full animate-fade-in-up" style="animation-delay: 0.6s;">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">Sistem Format Kelas Dunia</h2>
          <p class="text-xl text-neutral-400 max-w-3xl mx-auto font-light leading-relaxed">
            Algoritma sistem klasemen kami dirancang dengan sangat mendetail layaknya standar global. Semua format turnamen ada di genggaman Anda.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
          
          <!-- Single -->
          <div class="bg-neutral-900/40 border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/80 transition-all duration-300 hover:shadow-2xl hover:shadow-red-900/10 group">
            <div class="flex items-center gap-4 mb-4">
              <div class="bg-red-500/10 p-3 rounded-xl border border-red-500/20 group-hover:scale-110 transition-transform">
                <UIcon name="i-lucide-swords" class="size-6 text-red-400" />
              </div>
              <h3 class="text-2xl font-bold text-white">Single Elimination</h3>
            </div>
            <p class="text-neutral-400 font-light leading-relaxed mb-4">Sistem gugur konvensional. Tim yang memenangkan <code class="text-primary-300 bg-primary-950/50 px-2 py-0.5 rounded text-xs">Match</code> akan secara otomatis melaju ke ronde berikutnya dengan format set (<i>Best of X</i>).</p>
          </div>

          <!-- Double -->
          <div class="bg-neutral-900/40 border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/80 transition-all duration-300 hover:shadow-2xl hover:shadow-orange-900/10 group">
            <div class="flex items-center gap-4 mb-4">
              <div class="bg-orange-500/10 p-3 rounded-xl border border-orange-500/20 group-hover:scale-110 transition-transform">
                <UIcon name="i-lucide-repeat-2" class="size-6 text-orange-400" />
              </div>
              <h3 class="text-2xl font-bold text-white">Double Elimination</h3>
            </div>
            <p class="text-neutral-400 font-light leading-relaxed mb-4">Setiap peserta punya dua "nyawa". Tim yang gugur di <i>Upper Bracket</i> dikirim ke <i>Lower Bracket</i>, menampung kesempatan kedua untuk meraih Grand Final.</p>
          </div>

          <!-- Round Robin -->
          <div class="bg-neutral-900/40 border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/80 transition-all duration-300 hover:shadow-2xl hover:shadow-indigo-900/10 group">
            <div class="flex items-center gap-4 mb-4">
              <div class="bg-indigo-500/10 p-3 rounded-xl border border-indigo-500/20 group-hover:scale-110 transition-transform">
                <UIcon name="i-lucide-table-properties" class="size-6 text-indigo-400" />
              </div>
              <h3 class="text-2xl font-bold text-white">Round Robin</h3>
            </div>
            <p class="text-neutral-400 font-light leading-relaxed mb-4">Pembagian murni ala Fase Grup. Seluruh <code class="text-primary-300 bg-primary-950/50 px-2 py-0.5 rounded text-xs">Participant</code> dalam grup saling bertemu. Poin agregat menjadi metrik penentu wakil yang lolos ke *Playoff*.</p>
          </div>

          <!-- Swiss -->
          <div class="bg-neutral-900/40 border border-white/5 p-8 rounded-[2rem] hover:bg-neutral-800/80 transition-all duration-300 hover:shadow-2xl hover:shadow-purple-900/10 group">
            <div class="flex items-center gap-4 mb-4">
              <div class="bg-purple-500/10 p-3 rounded-xl border border-purple-500/20 group-hover:scale-110 transition-transform">
                <UIcon name="i-lucide-settings-2" class="size-6 text-purple-400" />
              </div>
              <h3 class="text-2xl font-bold text-white">Swiss System</h3>
            </div>
            <p class="text-neutral-400 font-light leading-relaxed mb-4">Format dinamis bebas penyisihan awal. Algoritma otomatis *matchmaking* mencari lawan tanding yang seimbang (W/L) di setiap rondenya secara adil.</p>
          </div>

        </div>
      </section>

    </div>
  </div>
</template>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  opacity: 0;
  animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.glow-slow {
  animation: glow 8s ease-in-out infinite alternate;
}
.glow-fast {
  animation: glow 6s ease-in-out infinite alternate-reverse;
}

@keyframes glow {
  0% { transform: scale(1) translate(0px, 0px); opacity: 0.1; }
  100% { transform: scale(1.1) translate(20px, 20px); opacity: 0.15; }
}
</style>
