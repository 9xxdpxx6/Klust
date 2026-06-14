# Accessibility (a11y) pass on key pages

**Priority:** Low
**Labels:** accessibility, frontend

## Summary

The interface has not been audited for accessibility. Improving a11y broadens
usability and signals project maturity.

## Proposed Work

- Run an automated audit (axe / Lighthouse) on the public site, login/register,
  and the student case catalog.
- Fix the highest-impact issues: form labels, color contrast, focus states,
  keyboard navigation, and alt text.
- Add `aria-*` attributes to custom interactive components where PrimeVue does
  not already provide them.

## Acceptance Criteria

- No critical automated a11y violations on the audited pages.
- Core flows are operable with keyboard only.
