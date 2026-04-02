import { defineStore } from 'pinia'
import type {
  CreateTeamPayload,
  Team,
  TeamInvitation,
  TeamMember,
  TeamPaginatedResponse,
  TeamQueryParams,
  UpdateTeamPayload
} from '~/types/team.types'

export const useTeamStore = defineStore('team', () => {
  // ─── State ────────────────────────────────────────────────────────────────
  const teams = ref<Team[]>([])
  const myTeams = ref<Team[]>([])
  const myInvitations = ref<TeamInvitation[]>([])
  const currentTeam = ref<Team | null>(null)
  const total = ref(0)
  const currentPage = ref(1)
  const lastPage = ref(1)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // ─── Actions ──────────────────────────────────────────────────────────────

  /**
   * GET /api/v1/teams — daftar semua tim (paginated, untuk admin listing)
   */
  const fetchTeams = async (params?: TeamQueryParams) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<TeamPaginatedResponse>('/api/v1/teams', {
        params: {
          page: params?.page ?? 1,
          per_page: params?.per_page ?? 10,
          ...(params?.search ? { search: params.search } : {})
        }
      })
      teams.value = response.data
      total.value = response.total
      currentPage.value = response.current_page
      lastPage.value = response.last_page
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengambil data tim'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * GET /api/v1/my-teams — tim milik user yang sedang login
   */
  const fetchMyTeams = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<Team[]>('/api/v1/my-teams')
      myTeams.value = response
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengambil data tim Anda'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * GET /api/v1/teams/{id} — detail tim lengkap dengan captain & members
   */
  const fetchTeam = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<Team>(`/api/v1/teams/${id}`)
      currentTeam.value = response
      return response
    } catch (e: any) {
      error.value = e.data?.message || 'Tim tidak ditemukan'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * POST /api/v1/teams — buat tim baru
   */
  const createTeam = async (payload: CreateTeamPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<Team>('/api/v1/teams', {
        method: 'POST',
        body: payload
      })
      // Tambahkan ke list dan refresh total
      teams.value.unshift(response)
      myTeams.value.unshift(response)
      total.value += 1
      return response
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal membuat tim'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * PUT /api/v1/teams/{id} — update info tim
   */
  const updateTeam = async (id: number, payload: UpdateTeamPayload) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<Team>(`/api/v1/teams/${id}`, {
        method: 'PUT',
        body: payload
      })
      // Sinkronisasi list
      const idx = teams.value.findIndex(t => t.id === id)
      if (idx !== -1) teams.value[idx] = response

      const myIdx = myTeams.value.findIndex(t => t.id === id)
      if (myIdx !== -1) myTeams.value[myIdx] = response

      if (currentTeam.value?.id === id) currentTeam.value = response
      return response
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengupdate tim'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * DELETE /api/v1/teams/{id} — hapus tim (hanya kapten)
   */
  const deleteTeam = async (id: number) => {
    isLoading.value = true
    error.value = null
    try {
      await useApi(`/api/v1/teams/${id}`, { method: 'DELETE' })
      teams.value = teams.value.filter(t => t.id !== id)
      myTeams.value = myTeams.value.filter(t => t.id !== id)
      total.value = Math.max(0, total.value - 1)
      if (currentTeam.value?.id === id) currentTeam.value = null
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal menghapus tim'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * POST /api/v1/teams/{teamId}/invite — undang anggota via email
   */
  const inviteMember = async (teamId: number, email: string) => {
    isLoading.value = true
    error.value = null
    try {
      await useApi(`/api/v1/teams/${teamId}/invite`, {
        method: 'POST',
        body: { email }
      })
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengundang anggota'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * DELETE /api/v1/teams/{teamId}/members/{userId} — keluarkan anggota
   */
  const removeMember = async (teamId: number, userId: number) => {
    isLoading.value = true
    error.value = null
    try {
      await useApi(`/api/v1/teams/${teamId}/members/${userId}`, { method: 'DELETE' })
      if (currentTeam.value?.id === teamId && currentTeam.value.members) {
        currentTeam.value.members = currentTeam.value.members.filter((m: TeamMember) => m.id !== userId)
      }
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengeluarkan anggota'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * POST /api/v1/teams/{teamId}/transfer — transfer jabatan kapten
   */
  const transferCaptaincy = async (teamId: number, userId: number) => {
    isLoading.value = true
    error.value = null
    try {
      await useApi(`/api/v1/teams/${teamId}/transfer`, {
        method: 'POST',
        body: { user_id: userId }
      })
      // Refresh detail tim agar state kapten terupdate
      if (currentTeam.value?.id === teamId) {
        await fetchTeam(teamId)
      }
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal transfer jabatan kapten'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * GET /api/v1/my-invitations — undangan tim untuk user yang login
   */
  const fetchMyInvitations = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<TeamInvitation[]>('/api/v1/my-invitations')
      myInvitations.value = response
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengambil data undangan'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * POST /api/v1/teams/invitations/{token}/accept — terima undangan
   */
  const acceptInvitation = async (token: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<Team>(`/api/v1/teams/invitations/${token}/accept`, {
        method: 'POST'
      })
      // Refresh myTeams and remove from invitations
      await fetchMyTeams()
      myInvitations.value = myInvitations.value.filter(inv => inv.token !== token)
      return response
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal menerima undangan. Token mungkin tidak valid atau sudah kadaluarsa.'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * POST /api/v1/teams/invitations/{token}/decline — tolak undangan
   */
  const declineInvitation = async (token: string) => {
    isLoading.value = true
    error.value = null
    try {
      await useApi(`/api/v1/teams/invitations/${token}/decline`, {
        method: 'POST'
      })
      // Remove from invitations list
      myInvitations.value = myInvitations.value.filter(inv => inv.token !== token)
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal menolak undangan'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  // ─── Getters ──────────────────────────────────────────────────────────────

  const teamById = (id: number) => computed(() => teams.value.find(t => t.id === id))

  return {
    // State
    teams,
    myTeams,
    myInvitations,
    currentTeam,
    total,
    currentPage,
    lastPage,
    isLoading,
    error,
    // Actions
    fetchTeams,
    fetchMyTeams,
    fetchMyInvitations,
    fetchTeam,
    createTeam,
    updateTeam,
    deleteTeam,
    inviteMember,
    removeMember,
    transferCaptaincy,
    acceptInvitation,
    declineInvitation,
    // Getters
    teamById
  }
})
