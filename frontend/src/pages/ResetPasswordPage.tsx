import { useState } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import { authApi } from '../api/auth';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Button } from '../components/ui/button';
import { Alert } from '../components/ui/alert';
import { AxiosError } from 'axios';

export function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const token = searchParams.get('token') || '';
  const emailParam = searchParams.get('email') || '';

  const [formData, setFormData] = useState({
    email: emailParam,
    password: '',
    password_confirmation: '',
  });
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  const validate = () => {
    const errors: Record<string, string> = {};
    if (!formData.email) errors.email = 'Email is required';
    if (!formData.password) errors.password = 'Password is required';
    else if (formData.password.length < 8) errors.password = 'Minimum 8 characters';
    if (formData.password !== formData.password_confirmation) errors.password_confirmation = 'Passwords do not match';
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;

    setIsLoading(true);
    setError(null);

    try {
      await authApi.resetPassword({ ...formData, token });
      navigate('/login', { state: { reset: true } });
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Invalid or expired reset link.');
    } finally {
      setIsLoading(false);
    }
  };

  if (!token) {
    return (
      <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center px-6">
        <Card className="w-full">
          <CardHeader>
            <CardTitle>Invalid reset link</CardTitle>
            <CardDescription>This password reset link is invalid or has expired.</CardDescription>
          </CardHeader>
          <CardContent>
            <Link to="/forgot-password">
              <Button variant="secondary" className="w-full">
                Request new link
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center px-6">
      <Card className="w-full">
        <CardHeader>
          <CardTitle>Set new password</CardTitle>
          <CardDescription>Choose a new password for your account.</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            {error && <Alert variant="error" message={error} />}
            <Input
              label="Email"
              type="email"
              value={formData.email}
              onChange={(e) => { setFormData((prev) => ({ ...prev, email: e.target.value })); setValidationErrors((prev) => ({ ...prev, email: '' })); }}
              error={validationErrors.email}
              autoComplete="email"
            />
            <Input
              label="New Password"
              type="password"
              value={formData.password}
              onChange={(e) => { setFormData((prev) => ({ ...prev, password: e.target.value })); setValidationErrors((prev) => ({ ...prev, password: '' })); }}
              error={validationErrors.password}
              placeholder="At least 8 characters"
              autoComplete="new-password"
            />
            <Input
              label="Confirm Password"
              type="password"
              value={formData.password_confirmation}
              onChange={(e) => { setFormData((prev) => ({ ...prev, password_confirmation: e.target.value })); setValidationErrors((prev) => ({ ...prev, password_confirmation: '' })); }}
              error={validationErrors.password_confirmation}
              autoComplete="new-password"
            />
            <Button type="submit" className="w-full" isLoading={isLoading}>
              {isLoading ? 'Resetting...' : 'Reset password'}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
