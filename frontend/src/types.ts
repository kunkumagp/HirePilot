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

export interface JobDetail {
  id: number;
  title: string;
  company: string;
  url: string;
  source: string;
  location: string;
  salary_min: string | null;
  salary_max: string | null;
  salary_currency: string | null;
  description: string;
  tags: string[];
  posted_at: string | null;
  first_seen_at: string | null;
  score: number;
  reasons: string[];
  status: string;
  location_uncertain: boolean;
  salary_unknown: boolean;
  cover_letter: string | null;
  notified_at: string | null;
  created_at: string | null;
}

export interface DocumentItem {
  id: number;
  original_name: string;
  size: number;
  extracted_data: Record<string, unknown> | null;
  created_at: string | null;
}

export interface CvData {
  name: string;
  email: string;
  phone: string;
  summary: string;
  skills: string[];
  experience: string;
  education: string;
  projects: string;
  certifications: string;
  languages: string;
  awards: string;
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
  field: string;
  job_query: string | null;
  job_category: string | null;
  job_subcategory: string | null;
  field_keywords: string[];
  experience_level: 'any' | 'junior' | 'mid' | 'senior' | 'lead';
  min_years_experience: number | null;
  work_arrangement: 'any' | 'remote' | 'hybrid' | 'onsite';
  preferred_countries: string[];
  salary_currency: string;
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

export interface JobCategoryOption {
  key: string;
  label: string;
  subcategories: Array<{ key: string; label: string }>;
}

export interface SettingsOptions {
  fields: Array<{ key: string; label: string }>;
  job_categories: JobCategoryOption[];
  experience_levels: string[];
  arrangements: string[];
}

export interface Channel {
  id: number;
  type: 'telegram' | 'email' | 'inapp';
  enabled: boolean;
  target: string | null;
}

export interface Paginated<T> {
  data: T[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  links?: {
    next: string | null;
  };
}
