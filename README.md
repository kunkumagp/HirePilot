# HirePilot

**Remote Job Hunter** — ශ්‍රී ලංකාවේ developer කෙනෙකුට ගැළපෙන, USD ගෙවන remote web-developer රස්සා හොයලා දෙන පද්ධතියක්.

මේක **සම්පූර්ණයෙන් rule-based** පද්ධතියක් — කිසිම AI/LLM/ML එකක් භාවිතා කරන්නේ නෑ. හැම තීරණයක්ම config එකේ තියෙන නීති අනුව ගන්නවා. ඒ නිසා ප්‍රතිඵල පැහැදිලියි, වියදම අඩුයි.

**අයදුම් කරන්නේ ඔබමයි** — මේකෙන් forms auto-fill කරන්නේ නෑ, job sites වලට login වෙන්නේ නෑ.

---

## මොකක්ද කරන්නේ?

1. RemoteOK, Remotive, We Work Remotely, Jobicy, Himalayas කියන **නොමිලේ API/RSS මූලාශ්‍ර 5** එකතුවෙන් ජොබ් ගන්නවා.
2. පරණ ජොබ්, නොගැළපෙන title, blacklist වචන, සීමිත රටවල්, අඩු/වෙන මුදල් — ඔක්කොම පෙරහන් කරනවා.
3. Keyword weights අනුව ලකුණු දීලා හොඳම ඒවා තෝරනවා.
4. එක් එක් user තෝරපු ක්‍රමයෙන් දැනුම් දෙනවා: **Telegram / Email / වෙබ් එකේ notification center**.
5. Apply link එකත්, සූදානම් **cover letter** එකත් එක්ක එවනවා.

---

## Tech stack

| කොටස | තාක්ෂණය |
|---|---|
| Backend | PHP 8.4 + Laravel 13, Eloquent, Artisan |
| Frontend | React + Vite + TypeScript (`frontend/`) |
| Backend ↔ Frontend | JSON API + Sanctum (Phase B දී) |
| Database | SQLite (local/Actions) → Postgres (SaaS) |
| Notifications | Telegram Bot API, SMTP, Laravel database notifications |
| Tests | PHPUnit + saved fixtures |
| Scheduling | GitHub Actions (cron) + `state` branch |

---

## Folder structure

```
HirePilot/
├── backend/     → Laravel app (API, job engine, tests, config/jobhunter.php)
├── frontend/    → React + Vite + TypeScript SPA
├── docs/PROJECT_BOOK.md   → ඕනෑම කෙනෙකුට පෙන්වන්න පුළුවන් ව්‍යාපෘති පොත
├── PLAN.md      → සවිස්තර තාක්ෂණික සැලැස්ම
├── AGENTS.md    → coding agent නීති සහ commands
└── .github/workflows/run.yml
```

---

## Local setup

**අවශ්‍ය දේවල්:** PHP 8.3+ (මෙහි 8.4), Composer 2, Node.js 20+, npm.

