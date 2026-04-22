<script setup lang="ts">
import { 
  CalendarDate, 
  getLocalTimeZone, 
  getWeeksInMonth, 
  today, 
  startOfMonth, 
  endOfMonth, 
  startOfWeek, 
  isSameDay,
  parseDate
} from '@internationalized/date'
import type { TournamentMatch } from '~/types/tournament'

const props = withDefaults(defineProps<{
  matches: TournamentMatch[]
  isLoading?: boolean
  isStaff?: boolean
}>(), {
  isLoading: false,
  isStaff: false
})

const emit = defineEmits(['select-match', 'schedule-match'])

const { locale } = useI18n()
const timeZone = getLocalTimeZone()
const now = today(timeZone)

const currentMonth = ref(startOfMonth(now))
const selectedDate = ref(now)

const monthName = computed(() => {
  const date = currentMonth.value.toDate(timeZone)
  return new Intl.DateTimeFormat(locale.value, { month: 'long', year: 'numeric' }).format(date)
})

const daysOfWeek = computed(() => {
  const start = startOfWeek(currentMonth.value, locale.value === 'id' ? 'id-ID' : 'en-US')
  return Array.from({ length: 7 }, (_, i) => {
    const date = start.add({ days: i }).toDate(timeZone)
    return new Intl.DateTimeFormat(locale.value, { weekday: 'narrow' }).format(date)
  })
})

const calendarGrid = computed(() => {
  const monthStart = startOfMonth(currentMonth.value)
  const monthEnd = endOfMonth(currentMonth.value)
  const gridStart = startOfWeek(monthStart, locale.value === 'id' ? 'id-ID' : 'en-US')
  
  const days = []
  let cursor = gridStart
  
  // We want to show a consistent grid, usually 6 weeks to cover all months
  for (let i = 0; i < 42; i++) {
    days.push({
      date: cursor,
      isCurrentMonth: cursor.month === currentMonth.value.month,
      isToday: isSameDay(cursor, now),
      isSelected: isSameDay(cursor, selectedDate.value)
    })
    cursor = cursor.add({ days: 1 })
  }
  return days
})

const nextMonth = () => { currentMonth.value = currentMonth.value.add({ months: 1 }) }
const prevMonth = () => { currentMonth.value = currentMonth.value.subtract({ months: 1 }) }
const goToToday = () => {
  currentMonth.value = startOfMonth(now)
  selectedDate.value = now
}

const matchesByDate = computed(() => {
  const map = new Map<string, TournamentMatch[]>()
  props.matches.forEach(m => {
    if (!m.scheduled_at) return
    try {
      // Extract YYYY-MM-DD regardless of T or space separator
      const datePart = m.scheduled_at.includes('T') 
        ? m.scheduled_at.split('T')[0] 
        : m.scheduled_at.split(' ')[0]
        
      const d = parseDate(datePart)
      const key = d.toString()
      if (!map.has(key)) map.set(key, [])
      map.get(key)?.push(m)
    } catch (e) {
      console.warn('Failed to parse date:', m.scheduled_at, e)
    }
  })
  return map
})

const getMatchesForDate = (date: CalendarDate) => {
  return matchesByDate.value.get(date.toString()) || []
}

const selectedDayMatches = computed(() => {
  return getMatchesForDate(selectedDate.value).sort((a, b) => {
    return (a.scheduled_at || '').localeCompare(b.scheduled_at || '')
  })
})

const formatMatchTime = (dateString?: string) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString(locale.value, { hour: '2-digit', minute: '2-digit', hour12: false })
}

const matchStatusColor = (status: string) => {
  const map: Record<string, string> = { upcoming: 'neutral', ongoing: 'primary', completed: 'success', bye: 'warning' }
  return map[status] || 'neutral'
}

