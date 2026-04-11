<script setup lang="ts">
const route = useRoute()
const { token, fetchUser } = useAuth()

definePageMeta({
  layout: 'default'
})

onMounted(async () => {
  const queryToken = route.query.token as string
  
  if (queryToken) {
    // Trim and explicitly set
    token.value = queryToken.trim()
    
    // Give Nuxt/Browser a tiny moment to commit the cookie
    await nextTick()
    
    let success = false
    let authError = ''
    
    try {
      // Kita panggil fetchUser dengan token mentah dari URL
      // Ini jauh lebih aman dan cepat daripada nunggu cookie
      const userData = await fetchUser(queryToken.trim())
      if (userData) {
        success = true
      } else {
        authError = 'Session activation failed'
      }
    } catch (e) {
      authError = 'Authentication error'
    }

    if (success) {
      await navigateTo('/')
    } else {
      await navigateTo(`/login?error=${encodeURIComponent(authError)}`)
    }
  } else {
    navigateTo('/login?error=No token received')
  }
})
</script>

<template>
  <div class="min-h-[60vh] flex flex-col items-center justify-center p-4">
    <div class="flex flex-col items-center gap-6">
      <div class="relative">
        <div class="size-16 border-4 border-primary-500/20 border-t-primary-500 rounded-full animate-spin"></div>
        <UIcon name="i-lucide-shield-check" class="absolute inset-0 m-auto size-6 text-primary-400" />
      </div>
      <div class="text-center">
        <h2 class="text-2xl font-bold text-white mb-2">Mengautentikasi...</h2>
        <p class="text-neutral-400">Mohon tunggu sebentar, kami sedang menyiapkan akun Anda.</p>
      </div>
    </div>
  </div>
</template>
