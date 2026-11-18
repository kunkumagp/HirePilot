import { useEffect, useState } from 'react';
import { api } from '../api/client';
import { useAuth } from '../context/AuthContext';
import type { ApiNotification, Job, Paginated } from '../types';

export function Dashboard() {
  const { user } = useAuth();
  const [stats, setStats] = useState({ jobs: 0, unread: 0 });

  useEffect(() => {
    void (async () => {
      const [jobs, notifications] = await Promise.all([
        api<Paginated<Job>>('/jobs'),
        api<Paginated<ApiNotification>>('/notifications'),
      ]);
      setStats({
        jobs: jobs.data.length,
        unread: notifications.data.filter((item) => item.read_at === null).length,
      });
    })();
  }, []);

  return (
    <section>
      <h1>Dashboard</h1>
      <p className="muted">Welcome back, {user?.name}.</p>
      <div className="grid">
        <article className="card">
          <h2>Matched jobs</h2>
          <p className="big">{stats.jobs}</p>
          <p className="muted">Jobs scored and stored for your account.</p>
        </article>
        <article className="card">
          <h2>Unread notifications</h2>
          <p className="big">{stats.unread}</p>
          <p className="muted">Open the notification centre to read them.</p>
        </article>
      </div>
    </section>
  );
}
