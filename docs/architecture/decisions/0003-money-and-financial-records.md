# ADR-0003 — Money and financial records

- **Status:** Accepted (PO, 2026-10-23)

## Decision
- Amounts are **integer XAF** in `bigint` columns, plus a `currency` column (default `XAF`) for future markets. No floats, ever.
- **Financial records are append-only:** charges, adjustments, payments, allocations, voids. A correction is a new reversing row with a reason and an actor. Updates and deletes are rejected (by a DB trigger from FIN-002).
- **A balance is always derived:** charges − adjustments − allocations of non-voided payments. Any cached balance must be rebuildable from the records.
- **Receipt numbers are gapless per school per year**, allocated under a row lock in the same transaction as the payment.

## Consequences
- Reports aggregate records, so indexes on `(school_id, student_id)` and `(school_id, academic_year_id)` are needed.
- No "edit payment" button exists. The UI offers "void and re-record".
