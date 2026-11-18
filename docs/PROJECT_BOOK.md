# HirePilot — Project Book

> **Living document** · Version 1.2 · Last updated: 2026-11-18
> **Status of implementation: Planning complete — coding has not started yet.**
> This book contains **no sensitive data**: no API tokens, no passwords, no personal names, no private emails, no credentials of any kind. Configuration examples are generic placeholders only.
>
> Update this book after every milestone so it always reflects the real state of the project.

---

## How to use this book

| If you are… | Read these sections |
|---|---|
| An **investor / stakeholder** | Executive Summary, §1, §2, §3, §11 |
| A **developer joining the project** | §4, §5, §6, §7, §8, §9 |
| Someone in a **job interview** | Executive Summary, §4, §6, §8, §9, §14 |
| A **non-technical reader** | §13 Glossary, then §1–§3 |

---

## English Executive Summary

**HirePilot** is a zero-cost, rule-based job-matching and notification system. It collects remote web-developer jobs from five free public APIs/RSS feeds, filters them for roles open to candidates in Sri Lanka and paid in USD, ranks them with configurable keyword rules, and sends the best matches to each user through the channels that user chooses: **Telegram, email, or an in-app notification centre on the website**. Users apply manually with one tap — the system never submits applications on their behalf.

The system deliberately uses **no AI or machine learning**. Every decision is transparent, deterministic, and driven by rules stored in configuration, which makes the results explainable and cheap to run. The backend is **Laravel (PHP)**, the frontend is **React with Inertia.js**, storage starts as **SQLite** and is designed to move to **Postgres**, and scheduling uses **GitHub Actions** with a dedicated state branch for persistence. The architecture is **multi-tenant from day one**: jobs are fetched once into a shared pool, while scores, notification state, and cover letters are stored per user. This allows the project to evolve into a SaaS where anyone with an account can use the service.

At the time of writing the planning is complete and implementation has not begun. The roadmap has eight milestones, ending with a SaaS-ready deployment.

---

## 1. ප්‍රස්තාවනාව — ගැටලුව (The problem)

Remote work වලට අයදුම් කරන ශ්‍රී ලංකාවේ developer කෙනෙකුට ප්‍රධාන ගැටලු කිහිපයක් තියෙනවා:

1. **තොරතුරු විසිරී තිබීම** — රස්සා එක් එක් වෙබ් අඩවියේ, එක් එක් API එකේ වෙන වෙනවා. හැමතැනම අතින් බලන්න වෙනවා.
2. **සුදුසුකම් නොගැළපීම** — බොහෝ "worldwide" ජොබ් ඇත්තටම වෙන රටවල අයට විතරයි ("US only", "must reside in…" වගේ සීමා තියෙනවා).
3. **මුදල් ගැටලුව** — සමහර ජොබ් වල වැටුප සඳහන් නෑ; සමහර ඒවා ඉතා අඩු මුදලක් දෙනවා. USD ගෙවන ඒවා වෙන් කරගන්න ඕනේ.
4. **කාලය වැය වීම** — දිනපතා අලුත් ජොබ් හොයන එක, ඒවා කියවලා cover letter හදන එක වැඩි කාලයක් ගන්නවා.
5. **දැනුම් දීමේ පහසුව නොමැතිකම** — එක කෙනෙක් Telegram වලට කැමතියි, අනිත් කෙනා ඊමේල් වලට, තුන්වැන්නා වෙබ් එක ඇතුළේ බලන්න කැමතියි.

### මේ ව්‍යාපෘතියෙන් කරන්නේ

මේ පස් ගැටලුවටම එක නොමිලේ, ස්වයංක්‍රීය පද්ධතියකින් පිළිතුරු දෙනවා. ඒත් **අයදුම් කරන්නේ පාවිච්චි කරන කෙනාමයි** — මේකෙන් forms auto-fill කරන්නේ නෑ, job sites වලට login වෙන්නේ නෑ. ඒක ආරක්ෂිතයි සහ එක් එක් site එකේ නීතිවලට ගරු කරනවා.

---

## 2. විසඳුම සහ වටිනාකම (Solution & value)

```
Free APIs / RSS  →  එක් වරක් fetch කරන්න  →  පොදු job pool එකක්  →  එක් එක් user ගේ නීති අනුව පෙරහන් කරන්න
→  ලකුණු දී පෙළ ගස්වන්න  →  හොඳම ඒවා තෝරන්න  →  එක user කෙනෙක් තෝරපු channel එකෙන් දැනුම් දෙන්න
→  apply link + සූදානම් cover letter
```

**ප්‍රධාන වටිනාකම්:**

