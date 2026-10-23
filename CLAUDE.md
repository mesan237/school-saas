# School SaaS — project rules

Multi-tenant school management SaaS (Cameroon first; nursery/primary/secondary; francophone, anglophone or bilingual schools).
Stack: Laravel 13 · PHP 8.4 · Inertia 3 · React 19 · TypeScript · Tailwind 4 · PostgreSQL · Fortify auth.

## Commands

- `composer test` — Pint check + Larastan + test suite (run before claiming any PHP change works)
- `composer lint` — auto-fix PHP formatting
- `npm run check` — frontend format + lint; `npm run types:check` — TypeScript; `npm run build` — production build
- `composer ci:check` — the local subset of CI (CI also runs the build, a migration round-trip, audits and gitleaks)
- **Until local PostgreSQL exists (Windows/XAMPP, see ADR-0005)**, `composer test` can't load a DB driver. Run instead:
  `composer lint:check`, `composer types:check`, then `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/pest`

## Non-negotiable rules

1. **Tenant isolation.** Every tenant-owned model uses `BelongsToSchool`. Tenant context comes only from the authenticated session/membership, never from request input. Another school's record must 404. See `docs/security/tenant-isolation.md`.
2. **Financial records are append-only.** Never update or delete charges, payments, allocations, adjustments; correct with reversal rows. Money is integer XAF (`bigint`).
3. **Authorization** goes through Policies; validation through FormRequests. Controllers stay thin; workflows live in `app/Domain/<Module>/Actions`.
4. **Schema changes only via migrations.** Never edit a merged migration.
5. **Never** read or write `.env*` secrets, weaken/delete a test or quality gate, merge, deploy, or touch production. Ask the product owner.
6. All user-facing strings are translatable (fr default, en).
7. Don't claim a check passed unless you ran it in this session; paste the real result into the ticket.

## Workflow

One ticket at a time, on branch `feat/<TICKET-ID>-slug`. Lifecycle and handoff format: `docs/engineering/development-workflow.md`. Tickets: `docs/roadmap/tickets/`.
Reviews: run the `reviewer` agent on every ticket; also `security-reviewer` when the ticket is labelled `sensitive`.

## Where things are

- Decisions: `docs/architecture/decisions/` (ADRs) — read before changing architecture
- Domain model & invariants: `docs/architecture/domain-model.md`
- Testing rules: `docs/engineering/testing.md`
- Discovery snapshot (historical, 2026-10-23): `docs/discovery/discovery-report.md`
