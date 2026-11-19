import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api } from '../api/client';
import type { JobDetail } from '../types';

export function JobDetailPage() {
  const { id } = useParams();
  const [job, setJob] = useState<JobDetail | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [regenerating, setRegenerating] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);

  useEffect(() => {
    void (async () => {
      try {
        setJob(await api<JobDetail>(`/jobs/${id}`));
      } catch (cause) {
        setError(cause instanceof Error ? cause.message : 'Could not load job details');
      }
    })();
  }, [id]);

  const regenerate = async () => {
    setRegenerating(true);
    setNotice(null);

    try {
      const result = await api<{ cover_letter: string }>(`/jobs/${id}/cover-letter`, {
        method: 'POST',
      });
      setJob((current) => (current ? { ...current, cover_letter: result.cover_letter } : current));
      setNotice('Cover letter rebuilt from your current profile.');
    } catch (cause) {
      setNotice(cause instanceof Error ? cause.message : 'Could not rebuild cover letter');
    } finally {
      setRegenerating(false);
    }
  };

  if (error) {
    return <div className="alert alert-error">{error}</div>;
  }

  if (!job) {
    return <div className="panel"><p className="empty-state">Loading job details…</p></div>;
  }

  const salary = job.salary_unknown
    ? 'Salary not listed'
    : `${job.salary_currency ?? ''} ${job.salary_min ?? '?'} – ${job.salary_max ?? '?'}`;

  return (
    <section className="page">
      <Link to="/jobs" className="link-button">← Back to jobs</Link>

      <div className="panel detail-panel">
        <div className="detail-header">
          <div>
            <p className="eyebrow">{job.company} · {job.source}</p>
            <h2>{job.title}</h2>
          </div>
          <div className="detail-score">
            <span className={`score-badge ${job.score >= 6 ? 'score-good' : 'score-low'}`}>{job.score}</span>
            <span className={`status-chip status-${job.status}`}>{job.status}</span>
          </div>
        </div>

        <div className="detail-meta">
          <span>🌍 {job.location || 'Location unclear'}</span>
          <span>💰 {salary}</span>
          <span>🕒 {job.posted_at ? new Date(job.posted_at).toLocaleDateString() : 'Post date unknown'}</span>
        </div>

        <div className="detail-actions">
          <a className="button button-primary" href={job.url} target="_blank" rel="noreferrer">
            Apply for this job ↗
          </a>
          <button type="button" className="button button-secondary" onClick={() => void regenerate()} disabled={regenerating}>
            {regenerating ? 'Rebuilding…' : 'Re-create cover letter'}
          </button>
          {notice ? <span className="save-message">{notice}</span> : null}
        </div>

        <div className="detail-section">
          <h3>Full job description</h3>
          <div className="job-description">
            {(job.description || 'No description provided by the source.')
              .split(/\n\s*\n/)
              .map((paragraph) => paragraph.trim())
              .filter(Boolean)
              .map((paragraph, index) => <p key={index}>{paragraph}</p>)}
          </div>
        </div>

        {job.tags.length > 0 ? (
          <div className="detail-section">
            <h3>Tags</h3>
            <div className="tag-row">
              {job.tags.map((tag) => <span className="tag-chip" key={tag}>{tag}</span>)}
            </div>
          </div>
        ) : null}

        <div className="detail-section">
          <h3>Why it matched</h3>
          {job.reasons.length === 0 ? (
            <p className="muted">No scoring reasons recorded.</p>
          ) : (
            <ul className="reasons-list">
              {job.reasons.map((reason) => <li key={reason}>{reason}</li>)}
            </ul>
          )}
        </div>

        <div className="detail-section">
          <div className="section-head">
            <h3>Cover letter</h3>
            <button
              type="button"
              className="button button-mini"
              onClick={() => { if (job.cover_letter) { void navigator.clipboard.writeText(job.cover_letter); } }}
            >
              Copy
            </button>
          </div>
          <pre className="cover-letter">{job.cover_letter ?? 'No cover letter yet. Use “Re-create cover letter” to build one.'}</pre>
          <p className="help-text">
            The letter is rebuilt from your current profile and skills — no AI is used.
          </p>
        </div>
      </div>
    </section>
  );
}
