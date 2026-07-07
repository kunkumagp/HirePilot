import { useState, useEffect, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { resumeApi, ResumeVersionItem, ResumeListItem } from '../../api/resumes';
import { Card, CardHeader, CardTitle, CardContent } from '../../components/ui/card';
import { Button } from '../../components/ui/button';
import { Alert } from '../../components/ui/alert';
import { Spinner } from '../../components/ui/spinner';
import { AxiosError } from 'axios';

export function ResumeDetailPage() {
  const { uuid } = useParams<{ uuid: string }>();
  const navigate = useNavigate();
  const [resume, setResume] = useState<ResumeListItem | null>(null);
  const [versions, setVersions] = useState<ResumeVersionItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [editingSection, setEditingSection] = useState<string | null>(null);
  const [editValue, setEditValue] = useState('');
  const fileInputRef = useRef<HTMLInputElement>(null);

  const fetchDetail = async () => {
    if (!uuid) return;
    try {
      const response = await resumeApi.show(uuid);
      const data = response.data.data;
      setResume(data.resume);
      setVersions(data.versions);
    } catch {
      setError('Failed to load resume.');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchDetail();
  }, [uuid]);

  const handleActivateVersion = async (versionUuid: string) => {
    if (!uuid) return;
    try {
      await resumeApi.activateVersion(uuid, versionUuid);
      setVersions((prev) =>
        prev.map((v) => ({ ...v, is_current: v.uuid === versionUuid }))
      );
      setSuccess('Version activated.');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Failed to activate.');
    }
  };

  const handleAddVersion = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file || !uuid) return;

    try {
      await resumeApi.addVersion(uuid, file);
      await fetchDetail();
      setSuccess('New version added.');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Upload failed.');
    }
  };

  const handleEditSection = (section: string, content: string) => {
    setEditingSection(section);
    setEditValue(content);
  };

  const handleSaveSection = async () => {
    if (!uuid || !editingSection || !currentVersion) return;

    const updatedContent = { ...(currentVersion.parsed_content || {}), [editingSection]: editValue };

    try {
      await resumeApi.updateParsedContent(uuid, currentVersion.uuid, updatedContent);
      setVersions((prev) =>
        prev.map((v) =>
          v.uuid === currentVersion.uuid
            ? { ...v, parsed_content: updatedContent }
            : v
        )
      );
      setEditingSection(null);
      setSuccess('Section updated.');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Save failed.');
    }
  };

  const handleDelete = async () => {
    if (!uuid) return;
    try {
      await resumeApi.delete(uuid);
      navigate('/resumes');
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Delete failed.');
    }
  };

  const currentVersion = versions.find((v) => v.is_current);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!resume) {
    return <Alert variant="error" message="Resume not found." />;
  }

  return (
    <div className="mx-auto max-w-3xl">
      <div className="mb-6 flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">{resume.title}</h1>
          <p className="mt-1 text-sm text-slate-400">
            {versions.length} version{versions.length !== 1 ? 's' : ''}
          </p>
        </div>
        <div className="flex gap-2">
          <Button variant="ghost" size="sm" onClick={() => navigate('/resumes')}>
            Back
          </Button>
          <Button variant="destructive" size="sm" onClick={handleDelete}>
            Delete
          </Button>
        </div>
      </div>

      {error && <Alert variant="error" message={error} onDismiss={() => setError(null)} className="mb-4" />}
      {success && <Alert variant="success" message={success} onDismiss={() => setSuccess(null)} className="mb-4" />}

      {currentVersion?.parsed_content && Object.keys(currentVersion.parsed_content).length > 0 && (
        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Parsed Content</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {Object.entries(currentVersion.parsed_content).map(([section, content]) => (
              <div key={section}>
                <div className="mb-1 flex items-center justify-between">
                  <h3 className="text-sm font-medium capitalize text-slate-300">{section}</h3>
                  <button
                    onClick={() => handleEditSection(section, content)}
                    className="text-xs text-cyan-500 hover:text-cyan-400"
                  >
                    {editingSection === section ? undefined : 'Edit'}
                  </button>
                </div>
                {editingSection === section ? (
                  <div className="space-y-2">
                    <textarea
                      className="min-h-[100px] w-full rounded-lg border border-slate-700 bg-slate-900 p-3 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                      value={editValue}
                      onChange={(e) => setEditValue(e.target.value)}
                    />
                    <div className="flex gap-2">
                      <Button size="sm" onClick={handleSaveSection}>Save</Button>
                      <Button size="sm" variant="ghost" onClick={() => setEditingSection(null)}>Cancel</Button>
                    </div>
                  </div>
                ) : (
                  <p className="whitespace-pre-wrap text-sm text-slate-400">{content}</p>
                )}
              </div>
            ))}
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Versions</CardTitle>
            <div>
              <input
                ref={fileInputRef}
                type="file"
                accept=".pdf,.docx,.txt"
                className="hidden"
                onChange={handleAddVersion}
              />
              <Button size="sm" onClick={() => fileInputRef.current?.click()}>
                Add Version
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          {versions.length === 0 ? (
            <p className="text-sm text-slate-400">No versions yet.</p>
          ) : (
            <div className="space-y-3">
              {versions.map((version) => (
                <div
                  key={version.uuid}
                  className="flex items-center justify-between rounded-lg border border-slate-800 p-4"
                >
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <span className="text-sm font-medium text-slate-200">
                        v{version.version_number}
                      </span>
                      {version.is_current && (
                        <span className="rounded-full bg-cyan-900/50 px-2 py-0.5 text-xs text-cyan-400">
                          Current
                        </span>
                      )}
                      <span className="text-xs text-slate-500">
                        {version.file_type.toUpperCase()} · {(version.file_size / 1024).toFixed(1)} KB
                      </span>
                    </div>
                    <p className="text-xs text-slate-500">
                      {version.original_filename} ·{' '}
                      {version.parse_status === 'completed'
                        ? 'Parsed'
                        : version.parse_status === 'failed'
                          ? 'Parse failed'
                          : 'Parsing...'}
                    </p>
                  </div>
                  <div className="flex gap-2">
                    {!version.is_current && (
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleActivateVersion(version.uuid)}
                      >
                        Activate
                      </Button>
                    )}
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={async () => {
                        try {
                          const response = await resumeApi.downloadVersion(uuid!, version.uuid);
                          const url = URL.createObjectURL(response.data as Blob);
                          const a = document.createElement('a');
                          a.href = url;
                          a.download = version.original_filename;
                          a.click();
                          URL.revokeObjectURL(url);
                        } catch {
                          setError('Download failed.');
                        }
                      }}
                    >
                      Download
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
