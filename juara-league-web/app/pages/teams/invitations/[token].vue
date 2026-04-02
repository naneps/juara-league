<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const teamStore = useTeamStore()
const toast = useToast()

const token = route.params.token as string
const status = ref<'loading' | 'success' | 'error'>('loading')
const errorDetail = ref('')
const joinedTeam = ref<any>(null)

onMounted(async () => {
  if (!token) {
    status.value = 'error'
    errorDetail.value = 'Token undangan tidak ditemukan.'
    return
  }

  try {
    joinedTeam.value = await teamStore.acceptInvitation(token)
    status.value = 'success'
    
    toast.add({
      title: 'Selamat!',
      description: `Anda telah bergabung dengan tim ${joinedTeam.value.name}`,
      color: 'success'
    })

    // Redirect otomatis setelah 3 detik
    setTimeout(() => {
      router.push('/dashboard/teams')
    }, 3000)
  } catch (e: any) {
    status.value = 'error'
    errorDetail.value = e.data?.message || 'Gagal menerima undangan. Token mungkin sudah kedaluwarsa atau sudah digunakan.'
  }
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-[60vh] px-4">
    <UCard class="w-full max-w-md bg-elevated shadow-xl border-default overflow-hidden relative">
      <!-- Glow effect for success/error -->
      <div 
        v-if="status !== 'loading'"
        class="absolute -top-12 -left-12 size-40 blur-3xl opacity-20 pointer-events-none"
        :class="status === 'success' ? 'bg-success' : 'bg-error'"
      />

      <template #body>
        <div class="flex flex-col items-center text-center py-6 gap-6">
          
          <!-- State: Loading -->
          <div v-if="status === 'loading'" class="space-y-4">
            <div class="relative flex items-center justify-center">
              <div class="size-16 rounded-full border-4 border-primary/20 border-t-primary animate-spin" />
              <UIcon name="i-lucide-mail-open" class="absolute size-6 text-primary" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-highlighted">Memproses Undangan...</h1>
              <p class="text-sm text-muted">Mohon tunggu sebentar sementara kami memvalidasi undangan Anda.</p>
            </div>
          </div>

          <!-- State: Success -->
          <div v-if="status === 'success'" class="space-y-4 animate-in fade-in zoom-in duration-500">
            <div class="size-20 rounded-2xl bg-success/10 flex items-center justify-center mx-auto ring-1 ring-success/20">
              <UIcon name="i-lucide-party-popper" class="size-10 text-success" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-highlighted">Berhasil Bergabung!</h1>
              <p class="text-sm text-muted mt-2">
                Anda sekarang adalah bagian dari tim 
                <strong class="text-primary">{{ joinedTeam?.name }}</strong>.
              </p>
            </div>
            <p class="text-xs text-muted pt-4 border-t border-default/50">
              Mengarahkan Anda ke dashboard dalam beberapa detik...
            </p>
            <UButton 
              label="Buka Dashboard Tim" 
              color="primary" 
              class="w-full mt-4"
              to="/dashboard/teams"
            />
          </div>

          <!-- State: Error -->
          <div v-if="status === 'error'" class="space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-500">
            <div class="size-20 rounded-2xl bg-error/10 flex items-center justify-center mx-auto ring-1 ring-error/20">
              <UIcon name="i-lucide-shield-alert" class="size-10 text-error" />
            </div>
            <div>
              <h1 class="text-xl font-bold text-highlighted">Undangan Tidak Valid</h1>
              <p class="text-sm text-muted mt-2">{{ errorDetail }}</p>
            </div>
            <div class="flex gap-2 pt-4">
              <UButton 
                label="Kembali ke Dashboard" 
                color="neutral" 
                variant="subtle"
                class="flex-1"
                to="/dashboard/teams"
              />
              <UButton 
                label="Hubungi Admin" 
                color="neutral" 
                variant="outline"
                class="flex-1"
              />
            </div>
          </div>

        </div>
      </template>
    </UCard>
  </div>
</template>
