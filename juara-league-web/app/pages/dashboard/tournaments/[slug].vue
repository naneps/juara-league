<script setup lang="ts">
const route = useRoute()
const slug = route.params.slug as string

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const links = [
  { label: 'Overview', icon: 'i-lucide-layout-dashboard', to: `/dashboard/tournaments/${slug}`, exact: true },
  { label: 'Peserta', icon: 'i-lucide-users', to: `/dashboard/tournaments/${slug}/participants` },
  { label: 'Babak', icon: 'i-lucide-layers', to: `/dashboard/tournaments/${slug}/stages` },
  { label: 'Bracket', icon: 'i-lucide-git-branch', to: `/dashboard/tournaments/${slug}/bracket` },
  { label: 'Staf', icon: 'i-lucide-shield-check', to: `/dashboard/tournaments/${slug}/staff` }
]
</script>

<template>
  <UDashboardPanel grow>
    <template #header>
      <UDashboardNavbar title="Kelola Turnamen">
        <template #leading>
          <UDashboardSidebarCollapse />
          <div class="h-5 w-px bg-neutral-200 dark:bg-white/10 mx-2" />
          <UButton 
            to="/dashboard/tournaments" 
            icon="i-lucide-arrow-left" 
            color="neutral" 
            variant="ghost" 
            size="xs" 
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="p-4 lg:p-8 space-y-8">
        <!-- Tab Navigation -->
        <div class="flex items-center gap-1 p-1 bg-neutral-100 dark:bg-neutral-900 rounded-2xl w-fit">
          <NuxtLink 
            v-for="link in links" 
            :key="link.to"
            :to="link.to"
            class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300"
            :class="[
              route.path === link.to || (link.exact && route.path === link.to)
                ? 'bg-white dark:bg-neutral-800 text-primary-500 shadow-sm' 
                : 'text-neutral-500 hover:text-neutral-900 dark:hover:text-white'
            ]"
          >
            <UIcon :name="link.icon" class="size-4" />
            <span class="uppercase tracking-widest text-[10px]">{{ link.label }}</span>
          </NuxtLink>
        </div>

        <!-- Page Content -->
        <NuxtPage />
      </div>
    </template>
  </UDashboardPanel>
</template>
