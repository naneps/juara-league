<script setup lang="ts">
import { useAdminStore } from '~/stores/admin.store'

definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const adminStore = useAdminStore()
const { users, usersMeta, isLoading, error } = storeToRefs(adminStore)
const toast = useToast()

const columns: any[] = [{
  accessorKey: 'user',
  header: 'User'
}, {
  accessorKey: 'roles',
  header: 'Role'
}, {
  accessorKey: 'status',
  header: 'Status'
}, {
  accessorKey: 'created_at',
  header: 'Bergabung'
}, {
  id: 'actions',
  header: 'Aksi'
}]

const search = ref('')
const roleFilter = ref('all')
const page = ref(1)

// Fetch data logic
const fetchUsers = () => {
  adminStore.fetchUsers({
    search: search.value || undefined,
    role: roleFilter.value === 'all' ? undefined : roleFilter.value,
    page: page.value,
    per_page: 15
  })
}

// Initial fetch + watch
const { refresh, status } = await useAsyncData('admin-users-list', () => 
  adminStore.fetchUsers({
    search: search.value || undefined,
    role: roleFilter.value === 'all' ? undefined : roleFilter.value,
    page: page.value,
    per_page: 15
  }).then(() => true),
  { 
    watch: [roleFilter, page],
    lazy: true 
  }
)

watch(search, (newVal, oldVal, onCleanup) => {
  const timeout = setTimeout(() => {
    page.value = 1
    refresh()
  }, 500)
  onCleanup(() => clearTimeout(timeout))
})

const handleToggleSuspension = async (user: any) => {
  try {
    const res = await adminStore.toggleUserSuspension(user.id)
    toast.add({
      title: res.is_suspended ? 'Akses Diblokir' : 'Akses Dipulihkan',
      description: `Akun ${user.name} telah berhasil diperbarui.`,
      color: res.is_suspended ? 'warning' : 'success'
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.data?.message || 'Terjadi kesalahan sistem.',
      color: 'danger'
    })
  }
}

const handleChangeRole = async (user: any, newRole: string) => {
  try {
    await adminStore.changeUserRole(user.id, newRole)
    toast.add({
      title: 'Role Diperbarui',
      description: `Role ${user.name} sekarang adalah ${newRole.toUpperCase()}.`,
      color: 'success'
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.data?.message || 'Terjadi kesalahan sistem.',
      color: 'danger'
    })
  }
}

const formatDate = (date: string) => {
  if (!date) return '-'
  try {
    return new Date(date).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    })
  } catch (e) {
    return '-'
  }
}
</script>

