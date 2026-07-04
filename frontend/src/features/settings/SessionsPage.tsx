import { useState, useEffect, useCallback } from 'react';
import { authApi } from '../../api/auth';
import { Card, CardHeader, CardTitle, CardContent } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Alert } from '../../components/ui/alert';
import { Spinner } from '../../components/ui/spinner';
import type { Session } from '../../types/auth';
import { AxiosError } from 'axios';

export function SessionsPage() {
  const [sessions, setSessions] = useState<Session[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [revokingId, setRevokingId] = useState<number | null>(null);

  const fetchSessions = useCallback(async () => {
    try {
      const response = await authApi.getSessions();
      setSessions(response.data.data);
    } catch {
      setError('Failed to load sessions.');
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchSessions();
  }, [fetchSessions]);

  const handleRevoke = async (tokenId: number) => {
    setRevokingId(tokenId);
    setError(null);
    try {
      await authApi.revokeSession(tokenId);
      setSessions((prev) => prev.filter((s) => s.id !== tokenId));
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Failed to revoke session.');
    } finally {
      setRevokingId(null);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-semibold">Active Sessions</h1>
      <Card>
        <CardHeader>
          <CardTitle>Sessions</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {error && <Alert variant="error" message={error} />}
          {sessions.length === 0 ? (
            <p className="text-sm text-slate-400">No active sessions.</p>
          ) : (
            sessions.map((session) => (
              <div
                key={session.id}
                className="flex items-center justify-between rounded-lg border border-slate-800 p-4"
              >
                <div className="space-y-1">
                  <div className="flex items-center gap-2">
                    <span className="text-sm font-medium text-slate-200">{session.name}</span>
                    {session.is_current && (
                      <span className="rounded-full bg-cyan-900/50 px-2 py-0.5 text-xs text-cyan-400">
                        Current
                      </span>
                    )}
                  </div>
                  <p className="text-xs text-slate-500">
                    {session.last_used_at
                      ? `Last used ${session.last_used_at}`
                      : 'Never used'}
                    {' · '}Created {new Date(session.created_at).toLocaleDateString()}
                  </p>
                </div>
                {!session.is_current && (
                  <Button
                    variant="ghost"
                    size="sm"
                    isLoading={revokingId === session.id}
                    onClick={() => handleRevoke(session.id)}
                    className="text-red-400 hover:text-red-300"
                  >
                    {revokingId === session.id ? 'Revoking...' : 'Revoke'}
                  </Button>
                )}
              </div>
            ))
          )}
        </CardContent>
      </Card>
    </div>
  );
}
