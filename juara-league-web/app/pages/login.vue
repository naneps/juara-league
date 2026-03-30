<script setup lang="ts">
const { login, loggedIn } = useAuth()
const config = useRuntimeConfig()

definePageMeta({
  layout: 'default'
})

const state = reactive({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

async function onSubmit() {
  loading.value = true
  error.value = ''
  try {
    await login(state)
    navigateTo('/')
  } catch (err: any) {
    error.value = err.data?.message || 'Login gagal. Silakan cek kembali email dan password Anda.'
  } finally {
    loading.value = false
  }
}

function loginWithGoogle() {
  window.location.href = `${config.public.apiUrl}/api/v1/auth/google/redirect`
}

onMounted(() => {
  if (loggedIn.value) {
    navigateTo('/')
  }
})
</script>

<template>
  <div class="min-h-screen relative flex items-start justify-center px-4 pt-32 pb-12 sm:px-6 lg:px-8 overflow-hidden bg-neutral-950">
    <!-- Background Decoration -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-[500px] bg-primary-500/10 blur-[120px] rounded-full pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-primary-600/5 blur-[100px] rounded-full pointer-events-none"></div>
    
    <div class="max-w-md w-full relative z-10">
      <!-- Card Container -->
      <div class="bg-neutral-900/40 backdrop-blur-2xl p-8 rounded-[2rem] border border-white/5 shadow-2xl relative overflow-hidden group">
        <!-- Inner Glow -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-primary-500/10 blur-3xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

        <div class="text-center mb-10">
          <div class="inline-flex items-center justify-center p-4 bg-primary-500/10 rounded-2xl mb-6 ring-1 ring-primary-500/20">
            <UIcon name="i-lucide-trophy" class="text-primary-400 size-8" />
          </div>
          <h2 class="text-3xl font-black text-white tracking-tight mb-2">
            Juara<span class="text-primary-500">League</span>
          </h2>
          <p class="text-neutral-400 font-medium">Selamat datang kembali, Juara!</p>
        </div>

        <div v-if="error" class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-3">
          <UIcon name="i-lucide-alert-circle" class="size-5 flex-shrink-0" />
          {{ error }}
        </div>

        <UForm :state="state" class="space-y-5" @submit="onSubmit">
          <UFormField label="Email" name="email">
            <UInput 
              v-model="state.email" 
              type="email" 
              placeholder="nama@email.com" 
              icon="i-lucide-mail"
              size="xl"
              class="w-full"
              :ui="{ 
                base: 'bg-white/5 border-white/5 focus:ring-primary-500/50 rounded-xl transition-all duration-300',
                leading: 'pointer-events-none'
              }"
            />
          </UFormField>

          <UFormField label="Password" name="password">
            <UInput 
              v-model="state.password" 
              type="password" 
              placeholder="••••••••" 
              icon="i-lucide-lock"
              size="xl"
              class="w-full"
              :ui="{ 
                base: 'bg-white/5 border-white/5 focus:ring-primary-500/50 rounded-xl transition-all duration-300',
                leading: 'pointer-events-none'
              }"
            />
          </UFormField>

          <div class="flex items-center justify-end">
            <NuxtLink to="#" class="text-sm font-semibold text-primary-400 hover:text-primary-300 transition-colors">
              Lupa password?
            </NuxtLink>
          </div>

          <UButton 
            type="submit" 
            color="primary" 
            block 
            size="xl" 
            :loading="loading"
            class="font-extrabold rounded-xl shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 transform active:scale-95 transition-all w-full"
          >
            Masuk Sekarang
          </UButton>

          <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-white/5"></div>
            </div>
            <div class="relative flex justify-center text-[10px] uppercase tracking-[0.2em] font-bold">
              <span class="px-4 bg-transparent text-neutral-500">Atau Lanjut Dengan</span>
            </div>
          </div>

          <UButton 
            variant="ghost" 
            color="neutral" 
            block 
            size="xl" 
            icon="i-logos-google-icon"
            class="font-bold border border-white/5 hover:bg-white/5 rounded-xl grayscale hover:grayscale-0 transition-all duration-500"
            @click="loginWithGoogle"
          >
            Google Account
          </UButton>
        </UForm>

        <p class="mt-10 text-center text-sm font-medium text-neutral-500">
          Belum punya akun?
          <NuxtLink to="/register" class="text-primary-400 font-bold hover:underline ml-1">
            Daftar Sekarang
          </NuxtLink>
        </p>
      </div>

      <!-- Footer Info -->
      <p class="mt-8 text-center text-xs text-neutral-600 font-medium">
        &copy; 2026 Juara League Platform. Safe & Secure Authentication.
      </p>
    </div>
  </div>
</template>
