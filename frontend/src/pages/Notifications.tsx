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
      current.map((item) => (item.id === id ? { ...item, read_at: new Date().toISOString() } : item)),
    );
  };

  return (
    <section>
      <h1>Notifications</h1>
      {items.length === 0 ? <p className="muted">Nothing here yet.</p> : null}
      <div className="list">
        {items.map((item) => (
          <article className={`card ${item.read_at ? '' : 'unread'}`} key={item.id}>
            <h2>{item.data.title ?? 'Job match'}</h2>
            <pre className="mono">{item.data.body ?? ''}</pre>
            {item.data.cover_letter ? (
              <button
                type="button"
                onClick={() => void navigator.clipboard.writeText(item.data.cover_letter ?? '')}
              >
                Copy cover letter
              </button>
            ) : null}
            {item.read_at === null ? (
              <button type="button" onClick={() => void markRead(item.id)}>
                Mark as read
              </button>
            ) : (
              <span className="muted">Read</span>
            )}
          </article>
        ))}
      </div>
    </section>
  );
}
