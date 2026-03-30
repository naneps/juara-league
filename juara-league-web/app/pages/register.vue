<script setup lang="ts">
const { register, loggedIn } = useAuth()

definePageMeta({
  layout: 'default'
})

const state = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const errors = ref<any>({})

async function onSubmit() {
  loading.value = true
  errors.value = {}
  try {
    await register(state)
    navigateTo('/')
  } catch (err: any) {
    if (err.data?.errors) {
      errors.value = err.data.errors
    } else {
      errors.value = { global: err.data?.message || 'Registrasi gagal. Silakan coba lagi.' }
    }
  } finally {
    loading.value = false
  }
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
    <div class="absolute top-0 right-1/2 translate-x-1/2 w-full max-w-4xl h-[500px] bg-primary-500/10 blur-[120px] rounded-full pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary-600/5 blur-[100px] rounded-full pointer-events-none"></div>
    
    <div class="max-w-md w-full relative z-10">
      <!-- Card Container -->
      <div class="bg-neutral-900/40 backdrop-blur-2xl p-8 rounded-[2rem] border border-white/5 shadow-2xl relative overflow-hidden group">
        <!-- Inner Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-500/10 blur-3xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>

        <div class="text-center mb-10">
          <div class="inline-flex items-center justify-center p-4 bg-primary-500/10 rounded-2xl mb-6 ring-1 ring-primary-500/20">
            <UIcon name="i-lucide-user-plus" class="text-primary-400 size-8" />
          </div>
          <h2 class="text-3xl font-black text-white tracking-tight mb-2">
            Mulai<span class="text-primary-500">Juara</span>
          </h2>
          <p class="text-neutral-400 font-medium">Daftar sekarang & mulai turnamen Anda!</p>
        </div>

        <div v-if="errors.global" class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-3">
          <UIcon name="i-lucide-alert-circle" class="size-5 flex-shrink-0" />
          {{ errors.global }}
        </div>

        <UForm :state="state" class="space-y-4" @submit="onSubmit">
          <UFormField label="Nama Lengkap" name="name" :error="errors.name?.[0]">
            <UInput 
              v-model="state.name" 
              placeholder="Mamad" 
              icon="i-lucide-user"
              size="lg"
              class="w-full"
              :ui="{ 
                base: 'bg-white/5 border-white/5 focus:ring-primary-500/50 rounded-xl transition-all duration-300',
                leading: 'pointer-events-none'
              }"
            />
          </UFormField>

          <UFormField label="Email" name="email" :error="errors.email?.[0]">
            <UInput 
              v-model="state.email" 
              type="email" 
              placeholder="nama@email.com" 
              icon="i-lucide-mail"
              size="lg"
              class="w-full"
              :ui="{ 
                base: 'bg-white/5 border-white/5 focus:ring-primary-500/50 rounded-xl transition-all duration-300',
                leading: 'pointer-events-none'
              }"
            />
          </UFormField>

          <UFormField label="Password" name="password" :error="errors.password?.[0]">
            <UInput 
              v-model="state.password" 
              type="password" 
              placeholder="••••••••" 
              icon="i-lucide-lock"
              size="lg"
              class="w-full"
              :ui="{ 
                base: 'bg-white/5 border-white/5 focus:ring-primary-500/50 rounded-xl transition-all duration-300',
                leading: 'pointer-events-none'
              }"
            />
          </UFormField>

          <UFormField label="Konfirmasi Password" name="password_confirmation">
            <UInput 
              v-model="state.password_confirmation" 
              type="password" 
              placeholder="••••••••" 
              icon="i-lucide-shield-check"
              size="lg"
              class="w-full"
              :ui="{ 
                base: 'bg-white/5 border-white/5 focus:ring-primary-500/50 rounded-xl transition-all duration-300',
                leading: 'pointer-events-none'
              }"
            />
          </UFormField>

          <UButton 
            type="submit" 
            color="primary" 
            block 
            size="xl" 
            :loading="loading"
            class="font-extrabold rounded-xl shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 transform active:scale-95 transition-all w-full mt-4"
          >
            Daftar Sekarang
          </UButton>
        </UForm>

        <p class="mt-8 text-center text-sm font-medium text-neutral-500">
          Sudah punya akun?
          <NuxtLink to="/login" class="text-primary-400 font-bold hover:underline ml-1">
            Masuk di sini
          </NuxtLink>
        </p>
      </div>

      <!-- Footer Info -->
      <p class="mt-8 text-center text-xs text-neutral-600 font-medium">
        Bergabung dengan ribuan pemain kompetitif lainnya.
      </p>
    </div>
  </div>
</template>