<template>
  <UDashboardPanel id="users_management_container">
    <template #header>
      <UDashboardNavbar title="Manajemen User" description="Kelola hak akses dan status akun seluruh pengguna platform.">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <div class="flex items-center gap-3">
            <UInput
              v-model="search"
              icon="i-lucide-search"
              placeholder="Cari user..."
              class="w-48 lg:w-64 border-neutral-200 dark:border-white/5"
              size="sm"
            />
            <USelectMenu
              v-model="roleFilter"
              :items="[
                { label: 'Semua Role', value: 'all' },
                { label: 'Admin', value: 'admin' },
                { label: 'User', value: 'user' }
              ]"
              value-attribute="value"
              size="sm"
              class="w-32"
            />
            <UButton
              icon="i-lucide-refresh-cw"
              variant="ghost"
              color="neutral"
              :loading="isLoading || status === 'pending'"
              @click="refresh"
            />
          </div>
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div v-if="error && !users.length" class="p-12 text-center">
        <div class="p-8 rounded-3xl bg-error-500/10 border border-error-500/20 max-w-md mx-auto">
          <UIcon name="i-lucide-alert-triangle" class="size-12 text-error-500 mb-4" />
          <h3 class="text-lg font-bold dark:text-white mb-2">Gagal Memuat Data</h3>
          <p class="text-sm dark:text-neutral-400 mb-6">{{ error }}</p>
          <UButton color="neutral" variant="outline" @click="refresh">Coba Lagi</UButton>
        </div>
      </div>

      <div v-else class="space-y-6">
        <UCard
          class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5 overflow-hidden"
          :ui="{ body: 'p-0 sm:p-0' }"
        >
          <UTable
            :rows="users || []"
            :columns="columns"
            :loading="isLoading || status === 'pending'"
            :ui="{ 
              thead: 'bg-neutral-50 dark:bg-white/[0.02]',
              th: { base: 'text-[10px] uppercase tracking-widest font-black text-neutral-500 py-4 px-4 border-b border-neutral-200 dark:border-white/5' },
              td: { base: 'py-4 px-4' }
            }"
          >
            <template #user-cell="{ row }">
              <div class="flex items-center gap-3">
                <UAvatar 
                  :src="(row.original as any).avatar" 
                  :alt="(row.original as any).name" 
                  size="sm"
                  class="ring-1 ring-white/10"
                />
                <div class="flex flex-col min-w-0">
                  <span class="font-bold dark:text-slate-100 italic tracking-tight truncate">{{ (row.original as any).name }}</span>
                  <span class="text-[10px] text-slate-500 font-medium truncate">@{{ (row.original as any).username || 'user' }} • {{ (row.original as any).email }}</span>
                </div>
              </div>
            </template>

            <template #roles-cell="{ row }">
              <div class="flex gap-1" v-if="(row.original as any).roles && (row.original as any).roles.length">
                <UBadge 
                  v-for="role in (row.original as any).roles" 
                  :key="role"
                  :color="role === 'admin' ? 'indigo' : 'neutral'"
                  variant="subtle"
                  size="sm"
                  class="font-black uppercase tracking-tighter text-[9px] px-2"
                >
                  {{ role }}
                </UBadge>
              </div>
            </template>

            <template #status-cell="{ row }">
              <UBadge 
                :color="(row.original as any).is_suspended ? 'error' : 'success'" 
                variant="soft"
                size="sm"
                class="font-bold uppercase text-[9px] tracking-widest"
              >
                {{ (row.original as any).is_suspended ? 'Suspended' : 'Active' }}
              </UBadge>
            </template>

            <template #created_at-cell="{ row }">
              <span class="text-xs text-neutral-500 font-medium tracking-tight">
                {{ formatDate((row.original as any).created_at) }}
              </span>
            </template>

            <template #actions-cell="{ row }">
              <UDropdownMenu
                v-if="row"
                :items="[[
                  {
                    label: (row.original as any).roles?.includes('admin') ? 'Jadikan User Biasa' : 'Beri Akses Admin',
                    icon: (row.original as any).roles?.includes('admin') ? 'i-lucide-user' : 'i-lucide-shield-check',
                    onSelect: () => handleChangeRole((row.original as any), (row.original as any).roles?.includes('admin') ? 'user' : 'admin')
                  },
                  {
                    label: (row.original as any).is_suspended ? 'Aktifkan Akun' : 'Blokir Akun',
                    icon: (row.original as any).is_suspended ? 'i-lucide-unlock' : 'i-lucide-lock',
                    color: (row.original as any).is_suspended ? 'success' : 'error',
                    onSelect: () => handleToggleSuspension((row.original as any))
                  }
                ]]"
              >
                <UButton
                  icon="i-lucide-more-horizontal"
                  color="neutral"
                  variant="ghost"
                  size="xs"
                />
              </UDropdownMenu>
            </template>

            <template #empty-state>
              <div class="flex flex-col items-center justify-center py-24 text-neutral-600">
                <div class="size-16 rounded-3xl bg-neutral-50 dark:bg-white/5 flex items-center justify-center mb-6">
                  <UIcon name="i-lucide-ghost" class="size-8 opacity-20" />
                </div>
                <p class="font-bold text-sm tracking-tight italic">Hampa bosku...</p>
                <p class="text-xs opacity-60">Tidak ada user yang ditemukan dengan kriteria ini.</p>
              </div>
            </template>
          </UTable>

          <div v-if="usersMeta && usersMeta.total > usersMeta.per_page" class="p-4 border-t border-neutral-200 dark:border-white/5 flex justify-end">
            <UPagination
              v-model="page"
              :total="usersMeta.total"
              :page-count="usersMeta.per_page"
              size="sm"
            />
          </div>
        </UCard>
      </div>
    </template>
  </UDashboardPanel>
</template>
