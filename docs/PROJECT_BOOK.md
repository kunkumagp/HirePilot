# HirePilot — Project Book

> **Living document** · Version 2.8 · Last updated: 2026-11-19
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

The system deliberately uses **no AI or machine learning**. Every decision is transparent, deterministic, and driven by rules stored in configuration, which makes the results explainable and cheap to run. The backend is **Laravel (PHP)**, the frontend is a **separate React SPA (Vite + TypeScript)** that talks to the backend through a **Sanctum-protected JSON API**, storage starts as **SQLite** and is designed to move to **Postgres**, and scheduling uses **GitHub Actions** with a dedicated state branch for persistence. The repository is split into `backend/` and `frontend/`. The architecture is **multi-tenant from day one**: jobs are fetched once into a shared pool, while scores, notification state, and cover letters are stored per user. This allows the project to evolve into a SaaS where anyone with an account can use the service.

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
| **`backend/` + `frontend/` වෙන් කිරීම** (2026-11-18 දී ගත් තීරණය) | Backend එක සහ frontend එක වෙන වෙනම තේරුම් ගන්නත්, වෙන වෙනම වැඩ කරන්නත්, පස්සේ වෙන වෙනම deploy කරන්නත් පහසු වෙන්න. |
| **React SPA (Vite + TypeScript)** — Inertia නෑ | වෙනම `frontend/` app එකක් ඕනේ වුණා. ඒ නිසා Inertia වෙනුවට **Laravel JSON API + Sanctum tokens** පාවිච්චි කරනවා. මේක මුල් තීරණයෙන් වෙනස් වීමක්. |
| **දැනුම්දීම් ක්‍රම 3යි** | පාවිච්චි කරන කෙනාට තමන්ට ගැළපෙන ක්‍රමය තෝරන්න දෙන්න ඕනේ වුණා. |
| **Per-user SMTP** | එක system sender එකක් පාවිච්චි කරනවා වෙනුවට එක user කෙනෙක්ගේම ඊමේල් එකෙන් එවනවා — නොමිලේ, වඩා විශ්වාසනීයයි. |
| **Multi-user schema දැන්ම** | පස්සේ SaaS එකක් කරනකොට data එක නැවත හදන්න ඕනේ නෑ. |
| **එකම fetch, බොහෝ user** | API rate limits රකින්න සහ වියදම අඩුවෙන් තියන්න. |
| **`state` branch** (Actions cache නෙවෙයි) | Cache එක මැකී යන්න පුළුවන්; branch එක වඩා විශ්වාසනීයයි. |
| **AI නෑ, auto-submit නෑ** | පිරිවැය අඩුවෙන් තියන්න, ප්‍රතිඵල පැහැදිලි කරන්න පුළුවන් වෙන්න, සහ job sites වල නීති කඩන්නේ නැති වෙන්න. |
| **`job_postings` සහ `notification_deliveries` යන නම්** (මුල් සැලැස්මේ `jobs` / `notifications` වෙනුවට) | Laravel එකේම queue සඳහා `jobs` table එකක් තියෙනවා. ඒ නිසා අපේ දත්ත වෙන් කරන්න ඕනේ වුණා. `notifications` කියන නම Laravel එකේ in-app notification එකට වෙන් කළා; අපේ audit log එක `notification_deliveries` වුණා. (Milestone 1 දී සොයාගත්තා.) |
| **RSS සඳහා built-in SimpleXML** (SimplePie වෙනුවට) | "Prefer the standard library" නීතිය අනුව අලුත් Composer dependency එකක් එපා කළා. PHP 8.4 එකේ SimpleXML තියෙන නිසා WWR feed එක කියවන්න ප්‍රමාණවත්. (Milestone 4 දී තීරණය.) |

### Git ශාඛා ප්‍රතිපත්තිය (Branching workflow)

සංවර්ධනය කරන්නේ එක පියවරකට එක branch එකක් භාවිතා කරලා. මේකෙන් හැම milestone එකක්ම වෙන වෙනම සමාලෝචනය කරන්නත්, අවශ්‍ය නම් ආපහු යන්නත් පුළුවන්.

