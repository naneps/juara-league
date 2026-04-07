<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import TournamentStaffManager from '~/components/tournaments/TournamentStaffManager.vue'

const route = useRoute()
const slug = route.params.slug as string
const tournamentStore = useTournamentStore()

const { data: tournament } = await useAsyncData(`tournament-staff-${slug}`, () => tournamentStore.getBySlug(slug))
</script>

<template>
  <div v-if="tournament" class="space-y-12">
    <TournamentStaffManager 
      :tournament-slug="slug" 
      :initial-staff="tournament.staff"
    />
  </div>
</template>
