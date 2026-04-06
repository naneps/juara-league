<script setup lang="ts">
import type { Sport, SportType, StoreSportPayload } from '~/types/sport'

definePageMeta({
  layout: 'dashboard'
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

const loadData = async () => {
  try {
    await sportStore.fetchAllSportsAdmin()
  } catch (e) {}
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <UDashboardPage unit="rem">
    <UDashboardPanel grow>
      <UDashboardNavbar title="Manajemen Cabang Olahraga">
        <template #right>
          <UButton
            label="Tambah Cabor"
            icon="i-lucide-plus"
            color="primary"
            @click="isAddModalOpen = true"
          />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <template #left>
          <p class="text-sm text-muted">Kelola daftar cabang olahraga yang tersedia untuk turnamen.</p>
        </template>
      </UDashboardToolbar>

      <UTable
        :data="sports"
        :columns="columns"
        :loading="sportStore.isLoading"
        class="w-full"
      >
        <template #name-cell="{ row }">
          <div class="flex items-center gap-3">
            <UAvatar
              v-if="(row.original as any).icon_url"
              :src="(row.original as any).icon_url!"
              size="xs"
            />
            <UIcon
              v-else
              :name="(row.original as any).type === 'e-sport' ? 'i-lucide-gamepad-2' : 'i-lucide-trophy'"
              class="w-5 h-5 opacity-50"
            />
            <span class="font-medium text-default">{{ (row.original as any).name }}</span>
          </div>
        </template>

        <template #type-cell="{ row }">
          <UBadge
            :color="(row.original as any).type === 'e-sport' ? 'info' : (row.original as any).type === 'traditional' ? 'warning' : 'neutral'"
            variant="subtle"
            size="xs"
            class="capitalize"
          >
            {{ (row.original as any).type.replace('-', ' ') }}
          </UBadge>
        </template>

        <template #is_active-cell="{ row }">
          <UBadge
            :color="(row.original as any).is_active ? 'success' : 'error'"
            variant="solid"
            size="xs"
          >
            {{ (row.original as any).is_active ? 'Aktif' : 'Nonaktif' }}
          </UBadge>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex justify-end gap-2">
            <UButton
              icon="i-lucide-pencil"
              color="neutral"
              variant="ghost"
              size="xs"
              @click="openEditModal(row.original as any)"
            />
            <UButton
              icon="i-lucide-trash-2"
              color="error"
              variant="ghost"
              size="xs"
              @click="confirmDelete((row.original as any).id)"
            />
          </div>
        </template>
      </UTable>
    </UDashboardPanel>

    <!-- Add Modal -->
    <UModal v-model:open="isAddModalOpen">
      <UCard :ui="{ body: 'sm:p-6' }">
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold leading-6 text-default">
              Tambah Cabang Olahraga
            </h3>
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-lucide-x"
              class="-my-1"
              @click="isAddModalOpen = false"
            />
          </div>
        </template>

        <UForm :state="state" class="space-y-4" @submit="onSubmitAdd">
          <UFormField label="Nama Cabang" name="name" required>
            <UInput v-model="state.name" placeholder="Contoh: Mobile Legends, Futsal" />
          </UFormField>

          <UFormField label="Tipe" name="type" required>
            <USelect
              v-model="state.type"
              :options="[
                { label: 'E-Sport', value: 'e-sport' },
                { label: 'Tradisional', value: 'traditional' },
                { label: 'Lainnya', value: 'miscellaneous' }
              ]"
              option-attribute="label"
              value-attribute="value"
            />
          </UFormField>

          <UFormField label="URL Icon (Opsional)" name="icon_url">
            <UInput v-model="state.icon_url" placeholder="https://..." />
          </UFormField>

          <UFormField label="Status" name="is_active">
            <UCheckbox v-model="state.is_active" label="Aktifkan cabang olahraga ini" />
          </UFormField>

          <div class="flex justify-end gap-3 pt-4">
            <UButton label="Batal" color="neutral" variant="ghost" @click="isAddModalOpen = false" />
            <UButton type="submit" label="Simpan" color="primary" :loading="isLoading" />
          </div>
        </UForm>
      </UCard>
    </UModal>

    <!-- Edit Modal -->
    <UModal v-model:open="isEditModalOpen">
      <UCard :ui="{ body: 'sm:p-6' }">
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold leading-6 text-default">
              Edit Cabang Olahraga
            </h3>
            <UButton
              color="neutral"
              variant="ghost"
              icon="i-lucide-x"
              class="-my-1"
              @click="isEditModalOpen = false"
            />
          </div>
        </template>

        <UForm :state="state" class="space-y-4" @submit="onSubmitEdit">
          <UFormField label="Nama Cabang" name="name" required>
            <UInput v-model="state.name" />
          </UFormField>

          <UFormField label="Tipe" name="type" required>
            <USelect
              v-model="state.type"
              :options="[
                { label: 'E-Sport', value: 'e-sport' },
                { label: 'Tradisional', value: 'traditional' },
                { label: 'Lainnya', value: 'miscellaneous' }
              ]"
              option-attribute="label"
              value-attribute="value"
            />
          </UFormField>

          <UFormField label="URL Icon (Opsional)" name="icon_url">
            <UInput v-model="state.icon_url" />
          </UFormField>

          <UFormField label="Status" name="is_active">
            <UCheckbox v-model="state.is_active" label="Aktifkan cabang olahraga ini" />
          </UFormField>

          <div class="flex justify-end gap-3 pt-4">
            <UButton label="Batal" color="neutral" variant="ghost" @click="isEditModalOpen = false" />
            <UButton type="submit" label="Perbarui" color="primary" :loading="isLoading" />
          </div>
        </UForm>
      </UCard>
    </UModal>
  </UDashboardPage>
</template>
