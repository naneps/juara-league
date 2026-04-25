<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useSportStore } from '~/stores/sport.store'
import { useTournamentStore } from '~/stores/tournamentStore'
import type { BracketType, ParticipantType, StoreTournamentPayload, TournamentMode } from '~/types/tournament'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const tournamentStore = useTournamentStore()
const sportStore = useSportStore()
const { isLoading } = storeToRefs(tournamentStore)
const { sports } = storeToRefs(sportStore)
const toast = useToast()
const { t } = useI18n()
const route = useRoute()
const editId = computed(() => route.query.edit as string | undefined)
const isEdit = computed(() => !!editId.value)

const state = reactive<StoreTournamentPayload>({
  sport_id: '',
  title: '',
  description: '',
  category: 'Pro',
  mode: 'open',
  venue_type: 'online',
  participant_type: 'individual',
  team_size: undefined,
  bracket_type: undefined,
  max_participants: 16,
  prize_pool: 0,
  prize_description: '',
  entry_fee: 0,
  registration_start_at: '',
  registration_end_at: '',
  start_at: '',
  venue: 'Online',
  banner_url: ''
})

watch(() => state.venue_type, (newType) => {
  if (newType === 'online') {
    state.venue = 'Online'
  } else if (state.venue === 'Online') {
    state.venue = ''
  }
})

