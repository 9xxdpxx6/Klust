# Move long-running work onto queues

**Priority:** Low
**Labels:** backend, performance, enhancement

## Summary

`.env.example` ships with `QUEUE_CONNECTION=sync`, so emails, Excel exports, and
reward processing run inline during the request. These should be queued for
responsiveness and reliability at scale.

## Proposed Work

- Identify synchronous heavy operations: email verification/notifications,
  analytics Excel exports (Maatwebsite Excel supports queued exports), and
  skill/badge reward processing.
- Convert them to queued jobs/notifications.
- Document running a worker (`php artisan queue:work`) and recommend a non-sync
  driver for production.

## Acceptance Criteria

- Heavy operations are dispatched to the queue.
- The app works with both `sync` (dev) and a real queue driver.
