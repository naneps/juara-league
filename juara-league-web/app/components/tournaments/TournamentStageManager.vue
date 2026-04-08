<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { Stage } from '~/types/tournament'

const props = defineProps<{
  tournamentSlug: string
  initialStages?: Stage[]
}>()

const tournamentStore = useTournamentStore()
const stages = ref<Stage[]>(props.initialStages || [])
const isAdding = ref(false)
const isSubmitting = ref(false)

const newStage = ref({ name: '', type: 'single_elim', settings: { bo: 1 } })

const stageTypes = [
  { 
    label: 'Single Elimination', 
    value: 'single_elim', 
    icon: 'i-lucide-skull', 
    description: 'Sistem gugur klasik. Kalah sekali langsung tereliminasi. Sempurna untuk Playoff yang cepat dan menegangkan.' 
  },
  { 
    label: 'Double Elimination', 
    value: 'double_elim', 
    icon: 'i-lucide-refresh-ccw', 
    description: 'Memberi kesempatan kedua. Kalah sekali turun ke Lower Bracket. Format paling adil untuk menentukan juara sejati.' 
  },
  { 
    label: 'Round Robin', 
    value: 'round_robin', 
    icon: 'i-lucide-swatches', 
    description: 'Sistem Liga. Semua tim akan saling bertemu. Bagus untuk fase kualifikasi grup untuk melihat konsistensi tim.' 
  },
  { 
    label: 'Swiss System', 
    value: 'swiss', 
    icon: 'i-lucide-git-merge', 
    description: 'Bertanding melawan tim dengan rekor kemenangan yang sama (Misal: 1-0 vs 1-0). Sangat efisien jika jumlah tim banyak.' 
  },
]

const stageTemplates = [
  { 
    id: 'single_elim',
    title: 'Playoff Klasik', 
    description: 'Sistem gugur klasik. Sempurna untuk babak penentuan yang menegangkan.', 
    icon: 'i-lucide-skull', 
    type: 'single_elim',
    defaultName: 'Playoff',
    color: 'error'
  },
  { 
    id: 'double_elim',
    title: 'Double Chance', 
    description: 'Format Upper & Lower Bracket. Memberi kesempatan kedua bagi tim yang kalah.', 
    icon: 'i-lucide-refresh-ccw', 
    type: 'double_elim',
    defaultName: 'Final Playoff',
    color: 'primary'
  },
  { 
    id: 'round_robin',
    title: 'Liga / Fase Grup', 
    description: 'Semua peserta saling bertanding. Cara terbaik menyaring tim paling konsisten.', 
    icon: 'i-lucide-swatches', 
    type: 'round_robin',
    defaultName: 'Group Stage',
    color: 'amber'
  },
  { 
    id: 'swiss',
    title: 'Elite Swiss', 
    description: 'Bertanding melawan lawan seimbang. Paling adil untuk jumlah peserta yang banyak.', 
    icon: 'i-lucide-git-merge', 
    type: 'swiss',
    defaultName: 'Swiss Stage',
    color: 'emerald'
  },
]

const isSelectingTemplate = ref(true)

const selectedStageType = computed({
  get: () => stageTypes.find(t => t.value === newStage.value.type) || stageTypes[0],
  set: (val: any) => { if (val) newStage.value.type = val.value },
})

const applyTemplate = (template: typeof stageTemplates[0]) => {
  newStage.value.name = template.defaultName
  newStage.value.type = template.type
  isSelectingTemplate.value = false
}

const resetAddState = () => {
  isAdding.value = false
  isSelectingTemplate.value = true
  newStage.value = { name: '', type: 'single_elim', settings: { bo: 1 } }
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
  } catch (e) {
    console.error('Failed to add stage', e)
  } finally {
    isSubmitting.value = false
  }
}

const removeStage = async (id: number) => {
  if (!confirm('Hapus tahapan ini? Semua bracket di babak ini akan hilang.')) return
  try {
    await tournamentStore.deleteStage(id)
    await fetchStages()
  } catch (e) {
    console.error('Failed to delete stage', e)
  }
}

const stageTypeInfo = (type: string) =>
  stageTypes.find(t => t.value === type) || { label: type.replace(/_/g, ' '), icon: 'i-lucide-layers', description: '' }

