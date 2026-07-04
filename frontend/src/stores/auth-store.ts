import { create } from 'zustand';
import type { User } from '../types/auth';
import { authApi } from '../api/auth';
import type { RegisterData, UpdateProfileData, ChangePasswordData } from '../types/auth';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isEmailVerified: boolean;
  isLoading: boolean;
  error: string | null;
  setUser: (user: User | null) => void;
  register: (data: RegisterData) => Promise<void>;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  fetchUser: () => Promise<void>;
  updateProfile: (data: UpdateProfileData) => Promise<void>;
  changePassword: (data: ChangePasswordData) => Promise<void>;
  deleteAccount: (password: string) => Promise<void>;
  clearError: () => void;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: (() => {
    try {
      const stored = localStorage.getItem('auth-user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  })(),
  token: localStorage.getItem('auth-token'),
  isAuthenticated: !!localStorage.getItem('auth-token'),
  isEmailVerified: false,
  isLoading: false,
  error: null,

  setUser: (user) => {
    if (user) {
      localStorage.setItem('auth-user', JSON.stringify(user));
    } else {
      localStorage.removeItem('auth-user');
    }
    set({
      user,
      isAuthenticated: !!user,
      isEmailVerified: user?.email_verified_at !== null && user?.email_verified_at !== undefined,
    });
  },

  register: async (data) => {
    set({ isLoading: true, error: null });
    try {
      const response = await authApi.register(data);
      const { user, token } = response.data.data;
      localStorage.setItem('auth-token', token);
      localStorage.setItem('auth-user', JSON.stringify(user));
      set({
        user,
        token,
        isAuthenticated: true,
        isEmailVerified: user.email_verified_at !== null,
        isLoading: false,
      });
    } catch (error: unknown) {
      const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Registration failed';
      set({ isLoading: false, error: message });
      throw error;
    }
  },

  login: async (email, password) => {
    set({ isLoading: true, error: null });
    try {
      const response = await authApi.login({ email, password });
      const { user, token } = response.data.data;
      localStorage.setItem('auth-token', token);
      localStorage.setItem('auth-user', JSON.stringify(user));
      set({
        user,
        token,
        isAuthenticated: true,
        isEmailVerified: user.email_verified_at !== null,
        isLoading: false,
      });
    } catch (error: unknown) {
      const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Invalid credentials';
      set({ isLoading: false, error: message });
      throw error;
    }
  },

  logout: async () => {
    set({ isLoading: true });
    try {
      await authApi.logout();
    } catch {
      // Proceed with local logout even if API fails
    }
    localStorage.removeItem('auth-token');
    localStorage.removeItem('auth-user');
    set({
      user: null,
      token: null,
      isAuthenticated: false,
      isEmailVerified: false,
      isLoading: false,
      error: null,
    });
  },

  fetchUser: async () => {
    const token = get().token;
    if (!token) {
      set({ isAuthenticated: false, user: null });
      return;
    }
    try {
      const response = await authApi.me();
      const { user } = response.data.data;
      localStorage.setItem('auth-user', JSON.stringify(user));
      set({
        user,
        isAuthenticated: true,
        isEmailVerified: user.email_verified_at !== null,
      });
    } catch {
      localStorage.removeItem('auth-token');
      localStorage.removeItem('auth-user');
      set({ user: null, token: null, isAuthenticated: false });
    }
  },

  updateProfile: async (data) => {
    set({ isLoading: true, error: null });
    try {
      const response = await authApi.updateProfile(data);
      const { user } = response.data.data;
      localStorage.setItem('auth-user', JSON.stringify(user));
      set({ user, isLoading: false });
    } catch (error: unknown) {
      const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Update failed';
      set({ isLoading: false, error: message });
      throw error;
    }
  },

  changePassword: async (data) => {
    set({ isLoading: true, error: null });
    try {
      await authApi.changePassword(data);
      set({ isLoading: false });
    } catch (error: unknown) {
      const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Password change failed';
      set({ isLoading: false, error: message });
      throw error;
    }
  },

  deleteAccount: async (password) => {
    set({ isLoading: true, error: null });
    try {
      await authApi.deleteAccount({ password });
      localStorage.removeItem('auth-token');
      localStorage.removeItem('auth-user');
      set({
        user: null,
        token: null,
        isAuthenticated: false,
        isEmailVerified: false,
        isLoading: false,
      });
    } catch (error: unknown) {
      const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Account deletion failed';
      set({ isLoading: false, error: message });
      throw error;
    }
  },

  clearError: () => set({ error: null }),
}));
