<script setup lang="ts">
definePageMeta({
  layout: false
})

const title = 'Arena Sedang Dipersiapkan - Juara League'
const description = 'Kami sedang meningkatkan performa platform untuk pengalaman bertanding yang lebih gila!'

useSeoMeta({
  title,
  description
})

const socialLinks = [
  { icon: 'i-simple-icons-instagram', label: 'Instagram', to: 'https://instagram.com/juaraleague' },
  { icon: 'i-simple-icons-x', label: 'X (Twitter)', to: 'https://twitter.com/juaraleague' },
  { icon: 'i-simple-icons-discord', label: 'Discord', to: 'https://discord.gg/juaraleague' }
]
</script>

<template>
  <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#050505] text-white selection:bg-primary-500/30">
    <!-- Animated Background Gradients -->
    <div class="absolute inset-0 z-0">
      <div class="absolute top-[-20%] left-[-10%] w-[70%] h-[70%] bg-primary-900/10 rounded-full blur-[160px] animate-pulse-slow" />
      <div class="absolute bottom-[-20%] right-[-10%] w-[70%] h-[70%] bg-blue-900/5 rounded-full blur-[160px] animate-pulse-slow [animation-delay:4s]" />
      
      <!-- Floating Particles Effect -->
      <div class="absolute inset-0 opacity-20">
        <div v-for="n in 15" :key="n" 
             class="absolute rounded-full bg-white animate-float"
             :style="{
               width: Math.random() * 4 + 'px',
               height: Math.random() * 4 + 'px',
               top: Math.random() * 100 + '%',
               left: Math.random() * 100 + '%',
               animationDuration: (Math.random() * 10 + 10) + 's',
               animationDelay: (Math.random() * 5) + 's'
             }"
        />
      </div>
    </div>

    <!-- Content -->
    <div class="relative z-10 w-full max-w-3xl px-6 text-center">
      <!-- Icon Container with Glow -->
      <div class="mb-12 relative inline-block group">
        <div class="absolute inset-0 bg-primary-500/20 blur-2xl rounded-full animate-ping-slow" />
        <div class="relative flex items-center justify-center p-6 bg-white/5 rounded-3xl border border-white/10 backdrop-blur-2xl transition-transform duration-700 group-hover:scale-110">
           <UIcon name="i-heroicons-bolt" class="w-16 h-16 text-primary-500 animate-pulse" />
        </div>
      </div>

      <div class="overflow-hidden mb-6">
        <h1 class="text-5xl md:text-8xl font-black tracking-tighter leading-none animate-slide-up">
          REHAT <span class="text-primary-500">SEJENAK.</span>
        </h1>
      </div>
      
      <div class="overflow-hidden mb-10">
        <p class="text-xl md:text-3xl font-bold text-white/80 animate-slide-up [animation-delay:0.2s]">
          Arena lagi kita upgrade biar makin <span class="italic text-white">kenceng!</span>
        </p>
      </div>
      
      <p class="text-gray-400 mb-12 leading-relaxed max-w-xl mx-auto text-lg animate-fade-in [animation-delay:0.5s]">
        Sama kayak atlet pro, platform kita juga butuh waktu buat istirahat dan <i>power up</i>. Tenang aja, semua data turnamen lu aman. Kita bakal balik lagi lebih jago dari sebelumnya!
      </p>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mb-20 animate-fade-in [animation-delay:0.8s]">
        <UButton
          to="/"
          size="xl"
          color="primary"
          variant="solid"
          class="px-10 py-4 font-black rounded-2xl shadow-[0_0_40px_rgba(var(--color-primary-500),0.3)] hover:shadow-[0_0_60px_rgba(var(--color-primary-500),0.5)] transition-all duration-500 hover:scale-105 active:scale-95"
        >
          CEK KONDISI ARENA
        </UButton>

        <!-- Admin Login Button — discreet, only visible on hover -->
        <UButton
          to="/login"
          size="xl"
          color="neutral"
          variant="ghost"
          class="px-10 py-4 font-black rounded-2xl opacity-0 hover:opacity-100 focus:opacity-100 transition-all duration-500 border border-white/5 hover:border-white/20 hover:bg-white/5 text-white/30 hover:text-white/70 text-sm"
          icon="i-lucide-shield"
        >
          Admin Login
        </UButton>
      </div>

      <!-- Socials -->
      <div class="flex flex-col items-center gap-6 animate-fade-in [animation-delay:1s]">
        <span class="text-xs font-bold tracking-[0.3em] text-white/30 uppercase">Pantau Update Di Sini</span>
        <div class="flex items-center justify-center gap-4">
          <template v-for="social in socialLinks" :key="social.label">
            <ULink
              :to="social.to"
              target="_blank"
              class="group relative flex items-center justify-center size-12 bg-white/5 rounded-xl border border-white/10 hover:border-primary-500/50 hover:bg-primary-500/10 transition-all duration-300"
            >
              <UIcon :name="social.icon" class="size-5 text-gray-400 group-hover:text-primary-400 transition-colors" />
            </ULink>
          </template>
        </div>
      </div>
    </div>

    <!-- Bottom Decoration -->
    <div class="absolute bottom-12 left-0 right-0 flex justify-center opacity-20 pointer-events-none">
      <div class="text-[12rem] font-black tracking-tighter leading-none select-none">JUARA LEAGUE</div>
    </div>
  </div>
</template>

<style scoped>
@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
  0%, 100% { transform: translateY(0) translateX(0); }
  25% { transform: translateY(-20px) translateX(10px); }
  50% { transform: translateY(-10px) translateX(20px); }
  75% { transform: translateY(-30px) translateX(-10px); }
}

@keyframes ping-slow {
  0% { transform: scale(1); opacity: 0.5; }
  100% { transform: scale(1.5); opacity: 0; }
}

.animate-slide-up {
  animation: slide-up 1.2s cubic-bezier(0.2, 1, 0.3, 1) both;
}

.animate-fade-in {
  animation: fade-in 1s ease-out both;
}

.animate-float {
  animation: float linear infinite;
}

.animate-ping-slow {
  animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.animate-pulse-slow {
  animation: pulse 10s ease-in-out infinite;
}
</style>
