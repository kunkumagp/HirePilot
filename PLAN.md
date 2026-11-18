# Remote Job Hunter — සම්පූර්ණ ව්‍යාපෘති සැලැස්ම (Laravel / Multi-user Edition)

> මෙය මුල් `PLAN.md` එකේ සංශෝධිත හා සම්පූර්ණ පිටපතයි. මෙහි සඳහන් තීරණ සියල්ල ගරු කරන්න. **Code ලියන්න පෙර අවසන් සැලැස්ම මෙයයි.** සියලු පැහැදිලි කිරීම් සිංහලෙන්; code identifiers, cover letter සහ Telegram/Email පණිවිඩ ඉංග්‍රීසියෙන්.

---

## 0. තීරණ ලේඛනය (Decision log)

| මාතෘකාව | අවසන් තීරණය |
|---|---|
| Backend | **සම්පූර්ණයෙන් Laravel (PHP)** — Python නෑ, hybrid නෑ |
| PHP පරිචිතත්වය | "ටිකක් දන්නවා" — පියවරෙන් පියවර විස්තර සහිතව ලියන්න |
| Frontend | **React** (Inertia.js + Vite + TypeScript, එකම repo එකේ) |
| Auth | **Email + password** (Laravel Breeze / Inertia stack), sessions |
| Login පසු අතුරු මුහුණත | Dashboard, Jobs, Settings, Notifications පිටු |
| දැනුම්දීම් නාලිකා | **Telegram, Email, in-app (වෙබ් එකේ notification center)** — එක් එක් user තෝරාගන්නවා |
| Email යැවීම | **Per-user SMTP** credentials (encrypted) + local `.env` fallback |
| System notification | වෙබ් එකේම notification center (database notifications); Web push පසුකාලීනව විකල්පයක් විතරයි |
| Multi-user | **Schema දැන්ම** හදන්න; මුල් milestones වල default user කෙනෙකින් local ධාවනය; account UI Phase B දී |
| වියදම | Phase A (local) `$0`; Phase C (SaaS) දී අවශ්‍ය පරිදි අඩු මිලක් ගෙවීමට අවසර ඇත |
| State persistence | GitHub Actions වල **වෙනම `state` branch** එකක් + concurrency group |
| Config | Laravel එකේ **`config/jobhunter.php`** (මුල් `config.yaml` වෙනුවට) + per-user DB overrides |
| Repo | දැන් සිටින `HirePilot` repo එකම project root; **private repo** |
| භාෂා | ලේඛන (README/AGENTS/PLAN) සිංහලෙන්; cover letter සහ පණිවිඩ ඉංග්‍රීසියෙන් |
| Tests | PHPUnit (හෝ Pest) + saved fixtures; live network නෑ |

---

## 1. අරමුණ (Goal)

නොමිලේ, **rule-based (කිසිම AI/LLM/ML නැති)** පද්ධතියක් ගොඩනගන්න. එය:

1. නොමිලේ ප්‍රසිද්ධ API/RSS මූලාශ්‍ර වලින් remote web developer රස්සා ගෙන එයි.
2. ශ්‍රී ලංකාවේ අයට විවෘත සහ USD ගෙවන ජොබ් පමණක් තබාගනී.
3. වින්‍යාසගත කළ හැකි keyword නීති වලින් ලකුණු දී පෙළ ගස්වයි.
4. හොඳම අලුත් ජොබ් එක් එක් **user තෝරපු නාලිකාවලින්** (Telegram / Email / වෙබ් එකේ notification) එවයි — apply link එකත්, template එකෙන් හදපු cover letter එකත් එක්ක.
5. පසුකාලීනව **ඕනෑම කෙනෙකුට account එකක් හදලා භාවිතා කරන්න පුළුවන් SaaS** එකක් බවට පත් වෙන්න හැඩරයක් දැන්ම තියෙනවා.

