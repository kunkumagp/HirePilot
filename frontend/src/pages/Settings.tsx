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
  const [saving, setSaving] = useState(false);

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
    setSaving(true);
    setMessage(null);

    try {
      await api('/settings', {
        method: 'PUT',
        body: JSON.stringify({
          name,
          portfolio_url: portfolioUrl || null,
          skills: skills.split(',').map((item) => item.trim()).filter(Boolean),
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

      setMessage('Settings saved successfully.');
    } catch (cause) {
      setMessage(cause instanceof Error ? cause.message : 'Could not save settings');
    } finally {
      setSaving(false);
    }
  };

  if (!loaded) {
    return <div className="panel"><p className="empty-state">Loading settings…</p></div>;
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h2>Preferences</h2>
          <p className="muted">Control what HirePilot matches and how it reaches you.</p>
        </div>
      </div>

      <div className="settings-grid">
        <div className="panel">
          <div className="panel-head">
            <div>
              <h3>Profile &amp; matching</h3>
              <p className="muted">Used to score jobs and fill your cover letter.</p>
            </div>
          </div>

          <div className="form-grid">
            <label>
              <span>Name</span>
              <input value={name} onChange={(event) => setName(event.target.value)} />
            </label>
            <label>
              <span>Portfolio URL</span>
              <input value={portfolioUrl} onChange={(event) => setPortfolioUrl(event.target.value)} />
            </label>
            <label className="span-two">
              <span>Skills</span>
              <input
                value={skills}
                onChange={(event) => setSkills(event.target.value)}
                placeholder="react, php, laravel"
              />
              <small className="help-text">Separate skills with commas.</small>
            </label>
            <label>
              <span>Minimum score</span>
              <input type="number" value={minScore} onChange={(event) => setMinScore(Number(event.target.value))} />
            </label>
            <label>
              <span>Max job age (days)</span>
              <input type="number" value={maxAgeDays} onChange={(event) => setMaxAgeDays(Number(event.target.value))} />
            </label>
            <label className="span-two">
              <span>Minimum yearly USD salary</span>
              <input type="number" value={minSalary} onChange={(event) => setMinSalary(Number(event.target.value))} />
            </label>
          </div>
        </div>

        <div className="panel">
          <div className="panel-head">
            <div>
              <h3>Notification channels</h3>
              <p className="muted">Choose where your matches are delivered.</p>
            </div>
          </div>

          <div className="channel-list">
            <div className="channel-card">
              <div className="channel-top">
                <div>
                  <strong>◉ Website</strong>
                  <p className="muted">In-app notification centre</p>
                </div>
                <input
                  className="switch"
                  type="checkbox"
                  checked={enabled.inapp}
                  onChange={(event) => setEnabled({ ...enabled, inapp: event.target.checked })}
                />
              </div>
            </div>

            <div className="channel-card">
              <div className="channel-top">
                <div>
                  <strong>✈ Telegram</strong>
                  <p className="muted">Bot API message with apply link</p>
                </div>
                <input
                  className="switch"
                  type="checkbox"
                  checked={enabled.telegram}
                  onChange={(event) => setEnabled({ ...enabled, telegram: event.target.checked })}
                />
              </div>
              {enabled.telegram ? (
                <div className="form-grid">
                  <label>
                    <span>Chat ID</span>
                    <input value={telegramTarget} onChange={(event) => setTelegramTarget(event.target.value)} />
                  </label>
                  <label>
                    <span>Bot token</span>
                    <input type="password" value={botToken} onChange={(event) => setBotToken(event.target.value)} />
                  </label>
                </div>
              ) : null}
            </div>

            <div className="channel-card">
              <div className="channel-top">
                <div>
                  <strong>✉ Email</strong>
                  <p className="muted">SMTP with your own credentials</p>
                </div>
                <input
                  className="switch"
                  type="checkbox"
                  checked={enabled.email}
                  onChange={(event) => setEnabled({ ...enabled, email: event.target.checked })}
                />
              </div>
              {enabled.email ? (
                <div className="form-grid">
                  <label className="span-two">
                    <span>Recipient</span>
                    <input type="email" value={emailTarget} onChange={(event) => setEmailTarget(event.target.value)} />
                  </label>
                  <label>
                    <span>SMTP host</span>
                    <input value={smtpHost} onChange={(event) => setSmtpHost(event.target.value)} />
                  </label>
                  <label>
                    <span>SMTP port</span>
                    <input value={smtpPort} onChange={(event) => setSmtpPort(event.target.value)} />
                  </label>
                  <label>
                    <span>Username</span>
                    <input value={smtpUsername} onChange={(event) => setSmtpUsername(event.target.value)} />
                  </label>
                  <label>
                    <span>Password</span>
                    <input type="password" value={smtpPassword} onChange={(event) => setSmtpPassword(event.target.value)} />
                  </label>
                </div>
              ) : null}
            </div>
          </div>
        </div>
      </div>

      <div className="save-bar">
        {message ? <span className="save-message">{message}</span> : <span />}
        <button type="button" className="button button-primary" disabled={saving} onClick={() => void save()}>
          {saving ? 'Saving…' : 'Save changes'}
        </button>
      </div>
    </section>
  );
}
