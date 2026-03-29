<template>
  <div class="py-24 space-y-8 max-w-4xl mx-auto text-center">
    <UIcon name="i-lucide-trophy" class="size-20 text-primary-500 mx-auto mb-6" />
    <h1 class="text-5xl font-bold tracking-tight text-white mb-6">
      Juara League
    </h1>
    <p class="text-xl text-neutral-400 mb-8 max-w-2xl mx-auto">
      Platform turnamen paling fleksibel & mudah digunakan di Indonesia. Kelola turnamen multi-format dengan bracket real-time dan manajemen role yang lengkap.
    </p>
    
    <div class="flex items-center justify-center gap-4">
      <UButton size="xl" color="primary" icon="i-lucide-plus">
        Buat Turnamen
      </UButton>
      <UButton size="xl" color="neutral" variant="subtle" icon="i-lucide-compass">
        Eksplorasi
      </UButton>
    </div>

    <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
      <UCard>
        <div class="space-y-4 p-2">
          <UIcon name="i-lucide-shuffle" class="size-8 text-primary-500" />
          <h3 class="text-xl font-semibold text-white">Multi Format</h3>
          <p class="text-sm text-neutral-400">Single Elimination, Double, Round Robin, hingga Swiss format langsung dalam satu tempat.</p>
        </div>
      </UCard>
      
      <UCard>
        <div class="space-y-4 p-2">
          <UIcon name="i-lucide-globe" class="size-8 text-primary-500" />
          <h3 class="text-xl font-semibold text-white">Public Bracket</h3>
          <p class="text-sm text-neutral-400">Bagikan live bracket ke semua orang melalui URL publik tanpa mewajibkan login.</p>
        </div>
      </UCard>

      <UCard>
        <div class="space-y-4 p-2">
          <UIcon name="i-lucide-users" class="size-8 text-primary-500" />
          <h3 class="text-xl font-semibold text-white">Multi-role System</h3>
          <p class="text-sm text-neutral-400">Mulai dari Owner, Co-Organizer, Referee, hingga fungsi Kapten untuk mengelola tim.</p>
        </div>
      </UCard>
    </div>
  </div>
</template>
