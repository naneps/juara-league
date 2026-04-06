export type SportType = 'e-sport' | 'traditional' | 'miscellaneous';

export interface Sport {
  id: string;
  name: string;
  type: SportType;
  icon_url: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

export interface StoreSportPayload {
  name: string;
  type: SportType;
  icon_url?: string | null;
  is_active: boolean;
}

export interface UpdateSportPayload extends Partial<StoreSportPayload> {}