const getParticipantName = (p: any) => {
  if (!p) return 'TBD'
  return p.team?.name || p.user?.name || 'TBD'
}
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- ── Calendar Container ── -->
    <div class="lg:col-span-7 xl:col-span-8 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 rounded-3xl overflow-hidden flex flex-col shadow-sm">
      
      <!-- Calendar Header -->
      <div class="px-6 py-4 border-b border-neutral-100 dark:border-white/5 flex items-center justify-between bg-white dark:bg-neutral-800/20">
        <h3 class="text-sm font-black uppercase tracking-widest text-neutral-900 dark:text-white">{{ monthName }}</h3>
        
        <div class="flex items-center gap-1">
          <UButton 
            variant="ghost" 
            color="neutral" 
            icon="i-lucide-chevron-left" 
            size="sm" 
            class="rounded-xl"
            @click="prevMonth" 
          />
          <UButton 
            variant="ghost" 
            color="neutral" 
            size="sm" 
            class="rounded-xl font-bold uppercase text-[10px] tracking-widest px-3"
            @click="goToToday"
          >
            Today
          </UButton>
          <UButton 
            variant="ghost" 
            color="neutral" 
            icon="i-lucide-chevron-right" 
            size="sm" 
            class="rounded-xl"
            @click="nextMonth" 
          />
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="p-4">
        <!-- Weekdays Header -->
        <div class="grid grid-cols-7 mb-2">
          <div 
            v-for="day in daysOfWeek" 
            :key="day" 
            class="text-center text-[10px] font-black text-neutral-400 dark:text-neutral-600 uppercase py-2"
          >
            {{ day }}
          </div>
        </div>

        <!-- Days Grid -->
        <div class="grid grid-cols-7 gap-1">
          <button
            v-for="cell in calendarGrid"
            :key="cell.date.toString()"
            @click="selectedDate = cell.date"
            class="aspect-square relative flex flex-col items-center justify-center rounded-2xl transition-all group overflow-hidden border border-transparent"
            :class="[
              !cell.isCurrentMonth ? 'opacity-20 pointer-events-none' : '',
              cell.isSelected 
                ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/20 z-10 scale-105' 
                : 'hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-700 dark:text-neutral-300'
            ]"
          >
            <!-- Today highlight -->
            <div v-if="cell.isToday && !cell.isSelected" class="absolute inset-0 border-2 border-primary-500/30 rounded-2xl"></div>
            
            <span class="text-xs font-black relative z-10">{{ cell.date.day }}</span>
            
            <!-- Match Indicators -->
            <div v-if="getMatchesForDate(cell.date).length > 0" class="mt-1 flex gap-0.5 justify-center relative z-10">
              <div 
                v-for="m in getMatchesForDate(cell.date).slice(0, 3)" 
                :key="m.id"
                class="size-1 rounded-full"
                :class="cell.isSelected ? 'bg-white' : 'bg-primary-500'"
              ></div>
              <div v-if="getMatchesForDate(cell.date).length > 3" class="text-[8px] font-bold leading-none" :class="cell.isSelected ? 'text-white/80' : 'text-neutral-400'">+</div>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- ── Jam View (Time View) Panel ── -->
    <div class="lg:col-span-5 xl:col-span-4 space-y-4">
      <div class="flex items-center justify-between px-2">
        <div>
          <h4 class="text-[10px] font-black text-neutral-500 uppercase tracking-[0.2em] mb-1">Time Schedule</h4>
          <h2 class="text-sm font-black text-neutral-900 dark:text-white uppercase tracking-tight">
            {{ selectedDate.toDate(timeZone).toLocaleDateString(locale, { day: 'numeric', month: 'long', year: 'numeric' }) }}
          </h2>
        </div>
        <UBadge v-if="selectedDayMatches.length > 0" color="primary" variant="subtle" size="sm" class="font-black">
          {{ selectedDayMatches.length }} {{ $t('dashboard.title_matches') }}
        </UBadge>
      </div>

      <!-- Match List for Selected Day -->
      <div v-if="selectedDayMatches.length > 0" class="space-y-3">
        <div
          v-for="match in selectedDayMatches"
          :key="match.id"
          class="w-full flex items-center gap-4 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-white/5 p-4 rounded-3xl transition-all hover:border-primary-500/40 hover:scale-[1.02] group shadow-sm dark:shadow-none overflow-hidden"
        >
          <!-- Left: Clickable Info Section -->
          <div 
            class="flex-1 flex items-center gap-4 cursor-pointer"
            @click="emit('select-match', match)"
          >
            <!-- Time -->
            <div class="flex flex-col items-center justify-center py-1 px-3 bg-neutral-100 dark:bg-neutral-800 rounded-2xl min-w-[60px]">
              <span class="text-xs font-black text-neutral-900 dark:text-white">{{ formatMatchTime(match.scheduled_at) }}</span>
              <span class="text-[8px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">WIB</span>
            </div>

            <!-- Match Info -->
            <div class="flex-1 min-w-0 pr-2">
              <div class="flex items-center justify-between mb-2">
                <span class="text-[9px] font-black text-neutral-500 uppercase tracking-widest">M#{{ match.match_number }}</span>
                <UBadge :color="matchStatusColor(match.status) as any" variant="subtle" size="xs" class="text-[8px] font-black uppercase">
                  {{ match.status }}
                </UBadge>
              </div>
              
              <div class="space-y-1">
                <div class="flex items-center justify-between gap-2 overflow-hidden">
                  <span class="text-[11px] font-bold text-neutral-800 dark:text-neutral-200 truncate">{{ getParticipantName(match.participant_1) }}</span>
                  <span class="text-xs font-black text-primary-500 italic">{{ match.scores?.participant_1 ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between gap-2 overflow-hidden">
                  <span class="text-[11px] font-bold text-neutral-800 dark:text-neutral-200 truncate">{{ getParticipantName(match.participant_2) }}</span>
                  <span class="text-xs font-black text-primary-500 italic">{{ match.scores?.participant_2 ?? 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Separate Actions -->
          <div v-if="isStaff" class="flex items-center gap-2 pl-4 border-l border-neutral-100 dark:border-white/5">
            <button
              type="button"
              class="size-8 flex items-center justify-center rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-500 hover:text-indigo-500 hover:bg-indigo-500/10 transition-all shadow-sm"
              @click="emit('schedule-match', match)"
            >
              <UIcon name="i-lucide-calendar-clock" class="size-4" />
            </button>
            <UIcon name="i-lucide-arrow-right" class="size-4 text-neutral-300 dark:text-neutral-700 opacity-0 group-hover:opacity-100 transition-opacity" />
          </div>
          <div v-else class="pl-4 border-l border-neutral-100 dark:border-white/5">
            <UIcon name="i-lucide-chevron-right" class="size-4 text-neutral-300 dark:text-neutral-700 opacity-0 group-hover:opacity-100 transition-opacity" />
          </div>
        </div>
      </div>

      <!-- Empty State for Day -->
      <div v-else class="py-16 text-center bg-neutral-50 dark:bg-neutral-900/40 border border-dashed border-neutral-300 dark:border-white/5 rounded-[2.5rem]">
        <div class="size-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mx-auto mb-4 border border-neutral-200 dark:border-neutral-700">
           <UIcon name="i-lucide-calendar-x" class="size-6 text-neutral-400 dark:text-neutral-600" />
        </div>
        <p class="text-[10px] font-black text-neutral-400 dark:text-neutral-600 uppercase tracking-widest">No matches scheduled</p>
      </div>
    </div>

  </div>
</template>
