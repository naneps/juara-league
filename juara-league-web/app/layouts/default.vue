<template>
  <div class="min-h-screen flex flex-col bg-transparent text-white">
    <!-- Navbar / App Bar -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-neutral-950/70 backdrop-blur-xl border-b border-white/5 h-20 flex items-center transition-all duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex justify-between items-center">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-3 cursor-pointer group">
          <div class="bg-primary-500/10 p-2 rounded-xl group-hover:bg-primary-500/20 transition-colors">
            <UIcon name="i-lucide-trophy" class="text-primary-400 size-6" />
          </div>
          <span class="font-extrabold text-2xl tracking-tight text-white drop-shadow-sm">Juara<span class="text-primary-400">League</span></span>
        </NuxtLink>
        
        <!-- Navigation -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-300">
          <NuxtLink to="/" class="hover:text-primary-400 transition-colors">Beranda</NuxtLink>
          <NuxtLink to="#fitur" class="hover:text-primary-400 transition-colors">Fitur</NuxtLink>
          <NuxtLink to="#format" class="hover:text-primary-400 transition-colors">Sistem Format</NuxtLink>
          <NuxtLink to="#harga" class="hover:text-primary-400 transition-colors">Harga</NuxtLink>
        </nav>
        
        <!-- Auth Buttons -->
        <div class="flex items-center gap-4">
          <UButton variant="ghost" color="neutral" class="hidden sm:inline-flex hover:bg-white/5 ring-1 ring-white/10 hover:ring-white/20">Sign In</UButton>
          <UButton color="primary" class="font-semibold px-6 shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40">Register</UButton>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-neutral-950 border-t border-white/5 py-16 relative z-10 w-full text-left">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
          <!-- Brand -->
          <div class="col-span-1 md:col-span-2">
            <div class="flex items-center gap-2 font-bold text-2xl tracking-tight mb-6">
              <UIcon name="i-lucide-trophy" class="text-primary-400 size-6" />
              <span>Juara<span class="text-primary-400">League</span></span>
            </div>
            <p class="text-neutral-400 text-base max-w-sm leading-relaxed mb-6">
              Platform turnamen olahraga terlengkap di Indonesia. Mewujudkan era kompetisi modern dengan eksekusi <i>Zero-hassle</i>.
            </p>
            <div class="flex gap-4">
              <UButton icon="i-lucide-twitter" color="neutral" variant="ghost" class="text-neutral-400 hover:text-white" />
              <UButton icon="i-lucide-instagram" color="neutral" variant="ghost" class="text-neutral-400 hover:text-white" />
              <UButton icon="i-lucide-github" color="neutral" variant="ghost" class="text-neutral-400 hover:text-white" />
            </div>
          </div>
          
          <!-- Links 1 -->
          <div>
            <h4 class="text-white font-semibold text-lg mb-6">Sistem Format</h4>
            <ul class="text-neutral-400 text-sm space-y-4">
              <li><a href="#" class="hover:text-primary-400 transition-colors">Single Elimination</a></li>
              <li><a href="#" class="hover:text-primary-400 transition-colors">Double Elimination</a></li>
              <li><a href="#" class="hover:text-primary-400 transition-colors">Round Robin</a></li>
              <li><a href="#" class="hover:text-primary-400 transition-colors">Swiss System</a></li>
            </ul>
          </div>
          
          <!-- Links 2 -->
          <div>
            <h4 class="text-white font-semibold text-lg mb-6">Dukungan</h4>
            <ul class="text-neutral-400 text-sm space-y-4">
              <li><a href="#" class="hover:text-primary-400 transition-colors">Dokumentasi API</a></li>
              <li><a href="#" class="hover:text-primary-400 transition-colors">Pusat Bantuan</a></li>
              <li><a href="#" class="hover:text-primary-400 transition-colors">Syarat Kebijakan</a></li>
            </ul>
          </div>
        </div>
        
        <div class="border-t border-white/5 mt-16 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-neutral-500">
          <p>&copy; 2026 Juara League Platform. Hak cipta dilindungi undang-undang.</p>
          <div class="flex items-center gap-2 mt-4 md:mt-0">
            <UIcon name="i-lucide-globe" class="size-4" />
            <span>Indonesia</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
