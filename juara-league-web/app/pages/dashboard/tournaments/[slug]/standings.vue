<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()

const { data: tournament } = await useAsyncData(`tournament-standings-${slug}`, () => tournamentStore.getBySlug(slug))

const selectedStageId = ref('')
const selectedGroupId = ref('')
const standings = ref([])
const isLoadingStandings = ref(false)

const standingsStages = computed(() => {
  return tournament.value?.stages?.filter((s: any) => ['round_robin', 'swiss'].includes(s.type)) || []
})

const selectedStage = computed(() => {
  return standingsStages.value.find((s: any) => s.id === selectedStageId.value) || standingsStages.value[0]
})

const fetchStandings = async () => {
  if (!selectedStage.value) return
  
  const groupId = selectedGroupId.value || selectedStage.value.groups?.[0]?.id
  if (!groupId) {
    standings.value = []
    return
  }

  isLoadingStandings.value = true
  try {
    const data = await tournamentStore.fetchStandings(slug, selectedStage.value.id, groupId)
    standings.value = data
  } catch (e) {
    console.error('Failed to fetch standings', e)
  } finally {
    isLoadingStandings.value = false
  }
}

watch(tournament, (newVal) => {
  if (newVal && standingsStages.value.length && !selectedStageId.value) {
    selectedStageId.value = standingsStages.value[0].id
    if (selectedStage.value?.groups?.length) {
      selectedGroupId.value = selectedStage.value.groups[0].id
    }
  }
}, { immediate: true })

watch(selectedStageId, (newStageId) => {
  const stage = standingsStages.value.find((s: any) => s.id === newStageId)
  if (stage?.groups?.length) {
    selectedGroupId.value = stage.groups[0].id
  } else {
    selectedGroupId.value = ''
  }
  fetchStandings()
})

watch(selectedGroupId, () => {
  fetchStandings()
})

onMounted(() => {
  if (tournament.value && standingsStages.value.length) {
    if (!selectedStageId.value) {
      selectedStageId.value = standingsStages.value[0].id
    }
    if (selectedStage.value?.groups?.length && !selectedGroupId.value) {
      selectedGroupId.value = selectedStage.value.groups[0].id
    }
    fetchStandings()
  }
})
</script>

<template>
  <div v-if="tournament" class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-black text-neutral-900 dark:text-white uppercase tracking-tight mb-2">{{ $t('dashboard.standings') }}</h2>
        <p class="text-neutral-500 font-medium text-sm">Monitor klasemen dan poin peserta secara real-time.</p>
      </div>

      <div class="flex items-center gap-4">
        <!-- Stage Selector -->
        <USelectMenu
          v-if="standingsStages.length > 1"
          v-model="selectedStageId"
          :options="standingsStages"
          value-attribute="id"
          option-attribute="name"
          class="w-48"
        >
          <template #default="{ open }">
            <UButton color="neutral" variant="ghost" class="rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-white/5">
              {{ standingsStages.find(s => s.id === selectedStageId)?.name || 'Pilih Babak' }}
              <UIcon name="i-lucide-chevron-down" class="size-4 ml-2" :class="{ 'rotate-180': open }" />
            </UButton>
          </template>
        </USelectMenu>

        <!-- Group Selector -->
        <USelectMenu
          v-if="selectedStage?.groups?.length > 1"
          v-model="selectedGroupId"
          :options="selectedStage.groups"
          value-attribute="id"
          option-attribute="name"
          class="w-32"
        >
          <template #default="{ open }">
            <UButton color="neutral" variant="ghost" class="rounded-xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-white/5">
              {{ selectedStage.groups.find(g => g.id === selectedGroupId)?.name || 'Pilih Grup' }}
              <UIcon name="i-lucide-chevron-down" class="size-4 ml-2" :class="{ 'rotate-180': open }" />
            </UButton>
          </template>
        </USelectMenu>

        <UButton 
          color="neutral" 
          variant="ghost" 
          icon="i-lucide-refresh-cw" 
          :loading="isLoadingStandings"
          @click="fetchStandings"
          class="rounded-xl"
        />
      </div>
    </div>

    <div v-if="standingsStages.length > 0">
      <TournamentsStandingsTable 
        :standings="standings" 
        :is-loading="isLoadingStandings" 
      />
    </div>

    <div v-else class="py-20 text-center bg-neutral-100 dark:bg-neutral-900/40 rounded-[3rem] border border-dashed border-neutral-300 dark:border-white/5">
       <UIcon name="i-lucide-list-ordered" class="size-16 text-neutral-300 dark:text-neutral-700 mb-4" />
       <p class="text-neutral-500 font-bold uppercase tracking-widest text-xs">Turnamen ini tidak menggunakan format Liga/Klasemen.</p>
    </div>
  </div>
</template>
