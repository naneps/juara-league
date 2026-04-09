export interface User {
  id: string;
  name: string;
  email: string;
  username: string | null;
  avatar: string | null;
  bio: string | null;
  phone: string | null;
  google_id: string | null;
  roles: string[];
  permissions: string[];
  created_at: string;
  updated_at?: string;
}
