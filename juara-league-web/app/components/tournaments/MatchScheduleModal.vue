<script setup lang="ts">
import { useTournamentStore } from '~/stores/tournamentStore'
import type { TournamentMatch } from '~/types/tournament'

const { t } = useI18n()
const props = defineProps<{
  tournamentSlug: string
  stageId: string
  allMatches: TournamentMatch[]
}>()

const model = defineModel<boolean>()
const emit = defineEmits(['updated'])

const selectedMatch = ref<TournamentMatch | null>(null)
const manualDate = ref('')
const manualTime = ref('')
const isSubmitting = ref(false)
const tournamentStore = useTournamentStore()

const open = (match: TournamentMatch) => {
  selectedMatch.value = match
  model.value = true
  
  if (match.scheduled_at) {
    const date = new Date(match.scheduled_at)
    manualDate.value = date.toISOString().split('T')[0]
    manualTime.value = date.toTimeString().split(' ')[0].substring(0, 5)
  } else {
    manualDate.value = ''
    manualTime.value = ''
  }
}

// Quick Actions
const setQuickDate = (mode: 'today' | 'tomorrow') => {
  const date = new Date()
  if (mode === 'tomorrow') date.setDate(date.getDate() + 1)
  manualDate.value = date.toISOString().split('T')[0]
}

const conflictMatch = computed(() => {
  if (!manualDate.value || !manualTime.value || !selectedMatch.value) return null
  const proposed = `${manualDate.value}T${manualTime.value}:00`
  
  return props.allMatches.find(m => 
    m.id !== selectedMatch.value?.id && 
    m.scheduled_at && 
    new Date(m.scheduled_at).getTime() === new Date(proposed).getTime()
  )
})

const updateSchedule = async () => {
  if (!selectedMatch.value || !manualDate.value || !manualTime.value) return
  
  if (conflictMatch.value) {
    useToast().add({ 
      title: t('match.schedule.conflict_error'), 
      description: t('match.schedule.conflict_desc', { match: conflictMatch.value.match_number }), 
      color: 'error' 
    })
    return
  }

  isSubmitting.value = true
  try {
    const scheduledAt = `${manualDate.value}T${manualTime.value}:00`
    await tournamentStore.updateMatch(props.tournamentSlug, props.stageId, selectedMatch.value.id, { 
      scheduled_at: scheduledAt 
    })
    useToast().add({ title: t('match.schedule.toast_success'), color: 'success' })
    model.value = false
    emit('updated')
  } catch (e: any) {
    useToast().add({ title: t('common.error'), description: e.data?.message || t('common.error'), color: 'error' })
  } finally {
    isSubmitting.value = false
  }
}

defineExpose({ open })
</script>

