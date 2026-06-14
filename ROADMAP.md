# Roadmap

This roadmap reflects the **actual current state** of Klust and outlines realistic next steps. It is a living document — milestones and priorities may shift as the project and its community evolve.

## Current State (v0.1.0)

Klust already implements a substantial feature set:

- Three role-based interfaces (admin/teacher, student, partner) with Spatie permission control
- Full case lifecycle: creation, publishing, archiving, applications, approval/rejection, team management
- Skill points, badges, certificates, and reward rules/events (gamification)
- A working **bank consultant simulator** (client generation, branching dialogue, credit/deposit calculators, scoring, evaluation) with a Three.js scene
- Public landing pages, registration with email verification, and an in-app notification center
- Partner analytics with Excel exports
- A rich demo dataset via database seeders

Known gaps that shape the roadmap below: automated test coverage is minimal (only the default example tests exist), the UI and content are primarily in Russian, and there is no CI pipeline yet.

---

## Milestone 1 — Contributor Readiness (near term)

Make the project easy for new contributors to clone, run, and trust.

- [x] Add a sanitized `.env.example` covering all required configuration keys
- [x] Add a `CODE_OF_CONDUCT.md`, `SECURITY.md`, and issue/PR templates
- [ ] Set up CI (GitHub Actions) to run `pint` and `php artisan test` on pull requests
- [ ] Replace the default example tests with real **feature tests** for core flows (auth, case application, partner approval)
- [ ] Document the seeded demo accounts and how to log in as each role

## Milestone 2 — Quality & Stability (near term)

Raise confidence in the existing functionality.

- [ ] Service-layer unit tests (`CaseService`, `ApplicationService`, `SimulatorService`, `BankSimulator/*`)
- [ ] Form Request validation tests for each role
- [ ] Authorization tests for resource ownership (e.g. a partner editing only their own cases)
- [ ] Establish a measurable test-coverage baseline and publish it
- [ ] Audit and fix N+1 queries with eager loading across dashboards and analytics

## Milestone 3 — Simulator Framework (mid term)

Generalize the bank simulator into a reusable system.

- [ ] Extract a shared simulator engine so new simulator types can be added without bespoke code
- [ ] Admin UI for configuring scoring formulas, client templates, and dialogue scenarios
- [ ] Multi-level difficulty (easy/medium/hard) with distinct client profiles
- [ ] Simulator analytics: completion rates, average scores, common mistakes
- [ ] Optional pluggable LLM-backed dialogue as an alternative to branching trees

## Milestone 4 — Collaboration & Workflow (mid term)

Deepen the student–partner collaboration loop.

- [ ] Case workspace for approved teams (deliverables, milestones, feedback)
- [ ] Partner ↔ team messaging
- [ ] Richer application status history and audit trail in the UI
- [ ] Email/notification preferences per user

## Milestone 5 — Internationalization & Accessibility (mid term)

Broaden the platform's reach.

- [ ] Extract hard-coded strings into Laravel localization files
- [ ] Provide an English locale alongside the existing Russian content
- [ ] Accessibility (a11y) pass on key pages and components

## Milestone 6 — Operations & Scale (longer term)

Prepare for real deployments.

- [ ] Dockerized production setup and deployment documentation
- [ ] Move long-running work (emails, exports, reward processing) onto queues
- [ ] Caching strategy for dashboards and analytics
- [ ] Observability: structured logging, error tracking, and basic metrics

---

## How priorities are set

Milestones 1 and 2 are intentionally first: a maintained open-source project needs reproducible setup and trustworthy tests before new features. Later milestones build on that foundation. Community feedback and contributor interest may reorder items within and across milestones.
