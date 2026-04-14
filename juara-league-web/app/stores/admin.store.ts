import { defineStore } from 'pinia'
import type { User } from '~/types/auth'
import type { Tournament } from '~/types/tournament'

export const useAdminStore = defineStore('admin', () => {
  // --- STATE ---
  const stats = ref<any>(null)
  const users = ref<User[]>([])
  const usersMeta = ref<any>(null)
  const pendingTournaments = ref<Tournament[]>([])
  
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // --- ACTIONS: DASHBOARD ---
  const fetchStats = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<any>('/api/v1/admin/stats')
      stats.value = response
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengambil statistik admin'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  // --- ACTIONS: USERS ---
  const fetchUsers = async (params: { search?: string, role?: string, page?: number, per_page?: number }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<any>('/api/v1/admin/users', { params })
      users.value = response.data
      usersMeta.value = response.meta
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengambil data user'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const toggleUserSuspension = async (userId: string | number) => {
    try {
      const res = await useApi<any>(`/api/v1/admin/users/${userId}/suspend`, { method: 'PATCH' })
      // Update local state if the user is in the current list
      const idx = users.value.findIndex(u => u.id === userId)
      if (idx !== -1) {
        users.value[idx].is_suspended = res.data.is_suspended
      }
      return res.data
    } catch (e: any) {
      throw e
    }
  }

  const changeUserRole = async (userId: string | number, role: string) => {
    try {
      const res = await useApi<any>(`/api/v1/admin/users/${userId}/role`, { 
        method: 'PATCH',
        body: { role }
      })
      // Update local state
      const idx = users.value.findIndex(u => u.id === userId)
      if (idx !== -1) {
        users.value[idx].roles = [role]
      }
      return res.data
    } catch (e: any) {
      throw e
    }
  }

  // --- ACTIONS: TOURNAMENTS ---
  const fetchPendingTournaments = async () => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Tournament[] }>('/api/v1/admin/tournaments', {
        params: { approval_status: 'pending_review' }
      })
      pendingTournaments.value = response.data
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal mengambil moderasi turnamen'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const approveTournament = async (id: string | number) => {
    try {
      await useApi(`/api/v1/admin/tournaments/${id}/approve`, { method: 'POST' })
      pendingTournaments.value = pendingTournaments.value.filter(t => t.id !== id)
    } catch (e: any) {
      throw e
    }
  }

  const rejectTournament = async (id: string | number, reason: string) => {
    try {
      await useApi(`/api/v1/admin/tournaments/${id}/reject`, { 
        method: 'POST',
        body: { reason }
      })
      pendingTournaments.value = pendingTournaments.value.filter(t => t.id !== id)
    } catch (e: any) {
      throw e
    }
  }

  return {
    stats,
    users,
    usersMeta,
    pendingTournaments,
    isLoading,
    error,
    fetchStats,
    fetchUsers,
    toggleUserSuspension,
    changeUserRole,
    fetchPendingTournaments,
    approveTournament,
    rejectTournament
  }
})
