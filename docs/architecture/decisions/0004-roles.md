# ADR-0004 — Roles and permissions

- **Status:** Accepted (PO, 2026-10-23)

## Decision
Fixed roles stored on the **membership** (user ↔ school): `owner`, `principal`, `bursar`, `secretary`, `teacher`. Platform admins are a separate flag on the user, with a separate area.
A single permission map in code (role → abilities) backs Laravel Policies. **No `spatie/laravel-permission`** until schools need custom roles.

## Consequences
- Every permission is reviewable in one file, and tests can iterate over the map.
- One user can hold different roles in different schools (e.g. a teacher at two schools).
