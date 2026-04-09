<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const route = useRoute()
const toast = useToast()

const open = ref(false)

const { user: authUser } = useAuth()
const isAdmin = computed(() => authUser.value?.roles?.some(role => ['admin', 'super_admin'].includes(role)))

const links = computed(() => {
  const allLinks: NavigationMenuItem[][] = []

  // 1. Admin Section (Exclusive Indigo Style)
  if (isAdmin.value) {
    allLinks.push([{
      label: 'Manajemen Platform',
      type: 'label',
      class: 'text-[10px] uppercase tracking-[2px] font-black text-indigo-500/70 mb-2 px-2'
    }, {
      label: 'Dashboard Admin',
      icon: 'i-lucide-shield-check',
      to: '/admin/dashboard',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: 'Moderasi Turnamen',
      icon: 'i-lucide-list-checks',
      to: '/admin/tournaments',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }])
  }

  // 2. User Section (Friendly Emerald Style)
  allLinks.push([{
    label: 'Menu Utama',
    type: 'label',
    class: 'text-[10px] uppercase tracking-[2px] font-black text-neutral-500/70 mb-2 px-2'
  }, {
    label: 'Ringkasan',
    icon: 'i-lucide-layout-dashboard',
    to: '/dashboard',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: 'Turnamen Saya',
    icon: 'i-lucide-trophy',
    to: '/dashboard/tournaments',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: 'Riwayat Ikut Serta',
    icon: 'i-lucide-history',
    to: '/dashboard/participations',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: 'Tim Saya',
    icon: 'i-lucide-users-round',
    to: '/dashboard/teams',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: 'Notifikasi',
    icon: 'i-lucide-bell',
    to: '/dashboard/inbox',
    badge: '4',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: 'Undangan Tim',
    icon: 'i-lucide-mail-search',
    to: '/dashboard/invitations',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: 'Pengaturan',
    to: '/dashboard/settings',
    icon: 'i-lucide-settings',
    color: 'primary',
    defaultOpen: false,
    type: 'trigger',
    children: [{
      label: 'Profil',
      to: '/dashboard/settings',
      exact: true,
      onSelect: () => {
        open.value = false
      }
    }, {
      label: 'Anggota',
      to: '/dashboard/settings/members',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: 'Keamanan',
      to: '/dashboard/settings/security',
      onSelect: () => {
        open.value = false
      }
    }]
  }])

  return allLinks
})

const groups = computed(() => [{
  id: 'links',
  label: 'Cari navigasi',
  items: links.value.flat()
}])

onMounted(async () => {
  const cookie = useCookie('cookie-consent')
  if (cookie.value === 'accepted') {
    return
  }

  toast.add({
    title: 'We use first-party cookies to enhance your experience on our website.',
    duration: 0,
    close: false,
    actions: [{
      label: 'Accept',
      color: 'neutral',
      variant: 'outline',
      onClick: () => {
        cookie.value = 'accepted'
      }
    }, {
      label: 'Opt out',
      color: 'neutral',
      variant: 'ghost'
    }]
  })
})
</script>

<template>
  <UDashboardGroup unit="rem">
    <UDashboardSidebar
      id="default"
      v-model:open="open"
      collapsible
      resizable
      class="bg-neutral-900/40 backdrop-blur-xl border-r border-white/5"
      :ui="{ 
        footer: 'lg:border-t lg:border-white/5',
        header: 'border-b border-white/5 bg-transparent'
      }"
    >
      <template #header="{ collapsed }">
        <div class="flex items-center gap-3 px-1">
          <UIcon name="i-lucide-trophy" class="size-6 text-primary-400" v-if="!collapsed" />
          <TeamsMenu :collapsed="collapsed" />
        </div>
      </template>

      <template #default="{ collapsed }">
        <div class="px-2 mb-4">
          <UDashboardSearchButton :collapsed="collapsed" class="bg-white/5 border-white/10 hover:bg-white/10 ring-0 transition-all duration-300" />
        </div>

        <div class="flex flex-col gap-8 px-1">
          <UNavigationMenu
            v-for="(group, index) in links"
            :key="index"
            :collapsed="collapsed"
            :items="group"
            orientation="vertical"
            tooltip
            popover
            :ui="{
              label: 'font-bold tracking-tight',
              item: 'rounded-xl transition-all duration-300'
            }"
          />
        </div>
      </template>

      <template #footer="{ collapsed }">
        <UserMenu :collapsed="collapsed" />
      </template>
    </UDashboardSidebar>

    <UDashboardSearch :groups="groups" />

    <slot />

    <NotificationsSlideover />
  </UDashboardGroup>
</template>

<style scoped>
:deep(.bg-elevated) {
  background-color: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

:deep(.ring-default) {
  --tw-ring-color: rgba(255, 255, 255, 0.1);
}

/* Custom indicator for active state */
:deep(.router-link-active) {
  position: relative;
  overflow: hidden;
}

:deep(.router-link-active)::before {
  content: '';
  position: absolute;
  left: 0;
  top: 25%;
  bottom: 25%;
  width: 2px;
  background-color: currentColor;
  border-radius: 9999px;
  filter: blur(1px);
}
</style>