**වැදගත්:** අයදුම් කිරීම පාවිච්චි කරන කෙනාම එක ක්ලික් එකකින් කරන්න ඕනේ. **Auto-submission නෑ** (Greenhouse, Lever, LinkedIn වගේ තැන් වලට අවසර නෑ, ban වෙන්න පුළුවන්).

---

## 2. සීමාවන් (Hard constraints)

- **Phase A (local):** වියදම `$0` — නොමිලේ tools/APIs විතරයි. **Phase C (SaaS):** අවශ්‍ය වුණොත් අඩු මිලේ hosting/domain ගෙවන්න පුළුවන් (user අවසරය ඇත).
- **කිසිම AI/LLM/ML API එකක් හෝ model එකක් නෑ.** සියලු තීරණ keyword/rule මත පදනම් වෙනවා.
- **Backend:** PHP 8.3+ සහ Laravel (install කරන අවස්ථාවේ latest stable / LTS — 12.x හෝ ඊට පසු). Composer dependencies: `laravel/framework`, HTTP සඳහා `guzzlehttp/guzzle` (හෝ Laravel `Http` facade එක පමණයි), RSS සඳහා `simplepie/simplepie` (හෝ `laminas/laminas-feed`). Dev: `phpunit/phpunit`. **අලුත් dependency එකක් එකතු කරන්න කලින් අනිවාර්යයෙන් අහන්න.**
- **Frontend:** React + Vite + TypeScript (npm dependencies එකතු කිරීමේදීත් සාකච්ඡා කරන්න).
- **Storage:** Phase A සහ GitHub Actions වල **SQLite** (එක file එකක්, `database/database.sqlite`). Phase C දී **Postgres** (Neon/Supabase වගේ) වලට මාරු වෙන්න හැඩ කරන්න — schema එක portable ලෙස ලියන්න, DB-specific SQL වළකින්න.
- **මූලාශ්‍ර:** නිල API/RSS විතරයි. Scraping තහනම් කරලා තියෙන sites නෑ. එක් එක් source එකේ rate limit සහ attribution නීති රකින්න. සියලු requests වල descriptive `User-Agent` එකක් දාන්න. **RemoteOK එකේ ජොබ් URL එකට link back කරන්න ඕනේ.**
- **Secrets:** හැමතැනම environment variables / `.env` (කිසිම එකක් commit කරන්න එපා). `.env.example` පමණයි commit කරන්නේ.
- **Repo:** private එකක් විය යුතුයි (Actions secrets සහ state branch ආරක්ෂාව සඳහා).
- **Type hints / strict types:** සියලු PHP classes වල `declare(strict_types=1);`, typed properties, typed params/returns. කුඩා, කියවන්න පහසු functions.

---

## 3. ඉහළ මට්ටමේ ගෘහ නිර්මාණය (Architecture)

මුල් සැලැස්මේ තිබුණේ `fetch → filter → score → store → notify` කියන එක user එක්කෙනෙක්ට. Multi-user නිසා දැන් මෙහෙමයි:

```
1. fetch_all_sources()        → එක් එක් source එකෙන් එක් වරක් විතරක් ගන්නවා (rate limits රකින්න)
                                 → global `job_postings` table එකට upsert කරනවා (dedupe සහිතව)
2. for each active user:
     load settings (defaults + user overrides)
     → filter pipeline (freshness, title, blacklist, location, salary)
     → scoring + reasons
     → `user_jobs` table එකට upsert (එක user කෙනෙක්ගේම state)
     → ඉහළම ලකුණු ඇති, තවම දැනුම් නොදුන්න ජොබ් තෝරන්න (max_notifications_per_run)
     → cover letter හදන්න (user profile එක අනුව)
     → notification dispatcher → එක user ගේ තෝරපු channels වලට විතරක් යවන්න
     → සාර්ථක වුණු ඒවා විතරක් "notified" කියලා mark කරන්න
3. log per-source + per-user counts (fetched / after filters / new / notified)
```

