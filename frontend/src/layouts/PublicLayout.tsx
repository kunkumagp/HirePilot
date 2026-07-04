import { Link, Outlet, useLocation } from 'react-router-dom';

export function PublicLayout() {
  const location = useLocation();
  const isAuthPage = ['/login', '/register', '/forgot-password', '/reset-password'].includes(location.pathname);

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100">
      <header className="border-b border-slate-800">
        <nav className="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
          <Link to="/" className="text-xl font-bold text-cyan-500">
            HirePilot
          </Link>
          {isAuthPage && (
            <div className="flex items-center gap-4">
              <Link
                to={location.pathname === '/login' ? '/register' : '/login'}
                className="text-sm text-slate-400 hover:text-slate-200"
              >
                {location.pathname === '/login' ? 'Create account' : 'Sign in'}
              </Link>
            </div>
          )}
        </nav>
      </header>
      <main>
        <Outlet />
      </main>
    </div>
  );
}
