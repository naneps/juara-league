<script setup lang="ts">
import { useSportStore } from '~/stores/sport.store'
import type { Sport, SportType } from '~/types/sport'

definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const sportStore = useSportStore()
const toast = useToast()

const sports = computed(() => sportStore.sports)
const isLoading = ref(false)

const columns: any[] = [{
  accessorKey: 'name',
  header: 'Nama Cabang'
}, {
  accessorKey: 'type',
  header: 'Tipe'
}, {
  accessorKey: 'is_active',
  header: 'Status'
}, {
  id: 'actions',
  header: 'Aksi'
}]

// Modals state
const isAddModalOpen = ref(false)
const isEditModalOpen = ref(false)
const selectedSport = ref<Sport | null>(null)

// Form state
const state = reactive({
  name: '',
  type: 'e-sport' as SportType,
  is_active: true,
  icon_url: ''
})

const resetForm = () => {
  state.name = ''
  state.type = 'e-sport'
  state.is_active = true
  state.icon_url = ''
}

const openEditModal = (sport: Sport) => {
  selectedSport.value = sport
  state.name = sport.name
  state.type = sport.type
  state.is_active = sport.is_active
  state.icon_url = sport.icon_url || ''
  isEditModalOpen.value = true
}

const onSubmitAdd = async () => {
  isLoading.value = true
  try {
    await sportStore.createSport({
      name: state.name,
      type: state.type,
      is_active: state.is_active,
      icon_url: state.icon_url || null
    })
    toast.add({ title: 'Berhasil!', description: 'Cabang olahraga baru telah ditambahkan.', color: 'success' })
    isAddModalOpen.value = false
    resetForm()
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal menambahkan data.', color: 'error' })
  } finally {
    isLoading.value = false
  }
}

const onSubmitEdit = async () => {
  if (!selectedSport.value) return
  isLoading.value = true
  try {
    await sportStore.updateSport(selectedSport.value.id, {
      name: state.name,
      type: state.type,
      is_active: state.is_active,
      icon_url: state.icon_url || null
    })
    toast.add({ title: 'Berhasil!', description: 'Data cabang olahraga diperbarui.', color: 'success' })
    isEditModalOpen.value = false
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal memperbarui data.', color: 'error' })
  } finally {
    isLoading.value = false
  }
}

const confirmDelete = async (id: string) => {
  if (!confirm('Apakah Anda yakin ingin menghapus cabang olahraga ini?')) return
  try {
    await sportStore.deleteSport(id)
    toast.add({ title: 'Terhapus', description: 'Data telah dihapus.', color: 'neutral' })
  } catch (e: any) {
    toast.add({ title: 'Gagal', description: e.data?.message || 'Gagal menghapus data.', color: 'error' })
  }
}

const { refresh, status } = await useAsyncData('admin-sports-list', () => 
  sportStore.fetchAllSportsAdmin().then(() => true),
  { lazy: true }
)
</script>

