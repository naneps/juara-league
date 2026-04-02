// ─── Status & Constants ───────────────────────────────────────────────────────

export type TeamStatus = 'active' | 'pending' | 'disqualified'

export const TEAM_STATUS_LABELS: Record<TeamStatus, string> = {
  active: 'Aktif',
  pending: 'Tertunda',
  disqualified: 'Diskualifikasi'
}

export const TEAM_STATUS_COLORS: Record<TeamStatus, 'success' | 'warning' | 'error'> = {
  active: 'success',
  pending: 'warning',
  disqualified: 'error'
}

// ─── Core Models ──────────────────────────────────────────────────────────────

export interface TeamMember {
  id: number
  name: string
  email: string
  avatar?: string
  pivot?: {
    role: string
    joined_at: string
  }
}

export interface Team {
  id: number
  name: string
  slug: string
  logo_url?: string
  description?: string
  captain_id: number
  captain?: TeamMember
  members?: TeamMember[]
  status: TeamStatus
  created_at: string
  updated_at: string
}

export interface TeamInvitation {
  id: number
  team_id: number
  team: Team
  email: string
  status: 'pending' | 'accepted' | 'declined'
  token: string
  expires_at: string
  created_at: string
}

// ─── Pagination ───────────────────────────────────────────────────────────────

export interface TeamPaginatedResponse {
  current_page: number
  data: Team[]
  from: number | null
  last_page: number
  per_page: number
  to: number | null
  total: number
}

// ─── API Payloads ─────────────────────────────────────────────────────────────

export interface CreateTeamPayload {
  name: string
  description?: string
  logo_url?: string
}

export interface UpdateTeamPayload {
  name?: string
  description?: string
  logo_url?: string
}

export interface InviteMemberPayload {
  email: string
}

export interface TransferCaptaincyPayload {
  user_id: number
}

// ─── Query Params ─────────────────────────────────────────────────────────────

export interface TeamQueryParams {
  page?: number
  per_page?: number
  search?: string
}
