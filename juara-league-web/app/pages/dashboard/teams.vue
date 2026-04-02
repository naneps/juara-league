<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

import type { Team } from '~/types/team.types'

// ─── Store & Utils ────────────────────────────────────────────────────────────
const teamStore = useTeamStore()
const toast = useToast()
const detailSlideover = useTemplateRef('detailSlideover')

// ─── State ────────────────────────────────────────────────────────────────────
const searchQuery = ref('')
const statusFilter = ref('all')
const currentPage = ref(1)
const perPage = 12

// Edit modal state
const editingTeam = ref<Team | null>(null)
const editModalOpen = ref(false)

// Delete confirm modal state
const deletingTeam = ref<Team | null>(null)
const deleteModalOpen = ref(false)

// Selection (Set of team IDs)
const selectedIds = ref<Set<number>>(new Set())

// ─── Derived Data ─────────────────────────────────────────────────────────────
const data = computed(() => {
  let list = teamStore.myTeams

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    list = list.filter(t =>
      t.name.toLowerCase().includes(q) || t.slug.toLowerCase().includes(q)
    )
  }

  if (statusFilter.value !== 'all') {
    list = list.filter(t => t.status === statusFilter.value)
  }

  return list
})

const total = computed(() => data.value.length)
const isAllSelected = computed(() =>
  data.value.length > 0 && data.value.every(t => selectedIds.value.has(t.id))
)
const selectedCount = computed(() => selectedIds.value.size)

const invitations = computed(() => teamStore.myInvitations)

// ─── Fetch ────────────────────────────────────────────────────────────────────
async function loadPage(page = 1) {
  currentPage.value = page
  selectedIds.value = new Set()
  await Promise.all([
    teamStore.fetchMyTeams(),
    teamStore.fetchMyInvitations()
  ])
}

await loadPage(1)

// ─── Selection helpers ────────────────────────────────────────────────────────
function toggleSelect(id: number, value: boolean) {
  const next = new Set(selectedIds.value)
  value ? next.add(id) : next.delete(id)
  selectedIds.value = next
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedIds.value = new Set()
  } else {
    selectedIds.value = new Set(data.value.map(t => t.id))
  }
}

// ─── Actions ──────────────────────────────────────────────────────────────────
function openEdit(team: Team) {
  editingTeam.value = team
  editModalOpen.value = true
}

function openDelete(team: Team) {
  deletingTeam.value = team
  deleteModalOpen.value = true
}

async function handleBulkDelete() {
  const ids = [...selectedIds.value]
  try {
    await Promise.all(ids.map(id => teamStore.deleteTeam(id)))
    toast.add({
      title: `${ids.length} tim dihapus`,
      color: 'success'
    })
    selectedIds.value = new Set()
  } catch (e: any) {
    toast.add({
      title: 'Gagal menghapus',
      description: e.data?.message || 'Pastikan Anda adalah kapten tim',
      color: 'error'
    })
  }
}

async function handleDeleteOne() {
  if (!deletingTeam.value) return
  try {
    await teamStore.deleteTeam(deletingTeam.value.id)
    toast.add({ title: `Tim "${deletingTeam.value.name}" dihapus`, color: 'success' })
    deleteModalOpen.value = false
    deletingTeam.value = null
  } catch (e: any) {
    toast.add({
      title: 'Gagal menghapus',
      description: e.data?.message || 'Pastikan Anda adalah kapten tim',
      color: 'error'
    })
  }
}

function onEditSuccess() {
  editModalOpen.value = false
  editingTeam.value = null
}

async function onAddSuccess() {
  await loadPage(1)
}
</script>

