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
      <div class="max-w-4xl mx-auto py-8 px-4">
        <UTabs :items="tabs" class="w-full" :ui="{ list: 'bg-neutral-50 dark:bg-white/5 border border-neutral-200 dark:border-white/5 rounded-2xl mb-8 p-1', trigger: 'rounded-xl' }">
          
          <!-- ═══════════════════════════════════════
               TAB 1: IDENTITAS
          ════════════════════════════════════════ -->
          <template #identity>
            <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5 shadow-2xl shadow-indigo-500/5">
              <div class="space-y-6">
                <!-- Section Header -->
                <div class="flex items-center gap-4 pb-5 border-b border-neutral-200 dark:border-white/5">
                  <div class="size-10 rounded-xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/20 shrink-0">
                    <UIcon name="i-lucide-award" class="size-5 text-indigo-400" />
                  </div>
                  <div>
                    <h3 class="font-black text-neutral-900 dark:text-white uppercase tracking-tighter">Identitas Brand</h3>
                    <p class="text-[10px] text-neutral-500 uppercase font-bold tracking-widest mt-0.5">Nama & Tagline Platform</p>
                  </div>
                </div>

                <!-- Fields — 2 kolom simetris -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <UFormField
                    label="Nama Platform"
                    required
                    help="Nama yang tampil di header, footer, dan email."
                  >
                    <UInput v-model="form.platform_name" size="lg" placeholder="Juara League" class="w-full" />
                  </UFormField>

                  <UFormField
                    label="Tagline Platform"
                    help="Slogan singkat yang merepresentasikan platform."
                  >
                    <UInput v-model="form.platform_tagline" size="lg" placeholder="Platform Turnamen Terlengkap" class="w-full" />
                  </UFormField>
                </div>
              </div>
            </UCard>
          </template>

          <!-- ═══════════════════════════════════════
               TAB 2: KONTROL PLATFORM
          ════════════════════════════════════════ -->
          <template #system>
            <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5">
              <div class="space-y-6">
                <!-- Section Header -->
                <div class="flex items-center gap-4 pb-5 border-b border-neutral-200 dark:border-white/5">
                  <div class="size-10 rounded-xl bg-orange-500/10 flex items-center justify-center border border-orange-500/20 shrink-0">
                    <UIcon name="i-lucide-sliders" class="size-5 text-orange-400" />
                  </div>
                  <div>
                    <h3 class="font-black text-neutral-900 dark:text-white uppercase tracking-tighter">Kontrol Platform</h3>
                    <p class="text-[10px] text-neutral-500 uppercase font-bold tracking-widest mt-0.5">Aktifkan atau nonaktifkan fitur operasional</p>
                  </div>
                </div>

                <!-- Toggle Rows — konsisten tingginya -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Maintenance Mode -->
                  <div class="flex items-start gap-4 p-4 rounded-2xl border border-neutral-200 dark:border-white/5 bg-neutral-50 dark:bg-white/[0.02] hover:border-red-500/20 transition-colors duration-200">
                    <div class="size-10 rounded-xl bg-red-500/10 flex items-center justify-center border border-red-500/20 shrink-0 mt-0.5">
                      <UIcon name="i-lucide-power" class="size-4 text-red-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-3 mb-2">
                        <span class="font-black text-neutral-900 dark:text-white text-xs uppercase tracking-widest">Maintenance Mode</span>
                        <USwitch v-model="form.maintenance_mode" color="error" class="shrink-0" />
                      </div>
                      <p class="text-[11px] text-neutral-500 leading-relaxed">
                        Hanya Admin yang bisa mengakses platform. Pengguna lain akan melihat halaman maintenance.
                      </p>
                    </div>
                  </div>

                  <!-- User Registration -->
                  <div class="flex items-start gap-4 p-4 rounded-2xl border border-neutral-200 dark:border-white/5 bg-neutral-50 dark:bg-white/[0.02] hover:border-emerald-500/20 transition-colors duration-200">
                    <div class="size-10 rounded-xl bg-emerald-500/10 flex items-center justify-center border border-emerald-500/20 shrink-0 mt-0.5">
                      <UIcon name="i-lucide-user-plus" class="size-4 text-emerald-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between gap-3 mb-2">
                        <span class="font-black text-neutral-900 dark:text-white text-xs uppercase tracking-widest">User Registration</span>
                        <USwitch v-model="form.registration_enabled" color="success" class="shrink-0" />
                      </div>
                      <p class="text-[11px] text-neutral-500 leading-relaxed">
                        Tutup pendaftaran akun baru sementara waktu. Pengguna yang ada tidak terpengaruh.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Status Indicators -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                  <div
                    class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-[11px] font-bold transition-all duration-300"
                    :class="form.maintenance_mode
                      ? 'bg-red-500/10 border border-red-500/20 text-red-400'
                      : 'bg-green-500/10 border border-green-500/20 text-green-400'"
                  >
                    <div class="size-2 rounded-full animate-pulse"
                      :class="form.maintenance_mode ? 'bg-red-400' : 'bg-green-400'"
                    />
                    {{ form.maintenance_mode ? 'Platform dalam mode pemeliharaan' : 'Platform berjalan normal' }}
                  </div>
                  <div
                    class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-[11px] font-bold transition-all duration-300"
                    :class="form.registration_enabled
                      ? 'bg-green-500/10 border border-green-500/20 text-green-400'
                      : 'bg-amber-500/10 border border-amber-500/20 text-amber-400'"
                  >
                    <div class="size-2 rounded-full animate-pulse"
                      :class="form.registration_enabled ? 'bg-green-400' : 'bg-amber-400'"
                    />
                    {{ form.registration_enabled ? 'Registrasi akun baru terbuka' : 'Registrasi akun baru ditutup' }}
                  </div>
                </div>
              </div>
            </UCard>
          </template>

          <!-- ═══════════════════════════════════════
               TAB 3: SOSIAL & KONTAK
          ════════════════════════════════════════ -->
          <template #contact>
            <UCard class="dark:bg-neutral-900/40 backdrop-blur-xl border border-neutral-200 dark:border-white/5">
              <div class="space-y-6">
                <!-- Section Header -->
                <div class="flex items-center gap-4 pb-5 border-b border-neutral-200 dark:border-white/5">
                  <div class="size-10 rounded-xl bg-sky-500/10 flex items-center justify-center border border-sky-500/20 shrink-0">
                    <UIcon name="i-lucide-share-2" class="size-5 text-sky-400" />
                  </div>
                  <div>
                    <h3 class="font-black text-neutral-900 dark:text-white uppercase tracking-tighter">Kontak & Sosial</h3>
                    <p class="text-[10px] text-neutral-500 uppercase font-bold tracking-widest mt-0.5">Email dukungan & tautan media sosial</p>
                  </div>
                </div>

                <!-- Email — full width -->
                <UFormField label="Email Dukungan" required help="Email resmi yang tampil untuk bantuan pengguna.">
                  <UInput v-model="form.contact_email" icon="i-lucide-mail" size="lg" placeholder="support@juaraleague.com" class="w-full" />
                </UFormField>

                <!-- Divider -->
                <div class="flex items-center gap-4">
                  <div class="flex-1 h-px bg-neutral-200 dark:bg-white/5" />
                  <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest shrink-0">Media Sosial</span>
                  <div class="flex-1 h-px bg-neutral-200 dark:bg-white/5" />
                </div>

                <!-- Social Links — 3 kolom simetris -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <UFormField label="Instagram">
                    <UInput
                      v-model="form.social_links.instagram"
                      icon="i-simple-icons-instagram"
                      placeholder="instagram.com/..."
                      class="w-full"
                    />
                  </UFormField>
                  <UFormField label="Twitter / X">
                    <UInput
                      v-model="form.social_links.twitter"
                      icon="i-simple-icons-x"
                      placeholder="x.com/..."
                      class="w-full"
                    />
                  </UFormField>
                  <UFormField label="Discord">
                    <UInput
                      v-model="form.social_links.discord"
                      icon="i-simple-icons-discord"
                      placeholder="discord.gg/..."
                      class="w-full"
                    />
                  </UFormField>
                </div>
              </div>
            </UCard>
          </template>

        </UTabs>

        <!-- Note Keamanan -->
        <div class="mt-8 p-5 rounded-2xl bg-amber-500/5 border border-amber-500/20 flex items-start gap-4">
          <div class="size-9 rounded-xl bg-amber-500/10 flex items-center justify-center shrink-0 border border-amber-500/20 mt-0.5">
            <UIcon name="i-lucide-triangle-alert" class="size-4 text-amber-400" />
          </div>
          <div>
            <p class="text-xs font-black text-amber-400 uppercase tracking-widest mb-1">Catatan Keamanan</p>
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
              Perubahan pada <strong class="text-neutral-700 dark:text-neutral-300">Kontrol Platform</strong> (Maintenance & Registrasi) berdampak instan kepada seluruh pengunjung. Pastikan Anda telah melakukan koordinasi sebelum mengaktifkan mode pemeliharaan.
            </p>
          </div>
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template>