1. **`dev` එක පදනමයි.** හැම milestone එකක්ම `dev` එකෙන් අලුත් branch එකක් හදලා ඒකට checkout වෙලා වැඩ කරනවා: `mil-1`, `mil-2`, `mil-3`, `mil-4`, `mil-5`, `mil-6`.
2. **Merge කරන්න කලින් Project Book එක update කරන්න ඕනේ.** Milestone එකේ වැඩ ඉවර වුණාම, ඒම milestone branch එකේදීම `docs/PROJECT_BOOK.md` එක update කරනවා — status එක `✅ Done — <date>` කරනවා, මොකක්ද හැදුවේ කියලා කෙටි සටහනක් එකතු කරනවා, අලුත් තීරණ තියෙනවා නම් §4 එකත් යාවත්කාලීන කරනවා.
3. **මුලින්ම milestone branch එක `origin` එකට push කරන්න.** Project Book update එකත් ඇතුළත්ව අදාළ branch එක push කරන්න: `git push origin <mil-N>`.
4. **ඊට පස්සේ `dev` එකට merge කරන්න.** කිසිම milestone වැඩේක් කෙලින්ම `dev` එකේ නොකරන්න.
5. **අන්තිමට `dev` එකත් `origin` එකට push කරන්න.** හැම merge එකකින්ම පස්සේ `dev` එකේ tests ආයෙත් ධාවනය කරලා හොඳින් තියෙනවාද බලලා, ඊට පස්සේ `git push origin dev` කරන්න.
6. **`mil-6` merge කරාට පස්සේ `mil-7-auth` හදන්න.** Auth + React UI milestone එක (§11) කරන්නේ `dev` එකෙන් හදපු `mil-7-auth` branch එකේ. ඊටත් පස්සේ SaaS readiness එක සඳහා එහෙමම අලුත් branch එකක් (`mil-8-saas`) හදන්න.

### Folder ව්‍යුහය

```
HirePilot/
├── backend/    → Laravel API + job engine (composer.json, artisan, app/, config/, database/, tests/ …)
├── frontend/   → React + Vite + TypeScript SPA (package.json, src/ …)
├── docs/PROJECT_BOOK.md
├── PLAN.md, AGENTS.md, README.md
└── .github/workflows/run.yml
```

Laravel එකේ ස්වාභාවික ව්‍යුහයේ දී files root එකේම තිබුණත්, පැහැදිලි බව සඳහා අපි ඒවා `backend/` එකට ගෙනාවා. සියලු Artisan/Composer/PHPUnit commands `backend/` එකෙන්, සියලු npm commands `frontend/` එකෙන් ධාවනය කරන්න.

---

## 5. Tech Stack (භාවිතා කරන තාක්ෂණයන්)

| ස්ථරය | තාක්ෂණය | තෝරාගත් හේතුව |
|---|---|---|
| Backend language | **PHP 8.3+** | Laravel එකට අවශ්‍ය වෙන පදනම |
| Backend framework | **Laravel** (install වෙන අවස්ථාවේ latest stable / LTS) | Auth, Eloquent ORM, notifications, queues, scheduler, migrations ඔක්කොම එක්ක එනවා |
| Dependency manager | **Composer** | PHP packages කළමනාකරණය |
| HTTP client | Laravel `Http` facade / **Guzzle** | API calls, timeouts සහ retries |
| RSS parsing | **SimplePie** (හෝ laminas-feed) | We Work Remotely RSS feed එක කියවන්න |
| Frontend | **React + Vite + TypeScript** (`frontend/` එකේ වෙනම SPA) | වේගවත්, type-safe UI; backend එකෙන් වෙනම වැඩ කරන්න පුළුවන් |
| API / Auth | **Laravel JSON API + Sanctum tokens** (email + password) | SPA එකට session නැතුව ආරක්ෂිතව authenticate වෙන්න |
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
| 2 | RemoteOK adapter + fixture + dry-run output | `mil-2` | ✅ Done — 2026-11-18 |
| 3 | Filters + scoring + edge-case tests | `mil-3` | ✅ Done — 2026-11-18 |
| 4 | Remotive, WWR, Jobicy, Himalayas + cross-source dedupe | `mil-4` | ✅ Done — 2026-11-18 |
| 5 | Notification channels (Telegram/Email/In-app) + cover letter | `mil-5` | ✅ Done — 2026-11-18 |
| 6 | GitHub Actions + state branch + සිංහල README/AGENTS | `mil-6` | ✅ Done — 2026-11-18 |
| 7 | Auth (Sanctum) + React SPA (Dashboard, Jobs, Settings, Notification centre) | `mil-7-auth` | ✅ Done — 2026-11-18 |
| 8 | SaaS readiness (Postgres, queues, per-user SMTP UI, deploy guide) | `mil-8-saas` | ✅ Done — 2026-11-18 |

