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
  bo_format: 'bo1',
  participants_advance: 4,
  groups_count: null as number | null,
  participants_per_group: null as number | null,
})

const stageTemplates = computed(() => [
  { id: 'single_elim', title: t('managers.stages.templates.single_elim.title'), description: t('managers.stages.templates.single_elim.desc'), icon: 'i-lucide-skull', type: 'single_elim', defaultName: t('managers.stages.templates.single_elim.default'), color: 'error' },
  { id: 'double_elim', title: t('managers.stages.templates.double_elim.title'), description: t('managers.stages.templates.double_elim.desc'), icon: 'i-lucide-refresh-ccw', type: 'double_elim', defaultName: t('managers.stages.templates.double_elim.default'), color: 'primary' },
  { id: 'round_robin', title: t('managers.stages.templates.round_robin.title'), description: t('managers.stages.templates.round_robin.desc'), icon: 'i-lucide-grid-3x3', type: 'round_robin', defaultName: t('managers.stages.templates.round_robin.default'), color: 'amber' },
  { id: 'swiss', title: t('managers.stages.templates.swiss.title'), description: t('managers.stages.templates.swiss.desc'), icon: 'i-lucide-git-merge', type: 'swiss', defaultName: t('managers.stages.templates.swiss.default'), color: 'emerald' },
])

const boFormats = [
  { label: 'Best of 1', value: 'bo1' },
  { label: 'Best of 3', value: 'bo3' },
  { label: 'Best of 5', value: 'bo5' },
  { label: 'Best of 7', value: 'bo7' },
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
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.add_success'), color: 'success' })
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.add_failed'), color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

const removeStage = async (stage: Stage) => {
  if (!confirm(t('managers.stages.confirm.delete'))) return
  try {
    await tournamentStore.deleteStage(props.tournamentSlug, stage.id)
    await fetchStages()
    useToast().add({ title: t('common.success'), description: t('managers.stages.toast.delete_success'), color: 'success' })
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.delete_failed'), color: 'error' })
  }
}

const isStarting = ref(false)
const startStage = async (stage: Stage) => {
  if (!confirm(t('managers.stages.confirm.start', { name: stage.name }))) return
  isStarting.value = true
  try {
    const result = await tournamentStore.startStage(props.tournamentSlug, stage.id)
    await fetchStages()
    useToast().add({ title: t('managers.stages.toast.start_success'), description: t('managers.stages.toast.matches_gen', { count: result.matches_generated }), color: 'success' })
    emit('stage-started', stage.id)
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('managers.stages.toast.start_failed'), color: 'error' })
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
                <div class="absolute -top-4 -right-4 size-24 bg-neutral-50 dark:bg-neutral-800 rounded-full group-hover:bg-primary-500/10 transition-colors" />
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

              <div>
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">{{ t('managers.stages.config.bo_label') }}</label>
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
                <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">{{ t('managers.stages.config.advance_label') }}</label>
                <UInput v-model.number="newStage.participants_advance" type="number" placeholder="4" icon="i-lucide-arrow-up-right" size="lg" class="w-full" />
              </div>

              <div v-if="newStage.type === 'round_robin'" class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">{{ t('managers.stages.config.groups_label') }}</label>
                  <UInput v-model.number="newStage.groups_count" type="number" placeholder="2" size="lg" class="w-full" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">{{ t('managers.stages.config.per_group_label') }}</label>
                  <UInput v-model.number="newStage.participants_per_group" type="number" placeholder="4" size="lg" class="w-full" />
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
                    {{ stage.bo_format?.toUpperCase() }} · {{ stage.participants_advance }} {{ t('managers.stages.config.advance_label').toLowerCase() }}
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
                  {{ t('managers.stages.actions.start') }}
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
                  {{ t('managers.stages.actions.view_bracket') }}
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
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0" />
                <p class="text-[11px] text-neutral-500 leading-relaxed">{{ t('managers.stages.tips.p1') }}</p>
              </div>
              <div class="flex items-start gap-2">
                <div class="size-1.5 rounded-full bg-primary-500 mt-1.5 shrink-0" />
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
  </div>
</template>
