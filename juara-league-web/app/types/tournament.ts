export type TournamentStatus = 'draft' | 'registration' | 'open' | 'ongoing' | 'completed' | 'canceled';
export type ApprovalStatus = 'auto_approved' | 'pending_review' | 'approved' | 'rejected';
export type TournamentMode = 'open' | 'invite';
export type VenueType = 'online' | 'offline';
export type BracketType = 'single' | 'double' | 'round_robin' | 'swiss' | 'group_stage';
export type ParticipantType = 'individual' | 'team';
export type PaymentStatus = 'free' | 'pending' | 'paid' | 'rejected';

export interface User {
  id: string;
  name: string;
  email: string;
  avatar?: string;
  plan?: 'Free' | 'Pro';
}

import type { Sport } from './sport';

export interface Stage {
  id: string;
  tournament_id: string;
  name: string;
  type: string;
  order: number;
  settings?: any;
  created_at: string;
  updated_at: string;
}

export interface Participant {
  id: string;
  tournament_id: string;
  user_id?: string;
  team_id?: string;
  user?: User;
  team?: Team;
  tournament?: Tournament;
  status: 'pending' | 'approved' | 'rejected' | 'disqualified';
  payment_status: PaymentStatus;
  payment_proof_url?: string;
  seed?: number;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface UserParticipation {
  id: string;
  status: 'pending' | 'approved' | 'rejected';
  payment_status: PaymentStatus;
  team_id?: string;
}

export interface Tournament {
  id: string;
  user?: User;
  sport?: Sport;
  sport_id: string;
  title: string;
  slug: string;
  description: string;
  category: string;
  status: TournamentStatus;
  approval_status: ApprovalStatus;
  mode: TournamentMode;
  venue_type: VenueType;
  participant_type: ParticipantType;
  team_size?: number;
  bracket_type: BracketType;
  venue?: string;
  banner_url?: string;
  prize_pool: number | string;
  prize_description?: string;
  entry_fee: number | string;
  max_participants: number;
  current_participants: number;
  registration_start_at?: string;
  registration_end_at?: string;
  start_at?: string;
  stages?: Stage[];
  participants?: Participant[];
  staff?: any[];
  participants_count?: number;
  user_participation?: UserParticipation | null;
  created_at: string;
  updated_at: string;
}

export interface TournamentFilter {
  search: string;
  sport_id: string;
  status: string;
  mode: string;
}

export interface StoreTournamentPayload {
  sport_id: string;
  title: string;
  description: string;
  category: string;
  mode: TournamentMode;
  venue_type: VenueType;
  participant_type: ParticipantType;
  team_size?: number;
  bracket_type: BracketType;
  max_participants: number;
  prize_pool: number;
  prize_description?: string;
  entry_fee: number;
  registration_start_at?: string;
  registration_end_at?: string;
  start_at?: string;
  venue?: string;
  banner_url?: string;
}

export interface Team {
  id: string;
  name: string;
  slug: string;
  logo_url?: string;
  description?: string;
  captain_id: string;
  status: 'active' | 'pending' | 'disqualified';
  created_at: string;
  updated_at: string;
}