### අදියර වශයෙන් බැලීම

- **Phase A — Local MVP (Milestones 1–6):** එක default user කෙනෙක්, නොමිලේ, GitHub Actions එකෙන් ස්වයංක්‍රීයව ධාවනය වෙනවා.
- **Phase B — Multi-user app (Milestone 7):** Login/register, පෞද්ගලික settings, වෙබ් එකේ notification centre.
- **Phase C — SaaS (Milestone 8):** Postgres, queues, ඕනෑම කෙනෙකුට account එකකින් පාවිච්චි කරන්න පුළුවන් තත්ත්වය; අවශ්‍ය පරිදි අඩු වියදමක් සහිත hosting.

**Milestone 1 සාරාංශය (2026-11-18):** Laravel 13.43 skeleton එක ස්ථාපනය කළා; Boost dev tool එකත් එකතු වුණා. `users` table එකට profile fields එකතු කරලා, `user_settings`, `user_channels`, `job_postings`, `user_jobs`, `notification_deliveries` කියන tables හැදුවා. ඊට අදාළ Eloquent models, factories සහ `DefaultUserSeeder` එකත් හැදුවා. සියලු tune කිරීම් `config/jobhunter.php` එකේ තියෙනවා. `php artisan jobhunter:run --dry-run` කියන skeleton command එක වැඩ කරනවා. **PHPUnit: 8 tests, 18 assertions — සියල්ල pass වුණා.**

**Milestone 2 සාරාංශය (2026-11-18):** RemoteOK එකේ නිල JSON API එක live sample එකකින් තහවුරු කළා (පළමු item එක legal/metadata බව ඇතුළුව). `JobData` readonly DTO එකක් සහ `Source` interface එකක් හදලා, `RemoteOkSource` adapter එක ඒවා ක්‍රියාත්මක කරනවා. API call එකේ config එකෙන් එන timeout/retries/User-Agent තියෙනවා; එක source එකක් fail වුණත් `[]` ආපහු එනවා. Trimmed sample එක `backend/storage/app/fixtures/remoteok.json` එකේ save කළා. `jobhunter:run --dry-run` එක දැන් එක් එක් source එකෙන් ගත්තු normalise කළ ජොබ් පෙන්නනවා (live ධාවනයේ දී RemoteOK එකෙන් **97** ජොබ් ලැබුණා). RemoteOK එකේ attribution/link-back නීතිය රකින්න මුල් job URL එකම තියාගන්නවා. සටහනක් ලෙස: සමහර RemoteOK salary අගයන් අසාමාන්‍ය ලෙස විශාලව (උදා. `114000000`) එනවා — ඒවා Milestone 3 හි salary normalisation එකේදී විමසා බලන්න. **PHPUnit: 13 tests, 45 assertions — සියල්ල pass වුණා.**

**Milestone 3 සාරාංශය (2026-11-18):** `FilterPipeline` එක පිළිවෙළටම පෙරහන් 5 යොදනවා — freshness, title include, blacklist, location (accept/reject/uncertain flag), සහ USD වැටුප. `SalaryNormalizer` එක config එකේ තියෙන සීමා අනුව hourly/monthly අගයන් yearly එකට හරවනවා. `ScoringService` එක weights අනුව ලකුණු + හේතු ලැයිස්තුව දෙනවා; per-user settings නැති වෙලාවේ `config/jobhunter.php` defaults පාවිච්චි වෙනවා. `jobhunter:run --dry-run` එක දැන් fetch → filter → score පෙන්නනවා. Live ධාවනයේ දී 97 ජොබ් ගත්තත් **0ක්** පෙරහන් පසු ඉතුරු වුණේ නෑ — 66ක් දින 14ට වඩා පරණයි, 30කගේ title එකේ include keyword නෑ, 1ක් blacklist වුණා. මේක filter එක වැඩ කරන බව පෙන්නනවා; ඕනේ නම් `max_age_days` හෝ `title_include` config එකෙන් ලිහිල් කරන්න පුළුවන්. **PHPUnit: 32 tests, 81 assertions — සියල්ල pass වුණා.**

