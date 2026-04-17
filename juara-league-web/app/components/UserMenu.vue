<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

defineProps<{
  collapsed?: boolean
}>()

const colorMode = useColorMode()
const appConfig = useAppConfig()
const { user: authUser, logout } = useAuth()
const { t } = useI18n()

const colors = ['red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose']
const neutrals = ['slate', 'gray', 'zinc', 'neutral', 'stone']

const user = computed(() => ({
  name: authUser.value?.name || t('usermenu.fallback_name'),
  avatar: {
    src: authUser.value?.avatar || `https://i.pravatar.cc/150?u=${authUser.value?.email || 'juara'}`,
    alt: authUser.value?.name || t('usermenu.fallback_name')
  }
}))

const isAdmin = computed(() => authUser.value?.roles?.some(role => ['admin', 'super_admin'].includes(role)))

const items = computed<DropdownMenuItem[][]>(() => {
  const baseItems = [[{
    type: 'label',
    label: user.value.name,
    avatar: user.value.avatar
  }], [{
    label: t('usermenu.profile'),
    icon: 'i-lucide-user'
  }, {
    label: t('usermenu.settings'),
    icon: 'i-lucide-settings',
    to: '/dashboard/settings'
  }]] as DropdownMenuItem[][]

  if (isAdmin.value) {
    baseItems[1].push({
      label: t('usermenu.admin_panel'),
      icon: 'i-lucide-shield-check',
      to: '/admin/dashboard',
      class: 'text-indigo-500 font-bold'
    })
  }

  baseItems.push([{
    label: t('usermenu.theme'),
    icon: 'i-lucide-palette',
    children: [{
      label: 'Primary',
      slot: 'chip',
      chip: appConfig.ui.colors.primary,
      content: {
        align: 'center',
        collisionPadding: 16
      },
      children: colors.map(color => ({
        label: color,
        chip: color,
        slot: 'chip',
        checked: appConfig.ui.colors.primary === color,
        type: 'checkbox',
        onSelect: (e) => {
          e.preventDefault()

          appConfig.ui.colors.primary = color
        }
      }))
    }, {
      label: 'Neutral',
      slot: 'chip',
      chip: appConfig.ui.colors.neutral === 'neutral' ? 'old-neutral' : appConfig.ui.colors.neutral,
      content: {
        align: 'end',
        collisionPadding: 16
      },
      children: neutrals.map(color => ({
        label: color,
        chip: color === 'neutral' ? 'old-neutral' : color,
        slot: 'chip',
        type: 'checkbox',
        checked: appConfig.ui.colors.neutral === color,
        onSelect: (e) => {
          e.preventDefault()

          appConfig.ui.colors.neutral = color
        }
      }))
    }]
  }, {
    label: t('usermenu.appearance'),
    icon: 'i-lucide-sun-moon',
    children: [{
      label: t('usermenu.light'),
      icon: 'i-lucide-sun',
      type: 'checkbox',
      checked: colorMode.value === 'light',
      onSelect(e: Event) {
        e.preventDefault()

        colorMode.preference = 'light'
      }
    }, {
      label: t('usermenu.dark'),
      icon: 'i-lucide-moon',
      type: 'checkbox',
      checked: colorMode.value === 'dark',
      onUpdateChecked(checked: boolean) {
        if (checked) {
          colorMode.preference = 'dark'
        }
      },
      onSelect(e: Event) {
        e.preventDefault()
      }
    }]
  }], [{
    label: t('usermenu.logout'),
    icon: 'i-lucide-log-out',
    onSelect: async () => {
      await logout()
    }
  }])

  return baseItems
})
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      v-bind="{
        ...user,
        label: collapsed ? undefined : user?.name,
        trailingIcon: collapsed ? undefined : 'i-lucide-chevrons-up-down'
      }"
      color="neutral"
      variant="ghost"
      block
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :ui="{
        trailingIcon: 'text-dimmed'
      }"
    />

    <template #chip-leading="{ item }">
      <div class="inline-flex items-center justify-center shrink-0 size-5">
        <span
          class="rounded-full ring ring-bg bg-(--chip-light) dark:bg-(--chip-dark) size-2"
          :style="{
            '--chip-light': `var(--color-${(item as any).chip}-500)`,
            '--chip-dark': `var(--color-${(item as any).chip}-400)`
          }"
        />
      </div>
    </template>
  </UDropdownMenu>
</template>
