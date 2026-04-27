<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore';
import type { Stage } from '~/types/tournament';

const { t } = useI18n()

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
  settings: {
    match_format: 'best_of',
    win_condition: 1,
    scoring_method: 'score_based',
    advance_count: 4,
    rounds: null as number | null,
    rules: {
      allow_draw: false,
      extra_time: false,
      penalties: false,
    }
  },
  groups_count: null as number | null,
  participants_per_group: null as number | null,
})

const stageTemplates = computed(() => [
  { id: 'single_elim', title: t('managers.stages.templates.single_elim.title'), description: t('managers.stages.templates.single_elim.desc'), icon: 'i-lucide-skull', type: 'single_elim', defaultName: t('managers.stages.templates.single_elim.default'), color: 'error' },
  { id: 'double_elim', title: t('managers.stages.templates.double_elim.title'), description: t('managers.stages.templates.double_elim.desc'), icon: 'i-lucide-refresh-ccw', type: 'double_elim', defaultName: t('managers.stages.templates.double_elim.default'), color: 'primary' },
  { id: 'round_robin', title: t('managers.stages.templates.round_robin.title'), description: t('managers.stages.templates.round_robin.desc'), icon: 'i-lucide-grid-3x3', type: 'round_robin', defaultName: t('managers.stages.templates.round_robin.default'), color: 'amber' },
  { id: 'swiss', title: t('managers.stages.templates.swiss.title'), description: t('managers.stages.templates.swiss.desc'), icon: 'i-lucide-git-merge', type: 'swiss', defaultName: t('managers.stages.templates.swiss.default'), color: 'emerald' },
])

const matchFormats = [
  { label: 'Single Game', value: 'single_game', icon: 'i-lucide-swords' },
  { label: 'Best of X', value: 'best_of', icon: 'i-lucide-trophy' },
  { label: 'Home & Away', value: 'leg', icon: 'i-lucide-repeat' },
]

const scoringMethods = [
  { label: 'Score Based', value: 'score_based', description: 'Win by total score/goals' },
  { label: 'Result Based', value: 'result_based', description: 'Win by match outcome' },
  { label: 'Point Based', value: 'point_based', description: 'Win by accumulated points (3/1/0)' },
]

const boOptions = [
  { label: 'BO1', value: 1 },
  { label: 'BO3', value: 2 },
  { label: 'BO5', value: 3 },
  { label: 'BO7', value: 4 },
]

const stageTypeInfo = (type: string) => {
  const map: Record<string, { label: string; icon: string; color: string }> = {
    single_elim: { label: t('tournament_card.bracket_types.single'), icon: 'i-lucide-skull', color: 'error' },
    double_elim: { label: t('tournament_card.bracket_types.double'), icon: 'i-lucide-refresh-ccw', color: 'primary' },
    round_robin: { label: t('tournament_card.bracket_types.round_robin'), icon: 'i-lucide-grid-3x3', color: 'amber' },
    swiss: { label: 'Swiss', icon: 'i-lucide-git-merge', color: 'emerald' },
  }
  return map[type] || { label: type, icon: 'i-lucide-layers', color: 'neutral' }
}

const stageStatusInfo = (status: string) => {
  const map: Record<string, { label: string; color: string; icon: string }> = {
    pending: { label: t('managers.stages.statuses.pending'), color: 'neutral', icon: 'i-lucide-clock' },
    ongoing: { label: t('managers.stages.statuses.ongoing'), color: 'primary', icon: 'i-lucide-play' },
    completed: { label: t('managers.stages.statuses.completed'), color: 'success', icon: 'i-lucide-check-circle' },
  }
  return map[status] || { label: status, color: 'neutral', icon: 'i-lucide-circle' }
}

const isSelectingTemplate = ref(true)

const applyTemplate = (template: any) => {
  newStage.value.name = template.defaultName
  newStage.value.type = template.type
  isSelectingTemplate.value = false
}

