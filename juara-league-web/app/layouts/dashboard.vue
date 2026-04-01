<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const route = useRoute()
const toast = useToast()

const open = ref(false)

const links = [[{
  label: 'Ringkasan',
  icon: 'i-lucide-layout-dashboard',
  to: '/dashboard',
  onSelect: () => {
    open.value = false
  }
}, {
  label: 'Turnamen',
  icon: 'i-lucide-trophy',
  to: '/dashboard/tournaments',
  onSelect: () => {
    open.value = false
  }
}, {
  label: 'Pertandingan',
  icon: 'i-lucide-swords',
  to: '/dashboard/matches',
  onSelect: () => {
    open.value = false
  }
}, {
  label: 'Tim Saya',
  icon: 'i-lucide-users-round',
  to: '/dashboard/teams',
  onSelect: () => {
    open.value = false
  }
}, {
  label: 'Notifikasi',
  icon: 'i-lucide-bell',
  to: '/dashboard/inbox',
  badge: '4',
  onSelect: () => {
    open.value = false
  }
}, {
  label: 'Pengaturan',
  to: '/dashboard/settings',
  icon: 'i-lucide-settings',
  defaultOpen: true,
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
    label: 'Notifikasi',
    to: '/dashboard/settings/notifications',
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
}]] satisfies NavigationMenuItem[][]

const groups = computed(() => [{
  id: 'links',
  label: 'Cari navigasi',
  items: links.flat()
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
      class="bg-elevated/25"
      :ui="{ footer: 'lg:border-t lg:border-default' }"
    >
      <template #header="{ collapsed }">
        <TeamsMenu :collapsed="collapsed" />
      </template>

      <template #default="{ collapsed }">
        <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" />

        <UNavigationMenu
          :collapsed="collapsed"
          :items="links[0]"
          orientation="vertical"
          tooltip
          popover
        />

        <UNavigationMenu
          :collapsed="collapsed"
          :items="links[1]"
          orientation="vertical"
          tooltip
          class="mt-auto"
        />
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