<template>
  <UModal 
    v-model:open="model" 
    :ui="{ 
      content: 'sm:max-w-lg w-full rounded-[2rem] bg-white dark:bg-[#0A0C14] border border-neutral-200 dark:border-white/5 shadow-2xl overflow-visible',
      overlay: 'bg-black/40 dark:bg-black/90 backdrop-blur-md',
    }"
  >
    <template #body>
      <div v-if="selectedMatch" class="relative">
        <!-- Close Button -->
        <button 
          @click="model = false"
          class="absolute -top-2 -right-2 z-30 size-10 flex items-center justify-center rounded-full bg-white dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-all shadow-xl border border-neutral-200 dark:border-white/10"
        >
          <UIcon name="i-lucide-x" class="size-5" />
        </button>

        <!-- Premium Adaptive Glows -->
        <div class="absolute -top-32 -left-32 size-96 bg-primary-500/5 dark:bg-primary-500/10 blur-[120px] pointer-events-none rounded-full"></div>

        <div class="p-10 space-y-10 relative z-10">
          <!-- Header section -->
          <div class="flex flex-col items-center text-center space-y-5">
            <div class="size-20 rounded-3xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 flex items-center justify-center shadow-2xl relative group">
              <div class="absolute inset-0 bg-primary-500/10 dark:bg-primary-500/20 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
              <UIcon name="i-lucide-calendar-plus" class="size-10 text-primary-500 relative z-10" />
            </div>
            <div>
              <h3 class="text-lg font-black text-neutral-950 dark:text-white uppercase tracking-[0.2em] mb-2">
                {{ $t('match.schedule.title') }}
              </h3>
              <p class="text-xs text-neutral-500 dark:text-neutral-400 font-bold max-w-xs mx-auto leading-relaxed">
                {{ $t('match.schedule.subtitle', { match: selectedMatch.match_number }) }}
              </p>
            </div>
          </div>

          <!-- Input Controls -->
          <div class="space-y-6">
            <!-- Date Selection -->
            <div class="space-y-3">
              <div class="flex items-center justify-between px-1">
                <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">{{ $t('match.schedule.date_label') }}</label>
                <div class="flex gap-2">
                   <button 
                     @click="setQuickDate('today')"
                     class="text-[9px] font-black uppercase tracking-widest text-primary-500 hover:text-primary-600 transition-colors"
                   >
                     {{ $t('match.schedule.quick_today') }}
                   </button>
                   <span class="text-neutral-300 dark:text-neutral-800">|</span>
                   <button 
                     @click="setQuickDate('tomorrow')"
                     class="text-[9px] font-black uppercase tracking-widest text-primary-500 hover:text-primary-600 transition-colors"
                   >
                     {{ $t('match.schedule.quick_tomorrow') }}
                   </button>
                </div>
              </div>
              
              <UInput 
                v-model="manualDate" 
                type="date" 
                size="xl" 
                icon="i-lucide-calendar"
                color="primary"
                class="w-full"
                :ui="{ 
                  rounded: 'rounded-2xl', 
                  base: 'bg-neutral-50/50 dark:bg-neutral-950/50 border-neutral-200 dark:border-white/5 focus:ring-primary-500 transition-all font-bold text-neutral-900 dark:text-white h-14',
                }" 
              />
              <p class="text-[9px] text-neutral-400 dark:text-neutral-600 font-bold px-2">{{ $t('match.schedule.date_helper') }}</p>
            </div>

            <!-- Time Selection -->
            <div class="space-y-3">
              <label class="text-[10px] font-black text-neutral-500 uppercase tracking-widest px-1">{{ $t('match.schedule.time_label') }}</label>
              <UInput 
                v-model="manualTime" 
                type="time" 
                size="xl" 
                icon="i-lucide-clock"
                color="primary"
                class="w-full"
                :ui="{ 
                  rounded: 'rounded-2xl', 
                  base: 'bg-neutral-50/50 dark:bg-neutral-950/50 border-neutral-200 dark:border-white/5 focus:ring-primary-500 transition-all font-bold text-neutral-900 dark:text-white h-14',
                }" 
              />
              <p class="text-[9px] text-neutral-400 dark:text-neutral-600 font-bold px-2">{{ $t('match.schedule.time_helper') }}</p>
            </div>

            <!-- Conflict Warning -->
            <transition name="fade">
              <div v-if="conflictMatch" class="p-5 bg-red-500/5 border border-red-500/10 rounded-2xl flex items-start gap-4 shadow-sm">
                <div class="size-8 rounded-xl bg-red-500/10 flex items-center justify-center shrink-0 border border-red-500/20">
                  <UIcon name="i-lucide-alert-octagon" class="size-5 text-red-500" />
                </div>
                <div class="space-y-1">
                   <p class="text-[10px] font-black text-red-500 uppercase tracking-widest">{{ $t('match.schedule.conflict_error') }}</p>
                   <p class="text-[10px] font-bold text-red-400 leading-relaxed uppercase tracking-wider">
                     {{ $t('match.schedule.conflict_warning', { match: conflictMatch.match_number }) }}
                   </p>
                </div>
              </div>
            </transition>
          </div>

          <!-- Actions -->
          <div class="space-y-4 pt-4">
            <UButton
              color="primary"
              block
              size="xl"
              class="rounded-2xl font-black uppercase tracking-[0.2em] h-16 shadow-[0_10px_30px_rgba(251,191,36,0.2)] hover:shadow-[0_15px_40px_rgba(251,191,36,0.3)] transition-all active:scale-[0.98]"
              :loading="isSubmitting"
              :disabled="!!conflictMatch"
              @click="updateSchedule"
            >
              {{ $t('match.schedule.submit') }}
            </UButton>

            <div class="text-center">
               <button 
                 @click="model = false"
                 class="text-[10px] font-black text-neutral-400 hover:text-neutral-950 dark:text-neutral-600 dark:hover:text-white uppercase tracking-[0.3em] transition-colors p-2"
               >
                 {{ $t('common.back') }}
               </button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </UModal>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
