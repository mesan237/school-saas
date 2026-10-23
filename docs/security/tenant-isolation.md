# Tenant isolation and authorization

Decision: ADR-0002. This document holds the **rules that code and reviews check against**.

## Tenant context
- Set **only** from the authenticated user's active membership, stored in the session and **re-validated every request** (the membership exists, is active, and the school is active).
- `school_id` from route parameters, query strings, forms or headers is never trusted. FormRequests don't accept it.
- Guests and platform-admin routes have no tenant context. Tenant-scoped queries without context **throw**; they don't return everything.

## Models and queries
- Tenant-owned models use `BelongsToSchool`, which adds a global scope and auto-fills `school_id` on create (and makes it non-mass-assignable).
- `withoutGlobalScope(s)` and raw `DB::` queries on tenant tables are allowed only in reviewed platform-admin or migration code, with an explicit school filter.
- Route model binding goes through the scope: another school's ID → **404** (not 403).

## Schema
- `school_id bigint NOT NULL`, indexed, FK to `schools`.
- A unique `(school_id, id)` on parent tables. Child FKs are composite: `(school_id, parent_id) → parent(school_id, id)`.
- Uniqueness rules are per school (e.g. `UNIQUE (school_id, matricule)`).
- RLS policies (TEN-004, before real data).

## Background work and side channels
- Jobs carry `school_id` explicitly and set and reset the tenant context around `handle()`.
- Exports, PDFs, search, notifications and reports reuse the scoped queries.
- Files are stored at `schools/{school_id}/…` on a private disk and served only through an authorized controller.

## Platform admin
A separate route group, guard check and layout. Every cross-tenant read or write is audited (who, which school, what, why).

## Required tests (per tenant resource)
1. A user of school A GET/PUT/DELETE on school B's record → 404, and B's record is unchanged.
2. School A's list and search endpoints never include B's rows.
3. A forged `school_id` in input is ignored or rejected.
4. A user with no active membership → cannot reach tenant routes.

## Known limitations
Until TEN-004, isolation depends on the application layer plus composite FKs. RLS doesn't protect against bugs in the code that sets the context, or against connections using the owner role. Tests and reviews remain mandatory.
