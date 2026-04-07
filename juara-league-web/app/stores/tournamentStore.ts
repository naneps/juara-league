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
      const response = await useApi<{ data: Tournament[] }>('/api/v1/tournaments?include=sport')
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
      const response = await useApi<{ data: Tournament[] }>('/api/v1/my-tournaments?include=sport')
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
      const response = await useApi<{ data: Tournament }>(`/api/v1/tournaments/${slug}?include=stages,participants,staff,sport`)
      return response.data
    } catch (e: any) {
      error.value = e.message || 'Turnamen tidak ditemukan'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const publishTournament = async (slug: string) => {
    isLoading.value = true
    error.value = null
    try {
      await useApi(`/api/v1/tournaments/${slug}/publish`, { method: 'POST' })
      // Update local state
      const updateStatus = (t: Tournament) => { if (t.slug === slug) t.status = 'open' }
      tournaments.value.forEach(updateStatus)
      myTournaments.value.forEach(updateStatus)
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mempublikasikan turnamen'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchStages = async (slug: string) => {
    try {
      const response = await useApi<{ data: any[] }>(`/api/v1/tournaments/${slug}/stages`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const createStage = async (slug: string, payload: any) => {
    try {
      const response = await useApi<{ data: any }>(`/api/v1/tournaments/${slug}/stages`, {
        method: 'POST',
        body: payload
      })
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const deleteStage = async (id: number) => {
    try {
      await useApi(`/api/v1/stages/${id}`, { method: 'DELETE' })
    } catch (e: any) {
      throw e
    }
  }

  const fetchParticipants = async (slug: string) => {
    try {
      const response = await useApi<{ data: any[] }>(`/api/v1/tournaments/${slug}/participants`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const updateParticipantStatus = async (id: number, status: string) => {
    try {
      const response = await useApi<{ data: any }>(`/api/v1/participants/${id}/status`, {
        method: 'PATCH',
        body: { status }
      })
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const fetchSports = async () => {
    try {
      const response = await useApi<{ data: any[] }>('/api/v1/sports')
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const fetchStaff = async (slug: string) => {
    try {
      const response = await useApi<{ data: any[] }>(`/api/v1/tournaments/${slug}/staff`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const addStaff = async (slug: string, payload: { email: string, role: string }) => {
    try {
      const response = await useApi<{ message: string }>(`/api/v1/tournaments/${slug}/staff`, {
        method: 'POST',
        body: payload
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  const removeStaff = async (slug: string, userId: number) => {
    try {
      const response = await useApi<{ message: string }>(`/api/v1/tournaments/${slug}/staff/${userId}`, {
        method: 'DELETE'
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  const getById = async (id: number | string) => {
    isLoading.value = true
    try {
      const response = await useApi<{ data: Tournament }>(`/api/v1/tournaments/${id}?include=sport`)
      return response.data
    } catch (e: any) {
      error.value = e.message || 'Turnamen tidak ditemukan'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const updateTournament = async (id: number | string, payload: StoreTournamentPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Tournament }>(`/api/v1/tournaments/${id}`, {
        method: 'PUT',
        body: payload
      })
      
      // Update local state
      const updateIdx = tournaments.value.findIndex(t => t.id === Number(id))
      if (updateIdx !== -1) tournaments.value[updateIdx] = response.data
      
      const myUpdateIdx = myTournaments.value.findIndex(t => t.id === Number(id))
      if (myUpdateIdx !== -1) myTournaments.value[myUpdateIdx] = response.data
      
      return response.data
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal memperbarui turnamen'
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
    updateTournament,
    getBySlug,
    getById,
    publishTournament,
    fetchStages,
    createStage,
    deleteStage,
    fetchParticipants,
    updateParticipantStatus,
    fetchSports,
    fetchStaff,
    addStaff,
    removeStaff
  }
})
