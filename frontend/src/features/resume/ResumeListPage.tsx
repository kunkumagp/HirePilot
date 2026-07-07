import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { resumeApi } from '../../api/resumes';
import { Spinner } from '../../components/ui/spinner';
import { Alert } from '../../components/ui/alert';
import { Card, CardContent } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { AxiosError } from 'axios';

interface ResumeListItem {
  uuid: string;
  title: string;
  is_active: boolean;
  current_version: {
    version_number: number;
    original_filename: string;
    file_type: string;
    parse_status: string;
    created_at: string;
  } | null;
  version_count: number;
  created_at: string;
}

export function ResumeListPage() {
  const [resumes, setResumes] = useState<ResumeListItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchResumes = async () => {
    try {
      const response = await resumeApi.list();
      setResumes(response.data.data);
    } catch {
      setError('Failed to load resumes.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchResumes();
  }, []);

  const handleDelete = async (uuid: string) => {
    try {
      await resumeApi.delete(uuid);
      setResumes((prev) => prev.filter((r) => r.uuid !== uuid));
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
    <div>
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Resumes</h1>
          <p className="mt-1 text-sm text-slate-400">Manage your CVs and resumes</p>
        </div>
        <div className="flex gap-3">
          <Link to="/resumes/trash">
            <Button variant="ghost" size="sm">Trash</Button>
          </Link>
          <Link to="/resumes/new">
            <Button>New Resume</Button>
          </Link>
        </div>
      </div>

      {error && <Alert variant="error" message={error} onDismiss={() => setError(null)} className="mb-4" />}

      {resumes.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center py-16">
            <div className="mb-4 text-4xl">📄</div>
            <h3 className="mb-2 text-lg font-medium text-slate-200">No resumes yet</h3>
            <p className="mb-6 text-sm text-slate-400">Upload your first resume to get started.</p>
            <Link to="/resumes/new">
              <Button>Upload Resume</Button>
            </Link>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {resumes.map((resume) => (
            <Link key={resume.uuid} to={`/resumes/${resume.uuid}`}>
              <Card className="h-full transition hover:border-slate-700 hover:bg-slate-900/80">
                <CardContent className="p-5">
                  <div className="mb-3 flex items-start justify-between">
                    <h3 className="font-medium text-slate-200">{resume.title}</h3>
                    <button
                      onClick={(e) => { e.preventDefault(); handleDelete(resume.uuid); }}
                      className="text-xs text-slate-500 hover:text-red-400"
                    >
                      Delete
                    </button>
                  </div>
                  <div className="space-y-1 text-sm text-slate-500">
                    <p>{resume.version_count} version{resume.version_count !== 1 ? 's' : ''}</p>
                    {resume.current_version && (
                      <>
                        <p>v{resume.current_version.version_number} · {resume.current_version.file_type.toUpperCase()}</p>
                        <p className="text-xs">
                          {resume.current_version.parse_status === 'completed'
                            ? 'Parsed'
                            : resume.current_version.parse_status === 'failed'
                              ? 'Parse failed'
                              : 'Parsing...'}
                        </p>
                      </>
                    )}
                  </div>
                </CardContent>
              </Card>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