**ඇයි මෙහෙමද?** එකම API call එකකින් ගත්තු ජොබ් ටික සියලුම user ලට නැවත භාවිතා කරන්න පුළුවන්. User ගේ පෞද්ගලික අදහස් (score, status, cover letter) වෙනම table එකක තියෙන නිසා කාගෙවත් දැනුම්දීම් එකිනෙක පැටලෙන්නේ නෑ.

---

## 4. ව්‍යාපෘති ව්‍යුහය (Project structure)

සියල්ල දැනට සිටින `C:\xampp-new\htdocs\projects\HirePilot` (git repo root) එකේම හදන්න:

```
HirePilot/
├── README.md                       # සිංහල setup + tune කරන ආකාරය
├── AGENTS.md                       # සිංහල නීති (§2 සාරාංශය) + test/dry-run commands
├── PLAN.md                         # මෙම ලේඛනය
├── composer.json
├── package.json
├── vite.config.ts
├── tsconfig.json
├── .env.example
├── .github/workflows/run.yml        # cron + workflow_dispatch + state branch
├── config/jobhunter.php             # සියලු default අගයන් (මුල් config.yaml වෙනුවට)
├── database/
│   ├── migrations/                  # users, jobs, user_jobs, user_channels, notifications, user_settings
│   ├── factories/
│   └── seeders/DefaultUserSeeder.php
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── JobPosting.php           # global job_postings table එකට (Laravel queue `jobs` එක්ක නොපැටලෙන්න)
│   │   ├── UserJob.php
│   │   ├── UserSetting.php
│   │   ├── UserChannel.php
│   │   └── NotificationDelivery.php # audit log (standard `notifications` එක in-app සඳහා වෙන් කරලා)
│   ├── DTO/JobData.php              # readonly DTO (මුල් Job dataclass එකට සමාන)
│   ├── Services/
│   │   ├── Sources/
│   │   │   ├── Source.php           # interface: name(), fetch(): array — කවදාවත් throw කරන්නේ නෑ
│   │   │   ├── RemoteOkSource.php
│   │   │   ├── RemotiveSource.php
│   │   │   ├── WeWorkRemotelySource.php
│   │   │   ├── JobicySource.php
│   │   │   └── HimalayasSource.php
│   │   ├── FetchService.php         # සියලු sources එකතු කරලා global jobs upsert කරනවා
│   │   ├── FilterPipeline.php
│   │   ├── ScoringService.php
│   │   ├── DedupeService.php
│   │   ├── CoverLetterService.php
│   │   ├── NotificationDispatcher.php
│   │   └── Notifiers/
│   │       ├── Notifier.php         # interface: send(User $u, array $items): SendResult
│   │       ├── TelegramNotifier.php
│   │       ├── EmailNotifier.php
│   │       └── InAppNotifier.php
│   ├── Jobs/                        # queued jobs (Phase C): FetchJobs, DispatchNotifications
│   ├── Http/Controllers/            # Inertia controllers: Dashboard, JobController, SettingsController, NotificationController
│   ├── Console/Commands/JobHunterRun.php   # php artisan jobhunter:run [--dry-run]
│   └── Providers/JobHunterServiceProvider.php
├── resources/
│   ├── views/cover_letter.md.tpl    # {company}, {job_title}, {matched_skills}, {my_name}, {portfolio_url}
│   ├── js/
│   │   ├── app.tsx                  # Inertia bootstrap
│   │   ├── Pages/Auth/Login.tsx, Register.tsx
│   │   ├── Pages/Dashboard.tsx
│   │   ├── Pages/Jobs/Index.tsx
│   │   ├── Pages/Settings/Profile.tsx, Filters.tsx, Channels.tsx
│   │   └── Pages/Notifications/Index.tsx
│   └── css/app.css
├── routes/web.php
├── storage/app/fixtures/            # saved sample API/RSS responses (tests වලට විතරයි)
└── tests/
    ├── Unit/Sources/                # fixture එකෙන් parse කරනවා, live network නෑ
    ├── Unit/FilterPipelineTest.php
    ├── Unit/ScoringServiceTest.php
    ├── Unit/DedupeServiceTest.php
    ├── Feature/NotificationDispatcherTest.php   # mock HTTP / fake mail
    └── Feature/AuthTest.php
```

