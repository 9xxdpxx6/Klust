# Generalize the bank simulator into a reusable framework

**Priority:** Medium
**Labels:** enhancement, architecture, simulator

## Summary

The simulator system currently has a single concrete implementation under
`app/Services/Simulators/BankSimulator/`. Adding a new simulator type would
require duplicating bespoke logic.

## Proposed Work

- Define a shared simulator contract/interface (session lifecycle, action
  processing, scoring, evaluation).
- Refactor `BankSimulator` services to implement that contract.
- Allow simulator type to be selected via the `Simulator` model so new types can
  be registered without controller changes.
- Document how to add a new simulator type in `docs/simulator/`.

## Acceptance Criteria

- The bank simulator behaves identically after the refactor.
- A documented extension point exists for new simulator types.
