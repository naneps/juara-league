<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useTournamentStore } from '~/stores/tournamentStore'
import type { StoreTournamentPayload } from '~/types/tournament'

definePageMeta({
  middleware: 'auth'
})

const tournamentStore = useTournamentStore()
const { isLoading } = storeToRefs(tournamentStore)
const toast = useToast()

const state = reactive<StoreTournamentPayload>({
  title: '',
  description: '',
  category: 'E-sports',
  mode: 'online',
  bracket_type: 'single_elimination',
  max_participants: 16,
  prize_pool: 0,
  entry_fee: 0,
  start_at: '',
  venue: '',
  banner_url: ''
})

const categories = ['Sepak Bola', 'Badminton', 'Basket', 'E-sports', 'Lainnya']
const modes = [
  { label: 'Online', value: 'online' },
  { label: 'Offline / On-site', value: 'offline' }
]
const bracketTypes = [
  { label: 'Single Elimination', value: 'single_elimination' },
  { label: 'Double Elimination', value: 'double_elimination' },
  { label: 'Round Robin', value: 'round_robin' },
  { label: 'Swiss System', value: 'swiss' }
]

const onSubmit = async () => {
  try {
    const result = await tournamentStore.createTournament(state)
    toast.add({
      title: 'Berhasil!',
      description: 'Turnamen Anda telah berhasil dibuat.',
      color: 'success'
    })
    navigateTo(`/tournaments/${result.slug}`)
  } catch (e: any) {
    toast.add({
      title: 'Gagal Membuat Turnamen',
      description: e.data?.message || 'Terjadi kesalahan sistem.',
      color: 'error'
    })
  }
}
</script>

