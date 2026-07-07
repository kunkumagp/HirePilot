import { createBrowserRouter, Navigate } from 'react-router-dom';
import { PublicLayout } from '../layouts/PublicLayout';
import { AuthenticatedLayout } from '../layouts/AuthenticatedLayout';
import { LoginPage } from '../pages/LoginPage';
import { RegisterPage } from '../pages/RegisterPage';
import { ForgotPasswordPage } from '../pages/ForgotPasswordPage';
import { ResetPasswordPage } from '../pages/ResetPasswordPage';
import { VerifyEmailPage } from '../pages/VerifyEmailPage';
import { ProfilePage } from '../features/settings/ProfilePage';
import { PasswordPage } from '../features/settings/PasswordPage';
import { SessionsPage } from '../features/settings/SessionsPage';
import { ResumeListPage } from '../features/resume/ResumeListPage';
import { ResumeCreatePage } from '../features/resume/ResumeCreatePage';
import { ResumeDetailPage } from '../features/resume/ResumeDetailPage';
import { ResumeTrashPage } from '../features/resume/ResumeTrashPage';
import App from '../App';
import { useAuthStore } from '../stores/auth-store';

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore();
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
}

function PublicRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore();
  if (isAuthenticated) {
    return <Navigate to="/" replace />;
  }
  return <>{children}</>;
}

export const router = createBrowserRouter([
  {
    path: '/',
    element: <PublicLayout />,
    children: [
      {
        index: true,
        element: <App />,
      },
      {
        path: 'login',
        element: (
          <PublicRoute>
            <LoginPage />
          </PublicRoute>
        ),
      },
      {
        path: 'register',
        element: (
          <PublicRoute>
            <RegisterPage />
          </PublicRoute>
        ),
      },
      {
        path: 'forgot-password',
        element: (
          <PublicRoute>
            <ForgotPasswordPage />
          </PublicRoute>
        ),
      },
      {
        path: 'reset-password',
        element: (
          <PublicRoute>
            <ResetPasswordPage />
          </PublicRoute>
        ),
      },
      {
        path: 'verify-email',
        element: (
          <ProtectedRoute>
            <VerifyEmailPage />
          </ProtectedRoute>
        ),
      },
    ],
  },
  {
    path: '/',
    element: (
      <ProtectedRoute>
        <AuthenticatedLayout />
      </ProtectedRoute>
    ),
    children: [
      {
        path: 'settings/profile',
        element: <ProfilePage />,
      },
      {
        path: 'settings/password',
        element: <PasswordPage />,
      },
      {
        path: 'settings/sessions',
        element: <SessionsPage />,
      },
      {
        path: 'resumes',
        element: <ResumeListPage />,
      },
      {
        path: 'resumes/new',
        element: <ResumeCreatePage />,
      },
      {
        path: 'resumes/:uuid',
        element: <ResumeDetailPage />,
      },
      {
        path: 'resumes/trash',
        element: <ResumeTrashPage />,
      },
    ],
  },
]);
