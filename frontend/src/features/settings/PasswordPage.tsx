import { useState } from 'react';
import { useAuthStore } from '../../stores/auth-store';
import { Card, CardHeader, CardTitle, CardContent } from '../../components/ui/card';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Alert } from '../../components/ui/alert';

export function PasswordPage() {
  const { changePassword, isLoading, error, clearError } = useAuthStore();
  const [success, setSuccess] = useState(false);
  const [formData, setFormData] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  const validate = () => {
    const errors: Record<string, string> = {};
    if (!formData.current_password) errors.current_password = 'Current password is required';
    if (!formData.password) errors.password = 'New password is required';
    else if (formData.password.length < 8) errors.password = 'Minimum 8 characters';
    if (formData.password !== formData.password_confirmation) errors.password_confirmation = 'Passwords do not match';
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError();
    setSuccess(false);
    if (!validate()) return;

    try {
      await changePassword(formData);
      setSuccess(true);
      setFormData({ current_password: '', password: '', password_confirmation: '' });
    } catch {
      // error set by store
    }
  };

  const updateField = (field: string, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
    setValidationErrors((prev) => ({ ...prev, [field]: '' }));
  };

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-semibold">Password</h1>
      <Card>
        <CardHeader>
          <CardTitle>Change Password</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            {success && <Alert variant="success" message="Password changed successfully." />}
            {error && <Alert variant="error" message={error} onDismiss={clearError} />}
            <Input
              label="Current Password"
              type="password"
              value={formData.current_password}
              onChange={(e) => updateField('current_password', e.target.value)}
              error={validationErrors.current_password}
              autoComplete="current-password"
            />
            <Input
              label="New Password"
              type="password"
              value={formData.password}
              onChange={(e) => updateField('password', e.target.value)}
              error={validationErrors.password}
              placeholder="At least 8 characters"
              autoComplete="new-password"
            />
            <Input
              label="Confirm New Password"
              type="password"
              value={formData.password_confirmation}
              onChange={(e) => updateField('password_confirmation', e.target.value)}
              error={validationErrors.password_confirmation}
              autoComplete="new-password"
            />
            <div className="pt-2">
              <Button type="submit" isLoading={isLoading}>
                {isLoading ? 'Changing...' : 'Change password'}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
