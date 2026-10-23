# Discovery Report — School Management SaaS

- **Date:** 2026-10-23
- **Status:** HISTORICAL SNAPSHOT (2026-10-23). Living decisions are in `docs/architecture/decisions/`, `docs/product/PRD.md` and `docs/roadmap/`.
- **Author:** Claude (technical lead), for the product owner

Labels used throughout:

- **[CONFIRMED]**: verified fact or a decision you have made.
- **[RECOMMENDED]**: my proposal, which needs your approval.
- **[ASSUMPTION]**: something I'm treating as true without proof. Each one says what follows from it.
- **[HYPOTHESIS]**: a business belief to test with real schools.
- **[OPEN]**: an unresolved question.

---

## 1. Environment and repository assessment

| Item              | Finding                                                      | Impact                                                                                                                                                                                                                                                                                   |
| ----------------- | ------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Project directory | `C:\xampp\htdocs\school management`. Empty, not a Git repo.  | Nothing to preserve. It's a clean start.                                                                                                                                                                                                                                                 |
| Path              | Contains a **space** and sits **inside XAMPP `htdocs`**      | **Security:** if XAMPP Apache is running, Apache serves everything under `htdocs`, which would include `.env` and the source code. The space in the path also breaks some tools (Docker bind mounts, some npm scripts). **Recommend moving to e.g. `C:\dev\school-saas`** (decision D2). |
| OS                | Windows 10 Home, build **21996** (an old Insider build)      | Docker Desktop needs WSL2. Home edition supports WSL2, but an old Insider build may block or destabilise the WSL/Docker install. **Risk to verify.**                                                                                                                                     |
| PHP               | 8.4.13 (XAMPP, ZTS)                                          | Compatible with Laravel 13. **Missing `pdo_pgsql`/`pgsql`** and no `redis` extension.                                                                                                                                                                                                    |
| Composer          | 2.8.10                                                       | OK                                                                                                                                                                                                                                                                                       |
| Node / npm        | 22.20.0 / 10.9.3                                             | OK for Vite and React 19. Use npm, since pnpm here is old (8.15).                                                                                                                                                                                                                        |
| Git               | 2.50.1; global `user.name` set; no default branch configured | Set `init.defaultBranch=main` and check `user.email` before the first commit.                                                                                                                                                                                                            |
| GitHub CLI (`gh`) | Not installed                                                | Optional. Makes PR and CI checks scriptable from Claude Code.                                                                                                                                                                                                                            |
| PostgreSQL        | **Not installed**                                            | Blocking for development (see D3).                                                                                                                                                                                                                                                       |
| Docker / WSL      | **Not installed**; WSL has no distro                         | Blocking for the Docker-based workflow (see D3).                                                                                                                                                                                                                                         |
| Redis             | Not installed                                                | Not needed initially (see §3).                                                                                                                                                                                                                                                           |
| Claude Code       | 2.1.12                                                       | Supports project subagents (`.claude/agents/*.md`), hooks and permissions (`.claude/settings.json`), skills, and the built-in `/code-review` and `/security-review`.                                                                                                                     |

### Free local development (no cost)

All of these run locally for free: Laravel + PostgreSQL + queue worker + scheduler; Mailpit to catch email; local disk instead of object storage; fake SMS and payment drivers (log-based); Pest, Larastan, Pint, ESLint and TypeScript; Playwright E2E; GitHub with a private repo and GitHub Actions (free minutes are enough at this scale).

### External accounts needed later (none needed for Milestone 1 except GitHub)

| Need                                   | When                 | Candidate options (to evaluate, not chosen)              | Cost type                                   |
| -------------------------------------- | -------------------- | -------------------------------------------------------- | ------------------------------------------- |
| GitHub (repo + Actions)                | M1                   | GitHub Free                                              | Free                                        |
| Domain + DNS                           | Staging (M3+)        | `.com` or `.cm` domain; Cloudflare DNS                   | Yearly                                      |
| VPS (staging + prod)                   | Staging M3+, prod M9 | Hetzner, OVH, DigitalOcean, local Cameroonian hosting    | Monthly                                     |
| Off-site object storage / backups      | M9                   | Any S3-compatible provider                               | Usage-based                                 |
| Transactional email                    | Staging              | Postmark, Brevo, Resend, SES                             | Free tier, then usage                       |
| SMS gateway                            | M7                   | Local Cameroonian aggregators vs international providers | **Per message, and this scales with usage** |
| Error monitoring / uptime              | Staging              | Sentry free tier; an uptime pinger                       | Free tier                                   |
| Mobile Money (MTN MoMo / Orange Money) | **Post-MVP**         | Direct operator APIs vs aggregators                      | Per-transaction fees plus KYC/contract      |

Secrets: none were found or created. `.env` files will be git-ignored, and Claude Code will be denied read/write access to them (§10).

---

## 2. Product assumptions and unresolved questions

### Cameroon-specific realities that shape the design