**අලුත් source එකක් එකතු කරන්නේ කොහොමද?** අලුත් `app/Services/Sources/XxxSource.php` එකක් හදලා, `config/jobhunter.php` එකේ `sources` යටතේ එක line එකක් එකතු කරන්න. වෙන කිසිම තැනක වෙනස් කරන්න ඕනේ නෑ.

---

## 5. Data model (multi-user schema — දැන්ම හදන්න)

| Table | වැදගත් columns | සටහන |
|---|---|---|
| `users` | id, name, email (unique), password, portfolio_url, email_verified_at, is_active, created_at, updated_at | Laravel default + portfolio_url, is_active |
| `user_settings` | user_id (PK/FK), skills (json), title_include (json), exclude_words (json), location_accept (json), location_reject (json), max_age_days, min_salary_usd_yearly, allow_unknown_salary (bool), min_score, weights (json), max_notifications_per_run | `config/jobhunter.php` defaults එකෙන් seed වෙනවා; UI එකෙන් override කරන්න පුළුවන් |
| `user_channels` | id, user_id, type (`telegram`/`email`/`inapp`), enabled, target (chat_id හෝ email), credentials (encrypted json), verified_at | එක user කෙනෙක්ට channels කිහිපයක් තියෙන්න පුළුවන් |
| `job_postings` (global) | id, source, external_id, title, company, url, description, location_text, tags (json), salary_min, salary_max, salary_currency, posted_at, first_seen_at, content_hash | **unique (source, external_id)**; `content_hash` එකෙන් cross-source dedupe; description වගේ විශාල දේවල් අවශ්‍ය වෙලාවට විතරක් ගන්න |
| `user_jobs` | user_id, job_posting_id, score, reasons (json), status (`new`/`notified`/`rejected`), location_uncertain (bool), salary_unknown (bool), cover_letter (text), notified_at | **unique (user_id, job_posting_id)** — මෙතනයි එක user කෙනෙක්ගේම ස්ථාවරය තියෙන්නේ |
| `notification_deliveries` | id, user_id, user_job_id, channel, status (`pending`/`sent`/`failed`), attempts, sent_at, error | audit log; retry සහ "මොකද වුණේ?" කියලා බලන්න |
| `sessions`, `password_reset_tokens` | Laravel defaults | Breeze එකෙන් එනවා |

> **Milestone 1 දී සිදු කළ නම් කිරීම්:** Laravel එකේ queue සඳහාම `jobs` කියලා table එකක් තියෙන නිසා අපේ ගෝලීය ජොබ් table එක **`job_postings`** ලෙස නම් කළා. Audit log එක **`notification_deliveries`** වුණා — Laravel එකේ standard `notifications` table එක ඉදිරියේදී in-app notifications සඳහා වෙන් කරලා තියෙනවා. මේ වෙනස Project Book එකේත් සටහන් කරලා තියෙනවා.

**Dedupe නීති:**
1. එකම source එකේ එකම `external_id` → නැවත දාන්නේ නෑ (upsert විතරයි).
2. වෙනස් source වල උනත්, normalise කරපු `(company, title)` හෝ `content_hash` එකම නම් එකම ජොබ් එකක් විදිහට සලකන්න (පළමු source එක තියාගන්න).
3. User ගේ `rejected` තීරණය කාගෙවත් දැනුම්දීමට බාධා කරන්නේ නෑ (per-user state).

---

## 6. Job DTO සහ Source interface

```php
final readonly class JobData {
    public function __construct(
        public string $source,
        public string $externalId,
        public string $title,
        public string $company,
        public string $url,
        public string $description,
        public string $locationText,
        public array $tags,
        public ?float $salaryMin,
        public ?float $salaryMax,
        public ?string $salaryCurrency,
        public ?Carbon $postedAt,
    ) {}
}
// fetched_at එක global jobs table එකේ first_seen_at විදිහට තියෙනවා.
```

