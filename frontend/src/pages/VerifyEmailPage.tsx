import { useState } from 'react';
import { useAuthStore } from '../stores/auth-store';
import { authApi } from '../api/auth';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Alert } from '../components/ui/alert';
import { AxiosError } from 'axios';

export function VerifyEmailPage() {
  const { user } = useAuthStore();
  const [isSending, setIsSending] = useState(false);
  const [sent, setSent] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleResend = async () => {
    setIsSending(true);
    setError(null);
    try {
      await authApi.resendVerification();
      setSent(true);
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Failed to resend. Please try again.');
    } finally {
      setIsSending(false);
    }
  };

  return (
    <div className="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center px-6">
      <Card className="w-full">
        <CardHeader>
          <CardTitle>Verify your email</CardTitle>
          <CardDescription>
            We sent a verification link to <strong>{user?.email}</strong>.
            Click the link to activate your account.
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {sent && (
            <Alert variant="success" message="Verification email sent! Check your inbox." />
          )}
          {error && <Alert variant="error" message={error} />}
          <Button
            variant="secondary"
            className="w-full"
            onClick={handleResend}
            isLoading={isSending}
          >
            {isSending ? 'Sending...' : 'Resend verification email'}
          </Button>
          <p className="text-center text-sm text-slate-500">
            Didn&apos;t receive it? Check your spam folder.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
