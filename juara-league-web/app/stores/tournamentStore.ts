import { defineStore } from 'pinia'
import type { Tournament, StoreTournamentPayload } from '~/types/tournament'

export const useTournamentStore = defineStore('tournament', () => {
  const tournaments = ref<Tournament[]>([])
  const myTournaments = ref<Tournament[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const fetchTournaments = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Tournament[] }>('/api/v1/tournaments')
      tournaments.value = response.data
    } catch (e: any) {
      error.value = e.message || 'Gagal mengambil data turnamen'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchMyTournaments = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Tournament[] }>('/api/v1/my-tournaments')
      myTournaments.value = response.data
    } catch (e: any) {
      error.value = e.message || 'Gagal mengambil data turnamen Anda'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const createTournament = async (payload: StoreTournamentPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Tournament }>('/api/v1/tournaments', {
        method: 'POST',
        body: payload
      })
      // Add to local state if needed
      tournaments.value.unshift(response.data)
      myTournaments.value.unshift(response.data)
      return response.data
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal membuat turnamen'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const getBySlug = async (slug: string) => {
    isLoading.value = true
    try {
      const response = await useApi<{ data: Tournament }>(`/api/v1/tournaments/${slug}`)
      return response.data
    } catch (e: any) {
      error.value = e.message || 'Turnamen tidak ditemukan'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  return {
    tournaments,
    myTournaments,
    isLoading,
    error,
    fetchTournaments,
    fetchMyTournaments,
    createTournament,
    getBySlug
  }
})
