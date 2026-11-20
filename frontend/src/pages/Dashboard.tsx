import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api/client';
import { useAuth } from '../context/AuthContext';
import type { ApiNotification, Job, Paginated } from '../types';

export function Dashboard() {
  const { user } = useAuth();
  const [jobs, setJobs] = useState<Job[]>([]);
  const [unread, setUnread] = useState(0);

  useEffect(() => {
    void (async () => {
      const [jobData, notificationData] = await Promise.all([
        api<Paginated<Job>>('/jobs'),
        api<Paginated<ApiNotification>>('/notifications'),
      ]);
      setJobs(jobData.data);
      setUnread(notificationData.data.filter((item) => item.read_at === null).length);
    })();
  }, []);

  const notified = jobs.filter((job) => job.status === 'notified').length;
  const ready = jobs.filter((job) => job.cover_letter !== null).length;

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h2>Welcome back, {user?.name}</h2>
          <p className="muted">Here is what HirePilot found for you.</p>
        </div>
        <Link to="/settings" className="button button-primary">
          Tune matching rules
        </Link>
      </div>

      <div className="stat-grid">
        <article className="stat-card">
          <span className="stat-icon stat-blue">▤</span>
          <div>
            <p className="stat-label">Matched jobs</p>
            <p className="stat-value">{jobs.length}</p>
          </div>
        </article>
        <article className="stat-card">
          <span className="stat-icon stat-amber">◉</span>
          <div>
            <p className="stat-label">Unread alerts</p>
            <p className="stat-value">{unread}</p>
          </div>
        </article>
        <article className="stat-card">
          <span className="stat-icon stat-green">✓</span>
          <div>
            <p className="stat-label">Notified</p>
            <p className="stat-value">{notified}</p>
          </div>
        </article>
        <article className="stat-card">
          <span className="stat-icon stat-violet">✎</span>
          <div>
            <p className="stat-label">Cover letters ready</p>
            <p className="stat-value">{ready}</p>
          </div>
        </article>
      </div>

      <div className="panel">
        <div className="panel-head">
          <div>
            <h3>Top matches</h3>
            <p className="muted">Highest scoring jobs from your latest run.</p>
          </div>
          <Link to="/jobs" className="link-button">
            View all →
          </Link>
        </div>

        {jobs.length === 0 ? (
          <p className="empty-state">No jobs yet. Run the pipeline to collect your first matches.</p>
        ) : (
          <div className="table-wrap">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Job</th>
                  <th>Score</th>
                  <th>Status</th>
                  <th>Source</th>
                  <th aria-label="Actions" />
                </tr>
              </thead>
              <tbody>
                {jobs.slice(0, 5).map((job) => (
                  <tr key={job.id}>
                    <td>
                      <strong>{job.title}</strong>
                      <span className="cell-sub">{job.company}</span>
                    </td>
                    <td>
                      <span className="score-badge">{job.score}</span>
                    </td>
                    <td>
                      <span className={`status-chip status-${job.status}`}>{job.status}</span>
                    </td>
                    <td className="muted">{job.source}</td>
                    <td>
                      <a className="link-button" href={job.url} target="_blank" rel="noreferrer">
                        Apply ↗
                      </a>
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
