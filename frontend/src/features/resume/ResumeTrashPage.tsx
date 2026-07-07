import { useState, useEffect } from 'react';
import { resumeApi, ResumeListItem } from '../../api/resumes';
import { Card, CardContent } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Alert } from '../../components/ui/alert';
import { Spinner } from '../../components/ui/spinner';
import { AxiosError } from 'axios';

export function ResumeTrashPage() {
  const [resumes, setResumes] = useState<ResumeListItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const fetchTrash = async () => {
    try {
      const response = await resumeApi.trash();
      setResumes(response.data.data);
    } catch {
      setError('Failed to load trash.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchTrash();
  }, []);

  const handleRestore = async (uuid: string) => {
    try {
      await resumeApi.restore(uuid);
      setResumes((prev) => prev.filter((r) => r.uuid !== uuid));
      setSuccess('Resume restored.');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Restore failed.');
    }
  };

  const handleForceDelete = async (uuid: string) => {
    if (!window.confirm('Permanently delete this resume? This cannot be undone.')) return;
    try {
      await resumeApi.forceDelete(uuid);
      setResumes((prev) => prev.filter((r) => r.uuid !== uuid));
      setSuccess('Resume permanently deleted.');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Delete failed.');
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
      <div className="mb-6">
        <h1 className="text-2xl font-semibold">Trash</h1>
        <p className="mt-1 text-sm text-slate-400">Deleted resumes are recoverable for 30 days.</p>
      </div>

      {error && <Alert variant="error" message={error} onDismiss={() => setError(null)} className="mb-4" />}
      {success && <Alert variant="success" message={success} onDismiss={() => setSuccess(null)} className="mb-4" />}

      {resumes.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <p className="text-sm text-slate-400">Trash is empty.</p>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {resumes.map((resume) => (
            <Card key={resume.uuid}>
              <CardContent className="flex items-center justify-between p-4">
                <div>
                  <p className="text-sm font-medium text-slate-200">{resume.title}</p>
                  <p className="text-xs text-slate-500">
                    Deleted {resume.deleted_at ? new Date(resume.deleted_at).toLocaleDateString() : 'Unknown'}
                  </p>
                </div>
                <div className="flex gap-2">
                  <Button variant="secondary" size="sm" onClick={() => handleRestore(resume.uuid)}>
                    Restore
                  </Button>
                  <Button variant="destructive" size="sm" onClick={() => handleForceDelete(resume.uuid)}>
                    Delete forever
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}