### Backend

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DefaultUserSeeder
php artisan test --compact
```

### Frontend

```sh
cd frontend
npm install
npm run dev      # වෙබ් එක http://localhost:5173 එකේ ධාවනය වෙනවා
npm run build    # production build
```

API එක වෙන URL එකක් නම් `frontend/.env.example` එක `frontend/.env` එකට copy කරලා `VITE_API_BASE_URL` වෙනස් කරන්න.

**Theme:** dark එක තමයි default එක. Topbar එකේ (හෝ login/register පිටුවේ) ☀ / ☾ button එකෙන් light/dark අතර මාරු වෙන්න පුළුවන් — තේරීම `localStorage` එකේ save වෙනවා.

### React SPA

`npm run dev` එකෙන් පස්සේ **/register** එකෙන් account එකක් හදලා **/login** වෙන්න. Login එකේදී ලැබෙන token එක browser එකේ localStorage එකේ තියෙනවා; API requests එක්ක `Bearer` header එකක් විදිහට යනවා. Pages: Dashboard, Jobs, Notifications (unread badge එක්ක), Settings, සහ Documents. Settings එකේ එක එක user කෙනෙක්ට තමන්ගේම **"Job title / role keywords"** free-text එක (උදා: `React Developer, HubSpot Marketing Manager`) — මේක තමයි වැඩිම ලකුණු දෙන signal එක. ඒකට අමතරව optional **job category සහ sub-category** dropdown දෙකක් (18 කාණ්ඩයක් / 96 උප-කාණ්ඩයක්) සහ custom keywords ද තියෙනවා, **skills**, **experience level / minimum years**, **work arrangement** (Remote/Hybrid/On-site), **preferred countries** සහ **salary currency** දාගන්න පුළුවන්. SMTP සහ bot token වගේ credentials user ගේම ඒවා ලෙස encrypted ලෙස store වෙනවා. Options ලැබෙන්නේ config එකෙන් (`/api/settings/options`) — frontend එකේ hardcode නෑ.

### Documents / ATS CV builder

**/documents** පිටුවෙන් ඔබේ CV එක **PDF** ලෙස upload කරන්න පුළුවන් (උපරිම 10 MB). Upload කරද්දීම server එකෙන් text එක අරගෙන **rule-based** ආකාරයට මේ දේවල් උපුටා ගන්නවා: නම, ඊමේල්, දුරකථනය, Summary, Skills, Experience සහ Education. කිසිම AI/LLM එකක් යොදන්නේ නෑ.

ඊට පස්සේ ඒ දත්ත ටික edit කරලා, **single-column, ATS-friendly CV** එකක් හදලා **DOCX** සහ **PDF** විදිහට download කරන්න පුළුවන්. Upload කරපු මුල් PDF එක සහ උපුටාගත්ත දත්ත එක user කෙනෙකුටම පමණයි පෙනෙන්නේ (tenant isolation). CV template එක තවම සරල default එකක් — ඔබට ගැළපෙන design එකක් දුන්නොත් ඊට අනුව වෙනස් කරන්න පුළුවන්.

### එක command එකකින් start / stop

Repo root එකේ ඉඳන් (PowerShell හෝ File Explorer එකෙන් double-click):

```powershell
.\start.cmd          # backend + frontend දෙකම පටන් ගන්න
.\stop.cmd           # දෙකම නවත්වන්න
.\dev.cmd status     # තත්ත්වය බලන්න
```

`start.cmd` එකෙන් backend එක `http://127.0.0.1:8000` වලත්, frontend එක `http://localhost:5173` වලත් ධාවනය වෙනවා. PID ටික `.runtime/` එකේ තියෙනවා (git-ignored). **PowerShell execution policy වෙනස් කරන්න ඕනේ නෑ** — `.cmd` wrapper එක ඒක bypass කරනවා.

අතින්ම ධාවනය කරන්න ඕනේ නම් පහළ commands ටිකත් පාවිච්චි කරන්න පුළුවන් (උඩ තියෙනවා).

### Dry run (කිසිම notification එකක් යවන්නේ නෑ, DB එකට ලියන්නෙත් නෑ)

```sh
cd backend
php artisan jobhunter:run --dry-run
```

UI එකෙන්ම ධාවනය කරන්න ඕනේ නම් **Jobs → "Run job search"** button එක ඔබන්න (ක්ෂණිකව API එක හරහා එම pipeline එකම ක්‍රියාත්මක වෙනවා).

CLI එකෙන් live run එකක් සඳහා `--dry-run` අයින් කරන්න. එතකොට ජොබ් `job_postings` එකට save වෙනවා. Sources එක් වරක් විතරක් fetch කරලා, ඊට පස්සේ සියලුම **active users** ගේම නීති අනුව ජොබ් ගළපා දැනුම් දෙනවා — එක user කෙනෙකුගේ settings අනිත් අයගේ ප්‍රතිඵල වලට බලපාන්නේ නෑ.

