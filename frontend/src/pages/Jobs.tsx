import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';
import type { Job, Paginated } from '../types';

export function Jobs() {
  const [jobs, setJobs] = useState<Job[]>([]);
  const [search, setSearch] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [running, setRunning] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);

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

  const runSearch = async () => {
    setRunning(true);
    setNotice(null);

    try {
      const result = await api<{ fetched: number; new: number; matches: number }>('/jobs/run', {
        method: 'POST',
      });
      setJobs((await api<Paginated<Job>>('/jobs')).data);
      setNotice(
        `Search complete — ${result.fetched} fetched from sources, ${result.new} new matches for you.`,
      );
    } catch (cause) {
      setNotice(cause instanceof Error ? cause.message : 'Could not run the job search');
    } finally {
      setRunning(false);
    }
  };

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
        <div className="heading-actions">
          <input
            className="search-input"
            type="search"
            placeholder="Search jobs or companies…"
            value={search}
            onChange={(event) => setSearch(event.target.value)}
          />
          <button type="button" className="button button-primary" onClick={() => void runSearch()} disabled={running}>
            {running ? 'Searching…' : 'Run job search'}
          </button>
        </div>
      </div>

      {notice ? <p className="save-message">{notice}</p> : null}

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
                      <Link className="job-title-link" to={`/jobs/${job.id}`}>
                        {job.title}
                      </Link>
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
                        <Link className="button button-secondary button-mini" to={`/jobs/${job.id}`}>
                          Details
                        </Link>
                        <a className="link-button" href={job.url} target="_blank" rel="noreferrer">
                          Apply ↗
                        </a>
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
