export type TournamentStatus = 'open' | 'ongoing' | 'finished' | 'draft';
export type TournamentMode = 'online' | 'offline';
export type BracketType = 'single_elimination' | 'double_elimination' | 'round_robin' | 'swiss';

export interface Tournament {
  id: string | number;
  title: string;
  slug: string;
  description: string;
  category: string; // e.g. Football, E-sports
  status: TournamentStatus;
  mode: TournamentMode;
  location?: string;
  image: string;
  organizer: {
    name: string;
    avatar: string;
    is_verified: boolean;
  };
  prize_pool: string;
  entry_fee: string;
  start_date: string;
  max_participants: number;
  current_participants: number;
  bracket_type: BracketType;
}

export interface TournamentFilter {
  search: string;
  category: string;
  status: string;
  mode: string;
}
