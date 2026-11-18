import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const onLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <div className="shell">
      <header className="topbar">
        <strong>HirePilot</strong>
        <nav>
          <NavLink to="/">Dashboard</NavLink>
          <NavLink to="/jobs">Jobs</NavLink>
          <NavLink to="/notifications">Notifications</NavLink>
          <NavLink to="/settings">Settings</NavLink>
          <button type="button" onClick={() => void onLogout()}>
            Log out
          </button>
        </nav>
        <span className="muted">{user?.email}</span>
      </header>
      <main className="content">
        <Outlet />
      </main>
    </div>
  );
}
