export interface User {
  id: number;
  name: string;
  email: string;
  avatar: string | null;
  google_id: string | null;
  created_at: string;
  updated_at?: string;
}