<template>
  <UDashboardPanel id="teams">
    <template #header>
      <UDashboardNavbar title="Manajemen Tim">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <TeamsAddModal @success="onAddSuccess" />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>

      <!-- ── Filter & Actions Bar ── -->
      <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
        <!-- Search -->
        <UInput
          v-model="searchQuery"
          class="max-w-xs"
          icon="i-lucide-search"
          placeholder="Cari nama atau slug tim..."
        />

        <div class="flex flex-wrap items-center gap-2">
          <!-- Select all toggle (hanya tampil kalau ada data) -->
          <UButton
            v-if="data.length"
            :label="isAllSelected ? 'Batalkan Semua' : 'Pilih Semua'"
            :icon="isAllSelected ? 'i-lucide-square-minus' : 'i-lucide-check-square'"
            color="neutral"
            variant="ghost"
            size="sm"
            @click="toggleSelectAll"
          />

          <!-- Bulk delete (tampil saat ada yang dipilih) -->
          <UButton
            v-if="selectedCount"
            :label="`Hapus (${selectedCount})`"
            icon="i-lucide-trash"
            color="error"
            variant="subtle"
            size="sm"
            :loading="teamStore.isLoading"
            @click="handleBulkDelete"
          />

          <!-- Filter status -->
          <USelect
            v-model="statusFilter"
            :items="[
              { label: 'Semua Status', value: 'all' },
              { label: 'Aktif', value: 'active' },
              { label: 'Tertunda', value: 'pending' },
              { label: 'Diskualifikasi', value: 'disqualified' }
            ]"
            placeholder="Filter status"
            class="min-w-36"
            size="sm"
          />
        </div>
      </div>

      <!-- ── Invitations Section ── -->
      <TeamsInvitationsList />

      <!-- ── Stats summary ── -->
      <div class="flex items-center gap-2 text-sm text-muted mb-4">
        <span>
          Menampilkan <strong class="text-default">{{ data.length }}</strong> tim
        </span>
        <span v-if="selectedCount" class="text-primary font-medium">
          · {{ selectedCount }} dipilih
        </span>
      </div>

      <!-- ── Loading skeleton ── -->
      <div
        v-if="teamStore.isLoading"
        class="grid gap-4"
        style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))"
      >
        <div
          v-for="n in perPage"
          :key="n"
          class="rounded-2xl border border-default bg-elevated overflow-hidden"
        >
          <div class="h-28 bg-muted/40 animate-pulse" />
          <div class="p-4 space-y-3">
            <USkeleton class="h-4 w-3/4 mx-auto" />
            <USkeleton class="h-3 w-1/2 mx-auto" />
            <USkeleton class="h-6 w-20 mx-auto rounded-full" />
            <div class="grid grid-cols-2 gap-2 mt-2">
              <USkeleton class="h-12 rounded-xl" />
              <USkeleton class="h-12 rounded-xl" />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <USkeleton class="h-7 rounded-lg" />
              <USkeleton class="h-7 rounded-lg" />
            </div>
          </div>
        </div>
      </div>

      <!-- ── Empty state ── -->
      <div
        v-else-if="!data.length"
        class="flex flex-col items-center justify-center py-20 gap-4 text-center"
      >
        <div class="size-16 rounded-2xl bg-muted/50 flex items-center justify-center">
          <UIcon name="i-lucide-users" class="size-8 text-muted" />
        </div>
        <div>
          <p class="font-semibold text-highlighted">Belum ada tim</p>
          <p class="text-sm text-muted mt-1">
            {{ searchQuery || statusFilter !== 'all' ? 'Tidak ada tim yang cocok dengan filter.' : 'Mulai dengan membuat tim pertama Anda.' }}
          </p>
        </div>
        <TeamsAddModal v-if="!searchQuery && statusFilter === 'all'" @success="onAddSuccess">
          <UButton label="Buat Tim Baru" icon="i-lucide-plus" />
        </TeamsAddModal>
      </div>

      <!-- ── Cards Grid ── -->
      <div
        v-else
        class="grid gap-4"
        style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr))"
      >
        <TeamsCard
          v-for="team in data"
          :key="team.id"
          :team="team"
          :selected="selectedIds.has(team.id)"
          @update:selected="toggleSelect(team.id, $event)"
          @detail="detailSlideover?.openFor($event)"
          @edit="openEdit($event)"
          @delete="openDelete($event)"
        />
      </div>

      <!-- ── Pagination ── -->
      <div
        v-if="total > perPage"
        class="flex items-center justify-center pt-2 mt-auto border-t border-default"
      >
        <UPagination
          :page="currentPage"
          :items-per-page="perPage"
          :total="total"
          @update:page="loadPage"
        />
      </div>
    </template>
  </UDashboardPanel>

  <!-- ── Edit Modal (dikontrol dari luar lewat v-model) ── -->
  <TeamsEditModal
    v-model:open="editModalOpen"
    :team="editingTeam"
    @success="onEditSuccess"
  />

  <!-- ── Single Delete Confirm Modal ── -->
  <UModal
    v-model:open="deleteModalOpen"
    :title="`Hapus Tim`"
    :description="deletingTeam ? `Apakah Anda yakin ingin menghapus tim &quot;${deletingTeam.name}&quot;?` : ''"
  >
    <template #body>
      <p class="text-sm text-muted mb-4">
        Tindakan ini tidak dapat dibatalkan. Hanya kapten yang dapat menghapus tim.
      </p>
      <div class="flex justify-end gap-2">
        <UButton
          label="Batal"
          color="neutral"
          variant="subtle"
          @click="deleteModalOpen = false"
        />
        <UButton
          label="Hapus"
          color="error"
          variant="solid"
          :loading="teamStore.isLoading"
          @click="handleDeleteOne"
        />
      </div>
    </template>
  </UModal>

  <!-- ── Detail Slideover ── -->
  <TeamsDetailSlideover ref="detailSlideover" @updated="loadPage(currentPage)" />
</template>
