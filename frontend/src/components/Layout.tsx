import { useEffect, useState } from 'react';
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { api } from '../api/client';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import type { ApiNotification, Paginated } from '../types';

const pageTitles: Record<string, string> = {
  '/': 'Dashboard',
  '/jobs': 'Matched jobs',
  '/notifications': 'Notifications',
  '/settings': 'Settings',
};

const navigation = [
  { to: '/', label: 'Dashboard', icon: '◧' },
  { to: '/jobs', label: 'Jobs', icon: '▤' },
  { to: '/notifications', label: 'Notifications', icon: '◉' },
  { to: '/settings', label: 'Settings', icon: '⚙' },
  { to: '/documents', label: 'Documents', icon: '📄' },
];

export function Layout() {
  const { user, logout } = useAuth();
  const { theme, toggle } = useTheme();
  const navigate = useNavigate();
  const location = useLocation();
  const [unread, setUnread] = useState(0);

  useEffect(() => {
    void api<Paginated<ApiNotification>>('/notifications')
      .then((result) => setUnread(result.data.filter((item) => item.read_at === null).length))
      .catch(() => undefined);
  }, [location.pathname]);

  const onLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <span className="brand-mark">HP</span>
          <span className="brand-name">HirePilot</span>
        </div>

        <nav className="side-nav">
          {navigation.map((item) => (
            <NavLink key={item.to} to={item.to} end={item.to === '/'} className="side-link">
              <span className="side-icon">{item.icon}</span>
              <span>{item.label}</span>
            </NavLink>
          ))}
        </nav>

        <div className="sidebar-footer">
          <div className="user-chip">
            <span className="avatar">{(user?.name ?? 'U').charAt(0).toUpperCase()}</span>
            <span className="user-chip-text">
              <strong>{user?.name}</strong>
              <small>{user?.plan} plan</small>
            </span>
          </div>
          <button type="button" className="button button-ghost" onClick={() => void onLogout()}>
            Log out
          </button>
        </div>
      </aside>

      <div className="app-main">
        <header className="topbar">
          <div>
            <p className="eyebrow">Workspace</p>
            <h1 className="page-title">{pageTitles[location.pathname] ?? 'HirePilot'}</h1>
          </div>
          <div className="topbar-actions">
            <button
              type="button"
              className="theme-toggle"
              onClick={toggle}
              title={theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'}
              aria-label={theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'}
            >
              {theme === 'dark' ? '☀' : '☾'}
            </button>
            <NavLink to="/notifications" className="bell" aria-label="Notifications">
              ◉
              {unread > 0 ? <span className="bell-badge">{unread}</span> : null}
            </NavLink>
            <span className="muted topbar-email">{user?.email}</span>
          </div>
        </header>

        <main className="content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