1. **Two education subsystems.** The francophone system works in _trimestres_ and _séquences_ (usually 6 séquences per year), marks out of /20, subject _coefficients_, class rank, and a _bulletin_ that lists absence hours. The anglophone system uses terms and grading oriented towards the GCE. Nursery and primary use different assessment styles again. **The grading engine cannot be generic from day one.** We need to pick one subsystem and level band first (Q1).
2. **Bilingual country.** The UI should support French and English from the start. Retrofitting i18n is expensive, while adding it during scaffolding is cheap. [ASSUMPTION] French is the default language.
3. **Fees.** Private schools charge registration (_inscription_) plus tuition (_scolarité_) in **instalments (_tranches_) with deadlines**, sometimes plus PTA (_APE_) and other fees. Payment is largely **cash at the bursar's office**, with growing use of MTN MoMo and Orange Money. Arrears are a chronic cash-flow problem for school owners.
4. **Parents.** Many parents have phones but don't use email. SMS (and possibly WhatsApp) beats email for reaching them, but SMS costs money per message.
5. **Connectivity and power** are unreliable. The app needs light pages, server-side pagination, and printable outputs (receipts, class lists, report cards). Many schools will print.
6. **Onboarding effort.** A school has hundreds of students, often in Excel or on paper. **Bulk import (CSV/Excel) is an MVP requirement**, not a nice-to-have. Without it, the onboarding cost per school destroys the margin.
7. **Calendar.** The school year starts in September. The 2026–27 year **is already under way**, and first-trimester report cards are due around December. Schools usually decide on software **between June and August**, before the new school year starts.

### Unresolved questions

- **Q1 [OPEN, blocks MVP scope].** Which segment comes first: private francophone secondary, anglophone secondary, or nursery/primary? Do you have specific pilot schools or contacts?
- **Q2 [OPEN].** For your pilot contacts, which pain is worse: tracking fees and arrears, or producing report cards? Who pays: the owner (_promoteur_) or the principal?
- **Q3 [OPEN, blocks M1].** Can you install WSL2 and Docker Desktop on this machine (admin rights, reboot, compatibility with Insider build 21996)? Or do you prefer native PostgreSQL on Windows?
- **Q4 [OPEN].** How much time can you put in each week, and is there a target pilot date?
- **Q5 [OPEN, not blocking yet].** Is there a registered company? Contracts with schools, data-protection authorisation (§9) and receiving subscription payments all need a legal entity.

---

## 3. Recommended architecture

### Stack decisions

| #   | Decision                                                                                                                                                                      | Status                               | Rationale                                                                                                                                                                                                                                                                               | Alternatives considered                                                                                                                                                                                      |
| --- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| A1  | **Laravel 13** (current major; check the exact minor at scaffold time) on **PHP 8.4**                                                                                         | RECOMMENDED                          | Mature, well-documented, and covers everything we need (auth, queues, scheduler, policies, migrations). PHP 8.4 matches your local install and is widely available on Linux.                                                                                                            | Node/NestJS: more decisions to make yourself and weaker batteries for CRUD-heavy work. Django: fine, but you already know Laravel.                                                                           |
| A2  | **Inertia 3 + React 19 + TypeScript + Tailwind 4**, starting from the **official Laravel React starter kit**                                                                  | RECOMMENDED                          | No separate API to version and secure. Routing, auth and authorization stay server-side. React keeps the door open for an offline attendance PWA later (IndexedDB, service worker). The starter kit ships auth pages and a shadcn/ui component base, which serves as the design system. | **Livewire:** simpler, less JavaScript, and a strong option. It loses on offline capability and on reuse of React skills. Separate SPA + API: more surface area and more to secure, with no current benefit. |
| A3  | **Modular monolith, lightweight style**                                                                                                                                       | RECOMMENDED                          | One deployable unit, with modules as namespaces. Boundaries are enforced by **Pest architecture tests**, not by convention alone.                                                                                                                                                       | Microservices / hexagonal-everywhere: rejected because the overhead has no justification.                                                                                                                    |
| A4  | **PostgreSQL 17+**                                                                                                                                                            | RECOMMENDED / confirms your proposal | Row-Level Security, partial unique indexes, composite foreign keys, check constraints, strong transactional guarantees for finance.                                                                                                                                                     | MySQL (already in XAMPP): no RLS, weaker constraint features.                                                                                                                                                |
| A5  | **No Redis initially.** Queue, cache and sessions use the database driver.                                                                                                    | RECOMMENDED                          | One fewer service to run, secure and back up. At tens of schools, database-backed queues are fine. Revisit when queue throughput or cache latency is measured as a problem.                                                                                                             | Redis from day one: operational cost with no demonstrated need.                                                                                                                                              |
| A6  | **Docker (Laravel Sail) for local dev**, matching the Linux production environment                                                                                            | RECOMMENDED (depends on Q3)          | Reproducible environment and parity with production.                                                                                                                                                                                                                                    | Native Windows install: works, but drifts from production (Windows PHP vs Linux, path issues).                                                                                                               |
| A7  | **Money stored as integer XAF (`bigint`)**                                                                                                                                    | RECOMMENDED                          | The FCFA has no minor unit in practice, and integers avoid float rounding errors. A currency column is kept for future markets.                                                                                                                                                         | Decimal: unnecessary for XAF.                                                                                                                                                                                |
| A8  | **PDF generation** (receipts, report cards): start with dompdf. Run a spike before report cards to decide on a headless-Chromium service (e.g. Gotenberg) if layouts need it. | RECOMMENDED (decide later)           | dompdf is pure PHP with no extra service and is good enough for receipts.                                                                                                                                                                                                               | Browsershot (Chromium on the app server): heavier.                                                                                                                                                           |
| A9  | **Online-first.** The PWA offers installability and static-asset caching only. Offline attendance is a dedicated later milestone with a design doc.                           | CONFIRMS your proposal               | Offline needs sync, conflict resolution and secure local storage. That is a project of its own.                                                                                                                                                                                         | —                                                                                                                                                                                                            |

### Module layout (proposal)