```php
interface Source {
    public function name(): string;
    /** @return JobData[] — කිසිම තැනක throw කරන්න එපා; අසාර්ථක නම් log කරලා [] දෙන්න */
    public function fetch(): array;
}
```

හැම source එකම එකම format එකට normalise කරන්න ඕනේ. Field එකක් නැත්නම් හරි වැරදි format එකක් නම් හරි graceful ලෙස handle කරන්න (null/empty දාන්න, exception නෑ).

---

## 7. ජොබ් මූලාශ්‍ර (Sources)

**Code ලියන්න කලින් එක් එක් endpoint එක නිල docs හෝ live sample එකකින් තහවුරු කරන්න**, ඊට පස්සේ කෙටි පිටපතක් `storage/app/fixtures/` එකේ save කරන්න.

| Source | Type | වැදගත් දේවල් |
|---|---|---|
| RemoteOK | JSON API | පළමු array item එක legal/metadata — skip කරන්න. ජොබ් URL එකට link back කරන්න (attribution). |
| Remotive | JSON API | `software-dev` category. දවසකට කිහිපයක් විතරක් request කරන්න. |
| We Work Remotely | RSS | Programming category feeds. |
| Jobicy | JSON API | tag/geo filters තියෙනවා. |
| Himalayas | JSON API | salary fields තියෙනවා. |

සියලු HTTP calls වල **10–15s timeout** සහ **retry + exponential backoff** තියෙන්න ඕනේ (Guzzle / Laravel Http). Descriptive `User-Agent` එකක් දාන්න. එක source එකක් fail වුණත් run එක නවතින්නේ නෑ.

---

## 8. Filter pipeline (`FilterPipeline`) — මේ පිළිවෙළටම

`apply(JobData $job, UserSetting $settings): FilterResult` — `FilterResult` එකේ `passed`, `flags` (location_uncertain, salary_unknown), සහ ප්‍රතික්ෂේප වුණොත් හේතුව තියෙනවා.

1. **Freshness** — `posted_at` එක `max_age_days` (default 14) ට වඩා පරණ නම් skip.
2. **Title include** — `title_include` එකේ එක keyword එකක්වත් නැත්නම් skip (`web`, `frontend`, `full stack`, `react`, `node`, `laravel`, `php`, `javascript`, `typescript`, ...).
3. **Blacklist** — title හෝ description එකේ `exclude_words` (`intern`, `unpaid`, `principal`, `senior staff`, `security clearance`, ...) තියෙනවා නම් skip. සියල්ල config එකෙන් එන්න ඕනේ.
4. **Location eligibility**
   - Accept: location text හෝ tags වල `worldwide`, `anywhere`, `global`, `asia`, `apac`, `any timezone`, `sri lanka`.
   - Reject: `us only`, `usa only`, `united states only`, `eu only`, `europe only`, `uk only`, `canada only`, `must reside in`, `work authorization`, ...
   - Location හිස් හෝ අපැහැදිලි නම් **job එක තියාගන්න**, `location_uncertain = true` දාලා දැනුම්දීමේදී ⚠️ පෙන්නන්න.
5. **USD pay**
   - Accept: `salary_currency === 'USD'` හෝ text එකේ `$` / `USD`.
   - Salary කිසිම තැනක නැත්නම්: `allow_unknown_salary` (default `true`) නම් තියාගන්න, `salary_unknown = true` දාන්න; නැත්නම් skip.
   - Hourly/monthly අගය yearly එකට normalise කරන්න (උදා: hourly × 2080, monthly × 12). `min_salary_usd_yearly` (default 24000) ට අඩු max එකක් තියෙනවා නම් skip.

---

## 9. Scoring (`ScoringService`)

සරල එකතු කිරීමේ ලකුණු — සියලු weights `config/jobhunter.php` + per-user override වලින් එන්න ඕනේ, කොහෙවත් hardcode කරන්න එපා:

