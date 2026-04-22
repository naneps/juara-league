import { defineStore } from 'pinia'
import type { Sport, StoreSportPayload, UpdateSportPayload, SportType } from '~/types/sport'

export const useSportStore = defineStore('sport', () => {
  const sports = ref<Sport[]>([])
  const pagination = ref({
    total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1
  })
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
  const fetchAllSportsAdmin = async (params: { page?: number, per_page?: number, search?: string, type?: any } = {}) => {
    isLoading.value = true
    error.value = null
    
    // Ensure params are clean (handle object from SelectMenu)
    const queryParams = { ...params }
    if (queryParams.type && typeof queryParams.type === 'object') {
      queryParams.type = queryParams.type.value
    }

    try {
      const response = await useApi<{ data: Sport[], meta: any }>('/api/v1/admin/sports', {
        params: queryParams
      })

      sports.value = response.data
      if (response.meta) {
        pagination.value = {
          total: response.meta.total,
          per_page: response.meta.per_page,
          current_page: response.meta.current_page,
          last_page: response.meta.last_page
        }
      }
      return response
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
    pagination,
    isLoading,

    error,
    fetchSports,
    fetchAllSportsAdmin,
    createSport,
    updateSport,
    deleteSport
  }
})
