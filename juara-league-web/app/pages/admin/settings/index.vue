<script setup lang="ts">
import { useAdminStore } from '~/stores/admin.store'

definePageMeta({
  layout: 'dashboard',
  middleware: 'admin'
})

const adminStore = useAdminStore()
const { settings, isLoading } = storeToRefs(adminStore)
const toast = useToast()

const tabs = [
  { label: 'Identitas', icon: 'i-lucide-fingerprint', slot: 'identity' },
  { label: 'Kontrol Platform', icon: 'i-lucide-sliders', slot: 'system' },
  { label: 'Sosial & Kontak', icon: 'i-lucide-share-2', slot: 'contact' }
]

// Form states
const form = reactive({
  platform_name: '',
  platform_tagline: '',
  maintenance_mode: false,
  registration_enabled: true,
  contact_email: '',
  social_links: {
    instagram: '',
    twitter: '',
    discord: ''
  }
})

const { refresh, status } = await useAsyncData('admin-settings-load', async () => {
  await adminStore.fetchSettings()
  if (settings.value) {
    // Sync local form with store data
    form.platform_name = settings.value.identity?.platform_name || ''
    form.platform_tagline = settings.value.identity?.platform_tagline || ''
    form.maintenance_mode = settings.value.system?.maintenance_mode === true
    form.registration_enabled = settings.value.system?.registration_enabled === true
    form.contact_email = settings.value.contact?.contact_email || ''
    form.social_links = { ...settings.value.contact?.social_links }
  }
  return true
})

const handleSave = async () => {
  const payload = [
    { key: 'platform_name', value: form.platform_name },
    { key: 'platform_tagline', value: form.platform_tagline },
    { key: 'maintenance_mode', value: form.maintenance_mode },
    { key: 'registration_enabled', value: form.registration_enabled },
    { key: 'contact_email', value: form.contact_email },
    { key: 'social_links', value: form.social_links }
  ]

  try {
    await adminStore.updateSettings(payload)
    toast.add({
      title: 'Pengaturan Disimpan',
      description: 'Seluruh perubahan sistem telah berhasil diterapkan.',
      color: 'success'
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal Menyimpan',
      description: err.data?.message || 'Terjadi kesalahan saat memperbarui sistem.',
      color: 'danger'
    })
  }
}
</script>

<template>
  <UDashboardPanel id="system_settings_container">
    <template #header>
      <UDashboardNavbar title="Pengaturan Sistem" description="Konfigurasi identitas dan kontrol operasional platform.">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
        <template #right>
          <UButton
            label="Simpan Perubahan"
            icon="i-lucide-save"
            color="indigo"
            class="font-bold uppercase tracking-widest text-[10px] px-6"
            :loading="isLoading"
            @click="handleSave"
          />
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="max-w-4xl mx-auto py-8">
        <UTabs :items="tabs" class="w-full" :ui="{ list: 'bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5 rounded-2xl mb-8 p-1', trigger: 'rounded-xl' }">
          
          <!-- IDENTITY TAB -->
          <template #identity>
            <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5 shadow-2xl shadow-indigo-500/5">
              <div class="space-y-6">
                <div class="flex items-center gap-4 pb-4 border-b border-white/5">
                  <div class="size-10 rounded-xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20">
                    <UIcon name="i-lucide-award" class="size-5 text-indigo-400" />
                  </div>
                  <div>
                    <h3 class="font-black dark:text-white uppercase tracking-tighter">Identitas Brand</h3>
                    <p class="text-[10px] text-neutral-500 uppercase font-bold tracking-widest">Nama & Tagline Platform</p>
                  </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                  <UFormField label="Nama Platform" required help="Nama ini akan muncul di header, footer, dan email.">
                    <UInput v-model="form.platform_name" size="lg" placeholder="Juara League" />
                  </UFormField>

                  <UFormField label="Tagline Platform" help="Slogan singkat yang merepresentasikan platform.">
                    <UInput v-model="form.platform_tagline" size="lg" placeholder="Platform Turnamen Terlengkap" />
                  </UFormField>
                </div>
              </div>
            </UCard>
          </template>

          <!-- SYSTEM CONTROL TAB -->
          <template #system>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <UIcon name="i-lucide-power" class="size-5 text-error-500" />
                      <span class="font-bold dark:text-white uppercase text-xs tracking-widest">Maintenance Mode</span>
                    </div>
                    <USwitch v-model="form.maintenance_mode" color="error" />
                  </div>
                  <p class="text-[11px] text-neutral-500 leading-relaxed italic">
                    Saat diaktifkan, seluruh pengguna (kecuali Admin) hanya akan melihat halaman "Under Maintenance".
                  </p>
                </div>
              </UCard>

              <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <UIcon name="i-lucide-user-plus" class="size-5 text-emerald-500" />
                      <span class="font-bold dark:text-white uppercase text-xs tracking-widest">User Registration</span>
                    </div>
                    <USwitch v-model="form.registration_enabled" color="emerald" />
                  </div>
                  <p class="text-[11px] text-neutral-500 leading-relaxed italic">
                    Matikan ini untuk menutup pendaftaran akun baru sementara waktu.
                  </p>
                </div>
              </UCard>
            </div>
          </template>

          <!-- SOCIAL & CONTACT TAB -->
          <template #contact>
            <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5">
              <div class="space-y-8">
                <UFormField label="Email Dukungan" required help="Email resmi untuk bantuan pengguna.">
                  <UInput v-model="form.contact_email" icon="i-lucide-mail" size="lg" />
                </UFormField>

                <div class="space-y-4 pt-4 border-t border-white/5">
                  <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Media Sosial</p>
                  
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <UFormField label="Instagram URL">
                      <UInput v-model="form.social_links.instagram" icon="i-simple-icons-instagram" placeholder="https://instagram.com/..." />
                    </UFormField>
                    <UFormField label="Twitter/X URL">
                      <UInput v-model="form.social_links.twitter" icon="i-simple-icons-x" placeholder="https://x.com/..." />
                    </UFormField>
                    <UFormField label="Discord Invite">
                      <UInput v-model="form.social_links.discord" icon="i-simple-icons-discord" placeholder="https://discord.gg/..." />
                    </UFormField>
                  </div>
                </div>
              </div>
            </UCard>
          </template>

        </UTabs>

        <div class="mt-12 p-6 rounded-3xl bg-neutral-50 dark:bg-white/[0.02] border border-dashed border-neutral-200 dark:border-white/5 flex items-center gap-6">
           <div class="size-16 rounded-2xl bg-amber-500/10 flex items-center justify-center shrink-0 border border-amber-500/20">
             <UIcon name="i-lucide-info" class="size-8 text-amber-500/60" />
           </div>
           <div class="flex-1">
             <p class="text-xs font-black dark:text-white uppercase tracking-widest mb-1 italic">Catatan Keamanan</p>
             <p class="text-[11px] text-neutral-500 leading-relaxed font-medium">
               Perubahan pada **Kontrol Platform** (Maintenance & Registrasi) berdampak instan kepada seluruh pengunjung. Pastikan Anda telah melakukan koordinasi sebelum mengaktifkan mode pemeliharaan.
             </p>
           </div>
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>
