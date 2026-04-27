<script setup lang="ts">
const { user, loggedIn, logout, fetchUser } = useAuth()
const { isRegistrationEnabled } = useSettings()
const isLogoutModalOpen = ref(false)

const handleLogout = async () => {
  isLogoutModalOpen.value = false
  await logout()
  navigateTo('/login')
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-transparent text-neutral-900 dark:text-white transition-colors duration-500">
    <!-- Logout Confirmation Modal -->
    <UModal v-model:open="isLogoutModalOpen">
      <template #content>
        <div class="p-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-3xl shadow-2xl relative overflow-hidden">
          <!-- Decoration -->
          <div class="absolute -top-12 -right-12 w-24 h-24 bg-primary-500/10 blur-2xl rounded-full"></div>
          
          <div class="flex flex-col items-center text-center relative z-10">
            <div class="bg-red-500/10 p-4 rounded-2xl mb-6 ring-1 ring-red-500/20">
              <UIcon name="i-lucide-log-out" class="text-red-400 size-8" />
            </div>
            
            <h3 class="text-2xl font-black text-neutral-900 dark:text-white mb-3">Sesi Berakhir?</h3>
            <p class="text-neutral-500 dark:text-neutral-400 font-medium mb-8 max-w-[280px]">
              Apakah Anda yakin ingin keluar dari Juara League? Sesi Anda akan berakhir.
            </p>
            
            <div class="grid grid-cols-2 gap-4 w-full">
              <UButton 
                variant="ghost" 
                color="neutral" 
                size="lg" 
                class="font-bold border border-neutral-200 dark:border-white/5 hover:bg-neutral-100 dark:hover:bg-white/5 rounded-xl transition-all"
                @click="isLogoutModalOpen = false"
              >
                Batal
              </UButton>
              <UButton 
                color="primary" 
                size="lg" 
                class="font-black rounded-xl shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40 transition-all"
                @click="handleLogout"
              >
                Keluar
              </UButton>
            </div>
          </div>
        </div>
      </template>
    </UModal>

    <!-- Navbar / App Bar -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/70 dark:bg-neutral-950/70 backdrop-blur-xl border-b border-neutral-200 dark:border-white/5 h-20 flex items-center transition-all duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex justify-between items-center">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-3 cursor-pointer group">
          <div class="bg-primary-500/10 p-2 rounded-xl group-hover:bg-primary-500/20 transition-colors">
            <UIcon name="i-lucide-trophy" class="text-primary-500 dark:text-primary-400 size-6" />
          </div>
          <span class="font-extrabold text-2xl tracking-tight text-neutral-900 dark:text-white drop-shadow-sm">Juara<span class="text-primary-500 dark:text-primary-400">League</span></span>
        </NuxtLink>
        
        <!-- Navigation -->
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-neutral-600 dark:text-neutral-300">
          <NuxtLink to="/" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Beranda</NuxtLink>
          <NuxtLink v-if="loggedIn" to="/dashboard" class="text-primary-500 dark:text-primary-400 font-bold hover:text-primary-600 dark:hover:text-primary-500 transition-colors">Dashboard</NuxtLink>
          <NuxtLink to="/tournaments" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Semua Turnamen</NuxtLink>
          <NuxtLink to="/demo-bracket" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Demo Bracket</NuxtLink>
        </nav>
        
        <!-- Auth Buttons -->
        <ClientOnly>
          <div class="flex items-center gap-4">
            <template v-if="loggedIn">
              <div class="flex items-center gap-4">
                <ColorModeToggle />
                <div class="hidden sm:flex flex-col items-end mr-2">
                  <span class="text-sm font-bold text-neutral-900 dark:text-white leading-none">{{ user?.name }}</span>
                  <span class="text-[10px] text-neutral-500 uppercase tracking-wider font-bold">Organizer</span>
                </div>
                <UDropdownMenu :items="[
                  { label: user?.name, slot: 'header', disabled: true },
                  { label: 'Dashboard', icon: 'i-lucide-layout-dashboard', to: '/dashboard' },
                  { label: 'Turnamen Saya', icon: 'i-lucide-trophy', to: '/dashboard/tournaments' },
                  { label: 'Buat Turnamen', icon: 'i-lucide-plus-circle', to: '/dashboard/tournaments/create' },
                  { label: 'Setting Profil', icon: 'i-lucide-user', to: '/dashboard/settings' },
                  { label: 'Keluar', icon: 'i-lucide-log-out', onSelect: () => isLogoutModalOpen = true }
                ]">
                  <UAvatar 
                    :src="user?.avatar || `https://i.pravatar.cc/150?u=${user?.email}`" 
                    :alt="user?.name" 
                    size="sm"
                    class="ring-2 ring-primary-500/50 cursor-pointer"
                  />
                </UDropdownMenu>
              </div>
            </template>
            <template v-else>
              <ColorModeToggle />
              <NuxtLink to="/login">
                <UButton variant="ghost" color="neutral" class="hidden sm:inline-flex hover:bg-neutral-100 dark:hover:bg-white/5 ring-1 ring-neutral-200 dark:ring-white/10 hover:ring-neutral-300 dark:hover:ring-white/20">Sign In</UButton>
              </NuxtLink>
              <NuxtLink v-if="isRegistrationEnabled" to="/register">
                <UButton color="primary" class="font-semibold px-6 shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40">Register</UButton>
              </NuxtLink>
            </template>
          </div>
        </ClientOnly>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-neutral-950 border-t border-neutral-200 dark:border-white/5 py-16 relative z-10 w-full text-left">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
          <!-- Brand -->
          <div class="col-span-1 md:col-span-2">
            <div class="flex items-center gap-2 font-bold text-2xl tracking-tight mb-6">
              <UIcon name="i-lucide-trophy" class="text-primary-500 dark:text-primary-400 size-6" />
              <span class="text-neutral-900 dark:text-white">Juara<span class="text-primary-500 dark:text-primary-400">League</span></span>
            </div>
            <p class="text-neutral-400 text-base max-w-sm leading-relaxed mb-6">
              Platform turnamen olahraga terlengkap di Indonesia. Mewujudkan era kompetisi modern dengan eksekusi <i>Zero-hassle</i>.
            </p>
            <div class="flex gap-4">
              <UButton icon="i-lucide-twitter" color="neutral" variant="ghost" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white" />
              <UButton icon="i-lucide-instagram" color="neutral" variant="ghost" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white" />
              <UButton icon="i-lucide-github" color="neutral" variant="ghost" class="text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white" />
            </div>
          </div>
          
          <!-- Links 1 -->
          <div>
            <h4 class="text-neutral-900 dark:text-white font-semibold text-lg mb-6">Sistem Format</h4>
            <ul class="text-neutral-500 dark:text-neutral-400 text-sm space-y-4">
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Single Elimination</a></li>
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Double Elimination</a></li>
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Round Robin</a></li>
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Swiss System</a></li>
            </ul>
          </div>
          
          <!-- Links 2 -->
          <div>
            <h4 class="text-neutral-900 dark:text-white font-semibold text-lg mb-6">Dukungan</h4>
            <ul class="text-neutral-500 dark:text-neutral-400 text-sm space-y-4">
              <li><NuxtLink to="/guide/scoring" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Panduan Scoring</NuxtLink></li>
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Dokumentasi API</a></li>
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Pusat Bantuan</a></li>
              <li><a href="#" class="hover:text-primary-500 dark:hover:text-primary-400 transition-colors">Syarat Kebijakan</a></li>
            </ul>
          </div>
        </div>
        
        <div class="border-t border-neutral-200 dark:border-white/5 mt-16 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-neutral-500">
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
