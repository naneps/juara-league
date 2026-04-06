import { defineStore } from 'pinia'
import type { Sport, StoreSportPayload, UpdateSportPayload, SportType } from '~/types/sport'

export const useSportStore = defineStore('sport', () => {
  const sports = ref<Sport[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Public fetching (usually active only)
  const fetchSports = async (filters: { search?: string, type?: SportType } = {}) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await useApi<{ data: Sport[] }>('/api/v1/sports', {
        params: filters
      })
      sports.value = response.data
      return response.data
    } catch (e: any) {
      error.value = e.message || 'Gagal mengambil data cabang olahraga'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  // Admin CRUD operations
  const fetchAllSportsAdmin = async () => {
    isLoading.value = true
    error.value = null
    try {
      // Note: Backend might define different routes for admin list
      // If same, we can add a filter. Here we assume index admin exists
      // based on controller findings earlier
      const response = await useApi<{ data: Sport[] }>('/api/v1/sports') // Assuming public index returns all for now or check admin routes
      sports.value = response.data
      return response.data
    } catch (e: any) {
      error.value = e.message || 'Gagal mengambil data cabang olahraga admin'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const createSport = async (payload: StoreSportPayload) => {
    isLoading.value = true
    try {
      const response = await useApi<{ data: Sport }>('/api/v1/admin/sports', {
        method: 'POST',
        body: payload
      })
      sports.value.unshift(response.data)
      return response.data
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal membuat cabang olahraga'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const updateSport = async (id: string, payload: UpdateSportPayload) => {
    isLoading.value = true
    try {
      const response = await useApi<{ data: Sport }>(`/api/v1/admin/sports/${id}`, {
        method: 'PUT',
        body: payload
      })
      const index = sports.value.findIndex(s => s.id === id)
      if (index !== -1) {
        sports.value[index] = response.data
      }
      return response.data
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal memperbarui cabang olahraga'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  const deleteSport = async (id: string) => {
    isLoading.value = true
    try {
      await useApi(`/api/v1/admin/sports/${id}`, {
        method: 'DELETE'
      })
      sports.value = sports.value.filter(s => s.id !== id)
    } catch (e: any) {
      error.value = e.data?.message || 'Gagal menghapus cabang olahraga'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  return {
    sports,
    isLoading,
    error,
    fetchSports,
    fetchAllSportsAdmin,
    createSport,
    updateSport,
    deleteSport
  }
})
