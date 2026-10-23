# ADR-0005 — Local environment and test database

- **Status:** Accepted (PO, 2026-10-23)

## Decision
- **Target:** WSL2 + Docker + Laravel Sail (PostgreSQL, Mailpit), with the project in the WSL filesystem. This gives parity with the Linux production servers.
- **Interim (started 2026-10-23, before WSL/Docker exist):** develop natively on Windows in `C:\dev\school-saas` (outside XAMPP `htdocs`, which Apache serves) with XAMPP PHP 8.4. Tests use the starter kit's SQLite in-memory database, with the driver loaded per command.
- **Exit condition for the interim:** local PostgreSQL available (FND-010), which **must happen before TEN-001**. Tenancy tests are not trustworthy on SQLite.
- Fallback if WSL2/Docker can't be installed on this Windows build within a day: native PostgreSQL for Windows, plus enabling `pdo_pgsql` in XAMPP PHP.

## Consequences
- Moving into WSL later is a `git clone` from GitHub, so nothing is lost.
- CI (PostgreSQL service) is the authoritative check throughout.