- **නොමිලේ ධාවනය (Phase A):** ගෙවන API නෑ, AI model නෑ. GitHub Actions free tier එකෙන් පමණයි.
- **විනිවිද පෙනෙන තීරණ:** "ඇයි මේ ජොබ් එක එව්වේ?" කියලා හේතු ලැයිස්තුවක් එක්කම පෙන්නනවා. AI black box එකක් නෑ.
- **පුද්ගලීකෘත (Personalised):** හැම user ගේම skills, අවම වැටුප, ස්ථාන සීමා සහ දැනුම්දීම් රුචිකම් වෙනස් වෙන්න පුළුවන්.
- **පරිමාණය කරන්න පුළුවන් (Scalable):** එකම API call එකකින් ගත්තු දත්ත හැම user කෙනෙක්ටම නැවත පාවිච්චි කරනවා. User ගාණ වැඩි වුණත් source එකට යන requests ගාණ වැඩි වෙන්නේ නෑ.
- **සූදානම් cover letter:** සම්පූර්ණයෙන් template එකකින්, user ගේ skills ජොබ් එකේ තියෙන ඒවාත් එක්ක පුරවලා දෙනවා.

---

## 3. පරාසය සහ සීමා (Scope & boundaries)

**ඇතුළේ තියෙන දේවල්:**

- නිල API/RSS හරහා ජොබ් ලබා ගැනීම (මූලාශ්‍ර 5ක්).
- Freshness, title, blacklist, location, USD salary — පෙරහන් 5ක්.
- Keyword මත පදනම් ලකුණු දීම + හේතු.
- Template මත පදනම් cover letter.
- Telegram / Email / In-app දැනුම්දීම් — එක් එක් user තෝරාගන්නවා.
- Account පද්ධතිය සහ React අතුරු මුහුණත (පසු අදියරේ).
- ස්වයංක්‍රීය ධාවනය සහ state පවත්වාගෙන යාම.

**ඇතුළේ නැති දේවල් (අනිවාර්යයෙන් නොකරන දේවල්):**

- කිසිම AI / LLM / ML භාවිතයක්.
- Application forms ස්වයංක්‍රීයව පිරවීම හෝ job sites වලට login වීම.
- LinkedIn, Indeed, Glassdoor වගේ bot තහනම් කරලා තියෙන sites scrape කිරීම.
- Phase A දී ගෙවන ඕනෑම සේවාවක්.
- v1 එකේ Web push (පසුකාලීන විකල්පයක් විතරයි).
- Billing/payment gateway (SaaS අදියරේදී placeholder පමණයි).

---

## 4. සැලසුම් ක්‍රමවේදය (How the project was planned)

මේ ව්‍යාපෘතියේ සැලැස්ම **කේතය ලියන්න කලින් සම්පූර්ණ කළා**. අදියර මෙහෙමයි:

1. **අරමුණ සහ සීමා නිර්වචනය** — $0 වියදම, AI නෑ, official API/RSS විතරයි, secrets env වල විතරයි.
2. **තීරණ එකින් එක තහවුරු කිරීම** — ප්‍රශ්න අහලා පැහැදිලි කරගත්තා (පහළ table එක බලන්න).
3. **ගෘහ නිර්මාණය සහ data model එක සැලසුම් කිරීම** — පොදු `job_postings` pool + පුද්ගලික `user_jobs` අදහස මෙතැනදී තීරණය වුණා. (Laravel එකේ queue සඳහාම `jobs` කියලා table එකක් තියෙන නිසා අපේ table එක `job_postings` ලෙස නම් කළා.)
4. **Milestones 8කට කඩාගැනීම** — හැම එකටම "ඉවර වුණා කියන්නේ මොකක්ද" කියලා නිර්ණායකයක් දැම්මා.
5. **ලේඛනගත කිරීම** — `PLAN.md` (තාක්ෂණික සැලැස්ම), `AGENTS.md` (coding agent නීති), සහ මේ `PROJECT_BOOK.md` (පැහැදිලි කිරීමේ ලේඛනය).

### ප්‍රධාන තීරණ සහ හේතු (Decision log)

