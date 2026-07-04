import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../stores/auth-store';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Button } from '../components/ui/button';
import { Alert } from '../components/ui/alert';

export function LoginPage() {
  const navigate = useNavigate();
  const { login, isLoading, error, clearError } = useAuthStore();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  const validate = () => {
    const errors: Record<string, string> = {};
    if (!email) errors.email = 'Email is required';
    if (!password) errors.password = 'Password is required';
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError();
    if (!validate()) return;

    try {
      await login(email, password);
      navigate('/');
    } catch {
      // error is set in the store
    }
  };

  return (
    <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center px-6">
      <Card className="w-full">
        <CardHeader>
          <CardTitle>Welcome back</CardTitle>
          <p className="mt-1 text-sm text-slate-400">Sign in to your HirePilot account</p>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4" noValidate>
            {error && <Alert variant="error" message={error} onDismiss={clearError} />}
            <Input
              label="Email"
              type="email"
              value={email}
              onChange={(e) => { setEmail(e.target.value); setValidationErrors((prev) => ({ ...prev, email: '' })); }}
              error={validationErrors.email}
              placeholder="john@example.com"
              autoComplete="email"
            />
            <Input
              label="Password"
              type="password"
              value={password}
              onChange={(e) => { setPassword(e.target.value); setValidationErrors((prev) => ({ ...prev, password: '' })); }}
              error={validationErrors.password}
              placeholder="Enter your password"
              autoComplete="current-password"
            />
            <div className="text-right">
              <Link to="/forgot-password" className="text-sm text-cyan-500 hover:text-cyan-400">
                Forgot password?
              </Link>
            </div>
            <Button type="submit" className="w-full" isLoading={isLoading}>
              {isLoading ? 'Signing in...' : 'Sign in'}
            </Button>
          </form>
        </CardContent>
        <CardFooter className="justify-center">
          <p className="text-sm text-slate-400">
            Don&apos;t have an account?{' '}
            <Link to="/register" className="text-cyan-500 hover:text-cyan-400">
              Create one
            </Link>
          </p>
        </CardFooter>
      </Card>
    </div>
  );
}
