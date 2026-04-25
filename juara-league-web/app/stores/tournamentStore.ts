import { defineStore } from 'pinia'
import type { Participant, StoreTournamentPayload, Tournament, Stage, TournamentMatch, Game } from '~/types/tournament'

export const useTournamentStore = defineStore('tournament', () => {
  const tournaments = ref<Tournament[]>([])
  const myTournaments = ref<Tournament[]>([])
  const myParticipations = ref<Participant[]>([])
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
      const response = await useApi<{ data: Tournament }>(`/api/v1/tournaments/${slug}?include=stages.groups,participants,staff,sport`)
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
      const updateStatus = (t: Tournament) => { if (t.slug === slug) t.status = 'registration' }
      tournaments.value.forEach(updateStatus)
      myTournaments.value.forEach(updateStatus)
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mempublikasikan turnamen'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  // ── Stage Management ──

  const fetchStages = async (slug: string) => {
    try {
      const response = await useApi<{ data: Stage[] }>(`/api/v1/tournaments/${slug}/stages`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const fetchStageDetail = async (slug: string, stageId: string) => {
    try {
      const response = await useApi<{ data: Stage }>(`/api/v1/tournaments/${slug}/stages/${stageId}`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const createStage = async (slug: string, payload: any) => {
    try {
      const response = await useApi<{ data: Stage; recommendation?: any }>(`/api/v1/tournaments/${slug}/stages`, {
        method: 'POST',
        body: payload
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  const updateStage = async (slug: string, stageId: string, payload: any) => {
    try {
      const response = await useApi<{ data: Stage }>(`/api/v1/tournaments/${slug}/stages/${stageId}`, {
        method: 'PUT',
        body: payload
      })
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const deleteStage = async (slug: string, stageId: string) => {
    try {
      await useApi(`/api/v1/tournaments/${slug}/stages/${stageId}`, { method: 'DELETE' })
    } catch (e: any) {
      throw e
    }
  }

  const seedParticipants = async (slug: string, stageId: string, seeds: string[]) => {
    try {
      await useApi(`/api/v1/tournaments/${slug}/stages/${stageId}/seed`, {
        method: 'POST',
        body: { seeds }
      })
    } catch (e: any) {
      throw e
    }
  }

  const updateSeeds = async (slug: string, stageId: string, seeds: string[]) => {
    try {
      const response = await useApi(`/api/v1/tournaments/${slug}/stages/${stageId}/seed`, {
        method: 'POST',
        body: { seeds }
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  const shuffleParticipants = async (slug: string, stageId: string) => {
    try {
      await useApi(`/api/v1/tournaments/${slug}/stages/${stageId}/shuffle`, {
        method: 'POST'
      })
    } catch (e: any) {
      throw e
    }
  }

  const startStage = async (slug: string, stageId: string) => {
    try {
      const response = await useApi<{ data: any }>(`/api/v1/tournaments/${slug}/stages/${stageId}/start`, {
        method: 'POST'
      })
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const resetStage = async (slug: string, stageId: string) => {
    try {
      await useApi(`/api/v1/tournaments/${slug}/stages/${stageId}/reset`, {
        method: 'POST'
      })
    } catch (e: any) {
      throw e
    }
  }

  const advanceParticipants = async (slug: string, stageId: string, participantIds: string[]) => {
    try {
      const response = await useApi<{ data: any }>(`/api/v1/tournaments/${slug}/stages/${stageId}/advance`, {
        method: 'POST',
        body: { advancing_participants: participantIds }
      })
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const autoScheduleMatches = async (slug: string, stageId: string, settings?: { start_at?: string; interval_minutes?: number; matches_per_day?: number }) => {
    try {
      const response = await useApi<{ message: string }>(`/api/v1/tournaments/${slug}/stages/${stageId}/auto-schedule`, {
        method: 'POST',
        body: settings || {}
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  // ── Match & Game Management ──

  const fetchMatches = async (slug: string, stageId: string, filters?: { status?: string; round?: number; participant_id?: string }) => {
    try {
      const params = new URLSearchParams()
      if (filters?.status) params.set('status', filters.status)
      if (filters?.round) params.set('round', String(filters.round))
      if (filters?.participant_id) params.set('participant_id', filters.participant_id)
      const qs = params.toString() ? `?${params.toString()}` : ''
      const response = await useApi<{ data: TournamentMatch[] }>(`/api/v1/tournaments/${slug}/stages/${stageId}/matches${qs}`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const fetchMatchDetail = async (slug: string, stageId: string, matchId: string) => {
    try {
      const response = await useApi<{ data: TournamentMatch }>(`/api/v1/tournaments/${slug}/stages/${stageId}/matches/${matchId}`)
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const updateMatch = async (slug: string, stageId: string, matchId: string, payload: { status?: string; scheduled_at?: string }) => {
    try {
      const response = await useApi<{ data: TournamentMatch }>(`/api/v1/tournaments/${slug}/stages/${stageId}/matches/${matchId}`, {
        method: 'PATCH',
        body: payload
      })
      return response.data
    } catch (e: any) {
      throw e
    }
  }

  const inputGame = async (slug: string, stageId: string, matchId: string, payload: { game_number: number; winner_id: string; score_p1?: number; score_p2?: number }) => {
    try {
      const response = await useApi<{ message: string; data: any }>(`/api/v1/tournaments/${slug}/stages/${stageId}/matches/${matchId}/games`, {
        method: 'POST',
        body: payload
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  const correctGame = async (slug: string, stageId: string, matchId: string, gameId: string, payload: { winner_id: string; score_p1?: number; score_p2?: number }) => {
    try {
      const response = await useApi<{ message: string; data: any }>(`/api/v1/tournaments/${slug}/stages/${stageId}/matches/${matchId}/games/${gameId}`, {
        method: 'PUT',
        body: payload
      })
      return response
    } catch (e: any) {
      throw e
    }
  }

  // ── Other ──

  const fetchStandings = async (slug: string, stageId: string, groupId?: string) => {
    try {
      const url = groupId 
        ? `/api/v1/tournaments/${slug}/stages/${stageId}/groups/${groupId}/standings`
        : `/api/v1/tournaments/${slug}/stages/${stageId}/standings`
      const response = await useApi<{ data: any[] }>(url)
      return response.data
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

  const fetchStats = async (slug: string) => {
    try {
      const response = await useApi<{ data: any }>(`/api/v1/tournaments/${slug}/stats`)
      return response.data
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
      const updateIdx = tournaments.value.findIndex(t => String(t.id) === String(id) || t.slug === String(id))
      if (updateIdx !== -1) tournaments.value[updateIdx] = response.data
      const myUpdateIdx = myTournaments.value.findIndex(t => String(t.id) === String(id) || t.slug === String(id))
      if (myUpdateIdx !== -1) myTournaments.value[myUpdateIdx] = response.data
      return response.data
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal memperbarui turnamen'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchMyParticipations = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Participant[] }>('/api/my-participations')
      myParticipations.value = response.data
    } catch (e: any) {
      error.value = e.message || 'Gagal mengambil data pendaftaran'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const fetchDashboardOverview = async () => {
    try {
      const response = await useApi<{
        stats: {
          total_tournaments: number;
          ongoing_tournaments: number;
          pending_participants: number;
          matches_today: number;
        };
        recent_participants: any[];
        upcoming_matches: any[];
        recent_tournaments: any[];
      }>('/api/v1/dashboard/overview')
      return response
    } catch (e: any) {
      throw e
    }
  }

  return {
    tournaments, myTournaments, myParticipations, isLoading, error,
    fetchTournaments, fetchMyTournaments, fetchMyParticipations, fetchDashboardOverview,
    createTournament, updateTournament, getBySlug, getById, publishTournament,
    // Stage
    fetchStages, fetchStageDetail, createStage, updateStage, deleteStage,
    seedParticipants, shuffleParticipants, startStage, resetStage, advanceParticipants, autoScheduleMatches,
    // Match & Game
    fetchMatches, fetchMatchDetail, updateMatch, inputGame, correctGame,
    // Other
    fetchStandings, fetchParticipants, updateParticipantStatus, fetchSports,
    fetchStaff, addStaff, removeStaff
  }
})