onMounted(() => {
  fetchStages()
})
</script>

<template>
  <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden flex flex-col">
    
    <!-- ── Header Section ── -->
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
          <UButton
            v-if="!isAdding"
            size="sm"
            color="primary"
            icon="i-lucide-plus"
            @click="isAdding = true; isSelectingTemplate = true"
          >
            Tambah Babak
          </UButton>
          <UButton
            v-if="!isAdding"
            size="sm"
            color="neutral"
            variant="ghost"
            icon="i-lucide-refresh-cw"
            @click="fetchStages"
          />
        </div>
      </div>
    </div>

    <div class="flex flex-col xl:flex-row divide-y xl:divide-y-0 xl:divide-x divide-neutral-100 dark:divide-neutral-800">
      
      <!-- ── Left Side: Form or List ── -->
      <div class="flex-1">
        
        <!-- Add Stage Flow -->
        <div v-if="isAdding" class="p-6">
          
          <!-- Step 1: Template Selection -->
          <div v-if="isSelectingTemplate" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="mb-8">
              <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">Pilih Template Babak</h3>
              <p class="text-xs text-neutral-500">Gunakan template untuk mempercepat setup turnamen Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                v-for="template in stageTemplates"
                :key="template.id"
                @click="applyTemplate(template)"
                class="flex flex-col text-left p-5 rounded-3xl border border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-900 hover:border-primary-500 dark:hover:border-primary-500 hover:ring-4 hover:ring-primary-500/5 transition-all group relative overflow-hidden"
              >
                <!-- Background Decoration -->
                <div class="absolute -top-4 -right-4 size-24 bg-neutral-50 dark:bg-neutral-800 rounded-full group-hover:bg-primary-500/10 transition-colors" />
                
                <div class="size-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4 group-hover:bg-primary-500 group-hover:text-white transition-all">
                  <UIcon :name="template.icon" class="size-6" />
                </div>
                
                <p class="text-sm font-bold text-neutral-900 dark:text-white mb-1">{{ template.title }}</p>
                <p class="text-[11px] text-neutral-400 dark:text-neutral-500 leading-relaxed">{{ template.description }}</p>
                
                <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-primary-500 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all">
                  Gunakan Template
                  <UIcon name="i-lucide-arrow-right" class="size-3" />
                </div>
              </button>

              <!-- Custom Option -->
              <button
                @click="isSelectingTemplate = false"
                class="flex items-center gap-4 p-5 rounded-3xl border border-dashed border-neutral-200 dark:border-neutral-800 hover:border-primary-500 hover:bg-primary-50/10 transition-all group"
              >
                <div class="size-12 rounded-2xl border border-dashed border-neutral-200 dark:border-neutral-800 flex items-center justify-center group-hover:border-primary-500 group-hover:text-primary-500 transition-all">
                  <UIcon name="i-lucide-settings-2" class="size-6" />
                </div>
                <div>
                  <p class="text-sm font-bold text-neutral-900 dark:text-white">Custom Setup</p>
                  <p class="text-[11px] text-neutral-400">Konfigurasi manual dari awal.</p>
                </div>
              </button>
            </div>

            <div class="mt-10 flex justify-center">
              <UButton variant="ghost" color="neutral" size="sm" @click="resetAddState">Batal</UButton>
            </div>
          </div>

          <!-- Step 2: Final Configuration -->
          <div v-else class="animate-in fade-in slide-in-from-right-4 duration-500">
            <div class="flex items-center gap-3 mb-8">
              <UButton
                variant="ghost"
                color="neutral"
                icon="i-lucide-arrow-left"
                size="sm"
                class="-ml-2"
                @click="isSelectingTemplate = true"
              />
              <div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white leading-none mb-1">Konfigurasi Babak</h3>
                <p class="text-[10px] text-neutral-500">Sesuaikan detail nama untuk babak ini.</p>
              </div>
            </div>

            <div class="space-y-6">
              <UFormGroup label="Nama Babak" name="name" help="Nama ini akan tampil di publik (Contoh: Semifinal B)">
                <UInput
                  v-model="newStage.name"
                  placeholder="Masukkan nama babak..."
                  icon="i-lucide-edit-3"
                  size="xl"
                  class="w-full"
                  :ui="{ base: 'font-bold' }"
                />
              </UFormGroup>

              <UFormGroup label="Format Pertandingan" name="type">
                <USelectMenu
                  v-model="selectedStageType"
                  :items="stageTypes"
                  :icon="selectedStageType?.icon"
                  size="xl"
                  class="w-full"
                  :ui="{ base: 'font-bold' }"
                >
                  <template #item="{ item }">
                    <div class="py-1">
                      <div class="flex items-center gap-2 mb-0.5">
                        <UIcon :name="item.icon" class="size-4 text-neutral-400" />
                        <span class="font-semibold text-sm">{{ item.label }}</span>
                      </div>
                      <p class="text-[10px] text-neutral-500 leading-tight">{{ item.description }}</p>
                    </div>
                  </template>
                </USelectMenu>
              </UFormGroup>
              
              <div v-if="selectedStageType" class="p-4 rounded-xl bg-primary-50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-900/20">
                <div class="flex gap-3">
                  <UIcon name="i-lucide-info" class="size-5 text-primary-500 shrink-0 mt-0.5" />
                  <div>
                    <p class="text-xs font-bold text-primary-700 dark:text-primary-400 mb-1">Detail Format: {{ selectedStageType.label }}</p>
                    <p class="text-xs text-primary-600/80 dark:text-primary-400/70 leading-relaxed italic">
                      "{{ selectedStageType.description }}"
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end gap-3 pt-6 border-t border-neutral-100 dark:border-neutral-800">
                <UButton variant="ghost" color="neutral" @click="resetAddState">Batal</UButton>
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
            class="flex items-center justify-between px-6 py-5 hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition-all group"
          >
            <div class="flex items-center gap-4">
              <div class="size-10 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-sm font-black text-neutral-400 dark:text-neutral-500 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 group-hover:text-primary-500 transition-colors">
                {{ index + 1 }}
              </div>
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <p class="text-sm font-bold text-neutral-900 dark:text-white leading-none">{{ stage.name }}</p>
                  <UBadge color="neutral" variant="subtle" size="xs">{{ stageTypeInfo(stage.type).label }}</UBadge>
                </div>
                <p class="text-[11px] text-neutral-400 dark:text-neutral-500 italic max-w-sm line-clamp-1">
                  {{ stageTypeInfo(stage.type).description }}
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <UButton
                color="neutral"
                variant="ghost"
                icon="i-lucide-settings"
                size="xs"
                label="Settings"
              />
              <UButton
                color="error"
                variant="ghost"
                icon="i-lucide-trash-2"
                size="xs"
                @click="removeStage(stage.id)"
              />
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

      <!-- ── Right Side: Educational Section ── -->
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
                <p class="text-[11px] text-neutral-500 leading-relaxed italic">
                  <strong>Group Stage (Round Robin)</strong> di awal untuk menyaring tim terbaik.
                </p>
              </div>
              <div class="flex items-start gap-2">
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0" />
                <p class="text-[11px] text-neutral-500 leading-relaxed italic">
                  <strong>Playoff (Single/Double Elim)</strong> sebagai babak penentuan juara.
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <p class="text-xs font-bold text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
              <UIcon name="i-lucide-shield-info" class="size-4 text-blue-500" />
              Apa itu Babak?
            </p>
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
              Babak adalah satu fase kompetisi. Anda bisa menumpuk beberapa babak untuk menciptakan turnamen yang kompleks seperti Piala Dunia atau MPL.
            </p>
          </div>

          <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20">
            <p class="text-[10px] font-bold text-amber-700 dark:text-amber-500 flex items-center gap-1 mb-1">
              <UIcon name="i-lucide-alert-circle" />
              PENTING
            </p>
            <p class="text-[10px] text-amber-600/80 dark:text-amber-500/70 leading-relaxed">
              Jumlah tim yang lolos dari babak sebelumnya harus sesuai dengan slot yang tersedia di babak berikutnya.
            </p>
          </div>
        </div>
      </div>

    </div>

  </div>
</template>

