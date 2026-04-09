export default defineNuxtRouteMiddleware((to, from) => {
  const { user, loggedIn } = useAuth()

  // Pastikan user sudah login
  if (!loggedIn.value) {
    return navigateTo('/login')
  }

  // Cek apakah user memiliki role admin atau super_admin
  const isAdmin = user.value?.roles?.some((role: string) => 
    ['admin', 'super_admin'].includes(role)
  )

  if (!isAdmin) {
    // Jika bukan admin, tendang balik ke dashboard biasa
    return navigateTo('/dashboard')
  }
})