| තීරණය | හේතුව |
|---|---|
| **Laravel (PHP)**, Python/FastAPI නෙවෙයි | Auth, notifications, queues, scheduler වගේ SaaS සඳහා ඕන දේවල් Laravel එකේ built-in තියෙනවා. වේගයෙන් සහ අඩු අවදානමකින් ගොඩනගන්න පුළුවන්. |
| **React + Inertia.js** | Laravel එක්ක ගැළපෙන React පද්ධතියක්; separate SPA එකකට වඩා සරලයි (token කළමනාකරණය අඩුයි). |
| **දැනුම්දීම් ක්‍රම 3යි** | පාවිච්චි කරන කෙනාට තමන්ට ගැළපෙන ක්‍රමය තෝරන්න දෙන්න ඕනේ වුණා. |
| **Per-user SMTP** | එක system sender එකක් පාවිච්චි කරනවා වෙනුවට එක user කෙනෙක්ගේම ඊමේල් එකෙන් එවනවා — නොමිලේ, වඩා විශ්වාසනීයයි. |
| **Multi-user schema දැන්ම** | පස්සේ SaaS එකක් කරනකොට data එක නැවත හදන්න ඕනේ නෑ. |
| **එකම fetch, බොහෝ user** | API rate limits රකින්න සහ වියදම අඩුවෙන් තියන්න. |
| **`state` branch** (Actions cache නෙවෙයි) | Cache එක මැකී යන්න පුළුවන්; branch එක වඩා විශ්වාසනීයයි. |
| **AI නෑ, auto-submit නෑ** | පිරිවැය අඩුවෙන් තියන්න, ප්‍රතිඵල පැහැදිලි කරන්න පුළුවන් වෙන්න, සහ job sites වල නීති කඩන්නේ නැති වෙන්න. |
| **`job_postings` සහ `notification_deliveries` යන නම්** (මුල් සැලැස්මේ `jobs` / `notifications` වෙනුවට) | Laravel එකේම queue සඳහා `jobs` table එකක් තියෙනවා. ඒ නිසා අපේ දත්ත වෙන් කරන්න ඕනේ වුණා. `notifications` කියන නම Laravel එකේ in-app notification එකට වෙන් කළා; අපේ audit log එක `notification_deliveries` වුණා. (Milestone 1 දී සොයාගත්තා.) |

### Git ශාඛා ප්‍රතිපත්තිය (Branching workflow)

සංවර්ධනය කරන්නේ එක පියවරකට එක branch එකක් භාවිතා කරලා. මේකෙන් හැම milestone එකක්ම වෙන වෙනම සමාලෝචනය කරන්නත්, අවශ්‍ය නම් ආපහු යන්නත් පුළුවන්.

1. **`dev` එක පදනමයි.** හැම milestone එකක්ම `dev` එකෙන් අලුත් branch එකක් හදලා ඒකට checkout වෙලා වැඩ කරනවා: `mil-1`, `mil-2`, `mil-3`, `mil-4`, `mil-5`, `mil-6`.
2. **Merge කරන්න කලින් Project Book එක update කරන්න ඕනේ.** Milestone එකේ වැඩ ඉවර වුණාම, ඒම milestone branch එකේදීම `docs/PROJECT_BOOK.md` එක update කරනවා — status එක `✅ Done — <date>` කරනවා, මොකක්ද හැදුවේ කියලා කෙටි සටහනක් එකතු කරනවා, අලුත් තීරණ තියෙනවා නම් §4 එකත් යාවත්කාලීන කරනවා.
3. **ඊට පස්සේ merge කරන්න.** Update කරපු Project Book එකත් එක්ක අදාළ branch එක `dev` එකට merge කරනවා. කිසිම milestone වැඩේක් කෙලින්ම `dev` එකේ නොකරන්න.
4. **`mil-6` merge කරාට පස්සේ `mil-7-auth` හදන්න.** Auth + React UI milestone එක (§11) කරන්නේ `dev` එකෙන් හදපු `mil-7-auth` branch එකේ. ඊටත් පස්සේ SaaS readiness එක සඳහා එහෙමම අලුත් branch එකක් (`mil-8-saas`) හදන්න.
5. හැම merge එකකින්ම පස්සේ `dev` එකේ tests ආයෙත් ධාවනය කරලා හොඳින් තියෙනවාද බලන්න.

---

## 5. Tech Stack (භාවිතා කරන තාක්ෂණයන්)

| ස්ථරය | තාක්ෂණය | තෝරාගත් හේතුව |
|---|---|---|
| Backend language | **PHP 8.3+** | Laravel එකට අවශ්‍ය වෙන පදනම |
| Backend framework | **Laravel** (install වෙන අවස්ථාවේ latest stable / LTS) | Auth, Eloquent ORM, notifications, queues, scheduler, migrations ඔක්කොම එක්ක එනවා |
| Dependency manager | **Composer** | PHP packages කළමනාකරණය |
| HTTP client | Laravel `Http` facade / **Guzzle** | API calls, timeouts සහ retries |
| RSS parsing | **SimplePie** (හෝ laminas-feed) | We Work Remotely RSS feed එක කියවන්න |
| Frontend | **React + Inertia.js + Vite + TypeScript** | වේගවත්, type-safe UI; Laravel එක්ක හොඳින් ගැළපෙනවා |
| Auth | **Laravel Breeze** (email + password, sessions) | සරල සහ ආරක්ෂිත login/register |
| Database | **SQLite** (local / Actions) → **Postgres** (SaaS) | මුලින් එක file එකක් නිසා පහසුයි; පස්සේ පරිමාණයට Postgres |
| Notifications | Telegram Bot API, SMTP (per-user), Laravel database notifications | පාවිච්චි කරන කෙනාගේ තේරීම අනුව |
| Tests | **PHPUnit** + saved JSON/RSS fixtures | Live network නැතුව විශ්වාසනීය පරීක්ෂණ |
| Scheduling | **GitHub Actions** (cron) → Laravel scheduler + queue worker (SaaS) | Free tier එකෙන් පටන් ගෙන සර්වර් එකකට මාරු වෙන්න පුළුවන් |
| Version control | **Git**, private repository | Code සහ secrets ආරක්ෂාව |
| Documentation | Markdown (සිංහල + English summary) | සියලුම පාර්ශ්වයන්ට කියවන්න පුළුවන් වෙන්න |

