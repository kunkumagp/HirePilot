import { useState, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { resumeApi } from '../../api/resumes';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '../../components/ui/card';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Alert } from '../../components/ui/alert';
import { AxiosError } from 'axios';

export function ResumeCreatePage() {
  const navigate = useNavigate();
  const [title, setTitle] = useState('');
  const [file, setFile] = useState<File | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const fileInputRef = useRef<HTMLInputElement>(null);

  const validate = () => {
    const errors: Record<string, string> = {};
    if (!title.trim()) errors.title = 'Title is required';
    if (!file) errors.file = 'File is required';
    else {
      const ext = file.name.split('.').pop()?.toLowerCase();
      if (!['pdf', 'docx', 'txt'].includes(ext || '')) errors.file = 'Supported formats: PDF, DOCX, TXT';
      if (file.size > 10 * 1024 * 1024) errors.file = 'File size must not exceed 10MB';
    }
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    if (!validate() || !file) return;

    setIsLoading(true);
    try {
      const response = await resumeApi.create(title.trim(), file);
      navigate(`/resumes/${response.data.data.uuid}`);
    } catch (err) {
      const axiosError = err as AxiosError<{ message?: string }>;
      setError(axiosError?.response?.data?.message || 'Upload failed.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selected = e.target.files?.[0] || null;
    setFile(selected);
    setValidationErrors((prev) => ({ ...prev, file: '' }));
  };

  return (
    <div className="mx-auto max-w-2xl">
      <h1 className="mb-6 text-2xl font-semibold">Upload Resume</h1>
      <Card>
        <CardHeader>
          <CardTitle>New Resume</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-6" noValidate>
            {error && <Alert variant="error" message={error} />}
            <Input
              label="Resume Title"
              value={title}
              onChange={(e) => { setTitle(e.target.value); setValidationErrors((prev) => ({ ...prev, title: '' })); }}
              error={validationErrors.title}
              placeholder="e.g. Software Engineer CV"
            />
            <div className="space-y-1.5">
              <label className="block text-sm font-medium text-slate-300">File</label>
              <div
                className={`flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-8 transition
                  ${validationErrors.file ? 'border-red-500' : 'border-slate-700 hover:border-slate-500'}`}
                onClick={() => fileInputRef.current?.click()}
              >
                {file ? (
                  <div className="text-center">
                    <p className="text-sm font-medium text-slate-200">{file.name}</p>
                    <p className="mt-1 text-xs text-slate-500">
                      {(file.size / 1024 / 1024).toFixed(2)} MB
                    </p>
                  </div>
                ) : (
                  <div className="text-center">
                    <p className="mb-1 text-sm text-slate-400">
                      Drop your resume here or click to browse
                    </p>
                    <p className="text-xs text-slate-500">PDF, DOCX, or TXT (max 10MB)</p>
                  </div>
                )}
                <input
                  ref={fileInputRef}
                  type="file"
                  accept=".pdf,.docx,.txt,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain"
                  className="hidden"
                  onChange={handleFileSelect}
                />
              </div>
              {validationErrors.file && (
                <p className="text-sm text-red-400">{validationErrors.file}</p>
              )}
            </div>
            <Button type="submit" className="w-full" isLoading={isLoading}>
              {isLoading ? 'Uploading...' : 'Upload Resume'}
            </Button>
          </form>
        </CardContent>
        <CardFooter className="justify-center">
          <button
            onClick={() => navigate('/resumes')}
            className="text-sm text-slate-400 hover:text-slate-200"
          >
            Back to resumes
          </button>
        </CardFooter>
      </Card>
    </div>
  );
}
