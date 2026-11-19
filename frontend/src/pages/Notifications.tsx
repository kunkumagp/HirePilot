import { useEffect, useState } from 'react';
import { api } from '../api/client';
import type { ApiNotification, Paginated } from '../types';

export function Notifications() {
  const [items, setItems] = useState<ApiNotification[]>([]);

  useEffect(() => {
    void (async () => {
      setItems((await api<Paginated<ApiNotification>>('/notifications')).data);
    })();
  }, []);

  const markRead = async (id: string) => {
    await api(`/notifications/${id}/read`, { method: 'PATCH' });
    setItems((current) =>
      current.map((item) =>
        item.id === id ? { ...item, read_at: new Date().toISOString() } : item,
      ),
    );
  };

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h2>Notification centre</h2>
          <p className="muted">Your latest job matches and ready-made cover letters.</p>
        </div>
        <span className="count-pill">{items.filter((item) => item.read_at === null).length} unread</span>
      </div>

      {items.length === 0 ? (
        <div className="panel">
          <p className="empty-state">Nothing here yet. Run the pipeline to get your first alert.</p>
        </div>
      ) : (
        <div className="notification-list">
          {items.map((item) => (
            <article className={`notification-card ${item.read_at === null ? 'is-unread' : ''}`} key={item.id}>
              <div className="notification-head">
                <span className="notification-dot" />
                <div className="notification-title">
                  <h3>{item.data.title ?? 'Job match'}</h3>
                  <small>{item.created_at ? new Date(item.created_at).toLocaleString() : ''}</small>
                </div>
              </div>
              <pre className="notification-body">{item.data.body ?? ''}</pre>
              <div className="notification-actions">
                {item.data.cover_letter ? (
                  <button
                    type="button"
                    className="button button-secondary button-mini"
                    onClick={() => void navigator.clipboard.writeText(item.data.cover_letter ?? '')}
                  >
                    Copy cover letter
                  </button>
                ) : null}
                {item.read_at === null ? (
                  <button
                    type="button"
                    className="button button-mini"
                    onClick={() => void markRead(item.id)}
                  >
                    Mark as read
                  </button>
                ) : (
                  <span className="read-label">✓ Read</span>
                )}
              </div>
            </article>
          ))}
        </div>
      )}
    </section>
  );
}
