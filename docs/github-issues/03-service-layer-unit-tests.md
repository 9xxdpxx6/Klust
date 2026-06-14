# Add unit tests for the service layer

**Priority:** Medium
**Labels:** tests, quality

## Summary

Business logic lives in `app/Services/` (e.g. `CaseService`,
`CaseApplicationService`, `ApplicationService`, `TeamService`,
`SimulatorService`, and the `Simulators/BankSimulator/*` services), but none of
it is covered by tests.

## Proposed Work

- Add unit tests for the bank simulator's pure logic first, since it is the most
  self-contained: `CreditCalculatorService`, `DepositCalculatorService`,
  `ScoringService`, and `EvaluationService`.
- Add tests for `CaseService` and `TeamService` covering team-size limits and
  transactional behavior.

## Acceptance Criteria

- Calculator and scoring formulas are verified against the documented formulas
  in `docs/simulator/04_FORMULAS.md`.
- Tests pass via `php artisan test`.
