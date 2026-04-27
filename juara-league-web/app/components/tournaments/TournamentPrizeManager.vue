<script setup lang="ts">
import type { TournamentPrize } from '~/types/tournament'

const props = defineProps<{
  modelValue: any[]
}>()

const emit = defineEmits(['update:modelValue'])

const prizes = ref<any[]>(props.modelValue || [])

watch(() => props.modelValue, (newVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(prizes.value)) {
    prizes.value = [...newVal]
  }
}, { deep: true })

watch(prizes, (newVal) => {
  emit('update:modelValue', newVal)
}, { deep: true })

const addPrize = () => {
  const nextRank = prizes.value.length + 1
  prizes.value.push({
    tier_name: `Juara ${nextRank}`,
    prize_amount: 0,
    description: '',
    rank: nextRank,
    order: prizes.value.length
  })
}

const removePrize = (index: number) => {
  prizes.value.splice(index, 1)
  // Re-rank and re-order
  prizes.value.forEach((p, i) => {
    p.rank = i + 1
    p.order = i
  })
}

const moveUp = (index: number) => {
  if (index === 0) return
  const item = prizes.value.splice(index, 1)[0]
  prizes.value.splice(index - 1, 0, item)
  prizes.value.forEach((p, i) => { p.order = i })
}

const moveDown = (index: number) => {
  if (index === prizes.value.length - 1) return
  const item = prizes.value.splice(index, 1)[0]
  prizes.value.splice(index + 1, 0, item)
  prizes.value.forEach((p, i) => { p.order = i })
}

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val)
}

const getRankIcon = (rank: number) => {
  if (rank === 1) return 'i-lucide-trophy'
  if (rank === 2) return 'i-lucide-medal'
  if (rank === 3) return 'i-lucide-award'
  return 'i-lucide-star'
}

const getRankColorClass = (rank: number) => {
  if (rank === 1) return 'bg-amber-500/10 text-amber-600 border-amber-500/20'
  if (rank === 2) return 'bg-slate-400/10 text-slate-500 border-slate-400/20'
  if (rank === 3) return 'bg-orange-500/10 text-orange-600 border-orange-500/20'
  return 'bg-neutral-100 text-neutral-500 border-neutral-200'
}

const prizeTemplates = [
  {
    label: 'Winner Takes All',
    icon: 'i-lucide-trophy',
    prizes: [
      { rank: 1, tier_name: 'Juara 1', prize_amount: 0, description: 'Pemenang utama mengambil semua hadiah.' }
    ]
  },
  {
    label: 'Top 3 (Gold, Silver, Bronze)',
    icon: 'i-lucide-medal',
    prizes: [
      { rank: 1, tier_name: 'Juara 1', prize_amount: 0, description: 'Medali Emas' },
      { rank: 2, tier_name: 'Juara 2', prize_amount: 0, description: 'Medali Perak' },
      { rank: 3, tier_name: 'Juara 3', prize_amount: 0, description: 'Medali Perunggu' }
    ]
  },
  {
    label: 'Finalists (1st & Runner Up)',
    icon: 'i-lucide-users',
    prizes: [
      { rank: 1, tier_name: 'Juara 1', prize_amount: 0, description: 'Champion' },
      { rank: 2, tier_name: 'Runner Up', prize_amount: 0, description: 'Finalist' }
    ]
  },
  {
    label: 'Top 4',
    icon: 'i-lucide-award',
    prizes: [
      { rank: 1, tier_name: 'Juara 1', prize_amount: 0 },
      { rank: 2, tier_name: 'Juara 2', prize_amount: 0 },
      { rank: 3, tier_name: 'Juara 3', prize_amount: 0 },
      { rank: 4, tier_name: 'Juara 4', prize_amount: 0 }
    ]
  }
]