```
app/
  Domain/
    Tenancy/      School, Membership, TenantContext, BelongsToSchool trait
    Identity/     User-related actions (invites, password policies)
    Academics/    AcademicYear, Level, SchoolClass, Subject, ClassSubject
    Students/     Student, Guardian, Enrollment, Import
    Finance/      FeeType, FeeSchedule, Charge, Payment, Allocation, Receipt, CashSession
    Assessment/   Period, Mark, ReportCard (later)
    Attendance/   (later)
    Audit/        AuditLog
  Http/Controllers/<Module>/   thin controllers
  Http/Requests/<Module>/      FormRequest validation
  Policies/                    one policy per tenant-owned model
```

- **Workflows live in single-purpose Action classes** (`RecordPayment`, `EnrollStudent`). Controllers validate, authorize and call an action.
- **No repository layer.** Eloquent is already the data-access abstraction. Adding repositories now is premature.
- Architecture tests enforce, for example: tenant-owned models use `BelongsToSchool`; `Finance` does not reach into `Assessment` internals; controllers don't use `DB::` directly.

---

## 4. MVP definition

### Challenge to the proposed sequence

Your sequence puts attendance (5) before grades (6) and fees (7). **I recommend moving fees earlier and attendance later:**

- **Fees and arrears** hit the owner's cash flow directly. This is the pain most likely to drive willingness to pay [HYPOTHESIS H1]. It's useful from the first day of the school year, and it works without teachers adopting anything.
- **Report cards** are the second strongest pain [HYPOTHESIS H2]: a labour-intensive end-of-term crunch, and highly visible to parents. They are also the most subsystem-specific and the most complex.
- **Attendance** has the lowest willingness to pay and needs daily teacher adoption, which is the hardest change to make. Report cards need absence hours, but in the first version a total per term can be entered by hand.

**This order is a hypothesis.** If Q2 shows that report cards are the bigger pain for your pilot schools, swap M4 and M5. The foundation work stays the same either way.

### Recommended MVP: "Students + Fees" first, then "Report cards", as one paid package for the pilot

**In scope:**

- Platform admin creates a school and invites its owner/director. **No self-signup.** Onboarding is assisted, which matches the market and avoids building billing too early.
- Staff accounts with fixed roles: Owner/Director, Principal, Bursar, Secretary, Teacher.
- Academic year, levels, classes, subjects with coefficients.
- Students, guardians (name and phone), enrollment per year with history, and **CSV/Excel import**.
- Fee schedules per level and year with instalments and due dates; charges per student; discounts and scholarships as explicit adjustments.
- Recording cash, mobile-money and bank payments by hand, **printable receipts with sequential numbering**, voiding through reversal entries, student balances, arrears reports by class and instalment, and a **daily cash closing** per cashier.
- Then: mark entry, averages, ranks and **report cards for the chosen subsystem**.
- Audit log of sensitive actions.
- French and English UI.

**Out of the MVP:** parent portal, online payment collection, self-service signup with automated billing, offline mode, timetable, library, transport, payroll, analytics dashboards beyond basic counts and totals, AI features, native mobile app.

---

## 5. Domain model (initial)

```
School (tenant)
 ├── Membership (user ↔ school, role, status)  ← User is GLOBAL (one login, possibly many schools)
 ├── AcademicYear (status: planned | active | closed; one active per school)
 │    └── SchoolClass (level, name "6e A", academic_year)
 │         └── ClassSubject (subject, coefficient, teacher_membership)
 ├── Level (ordered: 6e…Tle / Form 1… ; section: fr | en)
 ├── Subject
 ├── Student (matricule unique per school, names, DOB, place of birth, sex)
 │    ├── StudentGuardian ↔ Guardian (name, phone, relationship)
 │    └── Enrollment (student, academic_year, class, status, enrolled_on, left_on)
 ├── Finance
 │    ├── FeeType (inscription, scolarité, APE…)
 │    ├── FeeSchedule (academic_year, level, fee_type) → Instalment (amount, due_on)
 │    ├── Charge (enrollment, instalment/fee_type, amount)          — immutable
 │    ├── Adjustment (enrollment, amount, reason, approved_by)      — discount/scholarship, immutable
 │    ├── Payment (student, amount, method, reference, received_by, received_at, receipt_no, status)
 │    ├── PaymentAllocation (payment → charge, amount)
 │    ├── PaymentVoid (payment, reason, voided_by)                   — reversal, never delete
 │    └── CashSession (cashier, opened/closed, expected vs counted)
 └── AuditLog (actor, action, subject, changes, ip, at) — append-only
```

`SchoolClass` avoids PHP's reserved word `class`.

### Invariants (enforced in the database where possible, otherwise in actions, and always tested)

1. Every tenant-owned row has a non-null `school_id`. A child row's `school_id` must equal its parent's. This is enforced with **composite foreign keys** `(school_id, parent_id) → parent(school_id, id)`, so the database itself refuses to attach another school's record even if the application has a bug.
2. At most one `active` academic year per school (partial unique index).
3. At most one active enrollment per student per academic year. A class change closes the old enrollment and opens a new one, so history is never lost.
4. Financial records (charges, adjustments, payments, allocations, voids) are **never updated or deleted**. Corrections are new rows. This can be enforced with a Postgres trigger that rejects `UPDATE`/`DELETE` on those tables, with a narrow exception for status columns.
5. Receipt numbers are **gapless and unique per school per year**, generated under a row lock in the same transaction as the payment.
6. Allocations for a payment never exceed the payment amount. Allocations to a charge never exceed the charge (overpayment becomes a credit, recorded explicitly).
7. A student's balance is **always derived** (charges − adjustments − non-voided allocations). It is never a stored, editable number. Caching it is acceptable later, provided it can be rebuilt from the records.
8. A published report card is a frozen snapshot. A correction produces a new version, and the old one is kept.

