export interface User {
  uuid: string;
  name: string;
  email: string;
  role: 'user' | 'admin';
  email_verified_at: string | null;
  timezone: string;
  is_active: boolean;
  created_at: string;
}

export interface Profile {
  avatar: string | null;
  phone: string | null;
  headline: string | null;
  bio: string | null;
  location: string | null;
  linkedin_url: string | null;
  github_url: string | null;
  website_url: string | null;
  preferences: Record<string, unknown> | null;
}

export interface Session {
  id: number;
  name: string;
  last_used_at: string | null;
  created_at: string;
  is_current: boolean;
}

export interface AuthData {
  user: User;
  token: string;
  token_expires_at?: string;
}

export interface UserWithProfile {
  user: User;
  profile: Profile | null;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  timezone?: string;
}

export interface LoginData {
  email: string;
  password: string;
}

export interface ForgotPasswordData {
  email: string;
}

export interface ResetPasswordData {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}

export interface UpdateProfileData {
  name?: string;
  timezone?: string;
  phone?: string;
  headline?: string;
  bio?: string;
  location?: string;
  linkedin_url?: string;
  github_url?: string;
  website_url?: string;
  preferences?: string;
}

export interface ChangePasswordData {
  current_password: string;
  password: string;
  password_confirmation: string;
}

export interface DeleteAccountData {
  password: string;
}
