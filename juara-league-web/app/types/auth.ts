export interface User {
  id: number;
  name: string;
  email: string;
  username: string | null;
  avatar: string | null;
  bio: string | null;
  phone: string | null;
  google_id: string | null;
  created_at: string;
  updated_at?: string;
}