| නීතිය | Default weight |
|---|---|
| title එකේ user skill එකක් තියෙනවා නම් | `+3` එක් skill එකකට |
| description එකේ user skill එකක් තියෙනවා නම් | `+1` එක් skill එකකට |
| `worldwide` කියලා තියෙනවා නම් | `+2` |
| USD salary එක target එකට වැඩි නම් | `+2` |
| පසුගිය පැය 24 එකේ post වෙලා නම් | `+1` |
| Location uncertain නම් | `−2` |

`score >= min_score` (default 5) උන ජොබ් විතරක් දැනුම් දෙන්න. **හේතු ලිස්ට් එකත් (`reasons`) return කරන්න** — දැනුම්දීමේදී "⭐ Score: N (reasons)" කියලා පෙන්නන්න ඕනේ.

---

## 10. Cover letter (`CoverLetterService`)

`resources/views/cover_letter.md.tpl` එකේ placeholders: `{company}`, `{job_title}`, `{matched_skills}`, `{my_name}`, `{portfolio_url}`. `{matched_skills}` එකට දාන්නේ **එක user ගේ skills ලිස්ට් එකෙන් ජොබ් text එකේ තියෙන ඒවා විතරයි**. AI generation නෑ — සුද්ධ templating විතරයි. මේකෙන් හැදුණු text එක `user_jobs.cover_letter` එකේ save කරනවා.

---

## 11. දැනුම්දීම් (Notifications) — එක් එක් user තෝරාගන්නවා

### Interface
```php
interface Notifier {
    public function name(): string;                       // telegram | email | inapp
    public function send(User $user, array $items): SendResult;  // items = UserJob + cover letter
}
```
`NotificationDispatcher` එක එක user ගේ `user_channels` වල `enabled` ඒවා විතරක් ධාවනය කරනවා. **ලකුණු වැඩිම ඒවා මුලින්**, එක run එකකට `max_notifications_per_run` (default 10) ට වැඩි නෑ. සාර්ථක වුණාට පස්සේ විතරක් `notifications.status = sent` සහ `user_jobs.status = notified` කරනවා. Rate limits සඳහා retry + backoff.

### Channels