**අනිවාර්යයෙන් නැති දේවල්:** AI SDK, LLM API, ML libraries, paid services (Phase A), scraping frameworks.

---

## 6. ගෘහ නිර්මාණය (Architecture)

### මූලික මූධර්මය: "එක් වරක් ගන්න, බොහෝ දෙනෙකුට ලබා දෙන්න"

```
   ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌────────┐ ┌───────────┐
   │ RemoteOK │ │ Remotive │ │   WWR    │ │ Jobicy │ │ Himalayas │
   └────┬─────┘ └────┬─────┘ └────┬─────┘ └───┬────┘ └─────┬─────┘
        └────────────┴─────────────┴──────────┴────────────┘
                              │  (එක් run එකකදී එක් වරක් බැගින්)
                              ▼
                     ┌─────────────────┐
                     │  FetchService   │  →  global `job_postings` table
                     │  + dedupe       │     (පොදු ජොබ් ගොනුව)
                     └────────┬────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        ▼                     ▼                     ▼
 ┌─────────────┐       ┌─────────────┐       ┌─────────────┐
 │ User A      │       │ User B      │       │ User C      │
 │ settings    │       │ settings    │       │ settings    │
 ├─────────────┤       ├─────────────┤       ├─────────────┤
 │ Filter      │       │ Filter      │       │ Filter      │
 │ Score       │       │ Score       │       │ Score       │
 │ user_jobs   │       │ user_jobs   │       │ user_jobs   │
 │ Cover letter│       │ Cover letter│       │ Cover letter│
 └──────┬──────┘       └──────┬──────┘       └──────┬──────┘
        │                     │                     │
   Telegram+Email        Email විතරයි         In-app විතරයි
        └─────────────────────┼─────────────────────┘
                              ▼
                  NotificationDispatcher
                  (retry + backoff, සාර්ථක වුණාට පස්සේ විතරක් "notified")
```

### ප්‍රධාන කොටස් සහ ඒවායේ වගකීම

| කොටස | වගකීම |
|---|---|
| `Source` interface + 5 adapters | එක් එක් API/RSS එක එකම `JobData` format එකට ගෙනත් දෙනවා. Adapter එකක් කිසිම තැනක exception එකක් එළියට දෙන්නේ නෑ — fail උනොත් log කරලා හිස් list එකක් දෙනවා. |
| `FetchService` | හැම source එක්කම කතා කරලා global pool එක update කරනවා. |
| `DedupeService` | `(source, external_id)` සහ normalised `(company, title)` / `content_hash` එකෙන් අනවශ්‍ය පිටපුරුදු අයින් කරනවා. |
| `FilterPipeline` | පෙරහන් 5 පිළිවෙළට යොදනවා. |
| `ScoringService` | ලකුණු + හේතු ලැයිස්තුව ආපහු දෙනවා. |
| `CoverLetterService` | User ගේ skills ජොබ් එකේ තියෙන ඒවාත් එක්ක template එක පුරවනවා. |
| `NotificationDispatcher` | එක user කෙනෙක් තෝරපු channels වලට විතරක් යවනවා; ලකුණු වැඩිම ඒවා මුලින්; එක run එකකට උපරිම 10ක්. |
| `TelegramNotifier` / `EmailNotifier` / `InAppNotifier` | එක් එක් channel එකේ නිශ්චිත ක්‍රියාත්මක කිරීම. |
| `jobhunter:run` Artisan command | සම්පූර්ණ pipeline එක එක command එකකින් ධාවනය කරනවා. `--dry-run` එකෙන් යවන්නේ නැතුව ප්‍රතිඵල පෙන්නනවා. |

---

## 7. Data Model (දත්ත ව්‍යුහය)

