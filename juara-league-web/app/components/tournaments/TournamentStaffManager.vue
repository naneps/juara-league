<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'

const props = defineProps<{
  tournamentSlug: string
  initialStaff?: any[]
}>()

const tournamentStore = useTournamentStore()
const staff = ref<any[]>(props.initialStaff || [])
const isSubmitting = ref(false)

const newStaff = ref({
  email: '',
  role: 'referee'
})

const roles = [
  { label: 'Co-Organizer', value: 'co_organizer' },
  { label: 'Referee', value: 'referee' }
]

const selectedRole = computed({
  get: () => roles.find(r => r.value === newStaff.value.role) || roles[0],
  set: (val: any) => { if (val) newStaff.value.role = val.value }
})

const fetchStaff = async () => {
  try {
    const data = await tournamentStore.fetchStaff(props.tournamentSlug)
    staff.value = data
  } catch (e) {
    console.error('Failed to fetch staff', e)
  }
}

const addStaff = async () => {
  if (!newStaff.value.email) return
  
  isSubmitting.value = true
  try {
    await tournamentStore.addStaff(props.tournamentSlug, newStaff.value)
    useToast().add({ title: 'Berhasil', description: 'Staf baru telah ditambahkan.', color: 'success' })
    newStaff.value.email = ''
    await fetchStaff()
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal menambahkan staf', color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const removeStaff = async (userId: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus staf ini?')) return
  
  try {
    await tournamentStore.removeStaff(props.tournamentSlug, userId)
    useToast().add({ title: 'Berhasil', description: 'Staf telah dihapus.', color: 'success' })
    await fetchStaff()
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal menghapus staf', color: 'error' })
  }
}

const getRoleLabel = (role: string) => {
  return roles.find(r => r.value === role)?.label || role
}
</script>

<template>
  <div class="space-y-12">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <h2 class="text-2xl font-black text-white uppercase tracking-tight mb-2">Manajemen Staf</h2>
        <p class="text-neutral-500 font-medium text-sm">Kelola tim yang membantu Anda menyelenggarakan turnamen ini.</p>
      </div>
      
      <div class="flex items-center gap-2 bg-neutral-900/50 p-2 rounded-2xl ring-1 ring-white/5">
        <UInput 
          v-model="newStaff.email" 
          placeholder="Email Pengguna" 
          variant="none"
          class="min-w-[250px]"
          :disabled="isSubmitting"
        />
        <USelectMenu 
          v-model="selectedRole" 
          :items="roles" 
          class="w-40"
          variant="none"
        />
        <UButton 
          color="primary" 
          icon="i-lucide-plus" 
          class="rounded-xl font-black uppercase tracking-widest px-6"
          :loading="isSubmitting"
          @click="addStaff"
        >
          Tambah
        </UButton>
      </div>
    </div>

    <!-- Staff List -->
    <div v-if="staff.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="member in staff" :key="member.id" class="bg-neutral-900/40 rounded-[2rem] border border-white/5 p-6 group hover:border-primary-500/30 transition-all duration-500">
        <div class="flex items-center justify-between mb-6">
           <UAvatar 
            :src="member.user?.avatar || `https://i.pravatar.cc/150?u=${member.user?.id}`" 
            size="lg" 
            class="rounded-2xl ring-2 ring-white/5"
          />
          <UBadge 
            :color="member.role === 'co_organizer' ? 'primary' : 'neutral'" 
            variant="soft" 
            class="rounded-lg text-[10px] font-black uppercase tracking-widest px-3 py-1"
          >
            {{ getRoleLabel(member.role) }}
          </UBadge>
        </div>

        <div class="mb-6">
          <p class="text-white font-black text-lg truncate mb-1">{{ member.user?.name }}</p>
          <p class="text-neutral-500 font-medium text-xs truncate">{{ member.user?.email }}</p>
        </div>

        <div class="pt-6 border-t border-white/5 flex justify-end">
          <UButton 
            color="error" 
            variant="ghost" 
            icon="i-lucide-trash-2" 
            size="xs" 
            class="rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"
            @click="removeStaff(member.user.id)"
          />
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20 bg-neutral-900/20 rounded-[3rem] border border-dashed border-white/10">
      <div class="bg-neutral-900/50 p-10 rounded-full mb-8 ring-1 ring-white/5 inline-block mx-auto">
        <UIcon name="i-lucide-users-2" class="size-16 text-neutral-800" />
      </div>
      <h3 class="text-xl font-black text-white mb-2 uppercase tracking-tight">Belum Ada Staf</h3>
      <p class="text-neutral-600 font-bold uppercase tracking-widest text-[10px]">Anda masih sendirian dalam mengelola turnamen ini.</p>
    </div>
  </div>
</template>