---

## 6. Tenant isolation and authorization strategy

### Options compared

| Approach                                               | Isolation strength                                             | Operational cost                                                                 | Verdict                                       |
| ------------------------------------------------------ | -------------------------------------------------------------- | -------------------------------------------------------------------------------- | --------------------------------------------- |
| Database per tenant                                    | Very strong                                                    | High: N migrations, N backups, connection management                             | Rejected for now. Overkill for small schools. |
| Schema per tenant                                      | Strong                                                         | Medium-high: migration fan-out, tooling friction                                 | Rejected                                      |
| **Shared schema + `school_id` + application scoping**  | Good, **if no query ever forgets the scope**                   | Low                                                                              | Necessary, but not sufficient on its own      |
| **+ PostgreSQL Row-Level Security (defense in depth)** | Strong: a forgotten scope returns zero rows instead of leaking | Medium: per-request session variable, separate DB roles, care with queue workers | **RECOMMENDED in combination**                |

### Recommended design (D4)

1. **Tenant context comes from the authenticated session, never from client input.** After login, the user picks an active school from their memberships (or it's automatic if they have only one). The middleware **re-validates the membership on every request**. URLs and forms never carry a `school_id` that the server trusts.
2. **Application layer:** a `BelongsToSchool` trait adds a global scope and auto-fills `school_id` on create. Policies check the role _and_ that the record belongs to the current school. Route model binding resolves through the scope, so another school's ID returns **404, not 403**, which avoids confirming that the record exists.
3. **Database layer:** composite foreign keys (invariant 1), plus **RLS policies** on every tenant table: `USING (school_id = current_setting('app.school_id')::bigint)`. The app connects as a **non-owner role** with `FORCE ROW LEVEL SECURITY` (table owners bypass RLS otherwise). Migrations run as the owner role.
4. **Session variable lifecycle:** set it per request in middleware with `set_config('app.school_id', ?, false)` and reset it at request end. Queue jobs carry `school_id` explicitly as a payload property, and a job middleware sets and resets the variable around each job. A long-running worker must never inherit the previous job's tenant.
5. **Platform admin** uses a separate area and a database role with explicit `BYPASSRLS` used only in that context. Every cross-tenant access is written to the audit log.
6. **Required tests:**
    - For every tenant-owned resource route, a test that school A's user requesting school B's record gets 404, and that list endpoints don't include B's rows.
    - An architecture test that every model in tenant modules uses `BelongsToSchool`.
    - A database test that every table with a `school_id` column has RLS enabled and forced (it queries `pg_class`/`pg_policies`), so a new table without RLS fails CI.
    - Job tests that check tenant context is set and then cleared.
7. **Exports, PDFs, file downloads, search, notifications:** files live under `schools/{school_id}/…` and are downloaded only through an authorized controller (no public URLs). Generated reports run through the same scoped queries.

**Limitations to document:** RLS does not protect against a compromised platform-admin path, a bug in the code that sets the session variable, or raw connections that use the owner role. That is why the application layer and the tests stay mandatory.

### Roles (D6)

Fixed roles on `Membership` (enum), with policies backed by a permission map in code. **No `spatie/laravel-permission` initially.** We add it only when schools actually ask for custom roles. Fewer moving parts, and every permission is reviewable in one file.

---

## 7. Authentication and account recovery

- **Staff:** email + password, using the starter kit's auth (Laravel Fortify-based; verify at scaffold time). Rate-limited login, secure session cookies, session regeneration on login, session invalidation on password change.
- **Two-factor authentication (TOTP):** **mandatory for platform admins and school owners/bursars**, optional for others. These accounts touch money and student data.
- **Staff without email** (common for some teachers): the school admin creates the account with a username and a temporary password, and the user must change it on first login. **Every admin-initiated reset is audited.**
- **Recovery:** email reset links when an email exists; otherwise a reset by the school admin. School-owner recovery when the owner has lost both password and 2FA goes through a **documented manual identity-verification runbook** run by platform support, never automatically.
- **Parents:** no accounts in the MVP. Phone/OTP login comes with the parent portal, because SMS OTP costs money per message.
- **Invitations:** signed, expiring, single-use links.

---

## 8. Deployment and operational strategy

### Phase approach

| Stage      | When                                                   | What                                                                             |
| ---------- | ------------------------------------------------------ | -------------------------------------------------------------------------------- |
| Local      | M1                                                     | Sail: app, Postgres, Mailpit. Queue worker and scheduler run as processes.       |
| CI         | M1                                                     | GitHub Actions with a Postgres service container. CI is the authoritative check. |
| Staging    | After M3 (so pilot schools can see real screens early) | One small VPS, HTTPS, seeded fake data only, deploy on merge to `main`.          |
| Production | M9, **only after the go-live checklist below**         | One VPS (≈4 vCPU / 8 GB): web, PHP-FPM, queue worker, scheduler, PostgreSQL.     |

Docker in production versus a provisioned Ubuntu server (Forge/Ploi-style) is decided in M9 (ADR). Both are viable. The real question is which one you can operate at 2 a.m.

### Go-live checklist (essential before any real school data)

- HTTPS everywhere, HSTS, secure headers, `APP_DEBUG=false`, secrets only in server environment variables.
- **Automated backups:** nightly `pg_dump` plus WAL archiving (or pgBackRest) to **off-site** object storage, encrypted. Uploaded files backed up as well.
- **A restore tested and timed into a fresh server**, with the procedure written as a runbook. Backups don't count until a restore has been tested.
- Error monitoring, uptime checks, and alerts when the queue or scheduler stops (heartbeat).
- Health endpoint (Laravel ships `/up`), extended to check the DB and queue.
- Safe migrations: additive first, no destructive change in the same deploy as the code that stops using a column, and a pre-deploy backup.
- Rollback procedure documented and rehearsed on staging.

### Operating-cost picture (rough estimates; verify prices before committing)

- **Fixed:** prod VPS + staging VPS + off-site storage + domain + email ≈ **€25–60/month**, almost independent of school count up to several dozen schools.
- **Variable per school:** SMS (the largest variable cost, so it needs per-school quotas or pass-through pricing), storage (small), **onboarding and support time (the real dominant cost)**.
- Revenue minus these costs minus your time is profit. A school paying a low fee that needs two days of data-entry help and weekly phone support may be loss-making. That's why the CSV import and an "onboarding kit" are in the MVP.

### Data residency [OPEN, legal]

Hosting in Europe gives ~100–150 ms latency, which is acceptable. **However**, under Cameroon's Law 2024/017 (§9), cross-border transfer of personal data appears to require authorisation. Local Cameroonian hosting avoids that issue but may bring reliability and support trade-offs. **This needs local legal advice before production.** It does not block development.

---

## 9. Security and privacy requirements

### Legal context [needs a lawyer; not legal advice]

- **Law No. 2024/017 of 23 December 2024 on Personal Data Protection** is Cameroon's first general data-protection law, with an **18-month transition period (ending around June 2026, so it has likely already passed)**.
- Reported obligations include **prior authorisation for processing**, consent and transparency, security measures, and **authorisation for cross-border transfers**.
- The supervisory authority depends on an implementing decree. Secondary sources disagree on whether the authority is operational and on the size of penalties.
- **Action:** get a short legal opinion before the pilot holds real data. In particular: are we a processor for schools or a controller? Are filings needed? Is hosting abroad allowed? What are the rules for **minors' data**?

### Technical requirements (built in from the start)

- Data minimisation: no national ID numbers, medical data or photos in the MVP unless there's a clear need. Guardian phone numbers are needed.
- Encryption in transit (TLS) and at rest for backups. Sensitive optional fields can be encrypted with Laravel's encrypted casts.
- Audit log for: logins and failed logins, role changes, password resets by admins, payments and voids, adjustments, report-card publication, exports, and platform-admin cross-tenant access.
- File uploads: allow-listed MIME types, size limits, stored privately, served only through authorized controllers, randomised names.
- Dependency scanning (`composer audit`, `npm audit`), secret scanning (gitleaks) in CI, Dependabot.
- Retention: define what happens to a school's data when it leaves (export, then deletion after N days). Financial records may have legal retention periods, to be confirmed with the lawyer.
- Incident response runbook before go-live: who is notified, how fast, and how the affected school is informed.
- **No production data in tests or fixtures, ever.** Use factories and a fake data seeder in French and English.

---

## 10. Claude Code agent and automation architecture

### Principles

- **Deterministic checks belong in scripts and CI. Judgment belongs to agents.** An AI reviewer never replaces a failing test.
- Subagents are **not persistent workers.** Each run starts with a fresh context and returns one final report. **The ticket file is the handoff medium:** the plan, review findings and verification log are written into `docs/roadmap/tickets/<ID>.md` by the orchestrator, which is the main session.
- Work is sequential, one ticket at a time on one branch, so no worktrees or parallel writers are needed.

### Evaluation of the proposed specialists

| Proposed                     | Verdict                                                                                                                                                                                                         | Reason                                                                                                                                                                                  |
| ---------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| A. Architecture/planning     | **Not a separate agent.** The main session plans, using plan mode plus a ticket template.                                                                                                                       | Planning needs the conversation context (your decisions). A fresh subagent would have to rebuild it, which costs more and produces worse plans.                                         |
| B. Implementation            | **Not a separate agent.** The main session implements.                                                                                                                                                          | With one ticket at a time, a handoff adds cost and no benefit. Revisit if we ever run independent tickets in parallel.                                                                  |
| C. Testing/quality           | **Folded into the reviewer, plus deterministic gates.**                                                                                                                                                         | The reviewer checks tests against acceptance criteria. "Do tests fail when behaviour breaks?" is answered mechanically by **Pest mutation testing** on the Tenancy and Finance modules. |
| D. Independent code reviewer | **CREATE: `reviewer`** (read-only)                                                                                                                                                                              | The independence is the point: it sees the diff, the ticket and the rules, but not the author's reasoning.                                                                              |
| E. Security reviewer         | **CREATE: `security-reviewer`** (read-only), **mandatory for tickets labelled `sensitive`** (auth, tenancy, finance, files, exports, platform admin). The built-in `/security-review` runs as a second opinion. | The checklist (tenant isolation, IDOR, finance integrity) is long and specific enough to deserve its own prompt.                                                                        |
| F. DevOps/release            | **Defer to M9.**                                                                                                                                                                                                | No infrastructure exists yet to specialise in.                                                                                                                                          |

### Agent specs (to be created in M1)

**`reviewer`**

- **Input:** ticket ID, base branch.
- **Output:** findings with severity (critical, high, medium, low), evidence (file:line), the rule or acceptance criterion violated, recommended fix, and a list of missing tests.
- **Tools:** Read, Grep, Glob, Bash (instructed to use only `git diff`/`git log` and test commands). It may not modify files.
- **Verification:** the orchestrator fixes critical/high findings, then re-runs the reviewer on the fix. Accepted lower-severity findings are recorded in the ticket.

**`security-reviewer`**: same contract, with a security checklist (§6, §7, §9) and the finance invariants (§5).

### Hooks and permissions (`.claude/settings.json`, project-level, committed)

- **Deny:** reading or editing `.env*`, `git push --force`, `migrate:fresh` / `db:wipe` outside the local environment, edits to `.github/workflows/` without an explicit request.
- **PostToolUse hook:** after Claude edits a file, auto-format it (Pint for PHP, Prettier for TS/TSX). Formatting is deterministic and cheap.
- **Not hooks:** test runs and static analysis. Those are slow, so they go through `composer check`, run at the verification step and in CI.

### Ticket lifecycle (summary; full version goes in `docs/engineering/development-workflow.md`)

`Ready → Planned (plan written into the ticket; your approval required if labelled high-risk) → In progress (branch feat/<ID>-slug) → Verified (composer check + npm run check pass locally, output pasted into the ticket) → Reviewed (reviewer, plus security-reviewer if sensitive; critical/high resolved) → PR (CI green) → Merged (by you)`.

**Claude never merges, deploys or touches production.** You merge.

---

## 11. Quality gates and testing strategy

| Gate                      | Tool                                                                                          | Local command           | CI      | Blocking            |
| ------------------------- | --------------------------------------------------------------------------------------------- | ----------------------- | ------- | ------------------- |
| PHP formatting            | Pint                                                                                          | `composer lint`         | ✓       | ✓                   |
| PHP static analysis       | Larastan (start at level 6, raise over time)                                                  | `composer analyse`      | ✓       | ✓                   |
| PHP unit + feature tests  | **Pest, against real PostgreSQL** (not SQLite, because RLS and constraints must be exercised) | `composer test`         | ✓       | ✓                   |
| Architecture rules        | Pest `arch()` tests                                                                           | part of `composer test` | ✓       | ✓                   |
| Mutation testing          | `pest --mutate` on Tenancy and Finance                                                        | `composer mutate`       | from M4 | ✓ above a threshold |
| TS types                  | `tsc --noEmit`                                                                                | `npm run types`         | ✓       | ✓                   |
| JS lint/format            | ESLint + Prettier                                                                             | `npm run lint`          | ✓       | ✓                   |
| Production build          | Vite                                                                                          | `npm run build`         | ✓       | ✓                   |
| Migrations                | `migrate:fresh` + `migrate:rollback` + `migrate` on a clean DB                                | in CI job               | ✓       | ✓                   |
| Dependencies              | `composer audit`, `npm audit --audit-level=high`, Dependabot                                  | —                       | ✓       | high/critical       |
| Secrets                   | gitleaks                                                                                      | —                       | ✓       | ✓                   |
| E2E (critical flows only) | Playwright (via Pest browser testing or standalone)                                           | `npm run e2e`           | from M3 | ✓                   |

**Aggregates:** `composer check` and `npm run check` run everything local. CI runs the same commands, with actions **pinned to commit SHAs**.

**Test policy:** every ticket's acceptance criteria map to named tests. Finance and tenancy rules need tests at the action level and the HTTP level. A bug fix starts with a failing test. **No agent may delete or weaken a test or a gate without a written justification in the ticket and your approval.**

---

## 12. Phased roadmap

Sizes: S ≈ ½–1 day, M ≈ 2–4 days, L ≈ 1–2 weeks of focused solo work. These are rough and depend on Q4.

| Milestone                            | Content                                                                                                                                                         | Size | Risk                                                                      |
| ------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---- | ------------------------------------------------------------------------- |
| **M1 Foundation**                    | Repo, environment, scaffold, quality tooling, CI, Claude control system, core docs, i18n                                                                        | M–L  | Environment setup on this Windows build                                   |
| **M2 Identity & tenancy**            | Schools, memberships, roles, tenant middleware, scope, composite FKs, **RLS**, audit log, auth hardening + 2FA, platform admin creates school and invites owner | L    | **Highest architectural risk**, so it's done early and done well          |
| **M3 Academic structure & students** | Years, levels, classes, subjects, students, guardians, enrollment history, **CSV/Excel import**, first staging deploy                                           | L    | Import edge cases (dirty Excel data)                                      |
| **M4 Fees & payments**               | Fee types and schedules, charges, adjustments, payments, allocation, receipts PDF, voids, balances, arrears report, cash closing                                | L    | **Financial integrity**, so it's labelled sensitive with mutation testing |
| **M5 Assessment & report cards**     | Periods, mark entry, coefficients, averages, ranks, report card PDF for the **chosen subsystem**, publication snapshot                                          | L    | Subsystem rules; real report-card samples needed from the pilot school    |
| **M6 Attendance**                    | Daily or per-period absences, totals feeding the report card                                                                                                    | M    | Teacher adoption                                                          |
| **M7 Notifications**                 | SMS driver abstraction, fee reminders, absence alerts, per-school quotas                                                                                        | M    | SMS provider reliability and cost                                         |
| **M8 Platform administration**       | Manual subscriptions (plan, period, paid status), school suspension, usage metrics                                                                              | M    | —                                                                         |
| **M9 Production readiness**          | Prod infrastructure, backups + **tested restore**, monitoring, security review, legal docs (privacy policy, school data-processing agreement)                   | M–L  | Legal/data-residency answer                                               |
| **M10 Pilot**                        | Onboard 1–2 schools, support runbook, feedback loop                                                                                                             | —    | —                                                                         |

**Suggested calendar [HYPOTHESIS]:** pilot fees and students with 1–2 friendly schools during the **2nd–3rd trimester of 2026–27** (roughly Jan–May 2027), report cards for the 3rd trimester, and the commercial launch sold in **June–August 2027 for the September 2027 school year**.

### Product and business validation plan

- **H1:** Owners will pay for fee and arrears control. **H2:** Principals will pay for report-card automation. **H3:** Per-student yearly pricing in tiers fits school budgets and how they collect money (in instalments).
- **Evidence to collect before expanding scope:** 8–10 structured interviews (owners, principals, bursars); 3 real report-card samples per target subsystem; current fee-collection process and tools (Excel? a notebook? a competitor?); what they pay today for anything similar; at least one letter of intent or paid pilot.
- **Pricing:** not finalised. Model the options (per student per year, tiers, termly instalments aligned with the school's own fee calendar) once interviews are done. Competitor price lists are inputs, not proof.
- **Acquisition [HYPOTHESIS]:** direct visits and referrals (owner networks, private-school associations), with a demo on staging populated with realistic fake data.

---

## 13. Decisions requiring your approval

| #   | Decision                                                                                                                                             | My recommendation                          |
| --- | ---------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------ |
| D1  | Stack: Laravel 13, PHP 8.4, Inertia 3, React 19, TS, Tailwind 4, official React starter kit, PostgreSQL, no Redis yet                                | Approve                                    |
| D2  | Move the project out of `htdocs` to a path without spaces (e.g. `C:\dev\school-saas`)                                                                | Approve                                    |
| D3  | Local environment: WSL2 + Docker Desktop + Sail **(recommended)**, or native PostgreSQL on Windows plus enabling `pdo_pgsql` in XAMPP PHP (fallback) | Depends on Q3                              |
| D4  | Tenancy: shared schema + `school_id` + app scope + composite FKs + **RLS from M2**                                                                   | Approve                                    |
| D5  | Tenant chosen through the authenticated user's membership; no subdomains initially                                                                   | Approve                                    |
| D6  | Fixed roles in code, no spatie permissions initially                                                                                                 | Approve                                    |
| D7  | Roadmap order: fees (M4) before report cards (M5) before attendance (M6)                                                                             | Approve, or swap M4/M5 after Q2            |
| D8  | MVP exclusions: no parent portal, no online payments, no self-signup, no offline mode                                                                | Approve                                    |
| D9  | First target segment and subsystem                                                                                                                   | **Your call (Q1)**                         |
| D10 | Agents: only `reviewer` and `security-reviewer` now; planning and implementation in the main session                                                 | Approve                                    |
| D11 | GitHub: private repository under your account; you merge PRs                                                                                         | Approve, and tell me the account/repo name |
| D12 | Get legal advice on Law 2024/017 before any real data                                                                                                | Approve (non-technical action for you)     |

---

## 14. Milestone 1: precise implementation plan

**Goal:** an empty but production-shaped application. You can clone it, start it with one command, run all checks green locally and in CI, and every rule and document needed for M2 is in place.

### Prerequisites (you)

- P1. Answer Q3. If Docker: enable WSL2 (`wsl --install`, reboot), install Docker Desktop, and confirm `docker run hello-world` works **inside WSL**. The project will live in the WSL filesystem (`~/code/school-saas`) for performance. Bind mounts from `C:\` into Docker are very slow.
- P2. Create an empty private GitHub repository (no README) and share the URL. Optionally install `gh` and run `gh auth login`.
- P3. Confirm `git config --global user.email`.

### Tickets

Each ticket file will contain: ID, scope, dependencies, acceptance criteria, test expectations, definition of done.

| ID          | Title                                | Depends on       | Acceptance criteria (summary)                                                                                                                                                                                                                                                                                                          |
| ----------- | ------------------------------------ | ---------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **FND-001** | Repository bootstrap                 | P1–P3            | Git repo on `main`; `.gitignore` covers `.env*`, `vendor`, `node_modules`, `storage` artefacts; `.editorconfig`; `.gitattributes` (LF line endings, which matters on Windows); first commit pushed.                                                                                                                                    |
| **FND-002** | Laravel scaffold                     | FND-001          | Laravel 13 + React starter kit installed. Sail with `pgsql` + `mailpit`. `APP_LOCALE=fr`, `APP_FALLBACK_LOCALE=en`, `APP_TIMEZONE=UTC` (display in `Africa/Douala`). DB/queue/cache/session use the database driver. `sail up` → `/up` returns 200; register/login works locally.                                                      |
| **FND-003** | Module skeleton & architecture tests | FND-002          | `app/Domain/` structure, a first ADR, Pest arch tests (no `dd`/`dump`, strict types, controllers don't use `DB` directly). The tests pass, and **a deliberate violation makes them fail** (shown in the ticket log).                                                                                                                   |
| **FND-004** | PHP quality tooling                  | FND-002          | Pint config, Larastan level 6 with a zero-error baseline (no ignored errors), Pest configured against Postgres (separate `testing` database). `composer lint`, `analyse`, `test` and `check` scripts.                                                                                                                                  |
| **FND-005** | Frontend quality tooling             | FND-002          | ESLint + Prettier + `tsc --noEmit` scripts; `npm run check` = lint + types + build, all green.                                                                                                                                                                                                                                         |
| **FND-006** | i18n foundation                      | FND-002          | Laravel translations for fr/en exposed to React through a typed helper; starter-kit auth pages translated to French; a locale switcher; a test that checks a French string renders.                                                                                                                                                    |
| **FND-007** | CI pipeline                          | FND-004, FND-005 | GitHub Actions: `php` job (Postgres 17 service, composer validate/lint/analyse/test, migrate/rollback check), `js` job (`npm ci` + check), `security` job (composer/npm audit, gitleaks). Actions pinned by SHA, Dependabot configured, CI green on a PR.                                                                              |
| **FND-008** | Claude Code control system           | FND-001          | Concise `CLAUDE.md` (commands, rules, pointers to docs); `.claude/settings.json` (deny rules, format hook); `.claude/agents/reviewer.md` and `security-reviewer.md`; ticket template. The deny rules are tested (an attempt to read `.env` is blocked).                                                                                |
| **FND-009** | Core documentation                   | FND-002          | `docs/product/PRD.md`, `docs/architecture/overview.md`, `domain-model.md`, `decisions/0001…` (stack, tenancy, money, roles), `docs/security/tenant-isolation.md`, `docs/engineering/testing.md`, `development-workflow.md`, `docs/roadmap/` (milestones + M2 tickets). This discovery report gets distilled into them, not duplicated. |
| **FND-010** | M1 review & demo                     | all              | `reviewer` run on the full M1 diff, findings resolved or accepted, completion report (what changed, commands actually run and their output, remaining gaps).                                                                                                                                                                           |

**Deliberately not in M1:** any domain table (schools, students…). Tenancy design belongs to M2, with its own reviewed plan, so it isn't rushed into scaffolding.

---

## 15. Product-owner answers and resulting decisions (2026-10-23)

### Answers

| Q               | Answer                                                                                                                                                                                                 | Consequence                                                                                                                                                                                                                                                                                                                                               |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Q1 Segment      | **All cycles (nursery, primary, secondary). A school can be francophone, anglophone or bilingual.** [CONFIRMED]                                                                                        | Each school configures **sections** (fr / en) and **cycles** (nursery / primary / secondary). Every `Level` belongs to one section and one cycle. Fees work the same way across all of them. Grading (later) becomes a pluggable rule set chosen per (section, cycle). This needs an ADR before M5. The data model must hold sections and cycles from M3. |
| Q2 Pain / payer | **Unpaid balances hurt most. Communication comes second:** reminders, information for staff, school-activity announcements, and parents viewing their child's details. **The owner pays.** [CONFIRMED] | Fees stay first (D7 confirmed). Arrears reminders to guardians become the first communication feature, because they link communication directly to the biggest pain. Staff announcements and the parent view of child details move up the roadmap, ahead of attendance.                                                                                   |
| Q3 Environment  | Recommended option: **WSL2 + Docker + Sail** [CONFIRMED]                                                                                                                                               | Installed by the product owner. Time-box: if it isn't working within a day, fall back to native PostgreSQL on Windows.                                                                                                                                                                                                                                    |
| Q4 Target       | **First week of November 2026** (read as Fri 2026-11-06), to have **something to present** to prospects [CONFIRMED, interpretation pending]                                                            | Reframed from "pilot" to **demo build on fake data**. A pilot with real school data stays gated by the go-live checklist (§8) and legal advice (§9).                                                                                                                                                                                                      |
| Q5 Company      | Not yet. Prospecting comes after the demo exists.                                                                                                                                                      | Nothing paid or contractual is needed for the demo. A company is needed before real data, SMS contracts or billing.                                                                                                                                                                                                                                       |
| D1–D12          | Product owner chose the recommended options [CONFIRMED]                                                                                                                                                | —                                                                                                                                                                                                                                                                                                                                                         |

### Revised plan: Demo Sprint (2026-10-26 → 2026-11-06)

The quality gates stay in place. When time runs short, the scope shrinks.

| Week | Content                                                                                                                                                                                                                                                                                                                          |
| ---- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1    | Environment; M1 trimmed (scaffold, Pint, Larastan, Pest, TS checks, CI, CLAUDE.md, two agents, core ADRs); M2 core (schools, sections, cycles, memberships, fixed roles, tenant middleware and scope, composite FKs, **cross-tenant tests**, platform admin creates school and invites owner)                                    |
| 2    | Academic years, levels, classes; students, guardians, enrollment; CSV import; fee schedules with _tranches_, charges, payments, printable receipts, voids, balances, **arrears report by class/instalment**; **arrears reminder preview with a fake SMS driver**; realistic bilingual demo seeder; local demo walkthrough script |

**Deferred out of the demo (needed before any real data):** Row-Level Security (cheap to add while there are few tables), mandatory 2FA, mutation testing, cash closing, production deployment and tested restore, legal documents.

**Order of cuts if we slip:** CSV import → seeder only; PDF receipts → print-friendly HTML; reminder preview → dropped.

## Sources consulted

- Laravel / Inertia versions (from Packagist starter kits listing laravel/framework ^13 and inertia-laravel ^3; to be re-verified with the official installer at scaffold time):
    - https://packagist.org/packages/shipfastlabs/modern-react-starter-kit
    - https://packagist.org/packages/lattice-php/starter-kit
- Cameroon Law No. 2024/017 (secondary sources, which conflict on penalties and on the authority's status):
    - https://lexafrica.com/2025/10/cameroon-data-protection-law-compliance/
    - https://www.dlapiperdataprotection.com/?c=CM&t=law
    - https://www.afriwise.com/resources/cameroons-evolving-data-privacy-landscape
    - https://www.mofo.com/privacy-library/cameroon