| Table | කරන්නේ (සරලව) |
|---|---|
| `users` | Account එක — නම, ඊමේල්, password hash, portfolio URL, active තත්ත්වය. |
| `user_settings` | එක user කෙනෙක්ගේ නීති — skills, title keywords, blacklist, location ලැයිස්තු, අවම USD වැටුප, ලකුණු weights, එක run එකක උපරිම දැනුම්දීම් ගාණ. |
| `user_channels` | කුමන ක්‍රමයෙන් දැනුම් දෙන්නද කියන එක — වර්ගය, target, encrypt කරපු credentials. |
| `job_postings` *(පොදු)* | මූලාශ්‍රය, external id, title, company, URL, description, location, tags, වැටුප, post වුණු දිනය, hash. **unique `(source, external_id)`** සහ `content_hash` index. |
| `user_jobs` *(පුද්ගලික)* | Score, හේතු, cover letter, status (`new` / `notified` / `rejected`), location අපැහැදිලිද?, වැටුප නැතිද? **unique `(user_id, job_posting_id)`**. |
| `notification_deliveries` | Audit log — කුමන channel එකට යැව්වාද, සාර්ථකද, කී පාරක් retry කළාද, වැරැද්ද මොකක්ද. Laravel එකේ standard `notifications` table එක ඉදිරියේදී in-app notifications සඳහා වෙන් කරලා තියෙනවා. |

**Dedupe නීති:**

1. එකම source එකේ එකම `external_id` ආයෙ ආවොත් — අලුත් row එකක් හදන්නේ නෑ, පරණ එක update වෙනවා.
2. වෙනස් source වලින් ආවත් normalised `(company, title)` හෝ `content_hash` එකම නම් — එකම ජොබ් එකක් විදිහට සලකනවා.
3. එක user කෙනෙක් "rejected" කළා කියලා අනිත් අයගේ දැනුම්දීමට බාධා වෙන්නේ නෑ (state එක සම්පූර්ණයෙන් per-user).

---

## 8. මූලික ක්‍රියාවලි (Core pipeline details)

### Filter පියවර (අනිවාර්ය පිළිවෙළට)

1. **Freshness** — නියමිත දවස් ගාණට (default 14) වඩා පරණ ජොබ් skip කරනවා.
2. **Title include** — වින්‍යාස කරපු keyword එකක්වත් title එකේ නැත්නම් skip.
3. **Blacklist** — `intern`, `unpaid`, `principal`, `security clearance` වගේ වචන තියෙනවා නම් skip.
4. **Location** — `worldwide`, `asia`, `sri lanka` වගේ දේවල් accept කරනවා; `us only`, `eu only`, `must reside in` වගේ දේවල් reject කරනවා. හිස් හෝ අපැහැදිලි නම් ජොබ් එක තියාගෙන ⚠️ flag එකක් දානවා.
5. **USD pay** — currency USD නම් හෝ `$` සලකුණ තියෙනවා නම් accept. Hourly/monthly අගය yearly එකට හරවලා අවම වැටුපත් එක්ක සසඳනවා. වැටුප සඳහන් නැති ඒවා config එක අනුව තියාගන්නත් පුළුවන් (flag එකක් එක්ක).

### Scoring (සරල එකතු කිරීම)

| නීතිය | Default weight |
|---|---|
| Title එකේ skill එකක් | +3 |
| Description එකේ skill එකක් | +1 |
| `worldwide` සඳහන් නම් | +2 |
| USD වැටුප ඉලක්කයට වැඩි නම් | +2 |
| පසුගිය පැය 24 එකේ post වෙලා නම් | +1 |
| Location අපැහැදිලි නම් | −2 |

`min_score` (default 5) ට වැඩි හෝ සම උන ඒවා විතරක් යවනවා. හේතු ලැයිස්තුවත් පණිවිඩයේ පෙනෙනවා.

### Cover letter

`{company}`, `{job_title}`, `{matched_skills}`, `{my_name}`, `{portfolio_url}` කියන placeholders පුරවන template එකක්. කිසිම AI generation එකක් නෑ — සුද්ධ templating විතරයි.

### Notification dispatcher

- එක run එකකදී එක user කෙනෙකුට උපරිම **10ක්** (configurable).
- ලකුණු වැඩිම ඒවා මුලින්.
- එක channel එකක් fail උනත් අනිත් ඒවාට බාධා වෙන්නේ නෑ.
- Retry + exponential backoff.
- **සාර්ථක send එකකින් පස්සේ විතරක්** state එක `notified` වෙනවා.

---

## 9. ගුණාත්මක ඉංජිනේරු විද්‍යාව (Quality engineering)

