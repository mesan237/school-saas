# ADR-0002 — Multi-tenancy model

- **Status:** Accepted (PO, 2026-10-23). RLS is deferred until before real data (2026-10-23).

## Decision
**Shared database and shared schema.** Every tenant-owned table has `school_id`. Isolation comes in three layers:
1. **Application:** a `BelongsToSchool` trait adds a global scope and fills `school_id` on create. Policies check role and ownership. Foreign IDs resolve to **404**.
2. **Schema:** composite foreign keys `(school_id, parent_id) → parent(school_id, id)`, so the database refuses to link records across schools.
3. **PostgreSQL RLS** (defense in depth): policies on `current_setting('app.school_id')`, the app connecting as a non-owner role with `FORCE ROW LEVEL SECURITY`. **Implemented in TEN-004, before any real school data.**

Tenant context comes from the **authenticated user's membership**, chosen at login and stored in the session, and re-validated on every request. It never comes from request input. Subdomains are not used initially.

## Alternatives rejected
- Database per tenant or schema per tenant: migration and backup fan-out are too expensive for small schools.
- Application scoping alone: one forgotten scope leaks data, which is why layers 2 and 3 exist.

## Consequences
- Every tenant table needs cross-tenant tests and composite FKs (enforced by review, and by an automated table check from TEN-004).
- Platform-admin cross-tenant access goes through a separate, audited path.
- Detailed rules are in `docs/security/tenant-isolation.md`.
