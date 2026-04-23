<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const route = useRoute()
const toast = useToast()

const open = ref(false)

const { user: authUser } = useAuth()
const isAdmin = computed(() => authUser.value?.roles?.some(role => ['admin', 'super_admin'].includes(role)))

const { t } = useI18n()

const links = computed(() => {
  const allLinks: NavigationMenuItem[][] = []

  // 1. Admin Section (Exclusive Indigo Style)
  if (isAdmin.value) {
    allLinks.push([{
      label: t('sidebar.admin_section'),
      type: 'label',
      class: 'text-[10px] uppercase tracking-[2px] font-black text-indigo-500/70 mb-2 px-2'
    }, {
      label: t('sidebar.admin_dashboard'),
      icon: 'i-lucide-shield-check',
      to: '/admin/dashboard',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: t('sidebar.tournament_moderation'),
      icon: 'i-lucide-list-checks',
      to: '/admin/tournaments',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: t('sidebar.user_management'),
      icon: 'i-lucide-users',
      to: '/admin/users',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: t('sidebar.sports'),
      icon: 'i-lucide-gamepad-2',
      to: '/admin/sports',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: t('sidebar.system_settings'),
      icon: 'i-lucide-settings-2',
      to: '/admin/settings',
      color: 'indigo',
      onSelect: () => {
        open.value = false
      }
    }])
  }

  // 2. User Section (Friendly Emerald Style)
  allLinks.push([{
    label: t('sidebar.main_menu'),
    type: 'label',
    class: 'text-[10px] uppercase tracking-[2px] font-black text-neutral-500/70 mb-2 px-2'
  }, {
    label: t('sidebar.summary'),
    icon: 'i-lucide-layout-dashboard',
    to: '/dashboard',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: t('sidebar.my_tournaments'),
    icon: 'i-lucide-trophy',
    to: '/dashboard/tournaments',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: t('sidebar.participation_history'),
    icon: 'i-lucide-history',
    to: '/dashboard/participations',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: t('sidebar.my_teams'),
    icon: 'i-lucide-users-round',
    to: '/dashboard/teams',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: t('sidebar.notifications'),
    icon: 'i-lucide-bell',
    to: '/dashboard/inbox',
    badge: '4',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: t('sidebar.team_invitations'),
    icon: 'i-lucide-mail-search',
    to: '/dashboard/invitations',
    color: 'primary',
    onSelect: () => {
      open.value = false
    }
  }, {
    label: t('sidebar.settings'),
    to: '/dashboard/settings',
    icon: 'i-lucide-settings',
    color: 'primary',
    defaultOpen: false,
    type: 'trigger',
    children: [{
      label: t('sidebar.profile'),
      to: '/dashboard/settings',
      exact: true,
      onSelect: () => {
        open.value = false
      }
    }, {
      label: t('sidebar.members'),
      to: '/dashboard/settings/members',
      onSelect: () => {
        open.value = false
      }
    }, {
      label: t('sidebar.security'),
      to: '/dashboard/settings/security',
      onSelect: () => {
        open.value = false
      }
    }]
  }])

  // 3. Additional Section (Landing Page)
  allLinks.push([{
    label: t('sidebar.platform'),
    type: 'label',
    class: 'text-[10px] uppercase tracking-[2px] font-black text-neutral-500/70 mb-2 px-2'
  }, {
    label: t('sidebar.homepage'),
    icon: 'i-lucide-external-link',
    to: '/',
    color: 'neutral',
    onSelect: () => {
      open.value = false
    }
  }])

  return allLinks
})

const groups = computed(() => {
  const searchItems = links.value.flat().reduce((acc: any[], item: any) => {
    // Jika punya children (seperti menu Pengaturan), masukkan anak-anaknya ke hasil pencarian
    if (item.children) {
      item.children.forEach((child: any) => {
        acc.push({
          label: child.label,
          icon: child.icon || item.icon, // Gunakan ikon parent jika anak tidak punya
          to: child.to,
          onSelect: child.onSelect
        })
      })
    } 
    // Jika item sendiri punya link 'to' atau 'href', masukkan ke hasil pencarian
    else if (item.to || item.href) {
      acc.push({
        label: item.label,
        icon: item.icon,
        to: item.to,
        href: item.href,
        target: item.target,
        onSelect: item.onSelect
      })
    }
    return acc
  }, [])

  return [{
    id: 'links',
    label: t('sidebar.search_placeholder'),
    items: searchItems
  }]
})

onMounted(async () => {
  const cookie = useCookie('cookie-consent')
  if (cookie.value === 'accepted') {
    return
  }

  toast.add({
    title: t('sidebar.cookie.title'),
    duration: 0,
    close: false,
    actions: [{
      label: t('sidebar.cookie.accept'),
      color: 'neutral',
      variant: 'outline',
      onClick: () => {
        cookie.value = 'accepted'
      }
    }, {
      label: t('sidebar.cookie.opt_out'),
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
      class="dark:bg-neutral-900/40 backdrop-blur-xl border-r border-neutral-200 dark:border-white/5"
      :ui="{ 
        footer: 'lg:border-t lg:border-neutral-200 dark:border-white/5',
        header: 'border-b border-neutral-200 dark:border-white/5 bg-transparent'
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
          <UDashboardSearchButton :collapsed="collapsed" class="bg-neutral-50 dark:bg-white/5 border-neutral-300 dark:border-white/10 hover:bg-white/10 ring-0 transition-all duration-300" />
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