---

## Telegram bot හදන හැටි

1. Telegram එකේ **@BotFather** හොයාගෙන `/newbot` කමාන්ඩ් එක දීලා bot එකක් හදන්න. ඒකෙන් ලැබෙන token එක **කිසිම තැනක commit කරන්න එපා** — `backend/.env` එකේ `TELEGRAM_BOT_TOKEN` ලෙස දාන්න.
2. හදපු bot එකට ඕනෑම පණිවිඩයක් යවන්න.
3. `https://api.telegram.org/bot<TOKEN>/getUpdates` එක බ්‍රවුසරේ දාලා `"chat":{"id":...}` අගය ගන්න — ඒක `TELEGRAM_CHAT_ID`.
4. Live run එකකදී පණිවිඩ යන්නේ එක user කෙනෙක්ගේ `user_channels` එකේ telegram channel එක **enabled** උන විට විතරයි (SaaS UI එකෙන් සකසන්න පුළුවන් වෙන්නේ Milestone 7 දී).

---

## Email දැනුම්දීම

- එක user කෙනෙක්ගේම SMTP credentials (host, port, username, password, encryption) එයාගේ `user_channels.credentials` එකේ **encrypted** ලෙස තියෙනවා.
- Local සඳහා `.env` එකේ `MAIL_*` අගයන් fallback එකක් විදිහට වැඩ කරනවා.

---

## Config එක වෙනස් කරන්න

සියලුම වෙනස් කියවිය හැකි අගයන් එකම තැනක: **`backend/config/jobhunter.php`**

- `filters.max_age_days` — ජොබ් එකක් කී දවසක් දක්වා "අලුත්" ලෙස ගන්නවාද (default 14).
- `filters.title_include` / `exclude_words` — title එකේ තියෙන්න ඕනේ / නොතියෙන්න ඕනේ වචන.
- `filters.min_salary_usd_yearly` — අවම වැටුප.
- `filters.salary` — hourly/monthly අගයන් yearly එකට හරවන සීමා.
- `scoring.weights` / `min_score` — ලකුණු සහ අවම ලකුණු.
- `sources` — එක් එක් source එක enabled ද කියන එක සහ adapter class එක.
- `notify` — එක run එකකට උපරිම දැනුම්දීම් ගාණ සහ retry/backoff.

කිසිම අගයක් code එකේ hardcode කරලා නෑ.

---

## GitHub Actions සැකසුම

- Repo එක **private** එකක් විය යුතුයි.
- Workflow එක `dev` branch එකෙන් හැම **විනාඩි 30 කට වරක්** ධාවනය වෙනවා; `workflow_dispatch` එකෙන් අතින්ත් පුළුවන්.
- `concurrency` group එක නිසා එකවර run දෙකක් එකම database එක ලියන්නේ නෑ.
- Run එකක් පටන් ගන්නකොට `backend/database/database.sqlite` එක **`state` branch** එකෙන් ගන්නවා; ඉවර වෙලා ආයෙ ඒ branch එකටම commit කරනවා. (Actions cache එක මැකී යන්න පුළුවන් නිසා branch එක තෝරගත්තා.)
- **Repo secrets** එකතු කරන්න:
  - `APP_KEY` — `php artisan key:generate --show` එකෙන් ලබාගත් අගය (ස්ථිරව තියන්න, නැත්නම් encrypt කරපු credentials කියවන්න බැහැ).
  - `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`
  - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` (අවශ්‍ය නම් පමණයි).

කිසිම secret එකක් මේ පොතේ හෝ repo එකේ නෑ.

---

## SaaS deployment සඳහා සූදානම

Secrets කිසිවක් repo එකේ නෑ — හැම අගයම server එකේ `.env` එකේ විතරයි. `backend/.env.production.example` එක ආරම්භක ලැයිස්තුව විදිහට පාවිච්චි කරන්න.

### 1. Backend environment එක

```sh
cd backend
cp .env.production.example .env
php artisan key:generate --show     # ලැබෙන APP_KEY එක .env එකට දාන්න (ස්ථිරව තබාගන්න)
```

- `DB_CONNECTION=pgsql` සහ `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` සකසන්න. Schema එක DB-specific SQL නැතුව ලියලා තියෙනවා.
- `QUEUE_CONNECTION=database` සහ **`JOBHUNTER_USE_QUEUE=true`** — එතකොට `DispatchUserNotifications` job එක notification බාර ගන්නවා.
- `FRONTEND_ORIGINS` එකට ඔබේ frontend domain එක දාන්න (අලුත් origin එක එකතු කරද්දී backend එක restart කරන්න ඕනේ — config cache තියෙන නිසා).
- `TELEGRAM_BOT_TOKEN` / `TELEGRAM_CHAT_ID` අමතර නැම්මක් නම් හිස්ව තියන්න පුළුවන්.

### 2. Dependencies සහ cache

```sh
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Config එකේ යමක් වෙනස් කළොත් `php artisan config:clear` කරලා ආයෙ cache කරන්න ඕනේ.

