import { useEffect, useState } from 'react';
import { api } from '../api/client';
import type { Job, Paginated } from '../types';

export function Jobs() {
  const [jobs, setJobs] = useState<Job[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    void (async () => {
      try {
        setJobs((await api<Paginated<Job>>('/jobs')).data);
      } catch (cause) {
        setError(cause instanceof Error ? cause.message : 'Could not load jobs');
      }
    })();
  }, []);

  if (error) {
    return <p className="error">{error}</p>;
  }

  return (
    <section>
      <h1>Your matched jobs</h1>
      {jobs.length === 0 ? <p className="muted">No matches yet.</p> : null}
      <div className="list">
        {jobs.map((job) => (
          <article className="card" key={job.id}>
            <div className="row">
              <h2>{job.title}</h2>
              <span className="badge">Score {job.score}</span>
            </div>
            <p className="muted">
              {job.company} · {job.location || 'location unclear'} · {job.source}
            </p>
            <p className="muted">
              {job.salary_unknown
                ? 'Salary not listed'
                : `${job.salary_currency ?? ''} ${job.salary_min ?? '?'} – ${job.salary_max ?? '?'}`}
            </p>
            <p>{job.reasons.join(' · ')}</p>
            <div className="row">
              <a href={job.url} target="_blank" rel="noreferrer">
                Apply
              </a>
              {job.cover_letter ? (
                <button
                  type="button"
                  onClick={() => void navigator.clipboard.writeText(job.cover_letter ?? '')}
                >
                  Copy cover letter
                </button>
              ) : null}
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
