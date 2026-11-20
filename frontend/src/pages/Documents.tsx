import { useEffect, useState, type FormEvent } from 'react';
import { api, downloadFile } from '../api/client';
import type { CvData, DocumentItem } from '../types';

const emptyCv = (): CvData => ({
  name: '',
  tagline: '',
  location: '',
  email: '',
  phone: '',
  linkedin: '',
  github: '',
  summary: '',
  skills: [],
  experience: '',
  education: '',
  projects: '',
  certifications: '',
  languages: '',
  awards: '',
});

function fromExtracted(data: Record<string, unknown> | null): CvData {
  if (!data) {
    return emptyCv();
  }

  const asText = (key: string): string => String(data[key] ?? '');

  return {
    name: asText('name'),
    tagline: asText('tagline'),
    location: asText('location'),
    email: asText('email'),
    phone: asText('phone'),
    linkedin: asText('linkedin'),
    github: asText('github'),
    summary: asText('summary'),
    skills: Array.isArray(data.skills) ? data.skills.map(String) : [],
    experience: asText('experience'),
    education: asText('education'),
    projects: asText('projects'),
    certifications: asText('certifications'),
    languages: asText('languages'),
    awards: asText('awards'),
  };
}

export function Documents() {
  const [documents, setDocuments] = useState<DocumentItem[]>([]);
  const [selected, setSelected] = useState<DocumentItem | null>(null);
  const [form, setForm] = useState<CvData>(emptyCv());
  const [file, setFile] = useState<File | null>(null);
  const [uploading, setUploading] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    void (async () => {
      try {
        setDocuments(await api<DocumentItem[]>('/documents'));
      } catch (cause) {
        setError(cause instanceof Error ? cause.message : 'Could not load documents');
      }
    })();
  }, []);

  const upload = async (event: FormEvent) => {
    event.preventDefault();

    if (!file) {
      return;
    }

    setUploading(true);
    setNotice(null);
    setError(null);

    try {
      const payload = new FormData();
      payload.append('file', file);

      const document = await api<DocumentItem>('/documents', {
        method: 'POST',
        body: payload,
      });

      setDocuments((current) => [document, ...current]);
      setSelected(document);
      setForm(fromExtracted(document.extracted_data));
      setFile(null);
      setNotice('CV uploaded. Extracted data is ready to edit below.');
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : 'Upload failed');
    } finally {
      setUploading(false);
    }
  };

  const remove = async (document: DocumentItem) => {
    try {
      await api(`/documents/${document.id}`, { method: 'DELETE' });
      setDocuments((current) => current.filter((item) => item.id !== document.id));

      if (selected?.id === document.id) {
        setSelected(null);
        setForm(emptyCv());
      }
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : 'Could not delete document');
    }
  };

  const download = async (format: 'docx' | 'pdf') => {
    if (!selected) {
      return;
    }

    try {
      await downloadFile(
        `/documents/${selected.id}/cv/${format}`,
        { method: 'POST', body: JSON.stringify(form) },
        `ats-cv.${format}`,
      );
      setNotice(`${format.toUpperCase()} downloaded.`);
    } catch (cause) {
      setError(cause instanceof Error ? cause.message : 'Could not generate CV');
    }
  };

  const setField = (key: keyof CvData, value: string) => {
    setForm((current) => ({ ...current, [key]: value }));
  };

  if (error) {
    return <div className="alert alert-error">{error}</div>;
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h2>Documents</h2>
          <p className="muted">Upload a CV, extract its content, and build an ATS-friendly version.</p>
        </div>
      </div>

      <div className="panel">
        <form className="upload-row" onSubmit={(event) => void upload(event)}>
          <label className="upload-field">
            <span>Upload CV (PDF only, max 10 MB)</span>
            <input
              type="file"
              accept="application/pdf,.pdf"
              onChange={(event) => setFile(event.target.files?.[0] ?? null)}
            />
          </label>
          <button type="submit" className="button button-primary" disabled={uploading || !file}>
            {uploading ? 'Uploading…' : 'Upload & extract'}
          </button>
        </form>
        {notice ? <p className="save-message">{notice}</p> : null}
      </div>

      <div className="documents-grid">
        <div className="panel">
          <div className="panel-head">
            <h3>Your documents</h3>
          </div>

          {documents.length === 0 ? (
            <p className="empty-state">No documents uploaded yet.</p>
          ) : (
            <div className="document-list">
              {documents.map((document) => (
                <div
                  className={`document-row ${selected?.id === document.id ? 'is-selected' : ''}`}
                  key={document.id}
                >
                  <button
                    type="button"
                    className="document-open"
                    onClick={() => {
                      setSelected(document);
                      setForm(fromExtracted(document.extracted_data));
                    }}
                  >
                    <strong>{document.original_name}</strong>
                    <small>
                      {Math.round(document.size / 1024)} KB ·{' '}
                      {document.created_at ? new Date(document.created_at).toLocaleDateString() : ''}
                    </small>
                  </button>
                  <button
                    type="button"
                    className="button button-mini"
                    onClick={() => void remove(document)}
                  >
                    Delete
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

        <div className="panel">
          <div className="panel-head">
            <div>
              <h3>ATS CV builder</h3>
              <p className="muted">
                {selected ? `Based on ${selected.original_name}` : 'Upload or select a document first.'}
              </p>
            </div>
          </div>

          <fieldset disabled={!selected} className="cv-editor">
            <div className="form-grid">
              <label>
                <span>Full name</span>
                <input value={form.name} onChange={(e) => setField('name', e.target.value)} />
              </label>
              <label>
                <span>Tagline / role keywords</span>
                <input value={form.tagline} onChange={(e) => setField('tagline', e.target.value)} />
              </label>
              <label className="span-two">
                <span>Location</span>
                <input value={form.location} onChange={(e) => setField('location', e.target.value)} />
              </label>
              <label>
                <span>Email</span>
                <input value={form.email} onChange={(e) => setField('email', e.target.value)} />
              </label>
              <label>
                <span>Phone</span>
                <input value={form.phone} onChange={(e) => setField('phone', e.target.value)} />
              </label>
              <label>
                <span>LinkedIn URL</span>
                <input value={form.linkedin} onChange={(e) => setField('linkedin', e.target.value)} />
              </label>
              <label>
                <span>Github URL</span>
                <input value={form.github} onChange={(e) => setField('github', e.target.value)} />
              </label>
              <label className="span-two">
                <span>Skills (comma separated)</span>
                <input
                  value={form.skills.join(', ')}
                  onChange={(e) =>
                    setForm((current) => ({
                      ...current,
                      skills: e.target.value.split(',').map((item) => item.trim()).filter(Boolean),
                    }))
                  }
                />
              </label>
              <label className="span-two">
                <span>Summary</span>
                <textarea value={form.summary} rows={3} onChange={(e) => setField('summary', e.target.value)} />
              </label>
              <label className="span-two">
                <span>Experience</span>
                <textarea value={form.experience} rows={5} onChange={(e) => setField('experience', e.target.value)} />
              </label>
              <label className="span-two">
                <span>Education</span>
                <textarea value={form.education} rows={3} onChange={(e) => setField('education', e.target.value)} />
              </label>
              <label className="span-two">
                <span>Projects / Certifications / Languages</span>
                <input
                  value={form.projects}
                  onChange={(e) => setField('projects', e.target.value)}
                  placeholder="Projects"
                />
                <input
                  value={form.certifications}
                  onChange={(e) => setField('certifications', e.target.value)}
                  placeholder="Certifications"
                />
                <input
                  value={form.languages}
                  onChange={(e) => setField('languages', e.target.value)}
                  placeholder="Languages"
                />
              </label>
            </div>

            <div className="detail-actions">
              <button type="button" className="button button-primary" onClick={() => void download('docx')}>
                Download DOCX
              </button>
              <button type="button" className="button button-secondary" onClick={() => void download('pdf')}>
                Download PDF
              </button>
            </div>
          </fieldset>
        </div>
      </div>
    </section>
  );
}
