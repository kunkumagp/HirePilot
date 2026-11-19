import { useEffect, useState } from 'react';
import { api } from '../api/client';
import type { Job, Paginated } from '../types';

export function Jobs() {
  const [jobs, setJobs] = useState<Job[]>([]);
  const [search, setSearch] = useState('');
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

  const filtered = jobs.filter((job) =>
    `${job.title} ${job.company} ${job.source}`.toLowerCase().includes(search.toLowerCase()),
  );

  if (error) {
    return <div className="alert alert-error">{error}</div>;
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h2>Your matched jobs</h2>
          <p className="muted">Scored, filtered and ranked by your rules.</p>
        </div>
        <input
          className="search-input"
          type="search"
          placeholder="Search jobs or companies…"
          value={search}
          onChange={(event) => setSearch(event.target.value)}
        />
      </div>

      <div className="panel">
        {filtered.length === 0 ? (
          <p className="empty-state">No jobs match this search.</p>
        ) : (
          <div className="table-wrap">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Job</th>
                  <th>Location</th>
                  <th>Salary</th>
                  <th>Score</th>
                  <th>Status</th>
                  <th aria-label="Actions" />
                </tr>
              </thead>
              <tbody>
                {filtered.map((job) => (
                  <tr key={job.id}>
                    <td className="job-cell">
                      <strong>{job.title}</strong>
                      <span className="cell-sub">{job.company} · {job.source}</span>
                      {job.reasons.length > 0 ? (
                        <span className="cell-reasons">{job.reasons.join(' · ')}</span>
                      ) : null}
                    </td>
                    <td className="muted">{job.location || 'Unclear'}</td>
                    <td className="muted">
                      {job.salary_unknown
                        ? 'Not listed'
                        : `${job.salary_currency ?? ''} ${job.salary_min ?? '?'}–${job.salary_max ?? '?'}`}
                    </td>
                    <td>
                      <span className={`score-badge ${job.score >= 5 ? 'score-good' : 'score-low'}`}>
                        {job.score}
                      </span>
                    </td>
                    <td>
                      <span className={`status-chip status-${job.status}`}>{job.status}</span>
                    </td>
                    <td>
                      <div className="row-actions">
                        <a className="link-button" href={job.url} target="_blank" rel="noreferrer">
                          Apply ↗
                        </a>
                        {job.cover_letter ? (
                          <button
                            type="button"
                            className="button button-mini"
                            onClick={() => void navigator.clipboard.writeText(job.cover_letter ?? '')}
                          >
                            Copy letter
                          </button>
                        ) : null}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </section>
  );
}
