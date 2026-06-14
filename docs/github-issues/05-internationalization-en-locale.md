# Internationalization: extract strings and add an English locale

**Priority:** Medium
**Labels:** i18n, enhancement

## Summary

The UI and content are primarily in Russian, with hard-coded strings in Vue
components and PHP. This limits the project's reach for an international
open-source audience.

## Proposed Work

- Audit hard-coded user-facing strings in `resources/js/` and Blade/PHP.
- Introduce Laravel localization files (`lang/`) and a front-end i18n approach
  for Vue (e.g. a shared translation prop or a lightweight i18n library).
- Provide an English locale alongside the existing Russian content.
- Make the locale switchable (per-user setting or query/locale middleware).

## Acceptance Criteria

- At least the public site and authentication flows are fully translatable.
- Switching locale changes all newly-extracted strings.
