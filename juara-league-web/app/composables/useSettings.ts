export const useSettings = () => {
  const settings = useState<any>('public_settings', () => null)
  const loading = ref(false)

  const fetchSettings = async () => {
    loading.value = true
    try {
      const response = await useApi('/api/v1/settings/public')
      settings.value = response.data
    } catch (e) {
      console.error('Failed to fetch settings', e)
    } finally {
      loading.value = false
    }
  }

  const isRegistrationEnabled = computed(() => settings.value?.registration_enabled !== false)
  const isMaintenanceMode = computed(() => settings.value?.maintenance_mode === true)

  return {
    settings,
    loading,
    fetchSettings,
    isRegistrationEnabled,
    isMaintenanceMode
  }
}
