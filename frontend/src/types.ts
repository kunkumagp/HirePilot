export interface User {
  id: number;
  name: string;
  email: string;
  portfolio_url: string | null;
  is_active: boolean;
  plan: 'free' | 'pro';
}

export interface Job {
  id: number;
  title: string;
  company: string;
  url: string;
  location: string;
  salary_min: string | null;
  salary_max: string | null;
  salary_currency: string | null;
  source: string;
  score: number;
  reasons: string[];
  status: string;
  location_uncertain: boolean;
  salary_unknown: boolean;
  cover_letter: string | null;
  notified_at: string | null;
}

export interface ApiNotification {
  id: string;
  type: string;
  data: {
    title?: string;
    body?: string;
    cover_letter?: string;
    url?: string;
  };
  read_at: string | null;
  created_at: string | null;
}

export interface UserSettings {
  skills: string[];
  title_include: string[];
  exclude_words: string[];
  location_accept: string[];
  location_reject: string[];
  max_age_days: number;
  min_salary_usd_yearly: number;
  allow_unknown_salary: boolean;
  min_score: number;
  weights: Record<string, number>;
  max_notifications_per_run: number;
}

export interface Channel {
  id: number;
  type: 'telegram' | 'email' | 'inapp';
  enabled: boolean;
  target: string | null;
}

export interface Paginated<T> {
  data: T[];
}
