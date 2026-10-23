# Development workflow (ticket → merge)

## Roles
- **Product owner (PO):** approves scope, high-risk plans and accepted risks, and **merges**.
- **Orchestrator (main Claude session):** plans, implements, runs checks, invokes reviewers, writes the ticket log.
- **`reviewer` / `security-reviewer` agents:** read-only, with a fresh context. They return a findings report, which the orchestrator pastes into the ticket. They hold no state between runs. **The ticket file is the memory.**

## Lifecycle
| Status | Exit condition |
|---|---|
| `ready` | Ticket has scope, dependencies, acceptance criteria (AC) and test expectations |
| `planned` | Plan written into the ticket. If labelled `high-risk` or `sensitive`: **PO approved the plan** |
| `in-progress` | Work on branch `feat/<ID>-slug`. Small commits, no unrelated refactoring |
| `verified` | `composer test`, `npm run check`, `npm run types:check` and `npm run build` pass. Exact output summary pasted into the ticket's verification log |
| `reviewed` | `reviewer` run (plus `security-reviewer` if `sensitive`). Critical/high findings fixed and re-reviewed. Accepted lower findings listed |
| `pr-open` | PR opened, CI green |
| `done` | PO merged. Docs updated. Follow-up tickets created |

## Plan (proportionate to risk)
Intended behaviour · files/modules · migrations · authorization and tenant implications · failure modes and edge cases · tests to add · AC.
For a routine task, a few lines is enough.

## Completion report (in the ticket and the PR description)
What changed and why · design decisions · files and migrations · **commands actually run, with their results** · remaining failures · security implications · manual verification needed · docs updated · follow-ups.

## Hard rules
- No merge, deploy, production-data change or irreversible operation without the PO.
- No deleting, skipping or weakening of tests or gates to get a ticket through. If a gate is wrong, open a ticket, justify the change, and get PO approval.
- "Proposed" tests are never reported as "passing". Only commands run in this session count.
