# Roadmap

Ticket IDs: `<AREA>-<NNN>`. One file per ticket in `tickets/`, from `tickets/_TEMPLATE.md`.
Areas: FND foundation · TEN tenancy · AUTH auth · ACA academics · STU students · FIN finance · COM communication · OPS operations.

## Current goal — Demo Sprint (2026-10-26 → 2026-11-06)

A demo build on **fake data** for prospecting. It is not a real-data pilot (see discovery report §15).

| ID      | Title                                                                                  | Labels               | Status                                  |
| ------- | -------------------------------------------------------------------------------------- | -------------------- | --------------------------------------- |
| FND-001 | Repository bootstrap                                                                   | routine              | done (local; GitHub remote pending)     |
| FND-002 | Laravel scaffold + app config (fr locale, name, timezone)                              | routine              | done                                    |
| FND-003 | Module skeleton + architecture tests (Pest)                                            | routine              | done                                    |
| FND-004 | PHP quality tooling (Pint, Larastan, Pest)                                             | routine              | done                                    |
| FND-005 | Frontend quality tooling                                                               | routine              | done (shipped by starter kit, verified) |
| FND-006 | i18n foundation (fr/en)                                                                | routine              | done                                    |
| FND-007 | CI on GitHub Actions with PostgreSQL                                                   | routine              | blocked: GitHub repo URL                |
| FND-008 | Claude Code control system                                                             | routine              | done                                    |
| FND-009 | Core documentation + ADRs                                                              | routine              | done                                    |
| FND-010 | Local PostgreSQL (Docker/Sail or native) + switch tests to Postgres                    | routine              | blocked: environment                    |
| FND-011 | Translate remaining starter-kit pages (register, reset, settings, dashboard) to French | routine              | ready                                   |
| TEN-001 | Schools, sections, cycles, memberships, fixed roles                                    | sensitive, high-risk | ready (needs FND-010)                   |
| TEN-002 | Tenant context middleware, `BelongsToSchool`, composite FKs, cross-tenant tests        | sensitive, high-risk | —                                       |
| TEN-003 | Platform admin creates school + invites owner; disable public registration             | sensitive            | —                                       |
| ACA-001 | Academic years, levels, classes, subjects                                              | routine              | —                                       |
| STU-001 | Students, guardians, enrollment history                                                | sensitive            | —                                       |
| STU-002 | CSV/Excel import                                                                       | sensitive            | —                                       |
| FIN-001 | Fee types, schedules, instalments                                                      | sensitive            | —                                       |
| FIN-002 | Charges, payments, allocation, receipts, voids                                         | sensitive, high-risk | —                                       |
| FIN-003 | Balances + arrears report by class and instalment                                      | sensitive            | —                                       |
| COM-001 | Arrears reminder preview (fake SMS driver)                                             | sensitive            | —                                       |
| OPS-001 | Bilingual demo seeder + demo walkthrough script                                        | routine              | —                                       |

## Before any real school data (post-demo, mandatory)

RLS (TEN-004) · mandatory 2FA for owners and bursars (AUTH-002) · audit log coverage review · cash closing (FIN-004) · production deploy + **tested restore** · legal opinion on Law 2024/017 · privacy policy + school data-processing agreement.

## Later milestones

Report cards (per section and cycle rule sets) · staff and parent announcements · parent view of child details · attendance · subscriptions admin · offline attendance (separate design).