**Milestone 4 සාරාංශය (2026-11-18):** ඉතිරි adapter 4 එකතු වුණා — Remotive (JSON), We Work Remotely (RSS), Jobicy (JSON), Himalayas (JSON). HTTP එක `JsonApiSource` base එකකට ගෙනලා duplication අයින් කළා; HTML → plain text සඳහා `TextNormalizer`, field හැරවීම් සඳහා `SourceValues` කියන පොඩි services එකතු වුණා. WWR RSS එක කියවන්න **PHP එකේම SimpleXML** පාවිච්චි කළා — ඒ නිසා අලුත් Composer dependency එකක් එපා වුණා (මුල් සැලැස්මේ තිබූ SimplePie වෙනුවට). `DedupeService` එකෙන් normalised `(company, title)` hash එකක් හදලා cross-source dedupe කරනවා; `FetchService` එක එයාගේම `(source, external_id)` එකත් එක්ක global `job_postings` table එකට upsert කරනවා — **`--dry-run` එකේදී කිසිම DB write එකක් නෑ**. හැම source එකටම trimmed fixture එකක් තියෙනවා. Live dry-run එකේ දී source 5 එකතුවෙන් **207 unique ජොබ්** ලැබිලා, පෙරහන් පස්සේ **1ක්** score 9ක් එක්ක ඉතුරු වුණා — හැම ජොබ් එකම එක් වරක් විතරක් ගණන් කරලා තියෙනවා කියන එක dedupe එක පෙන්නනවා. **PHPUnit: 46 tests, 139 assertions — සියල්ල pass වුණා.**

**Milestone 5 සාරාංශය (2026-11-18):** දැනුම්දීම් ක්‍රම තුනම එක `Notifier` interface එකක් යටතේ ක්‍රියාත්මක වෙනවා — **Telegram** (Bot API `sendMessage`), **Email** (Laravel Mail; per-user SMTP credentials encrypt කරලා තියෙනවා, නැත්නම් default mailer), සහ **In-app** (Laravel database notifications → වෙබ් එකේ notification center එකට එන data). `NotificationDispatcher` එක එක user කෙනෙක්ගේ enabled channels වලට විතරක් යවනවා, ලකුණු වැඩිම ඒවා මුලින්, එක run එකකට උපරිම `max_notifications_per_run`, retry + backoff එක්ක — එක channel එකක් fail වුණත් අනිත් ඒවාට බාධා වෙන්නේ නෑ. සාර්ථක වුණාට පස්සේ විතරක් `user_jobs.status = notified` වෙනවා; හැම උත්සාහයක්ම `notification_deliveries` audit table එකේ සටහන් වෙනවා. `CoverLetterService` එක `cover_letter.md.tpl` එක pure templating එකෙන් පුරවනවා (AI නෑ) — එක user ගේ skills ජොබ් එකේ තියෙන ඒවා විතරය matched_skills එකට යන්නේ. Laravel database notifications සඳහා `notifications` migration එකත් එකතු වුණා. **`--dry-run` එකේදී කිසිම user_jobs/notification/delivery write එකක් නෑ.** **PHPUnit: 52 tests, 166 assertions — සියල්ල pass වුණා.**

**Milestone 6 සාරාංශය (2026-11-18):** `.github/workflows/run.yml` එකතු වුණා — හැම විනාඩි 30 කට වරක් cron, `workflow_dispatch` අතින් run කිරීමට, සහ `concurrency` group එකකින් එකවර run දෙකක් වළක්වනවා. Workflow එක `dev` එක checkout කරලා, PHP 8.4 + Composer cache එක්ක dependencies ගෙනලා, **`state` branch එකෙන් `database.sqlite` restore කරලා**, migrate + job run කරලා, DB එක ආයෙ `state` branch එකට commit කරනවා. Secrets ඔක්කොම repo secrets වලින් env විදිහට එනවා (කිසිම එකක් repo එකේ නෑ). සිංහල **README.md** එකත් ලියලා ඉවරයි — local setup, dry-run, BotFather එකෙන් bot හදන හැටි, chat id ගන්න හැටි, per-user SMTP, config tune කිරීම, සහ Actions/secrets සැකසුම ඒකේ තියෙනවා. YAML එක Symfony YAML parser එකෙන් වලිදාගත්තා. **මෙතැනදී local/MVP version එක සම්පූර්ණයෙන් වැඩ කරනවා. PHPUnit: 52 tests, 166 assertions — සියල්ල pass වුණා.**