const applyTemplate = (template: typeof prizeTemplates[0]) => {
  prizes.value = template.prizes.map((p, i) => ({ ...p, order: i }))
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
       <h5 class="text-xs font-bold text-neutral-400 uppercase tracking-widest">Daftar Peringkat & Hadiah</h5>
       <div class="flex items-center gap-2">
         <UDropdownMenu :items="prizeTemplates.map(t => ({ label: t.label, icon: t.icon, onSelect: () => applyTemplate(t) }))">
            <UButton 
              size="xs" 
              color="neutral" 
              variant="outline" 
              icon="i-lucide-layout-template"
              class="font-bold"
            >
              Gunakan Template
            </UButton>
         </UDropdownMenu>
         <UButton 
           size="xs" 
           color="primary" 
           variant="soft" 
           icon="i-lucide-plus" 
           @click="addPrize"
           class="font-bold"
         >
           Tambah Tier
         </UButton>
       </div>
    </div>

    <div v-if="prizes.length === 0" class="py-10 text-center border-2 border-dashed border-neutral-200 dark:border-white/5 rounded-2xl bg-neutral-50/50 dark:bg-white/[0.02]">
      <UIcon name="i-lucide-trophy" class="size-10 text-neutral-300 dark:text-neutral-700 mx-auto mb-3" />
      <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400 mb-4">Belum ada tier hadiah yang diatur.</p>
      <UButton
        color="primary"
        variant="subtle"
        icon="i-lucide-plus"
        size="sm"
        @click="addPrize"
      >
        Tambah Hadiah Pertama
      </UButton>
    </div>

    <div v-else class="space-y-3">
      <div 
        v-for="(prize, index) in prizes" 
        :key="index"
        class="group bg-white dark:bg-white/[0.03] border border-neutral-200 dark:border-white/5 rounded-2xl p-4 transition-all hover:border-primary-500/30"
      >
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Order Controls -->
          <div class="flex sm:flex-col items-center justify-center gap-1 shrink-0">
             <UButton 
               variant="ghost" 
               color="neutral" 
               icon="i-lucide-chevron-up" 
               size="xs" 
               :disabled="index === 0"
               @click="moveUp(index)"
             />
             <div 
               class="size-8 rounded-xl flex items-center justify-center border transition-colors shadow-sm"
               :class="getRankColorClass(index + 1)"
             >
               <UIcon :name="getRankIcon(index + 1)" class="size-4" />
             </div>
             <UButton 
               variant="ghost" 
               color="neutral" 
               icon="i-lucide-chevron-down" 
               size="xs" 
               :disabled="index === prizes.length - 1"
               @click="moveDown(index)"
             />
          </div>

          <!-- Main Info -->
          <div class="flex-grow grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <UFormField label="Nama Tier" :help="`Misal: Champion, Juara ${index + 1}`">
              <UInput 
                v-model="prize.tier_name" 
                placeholder="Juara 1" 
                size="md" 
                class="w-full"
                :ui="{ base: 'rounded-xl' }"
              />
            </UFormField>

            <UFormField label="Jumlah Hadiah" help="Dalam Rupiah">
                    <CommonCurrencyInput
                      v-model="prize.prize_amount"
                      size="sm"
                      placeholder="0"
                      class="w-full"
                    />
            </UFormField>

            <UFormField label="Keterangan Lain" help="Trophy, Medali, Merchandise, dll" class="md:col-span-2 lg:col-span-1">
              <UInput 
                v-model="prize.description" 
                placeholder="Trophy + Sertifikat" 
                size="md" 
                class="w-full"
                :ui="{ base: 'rounded-xl' }"
              />
            </UFormField>
          </div>

          <!-- Delete -->
          <div class="shrink-0 flex items-center justify-center">
            <UButton 
              color="error" 
              variant="ghost" 
              icon="i-lucide-trash-2" 
              size="sm" 
              class="rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"
              @click="removePrize(index)"
            />
          </div>
        </div>
      </div>

      <div class="flex justify-center pt-2">
        <UButton
          color="neutral"
          variant="outline"
          icon="i-lucide-plus"
          size="sm"
          class="rounded-xl border-dashed border-2 hover:border-primary-500/50 hover:bg-primary-500/5"
          @click="addPrize"
        >
          Tambah Tier Hadiah
        </UButton>
      </div>
    </div>
  </div>
</template>