### 3. Queue worker

`deploy/supervisor.conf.example` එක ඔබේ Supervisor එකට copy කරලා path ටික සකසන්න:

```sh
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status hirepilot-worker:*
```

### 4. Scheduler (cron)

`deploy/crontab.example` එක අනුව හැම මිනිත්තුවකම `php artisan schedule:run` ධාවනය වෙන්න දාන්න. සැබෑ වැඩේ `backend/routes/console.php` එකේ තියෙන විනාඩු-30 schedule එකෙන් වෙනවා.

⚠️ **GitHub Actions එකත් එකම database එක ලියනවා නම් එක් ස්ථානයක් පමණක් ධාවනය කරන්න** — නැත්නම් දෙපාරක් ජොබ් එකතු වෙන්න පුළුවන්.

### 5. Frontend

```sh
cd frontend
npm ci
VITE_API_BASE_URL=https://api.yourdomain.com/api npm run build
```

හදුණු `frontend/dist` එක static hosting එකක (Nginx, Netlify, Vercel, Cloudflare Pages ආදිය) දාන්න. SPA එකේ `/jobs/123` වගේ URL එකක් directly ඇහුණොත් `index.html` එකට fallback කරන්න ඕනේ.

### 6. Plans සහ ඊළඟ පියවර

`users.plan` එක `free` හෝ `pro`; සීමා `config/jobhunter.php` එකේ `plans` යටතේ. **Payment/billing integrate කරලා නෑ** — ඒක පසුකාලීන වැඩක්.

පළමු deploy එකේදී මේ ටික පරීක්ෂා කරන්න: login/register, `/api/jobs/run`, Telegram/Email channel එකකින් සැබෑ notification එකක්, සහ scheduler එකෙන් විනාඩි 30කට පස්සේ අලුත් ජොබ් එනවද කියන එක.

---

## සීමා

- AI/LLM/ML නෑ.
- Auto-submission නෑ.
- LinkedIn/Indeed වගේ bot තහනම් කරපු sites scrape කරන්නේ නෑ — නිල API/RSS විතරයි.
- Attribution: RemoteOK/Jobicy එකේ මුල් job URL එකටම link කරනවා.

---

## වැඩි විස්තර

- **`docs/PROJECT_BOOK.md`** — investors, developers, සහ interviews සඳහා සම්පූර්ණ ව්‍යාපෘති පොත (රහස් දත්ත නෑ).
- **`PLAN.md`** — සවිස්තර සැලැස්ම සහ තීරණ.
- **`AGENTS.md`** — coding agent නීති සහ commands.