**Milestone 7 සාරාංශය (2026-11-18):** **Phase B ආරම්භයි.** පරිශීලක අනුමැතියෙන් `laravel/sanctum` (Composer) සහ `react-router-dom` (npm) එකතු වුණා — `install:api` එකෙන් API scaffolding, `personal_access_tokens` migration, සහ `auth:sanctum` guard සැකසුණා. API එකට register/login/logout/me, jobs, settings, channels සහ notifications endpoints (11ක්) එකතු වුණා; සියලු validate කිරීම් Form Requests වලිනුත්, responses API Resources වලිනුත්. User කෙනෙක් register වුණාම එයාගේ default settings එකත් හැදෙනවා. හැම user කෙනෙක්ටම පෙනෙන්නේ එයාගේම jobs සහ notifications විතරයි (tenant isolation tests එක්ක). Channel credentials කවදාවත් API response එකේ එළියට යන්නේ නෑ — encrypted ලෙස DB එකේ තියෙනවා. React SPA එකට Login/Register/Dashboard/Jobs/Notifications/Settings පිටු සහ token-based fetch client එකත් හදුණා; CORS එක `FRONTEND_ORIGINS` env එකෙන් සීමා කරලා තියෙනවා. **PHPUnit: 66 tests, 219 assertions — pass; frontend `npm run build` සාර්ථකයි.**

**Milestone 8 සාරාංශය (2026-11-18):** SaaS සඳහා හැඩය සූදානම් වුණා. `users.plan` (`free`/`pro`) එකතු වුණා; `config/jobhunter.php` එකේ `plans` සීමා අනුව `NotificationDispatcher` එක එක run එකක යවන දැනුම්දීම් ගාණ සීමා කරනවා (user ගේ අගය සහ plan එකේ අගයෙන් අඩුම එක ගන්නවා). `notify.use_queue = true` උන විට `DispatchUserNotifications` queued job එක notification බාර ගන්නවා — local/Actions එකේ නම් සින්ක්‍රොනස්වම යනවා. `routes/console.php` එකේ විනාඩි 30ක schedule එකකුත් තියෙනවා (SaaS host එකක `schedule:run` එක්ක පාවිච්චි වෙනවා). Postgres path, queue worker, `APP_KEY` ස්ථිරත්වය, frontend deployment සහ plan placeholder ගැන සිංහල deployment guide එක README එකේ එකතු වුණා. **Payment/billing integrate කරලා නෑ — placeholder විතරයි.** **PHPUnit: 69 tests, 227 assertions — pass.**

**Post-release (2026-11-19) — සියලු active users සඳහා pipeline:** කලින් `jobhunter:run` එක config එකේ සිටි default user ට විතරක් ජොබ් දුන්නා. දැන් එකම fetch එකකින් පස්සේ සියලුම `is_active = true` users ගේ තමන්ගේ settings අනුව filter → score → store → notify කරනවා. මේක `UserPipelineService` එකකට අදාළ කරලා තියෙනවා, ඒ නිසා tenant-isolation එක unit/feature test එකකින් ඔප්පු වෙනවා — එක user ගේ නීති අනිත් අයගේ ප්‍රතිඵල වලට බලපාන්නේ නෑ. CLI output එකත් දැන් per-user counts (`matched / new / notified`) පෙන්නනවා. **PHPUnit: 73 tests, 246 assertions — pass.**

**Post-release (2026-11-19) — per-user job preferences:** එක එක user ගේ රස්සා සෙවුම වෙනස් වෙන නිසා `user_settings` එක පුළුල් කළා — **field/role family** (config එකේ presets + එක user ගේම custom keywords), **experience level** (junior→lead + minimum years), **work arrangement** (remote/hybrid/onsite), **preferred countries/regions** සහ **salary currency**. Hard filters තියෙන්නේ location restrictions, blacklist සහ salary විතරයි; field/skills/experience/arrangement/country දැන් **scoring signals** නිසා ටිකක් අඩු ගැළපීමක් තියෙන ජොබුත් පෙනෙනවා, හැබැයි ලකුණු අඩුවෙන්. Field එකම ගැළපුණු ජොබ් එකක් දැනුම් ලැබීමට ඇති තරම් වෙන්න `field_match = 6`, `min_score = 6` ලෙස සකස් කළා. Preset ලැයිස්තුව frontend එකට දෙන්නේ `GET /api/settings/options` එකෙන් — UI එකේ hardcode නෑ. **PHPUnit: 78 tests, 270 assertions — pass; frontend build සහ browser check ද සාර්ථකයි.**

