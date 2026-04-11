import type { User } from '~/types/auth'
import type { ApiResponse } from '~/types/api'

export const useAuth = () => {
  const user = useState<User | null>('auth_user', () => null)
  const isFetching = useState<boolean>('auth_fetching', () => false)
  const token = useCookie<string | null>('auth_token', { path: '/' })
  const config = useRuntimeConfig()

  const loggedIn = computed(() => !!token.value)

  let fetchPromise: Promise<User | null> | null = null

  const fetchUser = async (explicitToken?: string) => {
    const activeToken = explicitToken || token.value
    
    if (!activeToken) {
      user.value = null
      return null
    }

    if (fetchPromise && !explicitToken) return fetchPromise

    // Debugging: Kita tambahkan log agar Anda bisa cek di Console browser (F12)
    console.log('🔄 JuaraLeague: Mengambil profil user...')

    fetchPromise = (async () => {
      isFetching.value = true
      try {
        const { data } = await useApi<ApiResponse<User>>('/api/v1/me', {
          headers: {
            Authorization: `Bearer ${activeToken}`
          }
        })
        user.value = data
        console.log('✅ JuaraLeague: Profil user berhasil dimuat!', data.name)
        return data
      } catch (error) {
        console.error('❌ JuaraLeague: Gagal mengambil profil user', error)
        if (!explicitToken) {
          token.value = null
          user.value = null
        }
        return null
      } finally {
        isFetching.value = false
        fetchPromise = null
      }
    })()

    return fetchPromise
  }

  // Auto-fetch user when token changes (captured from URL/callback)
  watch(token, (newToken) => {
    // Hanya jalankan di sisi Client agar tidak gagal di SSR (Server Side Rendering)
    if (import.meta.client && newToken && !user.value && !isFetching.value) {
      fetchUser()
    }
  }, { immediate: true })

  // Force fetch user on client-side initialization if token exists
  if (import.meta.client) {
    onMounted(() => {
      if (token.value && !user.value && !isFetching.value) {
        fetchUser()
      }
    })
  }

  const login = async (credentials: any) => {
    try {
      const response = await useApi<ApiResponse<User>>('/api/v1/login', {
        method: 'POST',
        body: credentials
      })
      token.value = response.token || null
      user.value = response.data
      return response
    } catch (error) {
      throw error
    }
  }

  const register = async (userData: any) => {
    try {
      const response = await useApi<ApiResponse<User>>('/api/v1/register', {
        method: 'POST',
        body: userData
      })
      token.value = response.token || null
      user.value = response.data
      return response
    } catch (error) {
      throw error
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await useApi('/api/v1/logout', { method: 'POST' })
      }
    } catch (error) {
    } finally {
      token.value = null
      user.value = null
      navigateTo('/login')
    }
  }

  const updateProfile = async (payload: any) => {
    try {
      const { data } = await useApi<ApiResponse<User>>('/api/v1/users/me', {
        method: 'PUT',
        body: payload
      })
      user.value = data
      return data
    } catch (error) {
      throw error
    }
  }

  const updatePassword = async (payload: any) => {
    try {
      await useApi('/api/v1/users/password', {
        method: 'PUT',
        body: payload
      })
    } catch (error) {
      throw error
    }
  }

  return {
    user,
    token,
    loggedIn,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    updatePassword
  }
}
