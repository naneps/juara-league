<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore';
import type { Stage } from '~/types/tournament';

const props = defineProps<{
  tournamentSlug: string
  initialStages?: Stage[]
}>()

const emit = defineEmits(['stage-started'])

const tournamentStore = useTournamentStore()
const stages = ref<Stage[]>(props.initialStages || [])
const isAdding = ref(false)
const isSubmitting = ref(false)
const expandedStageId = ref<string | null>(null)

const newStage = ref({
  name: '',
  type: 'single_elim',
  bo_format: 'bo1',
  participants_advance: 4,
  groups_count: null as number | null,
  participants_per_group: null as number | null,
})

const stageTemplates = [
  { id: 'single_elim', title: 'Playoff Klasik', description: 'Sistem gugur klasik. Kalah sekali langsung tereliminasi.', icon: 'i-lucide-skull', type: 'single_elim', defaultName: 'Playoff', color: 'error' },
  { id: 'double_elim', title: 'Double Chance', description: 'Upper & Lower Bracket. Memberi kesempatan kedua.', icon: 'i-lucide-refresh-ccw', type: 'double_elim', defaultName: 'Final Playoff', color: 'primary' },
  { id: 'round_robin', title: 'Liga / Fase Grup', description: 'Semua peserta saling bertanding.', icon: 'i-lucide-grid-3x3', type: 'round_robin', defaultName: 'Group Stage', color: 'amber' },
  { id: 'swiss', title: 'Elite Swiss', description: 'Bertanding melawan lawan seimbang.', icon: 'i-lucide-git-merge', type: 'swiss', defaultName: 'Swiss Stage', color: 'emerald' },
]

const boFormats = [
  { label: 'Best of 1', value: 'bo1' },
  { label: 'Best of 3', value: 'bo3' },
  { label: 'Best of 5', value: 'bo5' },
  { label: 'Best of 7', value: 'bo7' },
]

const stageTypeInfo = (type: string) => {
  const map: Record<string, { label: string; icon: string; color: string }> = {
    single_elim: { label: 'Single Elimination', icon: 'i-lucide-skull', color: 'error' },
    double_elim: { label: 'Double Elimination', icon: 'i-lucide-refresh-ccw', color: 'primary' },
    round_robin: { label: 'Round Robin', icon: 'i-lucide-grid-3x3', color: 'amber' },
    swiss: { label: 'Swiss', icon: 'i-lucide-git-merge', color: 'emerald' },
  }
  return map[type] || { label: type, icon: 'i-lucide-layers', color: 'neutral' }
}

const stageStatusInfo = (status: string) => {
  const map: Record<string, { label: string; color: string; icon: string }> = {
    pending: { label: 'Belum Mulai', color: 'neutral', icon: 'i-lucide-clock' },
    ongoing: { label: 'Berlangsung', color: 'primary', icon: 'i-lucide-play' },
    completed: { label: 'Selesai', color: 'success', icon: 'i-lucide-check-circle' },
  }
  return map[status] || { label: status, color: 'neutral', icon: 'i-lucide-circle' }
}

const isSelectingTemplate = ref(true)

const applyTemplate = (template: typeof stageTemplates[0]) => {
  newStage.value.name = template.defaultName
  newStage.value.type = template.type
  isSelectingTemplate.value = false
}

const resetAddState = () => {
  isAdding.value = false
  isSelectingTemplate.value = true
  newStage.value = { name: '', type: 'single_elim', bo_format: 'bo1', participants_advance: 4, groups_count: null, participants_per_group: null }
}

const fetchStages = async () => {
  try {
    const data = await tournamentStore.fetchStages(props.tournamentSlug)
    stages.value = data
  } catch (e) {
    console.error('Failed to fetch stages', e)
  }
}

