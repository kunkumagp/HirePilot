import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../stores/auth-store';
import { Avatar } from '../components/ui/avatar';
import { Button } from '../components/ui/button';
import { Alert } from '../components/ui/alert';

export function AuthenticatedLayout() {
  const { user, isEmailVerified, logout } = useAuthStore();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100">
      <header className="sticky top-0 z-50 border-b border-slate-800 bg-slate-950/80 backdrop-blur-sm">
        <nav className="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
          <div className="flex items-center gap-6">
            <Link to="/" className="text-xl font-bold text-cyan-500">
              HirePilot
            </Link>
            <div className="hidden items-center gap-4 md:flex">
              <Link to="/jobs" className="text-sm text-slate-400 hover:text-slate-200">
                Jobs
              </Link>
              <Link to="/resume" className="text-sm text-slate-400 hover:text-slate-200">
                Resumes
              </Link>
              <Link to="/applications" className="text-sm text-slate-400 hover:text-slate-200">
                Applications
              </Link>
            </div>
          </div>
          <div className="flex items-center gap-3">
            <Link to="/settings/profile">
              <Avatar name={user?.name || 'User'} size="sm" />
            </Link>
            <Button variant="ghost" size="sm" onClick={handleLogout}>
              Sign out
            </Button>
          </div>
        </nav>
      </header>

      {!isEmailVerified && (
        <div className="mx-auto max-w-6xl px-6 pt-4">
          <Alert
            variant="warning"
            message="Please verify your email address to access all features."
          />
        </div>
      )}

      <main className="mx-auto max-w-6xl px-6 py-8">
        <Outlet />
      </main>
    </div>
  );
}
