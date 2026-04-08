import type { TournamentStatus } from '~/types/tournament'

export interface StatusConfig {
  label: string
  color: 'neutral' | 'primary' | 'success' | 'info' | 'warning' | 'error' | 'secondary'
  icon: string
  description?: string
}

export const TOURNAMENT_STATUS_CONFIG: Record<TournamentStatus, StatusConfig> = {
  draft: {
    label: 'Draft',
    color: 'neutral',
    icon: 'i-lucide-file-text',
    description: 'Turnamen belum dipublikasikan.'
  },
  open: {
    label: 'Registrasi Buka',
    color: 'primary',
    icon: 'i-lucide-users',
    description: 'Pendaftaran peserta sedang dibuka.'
  },
  ongoing: {
    label: 'Sedang Berjalan',
    color: 'info',
    icon: 'i-lucide-play-circle',
    description: 'Pertandingan sedang berlangsung.'
  },
  finished: {
    label: 'Selesai',
    color: 'success',
    icon: 'i-lucide-check-circle',
    description: 'Turnamen telah berakhir.'
  },
  canceled: {
    label: 'Dibatalkan',
    color: 'error',
    icon: 'i-lucide-x-circle',
    description: 'Turnamen dibatalkan.'
  }
}

export function getTournamentStatus(status: TournamentStatus | string | undefined | null): StatusConfig {
  const s = (status || 'draft') as TournamentStatus
  return TOURNAMENT_STATUS_CONFIG[s] || TOURNAMENT_STATUS_CONFIG.draft
}
