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
  registration: {
    label: 'Registrasi Buka',
    color: 'primary',
    icon: 'i-lucide-users',
    description: 'Pendaftaran peserta sedang dibuka.'
  },
  open: { // Legacy/Fallback for registration
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
  completed: {
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

export const APPROVAL_STATUS_CONFIG: Record<string, StatusConfig> = {
  auto_approved: {
    label: 'Auto Approved',
    color: 'success',
    icon: 'i-lucide-shield-check',
    description: 'Lolos verifikasi otomatis sistem.'
  },
  pending_review: {
    label: 'Pending Review',
    color: 'warning',
    icon: 'i-lucide-timer',
    description: 'Menunggu review manual oleh tim platform.'
  },
  approved: {
    label: 'Approved',
    color: 'success',
    icon: 'i-lucide-check-circle-2',
    description: 'Turnamen telah disetujui.'
  },
  rejected: {
    label: 'Ditolak',
    color: 'error',
    icon: 'i-lucide-alert-circle',
    description: 'Turnamen tidak memenuhi syarat.'
  }
}

export function getTournamentStatus(status: TournamentStatus | string | undefined | null): StatusConfig {
  const s = (status || 'draft') as TournamentStatus
  return TOURNAMENT_STATUS_CONFIG[s] || TOURNAMENT_STATUS_CONFIG.draft
}

export function getApprovalStatus(status: string | undefined | null): StatusConfig {
  const s = status || 'pending_review'
  return APPROVAL_STATUS_CONFIG[s] || APPROVAL_STATUS_CONFIG.pending_review
}
