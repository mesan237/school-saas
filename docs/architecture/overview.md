# Architecture overview

A modular monolith (ADR-0001). One Laravel app serves Inertia/React pages. PostgreSQL is the single store. Queue and scheduler run on the database driver.

```
Browser (React via Inertia) ──HTTP──▶ Laravel
                                       ├─ Middleware: session → locale → auth → tenant context (membership)
                                       ├─ Controllers (thin) → FormRequest (validate) → Policy (authorize)
                                       ├─ Domain Actions (workflows, transactions)
                                       ├─ Eloquent models (BelongsToSchool scope)
                                       └─ Queue jobs (carry school_id), Scheduler
                                    ──▶ PostgreSQL · private file storage · SMS/email drivers
```

## Modules (`app/Domain/<Module>`)

| Module        | Owns                                              | May depend on                          |
| ------------- | ------------------------------------------------- | -------------------------------------- |
| Tenancy       | School, Section, Cycle, Membership, TenantContext | —                                      |
| Academics     | AcademicYear, Level, SchoolClass, Subject         | Tenancy                                |
| Students      | Student, Guardian, Enrollment, Import             | Tenancy, Academics                     |
| Finance       | Fee schedules, Charges, Payments, Receipts        | Tenancy, Academics, Students           |
| Communication | Messages, SMS driver                              | Tenancy, Students, Finance (read-only) |
| Audit         | AuditLog                                          | — (used by all)                        |

Rules: dependencies point one way. An architecture rule per module is added in the ticket that creates that module (`tests/Arch`); none exist yet because `app/Domain` is still empty. Cross-module calls go through Actions or models, never through another module's controllers.

## Conventions

- Workflow = an Action class with one public `handle()` / `__invoke()`, wrapped in a DB transaction when it writes more than one row.
- Validation in FormRequests; authorization in Policies, using the role map (ADR-0004).
- Times stored in UTC and displayed in `Africa/Douala`. Money as integer XAF (ADR-0003).
- UI strings are translated; `fr` is the default, `en` the fallback.
