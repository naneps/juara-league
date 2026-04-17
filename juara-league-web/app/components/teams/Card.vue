<script setup lang="ts">
import type { Team } from '~/types/team.types'
import { TEAM_STATUS_LABELS, TEAM_STATUS_COLORS } from '~/types/team.types'

const props = defineProps<{
  team: Team
  selected?: boolean
}>()

const emit = defineEmits<{
  detail: [team: Team]
  edit: [team: Team]
  delete: [team: Team]
  'update:selected': [value: boolean]
}>()

const avatarUrl = (name: string, logo?: string) =>
  logo || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&size=96&bold=true&color=fff`

const { t } = useI18n()

const statusColor = computed(() => TEAM_STATUS_COLORS[props.team.status])
const statusLabel = computed(() => t(`teams.status.${props.team.status}`))

const glowClass = computed(() => ({
  active: 'group-hover:shadow-[0_8px_24px_-4px_rgba(34,197,94,0.3)]',
  pending: 'group-hover:shadow-[0_8px_24px_-4px_rgba(234,179,8,0.3)]',
  disqualified: 'group-hover:shadow-[0_8px_24px_-4px_rgba(239,68,68,0.25)]'
})[props.team.status])

const dropdownItems = computed(() => [
  [
    { label: t('common.view_detail'), icon: 'i-lucide-layout-list', onSelect: () => emit('detail', props.team) },
    { label: t('teams.edit_modal.title'), icon: 'i-lucide-pencil', onSelect: () => emit('edit', props.team) }
  ],
  [
    { label: t('teams.delete_modal.confirm'), icon: 'i-lucide-trash', color: 'error' as const, onSelect: () => emit('delete', props.team) }
  ]
])
</script>

<template>
  <div
    class="group relative flex flex-col rounded-xl border border-default bg-elevated/80 backdrop-blur-sm overflow-hidden
           transition-all duration-300 ease-out cursor-pointer select-none
           hover:-translate-y-1"
    :class="[
      glowClass,
      selected ? 'ring-2 ring-primary border-primary' : ''
    ]"
    @click="emit('detail', team)"
  >
    <!-- Accent line at top -->
    <div
      class="h-1 w-full"
      :class="{
        'bg-success': team.status === 'active',
        'bg-warning': team.status === 'pending',
        'bg-error': team.status === 'disqualified'
      }"
    />

    <!-- Selection Checkbox -->
    <div class="absolute top-3 left-3 z-20" @click.stop v-if="selected">
      <UCheckbox
        :model-value="selected"
        @update:model-value="emit('update:selected', $event as boolean)"
      />
    </div>

    <!-- Dropdown Menu -->
    <div class="absolute top-3 right-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-200" @click.stop>
      <UDropdownMenu :items="dropdownItems" :content="{ align: 'end' }">
        <UButton icon="i-lucide-ellipsis-vertical" color="neutral" variant="ghost" size="xs" />
      </UDropdownMenu>
    </div>

    <div class="p-4 flex flex-col gap-4">
      <!-- Main Info: Avatar + Name (Horizontal row) -->
      <div class="flex items-center gap-3">
        <div class="relative flex-shrink-0">
          <UAvatar
            :src="avatarUrl(team.name, team.logo_url)"
            :alt="team.name"
            size="xl"
            class="relative ring-1 ring-default shadow-sm transition-transform duration-300 group-hover:scale-105"
          />
          <!-- Status dot indicator on avatar -->
          <span
            class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-elevated"
            :class="{
              'bg-success': team.status === 'active',
              'bg-warning': team.status === 'pending',
              'bg-error': team.status === 'disqualified'
            }"
          />
        </div>

        <div class="min-w-0 flex-1">
          <h3 class="font-bold text-highlighted text-base truncate leading-tight">
            {{ team.name }}
          </h3>
          <div class="flex items-center gap-1.5 mt-0.5">
            <span class="text-xs text-muted truncate">@{{ team.slug }}</span>
            <span class="text-muted/30">|</span>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-muted opacity-80">
              {{ statusLabel }}
            </span>
          </div>
        </div>
      </div>

      <!-- Compact Stats Row -->
      <div class="grid grid-cols-2 gap-2">
        <div class="flex items-center gap-2 p-2 rounded-lg bg-default/40 border border-default/50 min-w-0">
          <div class="size-7 rounded-md bg-warning/10 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-crown" class="size-4 text-warning" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] text-muted leading-none mb-0.5">{{ $t('teams.detail.role_owner') }}</p>
            <p class="text-xs font-semibold text-highlighted truncate leading-tight">
              {{ team.captain?.name ?? `#${team.captain_id}` }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 p-2 rounded-lg bg-default/40 border border-default/50">
          <div class="size-7 rounded-md bg-primary/10 flex items-center justify-center flex-shrink-0">
            <UIcon name="i-lucide-users" class="size-4 text-primary" />
          </div>
          <div>
            <p class="text-[10px] text-muted leading-none mb-0.5">{{ $t('teams.detail.members_tab') }}</p>
            <p class="text-xs font-bold text-highlighted leading-tight">
              {{ team.members?.length ?? '0' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Row of action buttons (Minimalist) -->
      <div class="flex items-center justify-between border-t border-default pt-3 mt-1" @click.stop>
        <div class="flex gap-2">
          <UButton
            icon="i-lucide-pencil"
            color="neutral"
            variant="ghost"
            size="sm"
            @click="emit('edit', team)"
          />
          <UButton
            icon="i-lucide-layout-list"
            color="neutral"
            variant="ghost"
            size="sm"
            @click="emit('detail', team)"
          />
        </div>
        <UButton
          :label="$t('common.view_detail')"
          variant="link"
          size="xs"
          trailing-icon="i-lucide-chevron-right"
          @click="emit('detail', team)"
        />
      </div>
    </div>
  </div>
</template>