<template>
  <div class="min-h-screen bg-neutral-950 pt-32 pb-20 relative overflow-hidden">
    <!-- Gradient Backgrounds -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-500/10 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-primary-600/5 blur-[100px] rounded-full"></div>

    <div class="max-w-4xl mx-auto px-4 relative z-10">
      <div class="mb-12">
        <UButton 
          to="/tournaments" 
          icon="i-lucide-arrow-left" 
          variant="ghost" 
          color="neutral" 
          class="mb-6 hover:translate-x-[-4px] transition-transform"
        >
          Kembali ke Daftar
        </UButton>
        <h1 class="text-4xl font-black text-white uppercase tracking-tight mb-2">
          Buat <span class="text-primary-500">Turnamen Baru</span>
        </h1>
        <p class="text-neutral-500 font-medium">Lengkapi detail turnamen Anda untuk mulai mengumpulkan peserta.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-2 space-y-6">
          <UCard :ui="{ 
            root: 'bg-neutral-900/40 backdrop-blur-3xl border-white/5 shadow-2xl rounded-[2rem]',
            body: 'p-8'
          }">
            <UForm :state="state" class="space-y-8" @submit="onSubmit">
              <!-- General Info -->
              <div class="space-y-6">
                <div class="flex items-center gap-2 mb-4">
                  <UIcon name="i-lucide-info" class="text-primary-500 size-5" />
                  <h3 class="text-lg font-bold text-white uppercase tracking-wider">Informasi Umum</h3>
                </div>

                <UFormGroup label="Judul Turnamen" name="title" required>
                  <UInput v-model="state.title" placeholder="Contoh: Juara Cup 2026" size="xl" class="w-full" />
                </UFormGroup>

                <div class="grid grid-cols-2 gap-4">
                  <UFormGroup label="Kategori" name="category" required>
                    <USelectMenu v-model="state.category" :options="categories" size="xl" />
                  </UFormGroup>
                  <UFormGroup label="Mode" name="mode" required>
                    <USelectMenu v-model="state.mode" :options="modes" value-attribute="value" option-attribute="label" size="xl" />
                  </UFormGroup>
                </div>

                <UFormGroup label="Deskripsi" name="description" required>
                  <UTextarea v-model="state.description" placeholder="Jelaskan tentang turnamen Anda..." :rows="6" size="xl" />
                </UFormGroup>
              </div>

              <div class="h-px bg-white/5"></div>

              <!-- Competition Rules -->
              <div class="space-y-6">
                <div class="flex items-center gap-2 mb-4">
                  <UIcon name="i-lucide-settings-2" class="text-primary-500 size-5" />
                  <h3 class="text-lg font-bold text-white uppercase tracking-wider">Aturan Kompetisi</h3>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <UFormGroup label="Format Bracket" name="bracket_type" required>
                    <USelectMenu v-model="state.bracket_type" :options="bracketTypes" value-attribute="value" option-attribute="label" size="xl" />
                  </UFormGroup>
                  <UFormGroup label="Maksimal Peserta" name="max_participants" required>
                    <UInput v-model.number="state.max_participants" type="number" size="xl" />
                  </UFormGroup>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <UFormGroup label="Prize Pool (IDR)" name="prize_pool">
                    <UInput v-model.number="state.prize_pool" type="number" size="xl" icon="i-lucide-trophy" />
                  </UFormGroup>
                  <UFormGroup label="Biaya Pendaftaran (IDR)" name="entry_fee">
                    <UInput v-model.number="state.entry_fee" type="number" size="xl" icon="i-lucide-ticket" />
                  </UFormGroup>
                </div>
              </div>

              <div class="h-px bg-white/5"></div>

              <!-- Schedule & Location -->
              <div class="space-y-6">
                <div class="flex items-center gap-2 mb-4">
                  <UIcon name="i-lucide-calendar" class="text-primary-500 size-5" />
                  <h3 class="text-lg font-bold text-white uppercase tracking-wider">Jadwal & Lokasi</h3>
                </div>

                <UFormGroup label="Waktu Dimulai" name="start_at" required>
                  <UInput v-model="state.start_at" type="datetime-local" size="xl" />
                </UFormGroup>

                <UFormGroup v-if="state.mode === 'offline'" label="Lokasi / Venue" name="venue" required>
                  <UInput v-model="state.venue" placeholder="Nama gedung, kota, dsb." size="xl" icon="i-lucide-map-pin" />
                </UFormGroup>

                <UFormGroup label="URL Banner Turnamen" name="banner_url">
                  <UInput v-model="state.banner_url" placeholder="https://image-url.com/banner.png" size="xl" icon="i-lucide-image" />
                </UFormGroup>
              </div>

              <div class="pt-6">
                <UButton 
                  type="submit" 
                  color="primary" 
                  size="xl" 
                  block 
                  :loading="isLoading"
                  class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-[0_0_30px_-5px_rgba(234,179,8,0.3)]"
                >
                  Publikasikan Turnamen
                </UButton>
              </div>
            </UForm>
          </UCard>
        </div>

        <!-- Preview Column -->
        <div class="lg:col-span-1">
          <div class="sticky top-32 space-y-6">
            <h3 class="text-xs font-black text-neutral-500 uppercase tracking-[0.2em] mb-4">Preview Card</h3>
            <TournamentCard 
              :tournament="{
                id: 0,
                title: state.title || 'Judul Turnamen Anda',
                slug: 'preview',
                description: state.description,
                category: state.category,
                status: 'draft',
                mode: state.mode,
                bracket_type: state.bracket_type,
                venue: state.venue,
                banner_url: state.banner_url || 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1000',
                prize_pool: state.prize_pool,
                entry_fee: state.entry_fee,
                max_participants: state.max_participants,
                current_participants: 0,
                start_at: state.start_at,
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
                user: { id: 0, name: 'Anda (Organizer)', email: '' }
              }"
            />

            <div class="bg-primary-500/5 border border-primary-500/10 p-6 rounded-3xl">
              <div class="flex gap-4">
                <UIcon name="i-lucide-shield-check" class="text-primary-500 size-6 shrink-0" />
                <p class="text-xs text-neutral-400 leading-relaxed">
                  Turnamen Anda akan diproses dan dapat segera ditemukan oleh peserta setelah dipublikasikan. Pastikan data yang Anda masukkan sudah benar.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
