import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../stores/auth-store';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '../components/ui/card';
import { Input } from '../components/ui/input';
import { Button } from '../components/ui/button';
import { Alert } from '../components/ui/alert';

export function RegisterPage() {
  const navigate = useNavigate();
  const { register, isLoading, error, clearError } = useAuthStore();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  const validate = () => {
    const errors: Record<string, string> = {};
    if (!formData.name) errors.name = 'Name is required';
    if (!formData.email) errors.email = 'Email is required';
    if (!formData.password) errors.password = 'Password is required';
    else if (formData.password.length < 8) errors.password = 'Password must be at least 8 characters';
    if (formData.password !== formData.password_confirmation) errors.password_confirmation = 'Passwords do not match';
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError();
    if (!validate()) return;

    try {
      await register(formData);
      navigate('/');
    } catch {
      // error is set in the store
    }
  };

  const updateField = (field: string, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
    setValidationErrors((prev) => ({ ...prev, [field]: '' }));
  };

  return (
    <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center px-6">
      <Card className="w-full">
        <CardHeader>
          <CardTitle>Create your account</CardTitle>
          <p className="mt-1 text-sm text-slate-400">Start your AI-powered job search journey</p>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4" noValidate>
            {error && <Alert variant="error" message={error} onDismiss={clearError} />}
            <Input
              label="Name"
              value={formData.name}
              onChange={(e) => updateField('name', e.target.value)}
              error={validationErrors.name}
              placeholder="John Doe"
              autoComplete="name"
            />
            <Input
              label="Email"
              type="email"
              value={formData.email}
              onChange={(e) => updateField('email', e.target.value)}
              error={validationErrors.email}
              placeholder="john@example.com"
              autoComplete="email"
            />
            <Input
              label="Password"
              type="password"
              value={formData.password}
              onChange={(e) => updateField('password', e.target.value)}
              error={validationErrors.password}
              placeholder="At least 8 characters"
              autoComplete="new-password"
            />
            <Input
              label="Confirm Password"
              type="password"
              value={formData.password_confirmation}
              onChange={(e) => updateField('password_confirmation', e.target.value)}
              error={validationErrors.password_confirmation}
              placeholder="Re-enter your password"
              autoComplete="new-password"
            />
            <Button type="submit" className="w-full" isLoading={isLoading}>
              {isLoading ? 'Creating account...' : 'Create account'}
            </Button>
          </form>
        </CardContent>
        <CardFooter className="justify-center">
          <p className="text-sm text-slate-400">
            Already have an account?{' '}
            <Link to="/login" className="text-cyan-500 hover:text-cyan-400">
              Sign in
            </Link>
          </p>
        </CardFooter>
      </Card>
    </div>
  );
}
