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
  type: 'single_elim' | 'double_elim' | 'round_robin' | 'swiss';
  status: 'pending' | 'ongoing' | 'completed';
  bo_format: string; // Legacy
  participants_advance?: number;
  groups_count?: number;
  participants_per_group?: number;
  order: number;
  settings?: {
    match_format: 'single_game' | 'best_of' | 'leg';
    win_condition: number;
    scoring_method: 'score_based' | 'result_based' | 'point_based';
    advance_count: number;
    rounds?: number;
    rules: {
      allow_draw: boolean;
      extra_time: boolean;
      penalties: boolean;
    }
  };
  groups?: Group[];
  matches?: TournamentMatch[];
  created_at: string;
  updated_at: string;
}

export interface Group {
  id: string;
  stage_id: string;
  name: string;
  order: number;
  matches?: TournamentMatch[];
  created_at: string;
  updated_at: string;
}

export interface MatchParticipant {
  id: string;
  match_id: string;
  participant_id: string;
  slot: number;
  score: number;
  rank?: number;
  is_winner: boolean;
  participant?: Participant;
}

export interface TournamentMatch {
  id: string;
  stage_id: string;
  group_id?: string;
  round: number;
  match_number: number;
  
  // Pivot participants (new system)
  participants?: MatchParticipant[];
  
  // Legacy participants mapping
  participant_1?: Participant;
  participant_2?: Participant;
  winner?: Participant;
  participant_1_id?: string;
  participant_2_id?: string;
  winner_id?: string;
  
  status: 'upcoming' | 'ongoing' | 'completed' | 'bye';
  bracket_side?: 'upper' | 'lower' | 'grand_final';
  next_match_winner_id?: string;
  next_match_loser_id?: string;
  scores?: Record<string, number>;
  games?: Game[];
  scheduled_at?: string;
  completed_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Game {
  id: string;
  match_id: string;
  game_number: number;
  winner?: Participant;
  winner_id?: string;
  score_p1?: number;
  score_p2?: number;
  status: 'created' | 'corrected';
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

export interface TournamentPrize {
  id?: string;
  tournament_id?: string;
  tier_name: string;
  prize_amount: number;
  description?: string;
  rank?: number;
  order: number;
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
  format_summary?: string;
  stages_info?: Array<{
    name: string;
    type: string;
    rules: {
      bo_format: string;
      groups_count?: number;
      participants_advance?: number;
    }
  }>;
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
  prizes?: TournamentPrize[];
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
  bracket_type?: BracketType;
  max_participants: number;
  prize_pool: number;
  prize_description?: string;
  entry_fee: number;
  registration_start_at?: string;
  registration_end_at?: string;
  start_at?: string;
  venue?: string;
  banner_url?: string;
  prizes?: TournamentPrize[];
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
