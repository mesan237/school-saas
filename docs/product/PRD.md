# Product requirements

- **Status:** Living document. Last decision update 2026-10-23.

## Problem

Private schools in Cameroon lose cash flow to **unpaid fee balances**. They track _tranches_ in notebooks or Excel, can't see who owes what by class, and remind parents by hand. Communication with parents and staff is also informal.

## Customer

- **Buyer:** the school owner (_promoteur/fondateur_). [CONFIRMED]
- **Daily users:** bursar, secretary, principal, then teachers. Parents come later.
- **Schools:** nursery, primary and secondary; francophone, anglophone or bilingual. [CONFIRMED]

## Current goal

A **demo on fake data by 2026-11-06**, used to prospect owners. [CONFIRMED]

## Demo scope (must work end-to-end)

1. Platform admin creates a school (sections, cycles) and invites the owner.
2. Owner sets up the academic year, levels, classes and staff accounts with roles.
3. Secretary registers or imports students and guardians, and enrolls them in classes.
4. Owner defines fee schedules with _tranches_ and due dates per level.
5. Bursar records payments (cash, mobile money, bank) and prints receipts. A wrong payment can be voided, never edited.
6. Owner sees **balances and arrears by class and instalment**, and previews reminder SMS messages to guardians.

## Next (after the demo, ordered)

Real-data readiness (RLS, 2FA, backups, legal) → report cards (per section and cycle) → staff and parent announcements → parent view of child details → attendance.

## Out of scope for now

Online payment collection, self-signup and automated billing, offline mode, native app, timetable, payroll, AI features.

## Hypotheses to validate while prospecting

- H1: Owners will pay for arrears control. H2: A reminder feature measurably raises collection. H3: Per-student yearly tiers, paid in instalments, fit school budgets.
- Evidence to collect: 8–10 owner interviews, real fee schedules and receipts, current tools and their cost, at least one letter of intent.