**Post-release (2026-11-19) — full job taxonomy:** සීමිත 7 presets වෙනුවට සම්පූර්ණ දෙ-මට්ටමේ taxonomy එකක් එකතු වුණා — **18 categories සහ 96 sub-categories** (Engineering, Design, Product, Data, Marketing, Sales, Customer, Operations, Finance, HR, Writing, Education, Healthcare, Legal, Real Estate, IT Support, Manufacturing, Logistics). Taxonomy එක `config/job_taxonomy.php` එකේ තියෙනවා; හැම sub-category එකකටම keyword ලැයිස්තුවක් තියෙනවා. Settings UI එකේ දැන් **Job category** සහ **Job sub-category** කියලා dropdown දෙකක් තියෙනවා — පළමු එක තෝරද්දී දෙවැන්න ඒ අනුව පිරෙනවා. Options ලැබෙන්නේ `/api/settings/options` එකෙන්; frontend එකේ hardcode නෑ. Scoring එකේදී තෝරපු sub-category එකේ keywords (හෝ category keywords) + user ගේ custom keywords එකතු වෙනවා. **PHPUnit: 79 tests, 279 assertions — pass; browser එකෙන් dropdown දෙක සහ sub-category filtering ද තහවුරු කළා.**

**Post-release (2026-11-19) — free-text job query:** සෑම රස්සාවකම designation එක වෙනස් වෙන නිසා fixed taxonomy එක පමණක් ඇති නොවුණා. දැන් Settings එකේ උඩින් **"Job title / role keywords"** කියලා text field එකක් තියෙනවා — user කෙනෙක්ට උදා: `React Developer, HubSpot Marketing Manager, Full Stack PHP` කියලාම ලියන්න පුළුවන්. Scoring එකේදී මුළු phrase එක title/description එකේ තිබුණොත් **+6** (`query_match`), ඒ phrase එකේ වැදගත් වචන තව **+1 බැගින්** (උපරිම 3 — `query_token`; `developer`, `remote` වගේ සාමාන්‍ය වචන stop-word ලෙස ඉවත් කරනවා). Category/Sub-category dropdown දෙක දැන් දෙවැන්නට තියෙන **optional helpers** විතරයි — දෙකෙන්ම keywords එකතු වෙනවා. **PHPUnit: 81 tests, 285 assertions — pass; browser එකෙන් අලුත් field එක සහ layout එකත් පරීක්ෂා කළා.**

**Post-release (2026-11-19) — job details page:** හැම ජොබ් එකකටම දැන් තමන්ගේ details පිටුවක් තියෙනවා (`/jobs/{id}`). `GET /api/jobs/{id}` එකෙන් සම්පූර්ණ විස්තරය, tags, වැටුප, location, score එකට හේතු සහ cover letter එක එවනවා — අනිත් අයගේ ජොබ් එකක් ඇහුවොත් 404 එනවා (tenant isolation). පිටුවේම **Apply for this job ↗** button එකත්, cover letter එකත්, **Re-create cover letter** button එකත් තියෙනවා. ඒ button එක `POST /api/jobs/{id}/cover-letter` එකට ගිහින් එක current profile/skills අනුව letter එක template එකෙන්ම අලුත් කරලා DB එකේ save කරනවා (AI නෑ). **PHPUnit: 85 tests, 304 assertions — pass; browser එකෙන් details page එක සහ regenerate එකත් තහවුරු කළා.**

**Post-release (2026-11-19) — paragraph-preserving job descriptions:** මුල් HTML එකේ තිබුණු paragraph/list break ටික `TextNormalizer` එක කලින් එක space එකකට ගුලි කරලා දැම්මා. දැන් block tags (`</p>`, `</h3>`, `</li>` සහ `<br>`) newline බවට හරවලා, පේළියක ඇතුළේ spaces විතරක් collapse කරනවා — ඒ නිසා මුල් post එකේ ආකාරයටම paragraph වශයෙන් කියවන්න පුළුවන්. Details page එක දැන් description එක blank-line අනුව කඩලා `<p>` blocks විදිහට පෙන්නනවා (උදා. Lemon.io post එකේ **35 paragraphs**). පරණ job ටිකත් refresh වෙන්න දැන් duplicate උනත් එම source එකේ description එක update වෙන විදිහට FetchService එක හදුවා. **PHPUnit: 88 tests, 307 assertions — pass; browser එකෙන් paragraph rendering තහවුරු කළා.**

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

