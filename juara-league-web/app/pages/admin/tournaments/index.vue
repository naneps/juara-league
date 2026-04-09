<script setup lang="ts">
import type { Tournament } from '~/types/tournament'

definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const { $api } = useNuxtApp()
const toast = useToast()

const columns = [{
  key: 'title',
  label: 'Turnamen'
}, {
  key: 'user',
  label: 'Penyelenggara'
}, {
  key: 'sport',
  label: 'Olahraga'
}, {
  key: 'registration_date',
  label: 'Registrasi'
}, {
  key: 'status',
  label: 'Status'
}, {
  key: 'actions',
  label: 'Aksi'
}]

const { data: tournaments, refresh, status } = await useAsyncData('admin-pending-tournaments', () => 
  $api<{ data: Tournament[] }>('/admin/tournaments', {
    params: {
      approval_status: 'pending_review'
    }
  }).then(res => res.data)
)

const handleApprove = async (id: string) => {
  try {
    await $api(`/admin/tournaments/${id}/approve`, { method: 'POST' })
    toast.add({
      title: 'Success',
      description: 'Turnamen berhasil disetujui.',
      color: 'success'
    })
    refresh()
  } catch (err: any) {
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Gagal menyetujui turnamen.',
      color: 'danger'
    })
  }
}

const handleReject = async (id: string) => {
  // Simple prompt for reason for now
  const reason = prompt('Alasan penolakan:')
  if (!reason) return

  try {
    await $api(`/admin/tournaments/${id}/reject`, { 
      method: 'POST',
      body: { reason }
    })
    toast.add({
      title: 'Rejected',
      description: 'Turnamen telah ditolak.',
      color: 'warning'
    })
    refresh()
  } catch (err: any) {
    toast.add({
      title: 'Error',
      description: err.data?.message || 'Gagal menolak turnamen.',
      color: 'danger'
    })
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}
</script>

<template>
  <UDashboardPage>
    <UDashboardHeader title="Moderasi Turnamen" description="Tinjau dan setujui turnamen yang masuk ke platform.">
      <template #right>
        <UButton icon="i-lucide-refresh-cw" variant="ghost" color="neutral" :loading="status === 'pending'" @click="refresh" />
      </template>
    </UDashboardHeader>

    <UDashboardPanelContent>
      <UCard class="bg-slate-900/50 border-slate-800">
        <UTable :rows="tournaments || []" :columns="columns" :loading="status === 'pending'">
          <template #title-data="{ row }">
            <div class="flex flex-col">
              <span class="font-bold text-slate-100">{{ row.title }}</span>
              <span class="text-xs text-slate-500 uppercase">{{ row.category }} • {{ row.mode }}</span>
            </div>
          </template>

          <template #user-data="{ row }">
            <div class="flex items-center gap-2">
              <UAvatar :src="row.user?.avatar" :alt="row.user?.name" size="2xs" />
              <div class="flex flex-col">
                <span class="text-sm text-slate-200">{{ row.user?.name }}</span>
                <span class="text-[10px] text-slate-500">{{ row.user?.email }}</span>
              </div>
            </div>
          </template>

          <template #sport-data="{ row }">
            <UBadge variant="subtle" color="neutral" size="sm">
              {{ row.sport?.name }}
            </UBadge>
          </template>

          <template #registration_date-data="{ row }">
            <div class="flex flex-col text-xs">
              <span class="text-slate-400">Mulai: {{ formatDate(row.registration_start_at) }}</span>
              <span class="text-slate-500">Selesai: {{ formatDate(row.registration_end_at) }}</span>
            </div>
          </template>

          <template #status-data="{ row }">
            <UBadge v-if="row.approval_status === 'pending_review'" color="warning" variant="subtle" class="animate-pulse">
              Menunggu Review
            </UBadge>
            <UBadge v-else-if="row.approval_status === 'approved'" color="success" variant="subtle">
              Disetujui
            </UBadge>
            <UBadge v-else color="neutral" variant="subtle">
              {{ row.approval_status }}
            </UBadge>
          </template>

          <template #actions-data="{ row }">
            <div class="flex items-center gap-2">
              <UButton
                icon="i-lucide-check-circle"
                color="indigo"
                variant="soft"
                size="xs"
                @click="handleApprove(row.id)"
              >
                Approve
              </UButton>
              <UButton
                icon="i-lucide-x-circle"
                color="neutral"
                variant="ghost"
                size="xs"
                @click="handleReject(row.id)"
              >
                Reject
              </UButton>
            </div>
          </template>

          <template #empty-state>
            <div class="flex flex-col items-center justify-center py-12 text-slate-500">
              <UIcon name="i-lucide-inbox" class="w-12 h-12 mb-4 opacity-20" />
              <p>Tidak ada turnamen yang menunggu review.</p>
            </div>
          </template>
        </UTable>
      </UCard>
    </UDashboardPanelContent>
  </UDashboardPage>
</template>
