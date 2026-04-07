<script setup lang="ts">
import { useTeamStore } from '~/stores/team.store'
import type { Tournament } from '~/types/tournament'

const props = defineProps<{
  tournament: Tournament
  modelValue: boolean
}>()

const emit = defineEmits(['update:modelValue', 'success'])

const teamStore = useTeamStore()
const toast = useToast()
const { myTeams, isLoading: teamsLoading } = storeToRefs(teamStore)

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const state = reactive({
  team_id: undefined as number | undefined,
  notes: '',
  payment_proof_url: '' // Untuk penggunaan masa depan
})

const isSubmitting = ref(false)

const canSubmit = computed(() => {
  if (props.tournament.participant_type === 'team') {
    return !!state.team_id
  }
  return true
})

const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    await useApi(`/api/v1/tournaments/${props.tournament.slug}/participants`, {
      method: 'POST',
      body: {
        team_id: state.team_id,
        notes: state.notes,
        payment_proof_url: state.payment_proof_url
      }
    })
    
    toast.add({
      title: 'Registrasi Berhasil!',
      description: 'Pendaftaran Anda sedang menunggu moderasi.',
      color: 'success'
    })
    
    emit('success')
    isOpen.value = false
  } catch (e: any) {
    toast.add({
      title: 'Registrasi Gagal',
      description: e.data?.message || 'Gagal mengirim pendaftaran',
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  if (props.tournament.participant_type === 'team') {
    await teamStore.fetchMyTeams()
  }
})
</script>

<template>
  <UModal 
    v-model:open="isOpen" 
    :ui="{ content: 'rounded-[2.5rem] bg-white dark:bg-neutral-900 border-none' }"
    class="sm:max-w-md"
  >
    <template #body>
      <div class="space-y-6 relative">
          <!-- Decoration -->
        <div class="absolute -top-24 -right-24 size-48 bg-primary-500/10 blur-[80px] rounded-full pointer-events-none"></div>

        <header class="mb-2 relative z-10">
          <div class="flex items-center justify-between mb-4">
             <div class="size-12 rounded-2xl bg-primary-500/10 flex items-center justify-center border border-primary-500/20">
                <UIcon :name="tournament.participant_type === 'team' ? 'i-lucide-users' : 'i-lucide-user'" class="text-primary-500 size-6" />
             </div>
             <UBadge :color="tournament.participant_type === 'team' ? 'primary' : 'neutral'" variant="subtle" class="rounded-full uppercase font-black tracking-widest text-[10px] px-3">
                {{ tournament.participant_type === 'team' ? 'Team Registration' : 'Individual Registration' }}
             </UBadge>
          </div>
          <h2 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tight leading-none mb-1">
            Join {{ tournament.title }}
          </h2>
          <p class="text-neutral-500 font-medium text-xs">Silakan lengkapi konfirmasi pendaftaran di bawah ini.</p>
        </header>

        <!-- Team Selector Slot -->
        <div v-if="tournament.participant_type === 'team'" class="space-y-4">
          <div class="bg-primary-500/5 p-4 rounded-2xl border border-primary-500/10 flex items-center gap-4">
             <div class="size-10 rounded-xl bg-primary-500/20 flex items-center justify-center">
                <UIcon name="i-lucide-users-2" class="text-primary-400 size-5" />
             </div>
             <div>
                <p class="text-[10px] font-black text-neutral-500 uppercase tracking-widest leading-none mb-1">Team Size Limit</p>
                <p class="text-sm font-black text-white uppercase">{{ tournament.team_size || 'Unlimited' }} Anggota / Tim</p>
             </div>
          </div>

          <div class="space-y-2">
            <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">Pilih Tim Pendaftar</label>
            <USelectMenu
              v-model="state.team_id"
              :items="myTeams"
              value-key="id"
              label-key="name"
              placeholder="Pilih Tim Anda..."
              size="xl"
              class="w-full"
              :ui="{ base: 'rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-white/5' }"
            >
              <template #leading>
                <UIcon name="i-lucide-shield-check" class="text-primary-500" />
              </template>
            </USelectMenu>
            <div v-if="!myTeams.length && !teamsLoading" class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3">
                <UIcon name="i-lucide-alert-circle" class="text-amber-500 size-4 shrink-0 mt-0.5" />
                <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold leading-normal">
                    Anda belum memiliki tim yang aktif. Silakan buat tim di Dashboard sebelum mendaftar.
                </p>
            </div>
          </div>
        </div>

        <!-- Individual Slot -->
        <div v-else-if="tournament.participant_type === 'individual'" class="bg-neutral-50 dark:bg-neutral-800/30 p-5 rounded-2xl border border-neutral-200 dark:border-white/5">
          <div class="flex items-center gap-3">
            <div class="size-10 rounded-full bg-primary-500/10 flex items-center justify-center ring-1 ring-primary-500/20">
                <UIcon name="i-lucide-user-check" class="text-primary-500 size-5" />
            </div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 font-medium leading-relaxed">
              Anda akan mendaftar sebagai **Individu**. Pastikan nama akun Anda sesuai dengan identitas asli.
            </p>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">Catatan Tambahan (Opsional)</label>
          <UTextarea
            v-model="state.notes"
            placeholder="Tambahkan catatan jika ada..."
            size="xl"
            class="w-full"
            :rows="3"
            :ui="{ base: 'rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border-neutral-200 dark:border-white/5' }"
          />
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex flex-col gap-3 w-full">
        <UButton
          color="primary"
          block
          size="xl"
          class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-xl shadow-primary-500/20"
          :loading="isSubmitting"
          :disabled="!canSubmit"
          @click="handleSubmit"
        >
          Kirim Pendaftaran
        </UButton>
        <UButton
          color="neutral"
          variant="ghost"
          block
          size="xl"
          class="rounded-2xl font-bold uppercase tracking-widest text-[10px] py-2"
          @click="isOpen = false"
        >
          Batal
        </UButton>
      </div>
    </template>
  </UModal>
</template>
