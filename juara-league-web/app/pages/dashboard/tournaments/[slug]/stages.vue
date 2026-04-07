<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import TournamentStageManager from '~/components/tournaments/TournamentStageManager.vue'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()

const { data: tournament } = await useAsyncData(`tournament-stages-${slug}`, () => tournamentStore.getBySlug(slug))
</script>

<template>
  <div v-if="tournament" class="space-y-12">
    <TournamentStageManager 
      :tournament-slug="slug" 
      :initial-stages="tournament.stages"
    />
  </div>
</template>
