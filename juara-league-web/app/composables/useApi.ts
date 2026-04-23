export const useApi = <T = any>(path: string, options: any = {}) => {
  const config = useRuntimeConfig()
  const token = useCookie('auth_token', { path: '/' })

  const user = useState('auth_user')

  return $fetch<T>(path, {
    baseURL: config.public.apiUrl,
    ...options,
    headers: {
      Accept: 'application/json',
      ...(token.value ? { Authorization: `Bearer ${token.value}` } : {}),
      ...options.headers,
    },
    async onResponseError({ response }: { response: any }) {
      // Handle Maintenance Mode
      if (response.status === 503 && response._data?.code === 'MAINTENANCE_MODE') {
        const currentPath = useRoute().path

        // Jangan redirect jika:
        // 1. Sudah di halaman maintenance
        // 2. Sedang di halaman login (biarkan admin tetap bisa login)
        // 3. Request yang gagal adalah endpoint login itu sendiri
        //    (backend sudah whitelist /login, jadi 503 di sini bukan dari maintenance)
        const isLoginPage = currentPath === '/login'
        const isLoginRequest = path.includes('/login')
        const isMaintenancePage = currentPath === '/maintenance'

        if (!isMaintenancePage && !isLoginPage && !isLoginRequest) {
          return navigateTo('/maintenance')
        }
      }

      if (response.status === 401) {
        // Only clear the session if we're on a page that strictly requires it
        // and we aren't currently in the middle of an auth process
        const currentPath = useRoute().path
        const isAuthPage = currentPath === '/login' || currentPath === '/register' || currentPath.startsWith('/auth/')
        
        if (!isAuthPage && currentPath !== '/') {
          token.value = null
          user.value = null
          navigateTo('/login')
        }
      }
    }
  })
}
