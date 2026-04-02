<script setup lang="ts">
const teamStore = useTeamStore()
const toast = useToast()

const invitations = computed(() => teamStore.myInvitations)

async function handleAccept(token: string, teamName: string) {
  try {
    await teamStore.acceptInvitation(token)
    toast.add({
      title: `Berhasil bergabung dengan ${teamName}`,
      color: 'success'
    })
  } catch (e: any) {
    toast.add({
      title: 'Gagal menerima undangan',
      description: e.data?.message || 'Token mungkin sudah tidak valid',
      color: 'error'
    })
  }
}

async function handleDecline(token: string) {
  try {
    await teamStore.declineInvitation(token)
    toast.add({
      title: 'Undangan ditolak',
      color: 'neutral'
    })
  } catch (e: any) {
    toast.add({
      title: 'Gagal menolak undangan',
      color: 'error'
    })
  }
}
</script>

<template>
  <div v-if="invitations.length" class="mb-8 space-y-4">
    <div class="flex items-center gap-2 px-1">
      <UIcon name="i-lucide-mail" class="size-5 text-primary" />
      <h2 class="text-lg font-bold text-highlighted">Undangan Tim</h2>
      <UBadge :label="invitations.length.toString()" color="primary" variant="subtle" size="sm" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div 
        v-for="inv in invitations" 
        :key="inv.id"
        class="relative flex flex-col p-4 rounded-2xl border border-primary/20 bg-primary/5 hover:bg-primary/10 transition-colors"
      >
        <div class="flex items-start justify-between gap-3 mb-3">
          <div class="flex items-center gap-3">
            <UAvatar
              :src="inv.team.logo_url"
              :alt="inv.team.name"
              size="lg"
              class="rounded-xl ring-2 ring-white dark:ring-neutral-900 shadow-sm"
            />
            <div class="min-w-0">
              <h3 class="font-bold text-highlighted truncate">{{ inv.team.name }}</h3>
              <p class="text-xs text-muted truncate">Kapten: {{ inv.team.captain?.name }}</p>
            </div>
          </div>
        </div>

        <p class="text-sm text-balance mb-4">
          Anda diundang untuk bergabung dengan tim <strong>{{ inv.team.name }}</strong>.
        </p>

        <div class="flex gap-2 mt-auto">
          <UButton
            label="Terima"
            icon="i-lucide-check"
            color="primary"
            class="flex-1"
            size="sm"
            :loading="teamStore.isLoading"
            @click="handleAccept(inv.token, inv.team.name)"
          />
          <UButton
            label="Tolak"
            variant="ghost"
            color="neutral"
            size="sm"
            :loading="teamStore.isLoading"
            @click="handleDecline(inv.token)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
