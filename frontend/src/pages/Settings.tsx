import { useEffect, useState } from 'react';
import { api } from '../api/client';
import type { Channel, User, UserSettings } from '../types';

interface SettingsResponse {
  user: User;
  settings: UserSettings;
  channels: Channel[];
}

export function Settings() {
  const [loaded, setLoaded] = useState(false);
  const [name, setName] = useState('');
  const [portfolioUrl, setPortfolioUrl] = useState('');
  const [skills, setSkills] = useState('');
  const [minScore, setMinScore] = useState(5);
  const [maxAgeDays, setMaxAgeDays] = useState(14);
  const [minSalary, setMinSalary] = useState(24000);
  const [enabled, setEnabled] = useState({ telegram: false, email: false, inapp: false });
  const [telegramTarget, setTelegramTarget] = useState('');
  const [emailTarget, setEmailTarget] = useState('');
  const [botToken, setBotToken] = useState('');
  const [smtpHost, setSmtpHost] = useState('');
  const [smtpPort, setSmtpPort] = useState('587');
  const [smtpUsername, setSmtpUsername] = useState('');
  const [smtpPassword, setSmtpPassword] = useState('');
  const [message, setMessage] = useState<string | null>(null);

  useEffect(() => {
    void (async () => {
      const data = await api<SettingsResponse>('/settings');
      setName(data.user.name);
      setPortfolioUrl(data.user.portfolio_url ?? '');
      setSkills(data.settings.skills.join(', '));
      setMinScore(data.settings.min_score);
      setMaxAgeDays(data.settings.max_age_days);
      setMinSalary(data.settings.min_salary_usd_yearly);

      data.channels.forEach((channel) => {
        setEnabled((current) => ({ ...current, [channel.type]: channel.enabled }));
        if (channel.type === 'telegram') {
          setTelegramTarget(channel.target ?? '');
        }
        if (channel.type === 'email') {
          setEmailTarget(channel.target ?? '');
        }
      });
      setLoaded(true);
    })();
  }, []);

  const save = async () => {
    setMessage(null);
    await api('/settings', {
      method: 'PUT',
      body: JSON.stringify({
        name,
        portfolio_url: portfolioUrl || null,
        skills: skills
          .split(',')
          .map((item) => item.trim())
          .filter(Boolean),
        min_score: Number(minScore),
        max_age_days: Number(maxAgeDays),
        min_salary_usd_yearly: Number(minSalary),
      }),
    });

    const channels = [
      {
        type: 'telegram',
        enabled: enabled.telegram,
        target: telegramTarget || null,
        credentials: botToken ? { bot_token: botToken } : undefined,
      },
      {
        type: 'email',
        enabled: enabled.email,
        target: emailTarget || null,
        credentials: smtpHost
          ? {
              smtp_host: smtpHost,
              smtp_port: Number(smtpPort),
              smtp_username: smtpUsername,
              smtp_password: smtpPassword,
            }
          : undefined,
      },
      { type: 'inapp', enabled: enabled.inapp, target: null },
    ];

    for (const channel of channels) {
      await api('/channels', { method: 'POST', body: JSON.stringify(channel) });
    }

    setMessage('Settings saved.');
  };

  if (!loaded) {
    return <p className="muted">Loading settings…</p>;
  }

  return (
    <section>
      <h1>Settings</h1>
      <div className="grid">
        <article className="card">
          <h2>Profile & matching</h2>
          <label>Name<input value={name} onChange={(e) => setName(e.target.value)} /></label>
          <label>Portfolio URL<input value={portfolioUrl} onChange={(e) => setPortfolioUrl(e.target.value)} /></label>
          <label>Skills (comma separated)<input value={skills} onChange={(e) => setSkills(e.target.value)} /></label>
          <label>Minimum score<input type="number" value={minScore} onChange={(e) => setMinScore(Number(e.target.value))} /></label>
          <label>Max age (days)<input type="number" value={maxAgeDays} onChange={(e) => setMaxAgeDays(Number(e.target.value))} /></label>
          <label>Minimum USD salary<input type="number" value={minSalary} onChange={(e) => setMinSalary(Number(e.target.value))} /></label>
        </article>

        <article className="card">
          <h2>Notification channels</h2>
          <label>
            <input type="checkbox" checked={enabled.telegram} onChange={(e) => setEnabled({ ...enabled, telegram: e.target.checked })} /> Telegram
          </label>
          <label>Chat ID<input value={telegramTarget} onChange={(e) => setTelegramTarget(e.target.value)} /></label>
          <label>Bot token<input type="password" value={botToken} onChange={(e) => setBotToken(e.target.value)} /></label>
          <label>
            <input type="checkbox" checked={enabled.email} onChange={(e) => setEnabled({ ...enabled, email: e.target.checked })} /> Email
          </label>
          <label>Email address<input type="email" value={emailTarget} onChange={(e) => setEmailTarget(e.target.value)} /></label>
          <label>SMTP host<input value={smtpHost} onChange={(e) => setSmtpHost(e.target.value)} /></label>
          <label>SMTP port<input value={smtpPort} onChange={(e) => setSmtpPort(e.target.value)} /></label>
          <label>SMTP username<input value={smtpUsername} onChange={(e) => setSmtpUsername(e.target.value)} /></label>
          <label>SMTP password<input type="password" value={smtpPassword} onChange={(e) => setSmtpPassword(e.target.value)} /></label>
          <label>
            <input type="checkbox" checked={enabled.inapp} onChange={(e) => setEnabled({ ...enabled, inapp: e.target.checked })} /> Website notifications
          </label>
        </article>
      </div>
      {message ? <p className="success">{message}</p> : null}
      <button type="button" onClick={() => void save()}>Save settings</button>
    </section>
  );
}
