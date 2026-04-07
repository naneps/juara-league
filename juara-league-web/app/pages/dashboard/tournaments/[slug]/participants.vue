<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import TournamentParticipantManager from '~/components/tournaments/TournamentParticipantManager.vue'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()

const { data: tournament } = await useAsyncData(`tournament-participants-${slug}`, () => tournamentStore.getBySlug(slug))
</script>

<template>
  <div v-if="tournament" class="space-y-12">
    <TournamentParticipantManager 
      :tournament-slug="slug" 
      :initial-participants="tournament.participants"
    />
  </div>
</template>
