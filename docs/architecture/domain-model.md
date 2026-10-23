# Domain model

This is the living version. Update it in the same PR as any migration that changes it.

## Entities (✓ = built; others planned for the demo sprint)
```
User (GLOBAL: one login, may belong to several schools)
School (tenant)
 ├── Section            fr | en            (a bilingual school has both)
 ├── Cycle              nursery | primary | secondary
 ├── Membership         user ↔ school, role (owner|principal|bursar|secretary|teacher), status
 ├── AcademicYear       planned | active | closed
 ├── Level              ordered; belongs to one Section and one Cycle (e.g. "6e" fr/secondary, "Class 1" en/primary)
 ├── SchoolClass        level + academic_year + name ("6e A")
 ├── Subject
 ├── Student            matricule (unique per school), names, DOB, place of birth, sex
 │    ├── Guardian ↔ StudentGuardian (relationship)  — name, phone
 │    └── Enrollment    student + academic_year + class, status, enrolled_on, left_on
 ├── FeeType            inscription, scolarité, APE…
 ├── FeeSchedule        academic_year + level + fee_type → Instalment (amount, due_on)
 ├── Charge             enrollment + instalment, amount                     (append-only)
 ├── Adjustment         enrollment, amount, reason, approved_by             (append-only)
 ├── Payment            student, amount, method, reference, receipt_no      (append-only)
 ├── PaymentAllocation  payment → charge, amount                            (append-only)
 ├── PaymentVoid        payment, reason, voided_by                          (append-only)
 └── AuditLog           actor, action, subject, changes, ip                 (append-only)
```

## Invariants
1. Tenant rows have `school_id NOT NULL`. A child's `school_id` equals its parent's (composite FK).
2. At most one `active` academic year per school (partial unique index).
3. At most one active enrollment per student per academic year. A class change closes the old enrollment and opens a new one.
4. A level's section must be one the school has enabled. Its cycle must be one the school offers.
5. Financial records are never updated or deleted (ADR-0003).
6. Receipt numbers are gapless and unique per (school, year).
7. Allocations ≤ payment amount. Allocations to a charge ≤ the charge. Overpayment becomes an explicit credit.
8. Balance = charges − adjustments − allocations of non-voided payments. It is always derivable.

## Naming
`SchoolClass`, because `class` is reserved in PHP. All money columns end in `_amount` and hold integer XAF.