- **Tests:** සෑම source adapter එකකටම saved fixture එකක්. Live network එකක් නැතුව පරීක්ෂණ ධාවනය වෙනවා. Filters (location/salary ඇතුළු edge cases), scoring, dedupe, notification dispatch (mock HTTP සහ fake mail), auth — ඔක්කොම ආවරණය වෙනවා.
- **`--dry-run`:** සම්පූර්ණ pipeline එක ක්‍රියාත්මක කරලා ප්‍රතිඵල console එකේ පෙන්නනවා; කිසිම පණිවිඩයක් යවන්නේ නෑ; `notified` කියලා mark කරන්නෙත් නෑ.
- **Logging:** හැම source එකකටම සහ හැම user කෙනෙක්ටම පැහැදිලි ගණන් — fetched / after filters / new / notified.
- **Resilience:** Timeouts (10–15s), retries, backoff. එක source එකක් බිඳුණත් run එක නවතින්නේ නෑ.
- **Respect for sources:** Descriptive `User-Agent`, rate limits, RemoteOK එකට link-back (attribution), official APIs/RSS විතරයි.
- **Type safety:** `declare(strict_types=1)`, typed properties/params/returns, කුඩා functions.
- **Reproducibility:** Composer + npm lock files, fixtures, migration-based schema.

---

## 10. ආරක්ෂාව සහ පෞද්ගලිකත්වය (Security & privacy)

- Secrets කිසිවෙකුත් repo එකේ නෑ — `.env` (local) සහ GitHub repo secrets (CI). `.env.example` එකේ හිස් උදාහරණ විතරයි.
- Per-user SMTP credentials `encrypt()` කරලා DB එකේ තියෙනවා.
- Repo එක **private** යි.
- කිසිම auto-submission එකක් නෑ — ඒකෙන් ban වීමේ අවදානම සහ නීති කඩවීම වළකිනවා.
- මේ Project Book එකේ කිසිම පුද්ගලික හැඳුනුම් දත්තයක් හෝ credential එකක් නෑ — ඒක ඕනෑම කෙනෙකුට පෙන්වන්න පුළුවන්.

---

## 11. සංවර්ධන තත්ත්වය සහ Roadmap (Status & roadmap)

> **අවංකව සටහන් කරන්න:** මේ ලේඛනය ලියන අවස්ථාවේ **සැලැස්ම විතරයි සම්පූර්ණ වෙලා තියෙන්නේ. කිසිම code එකක් ලියලා නෑ.** මේ table එක හැම milestone එකකින් පස්සේ update කරන්න.

| # | Milestone | Branch | තත්ත්වය |
|---|---|---|---|
| 1 | Skeleton + migrations + models + config + dry-run command | `mil-1` | ✅ Done — 2026-11-18 |
| 2 | RemoteOK adapter + fixture + dry-run output | `mil-2` | ⬜ Planned |
| 3 | Filters + scoring + edge-case tests | `mil-3` | ⬜ Planned |
| 4 | Remotive, WWR, Jobicy, Himalayas + cross-source dedupe | `mil-4` | ⬜ Planned |
| 5 | Notification channels (Telegram/Email/In-app) + cover letter | `mil-5` | ⬜ Planned |
| 6 | GitHub Actions + state branch + සිංහල README/AGENTS | `mil-6` | ⬜ Planned |
| 7 | Auth + React UI (Dashboard, Jobs, Settings, Notification centre) | `mil-7-auth` | ⬜ Planned |
| 8 | SaaS readiness (Postgres, queues, per-user SMTP UI, deploy guide) | `mil-8-saas` | ⬜ Planned |

### අදියර වශයෙන් බැලීම

- **Phase A — Local MVP (Milestones 1–6):** එක default user කෙනෙක්, නොමිලේ, GitHub Actions එකෙන් ස්වයංක්‍රීයව ධාවනය වෙනවා.
- **Phase B — Multi-user app (Milestone 7):** Login/register, පෞද්ගලික settings, වෙබ් එකේ notification centre.
- **Phase C — SaaS (Milestone 8):** Postgres, queues, ඕනෑම කෙනෙකුට account එකකින් පාවිච්චි කරන්න පුළුවන් තත්ත්වය; අවශ්‍ය පරිදි අඩු වියදමක් සහිත hosting.

**Milestone 1 සාරාංශය (2026-11-18):** Laravel 13.43 skeleton එක ස්ථාපනය කළා; Boost dev tool එකත් එකතු වුණා. `users` table එකට profile fields එකතු කරලා, `user_settings`, `user_channels`, `job_postings`, `user_jobs`, `notification_deliveries` කියන tables හැදුවා. ඊට අදාළ Eloquent models, factories සහ `DefaultUserSeeder` එකත් හැදුවා. සියලු tune කිරීම් `config/jobhunter.php` එකේ තියෙනවා. `php artisan jobhunter:run --dry-run` කියන skeleton command එක වැඩ කරනවා. **PHPUnit: 8 tests, 18 assertions — සියල්ල pass වුණා.**

