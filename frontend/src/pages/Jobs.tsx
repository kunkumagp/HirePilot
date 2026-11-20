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
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [total, setTotal] = useState(0);
  const [salaryFilter, setSalaryFilter] = useState('all');
  const [scoreFilter, setScoreFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');

  const applyPage = (result: Paginated<Job>, append: boolean) => {
    setJobs((current) =>
      append
        ? [...current, ...result.data.filter((job) => !current.some((item) => item.id === job.id))]
        : result.data,
    );
    setPage(result.meta?.current_page ?? 1);
    setHasMore((result.meta?.last_page ?? 1) > (result.meta?.current_page ?? 1));
    setTotal(result.meta?.total ?? result.data.length);
  };

  useEffect(() => {
    void (async () => {
      try {
        applyPage(await api<Paginated<Job>>('/jobs'), false);
      } catch (cause) {
        setError(cause instanceof Error ? cause.message : 'Could not load jobs');
      }
    })();
  }, []);

  const loadMore = async () => {
    setLoadingMore(true);

    try {
      applyPage(await api<Paginated<Job>>(`/jobs?page=${page + 1}`), true);
    } catch (cause) {
      setNotice(cause instanceof Error ? cause.message : 'Could not load more jobs');
    } finally {
      setLoadingMore(false);
    }
  };

  const salaryValue = (job: Job): number | null => {
    if (job.salary_unknown) {
      return null;
    }

    const raw = job.salary_max ?? job.salary_min;

    if (raw === null) {
      return null;
    }

    const value = Number(raw);

    return Number.isFinite(value) ? value : null;
  };

  const filtered = jobs.filter((job) => {
    const matchesSearch = `${job.title} ${job.company} ${job.source}`
      .toLowerCase()
      .includes(search.toLowerCase());

    const salary = salaryValue(job);
    const isNumericSalary =
      salaryFilter !== 'all' && salaryFilter !== 'listed' && salaryFilter !== 'unknown';
    const matchesSalary =
      salaryFilter === 'all' ||
      (salaryFilter === 'listed' && salary !== null) ||
      (salaryFilter === 'unknown' && salary === null) ||
      (isNumericSalary && salary !== null && salary >= Number(salaryFilter));

    const matchesScore = scoreFilter === 'all' || job.score >= Number(scoreFilter);
    const matchesStatus = statusFilter === 'all' || job.status === statusFilter;

    return matchesSearch && matchesSalary && matchesScore && matchesStatus;
  });

  const resetFilters = () => {
    setSearch('');
    setSalaryFilter('all');
    setScoreFilter('all');
    setStatusFilter('all');
  };

  const hasFilters =
    search !== '' || salaryFilter !== 'all' || scoreFilter !== 'all' || statusFilter !== 'all';

  const runSearch = async () => {
    setRunning(true);
    setNotice(null);

    try {
      const result = await api<{ fetched: number; new: number; matches: number }>('/jobs/run', {
        method: 'POST',
      });
      applyPage(await api<Paginated<Job>>('/jobs'), false);
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
        <button
          type="button"
          className="button button-primary"
          onClick={() => void runSearch()}
          disabled={running}
        >
          {running ? 'Searching…' : 'Run job search'}
        </button>
      </div>

      <div className="filters-bar">
        <input
          className="search-input"
          type="search"
          placeholder="Search jobs or companies…"
          value={search}
          onChange={(event) => setSearch(event.target.value)}
        />
        <select value={salaryFilter} onChange={(event) => setSalaryFilter(event.target.value)}>
          <option value="all">Any salary</option>
          <option value="listed">Salary listed</option>
          <option value="unknown">Salary not listed</option>
          <option value="60000">≥ 60k / year</option>
          <option value="100000">≥ 100k / year</option>
        </select>
        <select value={scoreFilter} onChange={(event) => setScoreFilter(event.target.value)}>
          <option value="all">Any score</option>
          <option value="3">Score ≥ 3</option>
          <option value="6">Score ≥ 6</option>
          <option value="9">Score ≥ 9</option>
        </select>
        <select value={statusFilter} onChange={(event) => setStatusFilter(event.target.value)}>
          <option value="all">Any status</option>
          <option value="new">New</option>
          <option value="notified">Notified</option>
          <option value="rejected">Rejected</option>
        </select>
        {hasFilters ? (
          <button type="button" className="button button-mini" onClick={resetFilters}>
            Reset
          </button>
        ) : null}
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
                      <span className={`score-badge ${job.score >= 3 ? 'score-good' : 'score-low'}`}>
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

      {filtered.length > 0 ? (
        <div className="list-footer">
          <span className="muted">Showing {filtered.length} of {total} matched jobs</span>
          {hasMore ? (
            <button
              type="button"
              className="button button-secondary"
              onClick={() => void loadMore()}
              disabled={loadingMore}
            >
              {loadingMore ? 'Loading…' : 'Load more'}
            </button>
          ) : null}
        </div>
      ) : null}
    </section>
  );
}
