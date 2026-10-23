# Testing strategy

## Levels

| Level        | What                                                                                     | Where                       |
| ------------ | ---------------------------------------------------------------------------------------- | --------------------------- |
| Unit         | Pure rules: balance computation, average and rank computation, receipt numbering format  | `tests/Unit`                |
| Feature      | HTTP + DB through Laravel: authorization, validation, tenant isolation, workflows        | `tests/Feature`             |
| Architecture | Structural rules (module boundaries, `BelongsToSchool` on tenant models, no debug calls) | `tests/Arch` (from FND-003) |
| Database     | Constraints, RLS presence on every tenant table (from M2)                                | `tests/Feature/Database`    |
| E2E          | Only critical flows: log in → record payment → print receipt                             | Playwright (from M3)        |

## Database used by tests

- **Target: PostgreSQL.** Tenant isolation relies on Postgres features (composite FKs, partial unique indexes, RLS), and SQLite can't exercise them.
- **Temporary (until local Postgres exists, see ADR-0005):** the starter kit's SQLite in-memory setup. On XAMPP PHP the driver must be loaded per command:
  `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/pest` (not `vendor/bin/phpunit`: it refuses to run Pest-style tests)
- CI switches to Postgres in FND-007. Any test that needs Postgres-only features is marked so it can't silently pass on SQLite.

## Mandatory tests by change type

- **Every tenant-owned resource:** a user of school A requesting school B's record gets 404, and school A's lists exclude school B's rows.
- **Every write endpoint:** unauthenticated → redirect; wrong role → 403; invalid input → 422 with errors; valid → persisted plus audit row (where required).
- **Finance:** amounts and balances computed from records; void creates a reversal and the original is unchanged; receipt numbers are unique and sequential under concurrency.
- **Bug fix:** a failing test reproducing the bug, committed with the fix.

## Proving tests can fail

For each new rule, temporarily break the implementation once and confirm the test fails. Record this in the ticket log. From M4, `pest --mutate` runs on the Tenancy and Finance modules.

## Fixtures

Factories and seeders only, with fake names and phone numbers (fr and en). **Never production data.**
