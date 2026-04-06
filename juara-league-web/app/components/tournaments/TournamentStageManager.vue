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

const newStage = ref({
  name: '',
  type: 'single_elim',
  settings: { bo: 1 }
})

const stageTypes = [
  { label: 'Single Elimination', value: 'single_elim' },
  { label: 'Double Elimination', value: 'double_elim' },
  { label: 'Round Robin', value: 'round_robin' },
  { label: 'Swiss', value: 'swiss' }
]

const fetchStages = async () => {
  try {
    const data = await tournamentStore.fetchStages(props.tournamentSlug)
    stages.value = data
  } catch (e) {
    console.error('Failed to fetch stages', e)
  }
}

const addStage = async () => {
  if (!newStage.value.name) return
  
  isSubmitting.value = true
  try {
    await tournamentStore.createStage(props.tournamentSlug, newStage.value)
    await fetchStages()
    isAdding.value = false
    newStage.value = { name: '', type: 'single_elim', settings: { bo: 1 } }
  } catch (e) {
    console.error('Failed to add stage', e)
  } finally {
    isSubmitting.value = false
  }
}

const removeStage = async (id: number) => {
  if (!confirm('Hapus stage ini?')) return
  
  try {
    await tournamentStore.deleteStage(id)
    await fetchStages()
  } catch (e) {
    console.error('Failed to delete stage', e)
  }
}
</script>

<template>
  <div class="space-y-8">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-black text-white uppercase tracking-tight">Tahapan Turnamen</h2>
      <UButton 
        v-if="!isAdding"
        color="primary" 
        icon="i-lucide-plus" 
        variant="soft" 
        class="rounded-xl font-bold uppercase tracking-widest text-xs"
        @click="isAdding = true"
      >
        Tambah Stage
      </UButton>
    </div>

    <!-- Add Stage Form -->
    <UCard v-if="isAdding" class="bg-neutral-900/50 border-white/5 rounded-[2rem] ring-0">
      <div class="space-y-6 p-4">
        <h3 class="text-lg font-black text-white uppercase tracking-tight">Stage Baru</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <UFormGroup label="Nama Stage" name="name">
            <UInput v-model="newStage.name" placeholder="Misal: Group Stage, Playoff" color="neutral" variant="outline" size="lg" />
          </UFormGroup>

          <UFormGroup label="Format" name="type">
            <USelectMenu v-model="newStage.type" :options="stageTypes" value-attribute="value" option-attribute="label" size="lg" />
          </UFormGroup>
        </div>

        <div class="flex justify-end gap-3 mt-8">
          <UButton color="neutral" variant="ghost" @click="isAdding = false">Batal</UButton>
          <UButton color="primary" :loading="isSubmitting" @click="addStage">Simpan Stage</UButton>
        </div>
      </div>
    </UCard>

    <!-- Stage List -->
    <div v-if="stages.length > 0" class="grid grid-cols-1 gap-4">
      <div 
        v-for="(stage, index) in stages" 
        :key="stage.id"
        class="group bg-neutral-900/40 p-6 rounded-2xl border border-white/5 flex items-center justify-between hover:border-primary-500/30 transition-all duration-300"
      >
        <div class="flex items-center gap-6">
          <div class="size-12 rounded-xl bg-neutral-800 flex items-center justify-center font-black text-neutral-500 group-hover:text-primary-500 transition-colors">
            {{ index + 1 }}
          </div>
          <div>
            <h4 class="text-white font-black uppercase tracking-tight leading-none mb-2">{{ stage.name }}</h4>
            <span class="text-[10px] text-neutral-500 font-bold uppercase tracking-widest">{{ stage.type.replace('_', ' ') }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <UButton color="neutral" variant="ghost" icon="i-lucide-trash-2" size="xs" color-hover="red" @click="removeStage(stage.id)" />
        </div>
      </div>
    </div>

    <div v-else-if="!isAdding" class="text-center py-12 bg-neutral-900/20 rounded-[2rem] border border-dashed border-white/10">
      <UIcon name="i-lucide-layers" class="size-12 text-neutral-800 mb-4" />
      <p class="text-neutral-600 font-bold uppercase tracking-widest text-xs">Belum ada tahapan yang dibuat</p>
    </div>
  </div>
</template>
