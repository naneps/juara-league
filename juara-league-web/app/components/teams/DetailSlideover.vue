<script setup lang="ts">
import type { Team, TeamMember } from '~/types/team.types'
import { TEAM_STATUS_LABELS, TEAM_STATUS_COLORS } from '~/types/team.types'

const emit = defineEmits(['closed', 'updated'])

const teamStore = useTeamStore()
const toast = useToast()
const { t } = useI18n()
const open = ref(false)
const isLoadingDetail = ref(false)
const detail = ref<Team | null>(null)

// Fungsi dipanggil dari parent untuk membuka slideover
async function openFor(team: Team) {
  open.value = true
  isLoadingDetail.value = true
  try {
    detail.value = await teamStore.fetchTeam(team.id)
  } catch {
    toast.add({ title: t('teams.detail_slideover.failed_load'), color: 'error' })
  } finally {
    isLoadingDetail.value = false
  }
}

defineExpose({ openFor })

const isCaptain = computed(() => {
  // cek dari currentTeam di store (akan diisi setelah fetchTeam)
  return detail.value?.captain_id === teamStore.currentTeam?.captain_id
})

async function handleRemoveMember(member: TeamMember) {
  if (!detail.value) return
  try {
    await teamStore.removeMember(detail.value.id, member.id)
    // Sinkronisasi local detail
    if (detail.value.members) {
      detail.value.members = detail.value.members.filter(m => m.id !== member.id)
    }
    toast.add({
      title: t('teams.detail_slideover.member_removed_title'),
      description: t('teams.detail_slideover.member_removed_desc', { name: member.name }),
      color: 'success'
    })
  } catch (error: any) {
    toast.add({
      title: 'Gagal',
      description: error.data?.message || 'Terjadi kesalahan',
      color: 'error'
    })
  }
}

function onInviteSuccess() {
  toast.add({
    title: t('teams.invite_modal.success_title'),
    description: t('teams.invite_modal.success_desc_simple'),
    color: 'success'
  })
}

function onEditSuccess() {
  if (detail.value) {
    // Sinkronisasi dari store
    const updated = teamStore.teams.find(t => t.id === detail.value?.id)
    if (updated) detail.value = { ...detail.value, ...updated }
  }
  emit('updated')
}

const avatarUrl = (name: string, logo?: string) =>
  logo || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random`
</script>

<template>
  <USlideover v-model:open="open" side="right">
    <template #header>
      <div class="flex items-center gap-3">
        <UAvatar
          v-if="detail"
          :src="avatarUrl(detail.name, detail.logo_url)"
          :alt="detail.name"
          size="md"
        />
        <div>
          <p class="font-semibold text-highlighted">{{ detail?.name ?? '...' }}</p>
          <p class="text-sm text-muted">@{{ detail?.slug ?? '' }}</p>
        </div>
      </div>
    </template>

    <template #body>
      <!-- Loading skeleton -->
      <div v-if="isLoadingDetail" class="space-y-4 py-4">
        <USkeleton class="h-6 w-1/2" />
        <USkeleton class="h-4 w-3/4" />
        <USkeleton class="h-4 w-full" />
        <USkeleton class="h-32 w-full" />
      </div>

      <template v-else-if="detail">
        <!-- Status & Deskripsi -->
        <div class="space-y-4 pb-4 border-b border-default">
          <div class="flex items-center gap-2">
            <UBadge
              :color="TEAM_STATUS_COLORS[detail.status]"
              variant="subtle"
              class="capitalize"
            >
              {{ TEAM_STATUS_LABELS[detail.status] }}
            </UBadge>
          </div>

          <p v-if="detail.description" class="text-sm text-muted">
            {{ detail.description }}
          </p>
          <p v-else class="text-sm text-muted italic">{{ $t('common.no_description') }}</p>

          <div class="text-sm text-muted">
            <span class="font-medium text-default">{{ $t('teams.detail.role_owner') }}:</span>
            {{ detail.captain?.name ?? `User #${detail.captain_id}` }}
          </div>
        </div>

        <!-- Aksi -->
        <div class="flex gap-2 py-4 border-b border-default">
          <TeamsEditModal :team="detail" @success="onEditSuccess">
            <UButton
              :label="$t('teams.edit_modal.title')"
              icon="i-lucide-pencil"
              color="neutral"
              variant="outline"
              size="sm"
            />
          </TeamsEditModal>

          <TeamsInviteModal :team="detail" @success="onInviteSuccess">
            <UButton
              :label="$t('teams.invite_modal.title')"
              icon="i-lucide-user-plus"
              color="primary"
              variant="outline"
              size="sm"
            />
          </TeamsInviteModal>
        </div>

        <!-- Daftar Anggota -->
        <div class="py-4">
          <p class="text-sm font-semibold text-highlighted mb-3">
            {{ $t('teams.detail.members_tab') }} ({{ detail.members?.length ?? 0 }})
          </p>

          <div v-if="!detail.members?.length" class="text-sm text-muted italic">
            {{ $t('teams.detail.no_members') }}
          </div>

          <ul v-else class="space-y-2">
            <li
              v-for="member in detail.members"
              :key="member.id"
              class="flex items-center justify-between gap-3 p-2 rounded-lg hover:bg-elevated transition-colors"
            >
              <div class="flex items-center gap-3 min-w-0">
                <UAvatar
                  :src="member.avatar || avatarUrl(member.name)"
                  :alt="member.name"
                  size="sm"
                />
                <div class="min-w-0">
                  <p class="text-sm font-medium text-highlighted truncate">
                    {{ member.name }}
                    <UBadge
                      v-if="member.id === detail.captain_id"
                      :label="$t('teams.detail.role_owner')"
                      color="warning"
                      variant="subtle"
                      size="xs"
                      class="ml-1"
                    />
                  </p>
                  <p class="text-xs text-muted truncate">{{ member.email }}</p>
                </div>
              </div>

              <UButton
                v-if="member.id !== detail.captain_id"
                icon="i-lucide-user-x"
                color="error"
                variant="ghost"
                size="xs"
                :loading="teamStore.isLoading"
                @click="handleRemoveMember(member)"
              />
            </li>
          </ul>
        </div>
      </template>
    </template>
  </USlideover>
</template>
