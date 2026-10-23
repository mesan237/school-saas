# FND-001…009 + FND-007 — Project foundation

- **Status:** reviewed (CI run on PostgreSQL pending → then `pr-open`)
- **Milestone:** M1 Foundation
- **Labels:** routine
- **Branch:** feat/FND-foundation (PR → main)

## Scope

In: repository; Laravel 13 React starter kit; Pest + architecture rules; fr/en i18n; Claude Code control system; core docs and ADRs; CI on PostgreSQL with security scanning.
Out: any domain table (tenancy starts in TEN-001, after FND-010 provides local PostgreSQL). Display timezone (`Africa/Douala`) is deferred to the first ticket that shows dates (ACA-001); storage stays UTC.

## Acceptance criteria

- [x] Untouched scaffold committed separately on `main`; all project work is a reviewable diff
- [x] `composer lint:check`, Larastan, Pest, `npm run check`, `npm run types:check` and `npm run build` pass locally
- [x] French is the default; locale switch validated and allow-listed; every `t()` key exists in `lang/fr.json`
- [x] Architecture rules each shown to fail on a planted violation
- [x] Claude Code: secrets and destructive commands denied; edits auto-formatted; read-only reviewer agents
- [ ] CI green on GitHub with the test process confirmed on PostgreSQL (guard test `tests/Feature/Database/DatabaseDriverTest.php`)

## Verification log (2026-10-23, local Windows/XAMPP, SQLite)

- Baseline scaffold before changes: Pint pass · Larastan L7 0 errors · 40/40 tests · tsc, lint, build pass
- After review fixes: `vendor/bin/pest` → 58 tests, 57 passed, 1 skipped (the CI-only PostgreSQL guard), 201 assertions
- `composer lint:check` → passed · `phpstan analyse` → 0 errors · `npm run types:check` → clean · `npm run check` → pass · `npm run build` → built
- `composer audit` → no advisories · `npm audit --omit=dev` → 0 vulnerabilities · full `npm audit` → 3 critical + 4 moderate, all dev-only (see FND-012)
- Probes (each confirmed failing, then removed): `DB::` in a controller; `dd()` (exposed that Pest's php preset misses Laravel helpers, so a rule was added); an untranslated `t()` key; removing the SetLocale default-locale fallback
- Format hook: formats project PHP and TS; ignores a sibling folder `school-saas-old`; exits 0 on a bad project root

## Review findings (reviewer agent, 2026-10-23: CHANGES REQUIRED → resolved)

| #   | Sev    | Finding                                                                                 | Resolution                                                                                              |
| --- | ------ | --------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| 1   | high   | Documented `vendor/bin/phpunit` command crashes since Pest tests were added             | Fixed in CLAUDE.md and testing.md (reproduced first)                                                    |
| 2   | medium | `composer test` can't run locally on XAMPP                                              | Documented the local equivalent until FND-010                                                           |
| 3   | medium | Permission rules ignored PowerShell, missed `.env` variants and `push <branch> --force` | Tightened (deny-only additions); still guard rails, not a security boundary                             |
| 4   | medium | Nothing proved the PHPUnit process uses PostgreSQL in CI                                | Guard test added; confirm on the first CI run                                                           |
| 5   | medium | gitleaks may get 403 on PR events                                                       | `pull-requests: read` added. Personal repo, so no licence; SHA checked via GitHub API = official v3.0.0 |
| 6   | medium | Unsupported locale crashes the UI; `APP_LOCALE` not validated                           | Frontend optional chaining + server fallback to `fr`; test added                                        |
| 7   | low    | `<html lang>` stale after switching                                                     | Synced in `useTranslation`                                                                              |
| 8   | low    | `back()` trusts Referer                                                                 | Accepted: not exploitable (CSRF, same-site). Test added for the no-Referer default                      |
| 9   | low    | Placeholder replacement order                                                           | Longest-first, like Laravel                                                                             |
| 10  | low    | Key-coverage test misses `Page.layout` titles                                           | Documented as a known limit; handled in FND-011                                                         |
| 11  | low    | Arch test names and docs overclaimed                                                    | Renamed; overview corrected                                                                             |
| 12  | low    | Hook path-prefix check                                                                  | Trailing separator + false-root guard; verified                                                         |
| 13  | low    | `ci:check` described as "everything CI runs"                                            | Reworded                                                                                                |
| 14  | low    | Middleware order in diagram                                                             | Corrected                                                                                               |

Missing tests added: plausible tampered locale (`de`), non-string locale, GET → 405, no-Referer redirect, locale survives login, misconfigured default.
Not added: frontend unit test for `translate()` (no JS test runner configured yet; the dev toolchain's vitest is the vulnerable package in FND-012).

## Open decisions for the product owner

1. **FND-012 accepted risk:** dev-only npm advisories reported, not blocking, until `vite-plus` can be upgraded.
2. **Locale on logout:** it currently resets to French because logout invalidates the session. Proposal: store the language on the user in a later ticket.
