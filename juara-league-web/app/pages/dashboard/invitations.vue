<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const teamStore = useTeamStore()
const { t } = useI18n()

const items = computed(() => [{
  label: t('invitations.tabs.pending'),
  icon: 'i-lucide-mail',
  slot: 'pending'
}, {
  label: t('invitations.tabs.history'),
  icon: 'i-lucide-history',
  slot: 'history'
}])

onMounted(async () => {
  await Promise.all([
    teamStore.fetchMyInvitations(),
    teamStore.fetchInvitationHistory()
  ])
})

async function refreshData() {
  await Promise.all([
    teamStore.fetchMyInvitations(),
    teamStore.fetchInvitationHistory()
  ])
}
</script>

<template>
  <UDashboardPanel id="invitations">
    <template #header>
      <UDashboardNavbar :title="$t('invitations.title')">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <UButton
            icon="i-lucide-refresh-cw"
            color="neutral"
            variant="ghost"
            :loading="teamStore.isLoading"
            @click="refreshData"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="max-w-4xl mx-auto w-full space-y-6">
        <div>
          <h1 class="text-2xl font-bold text-highlighted">{{ $t('invitations.all_invitations') }}</h1>
          <p class="text-sm text-muted mt-1">
            {{ $t('invitations.desc') }}
          </p>
        </div>

        <UTabs :items="items" class="w-full">
          <template #pending>
            <div class="mt-6">
              <TeamsInvitationsList v-if="teamStore.myInvitations.length" />
              <div v-else class="flex flex-col items-center justify-center py-20 gap-4 text-center">
                <div class="size-16 rounded-2xl bg-muted/50 flex items-center justify-center">
                  <UIcon name="i-lucide-mail-open" class="size-8 text-muted" />
                </div>
                <div>
                  <p class="font-semibold text-highlighted">{{ $t('invitations.no_pending') }}</p>
                  <p class="text-sm text-muted mt-1">
                    {{ $t('invitations.no_pending_desc') }}
                  </p>
                </div>
              </div>
            </div>
          </template>

          <template #history>
            <div class="mt-6">
              <TeamsInvitationsLog />
            </div>
          </template>
        </UTabs>
      </div>
    </template>
  </UDashboardPanel>
</template>
