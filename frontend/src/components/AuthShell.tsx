import type { ReactNode } from 'react';
import { useTheme } from '../context/ThemeContext';

interface AuthShellProps {
  title: string;
  subtitle: string;
  children: ReactNode;
  footer: ReactNode;
}

export function AuthShell({ title, subtitle, children, footer }: AuthShellProps) {
  const { theme, toggle } = useTheme();

  return (
    <div className="auth-shell">
      <aside className="auth-brand">
        <div className="brand brand-light">
          <span className="brand-mark">HP</span>
          <span className="brand-name">HirePilot</span>
        </div>
        <div className="auth-brand-copy">
          <h1>Work that fits you, delivered.</h1>
          <p>Remote roles filtered for your skills, your location and your salary — straight to your inbox.</p>
          <ul className="auth-features">
            <li><span>✓</span> Five free job sources in one place</li>
            <li><span>✓</span> Transparent, configurable scoring rules</li>
            <li><span>✓</span> Ready-made cover letters, no AI needed</li>
          </ul>
        </div>
        <p className="auth-footnote">Built for developers in Sri Lanka.</p>
      </aside>

      <main className="auth-panel">
        <button
          type="button"
          className="theme-toggle auth-theme-toggle"
          onClick={toggle}
          title={theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'}
          aria-label={theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'}
        >
          {theme === 'dark' ? '☀' : '☾'}
        </button>
        <div className="auth-card">
          <h2>{title}</h2>
          <p className="muted">{subtitle}</p>
          {children}
          <div className="auth-footer">{footer}</div>
        </div>
      </main>
    </div>
  );
}
