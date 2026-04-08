<script setup lang="ts">
const route = useRoute()
const slug = route.params.slug as string

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const links = [
  { label: 'Overview',  icon: 'i-lucide-layout-dashboard', to: `/dashboard/tournaments/${slug}`,              exact: true },
  { label: 'Peserta',   icon: 'i-lucide-users',             to: `/dashboard/tournaments/${slug}/participants` },
  { label: 'Babak',     icon: 'i-lucide-layers',            to: `/dashboard/tournaments/${slug}/stages` },
  { label: 'Bracket',   icon: 'i-lucide-git-branch',        to: `/dashboard/tournaments/${slug}/bracket` },
  { label: 'Staf',      icon: 'i-lucide-shield-check',      to: `/dashboard/tournaments/${slug}/staff` },
]

const isActive = (link: typeof links[0]) =>
  link.exact ? route.path === link.to : route.path.startsWith(link.to)
</script>

<template>
  <UDashboardPanel grow>
    <template #header>
      <UDashboardNavbar title="Kelola Turnamen">
        <template #leading>
          <UDashboardSidebarCollapse />
          <div class="h-5 w-px bg-neutral-200 dark:bg-neutral-800 mx-2" />
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
      <div class="p-4 lg:p-8 space-y-6">

        <!-- Tab navigation -->
        <div class="flex items-center gap-0.5 border-b border-neutral-200 dark:border-neutral-800">
          <NuxtLink
            v-for="link in links"
            :key="link.to"
            :to="link.to"
            class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium transition-colors duration-150 border-b-2 -mb-px"
            :class="isActive(link)
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
          >
            <UIcon :name="link.icon" class="size-4" />
            {{ link.label }}
          </NuxtLink>
        </div>

        <!-- Page Content -->
        <NuxtPage />
      </div>
    </template>
  </UDashboardPanel>
</template>