const resetAddState = () => {
  isAdding.value = false
  isSelectingTemplate.value = true
  newStage.value = {
    name: '',
    type: 'single_elim',
    settings: {
      match_format: 'best_of',
      win_condition: 1,
      scoring_method: 'score_based',
      advance_count: 4,
      rounds: null,
      rules: {
        allow_draw: false,
        extra_time: false,
        penalties: false,
      }
    },
    groups_count: null,
    participants_per_group: null
  }
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
    // Transform payload to match backend requirements
    const winCondition = newStage.value.settings.win_condition
    const boNumber = (winCondition * 2) - 1
    
    const payload = {
      ...newStage.value,
      bo_format: `bo${boNumber}`,
      participants_advance: newStage.value.settings.advance_count,
    }

    await tournamentStore.createStage(props.tournamentSlug, payload)
    await fetchStages()
    resetAddState()
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.add_success'), color: 'success' })
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.add_failed'), color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const participants = ref<any[]>([])
const isSeedingModalOpen = ref(false)
const selectedStageForSeeding = ref<Stage | null>(null)
const manualSeeds = ref<{ participant_id: string; seed: number }[]>([])

// Advance Participants State
const isAdvanceModalOpen = ref(false)
const isAdvancing = ref(false)
const selectedStageToAdvance = ref<Stage | null>(null)
const selectableParticipants = ref<any[]>([])
const selectedAdvancingIds = ref<string[]>([])

const openAdvance = async (stage: Stage) => {
  selectedStageToAdvance.value = stage
  isAdvanceModalOpen.value = true
  selectedAdvancingIds.value = []
  selectableParticipants.value = []
  
  try {
    const standings = await tournamentStore.fetchStandings(props.tournamentSlug, stage.id)
    selectableParticipants.value = standings.map((s, idx) => ({
      id: s.participant_id,
      name: s.participant?.team?.name || s.participant?.user?.name || 'Unknown',
      points: s.points,
      rank: idx + 1
    }))
    
    // Pre-select top X based on advance_count
    const topX = stage.settings?.advance_count || stage.participants_advance || 0
    selectedAdvancingIds.value = selectableParticipants.value.slice(0, topX).map(p => p.id)
  } catch (e) {
    console.error('Failed to fetch selectable participants', e)
    // Fallback to basic participants if standings fail
    const res = await tournamentStore.fetchParticipants(props.tournamentSlug)
    selectableParticipants.value = res.map(p => ({
      id: p.id,
      name: p.team?.name || p.user?.name || 'Unknown',
      rank: '?'
    }))
  }
}

const confirmAdvance = async () => {
  if (!selectedStageToAdvance.value) return
  isAdvancing.value = true
  const toast = useToast()
  try {
    await tournamentStore.advanceParticipants(
      props.tournamentSlug, 
      selectedStageToAdvance.value.id, 
      selectedAdvancingIds.value
    )
    toast.add({ title: t('common.success'), description: t('managers.stages.advance_modal.toast_success'), color: 'success' })
    isAdvanceModalOpen.value = false
    emit('refresh')
  } catch (e: any) {
    toast.add({ title: t('common.error'), description: e.data?.message || t('managers.stages.advance_modal.toast_failed'), color: 'error' })
  } finally {
    isAdvancing.value = false
  }
}

const fetchParticipants = async () => {
  try {
    const data = await tournamentStore.fetchParticipants(props.tournamentSlug)
    participants.value = data.filter((p: any) => p.status === 'approved')
  } catch (e) {
    console.error('Failed to fetch participants', e)
  }
}

const openSeeding = async (stage: Stage) => {
  seedingStage.value = stage
  if (participants.value.length === 0) {
    await fetchParticipants()
  }
  // Sort by current seed or default to whatever is in the list
  localParticipants.value = [...participants.value].sort((a, b) => (a.seed || 999) - (b.seed || 999))
  isSeedingModalOpen.value = true
}

let draggedIndex: number | null = null
const dragStart = (index: number) => {
  draggedIndex = index
}
const drop = (index: number) => {
  if (draggedIndex === null) return
  const item = localParticipants.value.splice(draggedIndex, 1)[0]
  localParticipants.value.splice(index, 0, item)
  draggedIndex = null
}

const saveSeeding = async () => {
  if (!seedingStage.value) return
  isSubmitting.value = true
  try {
    const seeds = localParticipants.value.map(p => p.id)
    await tournamentStore.seedParticipants(props.tournamentSlug, seedingStage.value.id, seeds)
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.seed_success'), color: 'success' })
    isSeedingModalOpen.value = false
    await fetchStages()
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.seed_failed'), color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

// ── Matchup Preview Logic ──
const matchupPreview = computed(() => {
  if (!localParticipants.value.length || !seedingStage.value) return { type: 'none', data: [] }
  
  const type = seedingStage.value.type
  const participants = localParticipants.value
  const count = participants.length

  if (type === 'round_robin') {
    const groupsCount = seedingStage.value.groups_count || 1
    const groups = Array.from({ length: groupsCount }, (_, i) => ({
      name: `Grup ${String.fromCharCode(65 + i)}`,
      members: [] as any[]
    }))
    
    // Snake draft logic
    let direction = 1
    let groupIndex = 0
    participants.forEach((p) => {
      groups[groupIndex].members.push(p)
      groupIndex += direction
      if (groupIndex >= groupsCount) {
        groupIndex = groupsCount - 1
        direction = -1
      } else if (groupIndex < 0) {
        groupIndex = 0
        direction = 1
      }
    })
    return { type: 'groups', data: groups }
  } 
  
  if (type === 'single_elim' || type === 'double_elim') {
    const totalRounds = Math.ceil(Math.log2(count))
    const bracketSize = Math.pow(2, totalRounds)
    
    // Generate Seed Positions (Standard balanced bracket)
    const generateSeedPositions = (size: number): number[] => {
      if (size === 1) return [0]
      let positions = [0, 1]
      while (positions.length < size) {
        const newPositions = []
        const currentSize = positions.length
        for (const pos of positions) {
          newPositions.push(pos)
          newPositions.push(currentSize * 2 - 1 - pos)
        }
        positions = newPositions
      }
      return positions
    }

    const positions = generateSeedPositions(bracketSize)
    const matches = []
    for (let i = 0; i < bracketSize / 2; i++) {
      const p1Index = positions[i * 2]
      const p2Index = positions[i * 2 + 1]
      
      matches.push({
        label: `Match ${i + 1}`,
        p1: participants[p1Index] || { id: 'bye-' + i, team: { name: 'BYE' } },
        p2: participants[p2Index] || { id: 'bye-' + i + '-2', team: { name: 'BYE' } }
      })
    }
    return { type: 'matches', data: matches }
  }

  return { type: 'none', data: [] }
})

const isResetModalOpen = ref(false)
const stageToReset = ref<Stage | null>(null)
const openReset = (stage: Stage) => {
  stageToReset.value = stage
  isResetModalOpen.value = true
}

const isResetting = ref(false)
const resetStage = async () => {
  if (!stageToReset.value) return
  isResetting.value = true
  try {
    await tournamentStore.resetStage(props.tournamentSlug, stageToReset.value.id)
    await fetchStages()
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.reset_success'), color: 'success' })
    isResetModalOpen.value = false
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.reset_failed'), color: 'error' })
  } finally {
    isResetting.value = false
  }
}

const isShuffling = ref(false)
const shuffleParticipants = async (stage: Stage) => {
  isShuffling.value = true
  try {
    await tournamentStore.shuffleParticipants(props.tournamentSlug, stage.id)
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.shuffle_success'), color: 'success' })
    await fetchParticipants()
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.shuffle_failed'), color: 'error' })
  } finally {
    isShuffling.value = false
  }
}

const isDeleteModalOpen = ref(false)
const stageToDelete = ref<Stage | null>(null)
const openDelete = (stage: Stage) => {
  stageToDelete.value = stage
  isDeleteModalOpen.value = true
}

const removeStage = async () => {
  if (!stageToDelete.value) return
  try {
    await tournamentStore.deleteStage(props.tournamentSlug, stageToDelete.value.id)
    await fetchStages()
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.delete_success'), color: 'success' })
    isDeleteModalOpen.value = false
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.delete_failed'), color: 'error' })
  }
}

const isStartModalOpen = ref(false)
const stageToStart = ref<Stage | null>(null)
const openStart = (stage: Stage) => {
  stageToStart.value = stage
  isStartModalOpen.value = true
}

const isStarting = ref(false)
const startStage = async () => {
  if (!stageToStart.value) return
  isStarting.value = true
  try {
    const result = await tournamentStore.startStage(props.tournamentSlug, stageToStart.value.id)
    await fetchStages()
    useToast().add({ title: t('managers.stages.toast.start_success'), description: t('managers.stages.toast.matches_gen', { count: result.matches_generated }), color: 'success' })
    emit('stage-started', stageToStart.value.id)
    isStartModalOpen.value = false
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.start_failed'), color: 'error' })
  } finally {
    isStarting.value = false
  }
}

const toggleExpand = (stageId: string) => {
  expandedStageId.value = expandedStageId.value === stageId ? null : stageId
}

onMounted(() => { 
  fetchStages() 
  fetchParticipants()
})
</script>

<template>
  <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden flex flex-col">
    
    <!-- ── Header ── -->
    <div class="px-6 py-5 border-b border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-800/20">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-base font-bold text-neutral-900 dark:text-white flex items-center gap-2">
            <UIcon name="i-lucide-workflow" class="size-5 text-primary-500" />
            {{ $t('managers.stages_title') }}
          </h2>
          <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            {{ $t('managers.stages_desc') }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <UButton v-if="!isAdding" size="sm" color="primary" icon="i-lucide-plus" @click="isAdding = true; isSelectingTemplate = true">
            {{ $t('managers.add_stage') }}
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
              <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">{{ t('managers.stages.config.select_template') }}</h3>
              <p class="text-xs text-neutral-500">{{ t('managers.stages.config.select_template_desc') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <button
                v-for="template in stageTemplates"
                :key="template.id"
                @click="applyTemplate(template)"
                class="flex flex-col text-left p-5 rounded-3xl border border-neutral-100 dark:border-neutral-800 bg-white dark:bg-neutral-900 hover:border-primary-500 dark:hover:border-primary-500 hover:ring-4 hover:ring-primary-500/5 transition-all group relative overflow-hidden"
              >
                <div class="absolute -top-4 -right-4 size-24 bg-neutral-50 dark:bg-neutral-800 rounded-full group-hover:bg-primary-500/10 transition-colors"></div>
                <div class="size-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4 group-hover:bg-primary-500 group-hover:text-white transition-all">
                  <UIcon :name="template.icon" class="size-6" />
                </div>
                <p class="text-sm font-bold text-neutral-900 dark:text-white mb-1">{{ template.title }}</p>
                <p class="text-[11px] text-neutral-400 dark:text-neutral-500 leading-relaxed">{{ template.description }}</p>
                <div class="mt-4 flex items-center gap-1.5 text-[10px] font-bold text-primary-500 opacity-0 group-hover:opacity-100 transition-all">
                  {{ t('managers.stages.config.use_template') }}
                  <UIcon name="i-lucide-arrow-right" class="size-3" />
                </div>
              </button>
            </div>

            <div class="mt-10 flex justify-center">
              <UButton variant="ghost" color="neutral" size="sm" @click="resetAddState">{{ t('common.cancel') }}</UButton>
            </div>
          </div>

          <!-- Step 2: Configuration -->
          <div v-else>
            <div class="flex items-center gap-4 mb-10">
              <UButton variant="ghost" color="neutral" icon="i-lucide-arrow-left" size="sm" class="-ml-2" @click="isSelectingTemplate = true" />
              <div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white leading-none mb-1">{{ t('managers.stages.config.title') }}</h3>
                <p class="text-[10px] text-neutral-500">{{ t('managers.stages.config.subtitle') }}</p>
              </div>
            </div>

            <div class="space-y-6">
              <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">{{ t('managers.stages.config.name_label') }}</label>
                <UInput v-model="newStage.name" :placeholder="t('managers.stages.config.name_placeholder')" icon="i-lucide-edit-3" size="xl" class="w-full" />
              </div>

              <!-- Match Format & Scoring -->
              <!-- Match Format & Scoring -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <!-- Left Column: Match Format -->
                <div class="space-y-6">
                  <div>
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest mb-3">{{ t('managers.stages.config.match_format') }}</label>
                  </div>
                  <div class="space-y-2">
                    <button
                      v-for="format in matchFormats"
                      :key="format.value"
                      @click="newStage.settings.match_format = format.value"
                      :class="[
                        'w-full flex items-center gap-3 p-3 rounded-2xl border transition-all text-left group',
                        newStage.settings.match_format === format.value
                          ? 'border-primary-500 bg-primary-500/5 ring-1 ring-primary-500'
                          : 'border-neutral-100 dark:border-neutral-800 hover:border-primary-300'
                      ]"
                    >
                      <div :class="[
                        'size-10 rounded-xl flex items-center justify-center transition-colors',
                        newStage.settings.match_format === format.value ? 'bg-primary-500 text-white' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400 group-hover:text-primary-500'
                      ]">
                        <UIcon :name="format.icon" class="size-5" />
                      </div>
                      <span :class="['text-xs font-bold', newStage.settings.match_format === format.value ? 'text-primary-500' : 'text-neutral-600 dark:text-neutral-400']">
                        {{ format.label }}
                      </span>
                    </button>
                  </div>

                  <!-- BO Condition -->
                  <div v-if="newStage.settings.match_format === 'best_of'" class="mt-4 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-800">
                    <label class="block text-[10px] font-bold text-neutral-400 uppercase tracking-wider mb-3">{{ t('managers.stages.config.win_condition') }}</label>
                    <div class="grid grid-cols-4 gap-2">
                      <button
                        v-for="opt in boOptions"
                        :key="opt.value"
                        @click="newStage.settings.win_condition = opt.value"
                        :class="[
                          'py-2 rounded-xl text-[10px] font-black border transition-all',
                          newStage.settings.win_condition === opt.value
                            ? 'bg-white dark:bg-neutral-900 border-primary-500 text-primary-500 shadow-sm'
                            : 'border-transparent text-neutral-400 hover:text-neutral-600'
                        ]"
                      >
                        {{ opt.label }}
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Right Column: Scoring & Advance -->
                <div class="space-y-6">
                  <div>
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest mb-3">{{ t('managers.stages.config.scoring_method') }}</label>
                  </div>
                  <div class="space-y-2">
                    <button
                      v-for="method in scoringMethods"
                      :key="method.value"
                      @click="newStage.settings.scoring_method = method.value"
                      :class="[
                        'w-full p-4 rounded-2xl border transition-all text-left group',
                        newStage.settings.scoring_method === method.value
                          ? 'border-primary-500 bg-primary-500/5 ring-1 ring-primary-500'
                          : 'border-neutral-100 dark:border-neutral-800 hover:border-primary-300'
                      ]"
                    >
                      <p :class="['text-xs font-bold mb-0.5', newStage.settings.scoring_method === method.value ? 'text-primary-500' : 'text-neutral-700 dark:text-neutral-200']">
                        {{ method.label }}
                      </p>
                      <p class="text-[10px] text-neutral-400 dark:text-neutral-500">{{ method.description }}</p>
                    </button>
                  </div>

                  <!-- Advance Participants -->
                  <div class="pt-2">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest mb-3">{{ t('managers.stages.config.advance_label') }}</label>
                    <UInput v-model.number="newStage.settings.advance_count" type="number" placeholder="4" icon="i-lucide-arrow-up-right" size="xl" class="w-full" />
                  </div>
                </div>
              </div>

              <!-- Rules & Specific Config -->
              <div class="pt-8 mt-8 border-t border-neutral-100 dark:border-neutral-800">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                  <div class="space-y-4">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest mb-3">{{ t('managers.stages.config.additional_rules') }}</label>
                    <div 
                      class="flex items-center justify-between p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/30 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-800/50 transition-all border border-transparent hover:border-primary-500/20"
                      @click="newStage.settings.rules.allow_draw = !newStage.settings.rules.allow_draw"
                    >
                      <div class="flex items-center gap-4">
                        <div class="size-10 rounded-xl bg-white dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-white/10 shadow-sm">
                          <UIcon name="i-lucide-divide" class="size-5 text-neutral-400" />
                        </div>
                        <div>
                          <p class="text-xs font-bold text-neutral-900 dark:text-neutral-100">{{ t('managers.stages.config.rules.draw') }}</p>
                          <p class="text-[10px] text-neutral-500">Enable tie results for matches</p>
                        </div>
                      </div>
                      <UToggle v-model="newStage.settings.rules.allow_draw" color="primary" @click.stop />
                    </div>

                    <div 
                      class="flex items-center justify-between p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/30 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-800/50 transition-all border border-transparent hover:border-primary-500/20"
                      @click="newStage.settings.rules.extra_time = !newStage.settings.rules.extra_time"
                    >
                      <div class="flex items-center gap-4">
                        <div class="size-10 rounded-xl bg-white dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-white/10 shadow-sm">
                          <UIcon name="i-lucide-timer" class="size-5 text-neutral-400" />
                        </div>
                        <div>
                          <p class="text-xs font-bold text-neutral-900 dark:text-neutral-100">{{ t('managers.stages.config.rules.extra_time') }}</p>
                          <p class="text-[10px] text-neutral-500">Add overtime if score is tied</p>
                        </div>
                      </div>
                      <UToggle v-model="newStage.settings.rules.extra_time" color="primary" @click.stop />
                    </div>

                    <div 
                      class="flex items-center justify-between p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/30 cursor-pointer hover:bg-neutral-100 dark:hover:bg-neutral-800/50 transition-all border border-transparent hover:border-primary-500/20"
                      @click="newStage.settings.rules.penalties = !newStage.settings.rules.penalties"
                    >
                      <div class="flex items-center gap-4">
                        <div class="size-10 rounded-xl bg-white dark:bg-neutral-800 flex items-center justify-center border border-neutral-200 dark:border-white/10 shadow-sm">
                          <UIcon name="i-lucide-target" class="size-5 text-neutral-400" />
                        </div>
                        <div>
                          <p class="text-xs font-bold text-neutral-900 dark:text-neutral-100">{{ t('managers.stages.config.rules.penalties') }}</p>
                          <p class="text-[10px] text-neutral-500">Decide winner via penalty shootout</p>
                        </div>
                      </div>
                      <UToggle v-model="newStage.settings.rules.penalties" color="primary" @click.stop />
                    </div>
                  </div>

                  <div class="space-y-4">
                    <template v-if="newStage.type === 'swiss'">
                      <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest mb-3">{{ t('managers.stages.config.total_rounds') }}</label>
                      <UInput v-model.number="newStage.settings.rounds" type="number" placeholder="Automatic" icon="i-lucide-hash" size="xl" class="w-full" />
                    </template>
                    
                    <template v-if="newStage.type === 'round_robin'">
                      <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest mb-3">{{ t('managers.stages.config.group_config') }}</label>
                      <div class="grid grid-cols-2 gap-4">
                        <UInput v-model.number="newStage.groups_count" type="number" :placeholder="t('managers.stages.config.groups_label')" size="xl" class="w-full" />
                        <UInput v-model.number="newStage.participants_per_group" type="number" :placeholder="t('managers.stages.config.per_group_label')" size="xl" class="w-full" />
                      </div>
                    </template>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end gap-4 pt-8 mt-8 border-t border-neutral-100 dark:border-neutral-800">
                <UButton variant="ghost" color="neutral" size="lg" @click="resetAddState">{{ t('common.cancel') }}</UButton>
                <UButton color="primary" size="lg" icon="i-lucide-save" :loading="isSubmitting" @click="addStage">{{ t('managers.stages.config.submit') }}</UButton>
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
                    <template v-if="stage.settings?.match_format">
                      <span class="capitalize">{{ stage.settings.match_format.replace('_', ' ') }}</span>
                      <span v-if="stage.settings.match_format === 'best_of'"> (BO{{ stage.settings.win_condition * 2 - 1 }})</span>
                    </template>
                    <template v-else>
                      {{ stage.bo_format?.toUpperCase() }}
                    </template>
                    · {{ stage.settings?.advance_count || stage.participants_advance }} {{ t('managers.stages.config.advance_label').toLowerCase() }}
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
                <!-- Start Babak (only pending) -->
                <UButton
                  v-if="stage.status === 'pending'"
                  color="primary"
                  variant="solid"
                  size="sm"
                  icon="i-lucide-play"
                  @click.stop="openStart(stage)"
                >
                  {{ t('managers.stages.actions.start') }}
                </UButton>

                <!-- Quick Shuffle (only pending) -->
                <UButton
                  v-if="stage.status === 'pending'"
                  color="neutral"
                  variant="ghost"
                  size="sm"
                  icon="i-lucide-shuffle"
                  @click.stop="shuffleParticipants(stage)"
                />

                <!-- Manual Seed (only pending) -->
                <UButton
                  v-if="stage.status === 'pending'"
                  color="neutral"
                  variant="outline"
                  size="sm"
                  icon="i-lucide-list-ordered"
                  @click.stop="openSeeding(stage)"
                >
                  {{ t('managers.stages.actions.seed_manual') }}
                </UButton>

                <!-- View Bracket (when ongoing/completed) -->
                <UButton
                  v-if="stage.status === 'ongoing' || stage.status === 'completed'"
                  color="primary"
                  variant="outline"
                  size="sm"
                  icon="i-lucide-git-branch"
                  :to="`/dashboard/tournaments/${tournamentSlug}/bracket?stage=${stage.id}`"
                >
                  {{ t('managers.stages.actions.view_bracket') }}
                </UButton>

                <!-- Reset Bracket (only ongoing/completed) -->
                <UButton
                  v-if="stage.status === 'ongoing' || stage.status === 'completed'"
                  color="warning"
                  variant="ghost"
                  size="sm"
                  icon="i-lucide-rotate-ccw"
                  @click.stop="openReset(stage)"
                >
                  {{ t('managers.stages.actions.reset') }}
                </UButton>

                <!-- Advance Participants (only completed) -->
                <UButton
                  v-if="stage.status === 'completed'"
                  color="success"
                  variant="solid"
                  size="sm"
                  icon="i-lucide-arrow-right-circle"
                  @click.stop="openAdvance(stage)"
                >
                  {{ t('managers.stages.actions.advance') }}
                </UButton>

                <!-- Delete (only pending) -->
                <UButton
                  v-if="stage.status === 'pending'"
                  color="error"
                  variant="ghost"
                  size="sm"
                  icon="i-lucide-trash-2"
                  @click.stop="openDelete(stage)"
                >
                  {{ t('common.delete') }}
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
          <p class="text-base font-bold text-neutral-900 dark:text-white mb-1">{{ t('managers.stages.empty.title') }}</p>
          <p class="text-sm text-neutral-400 dark:text-neutral-500 max-w-xs mx-auto mb-6">
            {{ t('managers.stages.empty.desc') }}
          </p>
          <UButton color="primary" variant="outline" icon="i-lucide-plus" @click="isAdding = true">{{ t('managers.stages.empty.create_first') }}</UButton>
        </div>
      </div>

      <!-- ── Right: Tips ── -->
      <div class="w-full xl:w-80 bg-neutral-50/50 dark:bg-neutral-800/10 p-6">
        <h4 class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
          <UIcon name="i-lucide-lightbulb" class="size-4 text-amber-500" />
          {{ t('managers.stages.tips.title') }}
        </h4>
        <div class="space-y-6">
          <div class="p-4 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-sm">
            <p class="text-xs font-bold text-neutral-800 dark:text-neutral-200 mb-2">{{ t('managers.stages.tips.pattern') }}</p>
            <div class="space-y-3">
              <div class="flex items-start gap-2">
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0"></div>
                <p class="text-[11px] text-neutral-500 leading-relaxed">{{ t('managers.stages.tips.p1') }}</p>
              </div>
              <div class="flex items-start gap-2">
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0"></div>
                <p class="text-[11px] text-neutral-500 leading-relaxed">{{ t('managers.stages.tips.p2') }}</p>
              </div>
            </div>
          </div>

          <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20">
            <p class="text-[10px] font-bold text-amber-700 dark:text-amber-500 flex items-center gap-1 mb-1">
              <UIcon name="i-lucide-alert-circle" />
              {{ t('managers.stages.tips.important') }}
            </p>
            <p class="text-[10px] text-amber-600/80 dark:text-amber-500/70 leading-relaxed">
              {{ t('managers.stages.tips.important_desc') }}
            </p>
          </div>
        </div>
      </div>
    </div>
    <!-- ── Seeding Modal ── -->
    <UModal v-model:open="isSeedingModalOpen" :ui="{ content: 'rounded-[2.5rem] bg-white dark:bg-neutral-900 border-none overflow-hidden sm:max-w-4xl' }">
      <template #body>
        <div class="p-8 relative min-h-[500px] overflow-x-hidden custom-scrollbar max-h-[85vh]">
          <!-- Decoration -->
          <div class="absolute -top-24 -right-24 size-64 bg-primary-500/10 blur-[100px] rounded-full pointer-events-none"></div>

          <div class="flex items-center gap-4 mb-8 relative z-10 pt-4">
            <div class="size-14 rounded-2xl bg-primary-500/10 flex items-center justify-center border border-primary-500/20 shadow-inner">
              <UIcon name="i-lucide-list-ordered" class="size-7 text-primary-500" />
            </div>
            <div>
              <h3 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tight leading-none">{{ t('managers.stages.seeding_modal.title') }}</h3>
              <p class="text-[11px] font-medium text-neutral-500 mt-1.5 uppercase tracking-widest">{{ t('managers.stages.seeding_modal.subtitle') }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 relative z-10">
            <!-- Left: Draggable List -->
            <div class="space-y-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ t('managers.stages.seeding_modal.list_label') }}</span>
                <UBadge variant="subtle" color="primary" class="rounded-full text-[9px] px-2 py-0">{{ t('managers.stages.seeding_modal.seed_order') }}</UBadge>
              </div>
              <div class="space-y-2 max-h-[450px] overflow-y-auto pr-2 custom-scrollbar">
                <div 
                  v-for="(p, index) in localParticipants" 
                  :key="p.id"
                  draggable="true"
                  @dragstart="dragStart(index)"
                  @dragover.prevent
                  @drop="drop(index)"
                  class="group p-3 bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200 dark:border-white/5 rounded-2xl cursor-move flex items-center gap-4 hover:border-primary-500/50 hover:bg-primary-500/[0.02] transition-all"
                >
                  <div class="size-7 rounded-lg bg-neutral-200 dark:bg-neutral-800 flex items-center justify-center text-[10px] font-black text-neutral-500 border border-neutral-300 dark:border-white/5 shadow-inner shrink-0">
                    {{ index + 1 }}
                  </div>
                  <div class="flex-grow min-w-0">
                    <p class="text-xs font-black text-neutral-800 dark:text-neutral-200 uppercase tracking-tight leading-tight truncate">
                      {{ p.team?.name || p.user?.name || 'TBD' }}
                    </p>
                  </div>
                  <UIcon name="i-lucide-grip-vertical" class="size-4 text-neutral-300 group-hover:text-primary-400 transition-colors shrink-0" />
                </div>
              </div>
            </div>

            <!-- Right: Preview Matchups -->
            <div class="bg-neutral-50/50 dark:bg-black/20 rounded-[2rem] border border-neutral-200 dark:border-white/5 p-6 flex flex-col h-full">
              <div class="flex items-center gap-3 mb-6 shrink-0">
                <div class="size-8 rounded-xl bg-primary-500/20 flex items-center justify-center">
                  <UIcon name="i-lucide-swords" class="size-4 text-primary-500" />
                </div>
                <h4 class="text-sm font-black text-neutral-900 dark:text-white uppercase tracking-widest">{{ t('managers.stages.seeding_modal.preview_title') }}</h4>
              </div>

              <div class="flex-grow overflow-y-auto custom-scrollbar pr-2">
                <!-- Elimination Preview -->
                <div v-if="matchupPreview.type === 'matches'" class="space-y-3">
                  <div v-for="(m, i) in matchupPreview.data" :key="i" class="p-3 bg-white dark:bg-neutral-900 border border-neutral-100 dark:border-white/5 rounded-2xl shadow-sm relative overflow-hidden group">
                    <div class="absolute inset-y-0 left-0 w-1 bg-primary-500/50"></div>
                    <div class="flex items-center justify-between gap-4">
                      <div class="flex-1 text-right">
                        <p class="text-[10px] font-black text-neutral-800 dark:text-neutral-200 uppercase truncate">
                          {{ m.p1.team?.name || m.p1.user?.name || 'BYE' }}
                        </p>
                        <p class="text-[8px] text-primary-500 font-bold uppercase tracking-tighter">Seed #{{ localParticipants.findIndex(p => p.id === m.p1.id) + 1 || '?' }}</p>
                      </div>
                      <div class="size-6 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center shrink-0 border border-neutral-200 dark:border-white/10">
                        <span class="text-[8px] font-black text-neutral-400 uppercase">VS</span>
                      </div>
                      <div class="flex-1">
                        <p class="text-[10px] font-black text-neutral-800 dark:text-neutral-200 uppercase truncate">
                          {{ m.p2.team?.name || m.p2.user?.name || 'BYE' }}
                        </p>
                        <p class="text-[8px] text-primary-500 font-bold uppercase tracking-tighter">Seed #{{ localParticipants.findIndex(p => p.id === m.p2.id) + 1 || '?' }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Round Robin Preview -->
                <div v-else-if="matchupPreview.type === 'groups'" class="space-y-6">
                  <div v-for="(g, i) in matchupPreview.data" :key="i" class="space-y-2">
                    <div class="flex items-center gap-2 px-2">
                      <div class="size-2 rounded-full bg-primary-500"></div>
                      <span class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">{{ g.name }}</span>
                    </div>
                    <div class="grid grid-cols-1 gap-1.5">
                      <div v-for="mem in g.members" :key="mem.id" class="px-3 py-2 bg-white dark:bg-neutral-900 border border-neutral-100 dark:border-white/5 rounded-xl flex items-center justify-between">
                        <span class="text-[10px] font-bold text-neutral-700 dark:text-neutral-300 uppercase truncate">{{ mem.team?.name || mem.user?.name }}</span>
                        <span class="text-[8px] font-black text-primary-500">#{{ localParticipants.findIndex(p => p.id === mem.id) + 1 }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-white/5 shrink-0">
                <p class="text-[9px] text-neutral-400 font-medium italic text-center leading-normal">
                  {{ t('managers.stages.seeding_modal.footnote', { type: seedingStage?.type === 'round_robin' ? 'Snake Draft' : 'Balanced Seeding' }) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <div class="flex items-center justify-end gap-3 w-full px-4">
          <UButton 
            color="neutral" 
            variant="ghost" 
            size="xl"
            class="rounded-2xl font-bold uppercase tracking-widest text-[10px] px-8"
            @click="isSeedingModalOpen = false"
          >
            {{ t('common.cancel') }}
          </UButton>
          <UButton 
            color="primary" 
            size="xl"
            icon="i-lucide-save" 
            class="rounded-2xl font-black uppercase tracking-widest px-10 py-4 shadow-xl shadow-primary-500/20"
            :loading="isSubmitting" 
            @click="saveSeeding"
          >
            {{ t('managers.stages.seeding_modal.submit') }}
          </UButton>
        </div>
      </template>
    </UModal>

    <!-- ── Reset Confirmation Modal ── -->
    <UModal v-model:open="isResetModalOpen" :ui="{ content: 'rounded-[2.5rem] bg-white dark:bg-neutral-900 border-none overflow-hidden sm:max-w-sm' }">
      <template #body>
        <div class="p-4 text-center relative">
          <!-- Decoration -->
          <div class="absolute -top-24 -left-24 size-48 bg-warning-500/10 blur-[80px] rounded-full pointer-events-none"></div>

          <div class="size-20 rounded-full bg-warning-500/10 flex items-center justify-center mx-auto mb-6 border border-warning-500/20 relative z-10">
            <UIcon name="i-lucide-alert-triangle" class="size-10 text-warning-500" />
          </div>
          
          <h3 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2 relative z-10">{{ t('managers.stages.reset_modal.title') }}</h3>
          <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2 relative z-10 leading-relaxed px-4">
            {{ t('managers.stages.reset_modal.desc') }}
          </p>
          <p class="text-[10px] font-black text-warning-500 uppercase tracking-widest relative z-10">
            {{ t('managers.stages.reset_modal.warning') }}
          </p>
        </div>
      </template>

      <template #footer>
        <div class="flex flex-col gap-3 w-full">
          <UButton
            color="warning"
            block
            size="xl"
            :label="t('managers.stages.reset_modal.confirm')"
            class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-xl shadow-warning-500/20"
            :loading="isResetting"
            @click="resetStage"
          />
          <UButton
            color="neutral"
            variant="ghost"
            block
            size="xl"
            :label="t('common.cancel')"
            class="rounded-2xl font-bold uppercase tracking-widest text-[10px] py-2"
            @click="isResetModalOpen = false"
          />
        </div>
      </template>
    </UModal>

    <!-- ── Start Confirmation Modal ── -->
    <UModal v-model:open="isStartModalOpen" :ui="{ content: 'rounded-[2.5rem] bg-white dark:bg-neutral-900 border-none overflow-hidden sm:max-w-sm' }">
      <template #body>
        <div class="p-4 text-center relative">
          <!-- Decoration -->
          <div class="absolute -top-24 -right-24 size-48 bg-primary-500/10 blur-[80px] rounded-full pointer-events-none"></div>

          <div class="size-20 rounded-full bg-primary-500/10 flex items-center justify-center mx-auto mb-6 border border-primary-500/20 relative z-10">
            <UIcon name="i-lucide-play" class="size-10 text-primary-500" />
          </div>
          
          <h3 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2 relative z-10">{{ t('managers.stages.start_modal.title') }}</h3>
          <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-2 relative z-10 leading-relaxed px-4">
            {{ t('managers.stages.start_modal.desc') }}
          </p>
          <p class="text-[10px] font-black text-primary-500 uppercase tracking-widest relative z-10">
            {{ t('managers.stages.start_modal.warning') }}
          </p>
        </div>
      </template>

      <template #footer>
        <div class="flex flex-col gap-3 w-full">
          <UButton
            color="primary"
            block
            size="xl"
            :label="t('managers.stages.start_modal.confirm')"
            class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-xl shadow-primary-500/20"
            :loading="isStarting"
            @click="startStage"
          />
          <UButton
            color="neutral"
            variant="ghost"
            block
            size="xl"
            :label="t('managers.stages.start_modal.cancel')"
            class="rounded-2xl font-bold uppercase tracking-widest text-[10px] py-2"
            @click="isStartModalOpen = false"
          />
        </div>
      </template>
    </UModal>

    <!-- ── Delete Confirmation Modal ── -->
    <UModal v-model:open="isDeleteModalOpen" :ui="{ content: 'rounded-[2.5rem] bg-white dark:bg-neutral-900 border-none overflow-hidden sm:max-w-sm' }">
      <template #body>
        <div class="p-4 text-center relative">
          <!-- Decoration -->
          <div class="absolute -bottom-24 -left-24 size-48 bg-error-500/10 blur-[80px] rounded-full pointer-events-none"></div>

          <div class="size-20 rounded-full bg-error-500/10 flex items-center justify-center mx-auto mb-6 border border-error-500/20 relative z-10">
            <UIcon name="i-lucide-trash-2" class="size-10 text-error-500" />
          </div>
          
          <h3 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2 relative z-10">{{ t('managers.stages.delete_modal.title') }}</h3>
          <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-6 relative z-10 leading-relaxed px-4">
            {{ t('managers.stages.delete_modal.desc') }}
          </p>
        </div>
      </template>

      <template #footer>
        <div class="flex flex-col gap-3 w-full">
          <UButton
            color="error"
            block
            size="xl"
            :label="t('managers.stages.delete_modal.confirm')"
            class="rounded-2xl font-black uppercase tracking-widest py-4 shadow-xl shadow-error-500/20"
            @click="removeStage"
          />
          <UButton
            color="neutral"
            variant="ghost"
            block
            size="xl"
            :label="t('common.cancel')"
            class="rounded-2xl font-bold uppercase tracking-widest text-[10px] py-2"
            @click="isDeleteModalOpen = false"
          />
        </div>
      </template>
    </UModal>
    <!-- Advance Participants Modal -->
    <UModal v-model:open="isAdvanceModalOpen" :ui="{ content: 'rounded-[2rem] bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 shadow-2xl' }">
      <template #body>
        <div class="p-8">
          <div class="flex items-center gap-4 mb-8">
            <div class="size-14 rounded-2xl bg-success-500/10 flex items-center justify-center border border-success-500/20">
              <UIcon name="i-lucide-arrow-right-circle" class="size-8 text-success-500" />
            </div>
            <div>
              <h3 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tight">{{ t('managers.stages.advance_modal.title') }}</h3>
              <p class="text-xs font-bold text-neutral-400 uppercase tracking-widest">{{ selectedStageToAdvance?.name }}</p>
            </div>
          </div>

          <div class="bg-neutral-50 dark:bg-neutral-950 rounded-2xl p-4 mb-6 border border-neutral-200 dark:border-white/5">
            <p class="text-[10px] font-black text-neutral-400 uppercase tracking-widest mb-2">{{ t('managers.stages.advance_modal.pilih_peserta') }}</p>
            <div class="flex items-center justify-between">
              <span class="text-sm font-bold text-neutral-700 dark:text-neutral-200">
                {{ t('managers.stages.advance_modal.selected_count', { count: selectedAdvancingIds.length, total: selectedStageToAdvance?.settings?.advance_count || selectedStageToAdvance?.participants_advance }) }}
              </span>
              <UBadge color="neutral" variant="subtle" size="xs">{{ t('managers.stages.advance_modal.recommended') }}</UBadge>
            </div>
          </div>

          <div class="space-y-2 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
            <div 
              v-for="p in selectableParticipants" 
              :key="p.id"
              class="flex items-center gap-4 p-3 rounded-xl border border-neutral-100 dark:border-white/5 transition-all hover:bg-neutral-50 dark:hover:bg-white/5"
              :class="{ 'bg-primary-500/5 border-primary-500/20': selectedAdvancingIds.includes(p.id) }"
            >
              <UCheckbox v-model="selectedAdvancingIds" :value="p.id" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-neutral-900 dark:text-white truncate">{{ p.name }}</p>
                <p class="text-[10px] text-neutral-400 font-medium">Ranking #{{ p.rank }} • {{ p.points ?? 0 }} Points</p>
              </div>
              <UIcon v-if="selectedAdvancingIds.includes(p.id)" name="i-lucide-check-circle-2" class="size-4 text-primary-500" />
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <div class="flex justify-end gap-3 w-full">
          <UButton 
            color="neutral" 
            variant="ghost" 
            class="font-bold uppercase tracking-wider text-xs"
            @click="isAdvanceModalOpen = false"
          >
            {{ t('common.cancel') }}
          </UButton>
          <UButton 
            color="success" 
            class="font-black uppercase tracking-wider text-xs shadow-lg shadow-success-500/20 px-6"
            :loading="isAdvancing"
            :disabled="selectedAdvancingIds.length === 0"
            @click="confirmAdvance"
          >
            {{ t('managers.stages.advance_modal.confirm') }}
          </UButton>
        </div>
      </template>
    </UModal>
  </div>
</template>