const formatDateForInput = (date?: string | Date) => {
  if (!date) return ''
  const d = new Date(date)
  if (isNaN(d.getTime())) return ''
  const pad = (n: number) => n.toString().padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

onMounted(async () => {
  try {
    await sportStore.fetchSports()
    
    if (isEdit.value && editId.value) {
      const tournament = await tournamentStore.getBySlug(editId.value)
      if (tournament) {
        state.sport_id = tournament.sport_id || tournament.sport?.id || ''
        state.title = tournament.title
        state.description = tournament.description
        state.category = tournament.category
        state.mode = tournament.mode
        state.participant_type = tournament.participant_type
        state.team_size = tournament.team_size
        state.bracket_type = tournament.bracket_type
        state.max_participants = tournament.max_participants
        state.prize_pool = Number(tournament.prize_pool)
        state.prize_description = tournament.prize_description || ''
        state.entry_fee = Number(tournament.entry_fee)
        state.registration_start_at = formatDateForInput(tournament.registration_start_at)
        state.registration_end_at = formatDateForInput(tournament.registration_end_at)
        state.start_at = formatDateForInput(tournament.start_at)
        state.venue = tournament.venue || ''
        state.banner_url = tournament.banner_url || ''
        state.venue_type = (tournament as any).venue_type || (state.venue?.toLowerCase() === 'online' ? 'online' : 'offline')
      }
    } else if (!state.sport_id && sports.value && sports.value.length > 0) {
      state.sport_id = sports.value[0]?.id || ''
    }
  } catch (e) {
    console.error('Error initializing form:', e)
  }
})

const selectedSport = computed(() => sports.value?.find(s => s.id === state.sport_id))

const sportOptions = computed(() => sports.value?.map(s => ({
  value: s.id,
  label: s.name,
  icon_url: s.icon_url
})) || [])

const selectedSportOption = computed({
  get: () => sportOptions.value.find(s => s.value === state.sport_id) || undefined,
  set: (val: any) => { if (val) state.sport_id = val.value }
})

const enrollmentModes = computed(() => [
  { label: t('tournament_form.modes.open'), value: 'open', icon: 'i-lucide-lock-open', desc: t('tournament_form.modes.open_desc') },
  { label: t('tournament_form.modes.invite'), value: 'invite', icon: 'i-lucide-lock', desc: t('tournament_form.modes.invite_desc') }
])

const venueTypes = computed(() => [
  { label: t('tournament_form.venues.online'), value: 'online', icon: 'i-lucide-globe' },
  { label: t('tournament_form.venues.offline'), value: 'offline', icon: 'i-lucide-map-pin' }
])

const participantTypes = computed(() => [
  { label: t('tournament_form.participants.individual'), value: 'individual', icon: 'i-lucide-user' },
  { label: t('tournament_form.participants.team'), value: 'team', icon: 'i-lucide-users' }
])

const bracketTypes: { label: string, value: BracketType }[] = [
  { label: 'Single Elimination', value: 'single' },
  { label: 'Double Elimination', value: 'double' },
  { label: 'Round Robin', value: 'round_robin' },
  { label: 'Swiss System', value: 'swiss' },
  { label: 'Group Stage', value: 'group_stage' }
]

const selectedBracketType = computed({
  get: () => bracketTypes.find(b => b.value === state.bracket_type) || null,
  set: (val: any) => { if (val) state.bracket_type = val.value }
})

const formValid = computed(() => {
  return state.title && state.sport_id && state.start_at
})

const checks = computed(() => [
  { label: t('tournament_form.checklist.title'), done: !!state.title },
  { label: t('tournament_form.checklist.sport'), done: !!state.sport_id },
  { label: t('tournament_form.checklist.start_at'), done: !!state.start_at },
  { label: t('tournament_form.checklist.description'), done: !!state.description },
])

const onSubmit = async () => {
  if (!state.sport_id) {
    toast.add({ title: t('tournament_form.toast.sport_required'), color: 'error' })
    return
  }
  try {
    if (isEdit.value && editId.value) {
      await tournamentStore.updateTournament(editId.value, state)
      toast.add({
        title: t('tournament_form.toast.success_title'),
        description: t('tournament_form.toast.update_success'),
        color: 'success'
      })
    } else {
      await tournamentStore.createTournament(state)
      toast.add({
        title: t('tournament_form.toast.success_title'),
        description: t('tournament_form.toast.create_success'),
        color: 'success'
      })
    }
    navigateTo(`/dashboard/tournaments`)
  } catch (e: any) {
    toast.add({
      title: isEdit.value ? t('tournament_form.toast.update_failed') : t('tournament_form.toast.create_failed'),
      description: e.data?.message || t('tournament_form.toast.system_error'),
      color: 'error'
    })
  }
}
</script>

<template>
  <UDashboardPanel id="tournament-create" grow>
    <template #header>
      <UDashboardNavbar>
        <template #leading>
          <div class="flex items-center gap-3">
            <UButton
              to="/dashboard/tournaments"
              variant="ghost"
              color="neutral"
              icon="i-lucide-arrow-left"
              size="sm"
            />
            <div class="h-5 w-px bg-white/10" />
            <div class="flex items-center gap-2">
              <div class="size-2 rounded-full bg-primary-500 animate-pulse" />
              <span class="text-xs font-semibold text-neutral-400 uppercase tracking-widest">{{ isEdit ? $t('tournament_form.edit_mode') : $t('tournament_form.draft_mode') }}</span>
            </div>
          </div>
        </template>
        <template #title>
          <span class="font-black text-white tracking-tight">{{ isEdit ? $t('tournament_form.edit_title') : $t('tournament_form.create_title') }}</span>
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div class="min-h-full bg-neutral-50 dark:bg-neutral-950 transition-colors duration-300">

        <!-- Page Header -->
        <div class="border-b border-neutral-200 dark:border-white/5 bg-white/80 dark:bg-neutral-900/80 backdrop-blur-xl px-8 py-6 sticky top-0 z-10 transition-all duration-300">
          <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="flex flex-col">
                <p class="text-[10px] font-bold text-primary-500 uppercase tracking-[0.2em] mb-1">{{ isEdit ? $t('tournament_form.edit_info') : $t('tournament_form.new_tournament') }}</p>
                <h1 class="text-2xl font-black text-neutral-900 dark:text-white tracking-tight leading-none flex items-center gap-2">
                  {{ $t('tournament_form.config_title') }} <span class="text-neutral-400 dark:text-neutral-500">{{ $t('tournament_form.config_subtitle') }}</span>
                </h1>
              </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-600 font-medium bg-neutral-100 dark:bg-white/5 px-3 py-1.5 rounded-full border border-neutral-200 dark:border-white/5">
              <UIcon name="i-lucide-shield-check" class="size-3.5" />
              {{ isEdit ? $t('tournament_form.all_saved') : $t('tournament_form.auto_saved') }}
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto px-8 py-10">
          <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-10">

            <!-- ─── LEFT: FORM ─── -->
            <UForm :state="state" class="space-y-2" @submit="onSubmit">

              <!-- SECTION 1: Identitas -->
              <div class="rounded-2xl border border-neutral-200 dark:border-white/5 bg-white dark:bg-white/[0.02] overflow-hidden shadow-sm dark:shadow-none">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-neutral-200 dark:border-white/5 bg-neutral-50 dark:bg-white/[0.02]">
                  <div class="flex items-center justify-center size-7 rounded-lg bg-primary-500/10 text-primary-500 dark:text-primary-400">
                    <UIcon name="i-lucide-flag" class="size-3.5" />
                  </div>
                  <span class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">{{ $t('tournament_form.section_identity') }}</span>
                  <div class="ml-auto h-px flex-1 bg-neutral-200 dark:bg-white/5 max-w-[80px]" />
                </div>

                <div class="p-6 space-y-5">
                  <UFormField :label="$t('tournament_form.field_title')" name="title" required>
                    <UInput
                      v-model="state.title"
                      :placeholder="$t('tournament_form.field_title_placeholder')"
                      size="xl"
                      class="w-full"
                      :ui="{ base: 'font-semibold' }"
                    />
                  </UFormField>

                  <div class="grid grid-cols-2 gap-4">
                    <UFormField :label="$t('tournament_form.field_sport')" name="sport_id" required>
                      <USelectMenu
                        v-model="selectedSportOption"
                        :items="sportOptions"
                        size="xl"
                        :placeholder="$t('tournament_form.field_sport_placeholder')"
                        :loading="sportStore.isLoading"
                        class="w-full"
                        :search-input="{ placeholder: $t('tournament_form.field_sport_search'), icon: 'i-lucide-search' }"
                      />
                    </UFormField>
                    <UFormField :label="$t('tournament_form.field_category')" name="category" required>
                      <UInput
                        v-model="state.category"
                        :placeholder="$t('tournament_form.field_category_placeholder')"
                        size="xl"
                        class="w-full"
                      />
                    </UFormField>
                  </div>

                  <div class="grid grid-cols-2 gap-6">
                    <UFormField :label="$t('tournament_form.field_enrollment')" name="mode" required>
                      <div class="grid grid-cols-2 gap-3 mt-1">
                        <button
                          v-for="m in enrollmentModes"
                          :key="m.value"
                          type="button"
                          @click="state.mode = m.value"
                          :class="[
                            'flex flex-col items-start gap-1 px-4 py-3 rounded-xl border text-sm font-semibold transition-all duration-200',
                            state.mode === m.value
                              ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                              : 'border-white/8 bg-white/[0.02] text-neutral-500 hover:border-white/15 hover:text-neutral-300'
                          ]"
                        >
                          <div class="flex items-center gap-2">
                            <UIcon :name="m.icon" class="size-3.5 shrink-0" />
                            {{ m.label }}
                          </div>
                          <span class="text-[10px] font-medium opacity-60 leading-tight">{{ m.desc }}</span>
                        </button>
                      </div>
                    </UFormField>

                    <UFormField :label="$t('tournament_form.field_venue_type')" name="venue_type" required>
                      <div class="grid grid-cols-2 gap-3 mt-1">
                        <button
                          v-for="v in venueTypes"
                          :key="v.value"
                          type="button"
                          @click="state.venue_type = v.value"
                          :class="[
                            'flex items-center justify-center gap-2 px-4 py-3 rounded-xl border text-sm font-semibold transition-all duration-200',
                            state.venue_type === v.value
                              ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                              : 'border-neutral-200 dark:border-white/8 bg-white dark:bg-white/[0.02] text-neutral-500 hover:border-neutral-300 dark:hover:border-white/15 hover:text-neutral-700 dark:hover:text-neutral-300'
                          ]"
                        >
                          <UIcon :name="v.icon" class="size-4 shrink-0" />
                          {{ v.label }}
                        </button>
                      </div>
                    </UFormField>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 items-start">
                    <UFormField :label="$t('tournament_form.field_participant_type')" name="participant_type" required class="flex-1">
                      <div class="grid grid-cols-2 gap-3 mt-1">
                        <button
                          v-for="p in participantTypes"
                          :key="p.value"
                          type="button"
                          @click="state.participant_type = p.value"
                          :class="[
                            'flex items-center gap-3 px-4 py-3.5 rounded-xl border text-sm font-semibold transition-all duration-200',
                            state.participant_type === p.value
                              ? 'border-primary-500 bg-primary-500/10 text-primary-400'
                              : 'border-white/8 bg-white/[0.02] text-neutral-500 hover:border-white/15 hover:text-neutral-300'
                          ]"
                        >
                          <UIcon :name="p.icon" class="size-4 shrink-0" />
                          {{ p.label }}
                        </button>
                      </div>
                    </UFormField>

                    <Transition
                      enter-active-class="transition-all duration-300 ease-out flex-shrink-0"
                      enter-from-class="opacity-0 -translate-x-4 max-w-0"
                      enter-to-class="opacity-100 translate-x-0 w-[180px]"
                      leave-active-class="transition-all duration-200 ease-in flex-shrink-0"
                      leave-from-class="opacity-100 translate-x-0 w-[180px]"
                      leave-to-class="opacity-0 -translate-x-4 max-w-0"
                    >
                      <UFormField v-if="state.participant_type === 'team'" :label="$t('tournament_form.field_team_size')" name="team_size" required class="w-[180px]">
                        <UInput
                          v-model.number="state.team_size"
                          type="number"
                          placeholder="5"
                          size="xl"
                          icon="i-lucide-users"
                          class="w-full mt-1"
                        />
                      </UFormField>
                    </Transition>
                  </div>

                  <UFormField :label="$t('tournament_form.field_description')" name="description" required>
                    <UTextarea
                      v-model="state.description"
                      :placeholder="$t('tournament_form.field_description_placeholder')"
                      :rows="4"
                      size="xl"
                      class="w-full"
                    />
                  </UFormField>

                  <UFormField :label="$t('tournament_form.field_banner')" name="banner_url">
                    <UInput
                      v-model="state.banner_url"
                      placeholder="https://..."
                      size="xl"
                      icon="i-lucide-image"
                      class="w-full"
                    />
                  </UFormField>
                </div>
              </div>

              <!-- SECTION 2: Jadwal -->
              <div class="rounded-2xl border border-neutral-200 dark:border-white/5 bg-white dark:bg-white/[0.02] overflow-hidden shadow-sm dark:shadow-none">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-neutral-200 dark:border-white/5 bg-neutral-50 dark:bg-white/[0.02]">
                  <div class="flex items-center justify-center size-7 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400">
                    <UIcon name="i-lucide-calendar-days" class="size-3.5" />
                  </div>
                  <span class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">{{ $t('tournament_form.section_schedule') }}</span>
                  <div class="ml-auto h-px flex-1 bg-neutral-200 dark:bg-white/5 max-w-[80px]" />
                </div>

                <div class="p-6 space-y-5">
                  <!-- Registration window -->
                  <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-3">{{ $t('tournament_form.field_reg_period') }}</p>
                    <div class="grid grid-cols-2 gap-4">
                      <UFormField :label="$t('tournament_form.field_reg_start')" name="registration_start_at">
                        <UInput v-model="state.registration_start_at" type="datetime-local" size="xl" class="w-full" />
                      </UFormField>
                      <UFormField :label="$t('tournament_form.field_reg_end')" name="registration_end_at">
                        <UInput v-model="state.registration_end_at" type="datetime-local" size="xl" class="w-full" />
                      </UFormField>
                    </div>
                  </div>

                  <!-- Tournament kickoff -->
                  <UFormField :label="$t('tournament_form.field_start_at')" name="start_at" required>
                    <UInput v-model="state.start_at" type="datetime-local" size="xl" class="w-full" />
                  </UFormField>
                </div>
              </div>

              <!-- SECTION 3: Aturan & Logistik -->
              <div class="rounded-2xl border border-neutral-200 dark:border-white/5 bg-white dark:bg-white/[0.02] overflow-hidden shadow-sm dark:shadow-none">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-neutral-200 dark:border-white/5 bg-neutral-50 dark:bg-white/[0.02]">
                  <div class="flex items-center justify-center size-7 rounded-lg bg-amber-500/10 text-amber-500 dark:text-amber-400">
                    <UIcon name="i-lucide-settings-2" class="size-3.5" />
                  </div>
                  <span class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">{{ $t('tournament_form.section_rules') }}</span>
                  <div class="ml-auto h-px flex-1 bg-neutral-200 dark:bg-white/5 max-w-[80px]" />
                </div>

                <div class="p-6 space-y-5">
                  <div class="grid grid-cols-2 gap-4">
                    <UFormField :label="$t('tournament_form.field_bracket')" name="bracket_type">
                      <USelectMenu
                        v-model="selectedBracketType"
                        :items="bracketTypes"
                        size="xl"
                        class="w-full"
                        :placeholder="$t('tournament_form.field_bracket_placeholder')"
                      >
                        <template #description>
                          <span class="text-[10px] text-neutral-500 italic">{{ $t('tournament_form.field_bracket_desc') }}</span>
                        </template>
                      </USelectMenu>
                    </UFormField>
                    <UFormField :label="$t('tournament_form.field_max_participants')" name="max_participants" required>
                      <UInput
                        v-model.number="state.max_participants"
                        type="number"
                        size="xl"
                        icon="i-lucide-users"
                        class="w-full"
                      />
                    </UFormField>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <UFormField :label="$t('tournament_form.field_prize')" name="prize_pool">
                      <UInput
                        v-model.number="state.prize_pool"
                        type="number"
                        size="xl"
                        icon="i-lucide-trophy"
                        placeholder="0"
                        class="w-full"
                      />
                    </UFormField>
                    <UFormField :label="$t('tournament_form.field_entry_fee')" name="entry_fee">
                      <UInput
                        v-model.number="state.entry_fee"
                        type="number"
                        size="xl"
                        icon="i-lucide-ticket"
                        :placeholder="$t('tournament_form.field_entry_fee_free')"
                        class="w-full"
                      />
                    </UFormField>
                  </div>

                  <UFormField :label="$t('tournament_form.field_prize_desc')" name="prize_description">
                    <UTextarea
                      v-model="state.prize_description"
                      :placeholder="$t('tournament_form.field_prize_desc_placeholder')"
                      :rows="3"
                      size="xl"
                      class="w-full"
                    />
                  </UFormField>

                  <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                  >
                    <UFormField v-if="state.venue_type === 'offline'" :label="$t('tournament_form.field_venue')" name="venue" required>
                      <UInput
                        v-model="state.venue"
                        :placeholder="$t('tournament_form.field_venue_placeholder')"
                        size="xl"
                        icon="i-lucide-map-pin"
                        class="w-full"
                      />
                    </UFormField>
                  </Transition>
                </div>
              </div>

              <!-- Submit -->
              <div class="pt-2">
                <UButton
                  type="submit"
                  color="primary"
                  size="xl"
                  block
                  :loading="isLoading"
                  :disabled="!formValid"
                  class="rounded-xl font-black uppercase tracking-widest h-14 shadow-xl shadow-primary-500/10 transition-all duration-300"
                  :class="{ 'opacity-40': !formValid }"
                >
                  <template #leading>
                    <UIcon name="i-lucide-send" class="size-4" />
                  </template>
                  {{ isEdit ? $t('tournament_form.submit_save') : $t('tournament_form.submit_publish') }}
                </UButton>
                <p class="text-center text-xs text-neutral-600 mt-3 font-medium">
                  {{ isEdit ? $t('tournament_form.submit_desc_save') : $t('tournament_form.submit_desc_publish') }}
                </p>
              </div>

            </UForm>

            <!-- ─── RIGHT: SIDEBAR ─── -->
            <div class="hidden lg:block">
              <div class="sticky top-8 space-y-4">

                <!-- Preview Card -->
                <div>
                  <p class="text-xs font-bold text-neutral-600 uppercase tracking-[0.25em] mb-4">{{ $t('tournament_form.preview_title') }}</p>
                  <TournamentCard
                    :tournament="({
                      id: 0,
                      sport_id: state.sport_id,
                      sport: selectedSport,
                      title: state.title || (locale === 'id' ? 'Judul Turnamen Anda' : 'Your Tournament Title'),
                      slug: 'preview',
                      description: state.description,
                      category: state.category,
                      status: 'draft',
                      mode: state.mode,
                      participant_type: state.participant_type,
                      team_size: state.team_size,
                      bracket_type: state.bracket_type,
                      venue: state.venue,
                      banner_url: state.banner_url || 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1000',
                      prize_pool: state.prize_pool,
                      entry_fee: state.entry_fee,
                      max_participants: state.max_participants,
                      current_participants: 0,
                      registration_start_at: state.registration_start_at,
                      registration_end_at: state.registration_end_at,
                      start_at: state.start_at,
                      created_at: new Date().toISOString(),
                      updated_at: new Date().toISOString(),
                      user: { id: 0, name: (locale === 'id' ? 'Anda (Organizer)' : 'You (Organizer)'), email: '' }
                    } as any)"
                  />
                </div>

                <!-- Checklist -->
                <div class="rounded-2xl border border-neutral-200 dark:border-white/5 bg-white dark:bg-white/[0.02] p-5 shadow-sm dark:shadow-none">
                  <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-bold text-neutral-400 uppercase tracking-wider">{{ $t('tournament_form.checklist_title') }}</p>
                    <span class="text-xs font-black text-primary-400">
                      {{ checks.filter(c => c.done).length }}/{{ checks.length }}
                    </span>
                  </div>

                  <!-- Progress bar -->
                  <div class="h-1 rounded-full bg-white/5 mb-4 overflow-hidden">
                    <div
                      class="h-full bg-primary-500 rounded-full transition-all duration-500"
                      :style="{ width: `${(checks.filter(c => c.done).length / checks.length) * 100}%` }"
                    />
                  </div>

                  <div class="space-y-2.5">
                    <div
                      v-for="check in checks"
                      :key="check.label"
                      class="flex items-center gap-2.5"
                    >
                      <div :class="[
                        'size-4 rounded-full flex items-center justify-center shrink-0 transition-all duration-300',
                        check.done ? 'bg-primary-500/20 text-primary-400' : 'bg-white/5 text-neutral-700'
                      ]">
                        <UIcon
                          :name="check.done ? 'i-lucide-check' : 'i-lucide-minus'"
                          class="size-2.5"
                        />
                      </div>
                      <span :class="[
                        'text-xs font-medium transition-colors duration-300',
                        check.done ? 'text-neutral-300' : 'text-neutral-600'
                      ]">
                        {{ check.label }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Tips -->
                <div class="rounded-2xl border border-neutral-200 dark:border-white/5 bg-white dark:bg-white/[0.02] p-5 shadow-sm dark:shadow-none">
                  <div class="flex items-center gap-2 mb-3">
                    <UIcon name="i-lucide-lightbulb" class="size-3.5 text-amber-400" />
                    <p class="text-xs font-bold text-neutral-400 uppercase tracking-wider">{{ $t('tournament_form.tips_title') }}</p>
                  </div>
                  <ul class="space-y-2.5">
                    <li v-for="tip in $tm('tournament_form.tips')" :key="tip" class="flex items-start gap-2 text-xs text-neutral-600 leading-relaxed">
                      <span class="shrink-0 mt-0.5 size-1.5 rounded-full bg-neutral-700 block" />
                      {{ $rt(tip) }}
                    </li>
                  </ul>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </UDashboardPanel>
</template> 