| Channel | ක්‍රියාත්මක වෙන ආකාරය |
|---|---|
| **Telegram** | Bot API `sendMessage` over HTTPS (Guzzle). Local/system token එක `.env` එකේ (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`); SaaS දී per-user token/chat `user_channels.credentials` එකේ (encrypted). Markdown/HTML escaping ගැන හරියට පරිස්සම් වෙන්න. |
| **Email** | Per-user SMTP credentials (`user_channels.credentials`, `encrypt()` කරලා තියන්න) හරහා Laravel Mailable. Local සඳහා `.env` SMTP fallback. හැම user ගේම ඊමේල් එකේ cover letter එකත් තියෙනවා. |
| **In-app** | Laravel `database` notification → `notifications` table එකට row එකක්. React `Pages/Notifications/Index.tsx` එකේ unread badge එකක් එක්ක පෙන්නනවා (මුලින් polling; පස්සේ broadcasting ඕනේ නම් Soketi/Pusher — අලුත් dep එකක් කලින් අහන්න). |

### පණිවිඩ format (English, මුල් සැලැස්මටම සමානයි)

```
🔥 {title} @ {company}
💰 {salary or "salary not listed"}
🌍 {location}   ⚠️ (location unclear, if uncertain)
⭐ Score: {score}  ({reasons})
🔗 {apply_url}
Source: {source}
```
ඊළඟ පණිවිඩයේ cover letter එක monospace block එකක් විදිහට (copy කරන්න පහසු වෙන්න). Email එකේත් මේ දෙකම තියෙනවා. In-app එකේ title/company/score/link එකත්, cover letter එකත් පෙන්නනවා.

---

## 12. Configuration සහ secrets

**`config/jobhunter.php`** — (මුල් ප්ලෑන් එකේ `config.yaml` වෙනුවට; Laravel වල ස්වාභාවික ක්‍රමය මේකයි.) මෙතන සියලු defaults තියෙනවා:

```php
return [
    'profile' => [                 // local default user එකට විතරයි; SaaS දී DB එකෙන් override
        'name' => 'My Name',
        'portfolio_url' => 'https://...',
        'skills' => ['react','node','typescript','laravel','php','javascript','tailwind'],
    ],
    'filters' => [
        'max_age_days' => 14,
        'title_include' => ['web','frontend','backend','full stack','fullstack','react','node','laravel'],
        'exclude_words' => ['intern','unpaid','principal','security clearance'],
        'location_accept' => ['worldwide','anywhere','global','asia','apac','sri lanka'],
        'location_reject' => ['us only','usa only','eu only','uk only','must reside'],
        'min_salary_usd_yearly' => 24000,
        'allow_unknown_salary' => true,
    ],
    'scoring' => [
        'min_score' => 5,
        'weights' => [
            'title_skill' => 3, 'desc_skill' => 1, 'worldwide' => 2,
            'usd_salary' => 2, 'fresh_24h' => 1, 'location_uncertain' => -2,
        ],
    ],
    'sources' => [
        'remoteok' => ['enabled' => true], 'remotive' => ['enabled' => true],
        'weworkremotely' => ['enabled' => true], 'jobicy' => ['enabled' => true],
        'himalayas' => ['enabled' => true],
    ],
    'notify' => [
        'max_notifications_per_run' => 10,
        'default_channels' => ['telegram'],  // local default; SaaS දී user_channels DB එකෙන්
    ],
    'http' => ['timeout' => 12, 'retries' => 3, 'user_agent' => 'HirePilot/1.0 (+contact)'],
];
```

- එක user කෙනෙක්ගේ වෙනස්කම් **`user_settings` / `user_channels`** tables වල store වෙනවා; Settings UI එකෙන් වෙනස් කරන්න පුළුවන්. Code එකේ කිසිම අගයක් hardcode කරන්න එපා.
- **`.env`:** `APP_KEY`, `DB_CONNECTION`, local සඳහා `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD`, `MAIL_FROM_ADDRESS`. `.env.example` එකේ උදාහරණ පමණයි.

---

## 13. Scheduling සහ state persistence

**Phase A/B (GitHub Actions — free):**
- `.github/workflows/run.yml`: `cron: '*/30 * * * *'` (UTC) + `workflow_dispatch`.
- Steps: checkout → setup PHP + Composer → cache `vendor` → restore `database/database.sqlite` **from `state` branch** → `composer install` → `php artisan migrate --force` → `php artisan jobhunter:run` → commit & push DB එක `state` branch එකට.
- `concurrency` group එකක් දාලා එකවර run දෙකක් එකම state එක ලියන එක වළක්වන්න.
- Secrets GitHuB repo secrets වල තියෙනවා (CI එකට env විදිහට දෙනවා).
- එක run එකක් ~1 min යටතේ තියන්න; ඕනේ නම් hourly කරන්න.

**Phase C (SaaS):**
- Server එකක Laravel scheduler (`php artisan schedule:run` cron එකෙන් සෑම මිනිත්තුවකම) + queue worker (`php artisan queue:work`).
- SQLite වෙනුවට **Postgres**; queue සඳහා database queue හෝ Redis. Per-user SMTP UI එකෙන් configure කරන්න.
- Deployment guide එක README එකේ සිංහලෙන් ලියන්න (domain, SSL, cron, worker, env).

---

## 14. ගුණාත්මකභාවය (Quality requirements)

- හැම source එකකටම සහ හැම user කෙනෙක්ටම පැහැදිලි log: `fetched / after filters / new / notified`.
- එක source එකක් fail උනත් run එක නවතින්නේ නෑ; error log වෙනවා විතරයි.
- සියලු HTTP වල timeout + retry/backoff; descriptive `User-Agent`.
- Tests: fixtures පාවිච්චි කරලා (live network නෑ) — source parsing, filter edge cases (location සහ salary ඇතුළු), scoring, DB dedupe, notifier dispatch (mock HTTP + fake mail), auth.
- **`php artisan jobhunter:run --dry-run`** — සම්පූර්ණ pipeline එක ක්‍රියාත්මක කරලා ප්‍රතිඵල console එකේ පෙන්නනවා; **කිසිම notification එකක් යවන්නේ නෑ, `notified` කියලා mark කරන්නෙත් නෑ.** DB write ටිකත් වළක්වන්න පුළුවන් නම් වඩා හොඳයි (transaction rollback හෝ dry-run mode).
- `declare(strict_types=1)` සහ type hints හැමතැනම; functions කුඩාවට තියන්න.

---

## 15. Milestones (මේ පිළිවෙළටම; එකෙන් පස්සේ නවත්වලා සිංහලෙන් සාරාංශයක් දෙන්න)

1. **Skeleton + schema** — Laravel install, migrations (ඉහත tables), models, `config/jobhunter.php`, `DefaultUserSeeder`, `jobhunter:run --dry-run` skeleton. *Done when:* `php artisan test` passes.
2. **First source + dry run** — RemoteOK adapter + fixture test; normalise කරලා `--dry-run` එකෙන් පෙන්නනවා.
3. **Filters + scoring** — සම්පූර්ණ pipeline + edge-case tests (location uncertain, unknown salary, hourly→yearly, blacklist).
4. **Remaining sources + dedupe** — Remotive, WWR, Jobicy, Himalayas; එක් එක්ට fixtures + tests; cross-source dedupe.
5. **Notifications + cover letter** — 3 notifiers, dispatcher, retry logic, template filling; mock HTTP/mail වලින් tests.
6. **Actions + state + docs** — `run.yml` (state branch, concurrency), සිංහල `README.md` සහ `AGENTS.md` (setup, BotFather, per-user SMTP, config tune කරන හැටි). *මෙතැනදී local version එක සම්පූර්ණයෙන් වැඩ කරනවා (default user, no UI).*
7. **Auth + React UI (Phase B)** — Breeze/Inertia React, login/register, Dashboard, Jobs list, Settings (profile/skills/filters/channels), Notifications center + unread badge. In-app notifications මෙතැනදී පණ ගන්නවා.
8. **SaaS readiness (Phase C)** — Postgres migration path, queues, per-user SMTP settings UI, basic plan/limits placeholder (billing integrate කරන්නේ නෑ — placeholder විතරයි), deployment guide.

---

## 16. Out of scope (කරන්න එපා)

- කිසිම AI/LLM/ML feature එකක්.
- Application forms auto-submit කිරීම හෝ job sites වලට login වීම.
- LinkedIn, Indeed, Glassdoor හෝ bot තහනම් කරපු ඕනෑම site එකක් scrape කිරීම.
- Web push notifications **v1 එකේ** (පසුකාලීන විකල්පයක් විතරයි).
- Billing/payment gateway integrate කිරීම (Phase C දී placeholder පමණයි).
- Phase A දී ගෙවන ඕනෑම service එකක්.

---

## 17. ඊළඟට කරන්න ඕනේ දේ (මේ ප්ලෑන් එක අනුමත වුණාට පස්සේ විතරයි)

- එක් එක් source එකේ නිල docs / live sample එක පරීක්ෂා කරලා fixtures save කරන්න (Milestone 2 දී).
- Laravel version එක install වෙලාවේ තහවුරු කරන්න (latest stable/LTS).
- Inertia + React පත් කරගන්න (ඔබට වෙනම SPA එකක් ඕනේ නම් Sanctum එකට මාරු වෙන්න පුළුවන් — ඒක කලින් කියන්න).
- **දැනටමත් එකඟ වුණු config placeholders (`My Name`, `https://...`) පස්සේ ඔබම වෙනස් කරගන්නවා.**
