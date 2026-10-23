# ADR-0001 — Application stack

- **Status:** Accepted (PO, 2026-10-23)

## Context

A small team (one developer plus AI assistance) is building a CRUD-heavy, multi-tenant business application with strict authorization, financial records and printable outputs. An offline PWA for attendance may come later.

## Decision

- **Laravel 13** (PHP 8.4) as a **modular monolith**, scaffolded from the official `laravel/react-starter-kit` (Fortify auth with 2FA and passkeys, Wayfinder typed routes, Larastan level 7, Pint).
- **Inertia 3 + React 19 + TypeScript + Tailwind 4.** The starter kit's shadcn/ui components are the base of the design system.
- **PostgreSQL** as the only database.
- **No Redis initially.** Queue, cache and sessions use the database driver.
- Module code lives in `app/Domain/<Module>/` (Models, Actions, Policies…). Controllers live in `app/Http/Controllers/<Module>/`. No repository layer.

## Alternatives rejected

- **Livewire:** simpler, but weaker for a future offline PWA, and React skills don't transfer.
- **SPA + separate API:** a larger security and versioning surface with no current need.
- **Microservices:** operational overhead without justification.
- **MySQL:** no RLS, weaker constraint features.

## Consequences

- One deployable unit. Module boundaries are enforced by architecture tests, not by deployment.
- Redis can be added when it's measured to be needed (queue throughput, cache latency).
