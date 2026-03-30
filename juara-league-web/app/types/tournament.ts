export type TournamentStatus = 'open' | 'ongoing' | 'finished' | 'draft';
export type TournamentMode = 'online' | 'offline';
export type BracketType = 'single_elimination' | 'double_elimination' | 'round_robin' | 'swiss';

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
}

export interface Tournament {
  id: number;
  user?: User;
  title: string;
  slug: string;
  description: string;
  category: string;
  status: TournamentStatus;
  mode: TournamentMode;
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
  created_at: string;
  updated_at: string;
}

export interface TournamentFilter {
  search: string;
  category: string;
  status: string;
  mode: string;
}

export interface StoreTournamentPayload {
  title: string;
  description: string;
  category: string;
  mode: TournamentMode;
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