<template>
  <UDashboardPanel id="sports_management_container">
    <template #header>
      <UDashboardNavbar title="Cabang Olahraga" description="Kelola daftar cabang olahraga yang tersedia untuk turnamen.">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <div class="flex items-center gap-3">
            <UButton
              icon="i-lucide-refresh-cw"
              variant="ghost"
              color="neutral"
              :loading="sportStore.isLoading || status === 'pending'"
              @click="refresh"
            />
            <UButton
              label="Tambah Cabor"
              icon="i-lucide-plus"
              color="indigo"
              class="font-bold tracking-widest uppercase text-[10px]"
              @click="isAddModalOpen = true"
            />
          </div>
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <!-- Stats / Header Decorator -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="p-4 rounded-2xl bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5 relative overflow-hidden group">
          <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest mb-1">Total Olahraga</p>
          <p class="text-2xl font-black dark:text-white italic tracking-tighter">{{ sports?.length || 0 }}</p>
        </div>
      </div>

      <UCard
        class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5 overflow-hidden"
        :ui="{ body: 'p-0 sm:p-0' }"
      >
        <UTable
          :data="sports || []"
          :columns="columns"
          :loading="sportStore.isLoading || status === 'pending'"
          :ui="{ 
            thead: 'bg-neutral-50 dark:bg-white/[0.02]',
            th: { base: 'text-[10px] uppercase tracking-widest font-black text-neutral-500 py-4 px-4 border-b border-neutral-200 dark:border-white/5' },
            td: { base: 'py-4 px-4 border-b border-neutral-100 dark:border-white/[0.02]' }
          }"
        >
          <template #name-cell="{ row }">
            <div class="flex items-center gap-3 py-1">
              <UAvatar
                v-if="(row.original as any).icon_url"
                :src="(row.original as any).icon_url!"
                size="sm"
                class="ring-2 ring-white/10"
              />
              <div 
                v-else
                class="size-8 rounded-full bg-neutral-50 dark:bg-white/5 border border-neutral-300 dark:border-white/10 flex items-center justify-center dark:text-white/50"
              >
                <UIcon
                  :name="(row.original as any).type === 'e-sport' ? 'i-lucide-gamepad-2' : 'i-lucide-trophy'"
                  class="size-4"
                />
              </div>
              <span class="font-bold dark:text-slate-100 tracking-tight">{{ (row.original as any).name }}</span>
            </div>
          </template>

          <template #type-cell="{ row }">
            <UBadge
              :color="(row.original as any).type === 'e-sport' ? 'indigo' : (row.original as any).type === 'traditional' ? 'amber' : 'neutral'"
              variant="subtle"
              size="sm"
              class="font-black uppercase tracking-tighter text-[9px] px-2"
            >
              {{ String((row.original as any).type).replace('-', ' ') }}
            </UBadge>
          </template>

          <template #is_active-cell="{ row }">
            <div class="flex items-center gap-2">
              <div class="size-2 rounded-full" :class="(row.original as any).is_active ? 'bg-emerald-500' : 'bg-neutral-600'" />
              <span class="text-[10px] font-bold tracking-widest uppercase" :class="(row.original as any).is_active ? 'text-emerald-500' : 'text-neutral-500'">
                {{ (row.original as any).is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </template>

          <template #actions-cell="{ row }">
            <div class="flex items-center justify-end gap-1">
              <UButton
                icon="i-lucide-pencil"
                color="neutral"
                variant="ghost"
                size="xs"
                @click="openEditModal(row.original as any)"
              />
              <div class="w-px h-3 bg-white/10 mx-1" v-if="!(row.original as any).is_active" />
              <UButton
                icon="i-lucide-trash-2"
                color="error"
                variant="ghost"
                size="xs"
                @click="confirmDelete((row.original as any).id)"
              />
            </div>
          </template>

          <template #empty-state>
            <div class="flex flex-col items-center justify-center py-20 text-neutral-500">
              <UIcon name="i-lucide-target" class="size-12 opacity-20 mb-4" />
              <p class="text-xs font-bold uppercase tracking-widest">Belum ada data olahraga</p>
            </div>
          </template>
        </UTable>
      </UCard>
    </template>
  </UDashboardPanel>

  <!-- Add Modal -->
  <UModal v-model:open="isAddModalOpen" title="Tambah Cabang Olahraga">
    <template #content>
      <div class="p-6 space-y-6">
        <UForm :state="state" class="space-y-4" @submit="onSubmitAdd">
          <UFormField label="Nama Cabang" name="name" required>
            <UInput v-model="state.name" placeholder="Contoh: Mobile Legends, Futsal" size="lg" />
          </UFormField>

          <UFormField label="Tipe" name="type" required>
            <USelectMenu
              v-model="state.type"
              :items="[
                { label: 'E-Sport', value: 'e-sport' },
                { label: 'Tradisional', value: 'traditional' },
                { label: 'Lainnya', value: 'miscellaneous' }
              ]"
              value-attribute="value"
              size="lg"
            />
          </UFormField>

          <UFormField label="URL Icon (Opsional)" name="icon_url">
            <UInput v-model="state.icon_url" placeholder="https://..." icon="i-lucide-link" size="lg" />
          </UFormField>

          <UFormField label="Status" name="is_active">
            <UCheckbox v-model="state.is_active" label="Aktifkan cabang olahraga ini untuk turnamen" />
          </UFormField>

          <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 dark:border-white/5 mt-6">
            <UButton label="Batal" color="neutral" variant="ghost" @click="isAddModalOpen = false" />
            <UButton type="submit" label="Simpan Data" color="indigo" class="px-6 font-bold" :loading="isLoading" />
          </div>
        </UForm>
      </div>
    </template>
  </UModal>

  <!-- Edit Modal -->
  <UModal v-model:open="isEditModalOpen" title="Perbarui Cabang Olahraga">
    <template #content>
      <div class="p-6 space-y-6">
        <UForm :state="state" class="space-y-4" @submit="onSubmitEdit">
          <UFormField label="Nama Cabang" name="name" required>
            <UInput v-model="state.name" size="lg" />
          </UFormField>

          <UFormField label="Tipe" name="type" required>
            <USelectMenu
              v-model="state.type"
              :items="[
                { label: 'E-Sport', value: 'e-sport' },
                { label: 'Tradisional', value: 'traditional' },
                { label: 'Lainnya', value: 'miscellaneous' }
              ]"
              value-attribute="value"
              size="lg"
            />
          </UFormField>

          <UFormField label="URL Icon (Opsional)" name="icon_url">
            <UInput v-model="state.icon_url" icon="i-lucide-link" size="lg" />
          </UFormField>

          <UFormField label="Status" name="is_active">
            <UCheckbox v-model="state.is_active" label="Aktifkan cabang olahraga ini untuk turnamen" />
          </UFormField>

          <div class="flex justify-end gap-3 pt-6 border-t border-neutral-200 dark:border-white/5 mt-6">
            <UButton label="Batal" color="neutral" variant="ghost" @click="isEditModalOpen = false" />
            <UButton type="submit" label="Simpan Perubahan" color="indigo" class="px-6 font-bold" :loading="isLoading" />
          </div>
        </UForm>
      </div>
    </template>
  </UModal>
</template>