*(Milestone එකක් ඉවර වුණාම `⬜ Planned` වෙනුවට `✅ Done — <date>` දාන්න. අතරමග නම් `🔄 In progress` දාන්න.)*

---

## 12. මෙහෙයුම් සහ යෙදවීම (Operations & deployment)

**Local / Phase A:**
- GitHub Actions workflow එක හැම විනාඩි 30 කට වරක් cron එකෙන් ධාවනය වෙනවා; අතින්ත් run කරන්න පුළුවන්.
- Run එක පටන් ගන්නකොට `database.sqlite` එක `state` branch එකෙන් ගන්නවා, ඉවර වෙලා ආයෙ ඒකටම commit කරනවා.
- `concurrency` group එකක් නිසා එකවර run දෙකක් එකම DB එක ලියන්නේ නෑ.
- Secrets GitHub repo secrets වලින් env විදිහට එනවා.

**Future / Phase C:**
- සර්වර් එකක Laravel scheduler සහ queue worker ධාවනය වෙනවා.
- Database එක Postgres වලට මාරු වෙනවා (schema එක දැනටම portable විදිහට ලියලා තියෙනවා).
- Domain, SSL, cron, worker, env පිළිබඳ සිංහල deployment guide එක README එකේ තියෙනවා.

---

## 13. පාරිභාෂික වචන (Glossary — සරල පැහැදිලි කිරීම්)

| වචනය | සරල තේරුම |
|---|---|
| **API** | වෙබ් අඩවියකින් දත්ත ඉල්ලලා ගන්න ක්‍රමයක් (අතින් පිටුව බලනවා වෙනුවට program එකෙන් අහනවා). |
| **RSS feed** | අලුත් ලිපි/ජොබ් ලැයිස්තුවක් දෙන සරල දත්ත ආකෘතියක්. |
| **Adapter** | එක් එක් මූලාශ්‍රයේ වෙනස් දත්ත ආකෘතිය අපේ එකම ආකෘතියට හරවලා දෙන කොටස. |
| **Dedupe** | එකම දේ දෙපාරක් තියෙන එක වළක්වා එකක් විතරක් තබාගන්න එක. |
| **Migration** | Database එකේ tables හදන/වෙනස් කරන කේත ගොනුවක් (Laravel වල මේක සාමාන්‍ය දෙයක්). |
| **Eloquent** | Laravel එකේ database එක්ක කතා කරන ආකාරය — tables එකක් වෙනුවට PHP objects වගේ සලකන්න දෙනවා. |
| **Queue / Worker** | වැඩේ පිළිවෙළකට තියලා පස්සේ ක්‍රියාත්මක කරන පද්ධතියක් (දැනුම්දීම් යවන්න වගේ). |
| **Cron** | නියමිත වෙලාවට දෙයක් ස්වයංක්‍රීයව කරන්න කියලා දෙන නිර්දේශයක්. |
| **SMTP** | ඊමේල් යවන්න පාවිච්චි කරන සම්මත ක්‍රමය. |
| **Token / Secret** | රහස් අගයක් (උදා: bot token, password) — කවදාවත් code එකේ දාන්නේ නෑ. |
| **Fixture** | පරීක්ෂණ සඳහා සේව් කරපු උදාහරණ API පිළිතුරක්. |
| **Dry-run** | ඇත්තටම පණිවිඩ යවන්නේ නැතුව ප්‍රතිඵල විතරක් පෙන්නන පරීක්ෂණ ධාවනයක්. |
| **Multi-tenant / SaaS** | එකම පද්ධතිය බොහෝ පාරිභෝගිකයන්ට වෙන වෙනම account එකක් විදිහට ලබා දීම. |

---

## 14. ප්‍රේක්ෂකයන්ට කෙටි සාරාංශ (Tailored summaries)

### Investors සඳහා (කෙටියෙන්)

- **Problem:** Remote job discovery විසිරී තියෙනවා; ශ්‍රී ලංකාවේ අයට ගැළපෙන, USD ගෙවන ජොබ් හොයාගන්න අමාරුයි.
- **Solution:** නොමිලේ data sources එකතු කරලා, පැහැදිලි නීති එක්ක පෙරහන් කරලා, පාවිච්චි කරන කෙනා කැමති ක්‍රමයෙන් දැනුම් දෙන පද්ධතියක්.
- **Differentiator:** AI නෑ → පිරිවැය අඩුයි, තීරණ පැහැදිලියි. Multi-user architecture එක නිසා user ගාණ වැඩි වුණත් source requests වැඩි වෙන්නේ නෑ.
- **Monetisation path:** Phase C දී account-පදනම් සේවාවක් ලෙස ගෙවන සැලසුම් එකතු කරන්න පුළුවන් (මේ වෙද්දී billing integrate කරලා නෑ — roadmap එකේ විතරයි).
- **Risk control:** Job sites වල නීති කඩන්නේ නෑ (official APIs/RSS විතරයි, auto-submit නෑ).

