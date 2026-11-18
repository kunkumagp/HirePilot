import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from 'react';
import { api, clearToken, getToken, setToken } from '../api/client';
import type { User } from '../types';

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, confirmation: string) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const bootstrap = async () => {
      if (!getToken()) {
        setLoading(false);
        return;
      }

      try {
        setUser(await api<User>('/user'));
      } catch {
        clearToken();
      } finally {
        setLoading(false);
      }
    };

    void bootstrap();
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const result = await api<{ token: string; user: User }>('/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    setToken(result.token);
    setUser(result.user);
  }, []);

  const register = useCallback(
    async (name: string, email: string, password: string, confirmation: string) => {
      const result = await api<{ token: string; user: User }>('/register', {
        method: 'POST',
        body: JSON.stringify({
          name,
          email,
          password,
          password_confirmation: confirmation,
        }),
      });
      setToken(result.token);
      setUser(result.user);
    },
    [],
  );

  const logout = useCallback(async () => {
    try {
      await api('/logout', { method: 'POST' });
    } finally {
      clearToken();
      setUser(null);
    }
  }, []);

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth must be used inside AuthProvider');
  }

  return context;
}
