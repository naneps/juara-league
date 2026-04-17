<script setup lang="ts">
import { h, resolveComponent } from 'vue'
import type { TableColumn } from '@nuxt/ui'

const UBadge = resolveComponent('UBadge')
const UButton = resolveComponent('UButton')
const { t } = useI18n()

const { data: matches, refresh } = await useAsyncData('my-matches', async () => {
  const res = await $fetch<any>('/api/my-matches')
  return res.data
}, {
  default: () => []
})

const getParticipantName = (p: any) => {
  if (!p) return 'TBD'
  return p.team?.name || p.user?.name || 'TBD'
}

const columns = computed<TableColumn<any>[]>(() => [
  {
    accessorKey: 'tournament',
    header: t('common.status'),
    cell: ({ row }) => {
      const match = row.original
      return h('div', { class: 'flex flex-col' }, [
        h('span', { class: 'text-xs font-black text-white uppercase truncate' }, match.stage?.tournament?.title),
        h('span', { class: 'text-[9px] font-bold text-neutral-500 uppercase tracking-widest' }, match.stage?.name)
      ])
    }
  },
  {
    accessorKey: 'match',
    header: t('dashboard.matches'),
    cell: ({ row }) => {
      const match = row.original
      return h('div', { class: 'flex items-center gap-3' }, [
        h('div', { class: 'flex flex-col text-right min-w-[80px]' }, [
           h('span', { class: 'text-[10px] font-black' }, getParticipantName(match.participant_1)),
           h('span', { class: 'text-[9px] font-black text-primary-500' }, match.scores?.participant_1 ?? 0)
        ]),
        h('span', { class: 'text-[10px] font-black text-neutral-600' }, 'VS'),
        h('div', { class: 'flex flex-col text-left min-w-[80px]' }, [
           h('span', { class: 'text-[10px] font-black' }, getParticipantName(match.participant_2)),
           h('span', { class: 'text-[9px] font-black text-primary-500' }, match.scores?.participant_2 ?? 0)
        ])
      ])
    }
  },
  {
    accessorKey: 'status',
    header: t('common.status'),
    cell: () => {
      return h(UBadge, { color: 'primary', variant: 'solid', size: 'xs', class: 'font-black uppercase' }, () => 'LIVE')
    }
  },
  {
    id: 'actions',
    header: '',
    cell: ({ row }) => {
      const match = row.original
      return h(UButton, {
        icon: 'i-lucide-external-link',
        variant: 'ghost',
        color: 'neutral',
        size: 'xs',
        to: `/tournaments/${match.stage?.tournament?.slug}?tab=live`
      })
    }
  }
])
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between px-4">
      <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
        <UIcon name="i-lucide-play-circle" class="text-primary-500" />
        {{ $t('home.matches_title') }}
      </h3>
      <UButton variant="ghost" color="neutral" size="xs" icon="i-lucide-rotate-cw" @click="refresh()" />
    </div>

    <div v-if="matches.length === 0" class="p-8 text-center bg-neutral-900/50 rounded-2xl border border-white/5 mx-4">
      <p class="text-[10px] font-black text-neutral-500 uppercase tracking-widest">{{ $t('home.no_matches') }}</p>
    </div>

    <UTable
      v-else
      :data="matches"
      :columns="columns"
      class="shrink-0"
      :ui="{
        base: 'table-fixed border-separate border-spacing-0',
        thead: '[&>tr]:bg-elevated/50 [&>tr]:after:content-none',
        tbody: '[&>tr]:last:[&>td]:border-b-0',
        th: 'first:rounded-l-lg last:rounded-r-lg border-y border-default first:border-l last:border-r text-[10px] font-black uppercase tracking-widest text-neutral-500',
        td: 'border-b border-default'
      }"
    />
  </div>
</template>