### Developers සඳහා

- Stack: PHP 8.3+ / Laravel, React + Inertia + Vite + TS, SQLite→Postgres, PHPUnit + fixtures.
- එකම source එකක් එකතු කරන්නේ අලුත් adapter file එකක් + config එකේ එක line එකකින් විතරයි.
- සියලු අගයන් config එකේ හෝ per-user DB settings වල — code එකේ hardcode නෑ.
- හැම සංකීර්ණ කොටසකටම fixture-පදනම් unit test එකක් තියෙනවා.
- Commands (ඉදිරියේ දී ක්‍රියාත්මක වෙන): `composer install`, `npm install`, `php artisan migrate`, `php artisan test`, `php artisan jobhunter:run --dry-run`.

### Job interview එකකදී කියන්න පුළුවන් දේවල්

- "මම පටන් ගන්න කලින් සම්පූර්ණ සැලැස්මක් හැදුවා — constraints, decision log, data model, milestones සහ test criteria එක්ක."
- "AI පාවිච්චි කරන්නේ නැතුව, පැහැදිලි කරන්න පුළුවන් rule-based scoring එකක් හදන එක මගේ තේරීමක් වුණා — ඒකෙන් වියදමත් අඩුයි, විශ්වාසනීයත්වයත් වැඩියි."
- "Multi-user system එකකදී දත්ත ගන්න එක එක් වරක් විතරක් කරලා, පරිශීලකයාගේ අර්ථ නිරූපණය වෙනම table එකක තියන එක මගේ ප්‍රධාන design decision එකක්."
- "එක source එකක් fail වුණත් run එක නවතින්නේ නෑ කියලා මම සැලසුම් කළා; හැම තැනම timeouts, retries සහ logging තියෙනවා."
- "Secrets කවදාවත් repo එකේ නෑ; ඒකට env සහ GitHub secrets පාවිච්චි කරනවා."

---

## 15. ලේඛන නඩත්තුව (Document maintenance)

- **Update කරන්න ඕනේ වෙලාව:** හැම milestone එකක වැඩත් ඉවර වෙලා, **`dev` එකට merge කරන්න කලින්** — ඒම milestone branch එකේදීම (§11 table එකේ status එක `✅ Done — <date>` කරන්න, මොකක්ද හැදුවේ කියලා සටහන් කරන්න, අලුත් තීරණ තියෙනවා නම් §4 එකත් update කරන්න). විස්තර §4 "Git ශාඛා ප්‍රතිපත්තිය" එකේ තියෙනවා.
- **කිසිම දෙයක් එපා:** token, password, පෞද්ගලික නම්/ඊමේල්, පුද්ගලික portfolio URLs, credential දත්ත.
- **අදාළ ලේඛන:**
  - `PLAN.md` — සවිස්තර තාක්ෂණික සැලැස්ම සහ නීති.
  - `AGENTS.md` — coding agent එකට දෙන කෙටි නීති සහ commands.
  - `README.md` — setup සහ පාවිච්චි කරන හැටි (Milestone 6 දී ලියන්න).
  - `docs/PROJECT_BOOK.md` — මේ ලේඛනය (ඕනෑම කෙනෙකුට පෙන්වන්න පුළුවන් පොත).

### Version history

| Version | Date | වෙනස |
|---|---|---|
| 1.0 | 2026-11-18 | පළමු සංස්කරණය — සැලැස්ම සම්පූර්ණ වූ පසු ලියන ලදී. Implementation තවම ආරම්භ වී නෑ. |
| 1.1 | 2026-11-18 | Git ශාඛා ප්‍රතිපත්තිය එකතු කළා (mil-1…mil-6 → dev, ඊට පස්සේ mil-7-auth) සහ milestones table එකට Branch තීරුව එකතු කළා. |
| 1.2 | 2026-11-18 | Milestone 1 සම්පූර්ණ වුණා — table නම් දෙකක වෙනස (`job_postings`, `notification_deliveries`) සහ M1 සාරාංශය/status එකතු කළා. |

---

*මේ පොත ඕනෑම කෙනෙකුට — investor කෙනෙකුට, developer කෙනෙකුට, හෝ interview එකකදී — ආරක්ෂිතව ලබා දෙන්න පුළුවන්. එකේ රහස් දත්ත නෑ.*