- Stack: PHP 8.3+ / Laravel (`backend/`), React + Vite + TS (`frontend/`), Sanctum API, SQLite→Postgres, PHPUnit + fixtures.
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
| 1.3 | 2026-11-18 | **`backend/` + `frontend/` folder split** සිදු කළා; Inertia වෙනුවට React SPA + Laravel API/Sanctum තීරණය ලේඛනගත කළා. |
| 1.4 | 2026-11-18 | Milestone 2 සම්පූර්ණ වුණා — RemoteOK adapter, `JobData` DTO, `Source` interface, fixture සහ dry-run output; status සහ සාරාංශය එකතු කළා. |
| 1.5 | 2026-11-18 | Merge workflow එක ශක්තිමත් කළා: පළමුව milestone branch එක `origin` එකට push, ඊට පස්සේ `dev` එකට merge, අන්තිමට `dev` එකත් push. |
| 1.6 | 2026-11-18 | Milestone 3 සම්පූර්ණ වුණා — FilterPipeline, SalaryNormalizer, ScoringService සහ edge-case tests; status සහ live-run සටහන එකතු කළා. |
| 1.7 | 2026-11-18 | Milestone 4 සම්පූර්ණ වුණා — ඉතිරි sources 4, fixtures, cross-source dedupe, FetchService; SimpleXML තීරණයත් ලේඛනගත කළා. |
| 1.8 | 2026-11-18 | Milestone 5 සම්පූර්ණ වුණා — නාලිකා 3, dispatcher, retry/backoff, cover letter, audit log; status සහ සාරාංශය එකතු කළා. |
| 1.9 | 2026-11-18 | Milestone 6 සම්පූර්ණ වුණා — GitHub Actions workflow, state-branch persistence, සිංහල README; MVP එක සම්පූර්ණයි. |
| 2.0 | 2026-11-18 | Milestone 7 සම්පූර්ණ වුණා — Sanctum auth API සහ React SPA (Phase B ආරම්භය); dependency අනුමැතියත් සටහන් කළා. |
| 2.1 | 2026-11-18 | Milestone 8 සම්පූර්ණ වුණා — plan limits, queued notifications, scheduler සහ SaaS deployment guide; සියලු milestones සම්පූර්ණයි. |
| 2.2 | 2026-11-19 | `jobhunter:run` එක සියලු active users ට ධාවනය වෙන විදිහට වෙනස් කළා (`UserPipelineService` + tenant isolation tests). |
| 2.3 | 2026-11-19 | Per-user job preferences එකතු කළා — field presets/custom keywords, experience, work arrangement, countries, salary currency සහ ඒ සඳහා API/UI. |
| 2.4 | 2026-11-19 | සීමිත presets වෙනුවට 18-category / 96-subcategory taxonomy එකක් සහ Settings එකේ dropdown දෙකක් එකතු කළා. |
| 2.5 | 2026-11-19 | Settings layout එකේ field overlap ගැටලුව හදුවා — Job search focus panel එක full-width කරලා, labels/input වලට අවශ්‍ය ඉඩ දුන්නා. |
| 2.6 | 2026-11-19 | Free-text job query field එක එකතු කළා — පරිශීලකයාට නිශ්චිත රස්සා designation ටයිප් කරන්න පුළුවන්; dropdown දෙක optional ලෙස ඉතිරි වුණා. |
| 2.7 | 2026-11-19 | Job details page එක, full description, Apply button සහ cover letter එක Re-create කරන්න පුළුවන් button එක එකතු කළා. |
| 2.8 | 2026-11-19 | Job description එක මුල් post එකේ paragraph ව්‍යුහයත් එක්ක `<p>` blocks විදිහට පෙන්නන්න හදුවා. |

---

*මේ පොත ඕනෑම කෙනෙකුට — investor කෙනෙකුට, developer කෙනෙකුට, හෝ interview එකකදී — ආරක්ෂිතව ලබා දෙන්න පුළුවන්. එකේ රහස් දත්ත නෑ.*
