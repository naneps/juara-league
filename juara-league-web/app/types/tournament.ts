export type TournamentStatus = 'open' | 'ongoing' | 'finished' | 'draft';
export type TournamentMode = 'online' | 'offline';
export type BracketType = 'single' | 'double' | 'round_robin' | 'swiss' | 'group_stage';
export type ParticipantType = 'individual' | 'team';

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
}

import type { Sport } from './sport';

export interface Stage {
  id: number;
  tournament_id: number;
  name: string;
  type: string;
  order: number;
  settings?: any;
  created_at: string;
  updated_at: string;
}

export interface Participant {
  id: number;
  tournament_id: number;
  user_id?: number;
  team_id?: number;
  user?: User;
  team?: Team;
  status: 'pending' | 'approved' | 'rejected' | 'paid';
  payment_proof_url?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export interface UserParticipation {
  id: number;
  status: 'pending' | 'approved' | 'rejected' | 'paid';
  team_id?: number;
}

export interface Tournament {
  id: number;
  user?: User;
  sport?: Sport;
  sport_id: string;
  title: string;
  slug: string;
  description: string;
  category: string;
  status: TournamentStatus;
  mode: TournamentMode;
  participant_type: ParticipantType;
  team_size?: number;
  bracket_type: BracketType;
  venue?: string;
  banner_url?: string;
  prize_pool: number | string;
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
  participant_type: ParticipantType;
  team_size?: number;
  bracket_type: BracketType;
  max_participants: number;
  prize_pool: number;
  entry_fee: number;
  registration_start_at?: string;
  registration_end_at?: string;
  start_at?: string;
  venue?: string;
  banner_url?: string;
}

export interface Team {
  id: number;
  name: string;
  slug: string;
  logo_url?: string;
  description?: string;
  captain_id: number;
  status: 'active' | 'pending' | 'disqualified';
  created_at: string;
  updated_at: string;
}