const addStage = async () => {
  if (!newStage.value.name.trim()) return
  isSubmitting.value = true
  try {
    await tournamentStore.createStage(props.tournamentSlug, newStage.value)
    await fetchStages()
    resetAddState()
    useToast().add({ title: 'Berhasil', description: 'Babak berhasil ditambahkan.', color: 'success' })
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal menambah babak', color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const removeStage = async (stage: Stage) => {
  if (!confirm('Hapus tahapan ini? Semua bracket di babak ini akan hilang.')) return
  try {
    await tournamentStore.deleteStage(props.tournamentSlug, stage.id)
    await fetchStages()
    useToast().add({ title: 'Dihapus', description: 'Babak berhasil dihapus.', color: 'success' })
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal menghapus babak', color: 'error' })
  }
}

const isStarting = ref(false)
const startStage = async (stage: Stage) => {
  if (!confirm(`Mulai babak "${stage.name}"? Bracket akan digenerate dan tidak bisa diubah.`)) return
  isStarting.value = true
  try {
    const result = await tournamentStore.startStage(props.tournamentSlug, stage.id)
    await fetchStages()
    useToast().add({ title: 'Stage Dimulai!', description: `${result.matches_generated} match telah digenerate.`, color: 'success' })
    emit('stage-started', stage.id)
  } catch (e: any) {
    useToast().add({ title: 'Gagal', description: e.data?.message || 'Gagal memulai stage', color: 'error' })
  } finally {
    isStarting.value = false
  }
}

const toggleExpand = (stageId: string) => {
  expandedStageId.value = expandedStageId.value === stageId ? null : stageId
}

onMounted(() => { fetchStages() })
</script>

<template>
  <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden flex flex-col">
    
    <!-- ── Header ── -->
    <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/50 dark:bg-neutral-800/20">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-base font-bold text-neutral-900 dark:text-white flex items-center gap-2">
            <UIcon name="i-lucide-workflow" class="size-5 text-primary-500" />
            Kelola Babak / Tahapan
          </h2>
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            Atur alur pertandingan mulai dari kualifikasi hingga babak final.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <UButton v-if="!isAdding" size="sm" color="primary" icon="i-lucide-plus" @click="isAdding = true; isSelectingTemplate = true">
            Tambah Babak
          </UButton>
          <UButton v-if="!isAdding" size="sm" color="neutral" variant="ghost" icon="i-lucide-refresh-cw" @click="fetchStages" />
        </div>
      </div>
    </div>

    <div class="flex flex-col xl:flex-row divide-y xl:divide-y-0 xl:divide-x divide-neutral-100 dark:divide-neutral-800">
      
      <!-- ── Main Content ── -->
      <div class="flex-1">
        
        <!-- Add Stage Flow -->
        <div v-if="isAdding" class="p-8 md:p-10">
          
          <!-- Step 1: Template Selection -->
          <div v-if="isSelectingTemplate">
            <div class="mb-8">
              <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">Pilih Template Babak</h3>
              <p class="text-xs text-neutral-500">Gunakan template untuk setup cepat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                v-for="template in stageTemplates"
                :key="template.id"
                @click="applyTemplate(template)"
                class="flex flex-col text-left p-5 rounded-3xl border border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-900 hover:border-primary-500 dark:hover:border-primary-500 hover:ring-4 hover:ring-primary-500/5 transition-all group relative overflow-hidden"
              >
                <div class="absolute -top-4 -right-4 size-24 bg-neutral-50 dark:bg-neutral-800 rounded-full group-hover:bg-primary-500/10 transition-colors" />
                <div class="size-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4 group-hover:bg-primary-500 group-hover:text-white transition-all">
                  <UIcon :name="template.icon" class="size-6" />
                </div>
                <p class="text-sm font-bold text-neutral-900 dark:text-white mb-1">{{ template.title }}</p>
                <p class="text-[11px] text-neutral-400 dark:text-neutral-500 leading-relaxed">{{ template.description }}</p>
                <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-primary-500 opacity-0 group-hover:opacity-100 transition-all">
                  Gunakan Template
                  <UIcon name="i-lucide-arrow-right" class="size-3" />
                </div>
              </button>
            </div>

            <div class="mt-10 flex justify-center">
              <UButton variant="ghost" color="neutral" size="sm" @click="resetAddState">Batal</UButton>
            </div>
          </div>

          <!-- Step 2: Configuration -->
          <div v-else>
            <div class="flex items-center gap-4 mb-10">
              <UButton variant="ghost" color="neutral" icon="i-lucide-arrow-left" size="sm" class="-ml-2" @click="isSelectingTemplate = true" />
              <div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white leading-none mb-1">Konfigurasi Babak</h3>
                <p class="text-[10px] text-neutral-500">Sesuaikan detail babak.</p>
              </div>
            </div>

            <div class="space-y-6">
              <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">Nama Babak</label>
                <UInput v-model="newStage.name" placeholder="Masukkan nama babak..." icon="i-lucide-edit-3" size="xl" class="w-full" />
              </div>

              <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">Format BO</label>
                <div class="grid grid-cols-4 gap-2">
                  <button
                    v-for="bo in boFormats"
                    :key="bo.value"
                    @click="newStage.bo_format = bo.value"
                    :class="[
                      'px-4 py-3 rounded-xl text-xs font-bold border transition-all text-center',
                      newStage.bo_format === bo.value
                        ? 'border-primary-500 bg-primary-500/10 text-primary-500'
                        : 'border-neutral-200 dark:border-neutral-700 text-neutral-500 hover:border-primary-300'
                    ]"
                  >
                    {{ bo.label }}
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">Peserta Advance</label>
                <UInput v-model.number="newStage.participants_advance" type="number" placeholder="4" icon="i-lucide-arrow-up-right" size="lg" class="w-full" />
              </div>

              <div v-if="newStage.type === 'round_robin'" class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">Jumlah Grup</label>
                  <UInput v-model.number="newStage.groups_count" type="number" placeholder="2" size="lg" class="w-full" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">Peserta / Grup</label>
                  <UInput v-model.number="newStage.participants_per_group" type="number" placeholder="4" size="lg" class="w-full" />
                </div>
              </div>

              <div class="flex items-center justify-end gap-4 pt-8 mt-8 border-t border-neutral-100 dark:border-neutral-800">
                <UButton variant="ghost" color="neutral" size="lg" @click="resetAddState">Batal</UButton>
                <UButton color="primary" size="lg" icon="i-lucide-save" :loading="isSubmitting" @click="addStage">Simpan Babak</UButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Stage list -->
        <div v-else-if="stages.length > 0" class="divide-y divide-neutral-100 dark:divide-neutral-800">
          <div
            v-for="(stage, index) in stages"
            :key="stage.id"
            class="group"
          >
            <!-- Stage Row -->
            <div
              class="flex items-center justify-between px-6 py-5 hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-all cursor-pointer"
              @click="toggleExpand(stage.id)"
            >
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-sm font-black text-neutral-400 dark:text-neutral-500 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 group-hover:text-primary-500 transition-colors">
                  {{ index + 1 }}
                </div>
                <div>
                  <div class="flex items-center gap-2 mb-1">
                    <p class="text-sm font-bold text-neutral-900 dark:text-white leading-none">{{ stage.name }}</p>
                    <UBadge :color="stageTypeInfo(stage.type).color as any" variant="subtle" size="xs">
                      <UIcon :name="stageTypeInfo(stage.type).icon" class="size-3 mr-1" />
                      {{ stageTypeInfo(stage.type).label }}
                    </UBadge>
                    <UBadge :color="stageStatusInfo(stage.status).color as any" variant="solid" size="xs">
                      <UIcon :name="stageStatusInfo(stage.status).icon" class="size-3 mr-1" />
                      {{ stageStatusInfo(stage.status).label }}
                    </UBadge>
                  </div>
                  <p class="text-[11px] text-neutral-400 dark:text-neutral-500">
                    {{ stage.bo_format?.toUpperCase() }} · {{ stage.participants_advance }} peserta advance
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <UIcon
                  :name="expandedStageId === stage.id ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
                  class="size-5 text-neutral-400 transition-transform"
                />
              </div>
            </div>

            <!-- Expanded Actions -->
            <div
              v-if="expandedStageId === stage.id"
              class="px-6 pb-5 border-t border-neutral-50 dark:border-neutral-800/50 bg-neutral-50/30 dark:bg-neutral-800/10"
            >
              <div class="flex flex-wrap items-center gap-2 pt-4">
                <!-- Start Stage -->
                <UButton
                  v-if="stage.status === 'pending'"
                  color="success"
                  size="sm"
                  icon="i-lucide-play"
                  :loading="isStarting"
                  @click.stop="startStage(stage)"
                >
                  Mulai Babak
                </UButton>

                <!-- View Bracket (when ongoing/completed) -->
                <UButton
                  v-if="stage.status === 'ongoing' || stage.status === 'completed'"
                  color="primary"
                  variant="outline"
                  size="sm"
                  icon="i-lucide-git-branch"
                  @click.stop="emit('stage-started', stage.id)"
                >
                  Lihat Bracket
                </UButton>

                <!-- Delete (only pending) -->
                <UButton
                  v-if="stage.status === 'pending'"
                  color="error"
                  variant="ghost"
                  size="sm"
                  icon="i-lucide-trash-2"
                  @click.stop="removeStage(stage)"
                >
                  Hapus
                </UButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="py-24 text-center">
          <div class="size-16 rounded-3xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mx-auto mb-4 border border-neutral-200 dark:border-neutral-700">
            <UIcon name="i-lucide-layers" class="size-8 text-neutral-300 dark:text-neutral-600" />
          </div>
          <p class="text-base font-bold text-neutral-900 dark:text-white mb-1">Belum Ada Babak</p>
          <p class="text-sm text-neutral-400 dark:text-neutral-500 max-w-xs mx-auto mb-6">
            Klik tombol di atas untuk mulai membuat tahapan turnamen Anda.
          </p>
          <UButton color="primary" variant="outline" icon="i-lucide-plus" @click="isAdding = true">Buat Babak Pertama</UButton>
        </div>
      </div>

      <!-- ── Right: Tips ── -->
      <div class="w-full xl:w-80 bg-neutral-50/50 dark:bg-neutral-800/10 p-6">
        <h4 class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
          <UIcon name="i-lucide-lightbulb" class="size-4 text-amber-500" />
          Tips & Contoh Alur
        </h4>
        <div class="space-y-6">
          <div class="p-4 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-sm">
            <p class="text-xs font-bold text-neutral-800 dark:text-neutral-200 mb-2">Pola Standard</p>
            <div class="space-y-3">
              <div class="flex items-start gap-2">
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0" />
                <p class="text-[11px] text-neutral-500 leading-relaxed"><strong>Group Stage (Round Robin)</strong> untuk menyaring tim terbaik.</p>
              </div>
              <div class="flex items-start gap-2">
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0" />
                <p class="text-[11px] text-neutral-500 leading-relaxed"><strong>Playoff (Single/Double Elim)</strong> sebagai babak final.</p>
              </div>
            </div>
          </div>

          <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20">
            <p class="text-[10px] font-bold text-amber-700 dark:text-amber-500 flex items-center gap-1 mb-1">
              <UIcon name="i-lucide-alert-circle" />
              PENTING
            </p>
            <p class="text-[10px] text-amber-600/80 dark:text-amber-500/70 leading-relaxed">
              Setelah babak dimulai, format dan konfigurasi tidak bisa diubah. Pastikan semuanya sudah benar sebelum klik "Mulai Babak".
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
