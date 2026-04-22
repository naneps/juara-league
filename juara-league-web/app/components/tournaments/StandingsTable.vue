<script setup lang="ts">
import type { Participant } from '~/types/tournament'

interface Standing {
  participant_id: number
  participant: Participant
  played: number
  win: number
  draw: number
  loss: number
  points: number
  rank: number
  goals_for?: number
  goals_against?: number
  goal_difference?: number
}

defineProps<{
  standings: Standing[]
  isLoading?: boolean
}>()
</script>

<template>
  <div class="overflow-x-auto rounded-2xl border border-neutral-200 dark:border-white/5 bg-white dark:bg-neutral-900/40 shadow-sm">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-neutral-50 dark:bg-neutral-800/50 border-b border-neutral-100 dark:border-white/5">
          <th class="px-6 py-4 text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest w-16 text-center">Pos</th>
          <th class="px-6 py-4 text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest min-w-[200px]">Tim / Pemain</th>
          <th class="px-4 py-4 text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest text-center">P</th>
          <th class="px-4 py-4 text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest text-center">W</th>
          <th class="px-4 py-4 text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest text-center">D</th>
          <th class="px-4 py-4 text-[10px] font-black text-neutral-400 dark:text-neutral-500 uppercase tracking-widest text-center">L</th>
          <th class="px-6 py-4 text-[10px] font-black text-neutral-900 dark:text-white uppercase tracking-widest text-center bg-neutral-100/50 dark:bg-white/5">PTS</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100 dark:divide-white/5">
        <tr v-if="isLoading" v-for="i in 5" :key="i" class="animate-pulse">
          <td v-for="j in 7" :key="j" class="px-6 py-4">
            <div class="h-4 bg-neutral-100 dark:bg-neutral-800 rounded mx-auto" :class="j === 2 ? 'w-32' : 'w-8'"></div>
          </td>
        </tr>
        
        <tr 
          v-else 
          v-for="(item, index) in standings" 
          :key="item.participant_id"
          class="hover:bg-neutral-50 dark:hover:bg-white/5 transition-colors group"
        >
          <td class="px-6 py-5 text-center">
            <span 
              :class="[
                'inline-flex items-center justify-center size-7 rounded-lg font-black text-xs shadow-sm',
                index === 0 ? 'bg-yellow-500 text-white' : 
                index === 1 ? 'bg-neutral-300 dark:bg-neutral-600 text-neutral-900 dark:text-white' :
                index === 2 ? 'bg-amber-600 text-white' : 'text-neutral-500 dark:text-neutral-400'
              ]"
            >
              {{ item.rank || index + 1 }}
            </span>
          </td>
          <td class="px-6 py-5">
            <div class="flex items-center gap-3">
              <UAvatar 
                :src="item.participant?.team?.logo || item.participant?.user?.avatar" 
                :alt="item.participant?.team?.name || item.participant?.user?.name"
                size="sm"
                class="ring-1 ring-neutral-200 dark:ring-white/10 group-hover:scale-110 transition-transform"
              />
              <span class="text-sm font-bold text-neutral-900 dark:text-white">{{ item.participant?.team?.name || item.participant?.user?.name }}</span>
            </div>
          </td>
          <td class="px-4 py-5 text-center text-sm font-medium text-neutral-600 dark:text-neutral-400">{{ item.played }}</td>
          <td class="px-4 py-5 text-center text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ item.win }}</td>
          <td class="px-4 py-5 text-center text-sm font-medium text-neutral-500">{{ item.draw }}</td>
          <td class="px-4 py-5 text-center text-sm font-bold text-red-600 dark:text-red-400">{{ item.loss }}</td>
          <td class="px-6 py-5 text-center text-base font-black text-neutral-900 dark:text-white bg-neutral-50/50 dark:bg-white/5">
            {{ item.points }}
          </td>
        </tr>
      </tbody>
    </table>
    
    <div v-if="!isLoading && standings.length === 0" class="p-12 text-center">
      <UIcon name="i-lucide-list-ordered" class="size-12 text-neutral-300 dark:text-neutral-700 mx-auto mb-4" />
      <p class="text-neutral-500 font-medium italic">Klasemen belum tersedia.</p>
    </div>
  </div>
</template>
